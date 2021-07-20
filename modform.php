<?php

require_once('config.php');
require_once(DIR_SYSTEM . 'startup.php');

// Registry
$registry = new Registry();

// Config
$config = new Config();
$config->load('catalog');
$registry->set('config', $config);

// Log
$log = new Log('custom_log');
$registry->set('log', $log);

date_default_timezone_set('UTC');

// Event
$event = new Event($registry);
$registry->set('event', $event);

// Event Register
if ($config->has('action_event')) {
	foreach ($config->get('action_event') as $key => $value) {
		foreach ($value as $priority => $action) {
			$event->register($key, new Action($action), $priority);
		}
	}
}

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Database
$registry->set('db', new DB(
		$config->get('db_engine'),
		$config->get('db_hostname'),
		$config->get('db_username'),
		$config->get('db_password'),
		$config->get('db_database'),
		$config->get('db_port'))
);

// данные сеты необходимы для использования стандартных методов моделей
$config->set('config_customer_group_id', 1);
$config->set('config_language_id', 1);
$config->set('config_store_id', 0);





class MyControllerForm extends Controller{
    private $secret;
    private $public;
    private function getPublic(){
        // получем настройки модуля (данные) из базы
        //$this->public = $this->config->get('modform_public');
        $this->public = '6LdPDxYbAAAAAFEq5lpQuvDXZ_90KuNW6GAgcm6R';
        return $this->public;
    }
    private function getSecret(){
        // получем настройки модуля (данные) из базы
        //$this->secret = $this->config->get('modform_secret');
        $this->secret = '6LdPDxYbAAAAAJFp_xPNBjaM6fT7UJMCgO5ASw_P';
        return $this->secret;
    }


    public function sendmail(){
    	foreach($_POST as $field => $value){
            if($field != 'g-recaptcha-response'){
                $values[] = trim(strip_tags($value));
                $fields[] = $field;
            }
        }

    	$headers = "MIME-Version: 1.0" . "\r\n" .
	               "Content-type: text/html; charset=UTF-8" . "\r\n";

    	mail('energoteh17@yandex.ru',
			    'Заказать звонок',
			    'Заполнена форма "Заказать звонок"'.PHP_EOL.'<br> от клиента: '.$values['0'].PHP_EOL.'<br> контактные данные: '.$values['1'].PHP_EOL.'<br>',
			    $headers);

    }
}
$form = new MyControllerForm($registry);
require_once $_SERVER["DOCUMENT_ROOT"].'/recaptchalib.php';
$reCaptcha = new ReCaptcha('6LdPDxYbAAAAAJFp_xPNBjaM6fT7UJMCgO5ASw_P');

if ($_POST["g-recaptcha-response"]) {
	$response = $reCaptcha->verifyResponse(
		$_SERVER["REMOTE_ADDR"],
		$_POST["g-recaptcha-response"]
	);
}

if ($response != null && $response->success){
	if($_POST['form-name'] != '' || $_POST['form-tel'] != ''){
		$form->sendmail();
		echo "success"; //anything on success
	} else {
		die(header("HTTP/1.0 404 Not Found")); //Throw an error on failure
	}
} else {
	die(header("HTTP/1.0 404 Not Found")); //Throw an error on failure
}





