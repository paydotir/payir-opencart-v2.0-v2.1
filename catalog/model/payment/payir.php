<?php 

class ModelPaymentPayir extends Model
{
	public function getMethod($address)
	{
		$this->load->language('payment/payir');

		if ($this->config->get('payir_status')) {

			$status = true;

		} else {

			$status = false;
		}

		$method_data = array ();

		if ($status) {

			$method_data = array (
        		'code'       => 'payir',
        		'title'      => $this->language->get('text_title'),
				'terms'      => null,
				'sort_order' => $this->config->get('payir_sort_order')
			);
		}

		return $method_data;
	}
}
