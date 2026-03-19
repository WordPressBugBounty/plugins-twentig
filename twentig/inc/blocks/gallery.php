<?php
/**
 * Server-side customizations for the `core/gallery` block.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sets the lightbox scale attribute to "contain" for images inside a gallery
 * that has an aspect ratio with image crop disabled.
 *
 * @param string $block_content The block content.
 * @param array  $block         The full block, including name and attributes.
 * @return string The unmodified block content.
 */
function twentig_gallery_image_scale_attr( $block_content, $block ) {
	$attributes    = $block['attrs'] ?? array();
	$scale_contain = isset( $attributes['aspectRatio'] ) && false === ( $attributes['imageCrop'] ?? true );
	if ( ! $scale_contain ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	$metadata  = array();
	while ( $processor->next_tag( array( 'tag_name' => 'figure', 'class_name' => 'wp-block-image' ) ) ) {
		$context = $processor->get_attribute( 'data-wp-context' );
		if ( $context ) {
			$data = json_decode( html_entity_decode( $context ), true );
			if ( ! empty( $data['imageId'] ) ) {
				$metadata[ $data['imageId'] ] = array( 'scaleAttr' => 'contain' );
			}
		}
	}
	if ( $metadata ) {
		wp_interactivity_state( 'core/image', array( 'metadata' => $metadata ) );
	}
	return $block_content;
}
add_filter( 'render_block_core/gallery', 'twentig_gallery_image_scale_attr', 10, 2 );

/**
 * Filters the gallery block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_gallery_block( $block_content, $block ) {

	$attributes = $block['attrs'] ?? array();
	$layout     = $attributes['twLayout'] ?? null;
	$animation  = $attributes['twAnimation'] ?? '';

	if ( ! $layout && ! $animation ) {
		return $block_content;
	}

	$tag_processor = new WP_HTML_Tag_Processor( $block_content );
	$tag_processor->next_tag();

	if ( $animation && 'carousel' !== $layout ) {
		$duration = $attributes['twAnimationDuration'] ?? '';
		$delay    = $attributes['twAnimationDelay'] ?? 0;

		$tag_processor->set_bookmark( 'tw-gallery' );
		$tag_processor->remove_class( 'tw-block-animation' );

		while ( $tag_processor->next_tag( 'figure' ) ) {
			if ( ! $tag_processor->has_class( 'tw-block-animation' ) ) {
				$tag_processor->add_class( 'tw-block-animation' );
				$tag_processor->add_class( 'tw-animation-' . sanitize_html_class( $animation ) );

				if ( $duration ) {
					$tag_processor->add_class( 'tw-duration-' . sanitize_html_class( $duration ) );
				}

				if ( $delay ) {
					$style_attr = $tag_processor->get_attribute( 'style' );
					$style      = '--tw-animation-delay:' . esc_attr( $delay ) . 's;' . $style_attr;
					$tag_processor->set_attribute( 'style', $style );
				}
			}
		}
		$tag_processor->seek( 'tw-gallery' );
		$block_content = $tag_processor->get_updated_html();
	}

	if ( 'justified' === $layout ) {
		$rowHeight = (int) ( $attributes['twRowHeight'] ?? 250 );
		$crop      = $attributes['imageCrop'] ?? true;
		$tag_processor->remove_class( 'columns-default' );

		$style_attr = $tag_processor->get_attribute( 'style' );
		$style      = '--tw-row-height:' . esc_attr( $rowHeight ) . 'px;' . $style_attr;
		$tag_processor->set_attribute( 'style', $style );

		if ( $crop ) {
			while ( $tag_processor->next_tag( 'figure' ) ) {
				$tag_processor->set_bookmark( 'figure' );
				$tag_processor->next_tag( 'img' );

				$ratio         = '';
				$attachment_id = absint( $tag_processor->get_attribute( 'data-id' ) );
				$tag_processor->set_attribute( 'decoding', 'auto' );

				if ( ! $attachment_id ) {
					$class = $tag_processor->get_attribute( 'class' );
					if ( ! empty( $class ) && preg_match( '/wp-image-([0-9]+)/i', $class, $class_id ) ) {
						$attachment_id = absint( $class_id[1] );
					}
				}

				if ( $attachment_id ) {
					$metadata = wp_get_attachment_metadata( $attachment_id );
					if ( is_array( $metadata ) && isset( $metadata['width'], $metadata['height'] ) && (int) $metadata['height'] !== 0 ) {
						$ratio = round( (int) $metadata['width'] / (int) $metadata['height'], 6 );
					}
				} else {
					$image_src = $tag_processor->get_attribute( 'src' );
					if ( $image_src ) {
						$query = wp_parse_url( str_replace( '&amp;', '&', $image_src ), PHP_URL_QUERY );
						if ( $query ) {
							$query_params = wp_parse_args( $query );
							if ( isset( $query_params['w'], $query_params['h'] ) && is_numeric( $query_params['w'] ) && is_numeric( $query_params['h'] ) && (int) $query_params['h'] !== 0 ) {
								$ratio = round( (int) $query_params['w'] / (int) $query_params['h'], 6 );
							}
						}
					}
				}

				if ( $ratio ) {
					$tag_processor->seek( 'figure' );
					$style_attr = $tag_processor->get_attribute( 'style' );
					$style      = '--tw-ratio:' . esc_attr( $ratio ) . ';' . $style_attr;
					$tag_processor->set_attribute( 'style', $style );
				}
			}
		}

		$block_content = $tag_processor->get_updated_html();

	} elseif ( 'carousel' === $layout ) {

		wp_enqueue_script_module(
			'tw-block-gallery',
			TWENTIG_ASSETS_URI . '/blocks/gallery/view.js',
			array( '@wordpress/interactivity' ),
			TWENTIG_VERSION
		);

		$slides_count     = count( $block['innerBlocks'] );
		$settings         = $attributes['twCarousel'] ?? array();
		$arrows_position  = $settings['arrowsPosition'] ?? 'overlay';
		$markers_position = $settings['markersPosition'] ?? 'below';
		$columns          = (int) ( $attributes['columns'] ?? 3 );
		$view             = $settings['view'] ?? '';
		$show_edges       = 'adjacent-images' === $view;

		if ( 1 !== $columns || str_contains( $arrows_position, 'below' ) ) {
			$markers_position = 'none';
		}

		$tag_processor->set_attribute( 'role', 'region' );
		$tag_processor->set_attribute( 'aria-roledescription', __( 'carousel', 'twentig' ) );
		$tag_processor->set_attribute( 'aria-label', __( 'Image gallery', 'twentig' ) );
		$tag_processor->set_attribute( 'data-wp-interactive', 'twentig/carousel' );
		$tag_processor->set_attribute( 'data-wp-init', 'callbacks.initCarousel' );
		$tag_processor->set_attribute(
			'data-wp-context',
			wp_json_encode(
				array(
					'showEdges'    => $show_edges,
					'currentIndex' => 0,
					'maxIndex'     => $slides_count - 1,
				),
				JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
			)
		);

		if ( $view ) {
			$tag_processor->add_class( 'tw-' . sanitize_html_class( $view ) );
		}

		$block_content = $tag_processor->get_updated_html();

		$controls_html = '';

		if ( 'none' !== $arrows_position ) {
			$controls_html .= '<div class="tw-carousel-arrows is-position-' . esc_attr( $arrows_position ) . '">';
			$controls_html .= '<button type="button" class="tw-carousel-arrow tw-carousel-arrow-left" aria-label="' . esc_attr__( 'Previous slide', 'twentig' ) . '" data-wp-on--click="actions.goPrevious" data-wp-bind--disabled="state.isAtStart"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="m13.6 18-6-6 6-6L15 7.4 10.4 12l4.6 4.6-1.4 1.4Z"></path></svg></button>';
			$controls_html .= '<button type="button" class="tw-carousel-arrow tw-carousel-arrow-right" aria-label="' . esc_attr__( 'Next slide', 'twentig' ) . '" data-wp-on--click="actions.goNext" data-wp-bind--disabled="state.isAtEnd"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M13.6 12 9 7.4 10.4 6l6 6-6 6L9 16.6l4.6-4.6Z"></path></svg></button>';
			$controls_html .= '</div>';
		}

		if ( 'none' !== $markers_position ) {
			$controls_html .= '<ul class="tw-gallery-carousel-nav is-position-' . esc_attr( $markers_position ) . '">';

			for ( $i = 1; $i <= $slides_count; $i++ ) {
				/* translators: %s: image number */
				$button_text = sprintf( __( 'Image %s', 'twentig' ), $i );
				$context = array(
					'index' => $i - 1,
				);

				$controls_html .= sprintf(
					'<li><button type="button" aria-label="%2$s" data-wp-on--click="actions.goToIndex" data-wp-class--selected="state.isMarkerActive" data-wp-bind--aria-current="state.isMarkerActive" data-wp-context="%1$s"></button></li>',
					htmlspecialchars( wp_json_encode( $context ), ENT_QUOTES, 'UTF-8' ),
					esc_attr( $button_text )
				);
			}
			$controls_html .= '</ul>';
		}



		$replace_regex = sprintf(
			'/(^\s*<%1$s\b[^>]*\bwp-block-gallery\b[^>]*>)(.*)(<\/%1$s>\s*$)/ms',
			preg_quote( 'figure', '/' )
		);

		$block_content = preg_replace_callback(
			$replace_regex,
			static function ( $matches ) use ( $controls_html ) {
			return $matches[1] . '<div class="tw-gallery-carousel-list">' . $matches[2] . '</div>' . $controls_html . $matches[3];
			},
			$block_content
		);
	}

	return $block_content;
}
add_filter( 'render_block_core/gallery', 'twentig_filter_gallery_block', 10, 2 );
