<?php

use UKMNorge\Arrangement\Arrangement;

require_once('UKM/Autoloader.php');

require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/person.class.php');
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/rom.class.php');

$m = new Arrangement( intval(get_option('pl_id')) );
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'controller/overnatting_netter.controller.php');

$TWIG['person']['ankomst'] = date('d.m', $start->timestamp);
$TWIG['person']['avreise'] = date('d.m', $stop->timestamp);
$TWIG['person']['ID'] = 'ny';
$TWIG['person']['romtype'] = 'enkelt';
$TWIG['romtyper'] = UKMF_overnatting_getRomtyper();

die( json_encode( $TWIG ) );