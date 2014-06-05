<?php
require_once( PLUGIN_DIR_PATH_UKMFESTIVALEN.'../UKMvideresending_festival/functions.php' );

if( isset( $_GET['kunstnere'] ) ) {
	$zipnavn = 'UKM-Festivalen '. date('Y') .' Kunstnere';
	$alle_innslag = $m->innslag();
	$bildetype = 'bilde_kunstner';
} else {
	$forestilling = new forestilling( $_GET['c_id'] );
	$zipnavn = 'UKM-Festivalen '. date('Y') .' '. $forestilling->get('c_name');
	$alle_innslag = $forestilling->innslag();
	$bildetype = 'bilde';
}

$counter = 0;
foreach( $alle_innslag as $order => $inn ) {
	$i = new innslag( $inn['b_id'] );
	
	if( $i->tittellos() )
		continue;
		
	$innslag = new stdClass();
	$innslag->ID 		= $i->g('b_id');
	$innslag->navn 		= $i->g('b_name');
	$innslag->media		= new stdClass();
	$innslag->rekkefolge = $order+1;

	$related_media = $i->related_items();
	
	// LETER BILDER AV KUNSTNERE
	if( $bildetype == 'bilde_kunstner' ) {
		if( $i->g('bt_form') != 'smartukm_titles_exhibition' ) {
			continue;
		}
		$innslag->rekkefolge = '';
	
		if( sizeof( $related_media['image'] ) == 0 ) {
			$innslag->media->image = 'none_uploaded';
		} else {
			$innslag->media->image = image_selected( $innslag, 0, 'bilde_kunstner', 'original' );
			if(!is_string( $innslag->media->image )) {
				$innslag->media->image->localpath = localpath_by_rel_id( $innslag->media->image->ID );
			}
		}
	// VI LETER BILDER AV KUNSTVERK OG SCENEINNSLAG
	} else {
		switch( $i->g('bt_form') ) {
			case 'smartukm_titles_video':
				$sort = 'film';
				break;
			case 'smartukm_titles_exhibition':
				$sort = 'kunst';			
				$titler = $i->titler( $m->g('pl_id'), $videresendtil->ID );
				
				if( is_array( $titler ) ) {
					foreach( $titler as $tittel ) {
						$tittel->media = new stdClass();
						if( sizeof( $related_media['image'] ) == 0 ) {
							$tittel->media->image = 'none_uploaded';
						} else {
							$tittel->media->image = image_selected( $innslag, $tittel->t_id, 'bilde', 'original' );
							if(!is_string( $innslag->media->image )) {
								$innslag->media->image->localpath = localpath_by_rel_id( $innslag->media->image->ID );
							}
						}
						$innslag->titler[] = $tittel;
					}
				}
				break;
			default:
				$sort = 'scene';
				
				if( sizeof( $related_media['image'] ) == 0 ) {
					$innslag->media->image = 'none_uploaded';
				} else {
					$innslag->media->image = image_selected( $innslag, false, 'bilde', 'original' );
					if(!is_string( $innslag->media->image )) {
						$innslag->media->image->localpath = localpath_by_rel_id( $innslag->media->image->ID );
					}
				}
					
				if( $i->har_playback() ) {
					$innslag->playback = $i->playback();
				} else {
					$innslag->playback = false;
				}
	
				break;
		}	
	}
	$TWIG['innslag'][] = $innslag;
}

if(isset($_GET['zip'])) {
	require_once('UKM/zip.class.php');
	$forestilling = new forestilling( $_GET['c_id'] );
	$zip = new zip( $zipnavn , true );
	foreach( $TWIG['innslag'] as $innslag ) {
		if( !is_string( $innslag->media->image ) ) {
			$extPos = strrpos($innslag->media->image->localpath, '.');
			$ext = substr($innslag->media->image->localpath, $extPos);
			
			$path = $innslag->media->image->localpath;
			$name = $zipnavn . $innslag->rekkefolge .' '. $innslag->navn . $ext;
			
			$zip->add( $path, $name );
		}
	}
	$TWIG['zipfile'] = $zip->compress();
}


function localpath_by_rel_id($rel_id) {
	if( !is_numeric( $rel_id ) ) {
		return false;
	}
	$qry = new SQL("SELECT * 
					FROM `ukmno_wp_related`
					WHERE `rel_id` = '#rel_id'",
					array('rel_id'=>$rel_id));
	$res = $qry->run('array');
	$res['post_meta'] = unserialize($res['post_meta']);

	$url = $res['blog_url'].'/files/'.$res['post_meta']['sizes']['thumbnail']['file'];
	$full = $res['blog_url'].'/files/'.$res['post_meta']['file']; 
	$large = $res['blog_url'].'/files/'
			. (isset($res['post_meta']['sizes']['large']) 
				? $res['post_meta']['sizes']['large']['file']
				: $res['post_meta']['file']
				);
	if($url == '/files/')
		return 'bilde mangler';

	return '/home/ukmno/public_html/wp-content/blogs.dir/'.$res['blog_id'].'/files/'.$res['post_meta']['file'];
}
?>