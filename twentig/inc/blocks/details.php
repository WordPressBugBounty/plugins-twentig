<?php
/**
 * Server-side customizations for the `core/details` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the details block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_details_block( $block_content, $block ) {
	$icon_type = $block['attrs']['twIcon'] ?? '';
	
	if ( empty( $icon_type ) ) {
		return $block_content;
	}

	$icon_position = $block['attrs']['twIconPosition'] ?? 'right';
	$tag_processor = new WP_HTML_Tag_Processor( $block_content );
	$tag_processor->next_tag();
	$tag_processor->add_class( 'tw-has-icon' );
	$tag_processor->add_class( 'tw-icon-' . sanitize_html_class( $icon_type ) );

	if ( 'left' === $icon_position ) {
		$tag_processor->add_class( 'tw-has-icon-left' );
	}

	$block_content = $tag_processor->get_updated_html();

	$icon_svg = '<svg class="details-arrow" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" version="1.1" aria-hidden="true" focusable="false"><path d="m12 15.4-6-6L7.4 8l4.6 4.6L16.6 8 18 9.4z"></path></svg>';
	if ( 'plus' === $icon_type ) {
		$icon_svg = '<svg class="details-plus" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" version="1.1" aria-hidden="true" focusable="false"><path class="plus-vertical" d="M11 6h2v12h-2z"/><path d="M6 11h12v2H6z"/></svg>';
	} elseif ( 'plus-circle' === $icon_type ) {
		$icon_svg = '<svg class="details-plus" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" version="1.1" aria-hidden="true" focusable="false"><path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2m0 1.75a8.25 8.25 0 1 0 0 16.5 8.25 8.25 0 0 0 0-16.5" /><path d="M11.125 7.5h1.75v9h-1.75z" class="plus-vertical" /><path d="M7.5 11.125h9v1.75h-9z" /></svg>';
	}
	return str_replace( '</summary>', $icon_svg . '</summary>', $block_content );
}
add_filter( 'render_block_core/details', 'twentig_filter_details_block', 10, 2 );
