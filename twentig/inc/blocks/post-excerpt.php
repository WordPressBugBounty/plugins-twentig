<?php
/**
 * Server-side customizations for the `core/post-excerpt` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the post excerpt block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Modified block content or empty string.
 */
function twentig_filter_post_excerpt_block( $block_content, $block ) {
	if ( ! empty( $block['attrs']['twShowManualOnly'] ) && ! has_excerpt() ) {
		return '';
	}
	return $block_content;
}
add_filter( 'render_block_core/post-excerpt', 'twentig_filter_post_excerpt_block', 10, 2 );
