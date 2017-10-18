<?php
/* 
Part of: UKM Videresending rapporter :: okonomi
Description: Lager en excel-fil med data som skal brukes til fakturagrunnlag
Author: M Mandal
Version: 1.0

# OUTPUTFIL:		Full relativ bane til hvor filen skal lagres (INKL .xls)
# DOKUMENTNAVN: 	Navn på dokumentet, lagres internt i excel-filen
# DATAARRAY: 		All informasjon som skal inn, i følgende struktur DATA[ark_nummer][rad_nummer][kolonne_nummer] = data
# ARKNAVN:			Array med alle arknavn i typen DATA[i]=>navn
# CREATOR:			Lagres internt i excel-filen
# KEYWORDS:			Lagres internt i excel-filen
function createExcel($outputFil, $dokumentNavn, $dataArray, $arkNavn, $creator='UKM Norge', $keywords='UKM Norge') {

*/

function UKMF_rapporter_konstanter() {
	if(isset($_POST['lagre_konstanter'])) {
		foreach($_POST as $key => $val) {
			if(strpos($key,'option_')!==false) {
				update_ukm_option(str_replace('option_','',$key), $val);
			}
		}
	}

	$option_keys = array('faktura_reiseandel' => 'Beregnet UKM Norges andel i reiseoppgjøret',
						'faktura_kunstfrakt' => 'Budsjetterte utgifter kunstfrakt',
						'faktura_deltakeravgift' => 'Beregnet deltakeravgift',
						'kvote_ledere' => 'Lederkvote',
						'kvote_deltakere' => 'Deltakerkvote',
						'subsidiert_deltakeravgift' => 'Subsidiert deltakeravgift',
						'ordinar_deltakeravgift' => 'Ordinær deltakeravgift',
						'egenandel_reise' => 'Egenandel reise',
						'ledermiddag_avgift' => 'Avgift ledermiddag',
						'hotelldogn_pris' => 'Pris per døgn på hotell UKM Norge'
					);
	$options = array();
	foreach( $option_keys as $key => $name ) {
		$option = new stdClass();
		$option->name = $name;
		$option->key = $key;
		$option->value = get_ukm_option( $key );
		$options[] = $option;
	}
	$TWIG['options'] = $options;

	echo TWIG('okonomi_konstanter.twig.html', $TWIG, dirname(__FILE__), true);
}


function UKMF_rapporter_okonomi() {
	global $objPHPExcel;
	require_once('UKM/inc/excel.inc.php');
	echo '<h2>Fakturagrunnlag UKM-Festivalen '.$data['season'].'</h2>'
		.'<div id="loading_faktura">Vennligst vent, beregner fakturagrunnlag</div>'
		.'<br />';
		
	####################################################################################
	## INITIER PHPExcel-objekt og sett innstillinger
	$objPHPExcel = new PHPExcel();

	$objPHPExcel->getProperties()->setCreator('Wordpress UKM Norge');
	$objPHPExcel->getProperties()->setLastModifiedBy('Wordpress UKM Norge');
	$objPHPExcel->getProperties()->setTitle('UKM-Festivalen '.get_option('season').' Fakturagrunnlag');
	$objPHPExcel->getProperties()->setSubject('UKM-Festivalen '.get_option('season').' Fakturagrunnlag');
	$objPHPExcel->getProperties()->setKeywords('UKM Norge');

	## Sett standard-stil
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(12);

	####################################################################################
	## OPPRETT TOLKNINGS-ARKET
	$objPHPExcel->createSheet(1);
	## START MED OVERSIKTEN
	$objPHPExcel->setActiveSheetIndex(0);

	####################################################################################
	#### INNSTILLINGER FOR FYLKESARKENE
	$data['kvote'] = get_ukm_option('kvote_deltakere') + get_ukm_option('kvote_ledere');
	$data['subsidiert_deltakeravgift'] = get_ukm_option('subsidiert_deltakeravgift');
	$data['ordinar_deltakeravgift'] = get_ukm_option('ordinar_deltakeravgift');
	$data['ledermiddag_avgift'] = get_ukm_option('ledermiddag_avgift');
	$data['hotelldogn_pris'] = get_ukm_option('hotelldogn_pris');
	$data['season']	= get_option('season');
	$data['egenandel_reise'] = get_ukm_option('egenandel_reise');

	####################################################################################
	#### LOOP ALLE FYLKER, GENERER FYLKESARK
	$qry = new SQL("SELECT `pl`.`pl_name`,
						   `pl`.`pl_id`,
						   `pl`.`pl_fylke`,
						   `i`.`systemet_overnatting_spektrumdeltakere`,
						   `i`.`overnatting_spektrumdeltakere`,
						   `i`.`overnatting_hotelldogn`,
						   `i`.`faktura_krav`,
						   `i`.`faktura_trekk`,
						   `i`.`faktura_beskrivelse`
					FROM `smartukm_videresending_infoskjema` AS `i`
					JOIN `smartukm_place` AS `pl` ON (`pl`.`pl_id` = `i`.`pl_id_from`)
					WHERE `pl`.`season` = '#season'
					ORDER BY `pl`.`pl_name` ASC",
					array('season'=>get_option('season')));
	$res = $qry->run();
	$i = 1;
	$arkRef = array();
	while($r = mysql_fetch_assoc($res)) {
		$i++;
		$data['fylke'] = utf8_encode($r['pl_name']);
		
		// Totalt antall deltakere og ledere
		$spektrum = (int) $r['systemet_overnatting_spektrumdeltakere'];
#		$spektrum_led = (int) $r['overnatting_spektrumdeltakere'];
#		$spektrum = $spektrum_sys > $spektrum_led ? $spektrum_sys : $spektrum_led;
		
		// Ledermiddag
		$middag = new SQL("SELECT * FROM `smartukm_videresending_ledere_middag`
						   WHERE `pl_from` = '#plid'",
						  array('plid'=>$r['pl_id'], 'season'=>$data['season']));
		$middag = $middag->run('array');
		$ledermiddag = 0;
		if(!empty($middag['ledermiddag_fylke1']))
			$ledermiddag++;
		if(!empty($middag['ledermiddag_fylke2']))
			$ledermiddag++;

		// Hotell
		$hotelldogn = (int) $r['overnatting_hotelldogn'];

		if($spektrum > $data['kvote']) {
			$utover = $spektrum - $data['kvote'];
			$innenfor = $data['kvote'];
		} else {
			$utover = 0;
			$innenfor = $spektrum;
		}

		// KUNST
		# ØSTLANDET 
		if($r['pl_fylke'] < 7 || $r['pl_fylke'] == 19)
			$kunst = 1600;
		elseif($r['pl_fylke'] < 18 && $r['pl_fylke'] > 14)
			$kunst = 800;
		else
			$kunst = 2000;
			
		// BEREGN OG LAGRE FORNUFTIG ARK-NAVN
		$arknavn = $data['fylke'];
		$arknavn = preg_replace(array('/[^a-zA-Z0-9]/', '/[ -]+/', '/^-|-$/'),
								array('', '-', ''),
								$arknavn);
		$arknavn = strtoupper(substr($arknavn,0,5));
		$arknavn = $arknavn == 'STFOL' ? 'OSTFO' : $arknavn;
		$arknavn = $arknavn == 'MREOG' ? 'MOREO' : $arknavn;
		$arknavn = $arknavn == 'SRTRN' ? 'SORTR' : $arknavn;

		$arkRef[$arknavn] = $data['fylke'];
										
		// PAKK DATA
		$data['deltakere_og_ledere_innnenfor_kvoten'] = $innenfor;
		$data['deltakere_og_ledere_utover_kvoten'] = $utover;
		$data['ekstra_deltakere_ledermiddag'] = $ledermiddag;
		$data['antall_hotelldogn'] = $hotelldogn;
		$data['frakt_av_kunst'] = $kunst;
		$data['ark'] = $arknavn;
		$data['krav'] = $r['faktura_krav'];
		$data['trekk'] = $r['faktura_trekk'];
		$data['beskrivelse'] = $r['faktura_beskrivelse'];

		$objPHPExcel->createSheet($i);
		$objPHPExcel->setActiveSheetIndex($i);
		$objPHPExcel->getActiveSheet()->setTitle($data['ark']);

		// GENERER SIDE
		expages($data);
	}
	
	####################################################################################
	## JOBB VIDERE MED OVERSIKTSARKET
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->setTitle('OVERSIKT');
	$objPHPExcel->setActiveSheetIndex(0)->getTabColor()->setRGB('A0CF67');
	exlocksheet();
	exorientation('landscape');

	## GENERER TEKST TIL OVERSKRIFT
	$e4 = new PHPExcel_RichText();
	$e4->createText('Til gode/');
	$e4rod = $e4->createTextRun(' skyldig ');
	$e4rod->getFont()->setSize(12);
	$e4rod->getFont()->setBold(true);
	$e4rod->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
	$e4hvit = $e4->createTextRun('reiseoppgjør');
	$e4hvit->getFont()->setSize(12);
	$e4hvit->getFont()->setBold(true);
	$e4hvit->getFont()->setColor(new PHPExcel_Style_Color('FFF7E1'));
	
	## GENERER TEKST TIL OVERSKRIFT
	$i4 = new PHPExcel_RichText();
	$i4->createText('Til gode/');
	$i4rod = $i4->createTextRun(' skyldig ');
	$i4rod->getFont()->setSize(12);
	$i4rod->getFont()->setBold(true);
	$i4rod->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
	$i4hvit = $i4->createTextRun('SUM');
	$i4hvit->getFont()->setSize(12);
	$i4hvit->getFont()->setBold(true);
	$i4hvit->getFont()->setColor(new PHPExcel_Style_Color('FFF7E1'));
	
	## SETT KOLONNEBREDDER
	excolwidth('A',25);
	for($col=2; $col<12; $col++) {
		exwrap(i2a($col).'4',35);
		excolwidth(i2a($col),14.5);
	}
	excolwidth('I',8);

	## OVERSKRIFTER
	excell('A1:J1','REISEFORDELING UKM-Festivalen '.$data['season'],'h2');
	excell('B4','Opprinnelig refusjonskrav','grey');
	excell('C4','Ikke godtatte utgifter','grey');
	excell('D4','Avtalt egenandel','grey');
	excell('E4',$e4,'grey');
	excell('F4','Skyldig deltakeravgift','grey');
	excell('G4','Skyldig forpleining', 'grey');
	excell('H4','Egenandel frakt av kunst','grey');
#	excell('I4','','grey');
	excell('I4:J4',$i4,'grey');
	
	## LAG RADER FOR ALLE FYLKER I OVERSIKTEN
	$rad = $start = 4;
	foreach($arkRef as $ark => $fylke) {
		$ark = $ark.'!';
		$rad++;
		exformat(excell('A'.$rad,$fylke,'bold'));
		exformat(excell('B'.$rad,'='.$ark.'B10'));
		exformat(excell('C'.$rad,'='.$ark.'B12'));
		exformat(excell('D'.$rad,'='.$ark.'D14'));
		excond(exformat(excell('E'.$rad,'=B'.$rad.'-C'.$rad.'-D'.$rad)));
		exformat(excell('F'.$rad,'='.$ark.'D20+'.$ark.'D22'));
		exformat(excell('G'.$rad,'='.$ark.'D24+'.$ark.'D26'));
		exformat(excell('H'.$rad,'='.$ark.'D28'));
		exformat(excell('I'.$rad,'=IF(J'.$rad.'<0,"(skyldig)","tilgode")'));
		excond(exformat(excell('J'.$rad,'=SUM(F'.$rad.'+G'.$rad.'+H'.$rad.'-E'.$rad.')')));
	}
	$stop = $rad;
	
	## SUM-RADER
	$rad+=2;
	excell('A'.$rad, 'SUM', 'bold');
	exformat(excell('B'.$rad, '=SUM(B'.$start.':B'.$stop.')','bold'));
	exformat(excell('C'.$rad, '=SUM(C'.$start.':C'.$stop.')','bold'));
	exformat(excell('D'.$rad, '=SUM(D'.$start.':D'.$stop.')','bold'));
	exformat(excell('E'.$rad, '=SUM(E'.$start.':E'.$stop.')','bold'));
	exformat(excell('F'.$rad, '=SUM(F'.$start.':F'.$stop.')','bold'));
	exformat(excell('G'.$rad, '=SUM(G'.$start.':G'.$stop.')','bold'));
	exformat(excell('H'.$rad, '=SUM(H'.$start.':H'.$stop.')','bold'));
	# kol i
	exformat(excell('J'.$rad, '=SUM(J'.$start.':J'.$stop.')','bold'));
	
	exprint('A1:J'.$rad);

	####################################################################################
	## TOLKNINGS-ARK
	## INITIER
	$objPHPExcel->setActiveSheetIndex(1);
	$objPHPExcel->getActiveSheet()->setTitle('TOLKNING');
	$objPHPExcel->setActiveSheetIndex(1)->getTabColor()->setRGB('F69A9B');
	exlocksheet();
	exprint('A1:F7');
	exorientation('landscape');

	## SETT KOLONNEBREDDER
	excolwidth('A',32);
	excolwidth('B',13);
	excolwidth('C',13);
	excolwidth('D',13);
	excolwidth('E',13);

	## OVERSKRIFTER
	excell('A2','','grey');
	excell('B2','','grey');
	excell('C2','Budsjett','grey');
	excell('D2','Faktisk kost','grey');
	excell('E2','Avvik','grey');
	
	## UTREGNINGER
	excell('A3','Totale godkjente reiseutgifter','bold');
	exformat(excell('B3','=OVERSIKT!B'.$rad.'-OVERSIKT!C'.$rad));

	excell('A4','UKM Norges andel i reiseoppgjøret','bold');
	exformat(excell('B4','=OVERSIKT!E'.$rad));
	exunlock(exformat(excell('C4',get_ukm_option('faktura_reiseandel'),'hvit')));
	exformat(excell('E4','=C4-B4'));
	
	excell('A5','Totale egenandeler frakt av kunst','bold');
	exformat(excell('B5','=OVERSIKT!H'.$rad));
	exunlock(exformat(excell('D5',get_ukm_option('faktura_kunstfrakt'),'hvit')));
	exformat(excell('E5','=B5-D5'));

	excell('A6','Totale deltakeravgifter','bold');
	exformat(excell('B6','=OVERSIKT!F'.$rad));
	exunlock(exformat(excell('C6',get_ukm_option('faktura_deltakeravgift'),'hvit')));
	exformat(excell('E6','=B6-C6'));
	
	exformat(excell('E7','=SUM(E4:E6)','bold'));
	exformat(excell('F7','SUM AVVIK','bold'));
	
	
	####################################################################################
	#### GENERER OG LAGRE EXCEL-FIL
	$filnavn = date('dmyHis').'_UKM-Festivalen_'.$data['season'].'_Fakturagrunnlag.xlsx';
	$objPHPExcel->setActiveSheetIndex(0);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save(UKM_HOME.'../temp/phpexcel/'.$filnavn);
	
	####################################################################################
	#### PRINT GUI
	echo '<div id="loaded_faktura" style="display:none;"><a href="//ukm.no/temp/phpexcel/'.$filnavn.'">'
		.'Last ned excelark med fakturagrunnlag'
		.'</a></div>'
		.'<script language="javascript" type="text/javascript">'
		."jQuery('#loading_faktura').html(jQuery('#loaded_faktura').html());"
		.'</script>'
		;
}

function expages($data) {
	global $objPHPExcel;
	exsetcss('A1:E42','bakgrunn');
	exprint('A1:E42');
	exlocksheet();
	## SETT KOLONNEBREDDER
	excolwidth('A',37);
	excolwidth('B',10);
	excolwidth('C',10);
	excolwidth('D',14);
	excolwidth('E',14);
	
	excell('A1:E1','REISEFORDELING OG DELTAKERAVGIFT', 'h3');

	excell('A2:E2',$data['fylke'],'h1',array('font'=>array('size'=>36)));
	
	excell('A3:E3','UKM-FESTIVALEN '.$data['season'],'h3');

	excell('A5', 'Antall deltakere og ledere innenfor kvoten');
	exunlock(excell('C5', $data['deltakere_og_ledere_innnenfor_kvoten'],'hvit'));
	excell('E5', 'Totalt','h4');
	
	excell('A6', 'Antall deltakere og ledere utover kvoten');	
	exunlock(excell('C6', $data['deltakere_og_ledere_utover_kvoten'],'hvit'));
	excell('E6', '=C5+C6','h4');
	
	excell('A8:E8', 'Reiseoppgjør','grey');
	
	excell('A10','Totale reiseutgifter i flg. bilag');
	exunlock(exformat(excell('B10:C10',$data['krav'],'hvit')));
	
	excell('A12',' - Utgifter som ikke refunderes');
	exunlock(exformat(excell('B12',$data['trekk'],'hvit')));
	exunlock(excell('C12:E12',$data['beskrivelse'],'hvit'));

	excell('B13','Sats', 'right');
	excell('C13','Antall', 'right');
	exformat(excell('D13','Sum', 'right'));
	
	excell('A14', ' - Egenandel reise');
	exformat(excell('B14', $data['egenandel_reise']));
	excell('C14', '=C5');
	exformat(excell('D14', '=B14*C14'));

	excell('A16', '=IF(E16>0,"Skyldige reiseutgifter","Reiseutgifter tilgode")', 'bold');
	exformat(excell('E16', '=D14-SUM(B10-B12)', 'bold'));

	excell('A18','Deltakeravgift og annen gjeld til UKM','grey');
	excell('B18','Sats','greyright');
	excell('C18','Antall','greyright');
	excell('D18','Sum','greyright');
	excell('E18','','grey');
		
	excell('A20','Deltakeravgift ordinær kvote');
	exformat(excell('B20',$data['subsidiert_deltakeravgift']));
	excell('C20','=C5');
	exformat(excell('D20','=B20*C20','bold'));
	
	excell('A22','Deltakeravgift ekstra leder/deltaker');
	exformat(excell('B22',$data['ordinar_deltakeravgift']));
	excell('C22','=C6');
	exformat(excell('D22','=B22*C22','bold'));
	
	excell('A24','Ledermiddag, ekstra deltaker');
	exformat(excell('B24',$data['ledermiddag_avgift']));
	excell('C24',$data['ekstra_deltakere_ledermiddag']);
	exformat(excell('D24','=B24*C24','bold'));
	
	excell('A26','Hotelldøgn');
	exformat(excell('B26',$data['hotelldogn_pris']));
	excell('C26',$data['antall_hotelldogn']);
	exformat(excell('D26','=B26*C26','bold'));
	
	excell('A28','Frakt av kunst');
	exformat(excell('D28',$data['frakt_av_kunst'],'bold'));
	
	excell('A30','Sum krav UKM Norge','bold');
	exformat(excell('E30','=SUM(D20:D28)','bold'));
	
	excell('A32:E32','Utregning','grey');
	
	excell('A34','Krav UKM Norge');
	exformat(excell('E34','=E30','bold'));
	
	excell('A36','=IF(E16>0,"Skyldige reiseutgifter","Reiseutgifter tilgode")','strong');
	exformat(excell('E36','=E16','bold'));
	
	excell('A38','=IF(E34+E36>0,"Fylket skal innbetale kroner:","UKM Norge skal tilbakeføre kroner:")','bold');
	exformat(excell('E38','=E34+E36','bold'));
	
	excell('A40','Beregning foretatt av');
	excell('E40','Dato:');
	excell('A41','UKM Norge wordpress','bold');
	excell('E41',date('d.m.y'));
}
function okonomi_form() {
	if(isset($_POST['lagre']))
		UKMV_rapporter_okonomi_save();
	$qry = new SQL("SELECT `pl`.`pl_name`,
						   `pl`.`pl_id`,
						   `pl`.`pl_fylke`,
						   `i`.`systemet_overnatting_spektrumdeltakere`,
						   `i`.`overnatting_spektrumdeltakere`,
						   `i`.`faktura_krav`,
						   `i`.`faktura_trekk`,
						   `i`.`faktura_beskrivelse`
					FROM `smartukm_videresending_infoskjema` AS `i`
					JOIN `smartukm_place` AS `pl` ON (`pl`.`pl_id` = `i`.`pl_id_from`)
					WHERE `pl`.`season` = '#season'
					ORDER BY `pl`.`pl_name` ASC",
					array('season'=>get_option('season')));
	$res = $qry->run();
	$i = 1;
	$arkRef = array();
	
	$TWIG = array();

	while($r = mysql_fetch_assoc($res)) {
		$TWIG['fylker'][] = $r;
	}

	echo TWIG('okonomi_krav.twig.html', $TWIG, dirname(__FILE__), true);
}
function UKMV_rapporter_okonomi_save() {
	foreach($_POST as $key => $val) {
		$info = explode('_',$key);
		$pl = $info[1];
		$felt = $info[2];
		
		$sql = new SQLins('smartukm_videresending_infoskjema',array('pl_id_from'=>$pl));
		$sql->add('faktura_'.$felt,$val);
		$sql->run();
	}
}

function get_ukm_option( $key ) {
	return get_site_option( 'UKMFvideresending_'.$key.'_'.get_option('season') );
}
function update_ukm_option( $key, $val ) {
	return update_site_option( 'UKMFvideresending_'.$key.'_'.get_option('season'), $val );
}

?>