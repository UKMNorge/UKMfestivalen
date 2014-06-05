<?php
require_once('UKM/monstring.class.php');
require_once('UKM/forestilling.class.php');

$m = new monstring( get_option('pl_id') );

if( isset( $_GET['c_id'] ) ) {
	$forestilling = new forestilling( $_GET['c_id'] );

	require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN .'../UKMvideresending_festival/controller/media.controller.php');
	
	var_dump( $TWIG['videresendte'] );
	
	$VIEW = 'media_download';
} else {
	$TWIG['forestillinger'] = $m->forestillinger();
}