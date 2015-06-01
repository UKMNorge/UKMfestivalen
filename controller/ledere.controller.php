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

require_once('UKM/inc/excel.inc.php');
global $objPHPExcel;
exInit('Ledere på UKM-festivalen');
exSheetName( 'Ledere' );

excell('A1','Fylke');
excell('B1','Navn');
excell('C1','Nummer');
excell('D1','E-post');

$rad = 1;
while( $r = mysql_fetch_assoc( $res ) ) {
	$leder = new leder( $r['l_id'] );
	
	$rad++;
	
	excell('A'.$rad, $leder->kommer_fra);
	excell('B'.$rad, $leder->l_navn);
	excell('C'.$rad, $leder->l_mobilnummer);
	excell('D'.$rad, $leder->l_epost);
	
	$TWIG['ledere'][] = $leder;
}

$TWIG['excel'] = exWrite($objPHPExcel, 'UKMF_Ledere');