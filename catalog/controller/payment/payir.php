<?php

class ControllerPaymentPayir extends Controller
{
	public function index()
	{
		$this->load->language('payment/payir');
		$this->load->model('checkout/order');
		//$this->load->library('encryption');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$amount     = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

		$encryption = new Encryption($this->config->get('config_encryption'));

		if ($this->currency->getCode() != 'RLS'&& $this->currency->getCode() != 'IRR') {

			$amount = $amount * 10;
		}

		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['error_warning'] = false;

		if (extension_loaded('curl')) {

			$api         = $this->config->get('payir_api');
			$callback    = $this->url->link('payment/payir/callback', 'order_id=' . $encryption->encrypt($order_info['order_id']), '', 'SSL');
			$telephone   = $order_info['telephone'];
			$order_id    = $order_info['order_id'];
			$description = 'پرداخت سفارش شناسه ' . $order_info['order_id'];

			$params = array (

				'api'          => $api,
				'amount'       => $amount,
				'redirect'     => urlencode($callback),
				'mobile'       => $telephone,
				'factorNumber' => $order_id,
				'description'  => $description
			);

			$result = $this->common('https://pay.ir/payment/send', $params);

			if ($result && isset($result->status) && $result->status == 1) {

				$data['action'] = 'https://pay.ir/payment/gateway/' . $result->transId;

			} else {

				$message = isset($result->errorMessage) ? $result->errorMessage : $this->language->get('error_request');

				$data['error_warning'] = $message;
			}

		} else {

			$data['error_warning'] = $this->language->get('error_curl');
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/payir.tpl')) {

			return $this->load->view($this->config->get('config_template') . '/template/payment/payir.tpl', $data);

		} else {

			return $this->load->view('default/template/payment/payir.tpl', $data);
		}
	}

	public function callback()
	{
		$this->load->language('payment/payir');
		$this->load->model('checkout/order');
		//$this->load->library('encryption');

		$this->document->setTitle($this->language->get('heading_title'));

		$encryption = new Encryption($this->config->get('config_encryption'));

		$order_id = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : false;
		$order_id = isset($order_id) ? $order_id : $encryption->decrypt($this->request->get['order_id']);

		$order_info = $this->model_checkout_order->getOrder($order_id);
		$amount     = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

		if ($this->currency->getCode() != 'RLS'&& $this->currency->getCode() != 'IRR') {

			$amount = $amount * 10;
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['button_continue'] = $this->language->get('button_continue');
		$data['continue']        = $this->url->link('common/home', '', 'SSL');

		$data['error_warning'] = false;

		if ($this->request->post['status'] && $this->request->post['transId'] && $this->request->post['factorNumber']) {

			$status        = $this->request->post['status'];
			$trans_id      = $this->request->post['transId'];
			$factor_number = $this->request->post['factorNumber'];
			$message       = $this->request->post['message'];

			if (isset($status) && $status == 1) {

				if ($order_id == $factor_number && $factor_number == $order_info['order_id']) {

					$params = array (

						'api'     => $this->config->get('payir_api'),
						'transId' => $trans_id
					);

					$result = $this->common('https://pay.ir/payment/verify', $params);

					if ($result && isset($result->status) && $result->status == 1) {

						$card_number = isset($_POST['cardNumber']) ? $_POST['cardNumber'] : null;

						if ($amount == $result->amount) {

							$comment = $this->language->get('text_transaction') . $trans_id;

							$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payir_order_status_id'), $comment);

						} else {

							$data['error_warning'] = $this->language->get('error_amount');
						}

					} else {

						$message = isset($result->errorMessage) ? $result->errorMessage : $this->language->get('error_undefined');

						$data['error_warning'] =  $this->language->get('error_request') . '<br/>' . $message;
					}

				} else {

					$data['error_warning'] = $this->language->get('error_invoice');
				}

			} else {

				$data['error_warning'] = $this->language->get('error_payment');
			}

		} else {

			$data['error_warning'] = $this->language->get('error_data');
		}

		if ($data['error_warning']) {

			$data['breadcrumbs'] = array ();

			$data['breadcrumbs'][] = array (

				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home', '', 'SSL')
			);

			$data['breadcrumbs'][] = array (

				'text' => $this->language->get('text_basket'),
				'href' => $this->url->link('checkout/cart', '', 'SSL')
			);
		
			$data['breadcrumbs'][] = array (

				'text' => $this->language->get('text_checkout'),
				'href' => $this->url->link('checkout/checkout', '', 'SSL')
			);

			$data['header'] = $this->load->controller('common/header');
			$data['footer'] = $this->load->controller('common/footer');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/payir_callback.tpl')) {

				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/payir_callback.tpl', $data));

			} else {

				$this->response->setOutput($this->load->view('default/template/payment/payir_callback.tpl', $data));
			}

		} else {

			$this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
		}
	}

	protected function common($url, $params)
	{
		$ch = curl_init();
		
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		
			$response = curl_exec($ch);
			$error    = curl_errno($ch);
		
			curl_close($ch);
		
			$output = $error ? false : json_decode($response);
		
			return $output;
	}
}

