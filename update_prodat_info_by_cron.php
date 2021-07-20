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

	public function parse_temp_attributes(){
		$this->load->model('setting/setting');
		$query = $this->db->query("SELECT `upc` , `attr_code`, `attr_name`, `attr_value`, `attr_uom` FROM ".DB_PREFIX."temp_attributes WHERE `updated` = ''  LIMIT 250");
		return $query->rows;
	}

	public function get_attr_id($name) {
		$query = $this->db->query("SELECT `attribute_id` FROM ".DB_PREFIX."attribute_description WHERE `name` = '". $name ."'");
		return $query->row;
	}

	public function get_product_id($upc) {
		$query = $this->db->query("SELECT `product_id` FROM ".DB_PREFIX."product WHERE `upc` = '". $upc ."'");
		return $query->row;
	}

	public function update_attributes($upc, $attr_id, $text){
		$sql  = "REPLACE ".DB_PREFIX."product_attribute SET `product_id` = $upc, `attribute_id` = $attr_id, `language_id` = 1, `text` = \"$text\"";
		/*print_r($sql);
		die('123');*/
		$this->db->query($sql);
	}

	public function mark_updated($upc){
		$sql  = "UPDATE `".DB_PREFIX."temp_attributes` SET `updated` = 'updated' WHERE `upc` = $upc";
		$this->db->query($sql);
	}
}

$update_prodat_obj = new MyControllerUpdateByCron($registry);
$products_data = $update_prodat_obj->parse_temp_attributes();
if(!empty($products_data)){
	foreach ($products_data as $product_data){
		/*echo '<pre>';
		var_dump(
			$product_data["upc"],
			$product_data["attr_name"],
			$product_data["attr_value"],
			$product_data["attr_uom"]
		);
		echo '</pre>';*/


		$attr_id = $update_prodat_obj->get_attr_id($product_data["attr_name"]);
		$product_id = $update_prodat_obj->get_product_id($product_data["upc"]);

		/*echo '<pre>';
		var_dump($product_data, !empty($product_id), $attr_id);
		echo '</pre>';*/
		//die('123');

		$update_prodat_obj->mark_updated($product_data["upc"]);

		if(!empty($product_id)){
			//continue;
			//die('1234');
			if($product_id && $attr_id && $product_data["attr_value"]){
				//die('1234');
				$update_prodat_obj->update_attributes($product_id["product_id"], (int)$attr_id["attribute_id"], $product_data["attr_value"]);
			}
		} //else {
			//die('123');
			/*if($product_id && $attr_id && $product_data["attr_value"]){
				//die('1234');
				$update_prodat_obj->update_attributes($product_id["product_id"], (int)$attr_id["attribute_id"], $product_data["attr_value"]);
			}*/
		//}

		//$update_prodat_obj->update_products($product_data["upc"], $product_data["quantity"], $product_data["price"]);

	}
} else {
	echo 'all products were updated';
	exit;
	//тут можно почистить временную табличку
}

// %progdir%\modules\wget\bin\wget.exe -q --no-cache http://tochka.loc/update_prodat_info_by_cron.php

