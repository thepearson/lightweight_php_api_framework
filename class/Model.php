<?php
/**
 * Base Model class
 * @author craigp
 */
class Model {
	
	/**
	 * Object construct
	 * 
	 * @param unknown_type $key
	 */
	public function __construct($other = '') {

	}

	
	static public function fromArray($model, $obj) {
		return Model::fromStdClass($model, (object) $obj);
	}
	

	static public function fromStdClass($model, $obj) {
		$keys = array_keys(get_object_vars($obj));
		foreach ($keys as $key) {
			if (property_exists($obj, $key)) {
				$model->$key = $obj->$key;
			}
		}
		return $model;
	}

	
}