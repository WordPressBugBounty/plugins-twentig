<?php
/**
 * Additional functionalities for block themes.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

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
 * Adds support for Twentig features.
 */
function twentig_block_theme_support() {

	if ( current_theme_supports( 'twentig-v2' ) ) {
		return;
	}
	 
	add_theme_support( 'tw-spacing' );
}
add_action( 'after_setup_theme', 'twentig_block_theme_support', 11 );
