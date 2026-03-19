(function( api, $ ) {

	api( 'portfolio_width', function( value ) {	
		var onChange = function( to ) {
			$( '.wp-block-group:has(.type-portfolio)' ).toggleClass( 'is-layout-constrained', to === 'wide' );
		};
		onChange( value.get() );
		value.bind( onChange );
	} );

	api( 'header_width', function( value ) {	
		var onChange = function( to ) {
			$( 'header.wp-block-template-part > .wp-block-group' ).toggleClass( 'is-layout-constrained', to === 'wide' );
		};
		onChange( value.get() );
		value.bind( onChange );
	} );

	api( 'footer_width', function( value ) {	
		var onChange = function( to ) {
			$( 'footer.wp-block-template-part > .wp-block-group' ).toggleClass( 'is-layout-constrained', to === 'wide' );
		};
		onChange( value.get() );
		value.bind( onChange );
	} );

	api( 'footer_style', function( value ) {
		var onChange = function( to ) {
			var $container = $( 'footer.wp-block-template-part > .wp-block-group' );
			$container.removeClass( 'has-base-background-color has-base-2-background-color has-contrast-background-color has-base-color has-border-top' ).css( 'border-top-color', '' ).css( 'border-top-width', '' );
			if ( to === 'subtle' ) {
				$container.addClass( 'has-base-2-background-color' );
			} else if ( to === 'inverse' ) {
				$container.addClass( 'has-base-color has-contrast-background-color' );
			} else if ( to === 'border' ) {
				$container.addClass( 'has-base-background-color has-border-top' );
			} else {
				$container.addClass( 'has-base-background-color' );
			}
		};
		onChange( value.get() );
		value.bind( onChange );
	} );

	api( 'logo_width', function( value ) {
		value.bind( function( to ) {
			$( '.wp-block-site-logo img' ).css( 'width', to + 'px' );
		} );
	} );

	[ 'single_layout', 'comments', 'single_navigation' ].forEach( function( controlId ) {
		api( controlId, function( value ) {
			value.bind( function() {
				api.preview.send( 'twentig-refresh-single-post', {
					isSingle: $( 'body' ).hasClass( 'single-post' )
				} );
			} );
		} );
	} );

	[ 'portfolio_single_layout', 'portfolio_single_navigation' ].forEach( function( controlId ) {
		api( controlId, function( value ) {
			value.bind( function() {
				api.preview.send( 'twentig-refresh-single-portfolio', {
					isSingle: $( 'body' ).hasClass( 'single-portfolio' )
				} );
			} );
		} );
	} );

})( wp.customize, jQuery );