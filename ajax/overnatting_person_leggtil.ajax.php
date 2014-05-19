<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/person.class.php');
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/rom.class.php');

if( $_POST['person'] == 'ny' ) {
	$person = new person_overnatting();
} else {
	$person = new person_overnatting( $_POST['person'] );
}
$person->set('navn', $_POST['navn']);
$person->set('mobil', $_POST['mobil']);
$person->set('epost', $_POST['epost']);
$person->set('gruppe', $_POST['gruppe']);
$person->set('ankomst', $_POST['ankomst']);
$person->set('avreise', $_POST['avreise']);
if( $_POST['person'] == 'ny' ) {
	$person->create();
	$rom = false;
} else {
	$person->update();
}

if( $_POST['romtype'] == 'dobbel' ) {
	if( is_numeric( $_POST['dobbeltromID'] ) ) {
		$rom = new rom( $_POST['dobbeltromID'] );
	} else {
		$rom = new rom();
		$rom->set('type', 'dobbelt');
		$rom->set('kapasitet', 2);
		$rom->create();
	}
	$relate = $rom->relate( $person );
} else {
	if( isset( $person->rom ) && $person->rom->type == 'enkelt' ) { 
	} else {
		$rom = new rom();
		$rom->set('type','enkelt');
		$rom->set('kapasitet',1);
		$rom->create();
		$relate = $rom->relate( $person );
	}
}
// Last inn rom-innstillinger for personen
$person->post_load();

die( json_encode( array('success' => true,
						'person' => $person
						)
				)
	);