<?php

use UKMNorge\Arrangement\Aktuelle;
use UKMNorge\Arrangement\Arrangement;
use UKMNorge\Database\SQL\Query;
use UKMNorge\Geografi\Fylker;
use UKMNorge\Nettverk\Omrade;

require_once('UKM/Autoloader.php');

// require_once( 'UKM/inc/excel.inc.php');

// LAST INN ALLE FYLKER
	$TWIG['user_editor_role'] = false;
	if(wp_get_current_user()->roles[0] == 'editor') {
		$TWIG['user_editor_role'] = true;
	}

	$ukmFestivalArrangement = new Arrangement( get_option( 'pl_id' ) );

	foreach($ukmFestivalArrangement->getVideresending()->getAvsendere() as $arrangAvsender) {
		$arrangement = $arrangAvsender->getArrangement();
		$fylke = $arrangement->getFylke();

		$TWIG['fylker'][] = array('fylkeName' => $fylke->getNavn(), 
								  'arrangementName' => $arrangement->getNavn(),
								  'festivalId' => $ukmFestivalArrangement->getId(),
								  'link' => $arrangement->getLink());
	}
	
	$kommentarer = [];
	$arrangementer = [];
	$fylker = [];

	foreach(Fylker::getAll() as $fylke) {
		$fylker[$fylke->getId()] = $fylke;
	}
	
	$til = new Arrangement(get_option('pl_id'));	
	
	foreach($til->getVideresending()->getAvsendere() as $avsender) {
		$fra = $avsender->getArrangement();
		$arrangementer[$fra->getId()] = $fra;
		$kommentarer[$fra->getFylke()->getId()][$fra->getId()] = $fra->getMetaValue('kommentar_overnatting_til_' . $til->getId());
	}


	$TWIG['kommentarer'] = $kommentarer;
	$TWIG['arrangementer'] = $arrangementer;
	$TWIG['alle_fylker'] = $fylker;