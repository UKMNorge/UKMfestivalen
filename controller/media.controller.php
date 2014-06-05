<?php
require_once('UKM/monstring.class.php');
require_once('UKM/forestilling.class.php');

$m = new monstring( get_option('pl_id') );

if( isset( $_GET['c_id'] ) ) {
	require_once('media_download.controller.php');
	$VIEW = 'media_download';
} else {
	$TWIG['forestillinger'] = $m->forestillinger();
}