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
	UKM_add_menu_page('festivalen', 'Mediefiler', 'Mediefiler', 'administrator', 'UKMFmedia', 'UKMFmedia', 'http://ico.ukm.no/media-menu.png',40);
	UKM_add_scripts_and_styles( 'UKMFmedia', 'UKMfestivalen_script' );

	UKM_add_menu_page('festivalen', 'Overnatting', 'Overnatting', 'administrator', 'UKMFovernatting', 'UKMFovernatting', 'http://ico.ukm.no/hotel-menu.png',41);
	UKM_add_scripts_and_styles( 'UKMFovernatting', 'UKMfestivalen_script' );

	UKM_add_menu_page('festivalen', 'Reise', 'Reise', 'administrator', 'UKMFreise', 'UKMFreise', 'http://ico.ukm.no/buss-menu.png',42);
	UKM_add_scripts_and_styles( 'UKMFreise', 'UKMfestivalen_script' );

	UKM_add_menu_page('festivalen', 'Mat & behov', 'Mat & behov', 'administrator', 'UKMFtilpasninger', 'UKMFtilpasninger', 'http://ico.ukm.no/medical-case-menu.png',43);
	UKM_add_scripts_and_styles( 'UKMtilpasninger', 'UKMfestivalen_script' );

	UKM_add_menu_page('festivalen', 'Ledermiddag', 'Ledermiddag', 'administrator', 'UKMFmiddag', 'UKMFmiddag', 'http://ico.ukm.no/chef-menu.png',44);
	UKM_add_scripts_and_styles( 'UKMFreise', 'UKMfestivalen_script' );

}

## INCLUDE SCRIPTS
function UKMfestivalen_script() {
	wp_enqueue_script('handlebars_js');
	wp_enqueue_script('TwigJS');

	wp_enqueue_script('WPbootstrap3_js');
	wp_enqueue_style('WPbootstrap3_css');
#	wp_enqueue_style( 'UKMfestivalen_style', plugin_dir_url( _FILE_ ) .'UKMfestivalen/UKMfestivalen.css');
	wp_enqueue_script( 'UKMfestivalen_script', plugin_dir_url( _FILE_ ) .'UKMfestivalen/ukmfestivalen.js');
	
}

## SHOW STATS OF PLACES
function UKMfestivalen($VIEW) {
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

function UKMFreise() {
	UKMfestivalen('reise');
}
function UKMFtilpasninger() {
	UKMfestivalen('tilpasninger');
}
function UKMFmedia() {
	UKMfestivalen('media');
}
function UKMFmiddag() {
	UKMfestivalen('middag');
}




















/*
## HOOK MENU AND SCRIPTS
if(is_admin()) {
	if(get_option('site_type')!=='land')
		return;

	add_action('admin_menu', 'UKMFestivalen_old_menu');
	add_action('admin_init', 'UKMFestivalen_old_scriptsandstyles',1000);
}

add_action('wp_ajax_UKMV_rapport_fraktseddel', 'UKMV_rapport_fraktseddel_ajax');


function UKMFestivalen_old_scriptsandstyles() {
	wp_register_style( 'UKMFestivalen_css', WP_PLUGIN_URL .'/UKMVideresending/videresending.css');
	wp_register_style('zoombox_css','/wp-content/plugins/UKMvisitorpages/zoombox/zoombox.css');
	
	wp_enqueue_script('jqueryGoogleUI', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js');
	
// 	wp_register_script('jquery-ui-effects-core', 'http://ukm.no/wp-includes/js/jquery/ui/jquery.effects.core.min.js');
//	wp_register_script('shake', 'http://ukm.no/wp-includes/js/jquery/ui/jquery.effects.shake.min.js'); 
	wp_register_script('zoombox_js','/wp-content/plugins/UKMvisitorpages/zoombox/zoombox.js');
	wp_register_script('UKMFestivalen_js', WP_PLUGIN_URL .'/UKMVideresending/videresending.js');
	wp_register_script('UKMNorge_JQprint', WP_PLUGIN_URL .'/UKMNorge/js/jquery.print.js');
}
## CREATE A MENU
function UKMFestivalen_old_menu() {
	$page = add_menu_page('UKM-Festivalen', 'UKM-Festivalen OLD', 'editor', 'UKMFestivalen_old', 'UKMFestivalen_old', 'http://ico.ukm.no/star-menu.png',463);    
    $page_hotell = add_submenu_page('UKMFestivalen_old', 'Overnatting', 'Overnatting', 'editor', 'UKMF_overnatting','UKMF_overnatting');
    $page_kunst = add_submenu_page('UKMFestivalen_old', 'Reise', 'Reise', 'editor', 'UKMF_reise','UKMF_reise');
    $page_kunst = add_submenu_page('UKMFestivalen_old', 'Kunst', 'Kunst', 'editor', 'UKMF_kunst','UKMF_kunst');
    $page_faktura = add_submenu_page('UKMFestivalen_old', '&Oslash;konomi', '&Oslash;konomi', 'editor', 'UKMF_faktura','UKMF_faktura');
    $page_works = add_submenu_page('UKMFestivalen_old', 'Workshops', 'Workshops', 'editor', 'UKMF_workshops','UKMF_workshops');

    add_action( 'admin_print_styles-' . $page, 'UKMFestivalen_old_scriptsandstyles_print' );
    add_action( 'admin_print_styles-' . $page_hotell, 'UKMFestivalen_old_scriptsandstyles_print' );
    add_action( 'admin_print_styles-' . $page_faktura, 'UKMFestivalen_old_scriptsandstyles_print' );
	add_action( 'admin_print_styles-' . $page_kunst, 'UKMFestivalen_old_scriptsandstyles_print' );
	add_action( 'admin_print_styles-' . $page_works, 'UKMFestivalen_old_scriptsandstyles_print' );
}

function UKMFestivalen_old_scriptsandstyles_print() {
	wp_enqueue_style( 'UKMFestivalen_css');
	wp_enqueue_style('zoombox_css');
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('jquery-ui-droppable');
	wp_enqueue_script('jquery-ui-effects-core');
	wp_enqueue_script('zoombox_js');
	wp_enqueue_script('shake');
	wp_enqueue_script('UKMNorge_JQprint');
	wp_enqueue_script('UKMFestivalen_js');
}


function UKMFestivalen_old() {
	if(isset($_GET['rapport'])) {
		require_once($_GET['rapport'].'.php');
		$funksjon = 'UKMFestivalen_old_rapporter_'.$_GET['rapport'];
		return $funksjon();
	}
	$nav = new nav('UKM Norge', '');	

	### STATISTIKKVERKTØY
	$cell = new navCell('Overnatting','sleeping-shelter','');
	$cell->link('?page=UKMF_overnatting', 'G&aring; til overnatting');
	$nav->add($cell);


	$cell = new navCell('Kunst', 'art-supplies', '');
	$cell->link('?page=UKMF_kunst', 'G&aring; til kunst');
	$nav->add($cell);
		
	$cell = new navCell('Reise', 'buss', '');
	$cell->link('?page=UKMF_reise', 'G&aring; til reise');
	$nav->add($cell);
		
	$cell = new navCell('&Oslash;konomi', 'money', '');
	$cell->link('?page=UKMF_faktura', 'G&aring; til &oslash;konomi');
	$nav->add($cell);
	
	$cell = new navCell('Workshops', 'professor', '');
	$cell->link('?page=UKMF_workshops', 'G&aring; til workshops');
	$nav->add($cell);

	$cell = new navCell('SMS', 'mobile', '');
	$cell->link('?page=SVEVESMS_main', 'G&aring; til SMS-modulen');
	$cell->link('?page=UKMFestivalen&rapport=sveveeksport', 'Last ned eksportfil for sveve');
	$nav->add($cell);
	
	
	$cell = new navCell('Media','media','');
	$cell->link('?page='.$_GET['page'].'&rapport=media', 'Status opplastet media');
	$cell->link('?page='.$_GET['page'].'&rapport=media_visning', 'Vis faktisk opplastet media');
	$cell->link('?page='.$_GET['page'].'&rapport=media_visning&forestilling', 'Last ned bilder hovedprogram');
	$cell->link('?page='.$_GET['page'].'&rapport=media_film', 'Last ned original-filmer');

	$nav->add($cell);

	
	echo $nav->run();
}

function UKMF_faktura() {
	require_once('faktura.php');
	UKMF_faktura_gui();
}

function UKMF_kunst() {
	require_once('kunst.php');
	UKMF_kunst_gui();
}

function UKMF_overnatting() {
	require_once('overnatting.php');
	UKMF_overnatting_gui();
}
function UKMF_reise() {
	require_once('reise.php');
	UKMF_reise_gui();
}
function UKMF_workshops() {
	require_once('workshops.php');
	UKMF_workshops_gui();
}
*/
?>