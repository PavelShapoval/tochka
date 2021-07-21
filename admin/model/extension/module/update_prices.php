<?php
class ModelExtensionModuleUpdatePrices extends Model {

	public function get_all_manufacturers(){
		$query = $this->db->query("SELECT DISTINCT `name`, `manufacturer_id` FROM " . DB_PREFIX . "manufacturer");
		return $query->rows;
	}

	public function select_products_by_manufacturer($manufacturer_id){
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
		$sql  = "REPLACE `".DB_PREFIX."prices_updates` SET `manufacturer_id`=".$manufacturer_id.", `percent` = ".$percent.", `side` = 'up'";
		$this->db->query($sql);
	}

	public function write_percent_up($product_id, $percent){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `percent_up` = ".$percent.", `percent_down` = '' WHERE `product_id` = $product_id";
		$this->db->query($sql);
	}

	public function write_percent_down($product_id, $percent){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `percent_up` = '', `percent_down` = ".$percent." WHERE `product_id` = $product_id";
		$this->db->query($sql);
	}

	public function write_updates_down($manufacturer_id, $percent){
		$sql  = "REPLACE `".DB_PREFIX."prices_updates` SET `manufacturer_id`=".$manufacturer_id.", `percent` = ".$percent.", `side` = 'down'";
		$this->db->query($sql);
	}

	public function clean_before_write($manufacturer_id){
		$sql  = "DELETE FROM `".DB_PREFIX."prices_updates` WHERE `manufacturer_id`=".$manufacturer_id;
		$this->db->query($sql);
	}

	public function disable_updates_clean_table(){
		$sql  = "DELETE FROM `".DB_PREFIX."prices_updates`";
		$this->db->query($sql);
	}

	public function check_temp_table(){
		$query = $this->db->query("SELECT `upc` , `quantity`, `price` FROM ".DB_PREFIX."temp_products WHERE `updated` != '' LIMIT 500 ");
		return $query->rows;
	}

	public function check_exist_rows(){
		$query = $this->db->query("SELECT count(id) FROM ".DB_PREFIX."temp_products");
		return $query->row;
	}

	public function check_status(){
		$query =  $this->db->query("SELECT `upc` , `quantity`, `price` FROM ".DB_PREFIX."temp_products");
		return $query->rows;
	}

	public function check_percents_table(){
		$query =  $this->db->query("SELECT `manufacturer_id` , `percent`, `side` FROM ".DB_PREFIX."prices_updates");
		return $query->rows;
	}

	public function get_manufacturer_name($manufacturer_id){
		$query = $this->db->query("SELECT `name` FROM ".DB_PREFIX."manufacturer WHERE `manufacturer_id` = ".$manufacturer_id);
		return $query->row;
	}



	/* module functions end */

	public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY cp.category_id) AS path FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function addCategory($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "category SET category_id = '" . (int)$data['id'] . "', parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()");

		$category_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");

		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		// Set which layout to use with this category
		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('category');

		return $category_id;
	}

	public function editCategory($category_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW() WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "'");

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE path_id = '" . (int)$category_id . "' ORDER BY level ASC");

		if ($query->rows) {
			foreach ($query->rows as $category_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' AND level < '" . (int)$category_path['level'] . "'");

				$path = array();

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_path['category_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");

					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$data['parent_id'] . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', level = '" . (int)$level . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "category_filter WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['category_filter'])) {
			foreach ($data['category_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_filter SET category_id = '" . (int)$category_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_store WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		// SEO URL
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'category_id=" . (int)$category_id . "'");

		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "category_to_layout WHERE category_id = '" . (int)$category_id . "'");

		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('category');
	}

	/**
	 * Getting category name by id
	 * @param Int
	 * @return String
	 */
	public function getCategoryNameById($category_id) {
		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "'");
		return $query->row;
	}

	/**
	 * Getting id of the attribute group by name
	 * @param String
	 * @return Int
	 */
	public function getIdAttributeGroupByName($name) {
		$query = $this->db->query("SELECT attribute_group_id FROM " . DB_PREFIX . "attribute_group_description WHERE name LIKE '" . $name . "'");
		return $query->row;
	}

	/**
	 * Getting id of the manufacturer by name
	 * @param String
	 * @return Int
	 */
	public function getIdManufacturerByName($name) {
		$query = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer WHERE name LIKE '" . $name . "'");
		return $query->row;
	}

	/**
	 * Getting id of the attribute by name
	 * @param String
	 * @return Int
	 */
	public function getIdAttributeByName($name) {
		$query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "attribute_description WHERE name LIKE '" . $name . "'");
		return $query->row;
	}

	/**
	 * Getting id of the product by model
	 * @param String
	 * @return Int
	 */
	public function getProductIdByModel($model) {
		$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE model LIKE '" . $model . "'");
		return $query->row;
	}

	public function add_product_desc($product_id, $language_id, $name, $description=NULL, $meta_description=NULL, $meta_keyword=NULL){

		$meta_title = $name;

		//$sql  = "INSERT INTO `".DB_PREFIX."product_description` (`product_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES ";
		//$sql .= "( $product_id, $language_id, '$name', '$description', '$meta_title', '$meta_description', '$meta_keyword' )";
		$sql  = "REPLACE INTO `".DB_PREFIX."product_description` (`product_id`, `language_id`, `name`, `meta_title`) VALUES ";
		$sql .= "($product_id, $language_id, \"$name\", \"$meta_title\")";

		$this->db->query($sql);

	}
	public function add_product_ids($product_id, $model, $upc, $quantity, $image, $price, $weight, $status){
		$sql  = "REPLACE INTO `".DB_PREFIX."product` (`product_id`, `model`, `upc`, `quantity`, `image`, `price`, `weight`, `status`) VALUES ";
		$sql .= "($product_id, \"$model\", \"$upc\", $quantity, \"$image\", $price, $weight, $status)";
		$this->db->query($sql);

	}
	public function add_product_sku($product_id,  $sku){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `sku` = $sku WHERE `product_id` = $product_id";
		$this->db->query($sql);

	}
	public function add_product_img($product_image_id, $product_id, $image){
		$sql  = "REPLACE INTO `".DB_PREFIX."product_image` (`product_image_id`, `product_id`, `image`, `sort_order`) VALUES ";
		$sql .= "($product_image_id, $product_id, \"$image\", 0)";
		$this->db->query($sql);
	}
	public function add_product_to_cat_test($product_id, $category_id){
		$sql  = "REPLACE INTO `".DB_PREFIX."product_to_category` (`product_id`, `category_id`) VALUES ";
		$sql .= "($product_id, $category_id)";
		$this->db->query($sql);
	}
	public function add_product_to_store($product_id, $store_id){
		$sql  = "REPLACE INTO `".DB_PREFIX."product_to_store` (`product_id`, `store_id`) VALUES ";
		$sql .= "($product_id, $store_id)";
		$this->db->query($sql);
	}
	public function add_product_quantity($upc, $quantity){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `quantity` = $quantity WHERE `upc` = $upc";
		$this->db->query($sql);
	}
	public function add_product_price($upc, $price){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `price` = $price WHERE `upc` = $upc";
		$this->db->query($sql);
	}
	public function add_product_items($id, $model, $SenderPrdCode, $quantity, $status){
		$sql  = "REPLACE INTO `".DB_PREFIX."product` (`product_id`, `model`, `upc`, `quantity`, `status`) VALUES ";
		$sql .= "($id, \"$model\", $SenderPrdCode, $quantity, $status)";
		$this->db->query($sql);
	}
	public function add_product_prices($id, $price){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `price` = $price WHERE `product_id` = $id";
		$this->db->query($sql);
		//"UPDATE " . DB_PREFIX . "category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'"
	}
	public function add_product_to_categories($id, $ProductGroup){
		$sql = "REPLACE INTO `".DB_PREFIX."category_description` (`name`, `meta_title`) VALUES";
		$sql .= "(\"$ProductGroup\", \"$ProductGroup\")";
		//$sql = "UPDATE ".DB_PREFIX."category_description SET `name` = \"$ProductGroup\" , `meta_title` = \"$ProductGroup\" WHERE `name` != \"$ProductGroup\"";
		$this->db->query($sql);
	}
	public function add_categories_names($cat_id, $ProductGroup){
		$sql = "REPLACE INTO `".DB_PREFIX."category_description` (`category_id`, `language_id`, `name`, `meta_title`) VALUES";
		$sql .= "($cat_id, 1, \"$ProductGroup\", \"$ProductGroup\")";
		$this->db->query($sql);
	}
	public function add_categories_ids($cat_id){
		$sql = "REPLACE INTO `".DB_PREFIX."category` (`category_id`, `parent_id`, `top`, `column`, `status`) VALUES";
		$sql .= "($cat_id, 0, 1, 1, 1)";
		$this->db->query($sql);
	}
	public function get_category_id($ProductGroup){
		$sql  = "SELECT `category_id` FROM `".DB_PREFIX."category_description` WHERE `name` = '$ProductGroup'";
		$category_id = $this->db->query($sql);
		return $category_id->row["category_id"];
	}
	public function set_product_to_category($id, $category_id){
		$sql  = "UPDATE `".DB_PREFIX."product_to_category` SET `category_id` = $category_id WHERE `product_id` = $id";
		$this->db->query($sql);
	}
	public function add_categories_path($category_id){
		$sql = "REPLACE INTO `".DB_PREFIX."category_path` (`category_id`, `path_id`, `level`) VALUES";
		$sql .= "($category_id, $category_id, 0)";
		$this->db->query($sql);
	}
	public function add_categories_to_store($category_id){
		$sql = "REPLACE INTO `".DB_PREFIX."category_to_store` (`category_id`, `store_id`) VALUES";
		$sql .= "($category_id, 0)";
		$this->db->query($sql);
	}
	public function add_manufacturers($name){
		$sql = "REPLACE INTO `".DB_PREFIX."test` (`name`) VALUES";
		$sql .= "(\"$name\")";
		$this->db->query($sql);
	}
	public function add_manufacturers_names($name){
		$sql = "REPLACE INTO `".DB_PREFIX."manufacturer` (`name`) VALUES";
		$sql .= "(\"$name\")";
		$this->db->query($sql);
	}
	public function add_manufacturers_desc($m_id, $name){
		$sql = "REPLACE INTO `".DB_PREFIX."manufacturer_description` (`manufacturer_id`,`description`) VALUES";
		$sql .= "($m_id, \"$name\")";
		$this->db->query($sql);
	}
	public function get_manufacturer_id($name){
		$query = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer WHERE name LIKE '" . $name . "'");
		return $query->row;
	}
	public function add_manufacturers_ids($id, $m_id){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `manufacturer_id` = $m_id WHERE `product_id` = $id";
		$this->db->query($sql);
	}
	public function add_manufacturers_ids_upc($upc, $m_id){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `manufacturer_id` = $m_id WHERE `upc` = $upc";
		$this->db->query($sql);
	}
	public function add_manufacturers_tostore($manufacturer_id){
		$sql = "REPLACE INTO `".DB_PREFIX."manufacturer_to_store` (`manufacturer_id`, `store_id`) VALUES";
		$sql .= "($manufacturer_id, 0)";
		$this->db->query($sql);
	}
	public function update_product_date($product_id, $opencartTime){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `date_modified` = $opencartTime WHERE `product_id` = $product_id";
		$this->db->query($sql);
	}

	public function add_property_width($id, $width){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `width` = $width, `date_modified` = CURDATE() WHERE `product_id` = $id";
		$this->db->query($sql);
	}
	public function add_property_height($id, $height){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `height` = $height, `date_modified` = CURDATE() WHERE `product_id` = $id";
		$this->db->query($sql);
	}
	public function add_datetime($upc, $date){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `date_modified` = CURDATE() WHERE `upc` = $upc AND `date_modified` != \"$date\"";
		$this->db->query($sql);
	}
	/*public function add_property_length($upc, $length){
		$sql  = "UPDATE `".DB_PREFIX."product` SET `length` = $length WHERE `upc` = $upc AND `length` = 0.00000000";
		$this->db->query($sql);
	}*/

	public function get_product_id($upc){
		$query = $this->db->query("SELECT `product_id` FROM " . DB_PREFIX . "product WHERE `upc` = '" . $upc . "'");
		return $query->row;
	}
	public function update_description($p_id, $description){
		$sql  = "UPDATE `".DB_PREFIX."product_description` SET `description` = \"$description\" WHERE `product_id` = $p_id AND `description` = ''";
		$this->db->query($sql);
	}
	public function get_products_ids_by_part_name($product_name_part){
		$query = $this->db->query("SELECT `product_id` FROM ".DB_PREFIX."product_description WHERE `name` LIKE '%$product_name_part%'");
		return $query->rows;
	}
	public function get_filter_id_by_part_name($product_name_part){
		$query = $this->db->query("SELECT `filter_id` FROM ".DB_PREFIX."filter_description WHERE `name` = '$product_name_part'");
		return $query->row;
	}
	public function get_category_ids_for_filters($product_id){
		$query = $this->db->query("SELECT `category_id` FROM ".DB_PREFIX."product_to_category WHERE `product_id` = '$product_id'");
		return $query->row;
	}
	public function set_filter_to_product($filter_id, $product_id){
		$sql  = "INSERT INTO`".DB_PREFIX."product_filter` SET `product_id` = $product_id, `filter_id` = $filter_id";
		$this->db->query($sql);
	}
	public function get_setted_filters($product_id, $filter_id){
		$query = $this->db->query("SELECT `filter_id` FROM ".DB_PREFIX."product_filter WHERE `product_id` = '$product_id' AND `filter_id` = '$filter_id'");
		return $query->row;
	}
	public function get_setted_filters_cats($cat_id, $filter_id){
		$query = $this->db->query("SELECT `filter_id` FROM ".DB_PREFIX."category_filter WHERE `category_id` = '$cat_id' AND `filter_id` = '$filter_id'");
		return $query->row;
	}
	public function set_filter_to_cat($filter_id, $cat_id){
		$sql  = "INSERT INTO`".DB_PREFIX."category_filter` SET `category_id` = $cat_id, `filter_id` = $filter_id";
		$this->db->query($sql);
	}
	public function get_setted_attr($product_id, $attr_id, $text){
		switch($text){
			case "бел.":
				$text = "Белый";
				break;
			case "сер.":
				$text = "Серый";
				break;
			case "красн.":
				$text = "Красный";
				break;
		}
		$query = $this->db->query("SELECT `attribute_id`, `text` FROM ".DB_PREFIX."product_attribute WHERE `product_id` = '$product_id' AND `attribute_id` = '$attr_id' AND `text` = '$text'");
		return $query->row;
	}
	public function get_attr_id_by_part_name($attr_name){
		$query = $this->db->query("SELECT `attribute_id` FROM ".DB_PREFIX."attribute_description WHERE `name` = '$attr_name'");
		return $query->row;
	}
	public function set_attr_to_product($product_id, $attr_id, $text){
		switch($text){
			case "бел.":
				$text = "Белый";
				break;
			case "сер.":
				$text = "Серый";
				break;
			case "красн.":
				$text = "Красный";
				break;
		}
		$sql  = "INSERT INTO`".DB_PREFIX."product_attribute` SET `product_id` = $product_id, `attribute_id` = $attr_id, `language_id` = 1, `text` = '$text'";
		$this->db->query($sql);
	}



}