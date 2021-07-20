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




class MyControllerImportByCron extends Controller
{
	public function index() {
		$this->load->model('setting/setting');
	}

	//public function import_pricat($start, $finish){
	public function import_pricat(){
		set_time_limit(1200);
		ini_set("memory_limit", "2056M");

		$this->load->model('setting/setting');
		//$directory = $_SERVER["DOCUMENT_ROOT"].'/IMPORT_PRICAT';
		//$file = array_pop(scandir($directory));
		//$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/IMPORT_PRICAT/'.$file;
		$file = 'PRICAT_DOWNLOAD.xml';
		//$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/'.$file;
		$xmlfile = $file;


		$z = new XMLReader;
		$z->open($xmlfile);
		$doc = new DOMDocument;

		$exist_rows = $this->check_exist_rows();
		$data_rows = $this->check_temp_table();


		if(!empty($data_rows) && $exist_rows["count(id)"] != 0){
			return;
		}

		while ($z->read() && $z->name !== 'DocDetail');
		$id = 1;
		while ($z->name === 'DocDetail')
		{

			$node = simplexml_import_dom($doc->importNode($z->expand(), true));
			$quantity = $node->QTY;
			$SenderPrdCode = $node->SenderPrdCode; //артикуль, по нему связаны pricat и prodat

			$DocDetailOptions = $node->DocDetailOptions;

			//if($id >= $start && $id <= $finish){
				/*цена базовая*/
				foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
					$Name = (string)$element->Name[0];
					$Value = (string)$element->Value[0];
					if($Name == "RetailPrice"){
						$RetailPrice = (float)$Value;
						$this->db->query("INSERT INTO " . DB_PREFIX . "temp_products SET id = '" .$id. "', upc = '" . $SenderPrdCode . "', quantity = '" . $quantity . "', price = '" . $RetailPrice .  "'");
					} else {
						continue;
					}
				}
			//}
			$id ++;
			$z->next('DocDetail');
		}

		return true;

	}

	public function check_finish(){
		$query = $this->db->query("SELECT max(id) FROM " . DB_PREFIX . "temp_products");
		return $query->row;
	}

	public function check_dublicates(){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "temp_products
		 WHERE `upc` IN (SELECT `upc` FROM ". DB_PREFIX ."temp_products GROUP BY `upc` HAVING COUNT(*) > 1)
		 ORDER BY `upc`");
		return $query->rows;
	}

	public function check_temp_table(){
		$query = $this->db->query("SELECT `upc` , `quantity`, `price` FROM ".DB_PREFIX."temp_products WHERE `updated` != '' LIMIT 500 ");
		return $query->rows;
	}

	public function check_exist_rows(){
		$query = $this->db->query("SELECT count(id) FROM ".DB_PREFIX."temp_products");
		return $query->row;
	}

}
$import_pricat_obj = new MyControllerImportByCron($registry); // $registry = new Registry();

$finish_loop = $import_pricat_obj->check_finish();

if($finish_loop == NULL){
	$finish_loop = 0;
}

$start = $finish_loop["max(id)"]+1;
$finish = $start+999;

//$import_pricat_obj->import_pricat($start, $finish);
$import_pricat_obj->import_pricat();






