<?php

class ControllerExtensionModuleXmlModule extends Controller {
	private $error = array();
	private $updated_cnt = 0;
	private $inserted_cnt = 0;
	private $language_id = 1; //current language


	public function index() {

		$this->load->language('extension/module/xml_module');
		$this->load->model('setting/setting');

		//кастом
		//$this->document->addScript('/admin/view/javascript/ajax.js');
		//кастом
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_xml_module', $this->request->post);
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
			'href' => $this->url->link('extension/module/xml_module', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/xml_module', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_xml_module_status'])) {
			$data['module_xml_module_status'] = $this->request->post['module_xml_module_status'];
		} else {
			$data['module_xml_module_status'] = $this->config->get('module_xml_module_status');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['xml_import_link'] = 'https://b2b-sandi.com.ua/export_xml/4fb01c5bf3957d849de460be7eb84821';

		$data['import_xml'] = (HTTPS_SERVER . 'index.php?route=extension/module/xml_module/export_xml&token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		$data['export_xml'] = (HTTPS_SERVER . 'controller/export_xml/export.php');

		$this->response->setOutput($this->load->view('extension/module/xml_module', $data));
	}

	/**
	* Importing goods from XML url and save them do DB
	*/
	public function import_xml(){

		$this->load->language('extension/module/xml_module');

		$this->load->model('extension/module/xml_module');

		$this->load->model('catalog/attribute_group');

		$this->load->model('catalog/attribute');

		$this->load->model('catalog/manufacturer');

		$this->load->model('catalog/product');

		$json = array();
		$xml_url = "";

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'extension/module/xml_module')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if ($_POST["xml_url"]){
			$xml_url = $_POST["xml_url"];
			$xml = simplexml_load_file($xml_url) or die("feed not loading");
			$this->parseCategories($xml);
			$this->parseProducts($xml);
		}

		if (!$json) {
			$json['success'] = $this->language->get('text_import_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

		$this->session->data['success'] = $this->language->get('text_success');
	}

	public function import_prodat(){
		//$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/PRODAT_TEST.xml';
		set_time_limit(1200);
		ini_set("memory_limit", "2056M");
		$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/PRODAT_369147_600656224/PRODAT_369147_600656224.xml';

		$this->load->language('extension/module/xml_module');

		$this->load->model('extension/module/xml_module');

		$z = new XMLReader;
		$z->open($xmlfile);
		$doc = new DOMDocument;
		$count = 0;
// move to the first <product /> node
		while ($z->read() && $z->name !== 'DocDetail');

// now that we're at the right depth, hop to the next <product/> until the end of the tree
		$id = 50;
		$product_image_id = 2352;
		while ($z->name === 'DocDetail')
		{
			//$node = new SimpleXMLElement($z->readOuterXML());
			$node = simplexml_import_dom($doc->importNode($z->expand(), true));

			$SenderPrdCode = $node->SenderPrdCode; //артикуль, по нему связаны pricat и prodat
			$clean_name = str_replace(array('\'', '"'), '', $node->ProductName);
			$language_id = 1;
			$model = str_replace(array('\'', '"'), '', $node->ProductName);
			$image = $node->Image->Value;
			$quantity = 1;
			$price = 1;
			if($node->VendorProdNum){
				$UPC = $node->VendorProdNum;
			}

			//VendorProdNum
			foreach ($node->Weight as $key => $object){
				$weight = $object->Value;
			}
			$status = 1;




			//$this->model_extension_module_xml_module->add_product_desc($id , $language_id, $clean_name);
			//$this->model_extension_module_xml_module->add_product_ids($id, $model, $UPC, $quantity, $image, $price, $weight, $status);
			//$this->model_extension_module_xml_module->add_product_img($product_image_id, $SenderPrdCode, $image);
			$this->model_extension_module_xml_module->add_product_sku($id, $SenderPrdCode);
			//$this->model_extension_module_xml_module->add_product_to_cat_test($id, 59);
			//$this->model_extension_module_xml_module->add_product_to_store($id, 0);
			//$this->model_extension_module_xml_module->add_product_upc($id, 0);
			$id++;
			$product_image_id++;





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
			$z->next('DocDetail');
		}



	}
	public function import_pricat(){
		set_time_limit(1200);
		ini_set("memory_limit", "2056M");
		//$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/PRICAT_TEST.xml';
		//$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/pricat_test2.xml';
		$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/PRICAT_335537_602955929.xml';

		$this->load->language('extension/module/xml_module');

		$this->load->model('extension/module/xml_module');

		$z = new XMLReader;
		$z->open($xmlfile);
		$doc = new DOMDocument;
		$count = 0;
// move to the first <product /> node
		while ($z->read() && $z->name !== 'DocDetail');

// now that we're at the right depth, hop to the next <product/> until the end of the tree
		$id = 50;
		$product_image_id = 2352;
		$groups = [];
		while ($z->name === 'DocDetail')
		{
			//$node = new SimpleXMLElement($z->readOuterXML());
			$node = simplexml_import_dom($doc->importNode($z->expand(), true));




			$clean_name = str_replace(array('\'', '"'), '', $node->ProductName);
			$language_id = 1;
			$model = str_replace(array('\'', '"'), '', $node->ProductName);
			//$image = $node->Image->Value;

			$quantity = $node->QTY;
			$UOM = $node->UOM;
			$EAN = $node->EAN;
			$SenderPrdCode = $node->SenderPrdCode; //артикуль, по нему связаны pricat и prodat
			$ReceiverPrdCode = $node->ReceiverPrdCode;
			$ItemsPerUnit = $node->ItemsPerUnit;
			$DocDetailOptions = $node->DocDetailOptions;
			$status = 1;

			//$this->model_extension_module_xml_module->add_product_desc($id, $language_id, $clean_name); //работатет но когда отключено добавление цен
			//$this->model_extension_module_xml_module->add_product_items($id, $model, $SenderPrdCode, $quantity, $status); //работает но когда отключено добавление цен
			//$this->model_extension_module_xml_module->add_product_img($product_image_id, $SenderPrdCode, $image);
			//$this->model_extension_module_xml_module->add_product_to_cat_test($id, 59); //работает но когда отключено добавление цен
			//$this->model_extension_module_xml_module->add_product_to_store($id, 0);
			//$this->model_extension_module_xml_module->add_product_sku($id, $SenderPrdCode);

			/*группа*/
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "ProductGroup"){
					$ProductGroup = str_replace(array('\'', '"'), '', $Value);
					//$ProductGroup = $Value;
					$category_id = $this->model_extension_module_xml_module->get_category_id($ProductGroup);

					if($category_id != ''){
						//$this->model_extension_module_xml_module->set_product_to_category($id, $category_id);
					} else {
						continue;
					}

					if(!in_array($ProductGroup, $groups)){
						$groups[] = $ProductGroup;
					};

					//die('123');
					//$this->model_extension_module_xml_module->parse_categories($ProductGroup);
					//$this->model_extension_module_xml_module->add_categories($ProductGroup);
					//$ProductGroup = str_replace(array('\'', '"'), '', $ProductGroup);
					//$this->model_extension_module_xml_module->add_product_to_categories($id, $ProductGroup); //не доделал
				} else {
						continue;
				}
				/*echo '<pre>';
				var_dump($ProductGroup);
				echo '</pre>';*/

			}
			/*бренд*/

			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "Brand"){
					$Brand = $Value;
					//$this->model_extension_module_xml_module->add_manufacturers($Brand);


				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($Brand);
				echo '</pre>';*/
			}

			/*глубина*/
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "Depth"){
					$Depth = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($Depth);
				echo '</pre>';*/
			}
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "DepthUnit"){
					$DepthUnit = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($DepthUnit);
				echo '</pre>';*/
			}
			/*ширина*/
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "Width"){
					$Width = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($Width);
				echo '</pre>';*/
			}
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "WidthUnit"){
					$WidthUnit = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($WidthUnit);
				echo '</pre>';*/
			}
			/*высота*/
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "Height"){
					$Height = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($Height);
				echo '</pre>';*/
			}
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "HeightUnit"){
					$HeightUnit = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($HeightUnit);
				echo '</pre>';*/
			}
			/*объем*/
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "Volume"){
					$Volume = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($Volume);
				echo '</pre>';*/
			}
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "VolumeUnit"){
					$VolumeUnit = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($VolumeUnit);
				echo '</pre>';*/
			}
			/*валюта*/
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "Currency"){
					$Currency = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($Currency);
				echo '</pre>';*/
			}
			/*вес*/
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "Weight"){
					$Weight = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($Weight);
				echo '</pre>';*/
			}
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "WeightUnit"){
					$WeightUnit = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($WeightUnit);
				echo '</pre>';*/
			}
			/*цена базовая*/
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "RetailPrice"){
					$RetailPrice = (float)$Value;
					//$this->model_extension_module_xml_module->add_product_prices($id, $RetailPrice); //функция работает одна, когда товары не добавляешь
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($RetailPrice);
				echo '</pre>';*/
			}
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "RetailCurrency"){
					$RetailCurrency = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($RetailCurrency);
				echo '</pre>';*/
			}
			/*номер товара поставщика*/
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "VendorProdNum"){
					$VendorProdNum = $Value;
				} else {
					continue;
				}
				/*echo '<pre>';
				var_dump($VendorProdNum);
				echo '</pre>';*/
				/*echo '<pre>';
				echo 'end product';
				echo '</pre>';*/
			}


			//die('123');



			//$this->model_extension_module_xml_module->add_product_desc($id , $language_id, $clean_name);

			//$this->model_extension_module_xml_module->add_product_img($product_image_id, $id, $image);
			//$this->model_extension_module_xml_module->add_product_to_cat_test($id, 59);
			//$this->model_extension_module_xml_module->add_product_to_store($id, 0);
			//$this->model_extension_module_xml_module->add_product_to_store($id, 0);
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

			$id++;
			/*if($id == 5000){
				break;
			}*/

			//die('123');

			// go to next <product />
			$z->next('DocDetail');
		}
		//функционал добавления названий категорий. Рабочий. Категории добавлены
		//$cat_id = 60;
		//foreach ($groups as $group){
			//$this->model_extension_module_xml_module->add_categories_ids($cat_id); //айдишники категорий
			//$this->model_extension_module_xml_module->add_categories_names($cat_id, $group); //названия категорий
			//$this->model_extension_module_xml_module->add_categories_path($cat_id); //путь категорий
			//$this->model_extension_module_xml_module->add_categories_to_store($cat_id); //отображение в магазине
		//	$cat_id++;
		//}

	}
	public function import_manufacturer(){
		$this->load->language('extension/module/xml_module');
		$this->load->model('extension/module/xml_module');

	$manufacturers2 = [
		"Hyperline",
		"КВТ",
		"EKF",
		"Rexant",
		"Элетех",
		"GALAD",
		"Legrand",
		"PROCONNECT",
		"КЭАЗ",
		"НовАтек-Электро",
		"HAUPA",
		"NETLAN",
		"ITK (группа IEK)",
		"NAVIGATOR",
		"ОНЛАЙТ",
		"Белый свет",
		"Световые технологии",
		"PHILIPS",
		"Протэкт",
		"IN HOME",
		"ASD",
		"LLT",
		"DKC",
		"VARTON",
		"КашинЗЭА",
		"Электротехник",
		"IEK",
		"Neon-Night",
		"КОНТАКТОР(группа Legrand)",
		"ENSTO",
		"Ballu",
		"Electrolux",
		"Энергомера",
		"Ruvinil",
		"ABB",
		"КМ-Профиль",
		"Simon",
		"Эра",
		"HINTEK",
		"Трофи",
		"КОСМОС",
		"WAGO",
		"Duracell",
		"Pleomax",
		"Camelion",
		"Ergolux",
		"АТОН",
		"Ultraflash",
		"Schneider Electric",
		"Кунцево-Электро",
		"Тритон",
		"Лисма",
		"HENSEL",
		"ONI (группа IEK)",
		"ASD-electric",
		"GAUSS(группа VARTON)",
		"ФАZA",
		"Duwi",
		"JazzWay",
		"Makel",
		"Стример",
		"OPORA ENGINEERING",
		"Трансвит",
		"LED-effect",
		"OneKeyElectro",
		"ССТ",
		"Инженерсервис",
		"LEZARD",
		"Warmstad",
		"OBO Bettermann",
		"Timberk",
		"SCOOLE",
		"HYUNDAI",
		"ЗЭТАРУС",
		"Урал Пак",
		"Пересвет",
		"CAVEL",
		"LEDVANCE OSRAM",
		"Universal",
		"Союз",
		"EATON",
		"ТАЙПИТ",
		"EXTHERM",
		"CSVT",
		"Ресанта",
		"МЕГАВАТТ",
		"СОЭМИ Ст.Оскол",
		"КЗОЦМ",
		"Электрофидер",
		"SHTOK",
		"HEGEL",
		"Передовик",
		"АСТЗ Ардатов",
		"Евроавтоматика F&F",
		"Кабэкс",
		"Новый Свет Рязань",
		"Аргос",
		"Аврора",
		"Ксенон",
		"REV",
		"Кольчугино",
		"Уралкабель",
		"Сибкабель",
		"Uniel",
		"НИЛЕД",
		"ВК",
		"Реле и Автоматика",
		"Конкорд",
		"Энергокомплект",
		"Энергокабель",
		"Владасвет СТЗ",
		"ЭЛПРОМ",
		"Цветлит",
		"ПромЭл",
		"GUSI",
		"ЧПТУП ВЭТП Свет Витебск",
		"Иркутсккабель",
		"Элкаб КЗ",
		"СКК",
		"Альгиз К",
		"Сарансккабель",
		"Костромское ФГУ ИК-1",
		"Росскат",
		"ЭЛЕКТРОКАБЕЛЬ НН",
		"Москабель",
		"SHLights",
		"РЭК-PRYSMIAN",
		"СегментЭнерго",
		"Электродеталь Воронеж",
		"Инкотекс",
		"Магна",
		"Промстройкабель",
		"ПожСпецКабель",
		"Михневский ЗЭМИ",
		"Диэлектрик",
		"Кавказкабель",
		"SUPRLAN",
		"РЭМЗ",
		"LADesign",
		"ПАРТНЕР",
		"Агрокабель",
		"Электропромпласт",
		"АЛЬФАКАБЕЛЬ",
		"3М",
		"ERA",
		"ВЛКЗ",
		"Севкабель Питер",
		"Grand Meyer",
		"Кирскабель",
		"Эм-кабель",
		"Helvar",
		"TYCO Electronics",
		"ЗСП",
		"Камкабель",
		"Угличкабель",
		"РК",
		"Сатурн-Электро",
		"OSRAM",
		"ЧУП 'ЭНВА'",
		"DiCiTi",
		"ИНСТАЛЛ",
		"КабельЭлектроСвязь",
		"ПЭМИ Ростов-на-Дону",
		"Балткабель",
		"Брестский ЭЛЗ",
		"Матрица",
		"КЭЛЗ",
		"Favor",
		"Security Force",
		"BURO",
		"Чувашкабель",
		"Людиновокабель",
		"Индустрия Гагарин",
		"Беларускабель",
		"МЗВА",
		"БАЛТИЙСКИЕ МЕТИЗЫ",
		"ЧУП Элект Белтиз",
		"Подольский ЗЭМИ",
		"ФОКУС",
		"Bylectrica",
		"Delta",
		"КПП прочее",
		"Nexans",
		"RADIUM",
		"Nordic Aluminium",
		"Спецкабель",
		"НКЗ 'ЛипарКабель'",
		"Экотон Москва",
		"ALFRESCO",
		"Солекс",
		"Рубеж",
		"ЮМЭК",
		"Sormat",
		"Нева-Транс Комплект",
		"РЗМКП",
		"Першотравенский з-д",
		"Болид",
		"ЗЭУ",
		"ЭКОНОМОВЪ",
		"Подольсккабель",
		"Техник",
		"Южноуральский АИЗ",
		"ИП Раченков А.В.",
		"Актей",
		"Томский ЭЛЗ",
		"Альянс Мастер",
		"Белоцерковское УПП УТОС",
		"Радий",
		"Каскад-Электро",
		"Рефлакс",
		"Белорецкий ЭМЗ Максимум",
		"Искра Львов",
		"Электротехнический з-д Калуга",
		"ПЭО прочее",
		"Пластдеталь",
		"Технологии света",
		"СПКБ Техно",
		"ПромСпецПрибор",
		"Клейтон",
		"ТЭФИ",
		"СПК прочее",
		"ФормАвто",
		"ВЭЛАН",
		"Net.on",
		"УКРИЗОЛЯТОР",
		"Сибирский Арсенал",
		"Synergy",
		"АФЗ",
		"МЕРИОН-Спецодежда",
		"ЭЛКАБ",
		"Энергозащита",
		];



		foreach ($manufacturers2 as $manufacturer){
			$this->model_extension_module_xml_module->add_manufacturers_names($manufacturer);
		}
	}

	public function import_manufacturer_id(){
		set_time_limit(1200);
		ini_set("memory_limit", "2056M");
		//1536
		$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/PRICAT_335537_602955929.xml';

		$this->load->language('extension/module/xml_module');
		$this->load->model('extension/module/xml_module');

		$z = new XMLReader;
		$z->open($xmlfile);
		$doc = new DOMDocument;
		while ($z->read() && $z->name !== 'DocDetail');

		$id = 50;
		if($id == 16552){

		}
		$brands = [];
		while ($z->name === 'DocDetail')
		{
			$node = simplexml_import_dom($doc->importNode($z->expand(), true));
			$DocDetailOptions = $node->DocDetailOptions;
			$SenderPrdCode = $node->SenderPrdCode;
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "Brand"){
					$Brand = $Value;
					/*if(!in_array($Brand, $brands)){
						$brands[] = $Brand;
					}*/

					if($Brand != ""){
						$m_id = $this->model_extension_module_xml_module->get_manufacturer_id($Brand);
						//описание бренда
						//$this->model_extension_module_xml_module->add_manufacturers_desc($m_id["manufacturer_id"], $Brand);
						//бренд в магазин
						//$this->model_extension_module_xml_module->add_manufacturers_tostore($m_id["manufacturer_id"]);
						//бренд для товара
						if($m_id){
							//$this->model_extension_module_xml_module->add_manufacturers_ids($id, $m_id["manufacturer_id"]);
						} else {
							//$this->model_extension_module_xml_module->add_manufacturers_ids($id, 7777);
						}
						//$this->model_extension_module_xml_module->add_manufacturers_ids_upc($SenderPrdCode, $m_id["manufacturer_id"]);
					}
				} else {
					continue;
				}
			}
			$id++;
			$z->next('DocDetail');

		}

	}

	public function import_update_time(){
		set_time_limit(1200);
		ini_set("memory_limit", "2056M");
		//1536
		$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/PRICAT_335537_602955929.xml';

		$this->load->language('extension/module/xml_module');
		$this->load->model('extension/module/xml_module');

		$z = new XMLReader;
		$z->open($xmlfile);
		$doc = new DOMDocument;
		while ($z->read() && $z->name !== 'DocDetail');
		$id = 50;
		while ($z->name === 'DocDetail')
		{
			$node = simplexml_import_dom($doc->importNode($z->expand(), true));
			$DocDetailOptions = $node->DocDetailOptions;
			$SenderPrdCode = $node->SenderPrdCode;
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "LastUpdDate"){
					$UpdateTime = $Value;
					$opencartTime = strtotime($UpdateTime);

					//$arrayTime = date_parse_from_format ('j.n.Y',$UpdateTime);
					//$opencartTime = $arrayTime['year'].'-'.$arrayTime['month'].'-'.$arrayTime['day'];
					if($opencartTime != ""){
						//обновить дату
						$this->model_extension_module_xml_module->update_product_date($SenderPrdCode, $opencartTime);
					}
				} else {
					continue;
				}
			}
			$id++;
			$z->next('DocDetail');

		}

	}

	public function update_properties(){
		set_time_limit(6800);
		ini_set("memory_limit", "2056M");
		//1536
		$xmlfile = $_SERVER["DOCUMENT_ROOT"].'/PRICAT_335537_602955929.xml';

		$this->load->language('extension/module/xml_module');
		$this->load->model('extension/module/xml_module');

		$z = new XMLReader;
		$z->open($xmlfile);
		$doc = new DOMDocument;
		$id = 50;




		while ($z->read() && $z->name !== 'DocDetail');

	//die('123');
		while ($z->name === 'DocDetail')
		{

			$node = simplexml_import_dom($doc->importNode($z->expand(), true));
			$SenderPrdCode = $node->SenderPrdCode;
			$DocDetailOptions = $node->DocDetailOptions;
			$OptionsArray = array();
			/*echo '<pre>';
			var_dump($SenderPrdCode);
			echo '</pre>';*/
			foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){
				$OptionsArray[] = $element;
			}
			foreach ($OptionsArray as $item){
				/*if($item->Name == "Width"){
					$Width = $item->Value;
					$this->model_extension_module_xml_module->add_property_width($id, $Width);
				}*/
				if($item->Name == "Height"){
					$Height = $item->Value;
					$this->model_extension_module_xml_module->add_property_height($id, $Height);
				}
			}


			$date = date('Y-d-m', time());
			//$date = '2021-01-27 00:00:00';



			//$this->model_extension_module_xml_module->add_datetime($SenderPrdCode, $date);


			/*foreach ( $DocDetailOptions->xpath( 'DocOption' ) as $element ){

				$Name = (string)$element->Name[0];
				$Value = (string)$element->Value[0];
				if($Name == "Width"){
					$Width = $Value;
					if($Width != ""){
						$this->model_extension_module_xml_module->add_property_width($SenderPrdCode, $Width);
					} else {
						continue;
					}
				}
				if($Name == "Height"){
					$Height = $Value;
					if($Height != ""){
						$this->model_extension_module_xml_module->add_property_height($SenderPrdCode, $Height);
					} else {
						continue;
					}
				}

			}*/




			$id++;


				//echo "<script>alert('updated else last items')</script>";
				//return $start+$loop;



			$z->next('DocDetail');
		}
	}

	public function convert($str)
	{
		return iconv("Windows-1251", "UTF-8", $str);
	}


	public function update_descriptions(){
		$this->load->language('extension/module/xml_module');
		$this->load->model('extension/module/xml_module');

		$root = $_SERVER["DOCUMENT_ROOT"];
		$fileName = $root.'/desc.csv';

		//http://tochka.loc/index.php?route=product/product&product_id=13806
		$count = 0;
		if (($fp = fopen($fileName, "r")) !== FALSE) {

			while (($data = fgetcsv($fp, 0, ";")) !== FALSE) {



				//if($count > 59500){


					//$list[] = iconv('WINDOWS-1251', 'UTF-8', $data);
					$data = array_map(array($this,'convert'), $data);


					$upc = $data['0'];
					$description = str_replace(array('\'', '"'), '', $data['8']);
					$description = str_replace('•', '<br>•', $description);

					$p_id = $this->model_extension_module_xml_module->get_product_id($upc);
					if(empty($p_id)){
						continue;
					}


					$this->model_extension_module_xml_module->update_description($p_id["product_id"], $description);


					//$list[] = $data;

				/*} elseif ($count == 60000){
					break;
				} */
				//SELECT * FROM oc_product_description WHERE `description` != ''




				$count++;
			}
			fclose($fp);



			//print_r($list);
		}
	}

	public function update_filters(){

		$this->load->language('extension/module/xml_module');
		$this->load->model('extension/module/xml_module');
		$product_name_part = $_POST['product_name'];
		//получаем айдишники запрашиваемых продуктов
		$product_ids_array = $this->model_extension_module_xml_module->get_products_ids_by_part_name($product_name_part);
		//получаем айдишники фильтров по имени продукта
		$filter_ids_array = $this->model_extension_module_xml_module->get_filter_id_by_part_name($product_name_part);
		if(empty($filter_ids_array)){
			echo '<script>alert("Фильтра не найдены")</script>';
			return;
		}

		foreach ($product_ids_array as $product_id){
			$miss_row_p = $this->model_extension_module_xml_module->get_setted_filters($product_id['product_id'], $filter_ids_array['filter_id']);
			if($miss_row_p == NULL){
				$this->model_extension_module_xml_module->set_filter_to_product($filter_ids_array['filter_id'], $product_id['product_id']);
			}

			$category_ids_array[] = $this->model_extension_module_xml_module->get_category_ids_for_filters($product_id['product_id']);
		}
		foreach ($category_ids_array as $cat_id){
			$cats_ids[] = $cat_id['category_id'];
		}
		//получаем уникальные айди категорий для установки фильтров
		$unique_cats_ids = array_unique($cats_ids);
		foreach($unique_cats_ids as $unique_cat_id){
			$miss_row_c = $this->model_extension_module_xml_module->get_setted_filters_cats($unique_cat_id, $filter_ids_array['filter_id']);
			if($miss_row_c == NULL){
				$this->model_extension_module_xml_module->set_filter_to_cat($filter_ids_array['filter_id'], $unique_cat_id);
			}
		}






		/*дальше написать установщики фильтров для продуктов и для категорий*/
	}

	public function update_attributes(){
		$this->load->language('extension/module/xml_module');
		$this->load->model('extension/module/xml_module');
		$product_name_part = $_POST['product_name'];
		$attr_name = $_POST['attr_name'];
		//получаем айдишники запрашиваемых продуктов
		$product_ids_array = $this->model_extension_module_xml_module->get_products_ids_by_part_name($product_name_part);
		/*получаем айдишник атрибута по части имени товара*/
		$attr_id = $this->model_extension_module_xml_module->get_attr_id_by_part_name($attr_name);
		//$attr_id["attribute_id"];
		foreach ($product_ids_array as $product_id){
			$miss_row_a = $this->model_extension_module_xml_module->get_setted_attr($product_id['product_id'], $attr_id["attribute_id"], $product_name_part);
			if($miss_row_a != NULL){
				continue;
			} else {
				$this->model_extension_module_xml_module->set_attr_to_product($product_id['product_id'], $attr_id["attribute_id"], $product_name_part);
			}

		}
	}

	/**
	* Parsing XML and adding products to DB
	* @param SimpleXMLElement Object
	*/
	private function parseProducts($xml){
		$catalogue = $xml->shop->offers;
		$data = array();

		foreach ($catalogue as $offer) {
			
			foreach ($offer as $product) {

				$isMainImageSet = false;
				$data['product_image'] = array();

				foreach ($product->attributes() as $key => $value) {
					if ($key == 'id') $data['sku'] = $value;
					elseif ($key == 'available') $data["stock_status_id"] = $value == true ? 7 : 5;
					elseif ($key == 'instock') $data["quantity"] = $value;
				}

				$data['price'] = $product->price;
				$data['currencyId'] = $product->currencyId;
				$data['product_category'] = array( $product->categoryId );

				foreach ($product->picture as $image) {
					if(!$isMainImageSet) { //main image
						$data['image'] = $image;
						$isMainImageSet = true;
					}else{ //additional images
						array_push($data['product_image'], $image);
					}
				}

				$data['shipping'] = $product->delivery == true ? 1 : 0;
				$data['name'] = $product->name;
				$data['vendor'] = $product->vendor;
				$data['vendorCode'] = $product->vendorCode;
				$data['product_description'] = array(
					$this->language_id => 
					array(
						'language_id' => $this->language_id,
						'name' => $product->name,
						'description' => $product->description,
						'tag' => '',
						'meta_title' => $product->name,
						'meta_description' => $product->description,
						'meta_keyword' => ''
					)
				);
				$data['model'] = $product->model;

				$manufacturers = $this->model_extension_module_xml_module->getIdManufacturerByName($product->vendor);
				if( !empty($manufacturers) ) $data['manufacturer_id'] = $manufacturers['manufacturer_id'];
				else {
					$data['manufacturer_id'] = $this->model_catalog_manufacturer->addManufacturer(
						array(
							'name' => $product->vendor,
							'sort_order' => '0'
						));
				}

				$attribute_text = $product->param;
				$attribute_id = 0;

				$attribute_data['attribute_description'] = array();
				if(!empty($product->param)){
					foreach ($product->param->attributes() as $key => $value) {
						if ($key == 'name'){
							$list_id_attributs = $this->model_extension_module_xml_module->getIdAttributeByName($value);
							if( empty($list_id_attributs) ){//If attribute not exist
								$category_name = $this->model_extension_module_xml_module->getCategoryNameById($product->categoryId);
								$list_id_groups_attributes = $this->model_extension_module_xml_module->getIdAttributeGroupByName($category_name['name']);
								$attribute_data['attribute_group_id'] = $list_id_groups_attributes['attribute_group_id'];
								$attribute_data['attribute_description'] = array(
										$this->language_id => array( 'name' => $value	)
									);
								$attribute_data['sort_order'] = 0;
								$attribute_id = $this->model_catalog_attribute->addAttribute($attribute_data);
							}else{//Attribute already exist
								$attribute_id = $list_id_attributs['attribute_id'];
							}
						}
					}
				}
				


// $this->log->write( $key .' - '. $value );
				$data['product_attribute'] = array();
				if(!empty($product->param)){
					foreach ($product->param->attributes() as $key => $value) {
						if ($key == 'name'){
							array_push($data['product_attribute'], array(
								'attribute_id' => $attribute_id,
								'product_attribute_description' => array(
										$this->language_id => array( 'text' => $attribute_text )
									)
								)
							);
						}
					}
				}

				$data['upc'] = '';
				$data['ean'] = '';
				$data['jan'] = '';
				$data['isbn'] = '';
				$data['mpn'] = '';
				$data['location'] = '';
				$data['minimum'] = '1';
				$data['subtract'] = '';
				$data['date_available'] = '';
				$data['points'] = '';
				$data['weight'] = '';
				$data['weight_class_id'] = '';
				$data['length'] = '';
				$data['width'] = '';
				$data['height'] = '';
				$data['length_class_id'] = '';
				$data['status'] = 1;
				$data['tax_class_id'] = '';
				$data['sort_order'] = '';

				$data['product_layout'] = array('0' => '0');
				$data['product_store'] = array('0');

				$products = $this->model_extension_module_xml_module->getProductIdByModel($data['model']);
				$product_id = 0;
				if( !empty($products) ) {
					$product_id = $products['product_id'];
					$this->model_catalog_product->editProduct($product_id, $data);
				}else{
					$product_id = $this->model_catalog_product->addProduct($data);
				}
			}
		}
	}

	/**
	* Parsing XML and adding categories to DB
	* @param SimpleXMLElement Object
	*/
	private function parseCategories($xml){
		$catalogue = $xml->shop->categories;
		$data = array();
		foreach ($catalogue as $categories) {
			foreach ($categories as $category) {
				$data['name'] = $category;
				foreach ($category->attributes() as $key => $value) {
					$data['parent_id'] = 0;
					if ($key == 'id') $data['id'] = $value;
					elseif ($key == 'parentId') $data['parent_id'] = (int)$value;
				}
				$this->addCategory($data);
				$this->addAttributeGroup($data);
			}
		}
	}

	/**
	* Adding attribute group to DB
	* Attribute group name = category name
	* @param [] $data
	* @return Int
	*/
	protected function addAttributeGroup($data) {
		$result = $this->model_extension_module_xml_module->getIdAttributeGroupByName($data['name']);
		$attribute_group_id = 0;
		if(empty($result)){
			$data['attribute_group_description'] = array(
				$this->language_id => 
				array(
					'language_id' => $this->language_id,
					'name' => $data['name']
				)
			);
			$data['sort_order'] = 0;
			$attribute_group_id = $this->model_catalog_attribute_group->addAttributeGroup($data);
		}else $attribute_group_id = $result['attribute_group_id'];
		return $attribute_group_id;
	}

	/**
	* Adding category to DB
	* @param [] $data
	*/
	protected function addCategory($data) {
		$category_id = (int)$data['id'];
		if( !empty( $this->model_extension_module_xml_module->getCategory($category_id) ) ) { //category already exist, UPDATE!
			$this->updateCategory($category_id, $data);
		}else { //category not found, INSERT!
			$this->insertCategory($data);
		}
	}

	/**
	* Preparing data array for category
	* @param [] $data
	* @return [] $data
	*/
	protected function prepareCategoryData($data) {
		$data['top'] = $data['parent_id'] == 0 ? 1 : 0;
		$data['column'] = 0;
		$data['sort_order'] = 0;
		$data['status'] = 1;
		$data['category_description'] = array(
			$this->language_id => 
			array(
				'category_id' => (int)$data['id'],
				'language_id' => $this->language_id,
				'name' => $data['name'],
				'description' => '',
				'meta_title' => $data['name'],
				'meta_description' => '',
				'meta_keyword' => ''
			)
		);
		$data['category_store'] = array(0);
		$data['category_layout'] = array(0 => 0);
		return $data;
	}

	/**
	* Updating category to DB
	* @param [] $data
	*/
	protected function updateCategory($category_id, $data) {
		$data = $this->prepareCategoryData($data);
		$this->model_extension_module_xml_module->editCategory($category_id, $data);
	}

	/**
	* Inserting category to DB
	* @param [] $data
	* @return Int - ID of inserted category
	*/
	protected function insertCategory($data) {
		$data = $this->prepareCategoryData($data);
		return $this->model_extension_module_xml_module->addCategory($data);
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/xml_module')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
