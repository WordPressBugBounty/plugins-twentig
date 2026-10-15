
( function() {

	function initAnimations() {
		const blocks_to_animate = document.querySelectorAll( '.tw-block-animation' );

		if ( ! blocks_to_animate.length ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			blocks_to_animate.forEach( ( block ) =>
				block.classList.add( 'animated' )
			);
			return;
		}

		const callback = ( entries, obs ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'animated' );
					obs.unobserve( entry.target );
				}
			} );
		};

		const observer = new IntersectionObserver( callback, {
			rootMargin: '0px 0px -80px 0px',
		} );
		const footerObserver = new IntersectionObserver( callback );

		blocks_to_animate.forEach( ( block ) => {
			( block.closest( 'footer.wp-block-template-part' )
				? footerObserver
				: observer
			).observe( block );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initAnimations, {
			once: true,
		} );
	} else {
		initAnimations();
	}
} )();
