<?php
require_once('UKM/monstring.class.php');


if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
	$options = array('vis_deltakerinfo_mode_pre');
	
	foreach( $options as $post ) {
		if( isset( $_POST[ $post ] ) ) {
			update_option( $post, ($_POST[ $post ] === 'false' ? false : $_POST[ $post ] ));
		}
	}
}

$pl = new monstring( get_option('pl_id') );
$monstring = new stdClass();
$monstring->starter = $pl->g('pl_start');
$monstring->slutter = $pl->g('pl_stop');


$TWIG['modes'] = array();

$mode_pre = new stdClass();
$mode_pre->vis_deltakerinfo = get_option('vis_deltakerinfo_mode_pre');
$TWIG['modes']['pre'] = $mode_pre;


$TWIG['monstring'] = $monstring;