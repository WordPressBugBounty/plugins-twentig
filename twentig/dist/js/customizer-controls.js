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
				control.setting.set( values.length ? values : '' );
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

	const PALETTE_SCHEMA = [
		{ slug: 'base', name: 'Base' },
		{ slug: 'base-2', name: 'Base 2' },
		{ slug: 'base-3', name: 'Base 3' },
		{ slug: 'contrast', name: 'Contrast' },
		{ slug: 'contrast-2', name: 'Contrast 2' },
		{ slug: 'accent', name: 'Accent' },
		{ slug: 'secondary', name: 'Secondary text' },
		{ slug: 'tertiary', name: 'Border' }
	];
	const PALETTE_SLUGS = PALETTE_SCHEMA.map( function( item ) {
		return item.slug;
	} );

	const isColorSafe = function( value ) {
		if ( typeof value !== 'string' || ! value.trim() ) {
			return false;
		}

		const normalized = value.trim();

		if ( /url\s*\(|expression\s*\(|javascript:|@import|-moz-binding|behavior\s*:/i.test( normalized ) ) {
			return false;
		}

		if ( /^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i.test( normalized ) ) {
			return true;
		}

		if ( normalized.toLowerCase() === 'transparent' ) {
			return true;
		}

		if ( /^(rgba?|hsla?|oklch|oklab|lab|lch|hwb|color-mix|color|var)\s*\(/i.test( normalized ) ) {
			return /^[a-z0-9\s(),.\-#%/]+$/i.test( normalized );
		}

		return false;
	};

	const normalizePalette = function( text ) {
		const fail = function() {
			throw new Error( twentigCustomizer.invalidPaletteText );
		};

		try {
			const palette = JSON.parse( text );

			if ( ! Array.isArray( palette ) || palette.length !== PALETTE_SLUGS.length ) {
				fail();
			}

			const bySlug = {};

			palette.forEach( function( item ) {
				if (
					typeof item !== 'object' ||
					item === null ||
					typeof item.slug !== 'string' ||
					typeof item.color !== 'string'
				) {
					fail();
				}

				const slug = item.slug.trim();
				const color = item.color.trim();

				if (
					! PALETTE_SLUGS.includes( slug ) ||
					bySlug[ slug ] ||
					! isColorSafe( color )
				) {
					fail();
				}

				bySlug[ slug ] = color;
			} );

			return JSON.stringify( PALETTE_SCHEMA.map( function( schemaItem ) {
				if ( ! bySlug[ schemaItem.slug ] ) {
					fail();
				}

				return {
					color: bySlug[ schemaItem.slug ],
					name: schemaItem.name,
					slug: schemaItem.slug
				};
			} ) );
		} catch ( error ) {
			fail();
		}
	};

	const clearPaletteFeedback = function( control ) {
		control.container.find( '.twentig-paste-palette-feedback' ).text( '' ).removeClass( 'is-error' );
	};

	const setPalettePanelExpanded = function( control, expanded ) {
		const $panel = control.container.find( '.twentig-paste-palette-panel' );
		const $toggle = control.container.find( '.twentig-paste-palette-toggle' );

		$panel.toggleClass( 'is-expanded', expanded ).toggleClass( 'is-collapsed', ! expanded );
		$toggle.attr( 'aria-expanded', expanded ? 'true' : 'false' );
	};

	const setPaletteError = function( control, message ) {
		clearPaletteFeedback( control );
		control.container.find( '.twentig-paste-palette-feedback' ).text( message ).addClass( 'is-error' );
		setPalettePanelExpanded( control, true );
	};

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

		api.control( 'color_palette', function( control ) {
			const $button = control.container.find( '.twentig-paste-palette-button' );
			const $radios = control.container.find( 'input[type="radio"]' );

			if ( ! $button.length ) {
				return;
			}

			setPalettePanelExpanded( control, false );

			control.container.on( 'click', '.twentig-paste-palette-toggle', function() {
				const expanded = $( this ).attr( 'aria-expanded' ) === 'true';
				setPalettePanelExpanded( control, ! expanded );
			} );

			api( 'custom_color_palette', function( customSetting ) {
				api( 'color_palette', function( paletteSetting ) {
					paletteSetting.bind( function( value ) {
						if ( value && customSetting.get() ) {
							customSetting.set( '' );
						}

						clearPaletteFeedback( control );
					} );

					customSetting.bind( function( value ) {
						if ( value ) {
							$radios.prop( 'checked', false );
						}
					} );

					control.container.on( 'change', 'input[type="radio"]', function() {
						customSetting.set( '' );
						clearPaletteFeedback( control );
					} );

					control.container.on( 'click', '.twentig-paste-palette-button', async function() {
						const $trigger = $( this );

						if ( ! window.navigator || ! window.navigator.clipboard || ! window.navigator.clipboard.readText ) {
							setPaletteError( control, twentigCustomizer.clipboardUnavailableText );
							return;
						}

						$trigger.prop( 'disabled', true );

						try {
							let text = '';

							try {
								text = await window.navigator.clipboard.readText();
							} catch ( error ) {
								throw new Error( twentigCustomizer.clipboardPermissionText );
							}

							if ( ! text.trim() ) {
								throw new Error( twentigCustomizer.emptyClipboardText );
							}

							const normalizedPalette = normalizePalette( text.trim() );

							$radios.prop( 'checked', false );
							paletteSetting.set( '' );
							customSetting.set( normalizedPalette );
							clearPaletteFeedback( control );
							setPalettePanelExpanded( control, true );
						} catch ( error ) {
							setPaletteError( control, error && error.message ? error.message : twentigCustomizer.invalidPaletteText );
						} finally {
							$trigger.prop( 'disabled', false );
						}
					} );
				} );
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

		const setPreviewUrlOnSectionExpand = function( sectionId, previewUrl, singleBodyClass ) {
			api.section( sectionId, function( section ) {
				section.expanded.bind( function( isExpanded ) {
					if ( ! isExpanded || ! previewUrl ) {
						return;
					}

					const iframe = api.previewer.preview && api.previewer.preview.iframe;
					if ( ! iframe || ! iframe.length ) {
						return;
					}

					const previewBody = iframe.contents().find( 'body' );
					if ( ! previewBody.hasClass( singleBodyClass ) ) {
						api.previewer.previewUrl.set( previewUrl );
					}
				} );
			} );
		};

		setPreviewUrlOnSectionExpand( 'blog', twentigCustomizer.blogUrl, 'single-post' );
		setPreviewUrlOnSectionExpand( 'portfolio', twentigCustomizer.portfolioUrl, 'single-portfolio' );

		api.section( 'homepage', function( section ) {
			section.expanded.bind( function( isExpanded ) {
				if ( isExpanded && twentigCustomizer.homeUrl ) {
					api.previewer.previewUrl.set( twentigCustomizer.homeUrl );
				}
			} );
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
		} )
		.fail( function() {
			button.removeClass( 'is-busy' ).attr( 'aria-disabled', 'false' );
		} );
	} );
})( wp.customize, jQuery );
