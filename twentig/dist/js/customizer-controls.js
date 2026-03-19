(function( api, $ ) {

	api.controlConstructor['tw-range'] = api.Control.extend( {
		ready: function() {
			const control = this;
			const $input  = this.container.find( '.tw-control-range-value' );
			const $slider = this.container.find( '.tw-control-range' );

			const initialValue = control.setting() || control.setting.default;
			$slider.val( parseFloat( initialValue ) );
			$input.val( initialValue );

			$slider.on( 'input change keyup', function() {
				$input.val( $( this ).val() ).trigger( 'change' );
			} );

			if ( control.setting() === '' ) {
				$slider.val( parseFloat( $slider.attr( 'min' ) ) );
			}

			$input.on( 'change keyup', function() {
				const value = $( this ).val();
				control.setting.set( value );
				if ( value ) {
					$slider.val( parseFloat( value ) );
				} else {
					$slider.val( parseFloat( $slider.attr( 'min' ) ) );
				}
			} );

			control.setting.bind( function( value ) {
				$slider.val( parseFloat( value ) );
			} );
		}
	} );

	api.controlConstructor['checkbox-multiple'] = api.Control.extend( {
		ready: function() {
			const control = this;
			const container = control.container;

			container.on( 'change', 'input[type="checkbox"]', function() {
				const values = container.find( 'input[type="checkbox"]:checked' ).map( function() {
					return this.value;
				} ).get();
				control.setting.set( null === values ? '' : values );
			} );
		}
	} );

	api.controlConstructor['radios-selector'] = api.Control.extend({
		ready: function() {
			const control = this;

			control.container.on( 'click', '.radio-selector', function() {
				const value = $( this ).val();
				control.setting.set( '' );
				control.setting.set( value );
			} );
		}
	} );
	
	api.bind( 'ready', function() {

		$( '.customize-section-back' ).removeAttr( 'tabindex' );

		api.previewer.bind( 'ready', function() {
			api.previewer.preview.iframe.attr( 'tabindex', '-1' );
		} );

		$( '#accordion-section-title_tagline .accordion-section-title button' ).focus();

		api( 'custom_logo', function( setting ) {
			api.control( 'logo_width' ).container.toggle( Boolean( setting.get() ) );

			setting.bind( function( newValue ) {
				api.control( 'logo_width' ).container.toggle( Boolean( newValue ) );
				api.previewer.refresh();
			} );
		} );

		if ( $( '#custom-save-button' ).length === 0 ) {
			const $container = $( '<div class="custom-save-button"><button type="button" id="custom-save-button" class="button button-primary"></button></div>' );
			$container.find('button').text( twentigCustomizer.finishText );
			$( '#customize-footer-actions' ).prepend( $container );
		}

		api.previewer.bind( 'twentig-refresh-single-post', function( data ) {
			if ( ! data.isSingle ) {
				api.previewer.previewUrl.set( twentigCustomizer.firstPostUrl );
			} else {
				api.previewer.refresh();
			}
		} );

		api.previewer.bind( 'twentig-refresh-single-portfolio', function( data ) {
			if ( ! data.isSingle ) {
				api.previewer.previewUrl.set( twentigCustomizer.firstProjectUrl );
			} else {
				api.previewer.refresh();
			}
		} );

		api( 'blog_layout', function( setting ) {
			setting.bind( function() {
				if ( twentigCustomizer.blogUrl ) {
					api.previewer.previewUrl.set( twentigCustomizer.blogUrl );
				}
			} );
		} );

		api( 'portfolio_layout', function( setting ) {
			setting.bind( function() {
				if ( twentigCustomizer.portfolioUrl ) {
					api.previewer.previewUrl.set( twentigCustomizer.portfolioUrl );
				}
			} );
		} );

		api( 'home_layout', function( setting ) {
			setting.bind( function( newPageId ) {
				const newPageIdInt = parseInt( newPageId, 10 );
				api( 'page_on_front' ).set( newPageIdInt );
			} );
		} );

		api( 'starter_presets', function( setting ) {
			setting.bind( function( presetKey ) {
				if ( twentigCustomizer.presets[ presetKey ] ) {
					const presetSettings = twentigCustomizer.presets[ presetKey ];
					Object.keys( presetSettings ).forEach( function( settingId ) {
						const newValue = presetSettings[ settingId ];
						if ( ! settingId.includes( 'social' ) ) {
							api( settingId, function( setting ) {
								setting.set( newValue );
							} );
						}
						if ( settingId === 'header_elements' ) {
							const control = wp.customize.control( settingId );
							if ( control ) {
								const valuesArray = typeof newValue === 'string' ? newValue.split( ',' ) : newValue;
								control.container.find( 'input[type="checkbox"]' ).each( function() {
									$( this ).prop( 'checked', $.inArray( $( this ).val(), valuesArray ) !== -1 );
								} );
							}
						}
					} );
					
					setTimeout( function() {
						api.previewer.previewUrl.set( twentigCustomizer.homeUrl );
					}, 100 );
				
				}
			} );
		} );

		if ( twentigCustomizer.defaultSettings ) {
			Object.keys( twentigCustomizer.defaultSettings ).forEach( function( settingId ) {
				const newValue = twentigCustomizer.defaultSettings[ settingId ];
				wp.customize( settingId, function( setting ) {
					setting.set( newValue );
				} );

				if ( settingId === 'header_elements' ) {
					const control = wp.customize.control( settingId );
					if ( control ) {
						const valuesArray = typeof newValue === 'string' ? newValue.split( ',' ) : newValue;
						control.container.find( 'input[type="checkbox"]' ).each( function() {
							$( this ).prop( 'checked', $.inArray( $( this ).val(), valuesArray ) !== -1 );
						} );
					}
				}
			} );

			setTimeout( function() {
				api.previewer.previewUrl.set( twentigCustomizer.homeUrl );
			}, 100 );
		}
	} );

	$( document ).on( 'click', '#custom-save-button', function( e ) {
		const button = $( this );

		if ( button.hasClass( 'is-busy' ) ) {
			e.preventDefault();
			return;
		}

		button.addClass( 'is-busy' ).attr( 'aria-disabled', 'true' );
		
		api.previewer.save().done( function() {
			wp.ajax.post( 'twentig_update_templates', {
				nonce: twentigCustomizer.nonce,
			} )
			.done( function( response ) {
				button.removeClass( 'is-busy' ).attr( 'aria-disabled', 'false' );
				window.parent.postMessage( {
					type: 'customization-complete',
					message: 'Customization process completed successfully'
				}, window.location.origin );
			} )
			.fail( function( error ) {
				button.removeClass( 'is-busy' ).attr( 'aria-disabled', 'false' );
			} );
		} );
	} );
})( wp.customize, jQuery );