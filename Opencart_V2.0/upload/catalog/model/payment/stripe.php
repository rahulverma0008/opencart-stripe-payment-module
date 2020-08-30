<?php
class ModelPaymentStripe extends Model {
	public function getMethod($address, $total) {
		$this->load->language('payment/stripe');

		$status = true;

		// stripe does not allow payment for 0 amount
		if($total <= 0) {
			$status = false; 
		}

		$method_data = array();

		if ($status) {
			$method_data = array(
				'code'       => 'stripe',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('stripe_sort_order')
			);
		}

		return $method_data;
	}

	public function log($file, $line, $caption, $message){

		if(!$this->config->get('payment_stripe_debug')){
			return;
		}

		$iso_time = date('c');
		$filename = 'stripe-'.strstr($iso_time, 'T', true).'.log';
	
		$log = new Log($filename);
		$msg = "[" . $iso_time . "] ";
		$msg .= "<" . $file . "> ";
		$msg .= "#" . $line . "# ";
		$msg .= "~" . $caption . "~ ";

		if(is_array($message)){
			$msg .= print_r($message, true);
		} else {
			$msg .= PHP_EOL . $message;
		}

		$msg .= PHP_EOL . PHP_EOL;		
		$log->write($msg);
	}
}
