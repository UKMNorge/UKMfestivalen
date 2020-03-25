<?php

use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Database\SQL\Query;

require_once('UKM/Autoloader.php');

require_once( 'UKM/inc/excel.inc.php');

// LAST INN ALLE FYLKER
	$fylker = new Query("SELECT `id`
					   FROM `smartukm_fylke`
					   ORDER BY `name` ASC");
	$fylker = $fylker->run();
	
	while($row = Query::fetch($fylker)) {
		$fylke = new fylke_monstring($row['id'], get_option('season'));
		$fylke = $fylke->monstring_get();
	
		if(!$fylke)	
			continue;
		if(!is_numeric($fylke->g('pl_id')) || $fylke->g('pl_id')==0)
			continue;
	
		$TWIG['fylker'][] = array('name' => $fylke->get('pl_name'),
								 'link' => $fylke->get('link'));
	}


	$overnatting = new Query("
		SELECT `pl_id_from`, `overnatting_kommentar`
		FROM `smartukm_videresending_infoskjema`
		WHERE `pl_id` = '#pl_to'
		",
		['pl_to' => get_option('pl_id') ]
	);
	$res = $overnatting->run();
	
	$kommentarer = [];
	while( $row = Query::fetch( $res ) ) {
		$fylke = new Arrangement( intval( $row['pl_id_from'] ));
		$kommentar = new stdClass();
		$kommentar->fylke = $fylke->getFylke()->getNavn();
		$kommentar->kommentar = stripslashes( $row['overnatting_kommentar'] );
		$kommentarer[ $kommentar->fylke ] = $kommentar;
	}
	
	ksort( $kommentarer );
	
	$TWIG['kommentarer'] = $kommentarer;