<?php
/**
 * Twentig dashboard class.
 */

defined( 'ABSPATH' ) || exit;

class TwentigDashboard {

	private static $instance;
	private $website_importer;
	private $settings_manager;
	private $customizer;
	
	private const THEME = 'twentig-one';

	/**
	 * Returns the list of theme mod keys managed by Twentig.
	 *
	 * Single source of truth shared by the importer (allowlist for setting mods)
	 * and the customizer updater (list of mods to clean up after saving).
	 *
	 * @return string[]
	 */
	public static function get_theme_mod_keys() {
		return array(
			'logo_width',
			'starter_presets',
			'menu',
			'social_instagram',
			'social_linkedin',
			'social_x',
			'social_facebook',
			'social_youtube',
			'social_mail',
			'header_elements',
			'header_layout',
			'header_width',
			'header_style',
			'header_position',
			'footer_layout',
			'footer_width',
			'footer_style',
			'home_layout',
			'portfolio_layout',
			'portfolio_width',
			'portfolio_single_layout',
			'portfolio_single_navigation',
			'blog_layout',
			'single_layout',
			'comments',
			'single_navigation',
			'color_palette',
			'typography',
		);
	}

	/**
	 * Gets class instance.
	 *
	 * @return object Instance.
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Initializes the dashboard.
	 */
	protected function __construct() {
		$this->load_dependencies();

		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'redirect_dashboard' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Loads the required dependencies for the Twentig dashboard.
	 */
	private function load_dependencies() {
		require_once TWENTIG_PATH . 'inc/dashboard/class-twentig-website-importer.php';
		require_once TWENTIG_PATH . 'inc/dashboard/class-twentig-settings.php';
		require_once TWENTIG_PATH . 'inc/dashboard/class-twentig-customizer.php';

		$this->website_importer = new TwentigWebsiteImporter();
		$this->settings_manager = new TwentigSettings();

		if ( get_template() === self::THEME ) {
			$this->customizer = new Twentig_Customizer();
		}
	}

	/**
	 * Adds a menu item for Twentig dashboard in the WordPress admin panel.
	 */
	public function add_menu() {
		add_menu_page(
			'Twentig',
			'Twentig',
			'edit_theme_options',
			'twentig',
			array( $this, 'render_menu_page' ),
			'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCI+PHBhdGggZmlsbD0iYmxhY2siIGQ9Ik0yMCA1LjUzOXEtLjAwMi0uMzAyLS4wMS0uNjAzYTguNzI1IDguNzI1IDAgMDAtLjExNS0xLjMxMyA0LjQzNiA0LjQzNiAwIDAwLS40MTItMS4yNUE0LjIgNC4yIDAgMDAxNy42MjcuNTM3IDQuNDUyIDQuNDUyIDAgMDAxNi4zOC4xMjUgOC43MjUgOC43MjUgMCAwMDE1LjA2NC4wMXEtLjMtLjAwOC0uNjAzLS4wMUg1LjU0cS0uMzAyLjAwMi0uNjA0LjAxYTguODI3IDguODI3IDAgMDAtMS4zMTMuMTE1IDQuNDQ0IDQuNDQ0IDAgMDAtMS4yNDguNDEyQTQuMiA0LjIgMCAwMC41MzggMi4zNzNhNC40MjIgNC40MjIgMCAwMC0uNDEyIDEuMjVBOC42MDQgOC42MDQgMCAwMC4wMSA0LjkzNXEtLjAwNy4zMDItLjAwOC42MDRDMCA1Ljc3OSAwIDYuMDE3IDAgNi4yNTZ2Ny40ODhjMCAuMjM5IDAgLjQ3Ny4wMDIuNzE2IDAgLjIwMS4wMDMuNDAzLjAwOC42MDRhOC43ODQgOC43ODQgMCAwMC4xMTYgMS4zMTMgNC40MzEgNC40MzEgMCAwMC40MTIgMS4yNSA0LjIgNC4yIDAgMDAxLjgzNiAxLjgzNSA0LjQyOSA0LjQyOSAwIDAwMS4yNDguNDEzIDguNzE1IDguNzE1IDAgMDAxLjMxNC4xMTVxLjMwMS4wMDguNjAzLjAxaDguMjA1bC43MTctLjAwMnEuMzAyIDAgLjYwMy0uMDA5YTguNzI0IDguNzI0IDAgMDAxLjMxNS0uMTE1IDQuNDI2IDQuNDI2IDAgMDAxLjI0OC0uNDEyIDQuMiA0LjIgMCAwMDEuODM2LTEuODM2IDQuNDE3IDQuNDE3IDAgMDAuNDEyLTEuMjQ5IDguNzM1IDguNzM1IDAgMDAuMTE1LTEuMzEzYy4wMDUtLjIwMS4wMDgtLjQwMy4wMS0uNjA0VjUuODQyek0xNS4xMTMgMTRoLTEuMkwxMi4zNSA5LjcyNyAxMC43ODcgMTRIOS42TDcuNzMxIDguODM3SDUuMjY0djIuNjI5YTEuMTYgMS4xNiAwIDAwMS4yIDEuMjI2IDIuMDM4IDIuMDM4IDAgMDAuNTEyLS4wOGwuMDggMS4zMmExLjkyNiAxLjkyNiAwIDAxLS44MDguMTYyIDIuMzUgMi4zNSAwIDAxLTIuNDgtMi41NlY4LjgzNkgyLjVWNy40MDhoMS4yNjdWNS42NDJoMS40OTd2MS43NjZoMy41NTRsMS4zODkgNC4yNzMgMS41MzctNC4yNzNoMS4yMTNsMS41NSA0LjI3MyAxLjM4OS00LjI3M0gxNy41eiIvPjwvc3ZnPg=='
		);
	}

	/**
	 * Renders the Twentig dashboard page.
	 */
	public function render_menu_page() {
		if ( get_transient( 'twentig_flush_rewrite_rules' ) ) {
			delete_transient( 'twentig_flush_rewrite_rules' );
			flush_rewrite_rules( false );
		}
		?>
		<div id="twentig-dashboard"></div>
		<?php
	}

	/**
	 * Redirects to the Twentig dashboard on single plugin activation.
	 */
	public function redirect_dashboard() {
		if ( get_transient( '_twentig_activation_redirect' ) && apply_filters( 'twentig_enable_activation_redirect', true ) ) {
			$do_redirect = true;
			delete_transient( '_twentig_activation_redirect' );

			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$do_redirect = false;
			}

			if ( $do_redirect ) {
				wp_safe_redirect( esc_url( admin_url( 'admin.php?page=twentig' ) ) );
				exit;
			}
		}
	}

	/**
	 * Registers the necessary REST API routes.
	 */
	public function register_routes() {
		$this->website_importer->register_routes();
		$this->settings_manager->register_routes();
	}

	/**
	 * Enqueues admin scripts (JS and CSS).
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {

		if ( 'toplevel_page_twentig' === $hook_suffix ) {
			$asset_file = include TWENTIG_PATH . 'dist/index.asset.php';

			$styles_to_enqueue = array( 'wp-components', 'wp-block-editor' );
			foreach ( $styles_to_enqueue as $style_handle ) {
				if ( wp_style_is( $style_handle, 'registered' ) ) {
					wp_enqueue_style( $style_handle );
				}
			}

			wp_enqueue_style(
				'twentig-editor',
				TWENTIG_ASSETS_URI . '/index.css',
				array(),
				$asset_file['version']
			);

			wp_enqueue_script(
				'twentig-homescreen',
				TWENTIG_ASSETS_URI . '/index.js',
				$asset_file['dependencies'],
				$asset_file['version'],
				array( 'in_footer' => false )
			);

			$config = array(
				'theme'        => get_template(),
				'isBlockTheme' => wp_is_block_theme(),
				'cssClasses'   => array(),
				'spacingSizes' => array(),
			);

			wp_add_inline_script(
				'twentig-homescreen',
				'var twentigEditorConfig = ' . wp_json_encode( $config ) . ';',
				'before'
			);

			wp_set_script_translations( 'twentig-homescreen', 'twentig' );

			$plugin            = 'twentig/twentig.php';
			$plugin_update_url = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . $plugin ), 'upgrade-plugin_' . $plugin );

			$home_template = get_block_template( get_stylesheet() . '//home', 'wp_template' );
			$blog_template = $home_template ? 'home' : 'index';

			$starter_sites = array_map(
				function ( $site ) {
					unset( $site['file'] );
					return $site;
				},
				$this->website_importer->get_starter_sites()
			);

			wp_add_inline_script(
				'twentig-homescreen',
				'var twentigDashboardConfig = ' . wp_json_encode(
					array(
						'siteUrl'            => esc_url( get_home_url() ),
						'editorUrl'          => esc_url( admin_url( 'site-editor.php' ) ),
						'customizerUrl'      => esc_url( admin_url( 'customize.php?startersite=1' ) ),
						'assetsUrl'          => TWENTIG_ASSETS_URI . '/images/',
						'theme'              => get_template(),
						'isBlockTheme'       => wp_is_block_theme(),
						'isTwentigTheme'     => current_theme_supports( 'twentig-theme' ),
						'supportsSpacing'    => current_theme_supports( 'tw-spacing' ),
						'blogTemplate'       => $blog_template,
						'wpVersion'          => get_bloginfo( 'version' ),
						'updateWordPressUrl' => current_user_can( 'update_core' ) ? network_admin_url( 'update-core.php' ) : '',
						'twentigVersion'     => TWENTIG_VERSION,
						'updateTwentigUrl'   => current_user_can( 'update_plugins' ) ? esc_url( $plugin_update_url ) : '',
						'deletePrevious'     => $this->website_importer->has_imported_posts(),
						'isFreshSite'        => get_option( 'fresh_site' ) ? true : false,
						'twentigOptions'     => twentig_get_options(),
						'starterSites'       => $starter_sites,
						'isImporterActive'   => is_plugin_active( 'wordpress-importer/wordpress-importer.php' ),
					)
				) . ';',
				'before'
			);

		}
	}

}
TwentigDashboard::get_instance();
