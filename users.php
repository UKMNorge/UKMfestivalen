<?php
require_once('UKM/innslag.class.php');
require_once('UKM/inc/password.inc.php');
function UKMFestivalen_brukere_opprett() {
	$m = new monstring(get_option('pl_id'));
	$innslag = $m->innslag_btid();
	
	foreach($innslag as $band_type => $bands) {
	
		if( $band_type == 2 OR $band_type == 5 ) { 
		
			foreach($bands as $band) {
				
				$inn = new innslag($band['b_id']);
				$inn->videresendte($m->g('pl_id'));
				$deltakere = $inn->personer();
				
				foreach( $deltakere as $deltaker ) {
					
					$username = $deltaker['p_firstname'].'.'.$deltaker['p_lastname'];
					$email    = UKM_ordpass() . '@fakeukm.no';
					$password = UKM_ordpass();
					
					echo $username . '-' . $password;
					
					$user_id = username_exists( $username );
					if(!$user_id and email_exists($email) == false ) {
					    $user_id = wp_create_user( $username, $password, $email );
					}
				}
							
			}
		
		}
	
	}
	
}

?>