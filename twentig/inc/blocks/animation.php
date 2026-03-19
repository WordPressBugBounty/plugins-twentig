<?php
/**
 * Server-side customizations for the core blocks.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the blocks to add animation.
 *
 * @param string $block_content The block content about to be appended.
 * @param array  $block         The full block, including name and attributes.
 * @return string Modified block content with animation classes and attributes.
 */
function twentig_add_block_animation( $block_content, $block ) {

	if ( empty( $block['attrs']['twAnimation'] ) ) {
		return $block_content;
	}

	static $script_enqueued = false;
	if ( ! $script_enqueued ) {
		wp_enqueue_script(
			'tw-block-animation',
			TWENTIG_ASSETS_URI . '/js/block-animation.js',
			array(),
			TWENTIG_VERSION,
			array(
				'in_footer' => false,
				'strategy'  => 'defer',
			)
		);
		$script_enqueued = true;
	}

	$attributes = $block['attrs'];
	$animation  = $attributes['twAnimation'];
	$duration   = $attributes['twAnimationDuration'] ?? '';
	$delay      = $attributes['twAnimationDelay'] ?? 0;

	$tag_processor = new WP_HTML_Tag_Processor( $block_content );
	$tag_processor->next_tag();
	$tag_processor->add_class( 'tw-block-animation' );
	$tag_processor->add_class( sanitize_html_class( 'tw-animation-' . $animation ) );

	if ( $duration ) {
		$tag_processor->add_class( sanitize_html_class( 'tw-duration-' . $duration ) );
	}

	if ( $delay ) {
		$style_attr = $tag_processor->get_attribute( 'style' );
		$style      = '--tw-animation-delay:' . esc_attr( $delay ) . 's;' . $style_attr;
		$tag_processor->set_attribute( 'style', $style );
	}

	return $tag_processor->get_updated_html();
}
add_filter( 'render_block', 'twentig_add_block_animation', 10, 2 );

/**
 * Handles no JavaScript detection.
 * Adds a style tag element when no JavaScript is detected.
 */
function twentig_support_no_script() {
	echo "<noscript><style>.tw-block-animation{opacity:1;transform:none;clip-path:none;}</style></noscript>\n";
}
add_action( 'wp_head', 'twentig_support_no_script' );
