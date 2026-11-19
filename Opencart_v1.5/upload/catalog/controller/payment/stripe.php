<?php
class ControllerPaymentStripe extends Controller {

	public function index() {

		// load all language variables
		$this->data = $this->load->language('payment/stripe');

		if (!empty($this->request->server['HTTPS'])) {
			$this->data['store_url'] = HTTPS_SERVER;
		} else {
			$this->data['store_url'] = HTTP_SERVER;
		}

		if($this->config->get('stripe_environment') == 'live') {
			$this->data['stripe_public_key'] = $this->config->get('stripe_live_public_key');
			$this->data['test_mode'] = false;
		} else {
			$this->data['stripe_public_key'] = $this->config->get('stripe_test_public_key');
			$this->data['test_mode'] = true;
		}

		// get order info
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		// we will use this owner info to send Stripe from client side
		$this->data['billing_details'] = array(
										'name' => $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'],
										'email' => $order_info['email'],
										'address' => array(
											'line1'	=> $order_info['payment_address_1'],
											'line2'	=> $order_info['payment_address_2'],
											'city'	=> $order_info['payment_city'],
											'state'	=> $order_info['payment_zone'],
											'postal_code' => $order_info['payment_postcode'],
											'country' => $order_info['payment_iso_code_2']
										)
									);

		// handles the XHR request for client side
		$this->data['action'] = $this->url->link('payment/stripe/confirm', '', 'SSL');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/stripe.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/stripe.tpl';
		} else {
			$this->template = 'default/template/payment/stripe.tpl';
		}		

		$this->render();
	}



	public function confirm(){
		
		$this->load->model('payment/stripe');
		$json = array('error' => 'Server did not get valid request to process');

		try{

			if(!isset($this->session->data['order_id'])){
				$this->model_payment_stripe->log(__FILE__, __LINE__, "Session Data ", $this->session->data);
				throw new Exception("Your order seems lost in session. We did not charge your payment. Please contact administrator for more information.");
			}

			// retrieve json from POST body
			$json_str = file_get_contents('php://input');
			$json_obj = json_decode($json_str);
			
			// load stripe libraries
			$this->initStripe();

			// get order info
			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

			if(empty($order_info)){
				$this->model_payment_stripe->log(__FILE__, __LINE__, "Order Data ", $this->order_info);
				throw new Exception("Your order seems lost before payment. We did not charge your payment. Please contact administrator for more information.");
			}

			// Create the PaymentIntent
			if (isset($json_obj->payment_method_id)) {

				// amount to charge for the order
				$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

				// amount in the smallest unit of the currency (cents)
				$amount = $this->getAmount($order_info);

				// Create the PaymentIntent
				$intent = \Stripe\PaymentIntent::create(array(
					'payment_method' => $json_obj->payment_method_id,
					'amount' => $amount,
					'currency' => strtolower($order_info['currency_code']),
					'confirmation_method' => 'manual',
					'confirm' => true,
					'description' => "Charge for Order #".$order_info['order_id'],
					'metadata' => array(
												'order_id'	=> $order_info['order_id'],
												'email'		=> $order_info['email']
											),
				));
			}

			if (isset($json_obj->payment_intent_id)) {
				$intent = \Stripe\PaymentIntent::retrieve(
					 $json_obj->payment_intent_id
				);
				// the payment must be the one created for this order
				$this->validateIntent($intent, $order_info);

				$intent->confirm();
			}

			if(!empty($intent)) {
				if (($intent->status == 'requires_action' || $intent->status == 'requires_source_action') &&
				$intent->next_action->type == 'use_stripe_sdk') {
					// Tell the client to handle the action
					$json = array(
						'requires_action' => true,
						'payment_intent_client_secret' => $intent->client_secret
					);
				} else if ($intent->status == 'succeeded') {
					// The payment didn’t need any additional actions and completed!
					// Handle post-payment fulfillment

					// charge this customer and update order accordingly
					$charge_result = $this->chargeAndUpdateOrder($intent, $order_info);

					// set redirect to success or failure page as per payment charge status
					if($charge_result) {
						$json = array('success' => $this->url->link('checkout/success', '', true));
					} else {
						$json = array('error' => 'Payment could not be completed. Please try again.');
					}

				} else {
					// Invalid status
					$json = array('error' => 'Invalid PaymentIntent Status ('.$intent->status.')');
				}
			}

		} catch (\Stripe\Error\Base $e) {
			// Display error on client
			$json = array('error' => $e->getMessage());
			
			$this->model_payment_stripe->log($e->getFile(), $e->getLine(), "Stripe Exception caught in confirm() method", $e->getMessage());

		} catch (\Exception $e) {
			$json = array('error' => $e->getMessage());

			$this->model_payment_stripe->log($e->getFile(), $e->getLine(), "Exception caught in confirm() method", $e->getMessage());

		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		return;
	}


	/**
	 * this method charges the source and update order accordingly
	 * @returns boolean
	 */
	private function chargeAndUpdateOrder($intent, $order_info){

		if(isset($intent->id)) {

			// insert stripe order
			$message = 'Payment Intent ID: '.$intent->id. PHP_EOL .'Status: '. $intent->status;

			$this->load->model('checkout/order');

			// update order statatus & addOrderHistory
			// paid will be true if the charge succeeded, or was successfully authorized for later capture.
			if($intent->status == "succeeded") {
				$this->model_checkout_order->confirm($order_info['order_id'], $this->config->get('stripe_order_success_status_id'), $message, false);
			} else {
				$this->model_checkout_order->confirm($order_info['order_id'], $this->config->get('stripe_order_failed_status_id'), $message, false);
			}
			
			// charge completed successfully
			return true;
		
		} else {
			// charge could not be completed
			return false;
		}
	}

	/**
	 * Amount of the order in the smallest unit of its currency (an integer, e.g. cents).
	 * The same value is used to create a payment and to verify one.
	 */
	private function getAmount($order_info) {
		$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

		// currencies without a minor unit (Stripe wants 1000 for 1000 JPY, not 100000)
		$zero_decimal = array('BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF');

		if (in_array(strtoupper($order_info['currency_code']), $zero_decimal)) {
			return (int)round($amount);
		}

		return (int)round($amount * 100);
	}

	/**
	 * A payment id sent by the browser must be the payment which has been created for THIS order, for the amount of this order.
	 * Without this check a customer could pay a cheap order and submit that (successful) payment for an expensive one.
	 */
	private function validateIntent($intent, $order_info) {
		$order_id = isset($intent->metadata['OrderId']) ? (string)$intent->metadata['OrderId'] : '';

		if ($order_id !== (string)$order_info['order_id']
			|| (int)$intent->amount !== $this->getAmount($order_info)
			|| strtolower((string)$intent->currency) !== strtolower($order_info['currency_code'])) {

			$this->log->write('Stripe: payment ' . $intent->id . ' does not match order #' . $order_info['order_id'] . ' (payment order: ' . $order_id . ', payment amount: ' . $intent->amount . ' ' . $intent->currency . ', order amount: ' . $this->getAmount($order_info) . ' ' . $order_info['currency_code'] . ')');

			throw new Exception("We could not verify this payment against your order. If you have been charged, please contact the store administrator with the reference " . $intent->id . ".");
		}
	}

	private function initStripe() {
		$this->load->library('stripe');
		if($this->config->get('stripe_environment') == 'live' || (isset($this->request->request['livemode']) && $this->request->request['livemode'] == "true")) {
			$stripe_secret_key = $this->config->get('stripe_live_secret_key');
		} else {
			$stripe_secret_key = $this->config->get('stripe_test_secret_key');
		}

		if($stripe_secret_key != '' && $stripe_secret_key != null) {
			\Stripe\Stripe::setApiKey($stripe_secret_key);
			return true;
		}

		$this->load->model('payment/stripe');
		$this->model_payment_stripe->log(__FILE__, __LINE__, "Unable to load stripe libraries");
		throw new Exception("Unable to load stripe libraries.");
		// return false;
	}
}