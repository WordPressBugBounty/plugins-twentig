<?php
/**
 * Server-side customizations for the `core/navigation-link` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the navigation link block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_navigation_link_block( $block_content, $block ) {

	$url        = $block['attrs']['url'] ?? '';
	$class_name = $block['attrs']['className'] ?? '';

	if ( empty( $url ) || ! str_contains( $class_name, 'is-style-tw-external-link' ) ) {
		return $block_content;
	}

	$icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6.4 18 5 16.6 14.6 7H6V5h12v12h-2V8.4z"></path></svg>';

	$block_content = preg_replace(
		'#(</a>)#',
		$icon . '$1',
		$block_content,
		1
	);

	return $block_content;
}
add_filter( 'render_block_core/navigation-link', 'twentig_filter_navigation_link_block', 10, 2 );
