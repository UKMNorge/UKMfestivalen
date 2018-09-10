<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/simple_orm.class.php');

class rom extends simple_orm {
	var $table_fields = array('id' => 'int',
							  'type'=>'string',
							  'kapasitet'=>'int'
							 );
	var $table_name = 'ukm_festival_overnatting_rom';
	var $table_relations = 'ukm_festival_overnatting_rel_person_rom';
	
	public function post_load() {
		$this->navn = strtoupper($this->type[0]).$this->ID;
	}	
	public function load_by_person( $person ) {
		if( get_class( $person ) !== 'person_overnatting' )
			return false;
		
		$sql = new SQL("SELECT `rom_id`
						FROM `#table`
						WHERE `person_id` = '#person'",
					array('table'	=> $this->table_relations,
						  'person'	=> $person->ID
						  )
						);
		$ID = $sql->run('field','rom_id');
		if( is_numeric( $ID ) ) {
			$this->ID = $ID;
			$this->_load( );
			return true;
		}
		return false;
	}
	
	public function relate( $person ) {
		if( get_class( $person ) !== 'person_overnatting' ) {
			return false;
		}
		
		// Unrelate before relate
		$sqldel = new SQLdel($this->table_relations, array('person_id'=>$person->ID));
		$sqldel->run();
		
		$sql = new SQLins($this->table_relations);
		$sql->add('person_id', $person->ID);
		$sql->add('rom_id', $this->ID);
		$res = $sql->run();
		
		return true;
	}
	
	public function related( $person ) {
		$sql = new SQL("SELECT `id`
						FROM `#table'
						WHERE `person_id' = '#person'
						AND `rom_id' = '#rom'",
						array( 'table' 	=> $this->table_relations,
							   'person'	=> $person->ID,
							   'rom'	=> $this->ID
							 )
					);
		$res = $sql->run();
		if( SQL::fetch( $res ) == 0 )
			return false;
		return true;
	}
	
	public function guests() {
		require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/person.collection.php');

		$this->personer = new personer_overnatting();
		$this->personer->load_by_room( $this->ID );
	}
}