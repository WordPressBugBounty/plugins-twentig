<?php
/**
 * Portfolio Post Type
 *
 * @package twentig
 */
 
defined( 'ABSPATH' ) || exit;

/**
 * Twentig Portfolio class.
 *
 * Registers a portfolio post type, its taxonomies, and customizes the admin.
 */
class Twentig_Portfolio {

	/**
	 * Initializes class.
	 *
	 * @return Twentig_Portfolio The single instance of the class.
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new Twentig_Portfolio();
		}

		return $instance;
	}

	/**
	 * Hooks into WordPress if Portfolio is enabled in the settings.
	 */
	public function __construct() {

		add_action( 'import_start', array( $this, 'register_post_type_and_taxonomies' ) );

		if ( ! twentig_is_option_enabled( 'portfolio' ) ) {
			return;
		}

		$this->register_post_type_and_taxonomies();

		add_filter( 'manage_portfolio_posts_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_portfolio_posts_custom_column', array( $this, 'custom_columns' ) );
	}

	/**
	 * Registers post type and taxonomies.
	 */
	function register_post_type_and_taxonomies() {
		if ( post_type_exists( 'portfolio' ) ) {
			return;
		}

		$options       = get_option( 'twentig-options' );
		$project_slug  = ! empty( $options['portfolio_slug'] ) ? $options['portfolio_slug'] : 'portfolio';
		$category_slug = ! empty( $options['portfolio_category_slug'] ) ? $options['portfolio_category_slug'] : 'portfolio-category';
		$tag_slug      = ! empty( $options['portfolio_tag_slug'] ) ? $options['portfolio_tag_slug'] : 'portfolio-tag';

		$supports = array(
			'title',
			'editor',
			'thumbnail',
			'excerpt',
			'comments',
			'revisions',
			'author',
			'custom-fields',
		);

		if ( current_theme_supports( 'post-formats' ) ) {
			$supports[] = 'post-formats';
		}

		register_post_type(
			'portfolio',
			array(
				'labels'          => array(
					'name'                  => esc_html__( 'Portfolio', 'twentig' ),
					'singular_name'         => esc_html__( 'Project', 'twentig' ),
					'add_new'               => esc_html__( 'Add New Project', 'twentig' ),
					'add_new_item'          => esc_html__( 'Add New Project', 'twentig' ),
					'edit_item'             => esc_html__( 'Edit Project', 'twentig' ),
					'new_item'              => esc_html__( 'New Project', 'twentig' ),
					'view_item'             => esc_html__( 'View Project', 'twentig' ),
					'search_items'          => esc_html__( 'Search Projects', 'twentig' ),
					'not_found'             => esc_html__( 'No projects found.', 'twentig' ),
					'not_found_in_trash'    => esc_html__( 'No projects found in Trash.', 'twentig' ),
					'all_items'             => esc_html__( 'All Projects', 'twentig' ),
					'item_link'             => esc_html__( 'Project Link', 'twentig' ),
					'item_link_description' => esc_html__( 'A link to a project.', 'twentig' ),
				),
				'public'          => true,
				'show_ui'         => true,
				'query_var'       => true,
				'capability_type' => 'page',
				'hierarchical'    => false,
				'menu_position'   => 5,
				'menu_icon'       => 'dashicons-portfolio',
				'show_in_rest'    => true,
				'taxonomies'      => array( 'portfolio_category', 'portfolio_tag' ),
				'supports'        => $supports,
				'rewrite'         => array(
					'slug'       => sanitize_title( $project_slug ),
					'with_front' => false,
				),
			)
		);

		register_taxonomy(
			'portfolio_category',
			array( 'portfolio' ),
			array(
				'labels'            => array(
					'name'                  => esc_html__( 'Project Categories', 'twentig' ),
					'item_link'             => esc_html__( 'Project Category Link', 'twentig' ),
					'item_link_description' => esc_html__( 'A link to a project category.', 'twentig' ),
				),
				'hierarchical'      => true,
				'rewrite'           => array( 'slug' => sanitize_title( $category_slug ) ),
				'show_admin_column' => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'portfolio_tag',
			array( 'portfolio' ),
			array(
				'labels'            => array(
					'name'                  => esc_html__( 'Project Tags', 'twentig' ),
					'item_link'             => esc_html__( 'Project Tag Link', 'twentig' ),
					'item_link_description' => esc_html__( 'A link to a project tag.', 'twentig' ),
				),
				'hierarchical'      => false,
				'rewrite'           => array( 'slug' => sanitize_title( $tag_slug ) ),
				'show_admin_column' => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Adds featured image column to portfolio edit screen.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns array.
	 */
	function edit_columns( $columns ) {
		$column_thumbnail = array( 'thumbnail' => esc_html__( 'Featured Image', 'twentig' ) );
		return array_slice( $columns, 0, 2, true ) + $column_thumbnail + array_slice( $columns, 1, null, true );
	}

	/**
	 * Displays featured image inside thumbnail column.
	 *
	 * @param string $column Column name.
	 */
	function custom_columns( $column ) {
		if ( 'thumbnail' === $column ) {
			echo get_the_post_thumbnail( get_the_ID(), array( 60, 60 ) );
		}
	}
}
add_action( 'init', array( 'Twentig_Portfolio', 'init' ), 9 );

/**
 * Filters the list of template types to add template description.
 *
 * @param array $default_template_types An array of template types, formatted as [ slug => [ title, description ] ].
 * @return array Modified array of template types.
 */
function twentig_default_portfolio_templates_types( $default_template_types ) {

	if ( post_type_exists( 'portfolio' ) ) {
		$default_template_types[ 'single-portfolio' ] = array(
			'title'       => esc_html_x( 'Single Projects', 'Template name', 'twentig' ),
			'description' => esc_html__( 'Displays a single project on your website.', 'twentig' ),
		);

		$default_template_types[ 'taxonomy-portfolio_category' ] = array(
			'title'       => esc_html_x( 'Project Categories', 'Template name', 'twentig' ),
			'description' => esc_html__( 'Displays portfolio categories.', 'twentig' ),
		);

		$default_template_types[ 'taxonomy-portfolio_tag' ] = array(
			'title'       => esc_html_x( 'Project Tags', 'Template name', 'twentig' ),
			'description' => esc_html__( 'Displays portfolio tags.', 'twentig' ),
		);
	}

	return $default_template_types;
}
add_filter( 'default_template_types', 'twentig_default_portfolio_templates_types' );