<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/person.class.php');
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/rom.class.php');

$person = new person_overnatting( $_POST['ID'] );
$person->delete( get_option('pl_id') );

die( json_encode( array('success' => true,
						'person' => $person
						)
				)
	);