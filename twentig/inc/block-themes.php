<?php
/**
 * Additional functionalities for block themes.
 *
 * @package twentig
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue styles for block themes: spacing, layout.
 */
function twentig_block_theme_enqueue_scripts() {
	if ( twentig_theme_supports_spacing() ) {
		wp_enqueue_style(
			'twentig-global-spacing',
			TWENTIG_ASSETS_URI . "/blocks/tw-spacing.css",
			array(),
			TWENTIG_VERSION
		);
	}
}
add_action( 'wp_enqueue_scripts', 'twentig_block_theme_enqueue_scripts', 11 );

/**
 * Enqueue styles inside the editor.
 */
function twentig_block_theme_editor_styles() {

	$fse_blocks = array(
		'columns',
		'latest-posts',
	);

	foreach ( $fse_blocks as $block_name ) {
		add_editor_style( TWENTIG_ASSETS_URI . "/blocks/{$block_name}/block.css" );
	}

	if ( twentig_theme_supports_spacing() ) {
		add_editor_style( TWENTIG_ASSETS_URI . "/blocks/tw-spacing.css" );
		add_editor_style( TWENTIG_ASSETS_URI . "/blocks/tw-spacing-editor.css" );
	}
}
add_action( 'admin_init', 'twentig_block_theme_editor_styles' );

/**
 * Fix columns core spacing
 * 
 * @see https://github.com/WordPress/gutenberg/issues/45277
 * 
 */
function twentig_fix_columns_default_gap( $metadata ) {
	if ( isset( $metadata['name'] ) && $metadata['name'] === 'core/columns' ) {
		if ( isset( $metadata['supports']['spacing']['blockGap']) && is_array( $metadata['supports']['spacing']['blockGap'] ) ) {
			$metadata['supports']['spacing']['blockGap']['__experimentalDefault'] = 'var(--wp--style--columns-gap-default,2em)';
		}
	}
	return $metadata;
}
add_filter( 'block_type_metadata', 'twentig_fix_columns_default_gap' );

/**
 * Adds support for Twentig features.
 */
function twentig_block_theme_support() {

	if ( current_theme_supports( 'twentig-v2' ) ) {
		return;
	}
	
	add_theme_support( 'tw-spacing' );

	require TWENTIG_PATH . 'inc/compat/custom-fonts.php';

	$theme = get_template();
	
	if ( ! str_starts_with( $theme, 'twentytwenty' ) && 'unset' === get_option( 'twentig_curated_fonts', 'unset' ) ) {
		$has_curated = twentig_find_curated_fonts() ? 'enabled' : 'disabled';
		update_option( 'twentig_curated_fonts', $has_curated );
	}

	if ( ( 'twentytwentythree' === $theme || 'twentytwentytwo' === $theme ) || 'enabled' === get_option( 'twentig_curated_fonts', 'unset' ) ) {
		add_filter( 'twentig_show_curated_fonts', '__return_true' );
	}
}
add_action( 'after_setup_theme', 'twentig_block_theme_support', 11 );
