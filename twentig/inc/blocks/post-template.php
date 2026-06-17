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

	$columns_count            = $attributes['layout']['columnCount'] ?? 3;
	$has_minimum_column_width = ! empty( $attributes['layout']['minimumColumnWidth'] );

	if ( 1 !== $columns_count ) {
		if ( isset( $attributes['twVerticalAlignment'] ) ) {
			$class_names[] = 'tw-valign-' . $attributes['twVerticalAlignment'];
		}
		if ( ! empty( $attributes['twColumnWidth'] ) && ! $has_minimum_column_width ) {
			$class_names[] = 'tw-cols-' . $attributes['twColumnWidth'];
		}
		if ( $has_minimum_column_width ) {
			$class_names[] = 'tw-is-responsive';
		}
	}

	if ( $has_minimum_column_width || ! empty( $class_names ) ) {
		$tag_processor = new WP_HTML_Tag_Processor( $block_content );
		$tag_processor->next_tag();

		if ( $has_minimum_column_width ) {
			$tag_processor->remove_class( 'tw-cols-small' );
			$tag_processor->remove_class( 'tw-cols-large' );
		}

		foreach ( $class_names as $class_name ) {
			$tag_processor->add_class( sanitize_html_class( $class_name ) );
		}
		$block_content = $tag_processor->get_updated_html();
	}

	return $block_content;
}
add_filter( 'render_block_core/post-template', 'twentig_filter_post_template_block', 10, 2 );
