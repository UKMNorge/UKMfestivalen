<?php
require_once('UKM/monstring.class.php');
require_once('UKM/forestilling.class.php');

$TWIG['c_id'] = $_GET['c_id'];

$m = new monstring( get_option('pl_id') );

if( isset( $_GET['c_id'] ) || isset( $_GET['kunstnere'] ) ) {
	require_once('media_download.controller.php');
	
	if( isset( $_GET['zip'] ) ) {
		$VIEW = 'media_download_zip';
	} else {
		$VIEW = 'media_download';
	}
} else {
	$TWIG['forestillinger'] = $m->forestillinger();
}