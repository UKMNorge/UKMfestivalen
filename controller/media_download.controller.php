<?php
require_once( PLUGIN_DIR_PATH_UKMFESTIVALEN.'../UKMvideresending_festival/functions.php' );

$forestilling = new forestilling( $_GET['c_id'] );
$alle_innslag = $forestilling->innslag();

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
	
	switch( $i->g('bt_form') ) {
		case 'smartukm_titles_video':
			$sort = 'film';
			if( sizeof( $related_media['tv'] ) == 0 ) {
				# $innslag->media->film = 'none_related';
				$media_ok = false;
			} else {
				# $innslag->media->film = $related_media['tv'];
			}
			break;
		case 'smartukm_titles_exhibition':
			$sort = 'kunst';
			
			if( sizeof( $related_media['image'] ) == 0 ) {
				$innslag->media->kunstner = 'none_uploaded';
				$media_ok = false;
			} else {
				$innslag->media->kunstner = image_selected( $innslag, 0, 'bilde_kunstner', 'original' );
				if(!is_string( $innslag->media->image )) {
					$innslag->media->image->localpath = localpath_by_rel_id( $innslag->media->image->ID );
				}
				if( $innslag->media->kunstner == 'none_selected' ) {
					$media_ok = false;
				}
			}
			
			$titler = $i->titler( $m->g('pl_id'), $videresendtil->ID );
			
			if( is_array( $titler ) ) {
				foreach( $titler as $tittel ) {
					$tittel->media = new stdClass();
					if( sizeof( $related_media['image'] ) == 0 ) {
						$tittel->media->image = 'none_uploaded';
						$media_ok = false;
					} else {
						$tittel->media->image = image_selected( $innslag, $tittel->t_id, 'bilde', 'original' );
						if(!is_string( $innslag->media->image )) {
							$innslag->media->image->localpath = localpath_by_rel_id( $innslag->media->image->ID );
						}
						if( $tittel->media->image == 'none_selected' ) {
							$media_ok = false;
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
				$media_ok = false;
			} else {
				$innslag->media->image = image_selected( $innslag, false, 'bilde', 'original' );
				if(!is_string( $innslag->media->image )) {
					$innslag->media->image->localpath = localpath_by_rel_id( $innslag->media->image->ID );
				}
				if( $innslag->media->image == 'none_selected' ) {
					$media_ok = false;
				}
			}
			
			if( sizeof( $related_media['tv'] ) == 0 ) {
				# $innslag->media->film = 'none_related';
				$media_ok = false;
			} else {
				# $innslag->media->film = $related_media['tv'];
			}
			
			if( $i->har_playback() ) {
				$innslag->playback = $i->playback();
			} else {
				$innslag->playback = false;
			}

			break;
	}	
	
	$TWIG['innslag'][] = $innslag;
}

var_dump( $TWIG['innslag'] );



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

	return UKM_HOME.'../wp-content/blogs.dir/'.$res['blog_id'].'/files/'.$res['post_meta']['file'];

/*
	$full = str_replace('http://','http123',$full);
	return '<a href="'.$large.'"><img src="'.$url.'" /></a>'
		.  '<br />'
		.  '<a href="http://ukm.no/wp-content/plugins/UKMimages/download.php?image='.$full.'&name='.$name.'" target="_blank">Last ned original</a>';
*/
}
?>