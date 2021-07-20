<?php
class ControllerExtensionPaymentProdamus extends Controller {
  private $version= '1.3.0-3.0';

  private $error = array();

  private $icons = array('no','all-payments','only-cards');

  public function index() {
    $this->load->language('extension/payment/prodamus');
    $this->load->model('setting/setting');
		$this->load->model('localisation/order_status');
		$this->load->model('localisation/geo_zone');

    $this->document->setTitle($this->language->get('heading_title'));

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $this->model_setting_setting->editSetting('payment_prodamus', $this->request->post);

      $this->session->data['success'] = $this->language->get('text_success');

      $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
    }

    $data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
    $data['text_no'] = $this->language->get('text_no');
    $data['text_yes'] = $this->language->get('text_yes');

    $data['entry_title'] = $this->language->get('entry_title');
    $data['entry_desc'] = $this->language->get('entry_desc');
    $data['entry_site_name'] = $this->language->get('entry_site_name');
    $data['entry_secret_key'] = $this->language->get('entry_secret_key');
    $data['entry_icon'] = $this->language->get('entry_icon');
		$data['entry_total'] = $this->language->get('entry_total');
    $data['entry_success_status'] = $this->language->get('entry_success_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
    $data['entry_sort_order'] = $this->language->get('entry_sort_order');
    $data['entry_confirm_status'] = $this->language->get('entry_confirm_status');

		$data['help_title'] = $this->language->get('help_title');
		$data['help_site_name'] = $this->language->get('help_site_name');
		$data['help_secret_key'] = $this->language->get('help_secret_key');
		$data['help_total'] = $this->language->get('help_total');
    $data['help_success_status'] = $this->language->get('help_success_status');
    $data['help_confirm_status'] = $this->language->get('help_confirm_status');

		$data['action'] = $this->url->link('extension/payment/prodamus', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

    $data['breadcrumbs'] = array();

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
    );

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_extension'),
      'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
    );

    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link('extension/payment/prodamus', 'user_token=' . $this->session->data['user_token'], true)
    );

    $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
    $data['order_statuses_confirm'] = $this->model_localisation_order_status->getOrderStatuses();
    array_unshift( $data['order_statuses_confirm'], array("order_status_id" => "0", "name" => $this->language->get('text_confirm_status')));

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

    if (!empty($this->error)) {
      foreach ($this->error as $k => $v) {
        $data['error_'.$k] = $v;
      }
    }

    if (isset($this->request->post['payment_prodamus_title']))
      $data['payment_prodamus_title'] = $this->request->post['payment_prodamus_title'];
    elseif (is_null($this->config->get('payment_prodamus_title')))
      $data['payment_prodamus_title'] = $this->language->get('default_title');
    else
      $data['payment_prodamus_title'] = $this->config->get('payment_prodamus_title');

    if (isset($this->request->post['payment_prodamus_desc']))
      $data['payment_prodamus_desc'] = $this->request->post['payment_prodamus_desc'];
    elseif (is_null($this->config->get('payment_prodamus_desc')))
      $data['payment_prodamus_desc'] = $this->language->get('default_desc');
    else
      $data['payment_prodamus_desc'] = $this->config->get('payment_prodamus_desc');

    $data['payment_prodamus_site_name'] = isset($this->request->post['payment_prodamus_site_name']) ? $this->request->post['payment_prodamus_site_name'] : $this->config->get('payment_prodamus_site_name');
    $data['payment_prodamus_secret_key'] = isset($this->request->post['payment_prodamus_secret_key']) ? $this->request->post['payment_prodamus_secret_key'] : $this->config->get('payment_prodamus_secret_key');
    $data['payment_prodamus_icon'] = isset($this->request->post['payment_prodamus_icon']) ? $this->request->post['payment_prodamus_icon'] : $this->config->get('payment_prodamus_icon');
    $data['payment_prodamus_total'] = isset($this->request->post['payment_prodamus_total']) ? $this->request->post['payment_prodamus_total'] : $this->config->get('payment_prodamus_total');
    $data['payment_prodamus_success_status_id'] = isset($this->request->post['payment_prodamus_success_status_id']) ? $this->request->post['payment_prodamus_success_status_id'] : $this->config->get('payment_prodamus_success_status_id');
    $data['payment_prodamus_geo_zone_id'] = isset($this->request->post['payment_prodamus_geo_zone_id']) ? $this->request->post['payment_prodamus_geo_zone_id'] : $this->config->get('payment_prodamus_geo_zone_id');
    $data['payment_prodamus_status'] = isset($this->request->post['payment_prodamus_status']) ? $this->request->post['payment_prodamus_status'] : $this->config->get('payment_prodamus_status');
    $data['payment_prodamus_sort_order'] = isset($this->request->post['payment_prodamus_sort_order']) ? $this->request->post['payment_prodamus_sort_order'] : $this->config->get('payment_prodamus_sort_order');

    $data['payment_prodamus_confirm_status_id'] = isset($this->request->post['payment_prodamus_confirm_status_id']) ? $this->request->post['payment_prodamus_confirm_status_id'] : $this->config->get('payment_prodamus_confirm_status_id');
    if ($data['payment_prodamus_confirm_status_id'] == $data['payment_prodamus_success_status_id']) {
      $data['payment_prodamus_confirm_status_id'] = '0';
    }

    if (isset($this->error['title'])) {
      $data['error_title'] = $this->error['title'];
    } else {
      $data['error_title'] = '';
    }

    if (isset($this->error['site_name'])) {
      $data['error_site_name'] = $this->error['site_name'];
    } else {
      $data['error_site_name'] = '';
    }

    if (isset($this->error['secret_key'])) {
      $data['error_secret_key'] = $this->error['secret_key'];
    } else {
      $data['error_secret_key'] = '';
    }

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/payment/prodamus', $data));
  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', 'extension/payment/prodamus')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    if (empty($this->request->post['payment_prodamus_title'])) {
      $this->error['title'] = $this->language->get('error_title');
    }

    if (empty($this->request->post['payment_prodamus_site_name'])) {
      $this->error['site_name'] = $this->language->get('error_site_name');
    }

    if (empty($this->request->post['payment_prodamus_secret_key'])) {
      $this->error['secret_key'] = $this->language->get('error_secret_key');
    }

    if ($this->request->post['payment_prodamus_success_status_id'] == $this->request->post['payment_prodamus_confirm_status_id']) {
      $this->error['warning'] = $this->language->get('error_confirm_status');
    }

    return !$this->error;
  }
}