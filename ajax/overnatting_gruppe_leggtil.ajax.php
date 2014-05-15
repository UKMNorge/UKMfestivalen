<?php
require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'class/gruppe.class.php');

$gruppe = new gruppe();
$gruppe->set('navn', $_POST['gruppe']);
$gruppe->create();

die( json_encode( array('success' => true,
						'gruppe' => $gruppe
						)
				)
	);