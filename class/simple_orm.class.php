<?php

use UKMNorge\Database\SQL\Delete;
use UKMNorge\Database\SQL\Insert;
use UKMNorge\Database\SQL\Query;
use UKMNorge\Database\SQL\Update;

abstract class simple_orm {
	var $table_fields = array();
	var $table_name = false;
	var $table_idcol = 'id';
	
	public function __construct( $id=false ) {
		$this->ID = $id;
		
		if( $id ) {
			$this->_load();
		}
	}
	
	private function trigger( $method ) {
		if( method_exists($this, $method ) )
			$this->$method();
	}
	
	public function set( $key, $val ) {
		$this->$key = $val;
	}
	
	public function update() {
		$sql = new Update($this->table_name, array($this->table_idcol => $this->ID));
		$this->_add_sql_values( $sql );
		$res = $sql->run();
		return $res != -1;
	}
	
	public function create( ) {
		$sql = new Insert( $this->table_name );
		$this->_add_sql_values( $sql );
		$res = $sql->run();
		
		$this->ID = $sql->insid();
	}
	
	public function delete( $pl_from ) {
		$sql = new Delete($this->table_name, array($this->table_idcol => $this->ID));
		$res = $sql->run();
		
		return false;
	}
	
	public function _add_sql_values( $sql ) {
		foreach( $this->table_fields as $key => $type ) {
			if( $type == 'int' )
				$sql->add( $key, (int)$this->$key );
			else
				$sql->add( $key, $this->$key );
		}
		return $sql;
	}
	
	
	public function _load() {
		$this->trigger('pre_load');
		$sql = new Query("SELECT * 
						FROM `#table`
						WHERE `#idcol` = '#ID'",
					array(	'table' => $this->table_name,
							'idcol' => $this->table_idcol,
							'ID' => $this->ID
						)
					);
		$sql->charset('UTF-8');
		$res = $sql->run();
		
		if( Query::numRows( $res ) == 0 ) {
			$this->trigger('post_load');
			return false;
		}
		
		$row = Query::fetch( $res );
		
		if( is_array( $row ) ) {
			foreach( $row as $key => $val ) {
				$this->$key = $val;
			}
		}
		$this->trigger('post_load');
		return true;
	}
}