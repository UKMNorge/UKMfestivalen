<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/gruppe.collection.php');

$grupper = new grupper();
$grupper->load();

$TWIG['grupper'] = $grupper;