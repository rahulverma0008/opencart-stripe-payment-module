<?php
class ControllerPaymentStripe extends Controller {

	public function index() {

		// load all language variables
		$data = $this->load->language('payment/stripe');

		if ($this->request->server['HTTPS']) {
			$data['store_url'] = HTTPS_SERVER;
		} else {
			$data['store_url'] = HTTP_SERVER;
		}

		if($this->config->get('stripe_environment') == 'live') {
			$data['stripe_public_key'] = $this->config->get('stripe_live_public_key');
			$data['test_mode'] = false;
		} else {
			$data['stripe_public_key'] = $this->config->get('stripe_test_public_key');
			$data['test_mode'] = true;
		}

		$data['stripe_3d_secure_supported'] = $this->config->get('stripe_3d_secure_supported');
		if(is_array($data['stripe_3d_secure_supported']) == false){
			$data['stripe_3d_secure_supported'] = array('required');
		}


		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100; // multiple by 100 to get value in cents

		$data['amount'] = $amount;
		$data['order_id'] = $order_info['order_id'];
		$data['currency'] = $order_info['currency_code'];

		$data['form_action'] = $this->url->link('payment/stripe/send', '', true);
		$data['form_callback'] = $this->url->link('payment/stripe/callback', '', true);


		if(version_compare(VERSION, '2.2.0.0', '>=')) {
			return $this->load->view('payment/stripe', $data);
		} else {
			if(file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/stripe.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/payment/stripe.tpl', $data);
			} else {
				return $this->load->view('default/template/payment/stripe.tpl', $data);
			}
		}
	}

	public function send(){

		$json = array();

		if(!isset($this->request->request['source']) || trim($this->request->request['source']) == '') {
			$json = array('error' => 'Incorrect Stripe Source');
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			return;
		}

		try {
			$source = $this->request->request['source'];
			$order_id = $this->session->data['order_id'];
			$stripe_environment = $this->config->get('stripe_environment');

			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($order_id);

			$this->load->model('payment/stripe');
			if(empty($order_info)){
				throw new Exception("Your order seems lost before payment. We did not charge your payment. Please contact administrator for more information.");
			}

			// load required model
			$this->load->model('account/customer');
			$this->load->library('stripe');
			$this->initStripe();

			// retrieve the source
			$stripe_source = \Stripe\Source::retrieve($source);

			if($stripe_source->status != 'chargeable') {
				throw new Exception("Stripe source you provided is not Chargeable");
			}

			// stripe charge source params
			$stripe_charge_source = array('source' => $stripe_source->id);

			// charge this customer and update order accordingly
			$charge_result = $this->chargeAndUpdateOrder($stripe_charge_source, $order_info, $stripe_environment);

			// set redirect to success or failure page as per payment charge status
			if($charge_result) {
				$json['success'] = $this->url->link('checkout/success', '', true);
			} else {
				$json['error'] = 'Charge could not be completed. Please try again.';
			}
		} catch(Exception $e){
			$this->model_payment_stripe->log($e->getFile(), $e->getLine(), "Exception Caught in send() method", $e->getMessage());
			$json['error'] = $e->getMessage();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
		return;
	}

	/**
	 * this method handles 3D Secure Payments callback
	 * @returns boolean
	 */
	public function callback(){

		try {
			$this->load->model('payment/stripe');
			if(!isset($this->request->request['secure']) || $this->request->request['secure'] != "3D"){

				/*****************************************************************
													Fallback:start
				// stripe redirects back on callback with URL encoded query string
				*****************************************************************/
				$qry_str = html_entity_decode(($_SERVER['QUERY_STRING']));
				$queries = array();
				parse_str($qry_str, $queries);
				foreach($queries as $k=>&$v){
					$key = str_replace('amp;', '', $k);
					$$key = $v;

					if($key == 'livemode') {
						$stripe_environment = $v;
					}

					$key . " = " . $v . "<br/>";
				}
				/*****************************************************************
													Fallback:ends
				// stripe redirects back on callback with URL encoded query string
				*****************************************************************/

				// still source not found? we should throw an error now
				if(!isset($source) || empty($source)){
					throw new Exception("Invalid Request. Payment source is missing.");
				}

			} else {
				$source = $this->request->request['source'];
				$client_secret = $this->request->request['client_secret'];
				$order_id = $this->request->request['order_id'];
				$stripe_environment = $this->request->request['livemode'] == "true"? 'live' : 'test';
			}

			$this->load->library('stripe');
			$this->initStripe();

			// retrieve the source
			$stripe_source = \Stripe\Source::retrieve($source);

			if($stripe_source['client_secret'] !== $client_secret){
				// Source's client secret does not match with Client Secret found in request
				throw new Exception("Source's client secret does not match with Client Secret found in request");
			}

			$this->load->model('checkout/order');
			$order_info = $this->model_checkout_order->getOrder($order_id);

			if($stripe_source['status'] != 'chargeable'){
				// Source is not yet chargeable
				throw new Exception("Source is not in chargeable state");
			} else {

				// stripe charge source params
				$stripe_charge_source = array('source' => $source);

				$charge_result = $this->chargeAndUpdateOrder($stripe_charge_source, $order_info, $stripe_environment);

				$source_params['source'] = $stripe_source['three_d_secure']['card'];

				// redirect to success or failure page as per payment charge status
				if($charge_result){
					$this->response->redirect($this->url->link('checkout/success', '', true));
				} else {
					if(isset($this->session->data['error'])){
						$this->response->redirect($this->url->link('checkout/cart', '', true));
					} else {
						$this->response->redirect($this->url->link('checkout/failure', '', true));
					}
				}
			}
		} catch(Exception $e){
			$this->session->data['error'] = $e->getMessage();
			$this->model_payment_stripe->log($e->getFile(), $e->getLine(), "Exception Caught in send() method", $e->getMessage());

			// mark this order as failed, otherwise it will become a missing order
			if(isset($order_info['order_id'])) {
				if(isset($stripe_source['id']) && isset($stripe_source['status'])){
					$message = 'Charge ID: '.$stripe_source['id']. PHP_EOL .'Status: '. $stripe_source['status'];
				} else {
					$message = $e->getMessage();
				}
				$this->load->model('checkout/order');
				$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('stripe_order_failed_status_id'), $message, false);
			}

			// redirect to cart page
			$this->response->redirect($this->url->link('checkout/cart', '', true));
		}
	}

	/**
	 * this method charges the source and update order accordingly
	 * @returns boolean
	 */
	private function chargeAndUpdateOrder($source_params, $order_info, $stripe_environment){

		// get the order total amount in currency it was paid
		$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100;

		$charge_params = $source_params;
		$charge_params['amount'] = $amount;
		$charge_params['currency'] = $order_info['currency_code'];
		$charge_params['description'] = "Charge for ".$order_info['email'];
		$charge_params['metadata'] = array('order_id' => $order_info['order_id']);

		// charge the customer
		$this->load->model('payment/stripe');
		$charge = \Stripe\Charge::create($charge_params);
	
		if(isset($charge['id'])) {

			// insert stripe order
			$message = 'Charge ID: '.$charge['id']. PHP_EOL .'Status: '. $charge['status'];

			$this->load->model('checkout/order');

			// update order statatus & addOrderHistory
			// paid will be true if the charge succeeded, or was successfully authorized for later capture.
			if($charge['paid'] == true) {
				$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('stripe_order_success_status_id'), $message, false);
			} else {
				$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('stripe_order_failed_status_id'), $message, false);
			}
			
			// charge completed successfully
			return true;
		
		} else {
			// charge could not be completed
			return false;
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