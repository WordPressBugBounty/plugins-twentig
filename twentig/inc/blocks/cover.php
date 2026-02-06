<?php
/**
 * Server-side customizations for the `core/cover` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the cover block output to add responsive image sizes.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_cover_block( $block_content, $block ) {

	$attributes = $block['attrs'] ?? array();
	$image_id = $attributes['id'] ?? null;

	if ( ! $image_id && ! empty( $attributes['useFeaturedImage'] ) ) {
		$image_id = get_post_thumbnail_id();
	}

	if ( ! $image_id ) {
		return $block_content;
	}

	$image_meta = wp_get_attachment_metadata( $image_id );

	if ( ! $image_meta || empty( $image_meta['width'] ) ) {
		return $block_content;
	}

	$width = absint( $image_meta['width'] );

	if ( ! $width ) {
		return $block_content;
	}

	$sizes = sprintf( 
		'(max-width: 799px) 200vw, (max-width: %1$dpx) 100vw, %1$dpx', 
		$width 
	);

	if ( ! empty( $attributes['style']['dimensions']['aspectRatio'] ) ) {
		$sizes = sprintf( 
			'(max-width: 799px) 125vw, (max-width: %1$dpx) 100vw, %1$dpx', 
			$width 
		);
	}

	$tag_processor = new WP_HTML_Tag_Processor( $block_content );

	if ( $tag_processor->next_tag( 'img' ) ) {
		$tag_processor->set_attribute( 'sizes', $sizes );
		$block_content = $tag_processor->get_updated_html();
	}

	return $block_content;
}
add_filter( 'render_block_core/cover', 'twentig_filter_cover_block', 10, 2 );
