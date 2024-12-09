<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/gruppe.class.php');

$id = $_POST['ID'];

// Delete group
$gruppe = new gruppe($id);
$gruppe->delete(null);

// Delete all persons in group
$personerIGruppen = personer_overnatting::load_by_group($id);
foreach( $personerIGruppen as $person ) {
    $person->delete(null);
}

die( json_encode( array('success' => true,
						'gruppe' => $gruppe
						)
				)
	);