<?php

class ModelExtensionModuleProductsInsert extends Model {
	public function add_product($product_id, $name, $description=NULL, $meta_description=NULL, $meta_keyword=NULL){
		$language_id = '1';
		$meta_title = $name;

		$sql  = "INSERT INTO `".DB_PREFIX."product_description` (`product_id`, `language_id`, `name`, `description`, `meta_title`, `meta_description`, `meta_keyword`) VALUES ";
		$sql .= "( $product_id, $language_id, '$name', '$description', '$meta_title', '$meta_description', '$meta_keyword' );";
		$this->db->query($sql);

	}
}