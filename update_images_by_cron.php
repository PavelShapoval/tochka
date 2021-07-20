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

class MyControllerUpdateImagesByCron extends Controller
{
	public function index() {
		$this->load->model('setting/setting');
	}

	public function parse_temp_images(){
		$this->load->model('setting/setting');
		$query = $this->db->query("SELECT `upc` , `img` FROM ".DB_PREFIX."temp_images WHERE `updated` = '' LIMIT 1000 ");
		return $query->rows;
	}

	public function check_image($product_id){
		$query = $this->db->query("SELECT `product_image_id` FROM ".DB_PREFIX."product_image WHERE `product_id` = '". $product_id ."'");
		return $query->row;
	}


	public function get_product_id($upc) {
		$query = $this->db->query("SELECT `product_id` FROM ".DB_PREFIX."product WHERE `upc` = '". $upc ."'");
		return $query->row;
	}

	public function update_images($product_id, $image){
		$sql  = "INSERT INTO ".DB_PREFIX."product_image SET `product_id` = $product_id, `image` = $image, `sort_order` = 0";
		$this->db->query($sql);
	}

	public function mark_updated($upc){
		$sql  = "UPDATE `".DB_PREFIX."temp_images` SET `updated` = 'updated' WHERE `upc` = $upc";
		$this->db->query($sql);
	}
}

$update_images_obj = new MyControllerUpdateImagesByCron($registry);
$images_data = $update_images_obj->parse_temp_images();
if(!empty($images_data)){
	foreach ($images_data as $image_data){
		$check_image = $update_images_obj->check_image($image_data["upc"]);
		$product_id = $update_images_obj->get_product_id($image_data["upc"]);

		$update_images_obj->mark_updated($image_data["upc"]);

		if(empty($check_image) && !empty($product_id)){
			$update_images_obj->update_images($product_id["product_id"], $image_data["img"]);
		} else {
			continue;
		}



	}
} else {
	echo 'all products were updated';
	exit;
	//тут можно почистить временную табличку
}

