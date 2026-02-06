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

	if ( 'left' === $icon_position ) {
		$tag_processor->add_class( 'tw-has-icon-left' );
	}

	$block_content = $tag_processor->get_updated_html();

	$icon_svg = '<svg class="details-arrow" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" version="1.1" aria-hidden="true" focusable="false"><path d="m12 15.375-6-6 1.4-1.4 4.6 4.6 4.6-4.6 1.4 1.4-6 6Z"></path></svg>';
	if ( 'plus' === $icon_type ) {
		$icon_svg = '<svg class="details-plus" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" version="1.1" aria-hidden="true" focusable="false"><path class="plus-vertical" d="M11 6h2v12h-2z"/><path d="M6 11h12v2H6z"/></svg>';
	} elseif ( 'plus-circle' === $icon_type ) {
		$icon_svg = '<svg class="details-plus" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" version="1.1" aria-hidden="true" focusable="false"><path d="M12 3.75c4.55 0 8.25 3.7 8.25 8.25s-3.7 8.25-8.25 8.25-8.25-3.7-8.25-8.25S7.45 3.75 12 3.75M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2Z" /><path d="M11.125 7.5h1.75v9h-1.75z" class="plus-vertical" /><path d="M7.5 11.125h9v1.75h-9z" /></svg>';
	}
	return str_replace( '</summary>', $icon_svg . '</summary>', $block_content );
}
add_filter( 'render_block_core/details', 'twentig_filter_details_block', 10, 2 );
