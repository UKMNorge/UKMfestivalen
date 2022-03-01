<?php

use UKMNorge\Arrangement\Aktuelle;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Database\SQL\Query;
use UKMNorge\Geografi\Fylker;
use UKMNorge\Nettverk\Omrade;

require_once('UKM/Autoloader.php');

// require_once( 'UKM/inc/excel.inc.php');

// LAST INN ALLE FYLKER
	$ukmFestivalArrangement = new Arrangement( get_option( 'pl_id' ) );

	foreach($ukmFestivalArrangement->getVideresending()->getAvsendere() as $arrangAvsender) {
		$arrangement = $arrangAvsender->getArrangement();
		$fylke = $arrangement->getFylke();

		$TWIG['fylker'][] = array('fylkeName' => $fylke->getNavn(), 
								  'arrangementName' => $arrangement->getNavn(),
								  'festivalId' => $ukmFestivalArrangement->getId(),
								  'link' => $arrangement->getLink());
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