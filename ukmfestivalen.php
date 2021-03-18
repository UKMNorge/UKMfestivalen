<?php  
/* 
Plugin Name: UKM-Festivalen
Plugin URI: http://www.ukm-norge.no
Description: Inneholder funksjonalitet reservert for UKM-Festivalen
Author: UKM Norge / M Mandal 
Version: 1.0 
Author URI: http://www.ukm-norge.no
*/

// I juni og juli videresender vi alle
// nye besøkende til festivalsiden
if( in_array( (int)date('m'), [6,7]) ) {
	add_action('init', 'UKMredir_festival');
}

if(is_admin()) {
	require_once('UKM/inc/twig-js.inc.php');

	global $blog_id;
	if(get_option('site_type')=='land') {
		add_action('admin_menu', 'UKMfestivalen_menu');
		add_action('wp_ajax_UKMfestivalen_ajax', 'UKMfestivalen_ajax');
	}
	define('PLUGIN_DIR_PATH_UKMFESTIVALEN', dirname(__FILE__).'/');
}

function UKMredir_festival() {
	if($_SERVER['REMOTE_ADDR']=='81.0.146.162')
		return;
	if(function_exists('is_admin') && is_admin())
		return;
	if ( !session_id() )
		@session_start();

	if($_SERVER['REQUEST_URI']!=='/')
		return;

	global $blog_id;


	if($blog_id == 1 && !isset($_SESSION['UKM_forward_to_festivalen'])) {
		$_SESSION['UKM_forward_to_festivalen'] = true;
		header("Location: http://ukm.no/festivalen/");
		exit();
	}
}

function UKMfestivalen_ajax() {
	require_once('ajax/'. $_POST['subaction'] .'.ajax.php');
	die();
}

## CREATE A MENU
function UKMfestivalen_menu() {
	global $UKMN;

	$page = add_menu_page('Workshops', 'Workshops', 'administrator', 'UKMFworkshops', 'UKMFworkshops', '//ico.ukm.no/plant-menu.png',45);
	add_action( 'admin_print_styles-' . $page, 'UKMfestivalen_script' );	

	$page = add_menu_page('Overnatting', 'Overnatting', 'administrator', 'UKMFovernatting', 'UKMFovernatting', '//ico.ukm.no/hotel-menu.png',41);
	add_action( 'admin_print_styles-' . $page, 'UKMfestivalen_script' );

	$page = add_menu_page('Økonomi', 'Økonomi', 'administrator', 'UKMFfaktura', 'UKMFfaktura', '//ico.ukm.no/excel-menu.png', 47);
	add_action( 'admin_print_styles-' . $page, 'UKMfestivalen_script' );
}

## INCLUDE SCRIPTS
function UKMfestivalen_script() {
	wp_enqueue_script('handlebars_js');
	wp_enqueue_script('TwigJS');

	wp_enqueue_script('WPbootstrap3_js');
	wp_enqueue_style('WPbootstrap3_css');
	wp_enqueue_script( 'UKMfestivalen_script', PLUGIN_PATH .'UKMfestivalen/ukmfestivalen.js');
	
}

## SHOW STATS OF PLACES
function UKMfestivalen($VIEW) {
	if( !defined('EXCEL_WRITE_PATH') ) 
		define('EXCEL_WRITE_PATH', '/home/ukmno/public_subdomains/download/phpexcel/');

	define('ZIP_WRITE_PATH', '/home/ukmno/public_subdomains/download/zip/');

	$TWIG = array();	

	if( isset( $_GET['action'] ) ) {
		$VIEW = $VIEW.'_'.$_GET['action'];
	}
	require_once('controller/'. $VIEW .'.controller.php');
	
	$TWIG['tab_active'] = $VIEW;
	
	echo TWIG($VIEW .'.twig.html', $TWIG, dirname(__FILE__), true);
	echo TWIGjs( dirname(__FILE__) );
}

function UKMFovernatting() {
	UKMfestivalen('overnatting');
}

function UKMFworkshops() {
	UKMfestivalen('workshops');
}

function UKMF_overnatting_getRomtyper() {
	return array('enkelt'=>1, 'dobbelt'=>2, 'trippelt'=>3, 'kvadrupelt'=>4 );
}

function UKMFfaktura() {
	require_once('faktura.php');
	if( isset( $_GET['rapport'] ) ) {
		switch( $_GET['rapport'] ) {
			case 'krav':
				return okonomi_form();
			
			case 'konstanter':
				return UKMF_rapporter_konstanter();
				
			case 'okonomi':
				return UKMF_rapporter_okonomi();
		}
	} else {
		UKMfestivalen('okonomi_home');
	}	
}