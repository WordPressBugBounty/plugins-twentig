<?php
/**
 * Server-side customizations for the `core/post-terms` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the post terms block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_post_terms_block( $block_content, $block ) {
	if ( empty( $block['attrs']['twUnlink'] ) ) {
		return $block_content;
	}

	$tag_processor = new WP_HTML_Tag_Processor( $block_content );
	
	if ( $tag_processor->next_tag() ) {
		$tag_processor->add_class( 'tw-no-link' );
		while ( $tag_processor->next_tag( array( 'tag_name' => 'a' ) ) ) {
			$tag_processor->remove_attribute( 'href' );
			$tag_processor->remove_attribute( 'rel' );
		}
	}
	return $tag_processor->get_updated_html();
}
add_filter( 'render_block_core/post-terms', 'twentig_filter_post_terms_block', 10, 2 );
