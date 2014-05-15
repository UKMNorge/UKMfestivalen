/* PERSONER OG ROM-ADMINISTRASJON */
	jQuery(document).on('click', '#person_leggtil button.submit', function(){
		btn_ajax_do( jQuery(this) );
		var data = {
			action: 'UKMfestivalen_ajax',
			subaction: 'overnatting_person_leggtil', 
			gruppe: jQuery('#person_leggtil #gruppe').val(),
			navn: jQuery('#person_leggtil #navn').val(),
			mobil: jQuery('#person_leggtil #mobil').val(),
			epost: jQuery('#person_leggtil #epost').val(),
			romtype: jQuery('#person_leggtil input[name="romtype"]:checked').val(),
			dobbeltromID: jQuery('#person_leggtil #dobbeltrom_med_hvem').val()
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			var data = jQuery.parseJSON( response );
			
			if( data.success ) {
				btn_ajax_success( jQuery('#person_leggtil_knapp') );
				jQuery('#personer tbody').append( twigJSovernattingperson_rad.render( data ) );
				jQuery('#person_leggtil').each(function(){this.reset()});
			} else {
				btn_ajax_error( jQuery('#person_leggtil_knapp') );
			}
		});
	});	
	
	
	jQuery(document).on('click focus change','#person_leggtil input[name="romtype"]', function(){
		if( jQuery('#person_leggtil input[name="romtype"]:checked').val() == 'dobbel' ) {
			jQuery(document).trigger('dobbeltrom_vis');
			jQuery(document).trigger('dobbeltrom_last_ledig_kapasitet');
		} else {
			jQuery(document).trigger('dobbeltrom_skjul');
		}

	});
	
	
	jQuery(document).on('dobbeltrom_vis', function(){
		jQuery('#dobbeltrom_deling').slideDown();
	});
	jQuery(document).on('dobbeltrom_skjul', function(){
		jQuery('#dobbeltrom_deling').slideUp();
	});
	jQuery(document).on('dobbeltrom_last_ledig_kapasitet', function(){
		var data = {
			action: 'UKMfestivalen_ajax',
			subaction: 'overnatting_ledig_dobbeltrom'
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			var data = jQuery.parseJSON( response );
			
			if( data.success ) {
				jQuery('#dobbeltrom_med_hvem optgroup').html( twigJSledigkapasitet.render( data ) );
			} else {
				console.error('Kunne ikke laste inn ledig kapasitet');
			}
		});

	});

	

/* GRUPPE-ADMINISTRASJON */
	jQuery(document).on('click', '#gruppe_opprett', function(){
		btn_ajax_do( jQuery(this) );
		var data = {
			action: 'UKMfestivalen_ajax',
			subaction: 'overnatting_gruppe_leggtil', 
			gruppe: jQuery('#gruppe_navn').val()
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			var data = jQuery.parseJSON( response );
			
			if( data.success ) {
				btn_ajax_success( jQuery('#gruppe_opprett') );
				jQuery('#grupper tbody').append( twigJSovernattinggruppe_rad.render( data ) );
				jQuery('#gruppe_navn').val('');
			} else {
				btn_ajax_error( jQuery('#gruppe_opprett') );
			}
	
		});
	});	
	jQuery(document).on('click','a.delete', function(){
		alert('Kontakt Marius');
	});
	
	
// BUTTON STATES FOR AJAX
	function btn_ajax_do( btn, text ) {
		if( text == undefined || text == null )
			text = 'Lagrer...';
		btn.addClass('btn-info').removeClass( btn.attr('data-class') ).html( text );
	}
	function btn_ajax_success( btn, text ) {
		if( text == undefined || text == null )
			text = 'Lagret!';
		btn.removeClass('btn-info').addClass( 'btn-success' ).html( text );
		setTimeout(function() {
			btn_ajax_reset( btn );	
		}, 2000);
	}
	
	function btn_ajax_error( btn ) {
		btn_ajax_reset( btn );
	}
	
	function btn_ajax_reset( btn ) {
		jQuery(btn).html(btn.attr('data-text')).removeClass('btn-success').addClass(btn.attr('data-class'));
	}