<?php
class ControllerMarketplaceExtension extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('marketplace/extension');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['type'])) {
			$data['type'] = $this->request->get['type'];
		} else {
			$data['type'] = 'module';
		}
		
		$data['categories'] = array();
		
		$files = glob(DIR_APPLICATION . 'controller/extension/extension/*.php', GLOB_BRACE);
		
$signore = array ('autolinks', 'bot_report', 'canonicals', 'clickfix', 'custom_alt_generator', 'custom_h1_generator', 'custom_h2_generator', 'custom_imgtitle_generator', 'custom_title_generator', 'extendedseo', 'keywords_generator', 'l', 'meta_description_generator', 'mlseo', 'not_found_report', 'redirect', 'rename_files', 'richsnippets', 'rp_generator', 'seoedit', 'seoeditor', 'seoimages', 'seopack', 'seopagination', 'seoreplacer', 'seoreport', 'table_edit_ajax', 'tag_generator');
			
		foreach ($files as $file) {
			$extension = basename($file, '.php');
if (!in_array($extension, $signore)) {
			
			
			// Compatibility code for old extension folders
			$this->load->language('extension/extension/' . $extension, 'extension');
		
			if ($this->user->hasPermission('access', 'extension/extension/' . $extension)) {
				$files = glob(DIR_APPLICATION . 'controller/extension/' . $extension . '/*.php', GLOB_BRACE);
		
				$data['categories'][] = array(
					'code' => $extension,
					'text' => $this->language->get('extension')->get('heading_title') . ' (' . count($files) .')',
					'href' => $this->url->link('extension/extension/' . $extension, 'user_token=' . $this->session->data['user_token'], true)
				);
			}			
		}
		
}
			
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketplace/extension', $data));
	}
}