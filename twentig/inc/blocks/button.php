<?php
/**
 * Server-side customizations for the `core/button` block.
 *
 * @package twentig
 */
 
defined( 'ABSPATH' ) || exit;

/**
 * Filters the button block output.
 *
 * @param string $block_content Rendered block content.
 * @param array  $block         Block object.
 * @return string Filtered block content.
 */
function twentig_filter_button_block( $block_content, $block ) {

	$attributes  = $block['attrs'] ?? array();
	$icon        = $attributes['twIcon'] ?? '';
	$show_label  = $attributes['twShowLabel'] ?? true;
	$open_dialog = $attributes['twOpenDialog'] ?? false;

	if ( empty( $icon ) && $show_label && ! $open_dialog ) {
		return $block_content;
	}

	if ( $icon ) {
		$button_icon = twentig_get_icon( $icon );
		if ( $button_icon ) {
			$position = $attributes['twIconPosition'] ?? 'right';
			$tag_name = $attributes['tagName'] ?? 'a';
			if ( ! in_array( $tag_name, array( 'a', 'button' ), true ) ) {
				$tag_name = 'a';
			}

			if ( 'left' === $position ) {
				$replacement = $show_label 
					? '$1' . $button_icon . '$2$3'
					: '$1' . $button_icon . '<span class="tw-button-text screen-reader-text">$2</span>$3';
			} else {
				$replacement = $show_label 
					? '$1$2' . $button_icon . '$3'
					: '$1<span class="tw-button-text screen-reader-text">$2</span>' . $button_icon . '$3';
			}
			
			$pattern = sprintf( '/(<%1$s[^>]*>)(.*?)(<\/%1$s>)/is', preg_quote( $tag_name, '/' ) );
			$result = preg_replace( $pattern, $replacement, $block_content, 1 );

			if ( null !== $result ) {
				$block_content = $result;
			}

			$tag_processor = new WP_HTML_Tag_Processor( $block_content );
			if ( $tag_processor->next_tag() ) {
				$tag_processor->add_class( 'tw-has-icon' );
				$tag_processor->add_class( 'has-icon__' . sanitize_html_class( $icon ) );
				$block_content = $tag_processor->get_updated_html();
			}
		}
	}

	if ( $open_dialog ) {
		$tag_processor = new WP_HTML_Tag_Processor( $block_content );
		
		if ( ! $tag_processor->next_tag( 'a' ) ) {
			return $block_content;
		}

		$url = $tag_processor->get_attribute( 'href' );		

		if ( empty( $url ) ) {
			return $block_content;
		}

		$iframe_src           = '';
		$video_src            = '';
		$class_names          = $attributes['className'] ?? '';
		$lightbox_class_names = 'tw-lightbox-video';

		if ( preg_match( '/(?:youtube\.com\/(?:(?:v|e(?:mbed)?|shorts|live)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $match ) ) {
			$iframe_src = 'https://www.youtube-nocookie.com/embed/' . esc_attr( $match[1] ) . '?autoplay=1&rel=0';
			if ( str_contains( $url, 'controls=0' ) ) {
				$iframe_src .= '&controls=0';
			}
			if ( str_contains( $url, '/shorts' ) ) {
				$lightbox_class_names .= ' tw-lightbox-9-16';
			}
		} elseif ( preg_match( '/(?:player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/[^\/]+\/videos\/|album\/\d+\/video\/|video\/|)(\d+)/i', $url, $match ) ) {
			$iframe_src = 'https://player.vimeo.com/video/' . esc_attr( $match[1] ) . '?autoplay=1';
		} elseif ( preg_match( '/\.(?:mp4|webm|ogv)(?:[?#]|$)/i', $url ) ) {
			$video_src = $url;
		}

		if ( empty( $iframe_src ) && empty( $video_src ) ) {
			return $block_content;
		}

		if ( str_contains( $class_names, 'tw-lightbox-dark' ) ) {
			$lightbox_class_names .= ' tw-lightbox-dark';
		}

		if ( str_contains( $class_names, 'tw-lightbox-full' ) ) {
			$lightbox_class_names .= ' tw-lightbox-full';
		}

		$tag_processor->remove_attribute( 'href' );
		$tag_processor->set_attribute( 'data-wp-interactive', 'twentig/modal' );
			
		$tag_processor->set_attribute(
			'data-wp-context',
			wp_json_encode(
				array(
					'videoSrc'           => esc_url( $video_src ),
					'iframeSrc'          => esc_url_raw( $iframe_src ),
					'lightboxClassNames' => $lightbox_class_names,
				),
				JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
			)
		);
		$tag_processor->set_attribute( 'data-wp-on--click', 'actions.openVideoModal' );
		$block_content = $tag_processor->get_updated_html();

		$block_content = str_replace( array( '<a ', '</a>' ), array( '<button ', '</button>' ), $block_content );
		
		add_action( 'wp_footer', 'twentig_video_print_lightbox_overlay' );

		wp_enqueue_script_module( 
			'tw-block-button', 
			TWENTIG_ASSETS_URI . '/blocks/button/view.js',
			array( '@wordpress/interactivity' ),
			TWENTIG_VERSION
		);
	}

	return $block_content;
}
add_filter( 'render_block_core/button', 'twentig_filter_button_block', 10, 2 );

/**
 * Renders the video lightbox markup.
 */
function twentig_video_print_lightbox_overlay() {
	$close_button_label = __( 'Close', 'twentig' );
	?>
	<dialog
		id="tw-modal-video"
		class="tw-lightbox-video"
		data-wp-interactive="twentig/modal"
		data-wp-context='{}'
		data-wp-on--close="actions.onCloseVideoModal"
		data-wp-on--click="actions.closeVideoModal"
		data-wp-bind--class="state.lightboxClassNames"
		>
		<button type="button" aria-label="<?php echo esc_attr( $close_button_label ); ?>" data-wp-on--click="actions.closeVideoModal" class="tw-lightbox-close-button">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z"></path></svg>
		</button>
		<div class="tw-lightbox-video-container">
			<iframe
				width="1000"
				allow="autoplay"
				allowfullscreen
				data-wp-bind--src="state.iframe"
				data-wp-bind--hidden="!state.isIframePlaying"
				hidden
			></iframe>
			<video 
				autoplay
				controls
				playsinline
				data-wp-bind--src="state.video"
				data-wp-bind--hidden="!state.isVideoPlaying"
				hidden
			></video>
		</div>
	</dialog>
	<?php
}

/**
 * Gets button icons.
 *
 * @return array Array of icon data with label and SVG markup.
 */
function twentig_get_icons() {
	$icons = array(
		'arrow-left' => array(
			'label' => __( 'Arrow Left', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="m7.825 13 5.6 5.6L12 20l-8-8 8-8 1.425 1.4-5.6 5.6H20v2z"></path></svg>',
		),
		'arrow-right' => array(
			'label' => __( 'Arrow Right', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M16.175 13H4v-2h12.175l-5.6-5.6L12 4l8 8-8 8-1.425-1.4z"></path></svg>',
		),
		'arrow-up' => array(
			'label' => __( 'Arrow Up', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M11 20V7.825l-5.6 5.6L4 12l8-8 8 8-1.4 1.425-5.6-5.6V20z"></path></svg>',
		),
		'arrow-down' => array(
			'label' => __( 'Arrow Down', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M11 4v12.175l-5.6-5.6L4 12l8 8 8-8-1.4-1.425-5.6 5.6V4z"></path></svg>',
		),
		'arrow-outward' => array(
			'label' => __( 'Arrow Outward', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M6.4 18 5 16.6 14.6 7H6V5h12v12h-2V8.4z"></path></svg>',
		),
		'external' => array(
			'label' => __( 'External', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M5 21q-.824 0-1.412-.587A1.93 1.93 0 0 1 3 19V5q0-.824.587-1.412A1.93 1.93 0 0 1 5 3h7v2H5v14h14v-7h2v7q0 .824-.587 1.413A1.93 1.93 0 0 1 19 21zm4.7-5.3-1.4-1.4L17.6 5H14V3h7v7h-2V6.4z"></path></svg>',
		),
		'arrow-alt-left' => array(
			'label' => __( 'Arrow Left Alt', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="m10 18-6-6 6-6 1.4 1.45L7.85 11H20v2H7.85l3.55 3.55z"></path></svg>',
		),
		'arrow-alt-right' => array(
			'label' => __( 'Arrow Right Alt', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="m14 18-1.4-1.45L16.15 13H4v-2h12.15L12.6 7.45 14 6l6 6z"></path></svg>',
		),
		'arrow-alt-up' => array(
			'label' => __( 'Arrow Up Alt', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M11 20V7.8l-3.6 3.6L6 10l6-6 6 6-1.4 1.4L13 7.8V20z"></path></svg>',
		),
		'arrow-alt-down' => array(
			'label' => __( 'Arrow Down Alt', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="m12 20-6-6 1.4-1.4 3.6 3.6V4h2v12.2l3.6-3.6L18 14z"></path></svg>',
		),
		'arrow-alt-outward' => array(
			'label' => __( 'Arrow Outward Alt', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M16 14.45h2V6H9.55v2h5.05L6 16.6 7.4 18 16 9.4z"></path></svg>',
		),
		'download' => array(
			'label' => __( 'Download', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="m12 16-5-5 1.4-1.45 2.6 2.6V4h2v8.15l2.6-2.6L17 11zm-6 4q-.824 0-1.412-.587A1.93 1.93 0 0 1 4 18v-3h2v3h12v-3h2v3q0 .824-.587 1.413A1.93 1.93 0 0 1 18 20z"></path></svg>',
		),
		'chevron-left' => array(
			'label' => __( 'Chevron Left', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="m13.6 18-6-6 6-6L15 7.4 10.4 12l4.6 4.6z"></path></svg>',
		),
		'chevron-right' => array(
			'label' => __( 'Chevron Right', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M13.6 12 9 7.4 10.4 6l6 6-6 6L9 16.6z"></path></svg>',
		),
		'chevron-up' => array(
			'label' => __( 'Chevron Up', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12 10.4 7.4 15 6 13.6l6-6 6 6-1.4 1.4z"></path></svg>',
		),
		'chevron-down' => array(
			'label' => __( 'Chevron Down', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="m12 16.4-6-6L7.4 9l4.6 4.6L16.6 9l1.4 1.4z"></path></svg>',
		),
		'play' => array(
			'label' => __( 'Play', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M8 19V5l11 7z"></path></svg>',
		),
		'play-circle' => array(
			'label' => __( 'Play Circle', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="m9.5 16.5 7-4.5-7-4.5zM12 22a9.7 9.7 0 0 1-3.9-.788 10.1 10.1 0 0 1-3.175-2.137q-1.35-1.35-2.137-3.175A9.7 9.7 0 0 1 2 12q0-2.075.788-3.9a10.1 10.1 0 0 1 2.137-3.175q1.35-1.35 3.175-2.137A9.7 9.7 0 0 1 12 2q2.075 0 3.9.788a10.1 10.1 0 0 1 3.175 2.137q1.35 1.35 2.137 3.175A9.7 9.7 0 0 1 22 12a9.7 9.7 0 0 1-.788 3.9 10.1 10.1 0 0 1-2.137 3.175q-1.35 1.35-3.175 2.137A9.7 9.7 0 0 1 12 22"></path></svg>',
		),
		'mail' => array(
			'label' => __( 'Mail', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M20 4q.825 0 1.412.588Q22 5.176 22 6v12q0 .825-.588 1.412A1.93 1.93 0 0 1 20 20H4q-.824 0-1.412-.588A1.93 1.93 0 0 1 2 18V6q0-.824.588-1.412A1.93 1.93 0 0 1 4 4zm-8 11L4 9v9h16V9zM4 7l8 6 8-6V6H4z"></path></svg>',
		),
		'phone' => array(
			'label' => __( 'Phone', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M19.95 21q.45 0 .75-.3t.3-.75V15.9a.88.88 0 0 0-.225-.588 1.16 1.16 0 0 0-.575-.362l-3.45-.7a1.6 1.6 0 0 0-.712.063 1.4 1.4 0 0 0-.588.337L13.1 17a16 16 0 0 1-1.8-1.213 18 18 0 0 1-1.625-1.437 18 18 0 0 1-1.513-1.662A12 12 0 0 1 6.975 10.9L9.4 8.45q.2-.2.275-.475T9.7 7.3l-.65-3.5a.9.9 0 0 0-.325-.562A.93.93 0 0 0 8.1 3H4.05q-.45 0-.75.3t-.3.75q0 3.125 1.362 6.175t3.863 5.55 5.55 3.862T19.95 21"></path></svg>',
		),
		'chat' => array(
			'label' => __( 'Chat', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M6 14h8v-2H6zm0-3h12V9H6zm0-3h12V6H6zM2 22V4q0-.824.587-1.412A1.93 1.93 0 0 1 4 2h16q.824 0 1.413.587Q22 3.176 22 4v12q0 .824-.587 1.413A1.93 1.93 0 0 1 20 18H6zm3.15-6H20V4H4v13.125z"></path></svg>',
		),
		'location' => array(
			'label' => __( 'Location', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M13.413 11.413Q14 10.825 14 10t-.588-1.412A1.93 1.93 0 0 0 12 8q-.825 0-1.412.588A1.93 1.93 0 0 0 10 10q0 .825.588 1.412Q11.175 12 12 12t1.412-.588M12 19.35q3.05-2.8 4.525-5.088Q18 11.976 18 10.2q0-2.725-1.738-4.462Q14.526 4 12 4T7.738 5.737Q6 7.476 6 10.2q0 1.775 1.475 4.063Q8.95 16.549 12 19.35M12 22q-4.024-3.425-6.012-6.363Q4 12.7 4 10.2q0-3.75 2.412-5.975T12 2t5.587 2.225T20 10.2q0 2.5-1.988 5.438T12 22"></path></svg>',
		),
		'event' => array(
			'label' => __( 'Event', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12.725 17.275Q12 16.55 12 15.5t.725-1.775T14.5 13t1.775.725T17 15.5t-.725 1.775T14.5 18t-1.775-.725M5 22q-.824 0-1.412-.587A1.93 1.93 0 0 1 3 20V6q0-.824.587-1.412A1.93 1.93 0 0 1 5 4h1V2h2v2h8V2h2v2h1q.824 0 1.413.588Q21 5.175 21 6v14q0 .824-.587 1.413A1.93 1.93 0 0 1 19 22zm0-2h14V10H5zM5 8h14V6H5z"></path></svg>',
		),
		'heart' => array(
			'label' => __( 'Heart', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="m12 19.25-1.042-.937a104 104 0 0 1-3.437-3.178q-1.355-1.322-2.136-2.354-.78-1.031-1.083-1.885A5.2 5.2 0 0 1 4 9.146Q4 7.292 5.27 6.02q1.273-1.27 3.127-1.27 1.02 0 1.979.438.958.435 1.625 1.229a4.6 4.6 0 0 1 1.625-1.23 4.7 4.7 0 0 1 1.98-.437q1.853 0 3.124 1.27Q20 7.293 20 9.147q0 .895-.292 1.729-.291.834-1.073 1.854-.78 1.02-2.145 2.365a112 112 0 0 1-3.49 3.26z"></path></svg>',
		),
		'shopping-bag' => array(
			'label' => __( 'Shopping Bag', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M6 22q-.824 0-1.412-.587A1.93 1.93 0 0 1 4 20V8q0-.824.588-1.412A1.93 1.93 0 0 1 6 6h2q0-1.65 1.175-2.825T12 2t2.825 1.175T16 6h2q.824 0 1.413.588Q20 7.175 20 8v12q0 .824-.587 1.413A1.93 1.93 0 0 1 18 22zm0-2h12V8h-2v2q0 .424-.287.713A.97.97 0 0 1 15 11a.97.97 0 0 1-.713-.287A.97.97 0 0 1 14 10V8h-4v2q0 .424-.287.713A.97.97 0 0 1 9 11a.97.97 0 0 1-.713-.287A.97.97 0 0 1 8 10V8H6zm4-14h4q0-.824-.588-1.412A1.93 1.93 0 0 0 12 4q-.825 0-1.412.588A1.93 1.93 0 0 0 10 6"></path></svg>',
		),
		'store' => array(
			'label' => __( 'Store', 'twentig' ),
			'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M21.025 11.05V19q0 .824-.587 1.413a1.93 1.93 0 0 1-1.413.587h-14q-.824 0-1.413-.587A1.93 1.93 0 0 1 3.026 19v-7.95q-.576-.525-.887-1.35-.313-.825-.013-1.8l1.05-3.4a2.2 2.2 0 0 1 .713-1.075A1.8 1.8 0 0 1 5.075 3h13.9q.675 0 1.175.413.5.412.725 1.087l1.05 3.4q.3.975-.012 1.775a3.95 3.95 0 0 1-.888 1.375m-6.8-1.05q.675 0 1.025-.463.35-.462.275-1.037l-.55-3.5h-1.95v3.7q0 .525.35.913.35.387.85.387m-4.5 0q.575 0 .938-.387.362-.388.362-.913V5h-1.95l-.55 3.5q-.1.6.262 1.05.363.45.938.45m-4.45 0q.45 0 .787-.325.338-.326.413-.825L7.025 5h-1.95l-1 3.35q-.15.5.162 1.075Q4.55 10 5.276 10m13.5 0q.724 0 1.05-.575.325-.575.15-1.075L18.925 5h-1.9l.55 3.85q.075.5.413.825a1.1 1.1 0 0 0 .787.325m-13.75 9h14v-7.05a.6.6 0 0 1-.162.05h-.088q-.675 0-1.187-.225-.513-.225-1.013-.725a3.3 3.3 0 0 1-1.025.7q-.575.25-1.225.25-.674 0-1.262-.25a3.3 3.3 0 0 1-1.038-.7 3.1 3.1 0 0 1-.988.7 2.95 2.95 0 0 1-1.212.25 3.3 3.3 0 0 1-1.312-.25 3.3 3.3 0 0 1-1.038-.7q-.525.525-1.037.737A3 3 0 0 1 5.275 12h-.113a.25.25 0 0 1-.137-.05z"></path></svg>',
		),
	);
	return $icons;
}

/**
 * Gets a specific icon's SVG markup.
 *
 * @param string $icon_name Icon identifier.
 * @return string Icon SVG markup or empty string if not found.
 */
function twentig_get_icon( $icon_name ) {
	$icons = twentig_get_icons();
	return $icons[ $icon_name ]['icon'] ?? '';
}
