<?php
/**
 * Server-side customizations for the `core/post-template` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the post template block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_post_template_block( $block_content, $block ) {

	$attributes  = $block['attrs'] ?? array();
	$layout      = $attributes['layout']['type'] ?? null;
	$class_names = array();

	if ( 'grid' !== $layout ) {
		return $block_content;
	}

	$columns_count = $attributes['layout']['columnCount'] ?? 3;
	if ( 1 !== $columns_count ) {
		if ( isset( $attributes['twVerticalAlignment'] ) ) {
			$class_names[] = 'tw-valign-' . $attributes['twVerticalAlignment'];
		}
		if ( isset( $attributes['twColumnWidth'] ) ) {
			$class_names[] = 'tw-cols-' . $attributes['twColumnWidth'];
		}
	}
	
	if ( ! empty( $class_names ) ) {
		$tag_processor = new WP_HTML_Tag_Processor( $block_content );
		$tag_processor->next_tag();

		foreach ( $class_names as $class_name ) {
			$tag_processor->add_class( sanitize_html_class( $class_name ) );
		}
		$block_content = $tag_processor->get_updated_html();
	}

	return $block_content;
}
add_filter( 'render_block_core/post-template', 'twentig_filter_post_template_block', 10, 2 );
