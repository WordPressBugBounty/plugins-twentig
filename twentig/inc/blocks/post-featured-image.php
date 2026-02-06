<?php
/**
 * Server-side customizations for the `core/post-featured-image` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the post featured image block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_post_featured_image_block( $block_content, $block ) {
	$attributes      = $block['attrs'] ?? array();
	$hover           = $attributes['twHover'] ?? '';
	$display_caption = $attributes['twDisplayCaption'] ?? false;

	if ( empty( $hover ) && ! $display_caption ) {
		return $block_content;
	}
		
	if ( ! empty( $hover ) ) {
		$hover_effect = 'tw-hover-' . sanitize_html_class( $hover );
		$tag_processor = new WP_HTML_Tag_Processor( $block_content );
		$tag_processor->next_tag();
		$tag_processor->add_class( $hover_effect );
		$block_content = $tag_processor->get_updated_html();
	}

	if ( $display_caption ) {
		$caption = get_the_post_thumbnail_caption();
		if ( $caption ) {
			$caption_html = wp_kses(
				$caption,
				array(
					'a'      => array(
						'href'   => true,
						'target' => true,
					),
					'br'     => true,
					'em'     => true,
					'strong' => true,
				)
			);
			$block_content = str_replace(
				'</figure>',
				'<figcaption class="wp-element-caption">' . $caption_html . '</figcaption></figure>',
				$block_content
			);
		}
	}
	return $block_content;
}
add_filter( 'render_block_core/post-featured-image', 'twentig_filter_post_featured_image_block', 10, 2 );
