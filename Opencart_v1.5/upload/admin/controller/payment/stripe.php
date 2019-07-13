<?php
class ControllerPaymentStripe extends Controller {
	private $error = array();

	public function index() {

		// load all language variables
		$this->data = $this->load->language('payment/stripe');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('stripe', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/stripe', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/stripe', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['stripe_environment'])) {
			$this->data['stripe_environment'] = $this->request->post['stripe_environment'];
		} elseif ($this->config->has('stripe_environment')) {
			$this->data['stripe_environment'] = $this->config->get('stripe_environment');
		} else {
			$this->data['stripe_environment'] = 'test';
		}
		if (isset($this->request->post['stripe_test_public_key'])) {
			$this->data['stripe_test_public_key'] = $this->request->post['stripe_test_public_key'];
		} else if($this->config->has('stripe_test_public_key')){
			$this->data['stripe_test_public_key'] = $this->config->get('stripe_test_public_key');
		} else {
			$this->data['stripe_test_public_key'] = '';
		}

		if (isset($this->request->post['stripe_test_secret_key'])) {
			$this->data['stripe_test_secret_key'] = $this->request->post['stripe_test_secret_key'];
		} else if($this->config->has('stripe_test_secret_key')){
			$this->data['stripe_test_secret_key'] = $this->config->get('stripe_test_secret_key');
		} else {
			$this->data['stripe_test_secret_key'] = '';
		}

		if (isset($this->request->post['stripe_live_public_key'])) {
			$this->data['stripe_live_public_key'] = $this->request->post['stripe_live_public_key'];
		} else if($this->config->has('stripe_live_public_key')){
			$this->data['stripe_live_public_key'] = $this->config->get('stripe_live_public_key');
		} else {
			$this->data['stripe_live_public_key'] = '';
		}

		if (isset($this->request->post['stripe_live_secret_key'])) {
			$this->data['stripe_live_secret_key'] = $this->request->post['stripe_live_secret_key'];
		} else if($this->config->has('stripe_live_secret_key')){
			$this->data['stripe_live_secret_key'] = $this->config->get('stripe_live_secret_key');
		} else {
			$this->data['stripe_live_secret_key'] = '';
		}

		if (isset($this->request->post['stripe_order_success_status_id'])) {
			$this->data['stripe_order_success_status_id'] = $this->request->post['stripe_order_success_status_id'];
		} else if($this->config->has('stripe_order_success_status_id')){
			$this->data['stripe_order_success_status_id'] = $this->config->get('stripe_order_success_status_id');
		} else {
			$this->data['stripe_order_success_status_id'] = '';
		}

		if (isset($this->request->post['stripe_order_failed_status_id'])) {
			$this->data['stripe_order_failed_status_id'] = $this->request->post['stripe_order_failed_status_id'];
		} else if($this->config->has('stripe_order_failed_status_id')){
			$this->data['stripe_order_failed_status_id'] = $this->config->get('stripe_order_failed_status_id');
		} else {
			$this->data['stripe_order_failed_status_id'] = '';
		}

		if (isset($this->request->post['stripe_status'])) {
			$this->data['stripe_status'] = $this->request->post['stripe_status'];
		} else if($this->config->has('stripe_status')){
			$this->data['stripe_status'] = (int)$this->config->get('stripe_status');
		} else {
			$this->data['stripe_status'] = 0;
		}

		if (isset($this->request->post['stripe_sort_order'])) {
			$this->data['stripe_sort_order'] = $this->request->post['stripe_sort_order'];
		} else if($this->config->has('stripe_sort_order')){
			$this->data['stripe_sort_order'] = (int)$this->config->get('stripe_sort_order');
		} else {
			$this->data['stripe_sort_order'] = 0;
		}

		if (isset($this->request->post['stripe_debug'])) {
			$this->data['stripe_debug'] = $this->request->post['stripe_debug'];
		} else if($this->config->has('stripe_debug')){
			$this->data['stripe_debug'] = (int)$this->config->get('stripe_debug');
		} else {
			$this->data['stripe_debug'] = 0;
		}

		// populate errors
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		if (isset($this->error['test_public_key'])) {
			$this->data['error_test_public_key'] = $this->error['test_public_key'];
		} else {
			$this->data['error_test_public_key'] = '';
		}

		if (isset($this->error['test_secret_key'])) {
			$this->data['error_test_secret_key'] = $this->error['test_secret_key'];
		} else {
			$this->data['error_test_secret_key'] = '';
		}

		if (isset($this->error['live_public_key'])) {
			$this->data['error_live_public_key'] = $this->error['live_public_key'];
		} else {
			$this->data['error_live_public_key'] = '';
		}

		if (isset($this->error['live_secret_key'])) {
			$this->data['error_live_secret_key'] = $this->error['live_secret_key'];
		} else {
			$this->data['error_live_secret_key'] = '';
		}

		$this->data['token'] = $this->session->data['token'];

		$this->template = 'payment/stripe.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/stripe')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		
		if(isset($this->request->post['stripe_environment'])){

			if($this->request->post['stripe_environment'] == 'test'){

				if(!isset($this->request->post['stripe_test_public_key']) || trim($this->request->post['stripe_test_public_key']) == ''){
					$this->error['test_public_key'] = $this->language->get('error_test_public_key');
				}
				if(!isset($this->request->post['stripe_test_secret_key']) || trim($this->request->post['stripe_test_secret_key']) == ''){
					$this->error['test_secret_key'] = $this->language->get('error_test_secret_key');
				}
			
			} else {

				if(!isset($this->request->post['stripe_live_public_key']) || trim($this->request->post['stripe_live_public_key']) == ''){
					$this->error['live_public_key'] = $this->language->get('error_live_public_key');
				}
				if(!isset($this->request->post['stripe_live_secret_key']) || trim($this->request->post['stripe_live_secret_key']) == ''){
					$this->error['live_secret_key'] = $this->language->get('error_live_secret_key');
				}
			}
		} else {
			$this->error['environment'] = $this->language->get('error_environment');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
