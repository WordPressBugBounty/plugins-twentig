<?php
/**
 * Server-side customizations for the `core/navigation` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the navigation block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_navigation_block( $block_content, $block ) {

	if ( empty( $block['attrs'] ) ) {
		return $block_content;
	}

	$attributes   = $block['attrs'];
	$hover_style  = $attributes['twHoverStyle'] ?? '';
	$active_style = $attributes['twActiveStyle'] ?? $hover_style;
	$overlay_menu = $attributes['overlayMenu'] ?? 'mobile';
	$class_names  = array();

	if ( $hover_style ) {
		$class_names[] = 'tw-nav-hover-' . $hover_style;
	}

	if ( $active_style ) {
		$class_names[] = 'tw-nav-active-' . $active_style;
	}

	if ( in_array( $overlay_menu, array( 'mobile', 'always' ), true ) ) {
		if ( isset( $attributes['twBreakpoint'] ) && 'mobile' === $overlay_menu ) {
			$class_names[] = 'tw-break-' . $attributes['twBreakpoint'];
		}
		if ( isset( $attributes['twMenuIconSize'] ) ) {
			$class_names[] = 'tw-icon-' . $attributes['twMenuIconSize'];
		}
	}

	if ( isset( $attributes['twGap'] ) ) {
		$class_names[] = 'tw-gap-' . $attributes['twGap'];
	}

	if ( ! empty( $class_names ) ) {
		$tag_processor = new WP_HTML_Tag_Processor( $block_content );
		$tag_processor->next_tag();
		foreach ( $class_names as $class_name ) {
			$tag_processor->add_class( sanitize_html_class( $class_name ) );
		}
		$block_content = $tag_processor->get_updated_html();
	}

	if ( 'menu' === ( $block['attrs']['icon'] ?? null ) ) {
		$block_content = str_replace(
			'<path d="M5 5v1.5h14V5H5zm0 7.8h14v-1.5H5v1.5zM5 19h14v-1.5H5V19z" />',
			'<rect x="4" y="6.5" width="16" height="1.5"></rect><rect x="4" y="11.25" width="16" height="1.5"></rect><rect x="4" y="16" width="16" height="1.5"></rect>',
			$block_content 
		);
	}

	return $block_content;
}
add_filter( 'render_block_core/navigation', 'twentig_filter_navigation_block', 10, 2 );
