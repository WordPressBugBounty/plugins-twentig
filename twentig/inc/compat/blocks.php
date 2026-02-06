<?php
/**
 * Block compatibility functions for classic themes and backward compatibility.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters the columns block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_compat_columns_block( $block_content, $block ) {

	$attributes     = $block['attrs'] ?? array();
	$classnames     = $attributes['className'] ?? '';
	$gap            = $attributes['style']['spacing']['blockGap'] ?? null;
	$horizontal_gap = is_array( $gap ) ? ( $gap['left'] ?? null ) : $gap;

	if ( twentig_theme_supports_spacing() && $horizontal_gap && str_contains( $horizontal_gap, 'px' ) ) {
		$gap_value = intval( $horizontal_gap );
		if ( $gap_value > 32 ) {
			$tag_processor = new WP_HTML_Tag_Processor( $block_content );
			$tag_processor->next_tag();
			$tag_processor->add_class( 'tw-large-gap' );
			$block_content = $tag_processor->get_updated_html();
		}
	}

	if ( str_contains( $classnames, 'tw-cols-' ) || str_contains( $classnames, 'tw-row-gap' ) ) {
		wp_enqueue_block_style(
			'core/columns',
			array(
				'handle' => 'tw-block-columns-compat',
				'src'    => TWENTIG_ASSETS_URI . '/blocks/columns/compat.css',
				'path'   => TWENTIG_PATH . 'dist/blocks/columns/compat.css',
			) 
		);
	}

	return $block_content;
}
add_filter( 'render_block_core/columns', 'twentig_filter_compat_columns_block', 10, 2 );

/**
 * Filters the column block output to add a CSS var to store the width attribute.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_compat_column_block( $block_content, $block ) {

	if ( wp_should_load_separate_core_block_assets() ) {
		return $block_content;
	}

	if ( isset( $block['attrs']['width'] ) ) {
		$tag_processor = new WP_HTML_Tag_Processor( $block_content );
		$tag_processor->next_tag();
		
		$style_attr = $tag_processor->get_attribute( 'style' );
		$style      = '--col-width:' . $block['attrs']['width'] . ';' . $style_attr;
		
		$tag_processor->set_attribute( 'style', $style );
		$block_content = $tag_processor->get_updated_html();
	}

	return $block_content;
}
add_filter( 'render_block_core/column', 'twentig_filter_compat_column_block', 10, 2 );

/**
 * Handles deprecation for our block settings by filtering the block before it's processed.
 *
 * @param array $parsed_block The block being rendered.
 * @return array Modified block data.
 */
function twentig_filter_compat_render_block_data( $parsed_block ) {
	$block_name = $parsed_block['blockName'];
	if ( 'core/post-author' === $block_name ) {
		if ( ! empty( $parsed_block['attrs']['twIsLink'] ) ) {
			$parsed_block['attrs']['isLink'] = true;
		}
	} elseif ( 'core/post-excerpt' === $block_name ) {
		$attributes = $parsed_block['attrs'];
		if ( isset( $attributes['twExcerptLength'] ) ) {
			$parsed_block['attrs']['excerptLength'] = $attributes['twExcerptLength'];
		}
	}

	return $parsed_block;
}
add_filter( 'render_block_data', 'twentig_filter_compat_render_block_data' );

/**
 * Filters the query block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_compat_query_block( $block_content, $block ) {

	$attributes = $block['attrs'] ?? array();
	$layout     = $attributes['displayLayout']['type'] ?? null;

	if ( ! $layout ) {
		return $block_content;
	}

	$class_names = array();
	$style       = '';

	if ( isset( $attributes['twBlockGapVertical'] ) ) {
		$style .= '--tw-gap-y:' . $attributes['twBlockGapVertical'] . ';';
	}

	if ( isset( $attributes['twBlockGapHorizontal'] ) ) {
		$style .= '--tw-gap-x:' . $attributes['twBlockGapHorizontal'] . ';';
	}

	if ( ! empty( $style ) ) {
		$class_names[] = 'tw-custom-gap';
	}

	if ( isset( $attributes['twVerticalAlignment'] ) ) {
		$class_names[] = 'tw-valign-' . $attributes['twVerticalAlignment'];
	}

	if ( isset( $attributes['twColumnWidth'] ) && ( 'flex' === $layout || str_contains( $block_content, 'wp-block-post-template-is-layout-grid' ) ) ) {
		$class_names[] = 'tw-cols-' . $attributes['twColumnWidth'];
	}

	if ( $style || $class_names ) {
		$tag_processor = new WP_HTML_Tag_Processor( $block_content );
		$tag_processor->next_tag( array( 'class_name' => 'wp-block-post-template' ) );
		if ( $style ) {
			$style_attr = $tag_processor->get_attribute( 'style' );
			$style     .= $style_attr;
			$tag_processor->set_attribute( 'style', $style );
		}

		if ( $class_names ) {
			foreach ( $class_names as $class_name ) {
				$tag_processor->add_class( sanitize_html_class( $class_name ) );
			}
		}

		$block_content = $tag_processor->get_updated_html();
	}

	return $block_content;
}
add_filter( 'render_block_core/query', 'twentig_filter_compat_query_block', 10, 2 );

/**
 * Filters the gallery block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_compat_gallery_block( $block_content, $block ) {

	if ( twentig_theme_supports_spacing() ) {
		$gap = $block['attrs']['style']['spacing']['blockGap'] ?? null;
		$gap = is_array( $gap ) && isset( $gap['left'] ) ? $gap['left'] : null;
		$gap = $gap ? $gap : '16px';

		if ( $gap && str_contains( $gap, 'px' ) ) {
			$tag_processor = new WP_HTML_Tag_Processor( $block_content );
			$tag_processor->next_tag();

			$gap_value = intval( $gap );

			if ( $gap_value > 32 ) {
				$tag_processor->add_class( 'tw-large-gap' );
			} elseif ( $gap_value > 16 ) {
				$tag_processor->add_class( 'tw-medium-gap' );
			}

			$block_content = $tag_processor->get_updated_html();
		}
	}

	$block_content = str_replace( 'tw-stack-sm', 'tw-cols-large', $block_content );

	return $block_content;
}
add_filter( 'render_block_core/gallery', 'twentig_filter_compat_gallery_block', 0, 2 );


/**
 * Gets spacing sizes.
 *
 * @return array Array of spacing size presets.
 */
function twentig_get_spacing_sizes() {

	$sizes = array(
		array(
			'slug' => '0',
			'name' => '0px',
			'size' => '0px',
		),
		array(
			'slug' => '1',
			'name' => '5px',
			'size' => '5px',
		),
		array(
			'slug' => '2',
			'name' => '10px',
			'size' => '10px',
		),
		array(
			'slug' => '3',
			'name' => '15px',
			'size' => '15px',
		),
		array(
			'slug' => '4',
			'name' => '20px',
			'size' => '20px',
		),
		array(
			'slug' => '5',
			'name' => '30px',
			'size' => '30px',
		),
		array(
			'slug' => '6',
			'name' => '40px',
			'size' => '40px',
		),
		array(
			'slug' => '7',
			'name' => '50px',
			'size' => '50px',
		),
		array(
			'slug' => '8',
			'name' => '60px',
			'size' => '60px',
		),
		array(
			'slug' => '9',
			'name' => '80px',
			'size' => '80px',
		),
		array(
			'slug' => '10',
			'name' => '100px',
			'size' => '100px',
		),
		array(
			'slug' => 'auto',
			'name' => 'auto',
			'size' => 'auto',
		),
	);
	return $sizes;
}

/**
 * Adds margin classes to the global styles.
 */
function twentig_enqueue_compat_class_styles() {

	$spacing_sizes = twentig_get_spacing_sizes();
	$css_spacing   = '';

	if ( empty( $spacing_sizes ) ) {	
		return;
	}
	
	foreach ( $spacing_sizes as $preset ) {
		$css_spacing .= '.tw-mt-' . esc_attr( $preset['slug'] ) . '{margin-top:' . esc_attr( $preset['size'] ) . '!important;}';
		$css_spacing .= '.tw-mb-' . esc_attr( $preset['slug'] ) . '{margin-bottom:' . esc_attr( $preset['size'] ) . '!important;}';
	}
	wp_add_inline_style( 'twentig-blocks', $css_spacing );
}
add_action( 'wp_enqueue_scripts', 'twentig_enqueue_compat_class_styles' );

/**
 * Enqueue spacing styles inside the editor.
 */
function twentig_compat_block_editor_spacing_styles() {
	$spacing_sizes = twentig_get_spacing_sizes();
	if ( empty( $spacing_sizes ) ) {
		return;
	}

	$css_spacing = '';
	foreach ( $spacing_sizes as $preset ) {
		$css_spacing .= '.tw-mt-' . esc_attr( $preset['slug'] ) . '{margin-top:' . esc_attr( $preset['size'] ) . '!important;}';
		$css_spacing .= '.tw-mb-' . esc_attr( $preset['slug'] ) . '{margin-bottom:' . esc_attr( $preset['size'] ) . '!important;}';
	}

	wp_add_inline_style( 'wp-block-library', $css_spacing );
}
add_action( 'admin_init', 'twentig_compat_block_editor_spacing_styles' );
