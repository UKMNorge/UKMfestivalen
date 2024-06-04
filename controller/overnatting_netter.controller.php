<?php
$monstring = new stdClass();
$monstring->navn = $m->getNavn();
$TWIG['monstring'] = $monstring;

	
	$netter = $m->getNetter();

	$start = $netter[0];

	$tilknytning_for = array();
	$dag = $start->format('d');
	$mnd = $start->format('m');;
	$ar = $start->format('Y');
	for( $i=1; $i<15; $i++) {
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
	$dag = $stop->format('d');
	$mnd = $stop->format('m');
	$ar = $stop->format('Y');
	for( $i=1; $i<10; $i++) {
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

	// Setter timestamp til alle DateTime for å bruke dem fra TWIG js
	foreach($netter as $natt) {
		$natt->timestamp = $natt->getTimestamp();
	}
	
$TWIG['netter']['for'] = $tilknytning_for;
$TWIG['netter']['under']		= $netter;
$TWIG['netter']['etter']= $tilknytning_etter;
