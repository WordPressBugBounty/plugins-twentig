<?php
/**
 * Twentig icon collection.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Twentig icon collection.
 */
function twentig_register_icon_collection() {
	wp_register_icon_collection(
		'twentig',
		array(
			'label'       => __( 'Material Symbols', 'twentig' ),
			'description' => __( 'A curated collection of Material Symbols provided by Twentig.', 'twentig' ),
		)
	);
}

/**
 * Registers the icons from the Twentig icon library manifest.
 */
function twentig_register_icons() {
	$manifest_path = TWENTIG_PATH . 'inc/icon-library-manifest.php';

	if ( ! is_readable( $manifest_path ) ) {
		wp_trigger_error(
			__FUNCTION__,
			__( 'Twentig icon collection manifest is missing or unreadable.', 'twentig' )
		);
		return;
	}

	$collection = include $manifest_path;

	if ( ! is_array( $collection ) ) {
		wp_trigger_error(
			__FUNCTION__,
			__( 'Twentig icon collection manifest is invalid.', 'twentig' )
		);
		return;
	}

	$icons_directory = TWENTIG_PATH . 'dist/images/icon-library/';

	foreach ( $collection as $icon_name => $icon_data ) {
		wp_register_icon(
			'twentig/' . $icon_name,
			array(
				'label'     => $icon_data['label'],
				'file_path' => $icons_directory . $icon_data['filePath'],
			)
		);
	}
}

if ( function_exists( 'wp_register_icon_collection' ) ) {
	add_action( 'init', 'twentig_register_icon_collection', 0 );
	add_action( 'init', 'twentig_register_icons' );
}
