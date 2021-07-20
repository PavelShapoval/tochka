<?php
class ControllerExtensionModuleProductsInsert extends Controller {
	public function index() {

		$this->load->language('extension/module/products_insert');
		$this->load->model('setting/module');
		echo '<pre>';
		var_dump($this->load->model('extension/module/products_insert'));
		echo '</pre>';
		$this->load->model('extension/module/products_insert');
		
		//кастом
		$this->document->addScript('/admin/view/javascript/ajax.js');
		//кастом
		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_module->editSetting('products_insert', $this->request->post);
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
			'href' => $this->url->link('extension/module/products_insert', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/products_insert', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_products_insert_status'])) {
			$data['module_products_insert_status'] = $this->request->post['module_products_insert_status'];
		} else {
			$data['module_products_insert_status'] = $this->config->get('module_products_insert_status');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['xml_import_link'] = 'https://b2b-sandi.com.ua/export_xml/4fb01c5bf3957d849de460be7eb84821';

		$data['import_xml'] = (HTTPS_SERVER . 'index.php?route=extension/module/products_insert/export_xml&token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		$data['export_xml'] = (HTTPS_SERVER . 'controller/export_xml/export.php');

		$this->response->setOutput($this->load->view('extension/module/products_insert', $data));
	}


}
function parse_xml(){
	//$model = $this->load->model('extension/module/products_insert');
	/*echo '<pre>';
	var_dump($model);
	echo '</pre>';*/
	set_time_limit(900);

//$xmlfile = 'PRICAT_335537_600342001.xml';
//$xmlfile = 'PRODAT_369147_600656224/PRODAT_369147_600656224.xml';
//$xmlfile = 'PRICAT_TEST.xml';
//$xmlfile = 'PRICAT_335537_594384354.xml';
	$xmlfile = '/PRODAT_TEST.xml';



	$z = new XMLReader;
	$z->open($xmlfile);

	$doc = new DOMDocument;
	$count = 0;

	while ($z->read() && $z->name !== 'DocDetail');


	while ($z->name === 'DocDetail')
	{
		$node = simplexml_import_dom($doc->importNode($z->expand(), true));
		$product_name = $node->ProductName;

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

		}
		//echo $node->Image->Value.'<br>';
		echo '<img src='.$node->Image->Value.' alt=""/>';
		echo $node->CertificateNum.'<br>';

		$count++;
		echo '<br>'.$count;
		echo '<hr>';*/

		// go to next <product />
		$z->next('DocDetail');
	}
}

