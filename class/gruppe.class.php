<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/simple_orm.class.php');
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/person.collection.php');

class gruppe extends simple_orm {
	var $table_fields = array('id' => 'int',
							  'navn'=>'string');
	var $table_name = 'ukm_festival_overnatting_gruppe';
	var $table_idcol = 'id';


	public function personer() {
		$this->personer = new personer_overnatting();
		$this->personer->filter("WHERE `gruppe` = '".$this->ID."'");
		$this->personer->load();
	}
}