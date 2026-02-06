<?php
/**
 * Block assets and customizations.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

foreach ( (array) glob( wp_normalize_path( TWENTIG_PATH . '/inc/blocks/*.php' ) ) as $twentig_block_file ) {
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
		plugins_url( 'dist/' . $block_library_filename . '.css', dirname( __FILE__ ) ),
		array(),
		$asset_file['version']
	);

	if ( ! is_admin() ) {
		return;
	}

	wp_enqueue_script(
		'twentig-blocks-editor',
		plugins_url( '/dist/index.js', dirname( __FILE__ ) ),
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
	);

	wp_localize_script( 'twentig-blocks-editor', 'twentigEditorConfig', $config );

	wp_set_script_translations( 'twentig-blocks-editor', 'twentig' );

	wp_enqueue_style(
		'twentig-editor',
		plugins_url( 'dist/index.css', dirname( __FILE__ ) ),
		array( 'wp-edit-blocks' ),
		$asset_file['version']
	);
}
add_action( 'enqueue_block_assets', 'twentig_block_assets' );

/**
 * Override block styles.
 */
function twentig_override_block_styles() {

	if ( ! wp_should_load_separate_core_block_assets() || ! wp_is_block_theme() ) {
		return;
	}

	// Override core blocks style.
	$overridden_blocks = array(
		'columns',
		'gallery',
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
add_action( 'wp_enqueue_scripts', 'twentig_enqueue_class_styles' );

/**
 * Filters the blocks to add animation.
 *
 * @param string $block_content The block content about to be appended.
 * @param array  $block         The full block, including name and attributes.
 * @return string Modified block content with animation classes and attributes.
 */
function twentig_add_block_animation( $block_content, $block ) {

	if ( ! empty( $block['attrs']['twAnimation'] ) ) {

		wp_enqueue_script( 
			'tw-block-animation', 
			plugins_url( '/dist/js/block-animation.js', dirname( __FILE__ ) ),
			array(),
			'1.0',
			array(
				'in_footer' => false,
				'strategy'  => 'defer',
			)
		);

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
			$tag_processor->set_attribute( 'style', esc_attr( $style ) );
		}

		return $tag_processor->get_updated_html();
	}

	return $block_content;
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
