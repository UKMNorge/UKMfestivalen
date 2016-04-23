<?php
$monstring = new stdClass();
$monstring->navn = $m->g('pl_name');
$TWIG['monstring'] = $monstring;


	$netter = $m->netter();
	
	$start = $netter[0];
	
	
	$tilknytning_for = array();
	$dag = $start->dag;
	$mnd = $start->mnd;
	$ar = $start->ar;
	for( $i=1; $i<6; $i++) {
		$tilknytning = new stdClass();
		
		$dag--;
	
		if( $dag < 1 ) {
			$mnd--;
			if( $mnd == 0 ) {
				$mnd = 12;
				$ar--;
			}
			
			$dag = cal_days_in_month( CAL_GREGORIAN, $mnd, $ar );
		}
		$tilknytning->dag = $dag;
		$tilknytning->mnd = $mnd;
		$tilknytning->ar = $ar;
		$tilknytning->timestamp = strtotime($ar.'-'.$mnd.'-'.$dag.' 00:00:00');

		$tilknytning_for[$i] = $tilknytning;
	}


	$stop = $netter[ sizeof( $netter )-1 ];
	
	
	$tilknytning_etter = array();
	$dag = $stop->dag;
	$mnd = $stop->mnd;
	$ar = $stop->ar;
	for( $i=1; $i<4; $i++) {
		$tilknytning = new stdClass();
		$dag++;
		if( $dag > cal_days_in_month( CAL_GREGORIAN, $mnd, $ar ) ) {
			$dag = 1;
			$mnd++;
			if( $mnd > 12 ) {
				$mnd = 1;
				$ar++;
			}
		}
		$tilknytning->dag = $dag;
		$tilknytning->mnd = $mnd;
		$tilknytning->ar = $ar;
		$tilknytning->timestamp = strtotime($ar.'-'.$mnd.'-'.$dag.' 00:00:00');

		$tilknytning_etter[$i] = $tilknytning;
	}
$TWIG['netter']['for'] = $tilknytning_for;
$TWIG['netter']['under']		= $netter;
$TWIG['netter']['etter']= $tilknytning_etter;
