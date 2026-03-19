<?php
/**
 * Twentig Customizer Class
 *
 * @package Twentig
 */

defined( 'ABSPATH' ) || exit;

class Twentig_Customizer {
	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( get_option( 'twentig-customize-starter' ) ) {
			add_action( 'init', array( $this, 'load_dependencies' ) );
			add_action( 'after_setup_theme', array( $this, 'theme_support' ) );
		}
	}

	/**
	 * Loads required dependencies for customizer functionality.
	 */
	public function load_dependencies() {
		require_once TWENTIG_PATH . 'inc/dashboard/customizer/class-customizer-controls.php';
		require_once TWENTIG_PATH . 'inc/dashboard/customizer/class-customizer-updater.php';
		new Twentig_Customizer_Controls();
		new Twentig_Customizer_Updater();
	}

	/**
	 * Adds theme support for custom logo.
	 */
	public function theme_support() {
		add_theme_support( 'custom-logo', array(
			'flex-width'  => true,
			'flex-height' => true,
		) );
	}
}
