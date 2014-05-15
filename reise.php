<?php
function UKMF_reise_gui() {
	global $objPHPExcel;
	UKM_loader('excel');
	echo '<h2>Ankomsttider UKM-Festivalen '.get_option('season').'</h2>'
		.'<div id="loading_reise">Vennligst vent, laster data</div>'
		.'<br />';

	$objPHPExcel = new PHPExcel();

	$objPHPExcel->getProperties()->setCreator('Wordpress UKM Norge');
	$objPHPExcel->getProperties()->setLastModifiedBy('Wordpress UKM Norge');
	$objPHPExcel->getProperties()->setTitle('UKM-Festivalen '.get_option('season').' Ankomst');
	$objPHPExcel->getProperties()->setSubject('UKM-Festivalen '.get_option('season').' Ankomst');
	$objPHPExcel->getProperties()->setKeywords('UKM Norge');

	## Sett standard-stil
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
	$rad = 2;
	
	exsetcss(excell('A1:F1', 'Ankomst UKM-Festivalen '.get_option('season'),'grey'), 'h1');
	$rad++;
	excolwidth('A',11);
	excolwidth('B',17);
	excolwidth('C',10);
	excolwidth('D',6);
	excolwidth('E',12);
	excolwidth('F',38);
	
	excell('A'.$rad, 'Ankomsttid','grey');
	excell('B'.$rad, 'Fylke', 'grey');
	excell('C'.$rad, 'Reisemåte', 'grey');
	excell('D'.$rad, 'Antall', 'grey');
	excell('E'.$rad, 'Ankomstdato','grey');
	excell('F'.$rad, 'Kommentarer','grey');
	
	$qry = new SQL("SELECT `pl`.`pl_name`,
						   `pl`.`pl_id`,
						   `pl`.`pl_fylke`,
						   `i`.*
					FROM `smartukm_videresending_infoskjema` AS `i`
					JOIN `smartukm_place` AS `pl` ON (`pl`.`pl_id` = `i`.`pl_id_from`)
					WHERE `pl`.`season` = '#season'
					ORDER BY `i`.`reise_inn_mate` DESC,
					`pl`.`pl_name` ASC
					",
					array('season'=>get_option('season')));
	$res = $qry->run();

	$fargevalg = 0;
	$forrigefarge = '';
	while($r = mysql_fetch_assoc($res)) {
		$rad++;
		if($forrigefarge != $r['reise_inn_mate']) {
			$forrigefarge = $r['reise_inn_mate'];
			$fargevalg++;
		}
			
			
		$deltakere = $r['systemet_overnatting_spektrumdeltakere'] > $r['overnatting_spektrumdeltakere']
					? $r['systemet_overnatting_spektrumdeltakere'] 
					: $r['overnatting_spektrumdeltakere'];
					
		$kommentar = $r['reise_inn_samtidig']=='ja'
					? ''
					: $r['reise_inn_samtidig_nei'];
					
		excell('A'.$rad, utf8_encode($r['reise_inn_tidspunkt']), 'fontcolor_'.$fargevalg);
		excell('B'.$rad, utf8_encode($r['pl_name']), 'fontcolor_'.$fargevalg);
		excell('C'.$rad, utf8_encode($r['reise_inn_mate']), 'fontcolor_'.$fargevalg);
		excell('D'.$rad, $deltakere, 'fontcolor_'.$fargevalg);
		excell('E'.$rad, utf8_encode($r['reise_inn_dato']), 'fontcolor_'.$fargevalg);
		excell('F'.$rad, $kommentar, 'fontcolor_'.$fargevalg);
	}
	
	####################################################################################
	#### GENERER OG LAGRE EXCEL-FIL
	$filnavn = date('dmyHis').'_UKM-Festivalen_'.$data['season'].'_Ankomst.xlsx';
	$objPHPExcel->setActiveSheetIndex(0);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save(UKM_HOME.'../temp/phpexcel/'.$filnavn);
	
	unset($objPHPExcel);
	unset($objWriter);
	
	####################################################################################
	####################################################################################
	## UTREISE
	global $objPHPExcel;
	$objPHPExcel = new PHPExcel();
	
	$objPHPExcel->getProperties()->setCreator('Wordpress UKM Norge');
	$objPHPExcel->getProperties()->setLastModifiedBy('Wordpress UKM Norge');
	$objPHPExcel->getProperties()->setTitle('UKM-Festivalen '.get_option('season').' Ankomst');
	$objPHPExcel->getProperties()->setSubject('UKM-Festivalen '.get_option('season').' Ankomst');
	$objPHPExcel->getProperties()->setKeywords('UKM Norge');

	## Sett standard-stil
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
	$rad = 2;
	
	exsetcss(excell('A1:F1', 'Avreise UKM-Festivalen '.get_option('season'),'grey'), 'h1');
	$rad++;
	excolwidth('A',11);
	excolwidth('B',17);
	excolwidth('C',10);
	excolwidth('D',6);
	excolwidth('E',12);
	excolwidth('F',38);
	
	excell('A'.$rad, 'Avreisetid','grey');
	excell('B'.$rad, 'Fylke', 'grey');
	excell('C'.$rad, 'Reisemåte', 'grey');
	excell('D'.$rad, 'Antall', 'grey');
	excell('E'.$rad, 'Avreisedato','grey');
	excell('F'.$rad, 'Kommentarer','grey');
	
	$qry = new SQL("SELECT `pl`.`pl_name`,
						   `pl`.`pl_id`,
						   `pl`.`pl_fylke`,
						   `i`.*
					FROM `smartukm_videresending_infoskjema` AS `i`
					JOIN `smartukm_place` AS `pl` ON (`pl`.`pl_id` = `i`.`pl_id_from`)
					WHERE `pl`.`season` = '#season'
					ORDER BY `i`.`reise_inn_mate` DESC,
					`pl`.`pl_name` ASC
					",
					array('season'=>get_option('season')));
	$res = $qry->run();

	$fargevalg = 0;
	$forrigefarge = '';
	while($r = mysql_fetch_assoc($res)) {
		$rad++;
		if($forrigefarge != $r['reise_inn_mate']) {
			$forrigefarge = $r['reise_inn_mate'];
			$fargevalg++;
		}
			
			
		$deltakere = $r['systemet_overnatting_spektrumdeltakere'] > $r['overnatting_spektrumdeltakere']
					? $r['systemet_overnatting_spektrumdeltakere'] 
					: $r['overnatting_spektrumdeltakere'];
					
		$kommentar = $r['reise_ut_samtidig']=='ja'
					? ''
					: utf8_encode($r['reise_ut_samtidig_nei']);
					
		excell('A'.$rad, utf8_encode($r['reise_ut_tidspunkt']), 'fontcolor_'.$fargevalg);
		excell('B'.$rad, utf8_encode($r['pl_name']), 'fontcolor_'.$fargevalg);
		excell('C'.$rad, utf8_encode($r['reise_inn_mate']), 'fontcolor_'.$fargevalg);
		excell('D'.$rad, $deltakere, 'fontcolor_'.$fargevalg);
		excell('E'.$rad, utf8_encode($r['reise_ut_dato']), 'fontcolor_'.$fargevalg);
		excell('F'.$rad, $kommentar, 'fontcolor_'.$fargevalg);
	}
	
	####################################################################################
	#### GENERER OG LAGRE EXCEL-FIL
	$filnavn2 = date('dmyHis').'_UKM-Festivalen_'.$data['season'].'_Avreise.xlsx';
	$objPHPExcel->setActiveSheetIndex(0);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save(UKM_HOME.'../temp/phpexcel/'.$filnavn2);
	
	####################################################################################
	#### PRINT GUI
	echo '<div id="loaded_reise" style="display:none;">'
		.'<a href="http://ukm.no/temp/phpexcel/'.$filnavn.'">'
		.'Last ned excelark med ankomsttid- og m&aring;te'
		.'</a>'
		.'<br /><br />'
		.'<a href="http://ukm.no/temp/phpexcel/'.$filnavn2.'">'
		.'Last ned excelark med avreisetid- og m&aring;te'
		.'</a>'

		.'</div>'
		.'<script language="javascript" type="text/javascript">'
		."jQuery('#loading_reise').html(jQuery('#loaded_reise').html());"
		.'</script>'
		;
}
?>