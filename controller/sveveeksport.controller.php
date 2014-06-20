<?php
require_once('UKM/inc/excel.inc.php');

global $objPHPExcel;

$band_types = new SQL("SELECT * FROM `smartukm_band_type`");
$band_types = $band_types->run();
while($band_type = mysql_fetch_assoc($band_types))
	$bt[$band_type['bt_id']] = utf8_encode($band_type['bt_name']);

$m = new monstring(get_option('pl_id'));
$innslag = $m->innslag_btid();

foreach($innslag as $band_type => $bands) {

	$band_type_name = substr($bt[$band_type],0,8);
	
//	$objPHPExcel = null;
	exInit('Ledermiddag');
	exSheetName( $band_type_name );

	excolwidth('A',30);
	excolwidth('B',13);
	excell('A1','Navn');
	excell('B1','Nummer');
	
	$rad = 1;
	foreach($bands as $band) {
		$inn = new innslag($band['b_id']);
		$inn->videresendte($m->g('pl_id'));

		$deltakere = $inn->personer();
		foreach($deltakere as $deltaker) {
			$rad++;
			excell('A'.$rad,$deltaker['p_firstname'].' '.$deltaker['p_lastname']);
			excell('B'.$rad,$deltaker['p_phone']);
		}
	}
	
	$excelData = new stdClass();
	$excelData->name = $bt[$band_type];
	$excelData->url = exWrite($objPHPExcel, 'UKMF_Sveveksport_'.preg_replace('/[^A-Za-z0-9-.\/]/', '', $excelData->name));
	
	$TWIG['excel'][] = $excelData;
}

?>