<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/rom.collection.php');
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/person.class.php');

$ledige = array();

if( $_POST['person'] == 'ny' ) {
	$selected_rom = false;
} else {
	$person = new person_overnatting( (int) $_POST['person'] );
	$selected_rom = $person->rom->ID;
	$person->rom->guests();
	$person->rom->selected=true;
	
	if( $person->rom->type == $_POST['romtype'] && $person->rom->personer->count() == $person->rom->kapasitet ) {
		$ledige = array( $person->rom );
	}
}

$rom = new rom_collection();
$rom->load_by_available( $_POST['romtype'] );
if( is_array( $rom->objects ) ) {
	foreach( $rom->objects as $current_room ) {
		if( $selected_rom == $current_room->ID ) {
			$current_room->selected = true;
		} else {
			$current_room->selected = false;
		}
		$current_room->guests();
	}
} else {
	$rom->objects = array();
}

die( json_encode(	array(	'success' => true,
							'ledige' => array_merge($ledige, $rom->objects),
							'type' => $_POST['romtype']
						)
				)
	);