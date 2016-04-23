<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/person.class.php');
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/rom.class.php');

$m = new monstring( get_option('pl_id') );
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'controller/overnatting_netter.controller.php');

$person = new person_overnatting( $_POST['ID'] );

$person->set('romtype', $person->rom->type );

$TWIG['person'] = $person;
$TWIG['romtyper'] = UKMF_overnatting_getRomtyper();

die( json_encode( $TWIG ) );