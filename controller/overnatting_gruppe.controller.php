<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/gruppe.class.php');

$gruppe = new gruppe( $_GET['id'] );
$gruppe->personer();

$m = new monstring( get_option('pl_id') );

$TWIG['gruppe'] = $gruppe;

require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'controller/overnatting_netter.controller.php');
$TWIG['romtyper'] = UKMF_overnatting_getRomtyper();
#krsort($tilknytning_for);
$TWIG['person']['ankomst'] = date('d.m', $start->timestamp);
$TWIG['person']['avreise'] = date('d.m', $stop->timestamp);
$TWIG['person']['ID'] = 'ny';
$TWIG['person']['romtype'] = 'enkelt';

