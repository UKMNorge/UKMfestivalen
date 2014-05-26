<?php
require_once( PLUGIN_DIR_PATH_UKMFESTIVALEN.'../UKMvideresending_festival/class/leder.class.php' );
require_once( 'UKM/inc/excel.inc.php');

// LAST INN ALLE FYLKER
	$fylker = new SQL("SELECT `id`
					   FROM `smartukm_fylke`
					   ORDER BY `name` ASC");
	$fylker = $fylker->run();
	
	while($row = mysql_fetch_assoc($fylker)) {
		$fylke = new fylke_monstring($row['id'], get_option('season'));
		$fylke = $fylke->monstring_get();
	
		if(!$fylke)	
			continue;
		if(!is_numeric($fylke->g('pl_id')) || $fylke->g('pl_id')==0)
			continue;
	
		$TWIG['fylker'][] = array('name' => $fylke->get('pl_name'),
								 'link' => $fylke->get('link'));
	}

// LAST INN INFO OM FESTIVALEN
	$m = new monstring( get_option('pl_id') );
	$netter = $m->netter();

	require_once(PLUGIN_DIR_PATH_UKMFESTIVALEN.'controller/overnatting_netter.controller.php');
	krsort($TWIG['netter']['for']);
	$alle_netter = array_merge( $TWIG['netter']['for'], $TWIG['netter']['under'], $TWIG['netter']['etter'] );
	$count_enkel = array();
	$count_dobbel = array();
	foreach( $alle_netter as $num => $data ) {
		$count_enkel['ledere'][ $data->dag.'.'.$data->mnd ] = 0;
		$count_dobbel['ledere'][$data->dag.'.'.$data->mnd ] = 0;
		$count_enkel['total'][ $data->dag.'.'.$data->mnd ] = 0;
		$count_dobbel['total'][$data->dag.'.'.$data->mnd ] = 0;
	}

// LAST INN INFO OM LEDERE
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
	
	global $objPHPExcel;
	$objPHPExcel = null;
	exInit('Overnatting hotell UKM Norge');
	exSheetName('Ledere');
	
	$rad = 1;
	excell('A'.$rad, 'Fra','bold');
	excell('B'.$rad, 'Navn','bold');
	excell('C'.$rad, 'Mobil','bold');
	excell('D'.$rad, 'E-post','bold');
	$col = 5;
	
	foreach( $netter as $num => $data ) {
		excell(i2a($col+$num).$rad, date('D d.m',$data->timestamp),'bold');
	}
	
	while( $r = mysql_fetch_assoc( $res ) ) {
		$rad++;
		$leder = new leder( $r['l_id'] );
		$navn = empty($leder->l_navn) ? 'Leder uten navn' : $leder->l_navn;
		
		excell('A'.$rad, $leder->kommer_fra,'bold');
		excell('B'.$rad, $navn,'bold');
		excell('C'.$rad, $leder->l_mobilnummer);
		excell('D'.$rad, $leder->l_epost);
		
		foreach( $netter as $num => $data ) {
			$pa_hotell = $leder->natt[ $data->dag.'_'.$data->mnd ]->sted == 'hotell';
			excell(i2a($col+$num).$rad, $pa_hotell ? 'x' : '-');
			if( $pa_hotell ) {
				$count_enkel['ledere'][ $data->dag.'.'.$data->mnd ] ++;
				$count_enkel['total'][ $data->dag.'.'.$data->mnd ] ++;
			}
		}
	}

// RESSURSER FRA UKM NORGE
	$sql = new SQL("SELECT `p`.`navn`,
						   `p`.`mobil`,
						   `p`.`epost`,
						   `p`.`ankomst`,
						   `p`.`avreise`,
						   `rom`.`id` AS `rom`,
						   `rom`.`type` AS `romtype`,
						   `gruppe`.`navn` AS `gruppe`
					FROM `ukm_festival_overnatting_person` AS `p`
					JOIN `ukm_festival_overnatting_rel_person_rom` AS `rel` ON (`p`.`id` = `rel`.`person_id`)
					JOIN `ukm_festival_overnatting_rom` AS `rom` ON (`rom`.`id` = `rel`.`rom_id`)
					JOIN `ukm_festival_overnatting_gruppe` AS `gruppe` ON (`gruppe`.`id` = `p`.`gruppe`)
					ORDER BY `p`.`gruppe` ASC, `rel`.`rom_id` ASC 
					");
	$res = $sql->run();
	while( $r = mysql_fetch_assoc( $res ) ) {
		$ressurspersoner[ $r['gruppe'] ][] = $r;
	}

	$excelArk = 0;
	if( is_array( $ressurspersoner ))
	foreach( $ressurspersoner as $gruppe => $personer ) {

		foreach( $alle_netter as $num => $data ) {
			$count_enkel[$gruppe][ $data->dag.'.'.$data->mnd ] = 0;
			$count_dobbel[$gruppe][$data->dag.'.'.$data->mnd ] = 0;
		}

		$excelArk++;
		$navn = substr($gruppe, 0, 16);
		$objPHPExcel->createSheet($excelArk);
		$objPHPExcel->setActiveSheetIndex($excelArk);
		exSheetName($navn,'f69a9b');

		excell('A1', 'Romnavn (unikt)', 'bold');
		excell('B1', 'Type', 'bold');
		excell('C1', 'Navn', 'bold');
		excell('D1', 'Mobil', 'bold');
		excell('E1', 'E-post', 'bold');
		$col = 6;
		$rad = 1;

		foreach( $alle_netter as $num => $data ) {
			excell(i2a($col+$num).$rad, date('D d.m',$data->timestamp),'bold');
		}
		$rad = 1;
		foreach( $personer as $p ) {
			$rad++;
			excell('A'.$rad, ucfirst(substr($p['romtype'],0,1)). $p['rom']);
			excell('B'.$rad, $p['romtype'],'bold');
			excell('C'.$rad, $p['navn'],'bold');
			excell('D'.$rad, $p['mobil']);
			excell('E'.$rad, $p['epost']);
			$start = $p['ankomst'];
			$stop = $p['avreise'];
			$selector = ' - ';
			foreach( $alle_netter as $num => $data ) {
				if( $start == date('d.m',$data->timestamp) )
					$text = 'x';
				if( $stop == date('d.m',$data->timestamp) ) 
					$text = '-';
				if( $text == 'x' && $p['romtype'] == 'enkelt') {
					$count_enkel[$gruppe][ $data->dag.'.'.$data->mnd ]++;
					$count_enkel['total'][ $data->dag.'.'.$data->mnd ]++;
				}
				if( $text == 'x' && $p['romtype'] == 'dobbelt') {
					$count_dobbel[$gruppe][ $data->dag.'.'.$data->mnd ]++;
					$count_dobbel['total'][ $data->dag.'.'.$data->mnd ]++;
				}
					
				excell(i2a($col+$num).$rad, $text);
			}
		}
	}
	
	$TWIG['excel_hotell_norge'] = exWrite($objPHPExcel,'UKMF_Hotell_UKM_Norge');	
	
	/// LEDERE I SPEKTRUM
	global $objPHPExcel;
	$objPHPExcel = null;
	exInit('Deltakerovernatting ledere UKM Norge');
	
	$index = 0;
	foreach( array_reverse($netter) as $natt ) {
		$objPHPExcel->createSheet($index);
		$objPHPExcel->setActiveSheetIndex($index);

		exSheetName($natt->dag.'.'.$natt->mnd);
		excell('A1:D1', 'Ledere i deltakerovernattingen '. $natt->dag .'.'. $natt->mnd);
		$rad = 2;
		excell('A'.$rad, 'Fylke','bold');
		excell('B'.$rad, 'Navn','bold');
		excell('C'.$rad, 'Mobil','bold');
		excell('D'.$rad, 'E-post','bold');

		$sql = new SQL("SELECT * FROM `smartukm_videresending_ledere_natt` AS `natt`
					JOIN `smartukm_videresending_ledere_ny` AS `leder` ON (`leder`.`l_id` = `natt`.`l_id`)
					JOIN `smartukm_place` AS `place` ON (`place`.`pl_id` = `leder`.`pl_id_from`)
					WHERE `sted` = 'deltakere'
					AND `dato` = '#dag_#mnd'
					AND `pl_id_to` = '#monstring'
					ORDER BY `place`.`pl_name` ASC, `leder`.`l_navn` ASC",
					array('dag' => $natt->dag, 'mnd' => $natt->mnd, 'monstring' => get_option('pl_id')));
		$res = $sql->run();
		while( $r = mysql_fetch_assoc( $res ) ) {
			$rad++;
			excell('A'.$rad, utf8_encode($r['pl_name']));
			excell('B'.$rad, $r['l_navn']);
			excell('C'.$rad, $r['l_mobilnummer']);
			excell('D'.$rad, $r['l_epost']);
		}
	}
	$TWIG['excel_deltakerovernatting'] = exWrite($objPHPExcel,'UKMF_Deltakerovernatting_UKM_Norge');	
	
	$TWIG['alle_netter'] = $alle_netter;
	$TWIG['count']['enkel'] = $count_enkel;
	$TWIG['count']['dobbel'] = $count_dobbel;
		
