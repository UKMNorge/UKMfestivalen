<?php
$loop = array('l2'=>'Lørdag','s2'=>'Søndag','m2'=>'Mandag');

#####################################################
## CONVERT AN INTEGER TO A LETTER (EXCEL COL)
#####################################################
function i2a($a) {
 return ($a-->26?chr(($a/26+25)%26+ord('A')):'').chr($a%26+ord('A'));
}

#####################################################
## LOAD STRUCTURE FILE
#####################################################
function id2col($id) {
	global $pamelding_id;
	global $festidb;
	
	$qry = "SELECT `navn`, `type`, `pamelding_id` FROM `ss3_pamelding_felt` WHERE `felt_id` = '".$id."'";
	$res = mysql_query($qry, $festidb);
	$rad = mysql_fetch_array($res);
	$pamelding_id = $rad['pamelding_id'];
	return array('navn'=>utf8_encode(html_entity_decode(strip_tags(str_replace(array('<br>','<br />'),' ',$rad['navn'])))),
				 'type'=>$rad['type']);
}

#####################################################
## LOAD STRUCTURE FILE
#####################################################
function loadWorkshops($day) {
	global $festidb;
	## LOOP AND FIND PAGES (WORKSHOPS)
	$page = -1;
	$qry = new SQL("SELECT * FROM `ukmno_ws_ws` WHERE `okt` LIKE '".$day."%' ORDER BY `navn` ASC");
	$res = $qry->run();
	while($rad = mysql_fetch_array($res)) {
		$page++;
		## SAVE WORKSHOP NAME FOR LATER USE
		$name = str_replace(array('<br>','<br />'),' ', $rad['navn']);
		$pagename[] = $name;
		## FIND ALL PARTICIPANTS
		$rel = new SQL("SELECT * FROM `ukmno_ws_rel` WHERE `ws_id` = '".$rad['ws_id']."'");
		$rel = $rel->run();
		while($rels = mysql_fetch_array($rel)) {
			$participant = people($rels['pam_id']);
			$excel[$page][] = $participant;
		}
		if(mysql_num_rows($rel) == 0) {
			$excel[$page][] = array('Ingen deltakere');	
		}
	}
	
	#echo '<pre>'; var_dump($excel); echo '</pre>'; die();
	$name = date('Ymd').'_Workshops_'.$day;
	$headers = array('Mobil','Navn','Fylke');
	## INITIATE EXCEL DOCUMENT
	## PHPExcel 
	require_once UKM_HOME.'phpexcel/PHPExcel.php';
	## PHPExcel_IOFactory
	require_once UKM_HOME.'phpexcel/PHPExcel/IOFactory.php';
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator('UKM Norge Wordpress')
							 ->setLastModifiedBy('UKM Norge Wordpress')
							 ->setTitle($name)
							 ->setSubject($name)
							 ->setDescription($name)
							 ->setKeywords("UKM Norge ".$name." ")
							 ->setCategory("UKM Norge rapport");
	
	## LOOP THE EXCEL ARRAY, AND CREATE DOCUMENT
	#ksort($excel);
	if(isset($excel) && is_array($excel)) {
		#echo '<h1>'.$day.'</h1>';
		foreach($excel as $page => $rows) {
		#echo '<h3>PAGE '. $page.'</h3>';
		#echo 'A1 - ' . $pagename[$page] . '<br />';
		#echo '<br />';
			$objPHPExcel->setActiveSheetIndex($page)->setCellValue('A1', utf8_encode($pagename[$page]));
			$objPHPExcel->setActiveSheetIndex($page)->mergeCells('A1:C1');
			for($i=0; $i<3; $i++) {
				$this_row_col_id = i2a($i+1).'2';
		#		echo $this_row_col_id . ' - ' . utf8_encode($headers[$i]) .'<br />';
				$objPHPExcel->setActiveSheetIndex($page)->setCellValue($this_row_col_id, utf8_encode($headers[$i]));
			}
		#	echo '<br />';
			foreach($rows as $row => $val) {
				foreach($val as $col => $data) {
					$this_row_col_id = i2a($col+1).($row+3);
		#			echo $this_row_col_id . ' - ' . $data .'<br />';
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue($this_row_col_id, utf8_encode($data));
				}
		#		echo '<br />';
			}
			$this_page_name = preg_replace(array('/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'),
  array('', '-', ''), $pagename[$page]);
			$this_page_name = substr($this_page_name,0,25);
			$objPHPExcel->getActiveSheet()->setTitle($this_page_name);
			if($page < sizeof($excel)-1) $objPHPExcel->createSheet();
		}
		## SET ACTIVE SHEET TO 0
		
		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		if(!file_exists(UKM_HOME.'../temp/phpexcel/workshops')) mkdir('../temp/phpexcel/workshops');
		$objWriter->save('../temp/phpexcel/workshops/'.$name.'.xls');
	
		return '../temp/phpexcel/workshops/'.$name.'.xls';
	}
	return 'javascript:alert(\'Ingen påmeldte\')';
}

function fylke($id) {
	global $fylke;
	global $festidb;
		
	if(isset($fylke[$id])) 
		return $fylke[$id];
	
	$qry = new SQL("SELECT * FROM `smartcore_fylke` WHERE `id` = '$id'");
	$rad = $qry->run('array');
	$fylke[$id] = $rad['name'];
	return $fylke[$id];
}

function people($id) {
	global $people, $fylke;
	global $festidb;
	
	## ALREADY FETCHED
	if(isset($people[$id])) 
		return $people[$id];
	## FETCH
	$qry = new SQL("SELECT * FROM `ukmno_ws_pameldte` WHERE `pam_id` = '$id';");
	$res = $qry->run();
	$array = mysql_fetch_array($res);
	foreach($array as $key => $val) {
		utf8_decode($val['navn']);
		if($key == 3) $people[$id][] = fylke($val);
		elseif(is_int($key) && $key !== 0) $people[$id][] = $val;
	}
	return $people[$id];
}

///////////////////////////////////////////////////////////////////////


function UKMF_workshops_gui() {
	if(isset($_POST['lagre_workshop'])) {
		if($_POST['ws_id']=='new')
			$SQL = new SQLins('ukmno_ws_ws');
		else
			$SQL = new SQLins('ukmno_ws_ws', array('ws_id'=>$_POST['ws_id']));
		
		if(empty($_POST['navn']))
			$_POST['navn'] = 'Workshop uten navn';
		
		$SQL->add('navn',$_POST['navn']);
		$SQL->add('beskrivelse',$_POST['beskrivelse']);
		$SQL->add('tresetninger',$_POST['punchline']);
		$SQL->add('for',$_POST['for']);
		$SQL->add('okt',$_POST['okt']);
		$SQL->add('rom',$_POST['rom']);
		$SQL->add('plasser',$_POST['plasser']);
		$SQL->add('url',$_POST['url']);
		
		$SQL->run();
	}
	if(!isset($_GET['ws']))
		UKMF_workshops_oversikt();
	else
		UKMF_workshops_skjema();
}

function UKMF_workshops_skjema() {
	$qry = new SQL("SELECT * FROM `ukmno_ws_ws`
					WHERE `ws_id` = '#id'",
					array('id'=>$_GET['ws']));
	$r = $qry->run('array');
		
	echo ''
		.'<form action="?page='.$_GET['page'].'" method="post">'

		.'<input type="hidden" name="ws_id" value="'.$_GET['ws'].'" />'
		.'<ul class="ukm" style="width: 580px;">'
		. '<li>'
		.  '<img src="'.UKMN_ico('professor',32,false).'" width="32" />'
		.  '<h2>Endre / legg til workshop</h2>'
		. '</li>'
		
		
		. '<li class="kunsthenting">'		
		.  '<div class="right" style="width: 550px;">'

		.   '<div class="form-field">'
		.    '<label>Navn</label>'
		.    '<input type="text" name="navn" value="'.utf8_encode($r['navn']).'" />'
		.   '</div>'
		
		.   '<div class="form-field">'
		.    '<label>Mest beregnet for</label>'
		.    '<select name="for" />'
		.	  '<option value="Kunstnere" '.($r['for']=='Kunstnere'?'selected="selected"':'').'>Kunstnere</option>'
		.	  '<option value="Scene" '.($r['for']=='Scene'?'selected="selected"':'').'>Scene</option>'
		.	  '<option value="Nettredaksjon" '.($r['for']=='Nettredaksjon'?'selected="selected"':'').'>Nettredaksjon</option>'
		.	  '<option value="Musikk" '.($r['for']=='Musikk'?'selected="selected"':'').'>Musikk</option>'
		.	  '<option value="Dansere" '.($r['for']=='Dansere'?'selected="selected"':'').'>Dansere</option>'
		.	  '<option value="Konferansierer" '.($r['for']=='Konferansierer'?'selected="selected"':'').'>Konferansierer</option>'
		.	  '<option value="Alle" '.($r['for']=='Alle'?'selected="selected"':'').'>Alle</option>'
		.	  '<option value="Videodeltakere" '.($r['for']=='Videodeltakere'?'selected="selected"':'').'>Videodeltakere</option>'
		.	 '</select>'
		.   '</div>'
		
		.   '<div class="form-field">'
		.    '<label>Dag</label>'
		.    '<select name="okt" />'
		.	  '<option value="l1"'.($r['okt']=='l1'?'selected="selected"':'').'>Mandag økt 1</option>'
		.	  '<option value="l2"'.($r['okt']=='l2'?'selected="selected"':'').'>Mandag økt 2</option>'
		.	  '<option value="s1"'.($r['okt']=='s1'?'selected="selected"':'').'>Tirsdag økt 1</option>'
		.	  '<option value="s2"'.($r['okt']=='s2'?'selected="selected"':'').'>Tirsdag økt 2</option>'
		.	  '<option value="m1"'.($r['okt']=='m1'?'selected="selected"':'').'>&Aring;pne workshop</option>'
		.	 '</select>'
		.   '</div>'		

		.   '<div class="form-field">'
		.    '<label>Rom</label>'
		.    '<input type="text" name="rom" value="'.utf8_encode($r['rom']).'" />'
		.   '</div>'

		.   '<div class="form-field">'
		.    '<label>Antall plasser</label>'
		.    '<input type="text" name="plasser" value="'.$r['plasser'].'" />'
		.   '</div>'

		.   '<div class="form-field">'
		.    '<label>Lenke til nyhetssak</label>'
		.    '<input type="text" name="url" value="'.$r['url'].'" />'
		.   '</div>'

		.   '<div class="form-field">'
		.    '<label>Punchline</label>'
		.    '<textarea name="punchline" style="width: 450px; height:120px;">'.utf8_encode($r['tresetninger']).'</textarea>'
		.   '</div>'


		.   '<div class="form-field">'
		.    '<label>Beskrivelse</label>'
		.    '<textarea name="beskrivelse" style="width: 450px; height:250px;">'.utf8_encode($r['beskrivelse']).'</textarea>'
		.   '</div>'

		
		.	'<br clear="all" /><br clear="all" />'
		.   '<div class="form-field">'
		.    '<input type="submit" name="lagre_workshop" value="Lagre" />'
		.   '</div>'
		.	'<br clear="all" />'		
		.  '</div>'
		.  '<br clear="all" />'
		. '</li>'
		.'</ul>'
		;

}

function UKMF_workshops_oversikt() {
	echo '<h2>Workshops</h2>';
	echo '<a href="?page='.$_GET['page'].'&ws=new">'.UKMN_ico('fancyplus').'Legg til workshop</a>'
		.'<br /><br />';

	$dager = array('l1'=>'Mandag økt 1',
	 			   'l2'=>'Mandag økt 2',
				   's1'=>'Tirsdag økt 1',
				   's2'=>'Tirsdag økt 2',
				   'm1'=>'Åpne workshops');
	$i = array();
	
	foreach($dager as $kode => $dag) {
		$qry = new SQL("SELECT * FROM `ukmno_ws_ws`
						WHERE `okt` = '#dag'
						ORDER BY `for` ASC, `navn` ASC",
						array('dag'=>$kode));
		$res = $qry->run();
		
		echo '<ul class="ukm">'
			. '<li>'
			.  '<img src="'.UKMN_ico('professor',32,false).'" width="32" />'
			.  '<h2>Workshops '.$dag.'</h2><a href="'.loadWorkshops($kode).'">Last ned p&aring;meldingslister</a>'
			. '</li>'
			;

		while($r = mysql_fetch_assoc($res)) {
			$teller[$kode]++;
			$plasser[$kode]+=$r['plasser'];
			echo '<li class="workshop">'
				. '<a href="?page='.$_GET['page'].'&ws='.$r['ws_id'].'">'.UKMN_icoButton('pencil',16,'Rediger').'</a>'
				.'<div>'.utf8_encode($r['navn']).'</div>'
				.'<div class="workshoprom">'.utf8_encode($r['rom']).'<br />('.$r['plasser'].' plasser)</div>'
				.'<br clear="all" />'
				.'</li>';
		}
		
		echo '<li class="workshop" id="row_footer">TOTALT '.$teller[$kode].' workshops og '.$plasser[$kode].' plasser</li>';
		
		echo '</ul>';
	}

}
?>