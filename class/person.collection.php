<?php

use UKMNorge\Database\SQL\Query;

require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/simple_collection.class.php');
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/person.class.php');

class personer_overnatting extends simple_collection {
	var $table_name = 'ukm_festival_overnatting_person';
	var $object_type = 'person_overnatting';
	
	public function load_by_room( $ID ) {
		$SQL = new Query("SELECT `person_id`
						FROM `ukm_festival_overnatting_rel_person_rom`
						WHERE `rom_id` = '#rom'",
					array('rom' => $ID)
					);
		$res = $SQL->run();
		$this->reset();
		while( $r = Query::fetch( $res ) ) {
			$this->objects[] = new $this->object_type( $r['person_id'] );
		}
	}
}