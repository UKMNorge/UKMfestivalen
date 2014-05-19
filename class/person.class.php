<?php

require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/simple_orm.class.php');
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/rom.class.php');

class person_overnatting extends simple_orm {
	var $table_fields = array('id' => 'int',
							  'navn'=>'string',
							  'mobil'=>'int',
							  'epost'=>'string',
							  'gruppe'=>'int',
							  'ankomst'=>'string',
							  'avreise'=>'string'
							  );
	var $table_name = 'ukm_festival_overnatting_person';
	var $table_idcol = 'id';
	
	public function post_load() {
		$this->rom = new rom();
		$this->rom->load_by_person( $this );
	}
}