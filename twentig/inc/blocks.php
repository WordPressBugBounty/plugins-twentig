<?php
/**
 * Block assets and customizations.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

foreach ( (array) glob( wp_normalize_path( TWENTIG_PATH . 'inc/blocks/*.php' ) ) as $twentig_block_file ) {
	require_once $twentig_block_file;
}

/**
 * Enqueues block assets for frontend and backend editor.
 */
function twentig_block_assets() {
	
	// Front end.
	$asset_file             = include TWENTIG_PATH . 'dist/index.asset.php';
	$block_library_filename = wp_should_load_separate_core_block_assets() && wp_is_block_theme() ? 'blocks/common' : 'style-index';

	wp_enqueue_style(
		'twentig-blocks',
		TWENTIG_ASSETS_URI . '/' . $block_library_filename . '.css',
		array(),
		$asset_file['version']
	);

	if ( ! current_theme_supports( 'twentig-theme' ) ) {
		wp_enqueue_style(
			'twentig-blocks-compat',
			TWENTIG_ASSETS_URI . '/blocks/compat.css',
			array(),
			$asset_file['version']
		);
	}

	if ( ! is_admin() ) {
		return;
	}

	wp_enqueue_script(
		'twentig-blocks-editor',
		TWENTIG_ASSETS_URI . '/index.js',
		$asset_file['dependencies'],
		$asset_file['version'],
		array( 'in_footer' => false )
	);

	$config = array(
		'theme'          => get_template(),
		'isBlockTheme'   => wp_is_block_theme(),
		'isTwentigTheme' => current_theme_supports( 'twentig-theme' ),
		'spacingSizes'   => function_exists( 'twentig_get_spacing_sizes' ) ? twentig_get_spacing_sizes() : array(),
		'cssClasses'     => twentig_get_block_css_classes(),
		'portfolioType'  => post_type_exists( 'portfolio' ) ? 'portfolio' : '',
		'buttonIcons'    => twentig_get_icons(),
	);

	wp_add_inline_script(
		'twentig-blocks-editor',
		'var twentigEditorConfig = ' . wp_json_encode( $config ) . ';',
		'before'
	);

	wp_set_script_translations( 'twentig-blocks-editor', 'twentig' );

	wp_enqueue_style(
		'twentig-editor',
		TWENTIG_ASSETS_URI . '/index.css',
		array( 'wp-edit-blocks' ),
		$asset_file['version']
	);

	$font_url = TWENTIG_ASSETS_URI . '/css/symbols.woff2';
	$css = "@font-face{font-family:'Material Symbols';font-style:normal;font-weight:400;src:url('{$font_url}') format('woff2');}";
	wp_add_inline_style( 'wp-block-library', $css );
}
add_action( 'enqueue_block_assets', 'twentig_block_assets' );

/**
 * Adds visibility classes to the global styles.
 */
function twentig_enqueue_class_styles() {
	$breakpoints = apply_filters( 'twentig_breakpoints', array( 'mobile' => 768, 'tablet' => 1024 ) );
	$mobile      = (int) ( $breakpoints['mobile'] ?? 768 );
	$tablet      = (int) ( $breakpoints['tablet'] ?? 1024 );

	$css = sprintf(
		'@media (width < %1$dpx) { .tw-sm-hidden { display: none !important; }}' .
		'@media (%1$dpx <= width < %2$dpx) { .tw-md-hidden { display: none !important; }}' .
		'@media (width >= %2$dpx) { .tw-lg-hidden { display: none !important; }}',
		$mobile,
		$tablet
	);

	wp_add_inline_style( 'twentig-blocks', $css );
}

/**
 * Override block styles.
 */
function twentig_override_block_styles() {

	twentig_enqueue_class_styles();

	if ( twentig_theme_supports_spacing() ) {
		wp_enqueue_style(
			'twentig-global-spacing',
			TWENTIG_ASSETS_URI . "/blocks/tw-spacing.css",
			array(),
			TWENTIG_VERSION
		);
	}

	if ( ! wp_should_load_separate_core_block_assets() || ! wp_is_block_theme() ) {
		return;
	}

	// Override core blocks style.
	$overridden_blocks = array(
		'columns',
		'media-text',
		'post-template',
		'latest-posts',
	);

	foreach ( $overridden_blocks as $block_name ) {
		$style_path = TWENTIG_PATH . "dist/blocks/$block_name/block.css";
		if ( file_exists( $style_path ) ) {
			wp_deregister_style( "wp-block-{$block_name}" );
			wp_register_style(
				"wp-block-{$block_name}",
				TWENTIG_ASSETS_URI . "/blocks/{$block_name}/block.css",
				array(),
				TWENTIG_VERSION
			);

			// Add a reference to the stylesheet's path to allow calculations for inlining styles in `wp_head`.
			wp_style_add_data( "wp-block-{$block_name}", 'path', $style_path );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'twentig_override_block_styles', 20 );

/**
 * Adds block-specific inline styles.
 */
function twentig_enqueue_block_styles() {

	if ( ! wp_should_load_separate_core_block_assets() || ! wp_is_block_theme() ) {
		return;
	}

	foreach ( glob( TWENTIG_PATH . 'dist/blocks/*/style.css' ) as $path ) {
		$block_name = basename( dirname( $path ) );
		wp_enqueue_block_style(
			"core/$block_name",
			array(
				'handle' => "tw-block-$block_name",
				'src'    => TWENTIG_ASSETS_URI . "/blocks/{$block_name}/style.css",
				'path'   => $path,
			)
		);
	}
}
add_action( 'init', 'twentig_enqueue_block_styles' );

/**
 * Enqueue styles inside the editor.
 */
function twentig_block_theme_editor_styles() {

	$blocks = array(
		'columns',
		'latest-posts',
	);

	foreach ( $blocks as $block_name ) {
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

	if ( current_theme_supports( 'twentig-theme' ) || current_theme_supports( 'twentig-v2' ) ) {
		return;
	}

	add_theme_support( 'tw-spacing' );
}
add_action( 'after_setup_theme', 'twentig_block_theme_support', 11 );
