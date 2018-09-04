<?php 

class ControllerPaymentPayir extends Controller
{
	private $error = array ();

	public function index()
	{
		$this->load->language('payment/payir');
		$this->load->model('setting/setting');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

			$this->model_setting_setting->editSetting('payir', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_authorization'] = $this->language->get('text_authorization');
		$data['text_sale'] = $this->language->get('text_sale');
        $data['text_edit'] = $this->language->get( 'text_edit' );

		$data['entry_api'] = $this->language->get('entry_api');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

        $data['tab_general'] = $this->language->get('tab_general');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array (

			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array (

			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array (

			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('payment/payir', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['action'] = $this->url->link('payment/payir', 'token=' . $this->session->data['token'], 'SSL');
		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->error['warning'])) {

			$data['error_warning'] = $this->error['warning'];

		} else {

			$data['error_warning'] = false;
		}

		if (isset($this->error['api'])) {

			$data['error_api'] = $this->error['api'];

		} else {

			$data['error_api'] = false;
		}

		if (isset($this->request->post['payir_api'])) {

			$data['payir_api'] = $this->request->post['payir_api'];

		} else {

			$data['payir_api'] = $this->config->get('payir_api');
		}

		if (isset($this->request->post['payir_order_status_id'])) {

			$data['payir_order_status_id'] = $this->request->post['payir_order_status_id'];

		} else {

			$data['payir_order_status_id'] = $this->config->get('payir_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payir_status'])) {

			$data['payir_status'] = $this->request->post['payir_status'];

		} else {

			$data['payir_status'] = $this->config->get('payir_status');
		}

		if (isset($this->request->post['payir_sort_order'])) {

			$data['payir_sort_order'] = $this->request->post['payir_sort_order'];

		} else {

			$data['payir_sort_order'] = $this->config->get('payir_sort_order');
		}

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/payir.tpl', $data));
	}

	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'payment/payir')) {

			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payir_api']) {

			$this->error['warning'] = $this->language->get('error_validate');
			$this->error['api'] = $this->language->get('error_api');
		}

		if (!$this->error) {

			return true;

		} else {

			return false;
		}
	}
}