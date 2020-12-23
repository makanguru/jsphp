function hmr_adjust_elements_waitimg(){
	jQuery( '.hmr-result' ).find( 'img' ).load( function(){
		hmr_adjust_elements();
	});
}

function hmr_adjust_elements(){
	if( typeof( jQuery( '.hmr-result' ).attr( 'hmr-stop-adjust-elements' ) ) != 'undefined' )
		return false;

	jQuery( '.hmr-result > li' ).css( {'height':'auto'} );
	var resultlist = jQuery( '.hmr-result > li' );
	var i = 1;
	var h = 0;
	var elements = [];
	resultlist.each( function(){
		if( h < jQuery( this ).outerHeight() )
			h = jQuery( this ).outerHeight();
		if( i <= hmr_columns )
			elements.push( this );
		if( i == hmr_columns ){
			jQuery( elements ).each( function(){
				jQuery( this ).css({height:h+'px'});
			});
			elements = [];
			h = 0;
			i = 0;
		} else {
		
		}
		i++;
	});
}

function collect_data( wrapper ){

		var data = {};
		wrapper.find('select').each( function(){
			if( ( jQuery( this ).attr( 'name' ) != 'orderby' || jQuery( this ).val() != null ) && jQuery( this ).attr( 'disabled' ) != 'disabled' ){				
				if( jQuery( this ).val() != '' ){
					data[ jQuery( this ).attr( 'name' ) ] = jQuery( this ).val() ;
				}
			}
		});
		
		wrapper.find('input').each( function(){
			if( typeof( jQuery( this ).attr( 'name' ) ) != 'undefined' && ( typeof jQuery( this ).attr( 'disabled' ) == 'undefined' || jQuery( this ).attr( 'disabled' ) == false ) ){
				if( jQuery( this ).hasClass( 'hmr-date' ) || jQuery( this ).attr( 'type' ) == 'hidden' || jQuery( this ).attr( 'name' ).substr( jQuery( this ).attr( 'name' ).length - 2, 2 ) != '[]' ){
					if( jQuery( this ).val() != '' ){
						if( jQuery( this ).attr( 'type' ) != 'radio' || jQuery( this ).prop( 'checked' ) ){
							if( jQuery( this ).attr( 'name' ).substr( jQuery( this ).attr( 'name' ).length - 2, 2 ) != '[]' ){
								data[ jQuery( this ).attr( 'name' ) ] = jQuery( this ).val() ;
							} else {
								var data_name = jQuery( this ).attr( 'name' ).substr( 0, jQuery( this ).attr( 'name' ).length - 2 )
								if( typeof( data[ data_name ] ) == 'undefined' )
									data[ data_name ] = [];
								data[ data_name ].push( jQuery( this ).val() );
							}
						}
					}
				} else{
					var n = jQuery( this ).attr( 'name' ).substr( 0, jQuery( this ).attr( 'name' ).length - 2 );
				
					if( jQuery( this ).prop( 'checked' ) ){
						if( typeof data[n] == 'undefined' )
							data[n] = [];					
						data[n].push( jQuery( this ).val() );
					}
				}
			}
		});
		return data;
	}

function get_filter_results( start, $form ){

		var wrapper = jQuery( '.hmr-wrapper' );
		var data = {
				action	:	'hmr-search',
				data	:	collect_data( wrapper )
		};
		
		
		if( typeof start == 'undefined' ){
			location.href = '#hmr-' + JSON.stringify( data.data );
		} else {
			if( typeof $form != 'undefined' ){
				var url = $form.attr( 'action' );
				url += '#hmr-' + JSON.stringify( collect_data( $form ) );
				location.href = url;
				return;
			}
		}
		wrapper.css({opacity:.1});
		search_data = data.data;
		jQuery.post(
					hmr_ajax_root,
					data,
					function( response ){
						response = JSON.parse( response );
						if( JSON.stringify( search_data ) != JSON.stringify( response.post ) )
							return;
						wrapper.css({opacity:1});
						
						var txt = '';
						if( response.result.length > 0 ){
							for( var i = 0; i < response.result.length; i++ ){
								txt += response.result[i];
							}
						} else {
							txt = '<li class="no-result">Keine Ergebnisse gefunden</li>';
						}
						jQuery( wrapper ).find( '.hmr-result' ).html( txt );
						if( response.result.length > 0 ){
							hmr_adjust_elements_waitimg();
						}
						
						var txt = '';
						if( response.nav.length > 0 ){
							for( var i = 0; i < response.nav.length; i++ ){
								txt += response.nav[i];
							}
						}
						jQuery( wrapper ).find('ul.hmr-nav').html( txt );
						if( typeof( response.head ) != 'undefined' )
							jQuery( wrapper ).find('.hmr-result-head').html( response.head );
						
						
						if (document.createEvent) {
							hmrLoadEvent = document.createEvent( 'HTMLEvents' );
							hmrLoadEvent.initEvent( 'hmrLoadEvent', true, true, response );
						} else {
							hmrLoadEvent = document.createEventObject();
							hmrLoadEvent.eventType = 'hmrLoadEvent';
						}
						hmrLoadEvent.eventName = 'hmrLoadEvent';
						hmrLoadEvent.data = { 'response': response, 'fields' : data.data };
						
						var eventElement = document.getElementsByClassName( 'hmr-wrapper' );
						eventElement = eventElement[0];
						if (document.createEvent) {
							eventElement.dispatchEvent(hmrLoadEvent);
						} else {
							eventElement.fireEvent("on" + hmrLoadEvent.eventType, hmrLoadEvent);
						}
					}
					);
	}


jQuery( document ).ready( function(){

	
	jQuery( '.hmr-wrapper' ).find( 'input' ).keyup( function( event ){
		if(event.which == 13)
			get_filter_results();
	});


	
	jQuery( document ).on( 'change', '.hmr-filter input, .hmr-filter select', function(){
		var possible_cond_key = jQuery( this ).closest( '.hmr-element' ).attr( 'data-id' );
		var possible_cond_val = jQuery( this ).val();
		if( ( jQuery( this ).attr('type') == 'checkbox' || jQuery( this ).attr('type') == 'radio' ) && !jQuery( this ).prop( 'checked' ) )
			possible_cond_val = -2;
		jQuery( '.hmr-element-hide' ).each( function(){
			if( jQuery( this ).attr( 'data-condkey' ) == possible_cond_key ){
				if( possible_cond_val == jQuery( this ).attr( 'data-condval' ) ){
					jQuery( this ).fadeIn();
					jQuery( this ).addClass( 'hmr-element' );
					jQuery( this ).find( 'input, select' ).attr( 'disabled', false );
				}else{
					jQuery( this ).hide();
					jQuery( this ).removeClass( 'hmr-element' );
					jQuery( this ).find( 'input, select' ).attr( 'disabled', true );
				}
			}
		});
		jQuery( '.hmr-wrapper' ).find( 'input[name="page"]' ).remove();
		if( jQuery( '.hmr-wrapper' ).find( '.hmr-button-btnsearch' ).length == 0 )
			get_filter_results();
	});
	
	jQuery( document ).on( 'click','.hmr-nav-click', function( event ){
		event.preventDefault();
		jQuery( '.hmr-wrapper' ).find( 'input[name="page"]' ).remove();
		var txt = '<input type="hidden" name="page" value="' + jQuery( this ).attr( 'data-href' ) + '" />';
		jQuery( txt ).appendTo( '.hmr-wrapper' );
		get_filter_results();
		jQuery('html, body').animate({ scrollTop: ( jQuery('.hmr-wrapper').offset().top - 25 )}, 'slow');
	});
	
	if( location.hash.substr( 0, 4 ) == '#hmr-' ){
		var range_max = '';
		var range_min = '';
		var	hash = JSON.parse( location.hash.substr( 4 ) );
		var do_ajax_request = true;
		for ( property in hash ) {
			jQuery( '.hmr-element-hide[data-condkey="'+property+'"]' ).each( function(){
				if( jQuery( this ).attr( 'data-condval' ) == hash[property] ){
					jQuery( this ).show();
					jQuery( this ).addClass( 'hmr-element' );
				}
			});
				
			if( jQuery( '.hmr-filter *[name="' + property + '"]' ).attr( 'type' ) != 'radio' )
				jQuery( '.hmr-filter *[name="' + property + '"]' ).val( hash[property] );
			jQuery( '.hmr-filter input[name="' + property + '[]"]' ).each( function(){
				if( jQuery( this ).attr( 'type' ) == 'checkbox' ){
					for( var i = 0; i < hash[property].length; i++ )
						if( jQuery( this ).val() == hash[property][i] )
							jQuery( this ).prop( 'checked', true );
				}
			});
			
			var date_index = 0;
			jQuery( '.hmr-filter input.hmr-date[name="' + property + '[]"]' ).each( function(){
				jQuery( this ).val( hash[property][ date_index ] );
				date_index++;
			});
			
			jQuery( '.hmr-filter input[type="radio"][name="' + property + '"][value="' + hash[property] +'"]' ).prop('checked',true);
			if( jQuery( '.hmr-filter *[name="' + property + '"]' ).parent().hasClass( 'hmr-range-wrapper' ) ){
				var arrange_slider = true;
				jQuery( '.hmr-filter *[name="' + property + '"]' ).parent().find( 'input[type="hidden"]' ).each( function(){
					if( jQuery( this ).val() != hash[ jQuery( this ).attr( 'name') ] )
						arrange_slider = false;
				});
				if( arrange_slider ){
					var parent = jQuery( '.hmr-filter *[name="' + property + '"]' ).parent()
					parent.find( 'input[type="hidden"]' ).each( function(){						
						if( jQuery( this ).attr( 'name' ).match(/max/i) )
							range_max = parseInt( jQuery( this ).val() );
						else
							range_min = parseInt( jQuery( this ).val() );
					});
					parent.find( '.hmr-range' ).slider( "option", "values", [range_min,range_max] );	
					if( parent.attr( 'data-unitfront' ) == 1 )
						var pricetxt = parent.attr( 'data-unit' ) + range_min + ' - ' + parent.attr( 'data-unit' ) + range_max;
					else
						var pricetxt = range_min + parent.attr( 'data-unit' ) + ' - ' + range_max + parent.attr( 'data-unit' );
					parent.find( '.hmr-write' ).text( pricetxt );
				}
			}
		}
		if( do_ajax_request )
			get_filter_results( true );
	}
});
/**
 * The load event
 */
var hmrLoadEvent;
