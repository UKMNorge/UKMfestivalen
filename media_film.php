<?php
function UKMFestivalen_rapporter_media_film() {
	UKM_loader('api/related.class');
	$m = new monstring(get_option('pl_id'));
	$alle_fylker = $m->innslag_geo();
	echo '<h1>Alle opplastede filmer</h1>'
		.'Denne siden lister ut alle opplastede filmer. For å se selve filmene, se nettsiden'
		;
	foreach($alle_fylker as $fylke => $innslag_fra_fylket) {
		echo '<h2>'.$fylke.'</h2>'
			.'<table>'
			.	'<tr>'
			.		'<td width="300"><strong>Navn p&aring; innslag</strong> - tittel</td>'
			.		'<th align="left" width="250">Lenke til video</th>'
			.	'</tr>'
			;
		foreach($innslag_fra_fylket as $b_id => $info) {
			// Hopp over alt annet enn kunst
			if( $info['bt_id'] != 2)
				continue;
			$titler = new titleInfo($b_id, $info['bt_form'],'land',$m->g('pl_id'));
			$titler = $titler->getTitleArray();
	
			foreach($titler as $tittel){
				$related = new related($info['b_id']);
				$related = $related->get();
				echo ''
					.	'<tr>'
					.		'<td>'
					.			'<strong>'.$info['b_name'].'</strong> - '.utf8_encode($tittel['name'])
					.			'<br />'.($info['bt_id'] == 1 ? 'Scene' : ($info['bt_id']==3 ? 'Film' : ''))
					.		'</td>'
					.		'<td>'
					;
					if(is_array($related)){
						foreach($related as $object_id => $info) {
							if($info['post_type']!='video')
								continue;
							$filename = substr($info['post_meta']['file'], strrpos($info['post_meta']['file'],'/')+1);
							$filename = substr($filename,0,strrpos($filename,'.'));
							
							echo '<a href="http://videoconverter.ukm.no/original.php?name='.$filename.'">'
								.'Last ned video fra '.strtolower($info['post_meta']['title']).'sm&oslash;nstring'
								.'</a>'
								.'<br />';
						}
					} else {
						echo 'INGEN VIDEO';
					}
				echo ''
					.		'</td>'
					.	'</tr>'
					;
			}
		}
		echo '</table>';
	}
}
?>