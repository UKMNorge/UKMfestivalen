<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/person.class.php');
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/rom.class.php');

$person = new person_overnatting();
$person->set('navn', $_POST['navn']);
$person->set('mobil', $_POST['mobil']);
$person->set('epost', $_POST['epost']);
$person->set('gruppe', $_POST['gruppe']);
$person->create();

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
	$rom = new rom();
	$rom->set('type','enkelt');
	$rom->set('kapasitet',1);
	$rom->create();
	$relate = $rom->relate( $person );
}
// Last inn rom-innstillinger for personen
$person->post_load();

die( json_encode( array('success' => true,
						'person' => $person
						)
				)
	);