<?php
require_once( 'UKM/inc/excel.inc.php');

$sql = new SQL("SELECT *
				FROM `smartukm_videresending_infoskjema` AS `info`
				JOIN `smartukm_place` AS `place` ON (`info`.`pl_id_from` = `place`.`pl_id`)
				WHERE `info`.`pl_id` = '#place'
				ORDER BY `reise_inn_mate`, `reise_inn_dato` ASC",
				array('place' => get_option('pl_id') ));
$res = $sql->run();

global $objPHPExcel;
$objPHPExcel = null;
exInit('Reise til og fra UKM-Festivalen');
exSheetName('Ankomst');

$objPHPExcel->createSheet(1);
$objPHPExcel->setActiveSheetIndex(1);
exSheetName('Avreise','f69a9b');


$objPHPExcel->setActiveSheetIndex(0);
$rad = 1;
excell('A'.$rad, 'Ankomsttid','bold');
excell('B'.$rad, 'Fylke','bold');
excell('C'.$rad, 'Reisemåte','bold');
excell('D'.$rad, 'Ankomststed','bold');
excell('E'.$rad, 'Antall','bold');
excell('F'.$rad, 'Ankomstdato','bold');
excell('G'.$rad, 'Totalt ant deltakere','bold');
excell('H'.$rad, 'Alle samtidig?','bold');
excell('I'.$rad, 'Kommentarer','bold');

$objPHPExcel->setActiveSheetIndex(1);
excell('A'.$rad, 'Avreisetid','bold');
excell('B'.$rad, 'Fylke','bold');
excell('C'.$rad, 'Reisemåte','bold');
excell('D'.$rad, 'Antall','bold');
excell('E'.$rad, 'Avreisedato','bold');
excell('F'.$rad, 'Totalt ant deltakere','bold');
excell('G'.$rad, 'Alle samtidig?','bold');
excell('H'.$rad, 'Kommentarer','bold');


while( $r = mysql_fetch_assoc( $res ) ) {
	$objPHPExcel->setActiveSheetIndex(0);

	$rad++;
	excell('A'.$rad, (string) utf8_encode($r['reise_inn_tidspunkt']));
	excell('B'.$rad, (string) utf8_encode($r['pl_name']));
	excell('C'.$rad, (string) utf8_encode($r['reise_inn_mate']));
	excell('D'.$rad, 0);
	excell('E'.$rad, (string) utf8_encode($r['reise_inn_sted']));
	excell('F'.$rad, (string) utf8_encode($r['reise_inn_tidspunkt']));
	excell('G'.$rad, (string) utf8_encode($r['overnatting_spektrumdeltakere']));
	excell('H'.$rad, (string) utf8_encode($r['reise_inn_samtidig']));
	excell('I'.$rad, (string) utf8_encode($r['reise_inn_samtidig_nei']));
	
	$objPHPExcel->setActiveSheetIndex(1);
	excell('A'.$rad, (string) utf8_encode($r['reise_ut_tidspunkt']));
	excell('B'.$rad, (string) utf8_encode($r['pl_name']));
	excell('C'.$rad, (string) utf8_encode($r['reise_ut_mate']));
	excell('D'.$rad, 0);
	excell('E'.$rad, (string) utf8_encode($r['reise_ut_tidspunkt']));
	excell('F'.$rad, (string) utf8_encode($r['overnatting_spektrumdeltakere']));
	excell('G'.$rad, (string) utf8_encode($r['reise_ut_samtidig']));
	excell('H'.$rad, (string) utf8_encode($r['reise_ut_samtidig_nei']));
}

$TWIG['excel_reise'] = exWrite($objPHPExcel,'UKMF_Reise_UKMFestivalen');