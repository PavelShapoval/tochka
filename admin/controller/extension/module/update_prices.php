<?php

class ControllerExtensionModuleUpdatePrices extends Controller {
	private $error = array();
	private $updated_cnt = 0;
	private $inserted_cnt = 0;
	private $language_id = 1; //current language


	public function index() {

		$this->load->language('extension/module/update_prices');

		$this->load->model('setting/setting');

		$this->load->model('extension/module/update_prices');

		//кастом
		//$this->document->addScript('/admin/view/javascript/ajax.js');
		//кастом
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_update_prices', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/update_prices', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/update_prices', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_update_prices_status'])) {
			$data['module_update_prices_status'] = $this->request->post['update_prices_module_status'];
		} else {
			$data['module_update_prices_status'] = $this->config->get('update_prices_module_status');
		}


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		//$data['update_prices_link'] = 'https://b2b-sandi.com.ua/export_xml/4fb01c5bf3957d849de460be7eb84821';

		//$data['import_xml'] = (HTTPS_SERVER . 'index.php?route=extension/module/xml_module/export_xml&token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		//$data['export_xml'] = (HTTPS_SERVER . 'controller/update_prices/export.php');
		$data['all_manufacturers'] = $this->show_all_manufacturers();

		$table_data = $this->check_table();
		$table_rows = [];
		$k = 0;
		foreach($table_data as $item){
			$manufacturer_name = $this->model_extension_module_update_prices->get_manufacturer_name($item["manufacturer_id"]);
			$table_rows[$k] = [
				'name' => $manufacturer_name['name'],
				'percent' => $item['percent'],
				'side' => $item['side']
			];
			$k++;
		}
		$data['table_data'] = $table_rows;

		$this->response->setOutput($this->load->view('extension/module/update_prices', $data));

		//
		//$this->show_all_manufacturers();


	}

	public function show_all_manufacturers(){
		$this->load->model('extension/module/update_prices');
		$all_manufacturers = $this->model_extension_module_update_prices->get_all_manufacturers();
		return $all_manufacturers;

		/*foreach ($all_manufacturers as $manufacturer){
			echo '<pre>';
			var_dump($manufacturer['name'].'<br>'.$manufacturer['manufacturer_id']);
			echo '</pre>';
		}*/
	}

/*	function add_percent($price, $percent) {
		return $value + ($price * $percent / 100);
	}*/

	public function update_prices_up(){
		$this->load->model('extension/module/update_prices');
		$product_ids = $this->model_extension_module_update_prices->select_products_by_manufacturer($_POST['manufacturer']);
		//$test2 = $this->model_extension_module_update_prices->count_products_by_manufacturer($_POST['manufacturer']);
		//$product_id["product_id"] = 50;

		if($_POST["percent"] == ""){
			exit('не введен процент');
		} else {
			echo 'Обновление цен прошло успешно.';
		}
		$persent = $_POST["percent"] / 100;
		foreach($product_ids as $product_id) {
			$this->model_extension_module_update_prices->update_product_prices_up($product_id["product_id"], $persent);
			$this->model_extension_module_update_prices->write_percent_up($product_id["product_id"], $persent);
		}

		$manufacturer_id = $_POST['manufacturer'];
		$this->model_extension_module_update_prices->clean_before_write($manufacturer_id);
		$this->model_extension_module_update_prices->write_updates_up($manufacturer_id, $persent);


	}


	public function update_prices_down(){
		$this->load->model('extension/module/update_prices');
		$product_ids = $this->model_extension_module_update_prices->select_products_by_manufacturer($_POST['manufacturer']);
		//$test2 = $this->model_extension_module_update_prices->count_products_by_manufacturer($_POST['manufacturer']);
		//$product_id["product_id"] = 50;
		if($_POST["percent"] == ""){
			exit('не введен процент');
		} else {
			echo 'Обновление цен прошло успешно.';
		}

		$persent = $_POST["percent"] / 100;
		foreach($product_ids as $product_id) {
			$this->model_extension_module_update_prices->update_product_prices_down($product_id["product_id"], $persent);
			$this->model_extension_module_update_prices->write_percent_down($product_id["product_id"], $persent);
		}

		$manufacturer_id = $_POST['manufacturer'];
		$this->model_extension_module_update_prices->clean_before_write($manufacturer_id);
		$this->model_extension_module_update_prices->write_updates_down($manufacturer_id, $persent);
	}


	public function disable_updates(){
		$this->load->model('extension/module/update_prices');
		$this->model_extension_module_update_prices->disable_updates_clean_table();
		$this->model_extension_module_update_prices->disable_persents();
	}

	public function check_table(){
		$this->load->model('extension/module/update_prices');
		$data = $this->model_extension_module_update_prices->check_percents_table();
		return $data;
	}


	public function file_upload(){
		set_time_limit(300);

		$ftp_server = 'service.russvet.ru';
		$ftp_user_name = "pricat";
		$ftp_user_pass = "vFtg23x";

		$conn_id =ftp_connect($ftp_server,21021,90) or die ('connect error');
// проверка имени пользователя и пароля
		$login_result = ftp_login($conn_id,$ftp_user_name,$ftp_user_pass);
		ftp_pasv($conn_id, true);
		if ((!$conn_id) || (!$login_result)) {
			echo "error FTP connect";
			echo "Try to FTP connect $ftp_server name $ftp_user_name!";
			exit;
		} else {
			echo "connection FTP  $ftp_server name $ftp_user_name";
		}
// получить содержимое текущей директории
		$contents = ftp_nlist($conn_id, "/siberia");

		$local_file = $_SERVER['DOCUMENT_ROOT'].'/PRICAT_DOWNLOAD.xml';
		$server_file = array_pop($contents);
		if (ftp_get($conn_id, $local_file, $server_file, FTP_BINARY)) {
			echo "write $local_file\n";
		} else {
			echo "error write\n";
		}
// закрытие соединения
		ftp_close($conn_id);
	}

	public function check_status_now(){
		$this->load->model('extension/module/update_prices');
		header('Content-Type', 'application/json');
		$rows = $this->model_extension_module_update_prices->check_status();
		if(empty($rows)){
			echo '<span style="color: green">Текущих загрузок нет, можно загружать файл</span>';
		} else {
			echo '<span style="color: red">данные загружаются в базу</span>';
		}
	}

	public function data_upload(){
		$this->load->model('extension/module/update_prices');
		set_time_limit(1200);
		ini_set("memory_limit", "2056M");

		$this->load->model('setting/setting');
		//$directory = $_SERVER["DOCUMENT_ROOT"].'/IMPORT_PRICAT';
		//$file = array_pop(scandir($directory));
		//$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/IMPORT_PRICAT/'.$file;
		$file = 'PRICAT_DOWNLOAD.xml';
		$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/'.$file;

		$z = new XMLReader;
		$z->open($xmlfile);
		$doc = new DOMDocument;

		$exist_rows = $this->model_extension_module_update_prices->check_exist_rows();
		$data_rows = $this->model_extension_module_update_prices->check_temp_table();


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




	public function import_manufacturer(){
		$this->load->language('extension/module/update_prices');
		$this->load->model('extension/module/update_prices');



	}



	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/update_prices')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
