<?php
require_once( PLUGIN_DIR_PATH_UKMFESTIVALEN.'../UKMvideresending_festival/functions.php' );
require_once( PLUGIN_DIR_PATH_UKMFESTIVALEN.'../UKMvideresending_festival/class/leder.class.php' );
require_once('UKM/inc/excel.inc.php');

$sql = new SQL("SELECT `ledermiddag_ukm` AS `ukm`,
						`ledermiddag_fylke1` AS `fylke1`,
						`ledermiddag_fylke2` AS `fylke2`,
						`pl_from`
				FROM `smartukm_videresending_ledere_middag`
				WHERE `pl_to` = '#pl_to'",
			array( 'pl_to' => get_option('pl_id')
				)
			);
$res = $sql->run();

$ledere = array();
while( $r = mysql_fetch_assoc( $res ) ) {
	$pl_to = new monstring( $r['pl_from'] );
	if( !empty( $r['ukm'] ) && is_numeric( $r['ukm'] ) ) {
		$leder = new leder( $r['ukm'] );
		$leder->fylke = $pl_to->g('pl_name');
		$leder->gratis = true;
		$ledere[] = $leder;
	}

	if( !empty( $r['fylke1'] ) && is_numeric( $r['fylke1'] ) ) {
		$leder = new leder( $r['fylke1'] );
		$leder->fylke = $pl_to->g('pl_name');
		$leder->gratis = false;
		$ledere[] = $leder;
	}

	if( !empty( $r['fylke2'] ) && is_numeric( $r['fylke2'] ) ) {
		$leder = new leder( $r['fylke2'] );
		$leder->fylke = $pl_to->g('pl_name');
		$leder->gratis = false;
		$ledere[] = $leder;
	}
}


global $objPHPExcel;
$objPHPExcel = null;
exInit('Ledermiddag');
exSheetName('Gjester');

$objPHPExcel->setActiveSheetIndex(0);
$rad = 1;
excell('A'.$rad, 'Ankomsttid','bold');

$rad = 1;
foreach( $ledere as $leder ) {
	$rad++;
/*	excell('A'.$rad, $leder->fylke);
	excell('B'.$rad, utf8_encode($leder->l_navn));
	excell('C'.$rad, $leder->l_mobilnummer);
	excell('D'.$rad, $leder->l_epost);
	excell('E'.$rad, $leder->gratis ? 'Gratis' : 'Betalt');*/
}
$TWIG['excel_middag'] = exWrite($objPHPExcel,'UKMF_Ledermiddag_UKMFestivalen');

$TWIG['ledere'] = $ledere;