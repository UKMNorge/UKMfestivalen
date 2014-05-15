<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/simple_collection.class.php');
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/gruppe.class.php');

class grupper extends simple_collection {
	var $table_name = 'ukm_festival_overnatting_gruppe';
	var $object_type = 'gruppe';
}