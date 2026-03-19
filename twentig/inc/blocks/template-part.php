<?php
/**
 * Server-side customizations for the `core/template-part` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the header template part output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_part_block( $block_content, $block ) {

	// Only process header template part with sticky scroll class.
	if ( ( $block['attrs']['slug'] ?? null ) !== 'header' || ! str_contains( $block_content, 'tw-sticky-scroll-up' ) ) {
		return $block_content;
	}

	wp_enqueue_script_module( 
		'tw-sticky-header', 
		TWENTIG_ASSETS_URI . '/blocks/template-part/view.js',
		array( '@wordpress/interactivity' ),
		TWENTIG_VERSION
	);

	$tag_processor = new WP_HTML_Tag_Processor( $block_content );

	if ( $tag_processor->next_tag( 'header' ) ) {
		$tag_processor->set_attribute( 'data-wp-interactive', 'twentig/sticky-header' );
		$tag_processor->set_attribute( 'data-wp-init', 'callbacks.init' );
		$tag_processor->set_attribute( 'data-wp-on-document--scroll', 'callbacks.handleScroll' );
		$tag_processor->set_attribute( 'data-wp-class--is-hidden', 'state.isHidden' );
	}

	return $tag_processor->get_updated_html();
}
add_filter( 'render_block_core/template-part', 'twentig_filter_part_block', 11, 2 );
