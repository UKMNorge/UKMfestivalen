<?php

use UKMNorge\Database\SQL\Query;

require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/simple_collection.class.php');
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/rom.class.php');
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/person.class.php');

class rom_collection extends simple_collection {
	var $table_name = 'ukm_festival_overnatting_rom';
	var $object_type = 'rom';
	
	public function load_by_available( $type = 'dobbelt' ) {
		$SQL = new Query("SELECT `rom`.*
						FROM `#table` AS `rom`
						WHERE `rom`.`kapasitet` > (SELECT COUNT(`rel`.`id`) 
													FROM `ukm_festival_overnatting_rel_person_rom` AS `rel` 
													WHERE `rel`.`rom_id` = `rom`.`id`
												  )
						AND `rom`.`type` = '#type'",
						array( 'table'	=> $this->table_name,
								'type'	=> $type
							)
					);
		$res = $SQL->run();
		
		while( $r = Query::fetch( $res ) ) {
			$this->objects[] = new $this->object_type( $r['id'] );
		}
	}
}