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

class MyControllerImportProdat extends Controller
{

	public function index() {
		$this->load->model('setting/setting');
	}


	public function import_prodat($start, $finish){
		//$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/PRODAT_TEST.xml';
		set_time_limit(1200);
		ini_set("memory_limit", "2056M");
		//$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/PRODAT_369147_600656224.xml';
		$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/PRODAT_369147_657930212/PRODAT_369147_657930212.xml';



		$z = new XMLReader;
		$z->open($xmlfile);
		$doc = new DOMDocument;
		$count = 0;
	// move to the first <product /> node
		while ($z->read() && $z->name !== 'DocDetail');

	// now that we're at the right depth, hop to the next <product/> until the end of the tree
		$id = 1;
		while ($z->name === 'DocDetail')
		{
			//$node = new SimpleXMLElement($z->readOuterXML());
			$node = simplexml_import_dom($doc->importNode($z->expand(), true));

			//echo $node->ProductName.'<br>';
			$SenderPrdCode = $node->SenderPrdCode; //артикуль, по нему связаны pricat и prodat
			if($id >= $start && $id <= $finish) {
				//echo $node->Image->Value.'<br>';
				//$this->db->query("INSERT INTO " . DB_PREFIX . "temp_images SET id = '" .$id. "', upc = '" . $SenderPrdCode . "', img = '" . $node->Image->Value . "'");


				//echo '<img src='.$node->Image->Value.' alt=""/>';
				foreach ( $node->FeatureETIMDetails->FeatureETIM as $key => $object ) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "temp_attributes SET id = '" .$id. "', upc = '" . $SenderPrdCode . "', attr_code = '" . $object->FeatureCode . "', attr_name = '" . $object->FeatureName .  "', attr_value = '" . $object->FeatureValue . "', attr_uom = '" . $object->FeatureUom . "'");
					//SELECT COUNT(DISTINCT upc) FROM oc_temp_attributes
					//echo $object->FeatureCode.'<br>';
					//echo $object->FeatureName.'<br>';
					//echo $object->FeatureValue.'<br>';
					//echo $object->FeatureUom . '<br>';

				}
			}

			//$clean_name = str_replace(array('\'', '"'), '', $node->ProductName);
			//$language_id = 1;
			//$model = str_replace(array('\'', '"'), '', $node->ProductName);
			//$image = $node->Image->Value;
			//$quantity = 1;
			//$price = 1;
			/*if($node->VendorProdNum){
				$UPC = $node->VendorProdNum;
			}*/

			//VendorProdNum
			/*foreach ($node->Weight as $key => $object){
				$weight = $object->Value;
			}*/
			//$status = 1;




			//$this->model_extension_module_xml_module->add_product_desc($id , $language_id, $clean_name);
			//$this->model_extension_module_xml_module->add_product_ids($id, $model, $UPC, $quantity, $image, $price, $weight, $status);
			//$this->model_extension_module_xml_module->add_product_img($product_image_id, $SenderPrdCode, $image);
			//$this->model_extension_module_xml_module->add_product_sku($id, $SenderPrdCode);
			//$this->model_extension_module_xml_module->add_product_to_cat_test($id, 59);
			//$this->model_extension_module_xml_module->add_product_to_store($id, 0);
			//$this->model_extension_module_xml_module->add_product_upc($id, 0);
			//$id++;
			//$product_image_id++;





			/*echo $node->EAN.'<br>';
			echo $node->SenderPrdCode.'<br>';
			echo $node->ProductName.'<br>';
			echo $node->ProductStatus.'<br>';
			echo $node->UOM.'<br>';
			echo $node->ItemsPerUnit.'<br>';
			echo $node->Multiplicity.'<br>';
			echo $node->ParentProdCode.'<br>';
			echo $node->ParentProdGroup.'<br>';
			echo $node->ProductCode.'<br>';
			echo $node->ProductGroup.'<br>';
			echo $node->VendorProdNum.'<br>';
			echo $node->Brand.'<br>';
			foreach ($node->Weight as $key => $object){
				echo $object->WeightUnit.'<br>';
				echo $object->Value.'<br>';
			}
			foreach ($node->Dimension as $key => $object){
				echo $object->DimensionUnit.'<br>';
				echo $object->Depth.'<br>';
				echo $object->Width.'<br>';
				echo $object->Height.'<br>';
			}
			foreach ($node->FeatureETIMDetails->FeatureETIM as $key => $object){

				echo $object->FeatureCode.'<br>';
				echo $object->FeatureName.'<br>';
				echo $object->FeatureValue.'<br>';
				echo $object->FeatureUom.'<br>';

			}*/
			//echo $node->Image->Value.'<br>';
			//echo '<img src='.$node->Image->Value.' alt=""/>';
			//echo $node->Image->Value;
			//echo $node->CertificateNum.'<br>';
			//die('123');
			/*echo '<pre>';
			var_dump($node->DocDetailOptions->DocOption);
			echo '</pre>';*/


			/*echo '<pre>';
			var_dump($node);
			echo '</pre>';*/
			//die('123');
			//$count++;
			//echo '<br>'.$count;
			//echo '<hr>';

			// go to next <product />
			$id ++;
			$z->next('DocDetail');
		}

	}

	public function check_finish(){
		$query = $this->db->query("SELECT max(id) FROM " . DB_PREFIX . "temp_attributes");
		return $query->row;
	}
}

$import_prodat_obj = new MyControllerImportProdat($registry); // $registry = new Registry();
$finish_loop = $import_prodat_obj->check_finish();

if($finish_loop == NULL){
	$finish_loop = 0;
}

$start = $finish_loop["max(id)"]+1;
$finish = $start+199;

$import_prodat_obj->import_prodat($start, $finish);

//%progdir%\modules\wget\bin\wget.exe -q --no-cache http://tochka.loc/import_prodat.php
//*/5

//DELETE FROM oc_temp_attributes WHERE id > 9000