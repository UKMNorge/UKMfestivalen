<?php
function UKMFestivalen_rapporter_sveveeksport() {
	global $objPHPExcel;

	$band_types = new SQL("SELECT * FROM `smartukm_band_type`");
	$band_types = $band_types->run();
	while($band_type = mysql_fetch_assoc($band_types))
		$bt[$band_type['bt_id']] = $band_type['bt_name'];
 
	UKM_loader('excel');
	echo '<h2>Excel-rapport for eksportering til sveve varsling</h2>';
	
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->getProperties()->setCreator('Wordpress UKM Norge');
	$objPHPExcel->getProperties()->setLastModifiedBy('Wordpress UKM Norge');
	$objPHPExcel->getProperties()->setTitle('UKM-Festivalen '.get_option('season').' SVEVEeksport');
	$objPHPExcel->getProperties()->setSubject('UKM-Festivalen '.get_option('season').' SVEVEeksport');
	$objPHPExcel->getProperties()->setKeywords('UKM Norge');

	## Sett standard-stil
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
	
	$ark = -1;

	$m = new monstring(get_option('pl_id'));
	$innslag = $m->innslag_btid();
	foreach($innslag as $band_type => $bands) {
#		echo '<h2>BT '.$bt[$band_type].' : ARK '. $ark.'</h2>';
		$ark++;
		$rad = 0;
		$rad++;
		$objPHPExcel->createSheet($ark);
		$objPHPExcel->setActiveSheetIndex($ark);
		$objPHPExcel->getActiveSheet()->setTitle(substr(utf8_encode($bt[$band_type]),0,8));
		excolwidth('A',30);
		excolwidth('B',13);
		excell('A'.$rad,utf8_encode('Navn'));
		excell('B'.$rad,utf8_encode('Nummer'));
		foreach($bands as $band) {
			$inn = new innslag($band['b_id']);
			$inn->videresendte($m->g('pl_id'));
			$deltakere = $inn->personer();
			foreach($deltakere as $deltaker) {
				$rad++;
#				echo 'RAD '.$rad.': '.$deltaker['p_firstname'].' '.$deltaker['p_lastname'].' - '.$deltaker['p_phone'].'<br />';
				excell('A'.$rad,$deltaker['p_firstname'].' '.$deltaker['p_lastname']);
				excell('B'.$rad,$deltaker['p_phone']);
			}
		}
	}
	$filnavn2 = date('dmyHis').'_UKM-Festivalen_'.$data['season'].'_SVEVEeksport.xls';
	$objPHPExcel->setActiveSheetIndex(0);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save(UKM_HOME.'../temp/phpexcel/'.$filnavn2);

	echo '<a href="http://ukm.no/temp/phpexcel/'.$filnavn2.'">'
		.'Last ned excelark for sveve'
		.'</a>';
}
?>