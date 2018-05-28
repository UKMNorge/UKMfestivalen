/* PERSONER OG ROM-ADMINISTRASJON */
	jQuery(document).on('reset_person_leggtil', function(){
		var data = {action: 'UKMfestivalen_ajax',
					subaction: 'overnatting_person_resetform'
					};
		jQuery.post(ajaxurl, data, function(response){
			jQuery('#person_leggtil').html( twigJSovernattingperson_form.render( jQuery.parseJSON(response) ) );
		});
	});
	
	jQuery(document).on('click', '.resetPersonLeggtil', function(e){
		e.preventDefault();
		jQuery(document).trigger('reset_person_leggtil');
	});
	
	jQuery(document).on('click', 'tr.person a.edit', function(e) {
	 e.preventDefault();
	 data = {action: 'UKMfestivalen_ajax',
	         subaction: 'overnatting_person_load',
	         ID: jQuery(this).parents('tr').attr('data-id')
	         };
	 jQuery.post(ajaxurl, data, function(response) {
		jQuery('#person_leggtil').html( twigJSovernattingperson_form.render( jQuery.parseJSON(response) ) );
		jQuery(document).trigger('load_romdeling');
	 });
	});

	jQuery(document).on('click', '#person_leggtil button.submit', function(){
		btn_ajax_do( jQuery(this) );
		var data = {
			action: 'UKMfestivalen_ajax',
			subaction: 'overnatting_person_leggtil', 
			gruppe: jQuery('#gruppe').val(),
			person: jQuery('#person_leggtil #person').val(),
			navn: jQuery('#person_leggtil #navn').val(),
			mobil: jQuery('#person_leggtil #mobil').val(),
			epost: jQuery('#person_leggtil #epost').val(),
			ankomst: jQuery('#person_leggtil #ankomst').val(),
			avreise: jQuery('#person_leggtil #avreise').val(),
			romtype: jQuery('#person_leggtil input[name="romtype"]:checked').val(),
			romID: jQuery('#person_leggtil #rom_med_hvem').val()
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			var data = jQuery.parseJSON( response );
			
			if( data.success ) {
				btn_ajax_success( jQuery('#person_leggtil_knapp') );
				person = jQuery('#personer tbody').find('tr#person_'+data.person.ID);
				if( person.length != 0 ) {
					person.remove();
				}	
				jQuery('#personer tr.noonehere').each(function(){
					jQuery(this).remove();
				});
				jQuery('#personer tbody').append( twigJSovernattingperson_rad.render( data ) );
				jQuery(document).trigger('reset_person_leggtil');
			} else {
				btn_ajax_error( jQuery('#person_leggtil_knapp') );
			}
		});
	});	
	
	
	jQuery(document).on('click focus change','#person_leggtil input[name="romtype"]', function(){
		jQuery(document).trigger('load_romdeling');
	});

	jQuery(document).on('load_romdeling', function() {
		var romtype = jQuery('#person_leggtil input[name="romtype"]:checked').val();
		
		if( romtype == 'enkelt' ) {
			jQuery('#rom_deling').slideUp();
		} else {
			jQuery('#rom_deling').slideDown();
			jQuery(document).trigger('rom_last_ledig_kapasitet');
		}
	});	
	
	jQuery(document).on('rom_last_ledig_kapasitet', function(){
		var data = {
			action: 'UKMfestivalen_ajax',
			subaction: 'overnatting_ledig_rom',
			person: jQuery('#person_leggtil #person').val(),
			romtype: jQuery('#person_leggtil input[name="romtype"]:checked').val()
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			var data = jQuery.parseJSON( response );
			
			if( data.success ) {
				jQuery('#rom_med_hvem optgroup').html( twigJSledigkapasitet.render( data ) );
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
	jQuery(document).on('click', '.deletePerson', function(e){
		e.preventDefault();
		var id = jQuery(this).parents('tr').attr('data-id');
		var data = {
			action: 'UKMfestivalen_ajax',
			subaction: 'overnatting_person_delete',
			ID: id,
		};
		jQuery.post(ajaxurl, data, function(response){
			jQuery('#person_'+ id ).slideUp();
		});
	});
	
/* ADMINISTRER LEDERE FRA FYLKENE */
jQuery(document).on('click', '#gotofylkebutton', function(){
	var target = jQuery('#gotofylke').val();
	jQuery(this).attr('href', target);
	return true;
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