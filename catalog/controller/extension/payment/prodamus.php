<?php
class ControllerExtensionPaymentProdamus extends Controller {
	public function index() {
    $this->load->language('extension/payment/prodamus');
    $this->load->model('checkout/order');

    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

    if (method_exists($this->model_checkout_order, 'getOrderTotals')) {
      $order_totals = $this->model_checkout_order->getOrderTotals($this->session->data['order_id']);
    }
    else {
      $order_totals = $this->_getOrderTotals($this->session->data['order_id']);
    }

    if (method_exists($this->model_checkout_order, 'getOrderProducts')) {
      $order_products = $this->model_checkout_order->getOrderProducts($this->session->data['order_id']);
    }
    else {
      $order_products = $this->_getOrderProducts($this->session->data['order_id']);
    }

    $data['payform_data'] = $this->_payment($order_info, $order_totals, $order_products);

    // $data['button_confirm'] = $this->language->get('button_confirm');
		// $data['button_confirm'] = sprintf($this->language->get('text_button_confirm'), $this->currency->format($total['value'], $this->session->data['currency']));

    $status_confirm_id = $this->config->get('payment_prodamus_confirm_status_id');

    if ($status_confirm_id > 0) {
      $data['button_confirm'] = 'Подтвердить заказ';
      $data['action'] =  $this->url->link('checkout/success');
    } else {
      $data['button_confirm'] = sprintf($this->language->get('text_button_confirm'), $this->currency->format($data['payform_data']['totals']['total_order'], $this->session->data['currency']));
      $data['action'] = $this->config->get('payment_prodamus_site_name');
    }

		$data['text_loading'] = $this->language->get('text_loading');

		$data['continue'] = $this->url->link('checkout/success', '', true);

    $data['confirm'] = $this->url->link('extension/payment/prodamus/confirm', '', true);

		return $this->load->view('extension/payment/prodamus', $data);
	}

	public function confirm() {
		if ($this->session->data['payment_method']['code'] == 'prodamus') {
      $this->load->model('checkout/order');
      
      $status_confirm_id = $this->config->get('payment_prodamus_success_status_confirm_id');

      $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('config_order_status_id'));

			$this->cart->clear();

			// Add to activity log
			if ($this->config->get('config_customer_activity')) {
				$this->load->model('account/activity');

				if ($this->customer->isLogged()) {
					$activity_data = array(
						'customer_id' => $this->customer->getId(),
						'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
						'order_id'    => $this->session->data['order_id']
					);

					$this->model_account_activity->addActivity('order_account', $activity_data);
				} else {
					$activity_data = array(
						'name'     => $this->session->data['guest']['firstname'] . ' ' . $this->session->data['guest']['lastname'],
						'order_id' => $this->session->data['order_id']
					);

					$this->model_account_activity->addActivity('order_guest', $activity_data);
				}
			}

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);
		}
	}

  public function repay() {
    $order_id = isset($this->request->get['order_id']) ? $this->request->get['order_id'] : 0;

    if (!$this->customer->isLogged()) {
      $this->session->data['redirect'] = $this->url->link('payment/prodamus/repay', 'order_id='.$order_id, true);
      $this->response->redirect($this->url->link('account/login', '', true));
    }

    $this->load->language('extension/payment/prodamus');
    $this->load->model('account/order');

    $order_info = $this->model_account_order->getOrder($order_id);

    if ($order_info){
      if (method_exists($this->model_account_order, 'getOrderTotals')) {
        $order_totals = $this->model_account_order->getOrderTotals($order_id);
      }
      else {
        $order_totals = $this->_getOrderTotals($this->session->data['order_id']);
      }

      if (method_exists($this->model_account_order, 'getOrderProducts')) {
        $order_products = $this->model_account_order->getOrderProducts($order_id);
      }
      else {
        $order_products = $this->_getOrderProducts($this->session->data['order_id']);
      }

      $data['payform_data'] = $this->_payment($order_info, $order_totals, $order_products);

      // $data['button_confirm'] = $this->language->get('button_confirm');
      // $data['button_confirm'] = sprintf($this->language->get('text_button_confirm'), $data['payform_data']['totals']['total_pay']);
      $data['button_confirm'] = sprintf($this->language->get('text_button_confirm'), $this->currency->format($data['payform_data']['totals']['total_order'], $this->session->data['currency']));

      $data['text_loading'] = $this->language->get('text_loading');

      $data['continue'] = $this->url->link('checkout/success', '', true);

      $data['action'] = $this->config->get('payment_prodamus_site_name');

      $data['confirm'] = $this->url->link('extension/payment/prodamus/confirm', '', true);

      return $this->load->view('extension/payment/prodamus', $data);
    }
    else {
      $this->response->redirect($this->url->link('account/order/info', 'order_id='.$order_id, true));
    }
  }

	public function response() {
    $this->load->language('account/order');
    $this->load->language('extension/payment/prodamus');
    $this->load->model('checkout/order');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_history'),
			'href' => $this->url->link('account/order', '', true)
		);

		$data['heading_title'] = $this->language->get('heading_title');

    $payform_data = array();

    array_walk( $this->request->get, function( &$v, $k ) use( &$payform_data ) {
      if ( preg_match('~^_payform_(.*)$~', $k, $m))
        $payform_data[$k] = $v;
    });

    $payform_data_sign = isset( $payform_data['_payform_sign'] ) ? $payform_data['_payform_sign'] : '';

    unset( $payform_data['_payform_sign'] );

    try {
      if ( empty( $payform_data ) ) {
        throw new Exception( 'emptydata' );
      }
      if ( empty( $payform_data_sign ) ) {
        throw new Exception( 'emptysign' );
      }
      if ( !$this->_verifyHmac( $payform_data, $payform_data_sign ) ) {
        throw new Exception( 'verify' );
      }
      if ( !isset( $payform_data['_payform_order_id'] ) ) {
        throw new Exception( 'emptyorder' );
      }
      if ( !isset( $payform_data['_payform_status'] ) ) {
        throw new Exception( 'emptystatus' );
      }

      // успешная оплата
      if ( 'success' == $payform_data['_payform_status'] ) {
        $order_status_id = $this->config->get('payment_prodamus_success_status_id');
        $comment = sprintf($this->language->get('response_text_status_success'), $payform_data['_payform_id']);
      }
      else {
        throw new Exception( 'status' );
      }

      $order_info = $this->model_checkout_order->getOrder($payform_data['_payform_order_id']);

      if (!$order_info) {
        throw new Exception( 'order' );
      }

      $this->model_checkout_order->addOrderHistory($order_info['order_id'], $order_status_id, $comment);

	if (isset($this->session->data['order_id']) && ($order_info['order_id'] == $this->session->data['order_id'])) {
		$this->cart->clear();

		// Add to activity log
		if ($this->config->get('config_customer_activity')) {
			$this->load->model('account/activity');

			if ($this->customer->isLogged()) {
				$activity_data = array(
					'customer_id' => $this->customer->getId(),
					'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
					'order_id'    => $this->session->data['order_id']
				);

				$this->model_account_activity->addActivity('order_account', $activity_data);
			} else {
				$activity_data = array(
					'name'     => $this->session->data['guest']['firstname'] . ' ' . $this->session->data['guest']['lastname'],
					'order_id' => $this->session->data['order_id']
				);

				$this->model_account_activity->addActivity('order_guest', $activity_data);
			}
		}

		unset($this->session->data['shipping_method']);
		unset($this->session->data['shipping_methods']);
		unset($this->session->data['payment_method']);
		unset($this->session->data['payment_methods']);
		unset($this->session->data['guest']);
		unset($this->session->data['comment']);
		unset($this->session->data['order_id']);
		unset($this->session->data['coupon']);
		unset($this->session->data['reward']);
		unset($this->session->data['voucher']);
		unset($this->session->data['vouchers']);
		unset($this->session->data['totals']);
	}

      if ($this->customer->isLogged()) {
        $data['breadcrumbs'][] = array(
          'text'      => $this->language->get('text_order'),
          'href'      => $this->url->link('account/order/info', 'order_id=' . $order_info['order_id'], true)
        );
        $data['text_message'] = sprintf($this->language->get('response_text_customer'), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('information/contact'));
      }
      else {
        $data['text_message'] = sprintf($this->language->get('response_text_guest'), $this->url->link('information/contact'));
      }
    }
    catch ( Exception $e ) {
      $data['text_message'] = $this->language->get('response_error_' . $e->getMessage());
    }

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['column_right'] = $this->load->controller('common/column_right');
    $data['content_top'] = $this->load->controller('common/content_top');
    $data['content_bottom'] = $this->load->controller('common/content_bottom');
    $data['footer'] = $this->load->controller('common/footer');

    echo $this->load->view('extension/payment/prodamus_response', $data);
	}

  public function makePayformData($args) {
    $is_extension_enabled = $this->config->get('payment_prodamus_status'); // статус расширения
    if (!$is_extension_enabled) { return array(); }

    $this->load->model('checkout/order');

    $order_info = $this->model_checkout_order->getOrder($args['order_id']);

    if (isset($args['order_totals'])) {
      $order_totals = $args['order_totals'];
    } elseif (method_exists($this->model_checkout_order, 'getOrderTotals')) {
      $order_totals = $this->model_checkout_order->getOrderTotals($args['order_id']);
    }
    else {
      $order_totals = $this->_getOrderTotals($args['order_id']);
    }

    if (isset($args['order_products'])) {
      $order_products = $args['order_products'];
    } elseif (method_exists($this->model_checkout_order, 'getOrderProducts')) {
      $order_products = $this->model_checkout_order->getOrderProducts($args['order_id']);
    } else {
      $order_products = $this->_getOrderProducts($args['order_id']);
    }

    $data['payform_data'] = $this->_payment($order_info, $order_totals, $order_products, true);

    if (isset($args['is_account_order_info']) && $args['is_account_order_info']) {
      $status_confirm_id = $this->config->get('payment_prodamus_confirm_status_id'); // статус, разрешающий оплату
      $status_after_payment_id = $this->config->get('payment_prodamus_success_status_id'); // статус после оплаты

      $data_order_status = $this->db->query('SELECT `order_status_id` FROM `' . DB_PREFIX . 'order` WHERE `order_id` = '.$args['order_id']);
      $data_order_status = $data_order_status->rows[0]['order_status_id']; // текущий статус заказа

      if (
        ($status_confirm_id > 0 && $data_order_status == $status_confirm_id) || 
        (!$status_confirm_id && $data_order_status != $status_after_payment_id) &&
        $is_extension_enabled
      ) {
        $this->load->language('extension/payment/prodamus');
        $data['button_confirm'] = sprintf($this->language->get('text_button_confirm'), $this->currency->format($data['payform_data']['totals']['total_order'], $this->session->data['currency']));
        $data['action'] = $this->config->get('payment_prodamus_site_name');
      } else {
        return array();
      }
      $data['view'] = $this->load->view('extension/payment/prodamus', $data);
      $data['prodamus_status_id'] = isset($status_after_payment_id) ? $status_after_payment_id : 0;

      return $data;
    } else {
      return $data['payform_data']['data'];
    }
  }

  private function _getOrderTotals($order_id) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");
    return $query->rows;
  }

  private function _getOrderProducts($order_id) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
    return $query->rows;
  }

  private function _payment($order_info, $order_totals, $order_products, $is_account_order_info = false) {
    $totals = $data = array();
    $data['order_id'] = $order_info['order_id'];
    $data['products'] = array();
    $data['customer_phone'] = $order_info['telephone'];
    $data['do'] = 'pay';
    $data['api'] = 'opencart';
    $data['urlReturn']	= str_replace('&amp;', '&', $this->url->link('account/order/info', 'order_id='.$order_info['order_id'], true));
    $data['urlSuccess']	= str_replace('&amp;', '&', $this->url->link('extension/payment/prodamus/response', '', true));

    $total_products = 0;
    $total_shipping = 0;
    if ($is_account_order_info) {
      $order_products_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_info['order_id'] . "'");
			foreach ($order_products_query->rows as $order_product_row) {
				$data['products'][] = array(
					'name'     => $order_product_row['name'],
					'price'    => $this->currency->format($order_product_row['price'], $order_info['currency_code'], false, false),
					'quantity' => $order_product_row['quantity'],
        );
        $total_products += $order_product_row['price'] * $order_product_row['quantity'];
      }

      $order_total_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_info['order_id'] . "' ORDER BY sort_order ASC");
			foreach ($order_total_query->rows as $order_total_row) {
				if ('shipping' != $order_total_row['code'])
					continue;
				$data['products'][] = array(
					'name'     => $order_total_row['title'],
					'price'    => $this->currency->format($order_total_row['value'], $order_info['currency_code'], false, false),
					'quantity' => 1,
        );
        $total_shipping += $order_total_row['value'];
      }
    } else {
      foreach ($order_products as &$product) {
        $data['products'][] = array(
          'name'     => $product['name'],
          'price'    => $this->currency->format($product['price'], $order_info['currency_code'], false, false),
          'quantity' => $product['quantity'],
        );
        $total_products += $product['price'] * $product['quantity'];
      } unset($product);

      foreach ($order_totals as &$order_total) {
        if ('shipping' != $order_total['code'])
          continue;
        $data['products'][] = array(
          'name'     => $order_total['title'],
          'price'    => $this->currency->format($order_total['value'], $order_info['currency_code'], false, false),
          'quantity' => 1,
        );
        $total_shipping += $order_total['value'];
      } unset($order_total);
    }

    $totals['total_pay'] = $this->currency->format($total_products + $total_shipping, $order_info['currency_code'], false, false);
    $totals['total_order'] = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);
    $totals['total_products'] = $this->currency->format($total_products, $order_info['currency_code'], false, false);
    $totals['total_shipping'] = $this->currency->format($total_shipping, $order_info['currency_code'], false, false);

    if ($totals['total_pay'] != $totals['total_order']) {
      $data['discount_value'] = $totals['total_pay'] - $totals['total_order'];
    }

    $data['signature'] = $this->_createHmac($data);

    return array('data'=>$data,'totals'=>$totals);
  }

  private function _createHmac($data) {
    $data = (array) $data;
    array_walk_recursive($data, function(&$v){
      $v = strval($v);
    });
    $this->_sort($data);
    if (version_compare(PHP_VERSION, '5.4.0', '<')) {
      $data = preg_replace_callback('/((\\\u[01-9a-fA-F]{4})+)/', function ($matches) {
         return json_decode('"'.$matches[1].'"');
      }, json_encode($data));
    }
    else {
      $data = json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    return hash_hmac('sha256', $data, $this->config->get('payment_prodamus_secret_key'));
  }

  private function _verifyHmac($data, $sign) {
    $_sign = $this->_createHmac($data);
    return ($_sign && (strtolower($_sign) == strtolower($sign)));
  }

  private function _sort(&$data) {
    ksort($data, SORT_REGULAR);
    foreach ($data as &$arr)
      is_array($arr) && $this->_sort($arr);
  }
}