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

	$query_block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'core/query' );
	$query_defaults   = isset( $query_block_type->attributes['query']['default'] ) ? $query_block_type->attributes['query']['default'] : array();
	$use_legacy_visibility_controls = (bool) apply_filters( 'twentig_use_legacy_visibility_controls', false );
	$support_viewport_visibility    = version_compare( get_bloginfo( 'version' ), '7.0', '>=' )
		&& ! $use_legacy_visibility_controls;

	$config = array(
		'theme'                      => get_template(),
		'isBlockTheme'               => wp_is_block_theme(),
		'isTwentigTheme'             => current_theme_supports( 'twentig-theme' ),
		'supportViewportVisibility'  => $support_viewport_visibility,
		'supportQueryExcludeCurrent' => array_key_exists( 'excludeCurrent', $query_defaults ),
		'spacingSizes'               => function_exists( 'twentig_get_spacing_sizes' ) ? twentig_get_spacing_sizes() : array(),
		'cssClasses'                 => twentig_get_block_css_classes(),
		'portfolioType'              => post_type_exists( 'portfolio' ) ? 'portfolio' : '',
		'buttonIcons'                => twentig_get_icons(),
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
 * Adds Twentig viewport defaults when the theme does not define them.
 *
 * @param WP_Theme_JSON_Data $theme_json Theme JSON data.
 * @return WP_Theme_JSON_Data Filtered theme JSON data.
 */
function twentig_add_default_viewport_settings( $theme_json ) {
	$theme_data = $theme_json->get_data();

	if ( isset( $theme_data['settings']['viewport'] ) ) {
		return $theme_json;
	}

	$breakpoints = apply_filters( 'twentig_breakpoints', array( 'mobile' => 768, 'tablet' => 1024 ) );
	$mobile      = (int) ( $breakpoints['mobile'] ?? 768 );
	$tablet      = (int) ( $breakpoints['tablet'] ?? 1024 );

	return $theme_json->update_with(
		array(
			'version'  => 3,
			'settings' => array(
				'viewport' => array(
					'mobile' => ( $mobile - 1 ) . 'px',
					'tablet' => ( $tablet - 1 ) . 'px',
				),
			),
		)
	);
}

if ( method_exists( 'WP_Theme_JSON', 'get_viewport_media_queries' ) ) {
	add_filter( 'wp_theme_json_data_theme', 'twentig_add_default_viewport_settings' );
}

/**
 * Adds visibility classes to the global styles.
 */
function twentig_enqueue_class_styles() {
	if ( method_exists( 'WP_Theme_JSON', 'get_viewport_media_queries' ) ) {
		$viewport_settings      = wp_get_global_settings( array( 'viewport' ) );
		$viewport_media_queries = WP_Theme_JSON::get_viewport_media_queries(
			$viewport_settings,
			array(
				'include_desktop' => true,
			)
		);

		$viewport_classes = array(
			'@mobile'  => '.tw-sm-hidden',
			'@tablet'  => '.tw-md-hidden',
			'@desktop' => '.tw-lg-hidden',
		);

		$css = '';
		foreach ( $viewport_classes as $viewport_state => $class_name ) {
			if ( isset( $viewport_media_queries[ $viewport_state ] ) ) {
				$css .= "{$viewport_media_queries[ $viewport_state ]} { {$class_name} { display: none !important; }}";
			}
		}

		wp_add_inline_style( 'twentig-blocks', $css );
		return;
	}

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
 * Overrides core block visibility rendering to use Twentig breakpoints.
 *
 * This mirrors wp_render_block_visibility_support(), except viewport media
 * queries are generated from the twentig_breakpoints filter.
 */
function twentig_render_block_visibility_support( $block_content, $block ) {
	$block_type = WP_Block_Type_Registry::get_instance()->get_registered( $block['blockName'] );

	if ( ! $block_type ) {
		return $block_content;
	}

	$block_visibility = $block['attrs']['metadata']['blockVisibility'] ?? null;

	if ( false === $block_visibility ) {
		return '';
	}

	if ( ! block_has_support( $block_type, 'visibility', true ) ) {
		return $block_content;
	}

	if ( is_array( $block_visibility ) && ! empty( $block_visibility ) ) {
		$viewport_config = $block_visibility['viewport'] ?? null;

		if ( ! is_array( $viewport_config ) || empty( $viewport_config ) ) {
			return $block_content;
		}

		$breakpoints = apply_filters( 'twentig_breakpoints', array( 'mobile' => 768, 'tablet' => 1024 ) );
		$mobile      = (int) ( $breakpoints['mobile'] ?? 768 );
		$tablet      = (int) ( $breakpoints['tablet'] ?? 1024 );

		$viewport_sizes = array(
			array(
				'name' => 'Mobile',
				'slug' => 'mobile',
				'size' => ( $mobile - 1 ) . 'px',
			),
			array(
				'name' => 'Tablet',
				'slug' => 'tablet',
				'size' => ( $tablet - 1 ) . 'px',
			),
			array(
				'name' => 'Desktop',
				'slug' => 'desktop',
			),
		);

		$viewport_media_queries = array();
		$previous_size          = null;
		foreach ( $viewport_sizes as $index => $viewport_size ) {
			if ( 0 === $index ) {
				$viewport_media_queries[ $viewport_size['slug'] ] = "@media (width <= {$viewport_size['size']})";
			} elseif ( count( $viewport_sizes ) - 1 === $index && $previous_size ) {
				$viewport_media_queries[ $viewport_size['slug'] ] = "@media (width > $previous_size)";
			} else {
				$viewport_media_queries[ $viewport_size['slug'] ] = "@media ({$previous_size} < width <= {$viewport_size['size']})";
			}

			$previous_size = $viewport_size['size'] ?? null;
		}

		$hidden_on = array();

		foreach ( $viewport_config as $viewport_config_size => $is_visible ) {
			if ( false === $is_visible && isset( $viewport_media_queries[ $viewport_config_size ] ) ) {
				$hidden_on[] = $viewport_config_size;
			}
		}

		if ( empty( $hidden_on ) ) {
			return $block_content;
		}

		sort( $hidden_on );

		$css_rules   = array();
		$class_names = array();

		foreach ( $hidden_on as $hidden_viewport_size ) {
			$visibility_class = 'wp-block-hidden-' . $hidden_viewport_size;
			$class_names[]    = $visibility_class;
			$css_rules[]      = array(
				'selector'     => '.' . $visibility_class,
				'declarations' => array(
					'display' => 'none !important',
				),
				'rules_group'  => $viewport_media_queries[ $hidden_viewport_size ],
			);
		}

		wp_style_engine_get_stylesheet_from_css_rules(
			$css_rules,
			array(
				'context'  => 'block-supports',
				'prettify' => false,
			)
		);

		if ( ! empty( $block_content ) ) {
			$processor = new WP_HTML_Tag_Processor( $block_content );
			if ( $processor->next_tag() ) {
				$processor->add_class( implode( ' ', $class_names ) );
				do {
					if ( 'IMG' === $processor->get_tag() ) {
						$processor->set_attribute( 'fetchpriority', 'auto' );
					}
				} while ( $processor->next_tag() );
				$block_content = $processor->get_updated_html();
			}
		}
	}

	return $block_content;
}

/*
 * WordPress 7.1 reads viewport breakpoints from settings.viewport. Keep the
 * compatibility override for earlier versions, which do not expose Core's
 * viewport media-query generator.
 */
if ( ! method_exists( 'WP_Theme_JSON', 'get_viewport_media_queries' ) ) {
	add_filter( 'render_block', 'twentig_render_block_visibility_support', 10, 2 );
	remove_filter( 'render_block', 'wp_render_block_visibility_support' );
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
				'ver'    => TWENTIG_VERSION,
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
