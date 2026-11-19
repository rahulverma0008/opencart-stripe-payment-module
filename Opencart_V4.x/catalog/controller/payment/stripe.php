<?php
namespace Opencart\Catalog\Controller\Extension\Stripe\Payment;

class Stripe extends \Opencart\System\Engine\Controller {

	/** Locales supported by Stripe.js */
	private const STRIPE_LOCALES = [
		'ar', 'bg', 'cs', 'da', 'de', 'el', 'en', 'en-AU', 'en-CA', 'en-NZ', 'en-GB', 'es', 'es-ES', 'es-419', 'et', 'fi', 'fil', 'fr', 'fr-CA',
		'he', 'hr', 'hu', 'id', 'it', 'ja', 'ko', 'lt', 'lv', 'ms', 'mt', 'nb', 'nl', 'pl', 'pt', 'pt-BR', 'ro', 'ru', 'sk', 'sl', 'sv', 'th',
		'tr', 'vi', 'zh', 'zh-HK', 'zh-TW'
	];

	/** Currencies without a minor unit (Stripe wants 1000 for 1000 JPY, not 100000) */
	private const ZERO_DECIMAL = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

	/** Currencies with three decimals (Stripe requires the last digit to be 0) */
	private const THREE_DECIMAL = ['BHD', 'JOD', 'KWD', 'OMR', 'TND'];

	/**
	 * Separator between the class and the method in a route: OpenCart 4.0.2.0 changed it from | to .
	 */
	private function separator(): string {
		return version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';
	}

	/**
	 * Did the customer choose Stripe? OpenCart 4.0.2.0 stores the chosen payment method as an array
	 * (code stripe.stripe), older versions as the plain code (stripe).
	 */
	private function isSelected(): bool {
		$payment_method = $this->session->data['payment_method'] ?? null;

		if (is_array($payment_method)) {
			return ($payment_method['code'] ?? '') == 'stripe.stripe';
		}

		return $payment_method === 'stripe';
	}

	/**
	 * Payment form shown on the checkout confirm step.
	 *
	 * @return string
	 */
	public function index(): string {
		$this->load->language('extension/stripe/payment/stripe');

		$this->load->model('checkout/order');

		if (!isset($this->session->data['order_id'])) {
			return $this->renderError($this->language->get('error_order'));
		}

		$order_info = $this->model_checkout_order->getOrder((int)$this->session->data['order_id']);

		if (!$order_info) {
			return $this->renderError($this->language->get('error_order'));
		}

		if ($this->getAmount($order_info) < 1) {
			return $this->renderError($this->language->get('error_amount'));
		}

		// language variables
		$data['text_pay_with_card'] = $this->language->get('text_pay_with_card');
		$data['text_debug'] = $this->language->get('text_debug');
		$data['button_submit_payment'] = $this->language->get('button_submit_payment');
		$data['error_stripe'] = $this->language->get('error_stripe');

		// stylesheet, root relative so it never gets blocked as mixed content on https
		$data['stylesheet'] = rtrim((string)parse_url((string)$this->config->get('config_url'), PHP_URL_PATH), '/') . '/extension/stripe/catalog/view/template/payment/stylesheet/stripe.css?v=2.0.0';

		if ($this->config->get('payment_stripe_environment') == 'live') {
			$data['payment_stripe_public_key'] = trim((string)$this->config->get('payment_stripe_live_public_key'));
			$data['test_mode'] = false;
		} else {
			$data['payment_stripe_public_key'] = trim((string)$this->config->get('payment_stripe_test_public_key'));
			$data['test_mode'] = true;
		}

		// we will use this owner info to send Stripe from client side. JSON for use inside <script>, safe against </script> and quotes in customer data
		$data['billing_details_json'] = json_encode($this->getBillingDetails($order_info), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);

		// locale lets Stripe.js render its own elements in the language of the store
		$data['locale'] = $this->getStripeLocale();

		// handles the XHR request for client side
		$data['action'] = $this->url->link('extension/stripe/payment/stripe' . $this->separator() . 'confirm', 'language=' . $this->config->get('config_language'), true);

		return $this->load->view('extension/stripe/payment/stripe', $data);
	}

	/**
	 * XHR end point called by the payment form, see stripe.twig
	 *
	 * Request (JSON body):
	 *   payment_method_id  Stripe PaymentMethod of the card to charge
	 *   payment_intent_id  Stripe PaymentIntent, sent after the card action (3D Secure) has been handled
	 *
	 * Response (JSON): success (url) | requires_action | error
	 *
	 * @return void
	 */
	public function confirm(): void {
		$this->load->language('extension/stripe/payment/stripe');

		$this->load->model('extension/stripe/payment/stripe');
		$this->load->model('checkout/order');

		$json = [];

		try {
			if ($this->request->server['REQUEST_METHOD'] != 'POST') {
				throw new \Exception($this->language->get('error_request'));
			}

			// Order, must be the order of the current session. Never trust an order id sent by the browser.
			if (!isset($this->session->data['order_id'])) {
				$this->model_extension_stripe_payment_stripe->log(__FILE__, __LINE__, 'Order id not found in session', '');

				throw new \Exception($this->language->get('error_order'));
			}

			$order_info = $this->model_checkout_order->getOrder((int)$this->session->data['order_id']);

			if (!$order_info) {
				$this->model_extension_stripe_payment_stripe->log(__FILE__, __LINE__, 'Order not found', (string)$this->session->data['order_id']);

				throw new \Exception($this->language->get('error_order'));
			}

			// The customer must have chosen this payment method
			if (!$this->isSelected()) {
				throw new \Exception($this->language->get('error_payment_method'));
			}

			$request = $this->getRequestData();

			$this->model_extension_stripe_payment_stripe->initStripe();

			if (!empty($request['payment_method_id']) && is_string($request['payment_method_id'])) {
				// Create the PaymentIntent
				$payment_intent = \Stripe\PaymentIntent::create($this->getPaymentIntentParams($order_info, $request['payment_method_id']));

				$this->validatePaymentIntent($payment_intent, $order_info);
			} elseif (!empty($request['payment_intent_id']) && is_string($request['payment_intent_id'])) {
				// payment intent id sent from client side (stripe UI): retrieve the intent and confirm it
				$payment_intent = \Stripe\PaymentIntent::retrieve($request['payment_intent_id']);

				// the payment must be the one created for this order
				$this->validatePaymentIntent($payment_intent, $order_info);

				if ($payment_intent->status == 'requires_confirmation') {
					$payment_intent->confirm();
				}
			} else {
				throw new \Exception($this->language->get('error_request'));
			}

			// check if the intent requires any further action
			if ($payment_intent->status == 'requires_action' || $payment_intent->status == 'requires_source_action') {
				$next_action_type = isset($payment_intent->next_action->type) ? $payment_intent->next_action->type : '';

				if ($next_action_type != 'use_stripe_sdk') {
					throw new \Exception($this->language->get('error_status'));
				}

				// Tell the client to handle the action
				$json['requires_action'] = true;
				$json['payment_intent_client_secret'] = $payment_intent->client_secret;
			} elseif ($payment_intent->status == 'succeeded') {
				// The payment is completed, no further action required
				$this->chargeAndUpdateOrder($payment_intent, $order_info);

				$json['success'] = $this->url->link('checkout/success', 'language=' . $this->config->get('config_language'), true);
			} else {
				$this->model_extension_stripe_payment_stripe->log(__FILE__, __LINE__, 'Unexpected PaymentIntent status', $payment_intent->id . ' ' . $payment_intent->status);

				if (!empty($payment_intent->last_payment_error->message)) {
					throw new \Exception($payment_intent->last_payment_error->message);
				}

				throw new \Exception($this->language->get('error_status'));
			}
		} catch (\Stripe\Exception\CardException $e) {
			// the message of a card error ("Your card was declined.") is meant to be shown to the customer
			$json['error'] = $e->getMessage();

			$this->model_extension_stripe_payment_stripe->log($e->getFile(), $e->getLine(), 'Card error caught in confirm() method', $e->getMessage());
		} catch (\Stripe\Exception\ApiErrorException $e) {
			// other API errors may expose internals (key prefix, account settings), only show them in test mode
			$json['error'] = $this->config->get('payment_stripe_environment') == 'live' ? $this->language->get('error_stripe') : $e->getMessage();

			$this->model_extension_stripe_payment_stripe->log($e->getFile(), $e->getLine(), 'Stripe exception caught in confirm() method', $e->getMessage());
		} catch (\Exception $e) {
			$json['error'] = $e->getMessage();

			$this->model_extension_stripe_payment_stripe->log($e->getFile(), $e->getLine(), 'Exception caught in confirm() method', $e->getMessage());
		} catch (\Throwable $e) {
			$json['error'] = $this->language->get('error_stripe');

			$this->model_extension_stripe_payment_stripe->log($e->getFile(), $e->getLine(), 'Error caught in confirm() method', $e->getMessage());
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/**
	 * Body of the request: JSON, with a fallback to POST fields.
	 *
	 * @return array<string, mixed>
	 */
	private function getRequestData(): array {
		$data = json_decode((string)file_get_contents('php://input'), true);

		if (is_array($data)) {
			return $data;
		}

		return $this->request->post;
	}

	/**
	 * Amount to charge for the order in the smallest currency unit. Always an integer, never a float,
	 * so 19.99 becomes 1999 (and not 1998.9999999999998). The same value is used to create a payment and to verify one.
	 *
	 * @param array<string, mixed> $order_info
	 */
	private function getAmount(array $order_info): int {
		$amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

		$currency = strtoupper($order_info['currency_code']);

		if (in_array($currency, self::ZERO_DECIMAL, true)) {
			return (int)round((float)$amount);
		}

		if (in_array($currency, self::THREE_DECIMAL, true)) {
			return (int)(round(round((float)$amount * 1000) / 10) * 10);
		}

		return (int)round((float)$amount * 100);
	}

	private function renderError(string $message): string {
		return '<div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';
	}

	/**
	 * Locale for Stripe.js, 'en-gb' => 'en-GB', unsupported locales fall back to the browser language ('auto').
	 */
	private function getStripeLocale(): string {
		$supported = array_combine(array_map('strtolower', self::STRIPE_LOCALES), self::STRIPE_LOCALES);

		$code = strtolower(str_replace('_', '-', (string)$this->config->get('config_language')));

		if (isset($supported[$code])) {
			return $supported[$code];
		}

		$code = explode('-', $code)[0];

		return $supported[$code] ?? 'auto';
	}

	/**
	 * Prepare data for Stripe:
	 *  - remove empty values (Stripe rejects empty strings for required fields and ignores them for optional ones)
	 *  - decode HTML entities. OpenCart stores customer input HTML encoded (O'Neil => O&#039;Neil), which must not reach Stripe
	 *
	 * @param array<mixed> $data
	 *
	 * @return array<mixed>
	 */
	private function removeEmpty(array $data): array {
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$data[$key] = $this->removeEmpty($value);

				if (!$data[$key]) {
					unset($data[$key]);
				}
			} elseif ($value === null || trim((string)$value) === '') {
				unset($data[$key]);
			} elseif (is_string($value)) {
				$data[$key] = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
			}
		}

		return $data;
	}

	/**
	 * Billing details which are used by Stripe.js when it creates the PaymentMethod
	 *
	 * @param array<string, mixed> $order_info
	 *
	 * @return array<string, mixed>
	 */
	private function getBillingDetails(array $order_info): array {
		// payment address if the store asks for it, otherwise shipping address, otherwise only the customer
		foreach (['payment', 'shipping'] as $type) {
			if (!empty($order_info[$type . '_firstname']) && !empty($order_info[$type . '_address_1'])) {
				return $this->removeEmpty([
					'name'    => $order_info[$type . '_firstname'] . ' ' . $order_info[$type . '_lastname'],
					'email'   => $order_info['email'],
					'address' => [
						'line1'       => $order_info[$type . '_address_1'],
						'line2'       => $order_info[$type . '_address_2'],
						'city'        => $order_info[$type . '_city'],
						'state'       => $order_info[$type . '_zone'],
						'postal_code' => $order_info[$type . '_postcode'],
						'country'     => $order_info[$type . '_iso_code_2']
					]
				]);
			}
		}

		return $this->removeEmpty([
			'name'  => $order_info['firstname'] . ' ' . $order_info['lastname'],
			'email' => $order_info['email']
		]);
	}

	/**
	 * Params to create (and confirm) a Stripe PaymentIntent for a card
	 *
	 * @param array<string, mixed> $order_info
	 *
	 * @return array<string, mixed>
	 */
	private function getPaymentIntentParams(array $order_info, string $payment_method_id): array {
		$params = [
			'payment_method'       => $payment_method_id,
			'payment_method_types' => ['card'],
			'amount'               => $this->getAmount($order_info),
			'currency'             => strtolower($order_info['currency_code']),
			'confirmation_method'  => 'manual',
			'confirm'              => true,
			'description'          => 'Charge for Order #' . $order_info['order_id'],
			// OrderId is verified when the payment is confirmed, see validatePaymentIntent()
			'metadata'             => $this->removeEmpty([
				'OrderId'    => $order_info['order_id'],
				'FirstName'  => $order_info['payment_firstname'] ?: $order_info['firstname'],
				'LastName'   => $order_info['payment_lastname'] ?: $order_info['lastname'],
				'Address'    => trim($order_info['payment_address_1'] . ' ' . $order_info['payment_address_2']),
				'City'       => $order_info['payment_city'],
				'Province'   => $order_info['payment_zone'],
				'PostalCode' => $order_info['payment_postcode'],
				'Country'    => $order_info['payment_iso_code_2'],
				'Email'      => $order_info['email'],
				'Phone'      => $order_info['telephone']
			])
		];

		// the order needs shipping
		if (!empty($order_info['shipping_firstname']) && !empty($order_info['shipping_address_1'])) {
			$params['shipping'] = $this->removeEmpty([
				'name'    => $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'],
				'address' => [
					'line1'       => $order_info['shipping_address_1'],
					'line2'       => $order_info['shipping_address_2'],
					'city'        => $order_info['shipping_city'],
					'state'       => $order_info['shipping_zone'],
					'postal_code' => $order_info['shipping_postcode'],
					'country'     => $order_info['shipping_iso_code_2']
				]
			]);
		}

		return $params;
	}

	/**
	 * Make sure the PaymentIntent has been created for this very order and amount.
	 *
	 * Without this check a customer could submit the id of any successful PaymentIntent
	 * (e.g. one created for a 1.00 order) to get a different, more expensive, order marked as paid.
	 *
	 * @param array<string, mixed> $order_info
	 *
	 * @throws \Exception
	 */
	private function validatePaymentIntent(\Stripe\PaymentIntent $payment_intent, array $order_info): void {
		$valid = true;

		// It must have been created for this very order
		if ((string)($payment_intent->metadata['OrderId'] ?? '') !== (string)$order_info['order_id']) {
			$valid = false;
		}

		if ((int)$payment_intent->amount !== $this->getAmount($order_info)) {
			$valid = false;
		}

		if (strtolower((string)$payment_intent->currency) !== strtolower($order_info['currency_code'])) {
			$valid = false;
		}

		if (!$valid) {
			$this->model_extension_stripe_payment_stripe->log(__FILE__, __LINE__, 'PaymentIntent does not match the order', [
				'payment_intent'          => $payment_intent->id,
				'payment_intent_order_id' => $payment_intent->metadata['OrderId'] ?? '',
				'payment_intent_amount'   => $payment_intent->amount,
				'payment_intent_currency' => $payment_intent->currency,
				'order_id'                => $order_info['order_id'],
				'order_amount'            => $this->getAmount($order_info),
				'order_currency'          => $order_info['currency_code']
			]);

			throw new \Exception(sprintf($this->language->get('error_payment_mismatch'), $payment_intent->id));
		}
	}

	/**
	 * Mark the order as paid. It is safe to call this more than once for the same order (e.g. the customer double clicked the button).
	 *
	 * @param array<string, mixed> $order_info
	 */
	private function chargeAndUpdateOrder(\Stripe\PaymentIntent $payment_intent, array $order_info): void {
		// the order has already got its status (an order has status 0 until its first history is added)
		if ((int)$order_info['order_status_id'] > 0) {
			return;
		}

		$order_status_id = (int)$this->config->get('payment_stripe_order_success_status_id');

		if (!$order_status_id) {
			$order_status_id = (int)$this->config->get('config_order_status_id');
		}

		$message = 'Payment Intent ID: ' . $payment_intent->id . PHP_EOL . 'Status: ' . $payment_intent->status;

		$this->model_checkout_order->addHistory((int)$order_info['order_id'], $order_status_id, $message, false);
	}
}
