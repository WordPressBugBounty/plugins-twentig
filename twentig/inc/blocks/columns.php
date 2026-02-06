<?php
/**
 * Fix columns core spacing.
 * @see https://github.com/WordPress/gutenberg/issues/45277
 * 
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

function twentig_fix_columns_default_gap( $metadata ) {
	if ( isset( $metadata['name'] ) && $metadata['name'] === 'core/columns' ) {
		if ( isset( $metadata['supports']['spacing']['blockGap'] ) && is_array( $metadata['supports']['spacing']['blockGap'] ) ) {
			$metadata['supports']['spacing']['blockGap']['__experimentalDefault'] = 'var(--wp--style--columns-gap-default,2em)';
		}
	}
	return $metadata;
}
add_filter( 'block_type_metadata', 'twentig_fix_columns_default_gap' );
