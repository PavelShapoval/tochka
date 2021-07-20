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

class MyControllerUpdateByCron extends Controller
{
	public function index() {
		$this->load->model('setting/setting');
	}

	public function parse_temp_products(){
		$this->load->model('setting/setting');
		$query = $this->db->query("SELECT `upc` , `quantity`, `price` FROM ".DB_PREFIX."temp_products WHERE `updated` = '' LIMIT 500 ");
		return $query->rows;
	}

	public function update_products($upc, $quantity, $price){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `quantity` = $quantity, `price` = $price WHERE `upc` = $upc";
		$this->db->query($sql);
	}

	public function mark_updated($upc){
		$sql  = "UPDATE `".DB_PREFIX."temp_products` SET `updated` = 'updated' WHERE `upc` = $upc";
		$this->db->query($sql);
	}

	public function clean_table(){
		$sql  = "DELETE FROM `".DB_PREFIX."temp_products`";
		$this->db->query($sql);
	}


	public function getPercentUp($upc) {
		$sql = "SELECT percent_up FROM " . DB_PREFIX . "product WHERE `upc` = $upc ";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getPercentDown($ups) {
		$sql = "SELECT percent_down FROM " . DB_PREFIX . "product WHERE `upc` = $ups ";
		$query = $this->db->query($sql);
		return $query->row;
	}
}

$update_pricat_obj = new MyControllerUpdateByCron($registry);
$products_data = $update_pricat_obj->parse_temp_products();
if(!empty($products_data)){
	foreach ($products_data as $product_data){
		$percent_up = $update_pricat_obj->getPercentUp($product_data["upc"]);
		$percent_down = $update_pricat_obj->getPercentDown($product_data["upc"]);
		if($percent_up['percent_up'] != 0){
			$final_price = $product_data["price"] + ($product_data["price"] * $percent_up['percent_up']);
		} elseif($percent_down['percent_down'] != 0){
			$final_price = $product_data["price"] - ($product_data["price"] * $percent_down['percent_down']);
		} else {
			$final_price = $product_data["price"];
		}

		$update_pricat_obj->update_products($product_data["upc"], $product_data["quantity"], $final_price);
		$update_pricat_obj->mark_updated($product_data["upc"]);
	}
} else {
	echo 'all products were updated';
	$update_pricat_obj->clean_table();
	exit;
	//тут можно почистить временную табличку
}

