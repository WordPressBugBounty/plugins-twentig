<?php
/**
 * Server-side customizations for the `core/accordion` block.
 *
 * @package twentig
 */
 
defined( 'ABSPATH' ) || exit;

/**
 * Filters the accordion block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_accordion_block( $block_content, $block ) {
	$icon_type = $block['attrs']['twIcon'] ?? '';
	$show_icon = $block['attrs']['showIcon'] ?? true;
	
	if ( empty( $icon_type ) || ! $show_icon ) {
		return $block_content;
	}

	$tag_processor = new WP_HTML_Tag_Processor( $block_content );
	if ( $tag_processor->next_tag( array( 'class_name' => 'wp-block-accordion' ) ) ) {
		$tag_processor->add_class( 'tw-has-icon' );
		$tag_processor->add_class( 'tw-icon-' . sanitize_html_class( $icon_type ) );
		$block_content = $tag_processor->get_updated_html();

		$icon = '<svg class="accordion-arrow" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" version="1.1" aria-hidden="true" focusable="false"><path d="m12 15.4-6-6L7.4 8l4.6 4.6L16.6 8 18 9.4z"></path></svg>';
		
		if ( 'plus' === $icon_type ) {
			$icon = '<svg class="accordion-plus" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" version="1.1" aria-hidden="true" focusable="false"><path class="plus-vertical" d="M11 6h2v12h-2z"/><path d="M6 11h12v2H6z"/></svg>';
		} elseif ( 'plus-circle' === $icon_type ) {
			$icon = '<svg class="accordion-plus" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" version="1.1" aria-hidden="true" focusable="false"><path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2m0 1.75a8.25 8.25 0 1 0 0 16.5 8.25 8.25 0 0 0 0-16.5" /><path d="M11.125 7.5h1.75v9h-1.75z" class="plus-vertical" /><path d="M7.5 11.125h9v1.75h-9z" /></svg>';
		}

		$block_content = preg_replace(
			'/(<span class="wp-block-accordion-heading__toggle-icon"[^>]*>)\+(<\/span>)/',
			'$1' . $icon . '$2',
			$block_content
		);
	}

	return $block_content;
}
add_filter( 'render_block_core/accordion', 'twentig_filter_accordion_block', 10, 2 );
