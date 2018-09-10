<?php
/* 
Part of: UKMFestivalen :: kunsthenting
Description: Genererer enkel henterapport for henting av kunst
Author: UKM Norge / M Mandal
Version: 1.0
*/

function UKMF_kunst_gui() {
	switch($_GET['rapport']) {
		case 'kunsthenting':
			return UKMV_rapporter_kunsthenting();
		case 'fraktseddel':
			return UKMF_kunst_fraktseddel();
		default:
			return UKMF_kunst_oversikt();
	}
}

function UKMF_kunst_oversikt() {
	require_once('UKM/zip.class.php');
	$m = new monstring(get_option('pl_id'));
	$alle_fylker = $m->innslag_geo();

	echo '<h1>Alle registrerte kunst- og kunstnerbilder for denne m&oslash;nstringen</h1>';
	echo '<div id="loadBox">Vennligst vent, forbereder media og pakker zip-fil</div>';

	foreach($alle_fylker as $fylke => $innslag_fra_fylket) {
		$return .= '<h2>'.$fylke.'</h2>'
			.'<table>'
			.	'<tr>'
			.		'<td width="300"><strong>Navn p&aring; innslag</strong> - verk</td>'
			.		'<th align="left" width="150">Bilde av verk</th>'
			.		'<th align="left" width="150">Bilde av kunstner</th>'
			.	'</tr>'
			;
		foreach($innslag_fra_fylket as $b_id => $info) {
			// Hopp over alt annet enn kunst
			if($info['bt_id'] != 3)
				continue;
			$titler = new titleInfo($b_id, $info['bt_form'],'land',$m->g('pl_id'));
			$titler = $titler->getTitleArray();
			
			foreach($titler as $tittel){
				$bilde_kunst_qry = new SQL("SELECT `rel_id`
								 	   FROM `smartukm_videresending_media`
									   WHERE `b_id` = '#bid'
									   AND `t_id` = '#tid'
									   AND `pl_id` = '#plid'
									   AND `m_type` = '#type'",
									   array('bid'=>$b_id,
									   		 'tid'=>$tittel['t_id'],
									   		 'plid'=>$m->g('pl_id'),
									   		 'type'=>'bilde')
									  );
				$bilde_kunst = UKMF_kunst_media_by_rel($bilde_kunst_qry->run('field','rel_id'));
				$bilde_kunstner_qry = new SQL("SELECT `rel_id`
								 	   FROM `smartukm_videresending_media`
									   WHERE `b_id` = '#bid'
									   AND `t_id` = '#tid'
									   AND `pl_id` = '#plid'
									   AND `m_type` = '#type'",
									   array('bid'=>$b_id,
									   		 'tid'=>$tittel['t_id'],
									   		 'plid'=>$m->g('pl_id'),
									   		 'type'=>'bilde_kunstner')
									  );
				$bilde_kunstner = UKMF_kunst_media_by_rel($bilde_kunstner_qry->run('field','rel_id'));
				$return .= ''
					.	'<tr>'
					.		'<td><strong>'.$info['b_name'].'</strong> - '.utf8_encode($tittel['name']).'</td>'
					.		'<td>'.$bilde_kunst.'</td>'
					.		'<td>'.$bilde_kunstner.'</td>'
					.	'</tr>'
					;
					
				$bildenavn = $fylke.' '.$info['b_name'].' - '.utf8_encode($tittel['name']).' - ';
				$verk_lokal = UKMF_kunst_media_by_rel($bilde_kunst_qry->run('field','rel_id'), $bildenavn.'VERK',true);
				$kunstner_lokal = UKMF_kunst_media_by_rel($bilde_kunstner_qry->run('field','rel_id'), $bildenavn.'KUNSTNER',true);
				$verk_ext = explode('.',$verk_lokal);
				$verk_ext = end($verk_ext);
				$kunstner_ext = explode('.',$kunstner_lokal);
				$kunstner_ext = end($kunstner_ext);
				
				if($verk_lokal !== 'bilde mangler')
					$filestoadd[$verk_lokal] = 'UKM '.$bildenavn.'VERK.'.$verk_ext;
				if($kunstner_lokal !== 'bilde mangler')
					$filestoadd[$kunstner_lokal] = 'UKM '.$bildenavn.'KUNSTNER.'.$kunstner_ext;
			}
		}
		$return .= '</table>';
	}
	$zipname = 'UKM_Kunstbilder_'.get_option('season').'.zip';	
	create_zip($filestoadd, $zipname, true);
	echo ''
		.'<div id="loadedBox">'
		. '<h2>Last ned ZIP-fil</h2>'
		. '<a href="//ukm.no/temp/zip/'.$zipname.'">Last ned alle kunstnere og kunstverk som zip-fil</a><br />'
		. $return
		.'<script>jQuery("#loadBox").slideUp();jQuery("#loadedBox").show();</script>'
		;
}

function UKMF_kunst_media_by_rel($rel_id, $localpath=false) {
	$qry = new SQL("SELECT * 
					FROM `ukmno_wp_related`
					WHERE `rel_id` = '#rel_id'",
					array('rel_id'=>$rel_id));
	$res = $qry->run('array');
	$res['post_meta'] = unserialize($res['post_meta']);

#	echo '<pre>'; var_dump($res); echo '</pre>';
	
	$url = $res['blog_url'].'/files/'.$res['post_meta']['sizes']['thumbnail']['file'];
	$full = $res['blog_url'].'/files/'.$res['post_meta']['file']; 
	$large = $res['blog_url'].'/files/'
			. (isset($res['post_meta']['sizes']['large']) 
				? $res['post_meta']['sizes']['large']['file']
				: $res['post_meta']['file']
				);
	if($url == '/files/')
		return 'bilde mangler';
		
	if($localpath)
		return UKM_HOME.'../wp-content/blogs.dir/'.$res['blog_id'].'/files/'.$res['post_meta']['file'];
		
	return '<a href="'.$large.'"><img src="'.$url.'" /></a>'
		.  '<br />'
		.  '<a href="'.$full.'" target="_blank">Last ned original</a>';
}

function UKMF_kunst_fraktseddel() {
$kolli = new SQL("SELECT `kolli`.*, `place`.`pl_name`
				FROM `smartukm_videresending_infoskjema_kunst_kolli` AS `kolli`
				JOIN `smartukm_place` AS `place` ON (`place`.`pl_id` = `kolli`.`pl_id_from`)
				WHERE `kolli`.`pl_id` = '#pl_id'
				AND `kolli`.`pl_id_from` = '#pl_from_id'
				ORDER BY `pl_name` ASC, `kolli_id` ASC",
				array('pl_from_id'=>$_GET['plfrom'], 'pl_id'=>$_GET['plto']));
$kolli = $kolli->run();

$skjema = new SQL("SELECT `kunst`.*, `place`.`pl_name`
				FROM `smartukm_videresending_infoskjema_kunst` AS `kunst`
				JOIN `smartukm_place` AS `place` ON (`place`.`pl_id` = `kunst`.`pl_id_from`)
				WHERE `kunst`.`pl_id` = '#pl_id'
				AND `kunst`.`pl_id_from` = '#pl_from_id'
				ORDER BY `pl_name` ASC",
				array('pl_from_id'=>$_GET['plfrom'], 'pl_id'=>$_GET['plto']));

$skjema = $skjema->run('array');
foreach($skjema as $key => $val) 
	$skjema[$key] = utf8_encode($val);
		
		
while($k = SQL::fetch($kolli)) {
	foreach($k as $key => $val) 
		$k[$key] = utf8_encode($val);

	$kolliene .= '<div class="kolonne">Kolli '.$k['kolli_id'].'</div>'
				.'<div class="kolonne">'.$k['kolli_bredde'].'</div>'
				.'<div class="kolonne">'.$k['kolli_hoyde'].'</div>'
				.'<div class="kolonne">'.$k['kolli_dybde'].'</div>'
				.'<div class="kolonne">'.$k['kolli_vekt'].'</div>'
				.'<br clear="all" />'
				;
	$lastrow = $k;
}


echo ''
	.'<h1>UKM Norge Fraktseddel'
	.	'<span id="forklaring"> (' . $lastrow['pl_name'] .')</span>'
	.'</h1>'	

	.'<h3>'
	.	'Kontaktperson: ' 
	.	$skjema['kunst_kontaktperson_ved_henting']
	.	' ('. $skjema['kunst_kontaktperson_ved_henting_mobil'].')'
	.'</h3>'
	
	.'<h3 style="margin-bottom: 0px;" class="fraogmed">'
	.	'Kunsten kan hentes fra og med: ' . $skjema['kunst_hentesnar']
	.'</h3>'

	.'<h3 style="margin-bottom: 0px;">Kommentar til spedit&oslash;r</h3>'	
	.$skjema['kunst_hentesnar_detaljer']
	
	.'<h3 style="margin-bottom: 0px;">Henteadresse</h3>'	
	. $skjema['kunst_henteadresse'] . '<br />'
	. $skjema['kunst_postnummer'] . ' ' . $skjema['kunst_poststed']. '<br />'
	.'<strong>Etasje: </strong>' . $skjema['kunst_etasje'] . '<br />'
	.'<strong>Inngang nr / fra: </strong>' . $skjema['kunst_inngang'] . '<br />'
	.'<strong>Heis: </strong>' . $skjema['kunst_heis'] . '<br />'
	
	.'<h3 style="margin-bottom: 0px;">Kolli som skal fraktes</h3>'
	. '<div id="kolliliste">'
	.	'<div class="kolonne header">Kolli</div>'
	.	'<div class="kolonne header">Bredde (i cm)</div>'
	.	'<div class="kolonne header">H&oslash;yde (i cm)</div>'
	.	'<div class="kolonne header">Dybde (i cm)</div>'
	.	'<div class="kolonne header">Vekt (i kg)</div>'
	. '<br clear="all" />'
	. $kolliene
	. '</div>'
	
	.'<h3 style="margin-bottom: 0px;">Eventuelle tilleggsopplysninger</h3>'	
	.$skjema['kunst_kommentarer']


	.'<h3 style="margin-bottom: 0px;">Retur etter festivalen</h3>'	
	.($skjema['kunst_leveringsadresse_samme']=='ja'
		? 'Samme som henteadresse'
		: $skjema['kunst_postretur']
	)

	;
}



function UKMV_rapporter_kunsthenting() {
	$skjema = new SQL("SELECT `kunst`.*, `place`.`pl_name`
					FROM `smartukm_videresending_infoskjema_kunst` AS `kunst`
					JOIN `smartukm_place` AS `place` ON (`place`.`pl_id` = `kunst`.`pl_id_from`)
					ORDER BY `pl_name` ASC");
	$skjema = $skjema->run();
	
	echo ''
#		.'<div class="rapport_back" style="display:none;" id="printButton_fraktseddel_back">'
#		. UKMN_icoButton('arrow-blue-left',35,'Tilbake til oversikten',11)
#		.'</div>'	
	
#		.'<div class="rapport_print" style="display:none;" id="printButton_fraktseddel" rel="fraktseddelen">'
#		. UKMN_icoButton('print',35,'Skriv ut fraktbrev',11)
#		.'</div>'

		.'<div id="fraktseddelen" style="display:none;"></div>';
	
	echo '<div class="rapport_print" id="printButton_oversikt" rel="kunstrapport">'
		. UKMN_icoButton('print',35,'Skriv ut',11)
		.'</div>'
		.'<br />'
		.'<ul class="rapport" id="kunstrapport">'
#		.'<link rel="stylesheet" id="UKMVideresending_css-css"  href="//ukm.no/wp-content/plugins/UKMVideresending/videresending.css?ver=3.3.1" type="text/css" media="all" />'
		.	'<li>'
		.		'<h3>Oversikt henting</h3>'
		.	'</li>'
		;

	echo '<li>'
		.	'<div class="fylke">Fylke</div>'
		.	'<div class="hentesfra">Kan hentes fra</div>'
		.	'<div class="kontaktperson">Kontaktperson</div>'
		.	'<div class="kommentar">Kommentar henting</div>'
		.	'<div class="tillegg">Tilleggsopplysninger</div>'
		.	'<br clear="all" />'
		.'</li>'
		;
	
	while($r = SQL::fetch($skjema)) {
		$kolli = new SQL("SELECT COUNT(`skjema_id`) AS `kolli`
						  FROM `smartukm_videresending_infoskjema_kunst_kolli`
						  WHERE `pl_id_from` = '#from'
						  AND `pl_id` = '#to'",
						  array('from'=>$r['pl_id_from'],
						  		'to'=>$r['pl_id'])
						 );
		$r['kolli'] = $kolli->run('field','kolli');

		foreach($r as $key => $val)
			$r[$key] = utf8_encode($val);
		echo '<li>'
		.		'<div class="fylke">'
		.			$r['pl_name']
		.			'<br />'
		.			'<span>'.$r['kolli'].' kolli</span><br />'
		.			'<a href="?page='.$_GET['page'].'&rapport=fraktseddel&plfrom='.$r['pl_id_from'].'&plto='.$r['pl_id'].'">[vis fraktseddel]</a>'
		.		'</div>'
		.		'<div class="hentesfra">'
		.			$r['kunst_hentesnar'].' &nbsp;'
		.		'</div>'
		.		'<div class="kontaktperson">'
		.			$r['kunst_kontaktperson_ved_henting'].' &nbsp;'
		.			'<br />'
		.			'('.$r['kunst_kontaktperson_ved_henting_mobil'].')'
		.		'</div>'
		.		'<div class="kommentar">'.$r['kunst_hentesnar_detaljer'].' &nbsp;</div>'
		.		'<div class="tillegg">'.$r['kunst_kommentarer'].' &nbsp;</div>'
		.		'<br clear="all" />'
		.	'</li>';
	}
	
	echo '</ul>';
}

?>