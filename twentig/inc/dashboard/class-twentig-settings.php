<?php
/**
 * Twentig Settings Class
 *
 * @package Twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Twentig Settings class.
 *
 * Manages plugin options and REST API endpoints.
 */
class TwentigSettings {

	/**
	 * Initializes the class.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'plugins_loaded', array( $this, 'disable_core_block_features' ) );
	}

	/**
	 * Registers the necessary REST API routes.
	 */
	public function register_routes() {

		register_rest_route(
			'twentig/v1',
			'/update-settings',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_settings' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * Registers the settings.
	 */
	public function register_settings() {

		register_setting(
			'twentig-options',
			'twentig-options',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_options' ),
			)
		);
	}

	/**
	 * Sanitizes the Twentig settings.
	 *
	 * @param array $settings The settings to validate.
	 * @return array The sanitized settings.
	 */
	public function sanitize_options( array $settings ) {
		$allowed_settings = array(
			'twentig_core_block_directory',
			'twentig_widgets_block_editor',
			'twentig_core_block_patterns',
			'patterns',
			'openverse',
			'predefined_spacing',
			'portfolio',
			'portfolio_slug',
			'portfolio_category_slug',
			'portfolio_tag_slug'
		);

		foreach ( $settings as $key => &$value ) {
			if ( ! in_array( $key, $allowed_settings, true ) ) {
				// Ignore any parameters not in the allowed list
				unset( $settings[$key] );
				continue;
			}

			switch ( $key ) {
				case 'twentig_core_block_directory':
				case 'twentig_widgets_block_editor':
				case 'twentig_core_block_patterns':
				case 'patterns':
				case 'openverse':
				case 'predefined_spacing':
				case 'portfolio':
					$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
					break;
				case 'portfolio_slug':
				case 'portfolio_category_slug':
				case 'portfolio_tag_slug':
					$value = sanitize_title( $value );
					break;
			}
		}

		return $settings;
	}

	/**
	 * Saves the settings and returns a response.
	 *
	 * @param WP_REST_Request $request The WP request object.
	 * @return WP_REST_Response|WP_Error The response object.
	 */
	public function save_settings( WP_REST_Request $request ) {		

		$settings = $request->get_param( 'settings' );
		$sanitized_settings = $this->sanitize_options( is_array( $settings ) ? $settings : array() );
		
		update_option( 'twentig-options', $sanitized_settings );
		
		return new WP_REST_Response(array(
			'success' => true,
			'message' => __( 'Settings saved', 'twentig' ),
		) );	
	}

	/**
	 * Disables core features based on user settings.
	 */
	public function disable_core_block_features() {
		if ( ! twentig_is_option_enabled( 'twentig_core_block_directory' ) ) {
			remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );
		}
		if ( ! twentig_is_option_enabled( 'twentig_core_block_patterns' ) ) {
			remove_theme_support( 'core-block-patterns' );
		}
		if ( ! twentig_is_option_enabled( 'twentig_widgets_block_editor' ) ) {
			add_filter( 'use_widgets_block_editor', '__return_false' );
		}
		if ( ! twentig_is_option_enabled( 'openverse' ) ) {
			add_filter( 'block_editor_settings_all', function( $settings ) {
				$settings['enableOpenverseMediaCategory'] = false;
				return $settings;
			}, 10 );
		}
	}

}
