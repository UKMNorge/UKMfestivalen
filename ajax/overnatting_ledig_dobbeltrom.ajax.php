<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/rom.collection.php');

$rom = new rom_collection();
$rom->load_by_available( 'dobbelt' );
if( is_array( $rom->objects ) ) {
	foreach( $rom->objects as $current_room ) {
		$current_room->guests();
	}
}

die( json_encode(	array(	'success' => true,
							'ledige' => $rom->objects
						)
				)
	);