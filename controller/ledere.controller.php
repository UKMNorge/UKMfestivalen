<?php
require_once( PLUGIN_DIR_PATH_UKMFESTIVALEN.'../UKMvideresending_festival/class/leder.class.php' );

$ledere = new SQL("SELECT `l_id`,`sort`.`pl_name`
					FROM `smartukm_videresending_ledere_ny` AS `leder`
					LEFT JOIN `smartukm_place` AS `sort` ON (`sort`.`pl_id` = `leder`.`pl_id_from`)
					WHERE `pl_id_to` = '#pl_to'
					AND `leder`.`season` = '#season'
					ORDER BY `sort`.`pl_name` ASC
					",
				array(	'pl_to' => get_option('pl_id'),
						'season' => get_option('season'),
					)
				);
$res = $ledere->run();

while( $r = mysql_fetch_assoc( $res ) ) {
	$leder = new leder( $r['l_id'] );
	
	$TWIG['ledere'][] = $leder;
}