<?php
/**
 * Server-side customizations for the `core/separator` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the separator block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_separator_block( $block_content, $block ) {
	$attributes = $block['attrs'] ?? array();
	$width      = $attributes['twWidth'] ?? '';
	$height     = $attributes['twHeight'] ?? '';
	$style      = '';

	if (
		( empty( $width ) && empty( $height ) ) ||
		str_contains( $block_content, 'is-style-dots' ) ||
		str_contains( $block_content, 'is-style-tw-asterisks' )
	) {
		return $block_content;
	}

	$tag_processor = new WP_HTML_Tag_Processor( $block_content );
	$tag_processor->next_tag();

	if ( ! empty( $width ) ) {
		$style .= 'width: ' . esc_attr( $width ) . '; max-width: 100%;';
	}

	if ( ! empty( $height ) ) {
		$style .= 'height: ' . esc_attr( $height ) . ';';
		if ( ! empty( $width ) && intval( $height ) > intval( $width ) ) {
			$tag_processor->add_class( 'is-vertical' );
		}
	}

	$style_attr = $tag_processor->get_attribute( 'style' );
	$style     .= $style_attr;
	$tag_processor->set_attribute( 'style', $style );

	return $tag_processor->get_updated_html();
}
add_filter( 'render_block_core/separator', 'twentig_filter_separator_block', 10, 2 );
