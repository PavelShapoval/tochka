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

class MyControllerUpdateByManufacturer extends Controller
{
	public function index() {
		$this->load->model('setting/setting');
	}


	public function get_data_from_table_prices_updates(){
		$this->load->model('setting/setting');
		$query = $this->db->query("SELECT DISTINCT `manufacturer_id`, `percent` FROM oc_prices_updates ORDER BY `manufacturer_id` DESC");
		return $query->rows;
	}

	/*public function select_products_by_manufacturer($manufacturer_id){
		$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE manufacturer_id = ".$manufacturer_id);
		return $query->rows;
	}

	public function count_products_by_manufacturer($manufacturer_id){
		$query = $this->db->query("SELECT COUNT(product_id) FROM " . DB_PREFIX . "product WHERE manufacturer_id = ".$manufacturer_id);
		return $query->rows;
	}

	public function update_product_prices_up($product_id, $percent){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `price` = `price` + `price` * ".$percent." WHERE `product_id` = $product_id";
		$this->db->query($sql);
	}

	public function update_product_prices_down($product_id, $percent){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `price` = `price` - `price` * ".$percent." WHERE `product_id` = $product_id";
		$this->db->query($sql);
	}

	public function write_updates_up($manufacturer_id, $percent){
		$sql  = "REPLACE `".DB_PREFIX."prices_updates` SET `manufacturer_id`=".$manufacturer_id.", `percent` = ".$percent."";
		$this->db->query($sql);

	}

	public function write_updates_down($manufacturer_id, $percent){
		$sql  = "REPLACE `".DB_PREFIX."prices_updates` SET `manufacturer_id`=".$manufacturer_id.", `percent` = ".$percent."";
		$this->db->query($sql);
	}

	public function disable_updates_clean_table(){
		$sql  = "DELETE FROM `".DB_PREFIX."prices_updates`";
		$this->db->query($sql);
	}*/
}

$update_manuf_obj = new MyControllerUpdateByManufacturer($registry);

$percents_data = $update_manuf_obj->get_data_from_table_prices_updates();
echo '<pre>';
var_dump($percents_data);
echo '</pre>';
die('123');




if(!empty($products_data)){
	foreach ($products_data as $product_data){
		$update_pricat_obj->update_products($product_data["upc"], $product_data["quantity"], $product_data["price"]);
		$update_pricat_obj->mark_updated($product_data["upc"]);
	}
} else {
	echo 'all products were updated';
	exit;
	//тут можно почистить временную табличку
}

