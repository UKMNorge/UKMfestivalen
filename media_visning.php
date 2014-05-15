<?php
function UKMFestivalen_rapporter_media_visning() {
	if(isset($_GET['forestilling']))
		return UKMFestivalen_rapporter_media_visning_forestilling();
	$m = new monstring(get_option('pl_id'));
	$alle_fylker = $m->innslag_geo();
	echo '<h1>Viser faktisk opplastet media</h1>'
		.'Unntatt fra listen er bilder av kunsten, som er '
		.'<a href="?page=UKMF_kunst&rapport=oversikt_bilder">samlet opp p&aring; denne siden</a>'
		.'<br />'
		.'Skal du laste ned mye media, anbefaler vi '
		.'<a href="?page='.$_GET['page'].'&rapport=media_visning&forestilling">nedlasting hovedforestillinger</a>'

		;
	foreach($alle_fylker as $fylke => $innslag_fra_fylket) {
		echo '<h2>'.$fylke.'</h2>'
			.'<table>'
			.	'<tr>'
			.		'<td width="300"><strong>Navn p&aring; innslag</strong> - tittel</td>'
			.		'<th align="left" width="150">Bilde av innslag</th>'
			.	'</tr>'
			;
		foreach($innslag_fra_fylket as $b_id => $info) {
			// Hopp over alt annet enn kunst
			if( !($info['bt_id'] == 1 || $info['bt_id'] = 2) )
				continue;
			$innslag = new innslag($info['b_id']);

			$titler = new titleInfo($b_id, $info['bt_form'],'land',$m->g('pl_id'));
			$titler = $titler->getTitleArray();
			
			foreach($titler as $tittel){
				$bilde_innslag = new SQL("SELECT `rel_id`
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
				$bilde_innslag = UKMFestivalen_rapporter_media_media_by_rel($bilde_innslag->run('field','rel_id'), $innslag->g('b_name'));
				echo ''
					.	'<tr>'
					.		'<td>'
					.			'<strong>'.$info['b_name'].'</strong> - '.utf8_encode($tittel['name'])
					.			'<br />'.($info['bt_id'] == 1 ? 'Scene' : ($info['bt_id']==3 ? 'Film' : ''))
					.		'</td>'
					.		'<td>'
					.			$bilde_innslag
					.		'</td>'
					.	'</tr>'
					;
			}
		}
		echo '</table>';
	}
}

function UKMFestivalen_rapporter_media_visning_forestilling() {
	UKM_loader('api/forestilling.class|toolkit|zip');
	$m = new monstring(get_option('pl_id'));
	$forestillinger = $m->forestillinger('c_start',false);
	echo '<h1>Last ned media for hovedprogram</h1>';
	
	echo '<div id="loadBox">Vennligst vent, forbereder media;';
	foreach($forestillinger as $forestilling) {
		if(strpos($forestilling['c_name'],'Forestilling')===false && strpos($forestilling['c_name'],'Utstilling')===false)
				continue;
		echo '<br />'. $forestilling['c_name'];
		
		$forestillingen = new forestilling($forestilling['c_id']);
		$innslag_i_forestillingen = $forestillingen->concertBands();
			
		$rekkefolge = 0;
		$filestoadd = array();
		$retur = '';
		foreach($innslag_i_forestillingen as $info) {
			$rekkefolge++;
			$innslag = new innslag($info['b_id']);
			// Hopp over alt annet enn kunst
			if( !($innslag->g('bt_id')==1  || $innslag->g('bt_id')==2) )
				continue;
		
			
			$titler = new titleInfo($innslag->g('b_id'), $innslag->g('bt_form'),'land',$m->g('pl_id'));
			$titler = $titler->getTitleArray();
			
			foreach($titler as $tittel){
				$bilde_innslag = new SQL("SELECT `rel_id`
								 	   FROM `smartukm_videresending_media`
									   WHERE `b_id` = '#bid'
									   AND `t_id` = '#tid'
									   AND `pl_id` = '#plid'
									   AND `m_type` = '#type'",
									   array('bid'=>$innslag->g('b_id'),
									   		 'tid'=>$tittel['t_id'],
									   		 'plid'=>$m->g('pl_id'),
									   		 'type'=>'bilde')
									  );
				$bildet_innslag = UKMFestivalen_rapporter_media_media_by_rel($bilde_innslag->run('field','rel_id'), $forestilling['c_name'].' nr '.$rekkefolge.' - '. $innslag->g('b_name'));
				$retur .= ''
					.	'<tr>'
					.		'<td>'
					.			'<strong>'.$rekkefolge.'. ' .$innslag->g('b_name').'</strong> - '.utf8_encode($tittel['name'])
					.			'<br />'.($innslag->g('bt_id') == 1 ? 'Scene' : ($innslag->g('bt_id')==3 ? 'Film' : ''))
					.		'</td>'
					.		'<td>'
					.			$bildet_innslag
					.		'</td>'
					.	'</tr>'
					;
				$bilde_lokal = UKMFestivalen_rapporter_media_media_by_rel($bilde_innslag->run('field','rel_id'), $forestilling['c_name'].' nr '.($rekkefolge<10?'0':'').$rekkefolge.' - '. $innslag->g('b_name'),true);
				$ext = explode('.',$bilde_lokal);
				$ext = end($ext);
				if($bilde_lokal !== 'bilde mangler')
					$filestoadd[$bilde_lokal] = 'UKM '.$forestilling['c_name'].' nr '.$rekkefolge.' - '. $innslag->g('b_name').'.'.$ext;

			}
		}

		if(!empty($retur)) {
		// ZIP
			$zipname = 'UKM_'.str_replace(' ','_',$forestilling['c_name'].'.zip');
			create_zip($filestoadd, $zipname, true);
			$zipfiler .= '<a href="http://ukm.no/temp/zip/'.$zipname.'">Last ned alle '.$forestilling['c_name'].' som zip-fil</a><br />';
	    	$enkeltfiler .= '<h2>'.$forestilling['c_name'].'</h2>'
				.'<table>'
				.	'<tr>'
				.		'<td width="300"><strong>Navn p&aring; innslag</strong> - tittel</td>'
				.		'<th align="left" width="150">Bilde av innslag</th>'
				. $retur
				.	'</tr>'
				.'</table>';
		}
	}
		echo '</div>'
			.'<div id="loadedBox">'
			. '<h2>Last ned ZIP-filer</h2>'
			. $zipfiler
			. '<h2>Oversikt over alle forestillinger</h2>'
			. $enkeltfiler
			.'</div>'
			.'<script>jQuery("#loadBox").slideUp();jQuery("#loadedBox").show();</script>'

			;
}

function UKMFestivalen_rapporter_media_media_by_rel($rel_id,$name,$localpath=false) {
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

	$full = str_replace('http://','http123',$full);
	return '<a href="'.$large.'"><img src="'.$url.'" /></a>'
		.  '<br />'
		.  '<a href="http://ukm.no/wp-content/plugins/UKMimages/download.php?image='.$full.'&name='.$name.'" target="_blank">Last ned original</a>';
}
?>