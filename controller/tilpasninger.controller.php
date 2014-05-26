<?php
require_once( 'UKM/inc/excel.inc.php');

$sql = new SQL("SELECT *
				FROM `smartukm_videresending_infoskjema` AS `info`
				JOIN `smartukm_place` AS `place` ON (`info`.`pl_id_from` = `place`.`pl_id`)
				WHERE `info`.`pl_id` = '#place'
				ORDER BY `pl_name` ASC",
				array('place' => get_option('pl_id')) );
$res = $sql->run();

global $objPHPExcel;
$objPHPExcel = null;
exInit('Mat og tilrettelegging UKM-Festivalen');
exSheetName('Mat');

$objPHPExcel->createSheet(1);
$objPHPExcel->setActiveSheetIndex(1);
exSheetName('Tilrettelegging','f69a9b');


$objPHPExcel->setActiveSheetIndex(0);
$rad = 1;
excell('A'.$rad, 'Fylke','bold');
excell('B'.$rad, 'Ant vegetarianere','bold');
excell('C'.$rad, 'Ant søliaki','bold');
excell('D'.$rad, 'Ant svinekjøtt','bold');
excell('E'.$rad, 'Annet','bold');

$objPHPExcel->setActiveSheetIndex(1);
excell('A'.$rad, 'Fylke','bold');
excell('B'.$rad, 'Bevegelseshemninger','bold');
excell('C'.$rad, 'Annet','bold');


while( $r = mysql_fetch_assoc( $res ) ) {
	$objPHPExcel->setActiveSheetIndex(0);

	$rad++;
	excell('A'.$rad, (string) utf8_encode($r['pl_name']));
	excell('B'.$rad, $r['mat_vegetarianere']);
	excell('C'.$rad, $r['mat_soliaki']);
	excell('D'.$rad, (string) $r['mat_annet']);
	
	$objPHPExcel->setActiveSheetIndex(1);
	excell('A'.$rad, (string) utf8_encode($r['pl_name']));
	excell('B'.$rad, (string) $r['tilrettelegging_bevegelseshemninger']);
	excell('C'.$rad, (string) $r['tilrettelegging_annet']);
}

$TWIG['excel_tilpasninger'] = exWrite($objPHPExcel,'UKMF_Tilrettelegging_UKMFestivalen');