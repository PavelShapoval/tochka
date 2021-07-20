<?php
$_['heading_title']	 = 'Prodamus.Payment';

// Text
$_['text_title'] = 'Prodamus.Payform';
$_['text_button_confirm'] = 'Pay %s';

// Error
$_['response_error_emptydata']    = 'The payment information was not sent in the request.';
$_['response_error_emptysign']    = 'The signature has not been sent in the request.';
$_['response_error_emptyorder']   = 'The order ID has not been sent in the request.';
$_['response_error_emptystatus']  = 'The payment status has not been sent in the request.';
$_['response_error_verify']       = 'The signature does not match the received data.';
$_['response_error_status']       = 'Unknown payment status was submitted';
$_['response_error_order']        = 'Order not found';

$_['response_text_customer']        = '<p>Payment successful!</p><p>You can view your order history by going to the <a href="%s">my account</a> page and by clicking on <a href="%s">history</a>.</p><p>Please direct any questions you have to the <a href="%s">store owner</a>.</p><p>Thanks for shopping with us online!</p>';
$_['response_text_guest']           = '<p>Payment successful!</p><p>Please direct any questions you have to the <a href="%s">store owner</a>.</p><p>Thanks for shopping with us online!</p>';
$_['response_text_status_success']  = 'Prodamus.Payment - Payment successful. Payment operation code: %s';