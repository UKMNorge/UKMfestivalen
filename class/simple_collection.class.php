<?php

abstract class simple_collection {
	var $table_name = false;
	var $table_idcol = 'id';
	var $object_type = false;
	var $filter = '';
	
	public function __construct(){
		if( method_exists( $this, 'initiate' ) ) {
			$this->initiate();
		}
	}
	
	public function filter( $filter ) {
		$this->filter = $filter;
	}
	
	public function reset() {
		$this->objects = null;
	}
	
	public function count() {
		return sizeof( $this->objects );
	}
	
	function load() {
		$ids = new SQL("SELECT `#id`
						FROM `#table`
						".$this->filter
						,
						array(	'id' => $this->table_idcol,
								'table' => $this->table_name
							)
						);
		$ids = $ids->run();
		while( $r = SQL::fetch( $ids ) ) {
			$this->objects[] = new $this->object_type( $r[ $this->table_idcol ] );
		}
	}
}