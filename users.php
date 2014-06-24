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
				$deltakere = $inn->personObjekter();
				
				foreach( $deltakere as $deltaker ) {
					$description = '';
					$deltaker->loadGEO();
					$firstname = explode(' ', $deltaker->get('p_firstname'));
					$lastname = explode(' ', $deltaker->get('p_lastname'));
					$username = strtolower(trim($firstname[0])).'.'.strtolower(trim($lastname[count($lastname)-1]));
					$email    = UKM_ordpass() . '@fakeukm.no';
					$password = UKM_ordpass();
					$title    = utf8_decode($deltaker->get('instrument'));
					$description = $deltaker->get('p_firstname') . ' ' . $deltaker->get('p_lastname') . ' er ' . $deltaker->alder() . ' gammel og kommer fra ' . $deltaker->get('kommune') . ' i ' . $deltaker->get('fylke');
					echo $username . '-' . $password . '<br />';
					
					$user_id = username_exists( $username );
					if(!$user_id and email_exists($email) == false ) {
					    $user_id = wp_create_user( $username, $password, $email );
					}
					update_user_meta($user_id, 'Title', $title);
					wp_update_user( array( 'ID' => $user_id, 'description' => $description, 'title' => $role ) );
				}
							
			}
		
		}
	
	}
	
}

?>