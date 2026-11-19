<?php
namespace Opencart\Catalog\Model\Extension\Stripe\Payment;

use Opencart\System\Library\Log;

class Stripe extends \Opencart\System\Engine\Model {

	/**
	 * Called by OpenCart (checkout/payment_method) to list the payment options of this extension.
	 *
	 * @param array<string, mixed> $address
	 *
	 * @return array<string, mixed>
	 */
	public function getMethods(array $address = []): array {
		$this->load->language('extension/stripe/payment/stripe');

		$name = $this->language->get('text_title');

		$option_data['stripe'] = [
			'code' => 'stripe.stripe',
			'name' => $name
		];

		return [
			'code'       => 'stripe',
			'name'       => $name,
			'option'     => $option_data,
			'sort_order' => $this->config->get('payment_stripe_sort_order')
		];
	}

	/**
	 * OpenCart 4.0.0.0 - 4.0.1.1 call getMethod() and expect a title instead of the options of getMethods().
	 *
	 * @param array<string, mixed> $address
	 *
	 * @return array<string, mixed>
	 */
	public function getMethod(array $address = []): array {
		$method = $this->getMethods($address);

		return [
			'code'       => 'stripe',
			'title'      => $method['name'],
			'sort_order' => $method['sort_order']
		];
	}

	/**
	 * Load the Stripe SDK and set the secret key of the configured environment.
	 *
	 * @throws \Exception
	 */
	public function initStripe(): bool {
		require_once(DIR_EXTENSION . 'stripe/system/library/stripe.php');

		if ($this->config->get('payment_stripe_environment') == 'live') {
			$secret_key = trim((string)$this->config->get('payment_stripe_live_secret_key'));
		} else {
			$secret_key = trim((string)$this->config->get('payment_stripe_test_secret_key'));
		}

		if ($secret_key === '') {
			$this->log(__FILE__, __LINE__, 'Unable to load Stripe libraries', 'Secret key found empty. Please configure Stripe credentials correctly.');

			throw new \Exception('Stripe is not configured correctly. Please contact the store administrator.');
		}

		\Stripe\Stripe::setApiKey($secret_key);

		return true;
	}

	/**
	 * Write to system/storage/logs/stripe-YYYY-MM-DD.log (only if Debug is enabled in the settings)
	 *
	 * @param string|array<mixed> $message
	 */
	public function log(string $file, int $line, string $caption, $message): void {
		if (!$this->config->get('payment_stripe_debug')) {
			return;
		}

		$iso_time = date('c');
		$filename = 'stripe-' . strstr($iso_time, 'T', true) . '.log';

		$log = new Log($filename);
		$msg = '[' . $iso_time . '] ';
		$msg .= '<' . $file . '> ';
		$msg .= '#' . $line . '# ';
		$msg .= '~' . $caption . '~ ';

		if (is_array($message)) {
			$msg .= print_r($message, true);
		} else {
			$msg .= PHP_EOL . $message;
		}

		$msg .= PHP_EOL . PHP_EOL;
		$log->write($msg);
	}
}
