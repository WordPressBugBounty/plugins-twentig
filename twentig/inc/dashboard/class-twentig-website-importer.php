<?php
/**
 * Handles WordPress XML imports for starter sites.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Twentig Website Importer class.
 */
class TwentigWebsiteImporter {

	private $importer;
	private $processed_ids  = array();
	private $nav_ids        = array();
	private $site_options   = array();
	private $has_portfolio  = false;
	private $import_started = false;

	/**
	 * Registers the necessary REST API routes.
	 */
	public function register_routes() {

		register_rest_route(
			'twentig/v1',
			'/import-starter-site',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'import_starter_site_callback' ),
				'permission_callback' => function () {
					return current_user_can( 'import' ) && current_user_can( 'delete_posts' );
				},
			)
		);

		register_rest_route(
			'twentig/v1',
			'/upload-starter-file',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'upload_starter_file_callback' ),
				'permission_callback' => function () {
					return current_user_can( 'import' ) && current_user_can( 'delete_posts' );
				},
			)
		);

		register_rest_route(
			'twentig/v1',
			'/install-wordpress-importer',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'install_wordpress_importer_callback' ),
				'permission_callback' => function () {
					return current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' );
				},
			)
		);
	}

	/**
	 * Imports the selected starter site.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function import_starter_site_callback( $request ) {
		require_once ABSPATH . 'wp-admin/includes/post.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/taxonomy.php';

		$starters                = $this->get_starter_sites();
		$starter_id              = absint( $request->get_param( 'starter_index' ) );
		$delete_previous_content = wp_validate_boolean( $request->get_param( 'delete_previous_content' ) );

		if ( ! isset( $starters[ $starter_id ] ) ) {
			return new WP_Error( 'invalid_starter_index', 'Invalid starter index specified.', array( 'status' => 400 ) );
		}

		if ( $delete_previous_content ) {
			$this->delete_previous_content();
		}

		$starter             = $starters[ $starter_id ];
		$this->site_options  = $starter['options'] ?? array();
		$this->has_portfolio = wp_validate_boolean( $this->site_options['portfolio'] ?? false );

		$file = $starter['file'];

		if ( ! file_exists( $file ) ) {
			return new WP_Error( 'starter_file_missing', esc_html__( 'Starter file not found.', 'twentig' ), array( 'status' => 500 ) );
		}

		$result = $this->import_and_update_site( $file );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( isset( $starter['type'] ) ) {
			update_option( 'twentig-customize-starter', $starter['type'] );
		}

		return new WP_REST_Response(
			array(
				'twentig_options' => twentig_get_options(),
			),
			200
		);
	}

	/**
	 * Uploads and imports a starter site XML file.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function upload_starter_file_callback( $request ) {

		$files = $request->get_file_params();

		if ( ! isset( $files['file'] ) ) {
			return new WP_Error( 'upload_failed', esc_html__( 'Failed to upload the XML file.', 'twentig' ), array( 'status' => 400 ) );
		}

		// Validate file extension.
		$file_extension = strtolower( pathinfo( $files['file']['name'], PATHINFO_EXTENSION ) );
		if ( 'xml' !== $file_extension ) {
			return new WP_Error( 'invalid_file_type', esc_html__( 'Only XML files are allowed.', 'twentig' ), array( 'status' => 400 ) );
		}

		// Validate file size to prevent memory exhaustion during import processing.
		$max_size = 10 * 1024 * 1024; // 10MB
		if ( $files['file']['size'] > $max_size ) {
			return new WP_Error( 'file_too_large', esc_html__( 'File size exceeds the maximum allowed size of 10MB.', 'twentig' ), array( 'status' => 400 ) );
		}

		// Register allowed XML mime type.
		add_filter( 'upload_mimes', array( $this, 'allow_xml_mime_type' ) );

		require_once ABSPATH . 'wp-admin/includes/post.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/taxonomy.php';

		$uploaded_file = wp_handle_upload( $files['file'], array( 'test_form' => false ) );

		remove_filter( 'upload_mimes', array( $this, 'allow_xml_mime_type' ) );

		if ( isset( $uploaded_file['error'] ) ) {
			return new WP_Error(
				'upload_failed',
				sprintf(
					/* translators: %s: Error message */
					esc_html__( 'Error uploading file: %s', 'twentig' ),
					esc_html( $uploaded_file['error'] )
				),
				array( 'status' => 400 )
			);
		}

		// Validate JSON options if provided.
		$options_param = $request->get_param( 'options' );
		if ( ! empty( $options_param ) ) {
			$this->site_options = json_decode( $options_param, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				wp_delete_file( $uploaded_file['file'] );
				return new WP_Error( 'invalid_json', esc_html__( 'Invalid JSON data in options parameter.', 'twentig' ), array( 'status' => 400 ) );
			}
		} else {
			$this->site_options = array();
		}

		$this->has_portfolio     = wp_validate_boolean( $this->site_options['portfolio'] ?? false );
		$delete_previous_content = wp_validate_boolean( $request->get_param( 'delete_previous_content' ) );

		if ( $delete_previous_content ) {
			$this->delete_previous_content();
		}

		try {
			$result = $this->import_and_update_site( $uploaded_file['file'] );
		} finally {
			if ( file_exists( $uploaded_file['file'] ) ) {
				wp_delete_file( $uploaded_file['file'] );
			}
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		update_option( 'twentig-customize-starter', 'custom' );

		return new WP_REST_Response(
			array(
				'message'         => 'File uploaded successfully',
				'twentig_options' => twentig_get_options(),
			),
			200
		);
	}

	/**
	 * Installs and activates the WordPress Importer plugin.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function install_wordpress_importer_callback() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';

		$plugin_slug = 'wordpress-importer';
		$plugin_file = 'wordpress-importer/wordpress-importer.php';

		if ( is_plugin_active( $plugin_file ) ) {
			return new WP_REST_Response( array( 'success' => true ), 200 );
		}

		if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
			$activate = activate_plugin( $plugin_file );

			if ( is_wp_error( $activate ) ) {
				return new WP_Error(
					'plugin_activation_failed',
					sprintf(
						/* translators: %s: Error message */
						esc_html__( 'Plugin activation failed: %s', 'twentig' ),
						$activate->get_error_message()
					),
					array( 'status' => 500 )
				);
			}

			return new WP_REST_Response( array( 'success' => true ), 200 );
		}

		$api = plugins_api( 'plugin_information', array( 'slug' => $plugin_slug ) );

		if ( is_wp_error( $api ) ) {
			return new WP_Error( 'api_error', $api->get_error_message(), array( 'status' => 500 ) );
		}

		ob_start();

		$skin     = new \Automatic_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

		ob_end_clean();

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'install_failed', $result->get_error_message(), array( 'status' => 500 ) );
		} elseif ( ! $result ) {
			return new WP_Error( 'install_failed', esc_html__( 'Installation failed.', 'twentig' ), array( 'status' => 500 ) );
		}

		$activate = activate_plugin( $plugin_file );

		if ( is_wp_error( $activate ) ) {
			return new WP_Error(
				'plugin_activation_failed',
				sprintf(
					/* translators: %s: Error message */
					esc_html__( 'Plugin activation failed: %s', 'twentig' ),
					$activate->get_error_message()
				),
				array( 'status' => 500 )
			);
		}

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	/**
	 * Imports the website template and updates the site options.
	 *
	 * @param string $file Path to the XML import file.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function import_and_update_site( $file ) {

		$this->import_started = false;

		add_action( 'import_start', array( $this, 'mark_import_started' ) );
		add_action( 'import_start', array( $this, 'delete_custom_files' ) );
		add_action( 'wp_import_insert_post', array( $this, 'match_post_id' ), 10, 4 );
		add_filter( 'wp_import_existing_post', array( $this, 'import_existing_post' ), 10, 2 );
		add_filter( 'wp_import_post_data_processed', array( $this, 'import_post_data_processed' ), 10, 2 );
		add_filter( 'wp_import_term_meta', array( $this, 'add_starter_term_meta' ), 10, 3 );

		// Load WordPress Importer plugin if not already loaded.
		if ( ! class_exists( 'WP_Import' ) ) {
			$this->load_wordpress_importer();
		}

		// Check if WordPress Importer is available.
		if ( ! class_exists( 'WP_Import' ) ) {
			$this->remove_import_hooks();
			return new WP_Error( 'wp_import_missing', __( 'WordPress Importer plugin is required to import starter sites.', 'twentig' ) );
		}

		// Use WordPress Importer plugin class.
		$this->importer                    = new WP_Import();
		$this->importer->fetch_attachments = true;

		if ( $this->has_portfolio ) {
			$options              = twentig_get_options();
			$options['portfolio'] = true;
			update_option( 'twentig-options', $options );
		}

		ob_start();
		$this->importer->import(
			$file,
			array(
				'rewrite_urls' => current_theme_supports( 'twentig-theme' ),
			)
		);
		ob_end_clean();
		$this->remove_import_hooks();

		if ( ! $this->import_started ) {
			return new WP_Error(
				'import_parse_failed',
				__( 'Failed to parse the import file. The XML may be malformed or empty.', 'twentig' )
			);
		}

		if ( empty( $this->processed_ids ) ) {
			return new WP_Error(
				'import_no_content',
				__( 'Import completed but no content was imported.', 'twentig' )
			);
		}

		$this->update_site_options();
		$this->update_nav_and_template_parts();

		if ( $this->has_portfolio ) {
			flush_rewrite_rules( false );
		}

		$transient_name = 'global_styles_' . get_stylesheet();
		delete_transient( $transient_name );

		return true;
	}

	/**
	 * Sets the import_started flag. Hooked to 'import_start'.
	 */
	public function mark_import_started() {
		$this->import_started = true;
	}

	/**
	 * Removes the hooks registered by import_and_update_site().
	 */
	private function remove_import_hooks() {
		remove_action( 'import_start', array( $this, 'mark_import_started' ) );
		remove_action( 'import_start', array( $this, 'delete_custom_files' ) );
		remove_action( 'wp_import_insert_post', array( $this, 'match_post_id' ), 10 );
		remove_filter( 'wp_import_existing_post', array( $this, 'import_existing_post' ), 10 );
		remove_filter( 'wp_import_post_data_processed', array( $this, 'import_post_data_processed' ), 10 );
		remove_filter( 'wp_import_term_meta', array( $this, 'add_starter_term_meta' ), 10 );
	}

	/**
	 * Loads the WordPress Importer plugin with all its dependencies.
	 *
	 * The WordPress Importer plugin requires several files to be loaded in the correct order.
	 * This method directly loads the necessary components to make the WP_Import class available.
	 *
	 * @return void
	 */
	private function load_wordpress_importer() {
		$importer_dir = WP_PLUGIN_DIR . '/wordpress-importer/';

		// Check if the WordPress Importer plugin directory exists.
		if ( ! is_dir( $importer_dir ) ) {
			return;
		}

		// Load WordPress import administration API if not already loaded.
		$import_admin_file = ABSPATH . 'wp-admin/includes/import.php';
		if ( file_exists( $import_admin_file ) && ! function_exists( 'wp_import_handle_upload' ) ) {
			require_once $import_admin_file;
		}

		// Load base WP_Importer class if not already loaded.
		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) ) {
				require_once $class_wp_importer;
			}
		}

		// Load WordPress Importer plugin dependencies.
		if ( ! class_exists( 'WP_Import' ) ) {

			// Load XML Processor and php-toolkit.
			if ( ! class_exists( 'WordPress\XML\XMLProcessor' ) ) {
				$xml_toolkit = $importer_dir . 'php-toolkit/load.php';
				if ( file_exists( $xml_toolkit ) ) {
					require_once $xml_toolkit;
				}
			}

			// Load WXR Parser classes.
			$parser_files = array(
				'parsers/class-wxr-parser.php',
				'parsers/class-wxr-parser-simplexml.php',
				'parsers/class-wxr-parser-xml.php',
				'parsers/class-wxr-parser-xml-processor.php',
			);

			foreach ( $parser_files as $parser_file ) {
				$file_path = $importer_dir . $parser_file;
				if ( file_exists( $file_path ) ) {
					require_once $file_path;
				}
			}

			// Finally, load the main WP_Import class
			$wp_import_class = $importer_dir . 'class-wp-import.php';
			if ( file_exists( $wp_import_class ) ) {
				require_once $wp_import_class;
			}
		}
	}

	/**
	 * Checks if there are already imported posts.
	 *
	 * @return bool True if imported posts exist, false otherwise.
	 */
	public function has_imported_posts() {
		global $wpdb;
		$post_exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s LIMIT 1",
				'_twentig_website_imported_post'
			)
		);
		return ! empty( $post_exists );
	}

	/**
	 * Maps pre-import ID to local ID.
	 *
	 * @param int    $post_id          New post ID.
	 * @param int    $original_post_id Original post ID from XML.
	 * @param array  $postdata         Post data.
	 * @param array  $post             Raw post array.
	 */
	public function match_post_id( $post_id, $original_post_id, $postdata, $post ) {
		$this->processed_ids[ (int) $original_post_id ] = (int) $post_id;
		if ( 'wp_navigation' === $post['post_type'] ) {
			$this->nav_ids[ (int) $original_post_id ] = (int) $post_id;
		}
	}

	/**
	 * Forces the import of existing posts.
	 *
	 * @param int   $post_exists Post ID if post exists, 0 otherwise.
	 * @param array $post        The post array to be inserted.
	 * @return int Modified post exists value.
	 */
	public function import_existing_post( $post_exists, $post ) {
		if ( $post_exists && in_array( $post['post_type'], array( 'page', 'post', 'portfolio', 'wp_navigation', 'wp_global_styles' ), true ) ) {
			$post_exists = 0;
		}
		return $post_exists;
	}

	/**
	 * Returns the supported page types for mapping.
	 *
	 * @return array supported page types.
	 */
	public function get_page_types() {

		$page_types = array(
			'home'      => array(
				'title' => __( 'Home', 'twentig' ),
			),
			'blog'      => array(
				'title' => __( 'Blog', 'twentig' ),
			),
			'portfolio' => array(
				'title' => __( 'Portfolio', 'twentig' ),
			),
			'work' => array(
				'title' => __( 'Work', 'twentig' ),
			),
			'about'     => array(
				'title' => __( 'About', 'twentig' ),
			),
			'contact'   => array(
				'title' => __( 'Contact', 'twentig' ),
			),
		);

		return apply_filters( 'twentig_page_types', $page_types );
	}

	/**
	 * Modifies the post data before it is inserted into the database.
	 *
	 * @param array $postdata Processed post data.
	 * @param array $post     Raw post data.
	 * @return array Modified post data.
	 */
	public function import_post_data_processed( $postdata, $post ) {
		if ( in_array( $postdata['post_type'], array( 'page', 'wp_template', 'wp_template_part', 'wp_navigation' ), true ) ) {
			$postdata['post_content'] = str_replace( 'SITE_URL', get_site_url(), $postdata['post_content'] );
			$postdata['post_content'] = str_replace( 'THEME_URL', get_template_directory_uri(), $postdata['post_content'] );

			if ( 'page' === $postdata['post_type'] ) {
				$page_types = $this->get_page_types();
				if ( isset( $page_types[ $postdata['post_name'] ] ) ) {
					$postdata['post_title'] = $page_types[ $postdata['post_name'] ]['title'];
					$postdata['post_name']  = sanitize_title( $page_types[ $postdata['post_name'] ]['title'] );
				}
			} elseif ( 'wp_template' === $postdata['post_type'] ) {
				static $all_templates = null;
				if ( null === $all_templates ) {
					$all_templates = get_default_block_template_types() + wp_get_theme_data_custom_templates();
				}

				if ( isset( $all_templates[ $postdata['post_name'] ] ) ) {
					$postdata['post_title'] = $all_templates[ $postdata['post_name'] ]['title'];
					if ( isset( $all_templates[ $postdata['post_name'] ]['description'] ) ) {
						$postdata['post_excerpt'] = $all_templates[ $postdata['post_name'] ]['description'];
					}
				}
			} elseif ( 'wp_template_part' === $postdata['post_type'] ) {
				static $theme_parts = null;
				if ( null === $theme_parts ) {
					$theme_parts = wp_get_theme_data_template_parts();
				}
				if ( isset( $theme_parts[ $postdata['post_name'] ] ) ) {
					$postdata['post_title'] = $theme_parts[ $postdata['post_name'] ]['title'];
				}
			}
		} elseif ( 'wp_global_styles' === $postdata['post_type'] ) {
			$user_cpt = WP_Theme_JSON_Resolver::get_user_data_from_wp_global_styles( wp_get_theme(), true );
			if ( isset( $user_cpt['ID'] ) ) {
				$postdata['ID'] = $user_cpt['ID'];
			}
		}

		// Add meta to identify post as an import
		if ( in_array( $postdata['post_type'], array( 'page', 'post', 'portfolio', 'attachment', 'wp_navigation' ), true ) ) {
			if ( ! isset( $postdata['meta_input'] ) ) {
				$postdata['meta_input'] = array();
			}
			$postdata['meta_input']['_twentig_website_imported_post'] = true;
		}
		return $postdata;
	}

	/**
	 * Adds meta to identify term as an import.
	 *
	 * @param array  $termmeta Term meta.
	 * @param int    $term_id  Term ID.
	 * @param object $term     Term object.
	 * @return array Modified term meta.
	 */
	public function add_starter_term_meta( $termmeta, $term_id, $term ) {

		$term = get_term( $term_id );
		if ( $term instanceof WP_Term && ! str_starts_with( $term->taxonomy, 'wp_' ) ) {
			$termmeta[] = array(
				'key'   => '_twentig_website_imported_term',
				'value' => true,
			);
		}
		return $termmeta;
	}

	/**
	 * Deletes previously imported posts and terms.
	 */
	public function delete_previous_content() {

		global $wpdb;

		$post_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'_twentig_website_imported_post'
			)
		);

		$term_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key = %s",
				'_twentig_website_imported_term'
			)
		);

		if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				wp_delete_post( $post_id, true );
			}
		}

		if ( ! empty( $term_ids ) && is_array( $term_ids ) ) {
			foreach ( $term_ids as $term_id ) {
				$term = get_term( $term_id );
				if ( ! is_wp_error( $term ) && $term instanceof WP_Term ) {
					wp_delete_term( $term_id, $term->taxonomy );
				}
			}
		}
	}

	/**
	 * Deletes default content, custom styles, templates, and template parts.
	 */
	public function delete_custom_files() {

		if ( get_option( 'fresh_site' ) ) {
			$first_post_slug = _x( 'hello-world', 'Default post slug', 'default' );
			$first_post      = get_page_by_path( $first_post_slug, OBJECT, 'post' );
			if ( $first_post ) {
				wp_delete_post( $first_post->ID, true );
			}
			$first_page = get_page_by_path( 'sample-page', OBJECT, 'page' );
			if ( $first_page ) {
				wp_delete_post( $first_page->ID, true );
			}
		}

		$wp_query_args = array(
			'post_type'      => array( 'wp_template', 'wp_template_part' ),
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'post_status'    => 'publish',
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => wp_get_theme()->get_stylesheet(),
				),
			),
		);

		$template_query  = new WP_Query( $wp_query_args );
		$posts_to_delete = $template_query->posts;

		foreach ( $posts_to_delete as $post ) {
			wp_delete_post( $post->ID, true );
		}

		$this->cleanup_theme_mods();
	}

	/**
	 * Updates the site options.
	 */
	public function update_site_options() {

		if ( isset( $this->site_options['spacing'] ) ) {
			$options                       = twentig_get_options();
			$options['predefined_spacing'] = true;
			update_option( 'twentig-options', $options );
		}

		if ( isset( $this->site_options['posts_per_page'] ) ) {
			update_option( 'posts_per_page', (int) $this->site_options['posts_per_page'] );
		}

		if ( isset( $this->site_options['front_page'] ) && 'posts' === $this->site_options['front_page'] ) {
			update_option( 'show_on_front', 'posts' );
		} else {

			$page_types       = $this->get_page_types();
			$front_page       = sanitize_title( $this->site_options['front_page'] ?? 'Home' );
			$front_page_title = isset( $page_types[ $front_page ] ) ? $page_types[ $front_page ]['title'] : $front_page;
			$blog_page        = sanitize_title( $this->site_options['blog_page'] ?? 'Blog' );
			$blog_page_title  = isset( $page_types[ $blog_page ] ) ? $page_types[ $blog_page ]['title'] : $blog_page;

			foreach ( $this->processed_ids as $old => $new ) {
				$page_title = get_the_title( $new );
				if ( $front_page_title === $page_title ) {
					if ( 'page' === get_post_type( $new ) ) {
						update_option( 'show_on_front', 'page' );
						update_option( 'page_on_front', $new );
					}
				} elseif ( $blog_page_title === $page_title ) {
					update_option( 'page_for_posts', $new );
				}
			}
		}

		if ( isset( $this->site_options['theme_mods'] ) && is_array( $this->site_options['theme_mods'] ) ) {
			$allowed_theme_mod_keys = TwentigDashboard::get_theme_mod_keys();

			foreach ( $this->site_options['theme_mods'] as $mod_key => $mod_value ) {
				if ( ! in_array( $mod_key, $allowed_theme_mod_keys, true ) ) {
					continue;
				}

				if ( 'header_elements' === $mod_key ) {
					$val = array_values( array_map( 'strval', (array) $mod_value ) );
					set_theme_mod( $mod_key, $val );
				} elseif ( 'menu' === $mod_key ) {
					$val = array_values( array_filter( (array) $mod_value, 'is_scalar' ) );
					$val = array_map(
						function( $item ) {
							return 'home' === $item ? 'home' : absint( $item );
						},
						$val
					);
					set_theme_mod( $mod_key, $val );
				} elseif ( is_scalar( $mod_value ) ) {
					if ( in_array( $mod_key, array( 'social_instagram', 'social_linkedin', 'social_x', 'social_facebook', 'social_youtube' ), true ) ) {
						$mod_value = esc_url_raw( (string) $mod_value );
					} elseif ( 'social_mail' === $mod_key ) {
						$mod_value = sanitize_email( (string) $mod_value );
					} elseif ( 'logo_width' === $mod_key ) {
						$mod_value = absint( $mod_value );
					} else {
						$mod_value = sanitize_text_field( (string) $mod_value );
					}
					set_theme_mod( $mod_key, $mod_value );
				}
			}
		}

	}

	/**
	 * Updates navigations and template parts.
	 */
	public function update_nav_and_template_parts() {

		if ( empty( $this->nav_ids ) ) {
			return;
		}

		$navigation_args = array(
			'post_type'     => 'wp_navigation',
			'no_found_rows' => true,
			'post_status'   => 'publish',
			'post__in'      => array_values( $this->nav_ids ),
		);

		$navigation_posts = new WP_Query( $navigation_args );

		$page_types = $this->get_page_types();

		foreach ( $navigation_posts->posts as $navigation_post ) {
			$navigation_blocks = block_core_navigation_filter_out_empty_blocks( parse_blocks( $navigation_post->post_content ) );

			// Flatten the block tree so navigation links at any submenu depth are
			// updated, not just the top level and one level of submenus.
			$all_nav_items = $this->flatten_blocks( $navigation_blocks );
			foreach ( $all_nav_items as &$block ) {
				if ( ! in_array( $block['blockName'], array( 'core/navigation-link', 'core/navigation-submenu', 'core/home-link' ), true ) ) {
					continue;
				}

				if ( isset( $block['attrs']['id'] ) ) {
					$old_id              = $block['attrs']['id'];
					$page_id             = $this->processed_ids[ $old_id ] ?? $old_id;
					$block['attrs']['id']  = $page_id;
					$block['attrs']['url'] = get_permalink( $page_id );

					$nav_label = sanitize_title( $block['attrs']['label'] ?? '' );
					if ( isset( $page_types[ $nav_label ] ) ) {
						$block['attrs']['label'] = $page_types[ $nav_label ]['title'];
					}
				} elseif ( ( $block['attrs']['kind'] ?? null ) === 'taxonomy' ) {
					$type = $block['attrs']['type'] ?? 'category';
					$slug = basename( rtrim( $block['attrs']['url'], '/' ) );
					if ( 'post_format' === $type ) {
						$link = get_post_format_link( $slug );
						if ( $link ) {
							$block['attrs']['url'] = $link;
						}
					} else {
						$link = get_term_link( $slug, $type );
						if ( ! is_wp_error( $link ) ) {
							$block['attrs']['url'] = $link;
						}
					}
				} elseif ( 'core/home-link' === $block['blockName'] ) {
					$nav_label = sanitize_title( $block['attrs']['label'] ?? '' );
					if ( isset( $page_types[ $nav_label ] ) ) {
						$block['attrs']['label'] = $page_types[ $nav_label ]['title'];
					}
				}
			}
			unset( $block );

			wp_update_post(
				array(
					'ID'           => $navigation_post->ID,
					'post_content' => serialize_blocks( $navigation_blocks ),
				)
			);
		}

		// Updates the navigation ref inside template parts.
		$template_args = array(
			'post_status'    => array( 'publish' ),
			'post_type'      => 'wp_template_part',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => wp_get_theme()->get_stylesheet(),
				),
			),
		);

		$template_part_query = new WP_Query( $template_args );
		foreach ( $template_part_query->posts as $post ) {
			$this->inject_nav_attribute_in_block_template_content( $post );
		}

	}

	/**
	 * Parses content, injects the correct navigation id,
	 * and updates the post.
	 *
	 * @param WP_Post $post Template post.
	 * @see inject_theme_attribute_in_block_template_content
	 */
	public function inject_nav_attribute_in_block_template_content( $post ) {
		$has_updated_content = false;
		$new_content         = '';
		$template_blocks     = parse_blocks( $post->post_content );

		$blocks = $this->flatten_blocks( $template_blocks );
		foreach ( $blocks as &$block ) {
			if ( 'core/navigation' === $block['blockName'] && isset( $block['attrs']['ref'] ) ) {
				$nav_id = $block['attrs']['ref'];
				if ( isset( $this->nav_ids[ $nav_id ] ) && $nav_id !== $this->nav_ids[ $nav_id ] ) {
					$block['attrs']['ref'] = $this->nav_ids[ $nav_id ];
					$has_updated_content   = true;
				}
			}
		}

		if ( $has_updated_content ) {
			foreach ( $template_blocks as &$block ) {
				$new_content .= serialize_block( $block );
			}

			wp_update_post(
				array(
					'ID'           => $post->ID,
					'post_content' => $new_content,
				)
			);
		}
	}

	/**
	 * Returns an array containing the references of
	 * the passed blocks and their inner blocks.
	 *
	 * @param array $blocks array of blocks.
	 *
	 * @return array block references to the passed blocks and their inner blocks.
	 */
	private function flatten_blocks( &$blocks ) {
		$all_blocks = array();
		$queue      = array();
		foreach ( $blocks as &$block ) {
			$queue[] = &$block;
		}

		while ( count( $queue ) > 0 ) {
			$block = &$queue[0];
			array_shift( $queue );
			$all_blocks[] = &$block;

			if ( ! empty( $block['innerBlocks'] ) ) {
				foreach ( $block['innerBlocks'] as &$inner_block ) {
					$queue[] = &$inner_block;
				}
			}
		}
		return $all_blocks;
	}

	/**
	 * Adds XML mime type to allowed upload types.
	 *
	 * @param array $defaults Default allowed mime types.
	 * @return array Modified mime types.
	 */
	public function allow_xml_mime_type( $defaults ) {
		$defaults['xml'] = 'text/xml';
		return $defaults;
	}

	/**
	 * Returns the starter sites defined for the theme.
	 *
	 * @return array List of starter sites.
	 */
	public function get_starter_sites() {

		$template_path = TWENTIG_PATH . 'dist/templates/';
		$template_uri  = TWENTIG_URI . 'dist/templates/';

		if ( current_theme_supports( 'twentig-theme' ) ) {

			return array(
				array(
					'title'      => __( 'Business', 'twentig' ),
					'screenshot' => esc_url( $template_uri . 'business-starter.webp' ),
					'file'       => $template_path . 'business-starter.xml',
					'url'        => 'https://demo.twentig.com/business-starter/',
					'options'    => array(
						'front_page'     => 'Home',
						'blog_page'      => 'Blog',
						'posts_per_page' => 12,
					),
					'type'       => 'business',
				),
				array(
					'title'      => __( 'Portfolio', 'twentig' ),
					'screenshot' => esc_url( $template_uri . 'portfolio-starter.webp' ),
					'file'       => $template_path . 'portfolio-starter.xml',
					'url'        => 'https://demo.twentig.com/portfolio-starter/',
					'options'    => array(
						'front_page'     => 'Home',
						'blog_page'      => 'Blog',
						'posts_per_page' => 12,
						'portfolio'      => true,
					),
					'type'       => 'portfolio',
				),
				array(
					'title'      => __( 'Blog', 'twentig' ),
					'screenshot' => esc_url( $template_uri . 'blog-starter.webp' ),
					'file'       => $template_path . 'blog-starter.xml',
					'url'        => 'https://demo.twentig.com/blog-starter/',
					'options'    => array(
						'front_page'     => 'posts',
						'posts_per_page' => 12,
					),
					'type'       => 'blog',
				),
				array(
					'title'      => __( 'Personal', 'twentig' ),
					'screenshot' => esc_url( $template_uri . 'personal-starter.webp' ),
					'file'       => $template_path . 'personal-starter.xml',
					'url'        => 'https://demo.twentig.com/personal-starter/',
					'options'    => array(
						'front_page'     => 'Home',
						'blog_page'      => 'Blog',
						'posts_per_page' => 12,
					),
					'type'       => 'personal',
				),
			);
		}

		$theme_support = get_theme_support( 'twentig-starter-websites' );
		if ( is_array( $theme_support ) && ! empty( $theme_support[0] ) ) {
			return array_values( $theme_support[0] );
		}
		return array();
	}

	/**
	 * Removes all Twentig theme mods.
	 */
	private function cleanup_theme_mods() {
		foreach ( TwentigDashboard::get_theme_mod_keys() as $mod_key ) {
			remove_theme_mod( $mod_key );
		}
	}

}