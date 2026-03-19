<?php
/**
 * Server-side customizations for the `core/group` block.
 *
 * @package twentig
 */
 
defined( 'ABSPATH' ) || exit;

/**
 * Filters the group block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_group_block( $block_content, $block ) {

	$attributes   = $block['attrs'] ?? array();
	$hover_bg     = $attributes['twHoverBackgroundColor'] ?? '';
	$hover_text   = $attributes['twHoverTextColor'] ?? '';
	$hover_border = $attributes['twHoverBorderColor'] ?? '';
	$hover_shadow = $attributes['twHoverShadow'] ?? '';

	if ( ! $hover_bg && ! $hover_text && ! $hover_border && ! $hover_shadow ) {
		return $block_content;
	}

	$tag_processor = new WP_HTML_Tag_Processor( $block_content );
	if ( $tag_processor->next_tag() ) {

		$style = '';
		if ( $hover_bg ) {
			$style .= twentig_get_color_preset_value( $hover_bg, '--hover-background-color' );
			$tag_processor->add_class( 'tw-has-hover-bg' );
		}

		if ( $hover_text ) {
			$style .= twentig_get_color_preset_value( $hover_text, '--hover-text-color' );
			$tag_processor->add_class( 'tw-has-hover-text' );
		}

		if ( $hover_border ) {
			$style .= twentig_get_color_preset_value( $hover_border, '--hover-border-color' );
			$tag_processor->add_class( 'tw-has-hover-border' );
		}

		if ( $hover_shadow ) {
			$style .= twentig_get_shadow_preset_value( $hover_shadow, '--hover-box-shadow' );
			$tag_processor->add_class( 'tw-has-hover-shadow' );
		}
		
		$style_attr = $tag_processor->get_attribute( 'style' );
		$tag_processor->set_attribute( 'style', $style . $style_attr );

		return $tag_processor->get_updated_html();
	}

	return $block_content;
}
add_filter( 'render_block_core/group', 'twentig_filter_group_block', 10, 2 );

/**
 * Converts color value to CSS custom property.
 *
 * @param string $value   Color value or preset reference.
 * @param string $css_var CSS custom property name.
 * 
 * @return string CSS declaration.
 */
function twentig_get_color_preset_value( $value, $css_var ) {
	if ( str_contains( $value, 'var:preset|color|' ) ) {
		$index_to_splice = strrpos( $value, '|' ) + 1;
		$slug  = _wp_to_kebab_case( substr( $value, $index_to_splice ) );
		$color = "var(--wp--preset--color--$slug)";
	} else {
		$color = $value;
	}
	return $css_var . ':' . esc_attr( $color ) . ';';
}

/**
 * Converts shadow preset to CSS custom property.
 *
 * @param string $value   Shadow preset slug.
 * @param string $css_var CSS custom property name.
 * 
 * @return string CSS declaration.
 */
function twentig_get_shadow_preset_value( $value, $css_var ) {
	$value = sanitize_key( $value );
	if ( ! $value ) {
		return '';
	}
	$shadow = "var(--wp--preset--shadow--$value)";
	return $css_var . ':' . esc_attr( $shadow ) . ';';
}
