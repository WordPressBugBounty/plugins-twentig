<?php
/**
 * Server-side customizations for the `core/site-logo` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the site logo block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_site_logo_block( $block_content, $block ) {

	if ( ! isset( $block['attrs']['twWidthMobile'] ) ) {
		return $block_content;
	}

	$logo_class = wp_unique_id( 'tw-logo-' );
	
	$width = (int) ( $block['attrs']['twWidthMobile'] ?? 0 );
	if ( $width <= 0 ) {
		return $block_content;
	}

	$tag_processor = new WP_HTML_Tag_Processor( $block_content );
	if ( $tag_processor->next_tag() ) {
		$tag_processor->add_class( $logo_class );
		$block_content = $tag_processor->get_updated_html();
	}

	$style = sprintf(
		'@media (max-width:767px) { .wp-block-site-logo.%1$s img{ width:%2$dpx; height:auto; } }',
		esc_attr( $logo_class ),
		$width
	);

	$action_hook_name = wp_is_block_theme() ? 'wp_head' : 'wp_footer';
	add_action(
		$action_hook_name,
		static function () use ( $style ) {
			echo '<style>' . $style . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	);
	
	return $block_content;
}
add_filter( 'render_block_core/site-logo', 'twentig_filter_site_logo_block', 10, 2 );
