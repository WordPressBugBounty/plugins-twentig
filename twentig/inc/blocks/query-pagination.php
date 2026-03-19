<?php
/**
 * Adds spacing support (margin and padding) to the `core/query-pagination` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Extend core/query-pagination to support margin and padding.
 *
 * @param array  $args       Block type registration arguments.
 * @param string $block_name Block name.
 * @return array Modified block registration arguments.
 */
function twentig_block_supports( $args, $block_name ) {
	if ( 'core/query-pagination' === $block_name ) {
		if ( ! isset( $args['supports']['spacing'] ) ) {
			$args['supports']['spacing'] = array();
		}
		$args['supports']['spacing']['margin'] = array( 'top', 'bottom' );
		$args['supports']['spacing']['padding'] = true;
	}
	return $args;
}
add_filter( 'register_block_type_args', 'twentig_block_supports', 10, 2 );
