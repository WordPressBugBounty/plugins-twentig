<?php
/**
 * Server-side customizations for the `core/video` block.
 *
 * @package twentig
 */
 
defined( 'ABSPATH' ) || exit;

/**
 * Filters the video block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_video_block( $block_content, $block ) {

	$tw_controls  = $block['attrs']['twMinimalControls']['enable'] ?? false;
	$play_in_view = $block['attrs']['twAutoplayInView'] ?? false;

	if ( ! $tw_controls && ! $play_in_view ) {
		return $block_content;
	}
	
	wp_enqueue_script_module( 
		'tw-block-video', 
		TWENTIG_ASSETS_URI . '/blocks/video/view.js',
		array( '@wordpress/interactivity' ),
		TWENTIG_VERSION
	);
		
	$tag_processor = new WP_HTML_Tag_Processor( $block_content );
	$tag_processor->next_tag();
		
	$tag_processor->set_attribute( 'data-wp-interactive', 'twentig/video-play-pause' );
	$tag_processor->set_attribute(
		'data-wp-context',
		wp_json_encode(
			array(
				'isVideoStarted'  => false,
				'isVideoPaused'   => true,
				'isVideoPlaying'  => false,
				'isVideoEnded'    => false,
				'ariaLabelPause'  => esc_html_x( 'Pause video', 'video control', 'twentig' ),
				'ariaLabelPlay'   => esc_html_x( 'Play video', 'video control', 'twentig' ),
				'ariaLabelReplay' => esc_html_x( 'Replay video', 'video control', 'twentig' ),
			),
			JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
		)
	);

	if ( $tw_controls ) {
		$tag_processor->add_class( 'minimal-controls' );
	}
		
	$tag_processor->next_tag( 'video' );

	if ( $play_in_view ) {
		$tag_processor->set_attribute( 'data-wp-run', 'callbacks.checkVisibility' );
		$tag_processor->set_attribute( 'muted', true );
		$tag_processor->set_attribute( 'playsinline', true );
	}
				
	if ( $tw_controls ) {
		$loop     = $tag_processor->get_attribute( 'loop' );
		$controls = $tag_processor->get_attribute( 'controls' );
		$style    = $block['attrs']['twMinimalControls']['style'] ?? 'light';
		$position = $block['attrs']['twMinimalControls']['position'] ?? 'center';

		$tag_processor->set_attribute( 'data-wp-on--playing', 'actions.handleVideoEvent' );
		$tag_processor->set_attribute( 'data-wp-on--pause', 'actions.handleVideoEvent' );
		$tag_processor->set_attribute( 'data-wp-on--ended', 'actions.handleVideoEvent' );

		$button_classes = 'play-button is-' . sanitize_html_class( $style );
	
		if ( $controls ) {
			$tag_processor->set_attribute( 'controls', false );
			$tag_processor->set_attribute( 'data-wp-bind--controls', 'context.isVideoStarted' );

			$button_html  = '<button class="'. $button_classes . '" data-wp-on--click="actions.playVideo" aria-label="'. esc_html_x( 'Play video', 'video control', 'twentig' ) .'" data-wp-bind--hidden="context.isVideoStarted">
				<svg viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
					<path d="M8 19V5l11 7z" class="control-visible"/>
				</svg></button>';
		} else {
			$button_html  = '<button class="'. $button_classes . '" data-wp-on--click="actions.playPauseVideo" data-wp-bind--aria-label="state.ariaLabel" data-wp-class--playing="context.isVideoPlaying">
				<svg viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
					<path data-wp-class--control-visible="context.isVideoPaused" d="M8 19V5l11 7z"/>
					<path data-wp-class--control-visible="context.isVideoPlaying" d="M10.5 18H7V6h3.5zm6.5 0h-3.5V6H17z"/>
				';
				
			if ( $loop === null ) {
				$button_html  .= '<path data-wp-class--control-visible="context.isVideoEnded" d="M12 5.333c-.175 0-.35.017-.517.034l1.525-1.525-1.175-1.175-3.508 3.5 3.508 3.508L13.008 8.5 11.55 7.042C11.7 7.025 11.842 7 12 7a5.84 5.84 0 0 1 5.833 5.833A5.84 5.84 0 0 1 12 18.667a5.84 5.84 0 0 1-5.833-5.834H4.5a7.5 7.5 0 1 0 7.5-7.5" />';
			}
			$button_html  .= '</svg></button>';
		}

		$custom_controls_html = sprintf(
			'<div class="custom-controls is-%s">%s</div>',
			sanitize_html_class( $position ),
			$button_html
		);
	}

	$block_content = $tag_processor->get_updated_html();
		
	if ( $tw_controls ) {
		$block_content = str_replace( '</video>', '</video>' . $custom_controls_html, $block_content );
	}

	return $block_content;	
}
add_filter( 'render_block_core/video', 'twentig_filter_video_block', 10, 2 );
