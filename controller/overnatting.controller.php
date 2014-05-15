<?php
require_once( PLUGIN_DIR_PATH.'../UKMvideresending_festival/class/leder.class.php' );
require_once( 'UKM/inc/excel.inc.php');

$m = new monstring( get_option('pl_id') );
$netter = $m->netter();

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
	}
}

$TWIG['excel_hotell_norge'] = exWrite($objPHPExcel,'UKMF_Hotell_UKM_Norge');