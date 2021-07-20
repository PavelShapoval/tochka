<?php
/**
 * Brainy Filter Pro 5.1.3 OC3, September 18, 2017 / brainyfilter.com 
 * Copyright 2015-2017 Giant Leap Lab / www.giantleaplab.com 
 * License: Commercial. Reselling of this software or its derivatives is not allowed. You may use this software for one website ONLY including all its subdomains if the top level domain belongs to you and all subdomains are parts of the same OpenCart store. 
 * Support: http://support.giantleaplab.com 
 */ 
class ControllerExtensionModuleBrainyFilter extends Controller {
	private $error = array(); 
    private $_data = array();
    
    /**
     * @var ModelModuleBrainyFilter
     */
    protected $model;
	
	const SUBMIT_TYPE_AUTO    = "auto";
	const SUBMIT_TYPE_DELAY   = "delay";
	const SUBMIT_TYPE_BUTTON  = "button";
	const SUBMIT_BUTTON_FLOAT = "float";
	const SUBMIT_BUTTON_FIXED = "fixed";
    
    private $default = array(
        'basic' => array(
            'behaviour' => array(
                'containerSelector' => '#content .row:nth-last-of-type(2)',
                'paginatorSelector' => '#content .row:nth-last-of-type(1)',
                'attribute_groups' => 1,
                'product_count' => 1,
                'hide_empty' => 0,
                'limit_height' => array(
                    'enabled' => 0,
                    'height' => 144,
                ),
                'limit_items' => array (
                    'enabled' => 0,
                    'number_to_show' => 4,
                    'number_to_hide' => 2,
                ),
                'sections' => array(
                    'search' => array(
                        'enabled' => 0,
                        'collapsed' => 0,
                    ),
                    'price' => array(
                        'enabled' => 1,
                        'collapsed' => 0,
                        'control' => 'slider',
                    ),
                    'category' => array(
                        'enabled' => 1,
                        'collapsed' => 0,
                        'control' => 'checkbox',
                    ),
                    'stock_status' => array(
                        'enabled' => 1,
                        'collapsed' => 0,
                    ),
                    'manufacturer' => array(
                        'enabled' => 1,
                        'collapsed' => 0,
                        'control' => 'checkbox',
                    ),
                    'attribute' => array(
                        'enabled' => 1,
                        'collapsed' => 0,
                    ),
                    'option' => array(
                        'enabled' => 0,
                        'collapsed' => 0,
                    ),
                    'filter' => array(
                        'enabled' => 0,
                        'collapsed' => 0,
                    ),
                    'rating' => array(
                        'enabled' => 0,
                        'collapsed' => 0,
                    ),
                ),
                'sort_order' => array(
                    'enabled'      => 0,
                    'search'       => 0,
                    'price'        => 1,
                    'category'     => 2,
                    'stock_status' => 3,
                    'manufacturer' => 4,
                    'attribute'    => 5,
                    'option'       => 6,
                    'filter'       => 7,
                    'rating'       => 8,
                ),
            ),
            'submission' => array(
                'submit_type'                   => 'button',
                'submit_button_type'            => 'float',
                'submit_delay_time'             => 1000,
                'hide_panel'                    => 1,
            ),
            'global' => array(
                'instock_status_id'             => 7,
                'subcategories_fix'             => 0,
                'multiple_attributes'           => 0,
                'attribute_separator'           => ',',
                'cron_secret_key'               => '',
                'hide_out_of_stock'             => 0,
                'postponed_count'              => 1,
            ),
            'style' => array(
                'block_header_background'       => array('val' =>'#f7f7f7'),
                'block_header_text'             => array('val' =>'#000000'),
                'product_quantity_background'   => array('val' =>'#F46234'),
                'product_quantity_text'         => array('val' =>'#ffffff'),
                'price_slider_background'       => array('val' =>'#eeeeee'),
                'price_slider_area_background'  => array('val' =>'#f6a828'),
                'price_slider_border'           => array('val' =>'#dddddd'),
                'price_slider_handle_background'=> array('val' =>'#f6f6f6'),
                'price_slider_handle_border'    => array('val' =>'#cccccc'),
                'group_block_header_background' => array('val' =>'#CECBCB'),
                'group_block_header_text'       => array('val' =>'#000000'),
                'resp_show_btn_color'           => array('val' =>'#19A3DF'),
                'resp_reset_btn_color'          => array('val' =>'#F53838'),
                
                'responsive' => array(
                    'enabled' => 0,
                    'collapsed' => 1,
                    'max_screen_width' => 768,
                    'max_width' => 300,
                    'position' => 'left',
                    'offset' => 80,
                ),
            ),
            'attributes_default' => array(
                'enable_all' => 1,
                'control' => 'checkbox',
            ),
            'options_default' => array(
                'enable_all' => 1,
                'control' => 'checkbox',
                'mode' => 'label',
            ),
            'filters_default' => array(
                'enable_all' => 1,
                'control' => 'checkbox',
            ),
            'attributes' => array(),
            'options' => array(),
            'filters' => array(),
            'current_adm_tab' => 'embedding',
        ),
    );
    
    private $defaultLayoutSettings = array(
        'module_id'         => 'new',
        'layout_position'   => 'column_left',
        'layout_sort_order' => '0',
        'layout_enabled'    => '1',
        'behaviour'         => array(
            'containerSelector' => '',
            'paginatorSelector' => '',
        ),
    );
    
    public function __construct($registry) {
        parent::__construct($registry);
        
        $this->load->model('extension/module/brainyfilter');
        $this->model = new ModelExtensionModuleBrainyFilter($this->registry);
    }

    public function install() {
        $this->load->model('setting/event');
        
        $this->model->addCustomIndexes();
        $this->model->createAttributeValueTable();
        $this->model->createProductAttrValueTable();
        $this->model->createTaxRateTable();
        $this->model->createFilterTable();
        $this->model->fillCacheTable();
        $this->model->fillTaxRateTable();
        
        $this->model->addDefaultLayout();
        
        $defaultLayout = $this->model->getDefaultLayout();
        // auto save basic settings
        $basicSettings = $this->default['basic'];
        $basicSettings['module_id'] = 'basic';
        $this->_saveSettings($basicSettings);
        // auto save default layout
        $defaultSettings = $this->defaultLayoutSettings;
        $defaultSettings['module_id'] = 'new';
        $defaultSettings['layout_id'] = $defaultLayout;
        $settings = $this->_arrayReplaceRecursive($this->default['basic'], $defaultSettings);
        $this->_saveSettings($settings);
        
        $this->model_setting_event->addEvent('brainyfilter', 'admin/model/catalog/product/addProduct/after', 'extension/module/brainyfilter/eventAddProduct');
        $this->model_setting_event->addEvent('brainyfilter', 'admin/model/catalog/product/editProduct/after', 'extension/module/brainyfilter/eventEditProduct');
        $this->model_setting_event->addEvent('brainyfilter', 'admin/model/catalog/product/deleteProduct/after', 'extension/module/brainyfilter/eventDeleteProduct');
        $this->model_setting_event->addEvent('brainyfilter', 'admin/model/catalog/review/addReview/after', 'extension/module/brainyfilter/eventEditReview');
        $this->model_setting_event->addEvent('brainyfilter', 'admin/model/catalog/review/editReview/after', 'extension/module/brainyfilter/eventEditReview');
        $this->model_setting_event->addEvent('brainyfilter', 'admin/model/catalog/review/deleteReview/after', 'extension/module/brainyfilter/eventEditReview');
    }
    
    public function uninstall() {
        $this->load->model('setting/event');
        
        $this->model->dropAttributeValueTable();
        $this->model->dropProductAttrValueTable();
        $this->model->dropTaxRateTable();
        $this->model->dropFilterTable();
        
        $this->model->removeDefaultLayout();
        
        $this->model->removeCustomIndexes();
        $this->model_setting_event->deleteEvent('brainyfilter');
    }
    
    public function eventEditProduct($route, $args, $output)
    {
        $productId = $args[0];
        $this->model->cacheProductProperties($productId);
    }
    
    public function eventAddProduct($route, $args, $productId)
    {
        $this->model->cacheProductProperties($productId);
    }
    
    public function eventDeleteProduct($route, $args, $output) 
    {
        $productId = $args[0];
        $this->model->deleteProductProperties($productId);
    }
    
    public function eventEditReview($route, $args, $output)
    {
        $reviewId = $args[0];
        $this->load->model('catalog/review');
        $review = $this->model_catalog_review->getReview($reviewId);
        $this->model->cacheProductProperties($review['product_id']);
    }
    
    public function index()
    {
        $this->load->model('setting/setting');
	    $this->load->model('localisation/stock_status');
        $this->load->model('design/layout');
        $this->load->model('catalog/category');
        $this->_setupLanguage();
        
        $isMijoShop = class_exists('MijoShop') && defined('JPATH_MIJOSHOP_OC');
		$this->document->addScript('view/javascript/tinycolor.js');
		$this->document->addScript('view/javascript/jquery.fn.sortable.min.js');
		$this->document->addScript('view/javascript/bootstrap.colorpickersliders.min.js');
		$this->document->addScript('view/javascript/jquery.autocomplete.min.js');
		$this->document->addScript('view/javascript/brainyfilter.js');
        
        if (isset($this->request->post['bf'])) {
            $post = $this->_parsePostData();
            if ($this->_validate($post)) {
                $moduleId = $this->_saveSettings($post);
                
                $this->session->data['success'] = $this->language->get('message_success');

                if ($this->request->post['action'] == 'apply') {
                    $this->response->redirect($this->_link('extension/module/brainyfilter', '&module_id=' . $moduleId));
                } else {
                    $this->response->redirect($this->_link('marketplace/extension'));
                }
            }
        }
        
        $moduleId = $this->_getRequestParam('module_id', 'basic');

		$this->document->setTitle($this->language->get('page_title'));
		if ($isMijoShop) {
			$this->document->addStyle('admin/view/stylesheet/brainyfilter.css');
            $this->document->addStyle('admin/view/stylesheet/bootstrap.colorpickersliders.min.css');
		} else {
			$this->document->addStyle('view/stylesheet/brainyfilter.css');
            $this->document->addStyle('view/stylesheet/bootstrap.colorpickersliders.min.css');
		}
        $this->_data['heading_title'] = $this->language->get('heading_title');
        
        $this->_data['breadcrumbs'] = array();

   		$this->_data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->_link('common/home'),
      		'separator' => false
   		);

   		$this->_data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('module'),
			'href'      => $this->_link('marketplace/extension'),
      		'separator' => ' :: '
   		);
		
   		$this->_data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('page_title'),
			'href'      => $this->_link('extension/module/brainyfilter'),
      		'separator' => ' :: '
   		);
        
        if (isset($this->session->data['success'])) {
			$this->_data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->_data['success'] = '';
		}
        
        
        $this->_data['error_warning'] = $this->error;
        
        $this->_data['cancel'] = $this->_link('marketplace/extension');
        $this->_data['action'] = $this->_link('extension/module/brainyfilter');
        $this->_data['instanceUrl'] = $this->_link('extension/module/brainyfilter', '&module_id=');
        $this->_data['refreshAction'] = $this->_link('extension/module/brainyfilter/refresh');
        $this->_data['attributeValuesAction'] = $this->_link('extension/module/brainyfilter/attributeValues');
        $this->_data['modRefreshAction'] = $this->_link('extension/module/brainyfilter/modRefreshTrigger');
        $this->_data['removeInstanceAction'] = $this->_link('extension/module/brainyfilter/removeInstance', '&module_id=' . $moduleId);
        
	    $this->_data['stockStatuses'] = $this->model_localisation_stock_status->getStockStatuses();

        $this->_data['attributes'] = $this->model->getAttributes();
        
        $this->_data['filters'] = $this->model->getFilters();
        
	    $this->_data['options'] = $this->model->getOptions();
        
        $this->_data['categories'] = $this->model->getCategories();
        $this->_data['category_layouts'] = $this->model->detectCategoryLayouts();
        
        $this->_data['defaultLayout'] = $this->defaultLayoutSettings;
        $this->_data['defaultLayout']['layout_id'] = $this->model->getDefaultLayout();

        $this->_data['possible_controls'] = array(
            'price' => array(
                'slider' => $this->_data['lang']->slider,
                'slider_lbl' => $this->_data['lang']->slider_labels_only,
                'slider_lbl_inp' => $this->_data['lang']->slider_labels_and_inputs
            ),
            'manufacturer' => array(
                'checkbox' => $this->_data['lang']->checkbox,
                'radio' => $this->_data['lang']->radio,
                'select' => $this->_data['lang']->selectbox,
            ),
            'category' => array(
                'checkbox' => $this->_data['lang']->checkbox,
                'radio' => $this->_data['lang']->radio,
                'select' => $this->_data['lang']->selectbox,
            ),
        );

	    $layoutsArr = $this->model_design_layout->getLayouts();
        $layouts = array();
        foreach ($layoutsArr as $l) {
            $layouts[$l['layout_id']] = $l['name'];
        }
        $this->_data['layouts'] = $layouts;
        
        $settings = $this->_applySettings();

        $isFirstLaunch = !$this->config->get('brainyfilter_layout_basic');
        $this->_data['isFirstLaunch'] = $isFirstLaunch ? 'true' : 'false';
        $this->_data['basicSettings'] = !$isFirstLaunch ? $this->config->get('brainyfilter_layout_basic') : array();
        
        $modSettings = $settings[$moduleId === 'new' ? 'basic' : $moduleId];
        
        $this->_data['filterBlocks'] = array();
        foreach ($modSettings['behaviour']['sections'] as $section => $set) {
            $item = array(
                'label'     => $this->language->get("filter_{$section}"),
                'name'      => $section,
            );
            if (isset($set['control'])) {
                $item['control'] = $set['control'];
            }
            $sortOrder = $modSettings['behaviour']['sort_order'][$section];
            $this->_data['filterBlocks'][$sortOrder] = $item;
        }
        ksort($this->_data['filterBlocks']);

        $this->_data['layoutsCount'] = count($settings) - 1;
        
        $this->_data['currentModule'] = false;
        
        if ($moduleId !== 'basic' && $moduleId !== 'new') {
            $curModule = $this->model_setting_module->getModule($moduleId);
            $this->_data['currentModule'] = (int)$curModule['bf_layout_id'];
        }
        
        unset($modSettings['module_id']);
        $this->_data['settings'] = $modSettings;
        
        $this->_data['moduleId'] = $moduleId;
        $this->_data['isNewInstance'] = $moduleId === 'new';
        $this->_data['modules'] = $this->model_setting_module->getModulesByCode('brainyfilter');
        
        $this->_data['languages'] = $this->getLanguages();
        $this->_data['catalogUrl'] = defined('HTTPS_CATALOG') ? HTTPS_CATALOG : HTTP_CATALOG;

        $this->_data['header'] = $this->load->controller('common/header');
		$this->_data['column_left'] = $this->load->controller('common/column_left');
		$this->_data['footer'] = $this->load->controller('common/footer');
				
		$this->response->setOutput($this->load->view('extension/module/brainyfilter', $this->_data));
    }
    
    public function getLanguages()
    {
        $this->load->model('localisation/language');
        $langs = $this->model_localisation_language->getLanguages();
        if (!is_array($langs) || !count($langs)) {
            return array();
        }
        foreach ($langs as $i => $lang) {
            if (version_compare(VERSION, '2.2.0.0', '<')) {
                $langs[$i]['image_path'] = "view/image/flags/{$lang['image']}";
            } else {
                $langs[$i]['image_path'] = "language/{$lang['code']}/{$lang['code']}.png";
            }
        }
        return $langs;
    }
    
    private function _getRequestParam($name, $default = null)
    {
        if (isset($this->request->get[$name])) {
            return $this->request->get[$name];
        }
        return $default;
    }

    public function refresh()
    {
        $this->model->fillTaxRateTable();
        $this->model->fillCacheTable();
        
        die('done');
    }
    
    public function attributeValues()
    {

        // returns atribute values
        if (isset($this->request->get['attr_id'])) {
            $values = $this->model->getAttributeValues($this->request->get['attr_id']);
            die(json_encode($values) );
        }
        
        // modifies sort order
        if (isset($this->request->post['sort_order'])) {
            $this->model->changeAttrValuesSortOrder($this->request->post['sort_order']);
            die('done');
        }

    }
    
    public function removeInstance()
    {
        $moduleId = $this->_getRequestParam('module_id');
        
        if ($moduleId && $moduleId !== 'basic' && $moduleId !== 'new') {
            $this->load->model('setting/setting');

            $settings = $this->model_setting_setting->getSetting('brainyfilter');

            if (isset($settings['brainyfilter_layout_' . $moduleId])) {
                unset($settings['brainyfilter_layout_' . $moduleId]);
            }
            $this->model_setting_setting->editSetting('brainyfilter', $settings);
            
            $this->load->model('setting/module');
            $this->model_setting_module->deleteModule($moduleId);
        }
        
        $this->response->redirect($this->_link('extension/module/brainyfilter'));
    }
    
    private function _parsePostData() {
        if(get_magic_quotes_gpc()){
            $this->request->post['bf'] = stripslashes($this->request->post['bf']);
        }
        $json = str_replace('&quot;', '"', $this->request->post['bf']);
        $data = json_decode($json, true);
        return $data;
    }
    
	/**
	 * Data validation
	 * The method validates the given POST data
	 * @todo Implement the method
	 * @return boolean
	 */
    private function _validate($data) {
        if (!$this->user->hasPermission('modify', 'extension/module/brainyfilter')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
        
        if (!$this->error) {
			return true;
		} else {
			return false;
		}	
    }
    
    private function _switchAttributeMode($data) 
    {
        // regeneration of cache tables if attribute mode was switched
        $conf = $this->config->get('brainyfilter_layout_basic');
        if (!$conf) {
            return;
        }
        $multiMode = $data['global']['multiple_attributes'];
        $isFirstEnabling = !$conf && $multiMode;
        $modeChanged = $conf && $multiMode != $conf['global']['multiple_attributes'];
        if ($modeChanged || $isFirstEnabling) {
            if ($multiMode) {
                $separator = $data['global']['attribute_separator'];
                $this->model->attrSeparator = $separator;
            } else {
                $this->model->attrSeparator = false;
            }
            $this->model->fillCacheTable();
        }
    }
    
    private function _saveSettings($data) 
    {
        $this->load->model('setting/setting');
        $this->load->language('extension/module/brainyfilter');
        $moduleId = $data['module_id'];
        
        $data['behaviour']['containerSelector'] = html_entity_decode($data['behaviour']['containerSelector']);
        $data['behaviour']['paginatorSelector'] = html_entity_decode($data['behaviour']['paginatorSelector']);
        
        if ($moduleId === 'basic') 
        {
            $this->_switchAttributeMode($data);
        } 
        else 
        {
            $data = $this->_removeDefaultSettings($data);
            /** @todo refactor these hardcoded options below **/
            if (isset($data['behaviour']['attribute_groups']) && $data['behaviour']['attribute_groups'] == 2) {
                unset($data['behaviour']['attribute_groups']);
            }
            if (isset($data['behaviour']['product_count']) && $data['behaviour']['product_count'] == 2) {
                unset($data['behaviour']['product_count']);
            }
            if (isset($data['behaviour']['hide_empty']) && $data['behaviour']['hide_empty'] == 2) {
                unset($data['behaviour']['hide_empty']);
            }
            if (isset($data['submission']['hide_panel']) && $data['submission']['hide_panel'] == 2) {
                unset($data['submission']['hide_panel']);
            }
            if (isset($data['responsive']['collapsed']) && $data['responsive']['collapsed'] == 2) {
                unset($data['responsive']['collapsed']);
            }

            $layoutsArr = $this->model_design_layout->getLayouts();
            $layouts = array();
            foreach ($layoutsArr as $l) {
                $layouts[$l['layout_id']] = $l['name'];
            }
            $module = array(
                'layout_id'  => $data['layout_id'],
                'position'   => $data['layout_position'],
                'sort_order' => $data['layout_sort_order'],
                'status'     => $data['layout_enabled'],
                'bf_layout_id' => $moduleId,
                'name'       => $layouts[$data['layout_id']] . ' / ' . $this->language->get('instance_' . $data['layout_position']),
            );
            
            $this->load->model('setting/module');
            if ($moduleId === 'new') {
                $this->model_setting_module->addModule('brainyfilter', $module);
                $moduleId = $this->db->getLastId();
            } else {
                $this->model_setting_module->editModule($moduleId, $module);
            }
            
            
            if ($data['layout_enabled']) {
                $lModule = array(
                    'layout_id' => $data['layout_id'],
                    'code' => 'brainyfilter.' . $moduleId,
                    'position' => $data['layout_position'],
                    'sort_order' => $data['layout_sort_order']
                );
                
                $this->model->editLayoutModule($lModule);
            } else {
                $this->model->deleteLayoutModule($moduleId);
            }
            
            if (empty($data['behaviour']['containerSelector'])) {
                unset($data['behaviour']['containerSelector']);
            }
            if (empty($data['behaviour']['paginatorSelector'])) {
                unset($data['behaviour']['paginatorSelector']);
            }
        }
        
        $settings = array();
        $settings['brainyfilter_layout_' . $moduleId] = $data;
        
        $fullSettings = $this->model_setting_setting->getSetting('brainyfilter');
        $this->model_setting_setting->editSetting('brainyfilter', array_merge($fullSettings, $settings));
        
        return $moduleId;
    }
	
    private function _removeDefaultSettings($arr)
    {
        if (is_array($arr)) {
            if ((isset($arr['enabled']) && $arr['enabled'] === '2') 
                || (isset($arr['enable_all']) && $arr['enable_all'] === '2') ) {
                $arr = null;
            } else {
                foreach ($arr as $k => $val) {
                    $arr[$k] = $this->_removeDefaultSettings($arr[$k]);
                    if (is_null($arr[$k])) {
                        unset($arr[$k]);
                    }
                }
            }
        } elseif ($arr === 'default') {
            $arr = null;
        } 
        if(is_array($arr) && isset($arr['default']) && $arr['default'] == 1){
            if (isset($arr['val'])) {
               unset($arr['val']);
            }  
        }
        return $arr;
    }

    private function _applyBasicLanguageValues(&$arr)
    {

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        foreach ($languages as  $value) {
            if (!isset($arr['basic']['behaviour']['filter_name'][$value['language_id']])) {
               $arr['basic']['behaviour']['filter_name'][$value['language_id']] = 'Brainy Filter';
            }
        }        
    }
    
    private function _applyBasicSettings($arr, $basic)
    {
        foreach ($basic['behaviour']['sections'] as $section => $set) {
            if (!isset($arr['behaviour']['sections'][$section])) {
                $set['enabled'] = '2';
                $arr['behaviour']['sections'][$section] = $set;
            }
        }
        if (!isset($arr['behaviour']['limit_height'])) {
            $arr['behaviour']['limit_height'] = $basic['behaviour']['limit_height'];
            $arr['behaviour']['limit_height']['enabled'] = '2';
        }
        if (!isset($arr['behaviour']['limit_items'])) {
            $arr['behaviour']['limit_items'] = $basic['behaviour']['limit_items'];
            $arr['behaviour']['limit_items']['enabled'] = '2';
        }
//        if (!isset($arr['behaviour']['sort_order'])) {
//            $arr['behaviour']['sort_order'] = $basic['behaviour']['sort_order'];
//            $arr['behaviour']['sort_order']['enabled'] = 'default';
//        }
        if (!isset($arr['submission']['submit_type'])) {
            $arr['submission']['submit_type'] = 'default';
            $arr['submission']['submit_button_type'] = $basic['submission']['submit_button_type'];
            $arr['submission']['submit_delay_time'] = $basic['submission']['submit_delay_time'];
        }
        if (!isset($arr['style']['responsive'])) {
            $arr['style']['responsive'] = $basic['style']['responsive'];
            $arr['style']['responsive']['enabled'] = '2';
            $arr['style']['responsive']['collapsed'] = '2';
        }
        
        return $arr;
    }
    
	/**
	 * Apply Settings
	 *
	 * @return extension settings 
	 */
    private function _applySettings() 
    {
        $this->default = $this->_adjustSettingsForTheme($this->default);
        
        if (isset($this->request->post['bf']) && is_array($this->request->post['bf'])) {
            $moduleId = $this->request->post['bf']['module_id'];
            $this->default[$moduleId] = self::_arrayReplaceRecursive($this->default['basic'], $this->request->post['bf']);
            return $this->default;
        } else {
            $this->_applyBasicLanguageValues($this->default);
            $settings = array();
            if ($this->config->get('brainyfilter_layout_basic')) {
                $settings['basic'] = $this->config->get('brainyfilter_layout_basic');
            }
            $enabledModules = $this->model->getEnabledLayoutModules();
            
            $this->load->model('setting/module');
            $allModules = $this->model_setting_module->getModulesByCode('brainyfilter');
            
            $defaultSettings = array();
            foreach ($allModules as $module) {
                $moduleId = $module['module_id'];
                $set = $this->config->get('brainyfilter_layout_' . $moduleId);
                $settings[$moduleId] = $this->_applyBasicSettings($set, $settings['basic']);
                // force disabling the modules which were removed from the Design admin section
                // exception: the default instance
                if (!in_array($moduleId, $enabledModules)) {
                    $settings[$moduleId]['layout_enabled'] = 0;
                }
                $defaultSettings[$moduleId] = self::_arrayReplaceRecursive($this->default['basic'], $this->defaultLayoutSettings);
            }
            if (!empty($settings)) {
                $defaultSettings = self::_arrayReplaceRecursive($this->default, $defaultSettings);
                return self::_arrayReplaceRecursive($defaultSettings, $settings);
            }
        }
        return $this->default;
    }

	/**
	 * Set Up Language variables
	 * 
	 * @return void
	 */
	private function _setupLanguage()
	{
        $lang = $this->load->language('extension/module/brainyfilter');
        $langObj = new stdClass();

        if (count($lang)) {
            foreach ($lang as $var => $val) {
                $langObj->$var = $val;
            }
        }
        // language variables from other files
		$langObj->yes = $this->language->get('text_yes');
		$langObj->no  = $this->language->get('text_no');
        $this->_data['lang'] = $langObj;
	}
    
    /**
     * An alternative of PHP native function array_replace_recursive(), which is designed
     * to bring similar functionality for PHP versions lower then 5.3. <br>
     * <b>Note</b>: unlike PHP native function the method holds only two arrays as parameters.
     * @param array $array An original array
     * @param array $array1 Replacement
     * @return array
     */
    private static function _arrayReplaceRecursive($array, $array1)
    {
        foreach ($array1 as $key => $value) {
            if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))) {
                $array[$key] = array();
            }

            if (is_array($value)) {
                $value = self::_arrayReplaceRecursive($array[$key], $value);
            }
            $array[$key] = $value;
        }
        return $array;
    }
    
    public function ocmodManager()
    {
        $this->load->model('setting/modification');
        
        if (isset($this->request->post['bf'])) {
            $data = array(
                'status' => (int)$this->request->post['bf']['enabled'],
                'xml' => html_entity_decode($this->request->post['bf']['xml'])
            );
            $this->model->updateMod($data);
            $this->response->redirect($this->_link('extension/module/brainyfilter/ocmodManager'));
        }
        
        $this->_setupLanguage();
		$this->document->setTitle($this->language->get('heading_title'));
        
        $isMijoShop = class_exists('MijoShop') && defined('JPATH_MIJOSHOP_OC');
		if ($isMijoShop) {
			$this->document->addStyle('admin/view/stylesheet/brainyfilter.css');
		} else {
			$this->document->addStyle('view/stylesheet/brainyfilter.css');
		}
        $this->_data['heading_title'] = $this->language->get('heading_title');
        
        if (isset($this->session->data['success'])) {
			$this->_data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->_data['success'] = '';
		}
        
        $this->_data['header'] = $this->load->controller('common/header');
		$this->_data['column_left'] = $this->load->controller('common/column_left');
		$this->_data['footer'] = $this->load->controller('common/footer');
        $this->_data['modRefreshAction'] = $this->_link('extension/module/brainyfilter/modRefreshTrigger');
        
        $mod = $this->model_setting_modification->getModificationByCode('brainyfilter');
        $this->_data['enabled'] = (bool)$mod['status'];
        $this->_data['xml'] = htmlentities($mod['xml']);
				
		$this->response->setOutput($this->load->view('extension/module/brainyfilter_ocmod', $this->_data));
    }

    /**
     * enable/disable OCMOD modification
     * The method triggers the refresh function (setting/modification/refresh)
     * 
     */
    public function modRefreshTrigger()
    {
        if (isset($this->request->post['enable'])) {
            $enable = $this->request->post['enable'] === 'true';
        } else {
            $enable = true;
        }
        if ($this->model->enableMod($enable)) {
            $this->response->redirect($this->_link('setting/modification/refresh'));
        }
    }
    
    protected function _adjustSettingsForTheme($settings) {
        $theme = $this->config->get('config_template');
        
        if ( preg_match('/^default/i', $theme) ) {
            $set = array(
                'containerSelector' => '#content .row:nth-last-of-type(2)',
                'paginatorSelector' => '#content .row:nth-last-of-type(1)',
            );
        } elseif ( preg_match('/^journal/i', $theme) ) {
            $set = array(
                'containerSelector' => '.main-products.product-list, .main-products.product-grid',
                'paginatorSelector' => '.pagination',
            );
        } elseif (preg_match('/^shoppica/i', $theme)) {
            $set = array(
                'containerSelector' => '#listing_options + .clear + .s_listing',
                'paginatorSelector' => '.pagination',
            );
        } elseif (preg_match('/^sellegance/i', $theme)) {
            $set = array(
                'containerSelector' => '.product-filter + .row',
                'paginatorSelector' => '.pagination',
            );
        } elseif (preg_match('/^pavilion/i', $theme)) {
            $set = array(
                'containerSelector' => '.tb_products',
                'paginatorSelector' => '.pagination',
            );
        } else {
            /* Most spread set of selectors. 
             * Themes which have such selectors:
             * oxy, aquacart, bigshop, sellya, beautyshop, pav_fashion, megashop
             */
            $set = array(
                'containerSelector' => '.product-list, .product-grid',
                'paginatorSelector' => '.pagination, .paging',
            );
        }
        
        $settings['basic']['behaviour'] = array_merge($settings['basic']['behaviour'], $set);
        
        return $settings;
    }
    
    protected function _link($path, $getPar = '') {
        return $this->url->link($path, 'user_token=' . $this->session->data['user_token'] . $getPar, 'SSL');
    }
}