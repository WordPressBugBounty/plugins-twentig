<?php
/**
 * Twentig plugin file.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

require TWENTIG_PATH . 'inc/dashboard/class-twentig-dashboard.php';
require TWENTIG_PATH . 'inc/utilities.php';
require TWENTIG_PATH . 'inc/blocks.php';
require TWENTIG_PATH . 'inc/block-styles.php';
require TWENTIG_PATH . 'inc/block-presets.php';
require TWENTIG_PATH . 'inc/block-patterns.php';
require TWENTIG_PATH . 'inc/twentig_portfolio.php';

/**
 * Loads theme-specific compatibility files.
 *
 * Conditionally includes files based on whether the active theme
 * is a block theme or classic theme, and includes theme-specific
 * compatibility layers for Twenty Twenty-series themes.
 */
function twentig_theme_support_includes() {
	$template = get_template();

	if ( wp_is_block_theme() ) {
		require_once TWENTIG_PATH . 'inc/block-themes.php';
		if ( str_starts_with( $template, 'twentytwenty' ) ) {
			$file_path = TWENTIG_PATH . "inc/compat/{$template}.php";
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
			}
			if ( in_array( $template, array( 'twentytwentyfour', 'twentytwentythree', 'twentytwentytwo' ), true ) ) {
				require_once TWENTIG_PATH . 'inc/compat/blocks.php';
				require_once TWENTIG_PATH . 'inc/compat/block-styles.php';
			}
		}
	} else {
		require_once TWENTIG_PATH . 'inc/compat/blocks.php';
		require_once TWENTIG_PATH . 'inc/compat/block-styles.php';
		if ( 'twentytwentyone' === $template || 'twentytwenty' === $template ) {
			require_once TWENTIG_PATH . "inc/classic/{$template}/{$template}.php";
		}
	}
}
add_action( 'plugins_loaded', 'twentig_theme_support_includes' );