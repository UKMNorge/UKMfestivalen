<?php  
/* 
Plugin Name: UKM-Festivalen
Plugin URI: http://www.ukm-norge.no
Description: Inneholder funksjonalitet reservert for UKM-Festivalen
Author: UKM Norge / M Mandal 
Version: 1.0 
Author URI: http://www.ukm-norge.no
*/


if(is_admin()) {
	require_once('UKM/inc/twig-js.inc.php');

	global $blog_id;
	if(get_option('site_type')=='land') {
		add_action('UKM_admin_menu', 'UKMfestivalen_menu');
		add_action('wp_ajax_UKMfestivalen_ajax', 'UKMfestivalen_ajax');
	}
	define('PLUGIN_DIR_PATH_UKMFESTIVALEN', dirname(__FILE__).'/');
}

function UKMfestivalen_ajax() {
	require_once('ajax/'. $_POST['subaction'] .'.ajax.php');
	die();
}

## CREATE A MENU
function UKMfestivalen_menu() {
	global $UKMN;

	UKM_add_menu_page('content', 'Forsiden', 'Forsiden', 'administrator', 'UKMFforside', 'UKMFforside', 'http://ico.ukm.no/toy-blue-menu.png',0);
	UKM_add_scripts_and_styles( 'UKMFforside', 'UKMfestivalen_script' );

	UKM_add_menu_page('festivalen', 'Workshops', 'Workshops', 'administrator', 'UKMFworkshops', 'UKMFworkshops', 'http://ico.ukm.no/plant-menu.png',45);
	UKM_add_scripts_and_styles( 'UKMFworkshops', 'UKMfestivalen_script' );

	UKM_add_menu_page('festivalen', 'Overnatting', 'Overnatting', 'administrator', 'UKMFovernatting', 'UKMFovernatting', 'http://ico.ukm.no/hotel-menu.png',41);
	UKM_add_scripts_and_styles( 'UKMFovernatting', 'UKMfestivalen_script' );

	UKM_add_menu_page('festivalen', 'Økonomi', 'Økonomi', 'administrator', 'UKMFfaktura', 'UKMFfaktura', 'http://ico.ukm.no/excel-menu.png', 47);
	UKM_add_scripts_and_styles('UKMFfaktura', 'UKMfestivalen_script');
}

function UKMFforside() {
	$TWIG = array('ukm_hostname' => UKM_HOSTNAME );
	require_once('controller/forside.controller.php');
	echo TWIG('forside.twig.html', $TWIG, dirname(__FILE__), true);
}

## INCLUDE SCRIPTS
function UKMfestivalen_script() {
	wp_enqueue_script('handlebars_js');
	wp_enqueue_script('TwigJS');

	wp_enqueue_script('WPbootstrap3_js');
	wp_enqueue_style('WPbootstrap3_css');
	wp_enqueue_script( 'UKMfestivalen_script', plugin_dir_url( _FILE_ ) .'UKMfestivalen/ukmfestivalen.js');
	
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