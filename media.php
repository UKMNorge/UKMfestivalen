<?php
/* 
Part of: UKM Videresending rapporter :: media
Description: Genererer rapport over media
Author: S O Bjerkan
Version: 1.0
*/

require_once(ABSPATH.'wp-content/plugins/UKMVideresending/videresending.php');

function UKMFestivalen_rapporter_media() {
	
	$sql = new SQL("SELECT `pl_id` 
					FROM `smartukm_place` 
					WHERE `season`=".get_option('season')."
					AND `pl_fylke`>0");
	$monstringer = $sql->run();

	$alleinnslagsortertmedoverskrift = '';
	while ($r = mysql_fetch_assoc($monstringer)) {
		$m = new monstring($r['pl_id']);
		$innslag = $m->videresendte();
		$pl_name = $m->g('pl_name');
		
		if (sizeof($innslag) == 0)
			continue;
		
		$alleinnslagsortertmedoverskrift = '<h1>'.$pl_name.'</h1>';
		
		$sortert = array();	
		foreach($innslag as $trash => $inn) {
			$i = new innslag($inn['b_id']);
			$i->loadGEO();
			
			$titler = new titleInfo( $i->g('b_id'), $i->g('bt_form'), 'land', $m->videresendTil());
			$titler = $titler->getTitleArray();
			
			$i->videresendte($m->videresendTil());
			$personer = $i->personer();
			
			$valgtBilde = new SQL("SELECT `rel_id`
							  FROM `smartukm_videresending_media`
							  WHERE `b_id` = '#bid'
							  AND `m_type` = 'bilde'",
							  array('bid'=>$i->g('b_id')));
			$valgtBilde = $valgtBilde->run('field','rel_id');
			
			$valgtBildeK = new SQL("SELECT `rel_id`
							  FROM `smartukm_videresending_media`
							  WHERE `b_id` = '#bid'
							  AND `m_type` = 'bilde_kunstner'",
							  array('bid'=>$i->g('b_id')));
			$valgtBildeK = $valgtBildeK->run('field','rel_id');
			
			
			foreach($titler as $trash2 => $t) {			
				$id = $inn['b_id'].'_'.$t['t_id'];
				$katogsjan = $i->g('kategori_og_sjanger');
				$items = $i->related_items();
				$krav[$i->g('bt_name')] = UKMV_innslagMediaKravTrueFalse($i->g('bt_form'));
				$kravTekst[$i->g('bt_name')] = UKMV_innslagMediaKrav($i);
				$overskrifter[$i->g('bt_form')] = $i->g('bt_name');
				
				$sortert[$i->g('bt_form')] .= '<tr>'
											.  '<td class="bname">'
											.   $i->g('b_name')
											.  '</td>'
											;
						
						if ($krav[$i->g('bt_name')]['bilde']) {
							if ($valgtBilde > 0)
								$sortert[$i->g('bt_form')] .= '<td align="center">'.UKMN_ico('circle-green', 16).'</td>';
							else
								$sortert[$i->g('bt_form')] .= '<td align="center">'
															.   UKMN_ico('circle-red', 16)
															.'</td>'; 
						}
						if ($krav[$i->g('bt_name')]['kunstbilde']) {
							if ($valgtBildeK > 0)
								$sortert[$i->g('bt_form')] .= '<td align="center">'.UKMN_ico('circle-green', 16).'</td>';
							else
								$sortert[$i->g('bt_form')] .= '<td align="center">'
															.   UKMN_ico('circle-red', 16)
															.'</td>';
						}
						if ($krav[$i->g('bt_name')]['video']) {
							if (is_array($items['video']))
								$sortert[$i->g('bt_form')] .= '<td align="center">'.UKMN_ico('circle-green', 16).'</td>';
							else 
								$sortert[$i->g('bt_form')] .= '<td align="center">'
															.  UKMN_ico('circle-red', 16)
															.'</td>';
						}
						
				$sortert[$i->g('bt_form')] .= '</tr>';
	
			}
		}
		
		
		$i = 0;
		foreach($sortert as $bt_form => $alleinnslag) {
			$tittel = $overskrifter[$bt_form];
			$i++;
			$undermeny .= '<div id="steg'.$i.'">'
						.'<a href="#'.$tittel.'">'.$i .': '. $tittel .'</a>'
						.'</div>';
			$alleinnslagsortertmedoverskrift .= '<a name="'.$tittel.'"></a>'
											.   '<h2 class="mediaoverskrift">'.$tittel.'</h2>'
											.	'<table cellpadding="0" cellspacing="0" class="medieoversikt">'
											.   '<thead>'
											.    '<tr>'
											.     '<th class="lefttext"> Innslag</th>'
											.     ($krav[$tittel]['bilde'] ? '<th width="75">Bilde</th>' : '')
											.     ($krav[$tittel]['video'] ? '<th width="75">Video</th>' : '')
											.     ($krav[$tittel]['kunstbilde'] ? '<th width="75">Bilde av kunstner</th>' : '')
											.    '</tr>'
											.   '</thead>'
											.   '<tbody>'
											.	$alleinnslag
											.   '</tbody>'
											.   '</table>'
											;
		}
		echo $alleinnslagsortertmedoverskrift;
	}
	/*
	echo '<div class="rapport_print" id="printButton_oversikt" rel="media">'
		. UKMN_icoButton('print',35,'Skriv ut',11)
		.'</div>'
		.'<br />'
		.'<ul class="ukm" id="media">'
		.	'<li>'
		.		UKMN_ico('city',32)
		.		'<h4>Media</h4>'
		.		'<br clear="all" />'
		.	'</li>';

		foreach ($ledere as $key => $ledere_fylke) {
			echo '<li class="rapport_rom">'
               .'<h4>'.utf8_encode($ledere_fylke[0]['pl_name']).' <span class="forklaring">('.sizeof($ledere_fylke)
			   .' ledere)</span></h4>';
			   foreach ($ledere_fylke as $key => $value) {
			   		echo '<div class="row">
                               <div class="navn">'.ucwords(utf8_encode($value['leder_navn'])).'</div>'.
                               '<div class="mobil">'.
								   (empty($value['leder_mobilnummer']) ? '' : $value['leder_mobilnummer']).'</div>'.
						   		'<div class="epost">'.$value['leder_e-post'].'</div>'
                       .'<br clear="all" /></div>';
			   }
			   echo '</li>';
		}
		echo '</ul>';*/
}

?>