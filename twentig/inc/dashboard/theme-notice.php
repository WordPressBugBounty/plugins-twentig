<?php
/**
 * Theme notice functionality for promoting Twentig One theme.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Determines whether the theme notice should be displayed on the current screen.
 *
 * @return bool True if the notice should be shown, false otherwise.
 */
function twentig_should_show_theme_notice() {
	if ( get_template() === 'twentig-one' ) {
		return false;
	}

	if ( get_user_meta( get_current_user_id(), '_twentig_theme_notice', true ) ) {
		return false;
	}

	if ( ! current_user_can( 'switch_themes' ) && ! current_user_can( 'install_themes' ) ) {
		return false;
	}

	if ( ! function_exists( 'get_current_screen' ) ) {
		return false;
	}

	$screen      = get_current_screen();
	$allowed_ids = array( 'dashboard', 'themes', 'plugins' );

	if ( ! $screen || ! in_array( $screen->id, $allowed_ids, true ) ) {
		return false;
	}

	return true;
}

/**
 * Enqueues the styles and script needed for the theme notice.
 *
 * Hooked into admin_enqueue_scripts so assets are registered and enqueued
 * via the proper WordPress enqueueing API instead of inline output in the
 * admin_notices hook body.
 */
function twentig_enqueue_theme_notice_assets() {
	if ( ! twentig_should_show_theme_notice() ) {
		return;
	}

	$css = '
		.twentig-link-notice {
			display: flex;
			padding: 0;
			gap: 20px;
			border-left-color: #3858e9;
			background: #fff;
		}

		.twentigone-notice-logo {
			background: #f6f7fe;
			padding: 20px 12px;
		}

		.twentigone-notice-content {
			padding: 20px 0;
		}

		.twentigone-notice-content h3 {
			margin: 0;
		}

		.twentigone-notice-content p {
			font-size: 14px;
			padding: 0;
			margin: 0.5em 0 1em;
		}

		.twentigone-notice-buttons {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
		}

		.twentigone-notice-buttons .button-primary {
			background: #3858e9;
			border-color: #3858e9;
		}

		.twentigone-notice-buttons .button-primary:is(:hover,:focus) {
			background: #2145e6;
			border-color: #2145e6;
		}

		.twentigone-notice-buttons .button-primary:focus {
			box-shadow: 0 0 0 1px #fff, 0 0 0 3px #3858e9;
		}

		.twentigone-notice-buttons .button-primary svg {
			vertical-align: middle;
			fill: currentColor;
		}

	';

	// Attach the notice styles to the wp-admin handle which is always present.
	wp_add_inline_style( 'wp-admin', $css );

	$js = '
		jQuery( function( $ ) {
			$( document ).on( "click", ".twentig-link-notice .notice-dismiss", function( e ) {
				e.preventDefault();
				$.post( ajaxurl, {
					action: "twentig_dismiss_theme_link_notice",
					twentig_theme_link_notice_nonce: $( "#twentig_theme_link_notice_nonce" ).val()
				} );
			} );
		} );
	';

	wp_add_inline_script( 'jquery', $js );
}
add_action( 'admin_enqueue_scripts', 'twentig_enqueue_theme_notice_assets' );

/**
 * Displays an admin notice promoting the Twentig One theme.
 *
 * Shows a dismissible notice in the WordPress admin dashboard to inform
 * users about the Twentig One theme. The notice includes download/activate
 * buttons and can be dismissed per user.
 */
function twentig_theme_notice() {

	if ( ! twentig_should_show_theme_notice() ) {
		return;
	}

	$theme_slug  = 'twentig-one';
	$cta_url     = 'https://twentig.com/twentig-one/?utm_source=twentig-plugin&utm_medium=admin-notice&utm_campaign=theme';
	$button_text = __( 'Activate', 'twentig' );
	$button_url  = '';

	// Check if the Twentig One theme is installed.
	$theme = wp_get_theme( $theme_slug );
	if ( $theme->exists() ) {
		$activate_url = add_query_arg(
			array(
				'action'     => 'activate',
				'stylesheet' => $theme_slug,
			),
			self_admin_url( 'themes.php' )
		);

		$button_text     = __( 'Activate', 'twentig' );
		$button_url      = wp_nonce_url( $activate_url, 'switch-theme_' . $theme_slug );
	}

	?>
	<div class="notice notice-info is-dismissible twentig-link-notice">
		<div class="twentigone-notice-logo" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="24" height="24" xmlns="http://www.w3.org/2000/svg" focusable="false" aria-hidden="true">
				<path d="M24 6.647c0-.241 0-.483-.01-.724a10.5 10.5 0 0 0-.14-1.576 5.3 5.3 0 0 0-.493-1.5 5.04 5.04 0 0 0-2.2-2.2 5.3 5.3 0 0 0-1.5-.493 10.5 10.5 0 0 0-1.577-.14Q17.716 0 17.354 0H6.648c-.241 0-.483 0-.724.01a11 11 0 0 0-1.576.14 5.3 5.3 0 0 0-1.5.494 5.04 5.04 0 0 0-2.2 2.2 5.3 5.3 0 0 0-.493 1.5 10 10 0 0 0-.142 1.578q-.012.362-.013.725v10.705c0 .242 0 .483.011.725a10.4 10.4 0 0 0 .139 1.576 5.3 5.3 0 0 0 .493 1.5 5.04 5.04 0 0 0 2.2 2.2 5.3 5.3 0 0 0 1.5.494 10.4 10.4 0 0 0 1.576.138q.362.01.724.012H17.35c.241 0 .482 0 .723-.011a10.4 10.4 0 0 0 1.577-.138 5.3 5.3 0 0 0 1.5-.494 5.05 5.05 0 0 0 2.2-2.2 5.3 5.3 0 0 0 .493-1.5 10.6 10.6 0 0 0 .14-1.576c0-.242.009-.484.01-.725V6.647Z"/><path d="M9.278 10.734 11.52 17h1.424l1.876-5.186L16.7 17h1.44L21 9h-1.925l-1.666 5.186L15.548 9h-1.456l-1.844 5.186L10.582 9H6.316V6.857H4.521V9H3v1.734h1.521v3.272A2.838 2.838 0 0 0 7.5 17.115a2.3 2.3 0 0 0 .971-.2l-.1-1.6a2.4 2.4 0 0 1-.615.1 1.4 1.4 0 0 1-1.44-1.489v-3.192Z" style="fill:#fff"/>
			</svg>
		</div>
		<div class="twentigone-notice-content">
			<h3><?php esc_html_e( 'Introducing the Twentig One Theme', 'twentig' ); ?></h3>
			<p><?php esc_html_e( 'Unlock Twentig’s full power with our new block theme. The perfect foundation for your website and our starter sites.', 'twentig' ); ?></p>
			<div class="twentigone-notice-buttons">
				<?php if ( ! empty( $button_url ) ) : ?>
					<a class="button button-primary" href="<?php echo esc_url( $button_url ); ?>"><?php echo esc_html( $button_text ); ?></a>
				<?php else: ?>
					<a class="button button-primary" href="<?php echo esc_url( $cta_url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Get Twentig One', 'twentig' ); ?>
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" class="link-external-icon" aria-hidden="true" focusable="false"><path d="M19.5 4.5h-7V6h4.44l-5.97 5.97 1.06 1.06L18 7.06v4.44h1.5v-7Zm-13 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3H17v3a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h3V5.5h-3Z"></path></svg>
					</a>				
				<?php endif; ?>
			</div>
		</div>
		<?php wp_nonce_field( 'twentig_theme_link_notice', 'twentig_theme_link_notice_nonce', false ); ?>
	</div>
	<?php
}
add_action( 'admin_notices', 'twentig_theme_notice' );

/**
 * Handles AJAX request to dismiss the theme notice.
 *
 * Stores user meta to prevent the notice from showing again for the current user.
 */
function twentig_dismiss_theme_link_notice() {
	check_ajax_referer( 'twentig_theme_link_notice', 'twentig_theme_link_notice_nonce' );
	if ( ! current_user_can( 'switch_themes' ) && ! current_user_can( 'install_themes' ) ) {
		wp_send_json_error( null, 403 );
	}
	update_user_meta( get_current_user_id(), '_twentig_theme_notice', true );
	wp_send_json_success();
}
add_action( 'wp_ajax_twentig_dismiss_theme_link_notice', 'twentig_dismiss_theme_link_notice' );
