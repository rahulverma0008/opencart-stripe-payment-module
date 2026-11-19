<?php
namespace Opencart\Admin\Controller\Extension\Stripe\Payment;

class Stripe extends \Opencart\System\Engine\Controller {

	/**
	 * Settings of the extension and their default values
	 *
	 * @var array<string, mixed>
	 */
	private const SETTINGS = [
		'payment_stripe_environment'             => 'test',
		'payment_stripe_test_public_key'         => '',
		'payment_stripe_test_secret_key'         => '',
		'payment_stripe_live_public_key'         => '',
		'payment_stripe_live_secret_key'         => '',
		'payment_stripe_order_success_status_id' => '',
		'payment_stripe_order_failed_status_id'  => '',
		'payment_stripe_debug'                   => 0,
		'payment_stripe_status'                  => 0,
		'payment_stripe_sort_order'              => ''
	];

	/**
	 * Separator between the class and the method in a route: OpenCart 4.0.2.0 changed it from | to .
	 */
	private function separator(): string {
		return version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';
	}

	/**
	 * Settings page
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/stripe/payment/stripe');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/stripe/payment/stripe', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/stripe/payment/stripe' . $this->separator() . 'save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

		// Settings
		foreach (self::SETTINGS as $key => $default) {
			if ($this->config->has($key)) {
				$data[$key] = $this->config->get($key);
			} else {
				$data[$key] = $default;
			}
		}

		// Order Status
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/stripe/payment/stripe', $data));
	}

	/**
	 * Save the settings (AJAX)
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('extension/stripe/payment/stripe');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/stripe/payment/stripe')) {
			$json['error']['warning'] = $this->language->get('error_permission');
		}

		// Keys are pasted from the Stripe dashboard, remove whitespace around them
		foreach (['test_public_key', 'test_secret_key', 'live_public_key', 'live_secret_key'] as $key) {
			if (isset($this->request->post['payment_stripe_' . $key])) {
				$this->request->post['payment_stripe_' . $key] = trim((string)$this->request->post['payment_stripe_' . $key]);
			}
		}

		if (!isset($this->request->post['payment_stripe_environment']) || !in_array($this->request->post['payment_stripe_environment'], ['test', 'live'])) {
			$json['error']['environment'] = $this->language->get('error_environment');
		} else {
			// the keys of the chosen environment are required
			$environment = $this->request->post['payment_stripe_environment'];

			foreach (['public_key', 'secret_key'] as $key) {
				if (empty($this->request->post['payment_stripe_' . $environment . '_' . $key])) {
					$json['error'][$environment . '_' . $key] = $this->language->get('error_' . $environment . '_' . $key);
				}
			}
		}

		if (isset($json['error']) && !isset($json['error']['warning'])) {
			$json['error']['warning'] = $this->language->get('error_warning');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('payment_stripe', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
