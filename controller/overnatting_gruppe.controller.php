<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/gruppe.class.php');

$gruppe = new gruppe( $_GET['id'] );
$gruppe->personer();

$TWIG['gruppe'] = $gruppe;