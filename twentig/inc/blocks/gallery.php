<?php
/**
 * Server-side customizations for the `core/gallery` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the gallery block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_gallery_block( $block_content, $block ) {

	$attributes = $block['attrs'] ?? array();
	$layout     = $attributes['twLayout'] ?? null;
	$animation  = $attributes['twAnimation'] ?? '';

	$scale_contain = isset( $block['attrs']['twImageRatio'] ) && false === ( $block['attrs']['imageCrop'] ?? true );
	if ( $scale_contain ) {
		$block_content = str_replace( 'scaleAttr&quot;:false', 'scaleAttr&quot;:&quot;contain&quot;', $block_content );
	}

	if ( ! $animation ) {
		return $block_content;
	}

	$tag_processor = new WP_HTML_Tag_Processor( $block_content );
	$tag_processor->next_tag();
	
	$duration = $attributes['twAnimationDuration'] ?? '';
	$delay    = $attributes['twAnimationDelay'] ?? 0;

	$tag_processor->set_bookmark( 'tw-gallery' );
	$tag_processor->remove_class( 'tw-block-animation' );

	while ( $tag_processor->next_tag( 'figure' ) ) {
		if ( ! $tag_processor->has_class( 'tw-block-animation' ) ) {
			$tag_processor->add_class( 'tw-block-animation' );
			$tag_processor->add_class( 'tw-animation-' . $animation );

			if ( $duration ) {
				$tag_processor->add_class( 'tw-duration-' . $duration );
			}

			if ( $delay ) {
				$style_attr = $tag_processor->get_attribute( 'style' );
				$style      = '--tw-animation-delay:' . esc_attr( $delay ) . 's;' . $style_attr;
				$tag_processor->set_attribute( 'style', $style );
			}				
		}
	}
	$tag_processor->seek( 'tw-gallery' );
	$block_content = $tag_processor->get_updated_html();
	return $block_content;
}
add_filter( 'render_block_core/gallery', 'twentig_filter_gallery_block', 10, 2 );
