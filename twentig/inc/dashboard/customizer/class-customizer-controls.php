<?php
/**
 * Customizer Controls Class.
 *
 * @package Twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class to register customizer controls and settings.
 */
class Twentig_Customizer_Controls {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$starter_type = get_option( 'twentig-customize-starter' );
		if ( 'custom' === $starter_type ) {
			add_action( 'customize_register', array( $this, 'register_light_customizer_options' ) );
		} else {
			add_action( 'customize_register', array( $this, 'register_customizer_options' ) );
		}
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'enqueue_customizer_scripts' ) );
		add_action( 'admin_menu', array( $this, 'remove_customize_menu' ), 999 );
		add_action( 'admin_bar_menu', array( $this, 'remove_customize_from_admin_bar' ), 999 );
	}

	/**
	 * Register light customizer options for custom starter type.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer object.
	 */
	public function register_light_customizer_options( $wp_customize ) {
		require_once TWENTIG_PATH . 'inc/dashboard/customizer/controls/class-customizer-range-control.php';

		$wp_customize->register_control_type( 'Twentig_Customizer_Range_Control' );

		$wp_customize->add_section(
			'last_step',
			array(
				'title'    => __( 'Set site identity', 'twentig' ),
				'priority' => 1,
			)
		);

		$wp_customize->remove_section( 'static_front_page' );
		$wp_customize->remove_section( 'custom_css' );
		$wp_customize->remove_control( 'site_icon' );
		$wp_customize->remove_control( 'blogdescription' );

		$wp_customize->get_control( 'blogname' )->section  = 'last_step';
		$wp_customize->get_control( 'blogname' )->priority = 1;

		$wp_customize->get_control( 'custom_logo' )->section  = 'last_step';
		$wp_customize->get_control( 'custom_logo' )->priority = 2;

		$wp_customize->get_section( 'title_tagline' )->priority = 2;

		$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';

		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.wp-block-site-title',
				'render_callback' => function() {
					return get_bloginfo( 'name', 'display' );
				},
			)
		);

		$wp_customize->add_setting(
			'logo_width',
			array(
				'default'           => 120,
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'absint',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new Twentig_Customizer_Range_Control(
				$wp_customize,
				'logo_width',
				array(
					'label'       => __( 'Logo Width', 'twentig' ),
					'section'     => 'last_step',
					'input_attrs' => array(
						'min'  => 40,
						'max'  => 300,
						'step' => 10,
					),
					'priority'    => 11,
				)
			)
		);

		$wp_customize->add_setting(
			'portfolio_width',
			array(
				'default'           => 'wide',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => function( $input ) {
					return in_array( $input, array( 'wide', 'full' ), true ) ? $input : 'wide';
				},
				'transport'         => 'postMessage',
			)
		);
	}

	/**
	 * Registers full customizer options.
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer object.
	 */
	public function register_customizer_options( $wp_customize ) {

		require_once TWENTIG_PATH . 'inc/dashboard/customizer/controls/class-color-palette-control.php';
		require_once TWENTIG_PATH . 'inc/dashboard/customizer/controls/class-customizer-range-control.php';
		require_once TWENTIG_PATH . 'inc/dashboard/customizer/controls/class-checkbox-multiple-control.php';
		require_once TWENTIG_PATH . 'inc/dashboard/customizer/controls/class-radio-control.php';

		$wp_customize->register_control_type( 'Twentig_Customizer_Range_Control' );

		$wp_customize->remove_section( 'static_front_page' );
		$wp_customize->remove_section( 'custom_css' );
		$wp_customize->remove_control( 'site_icon' );
		$wp_customize->remove_control( 'blogdescription' );

		$wp_customize->get_control( 'blogname' )->priority = 1;

		$wp_customize->get_control( 'custom_logo' )->priority = 2;

		$wp_customize->get_section( 'title_tagline' )->priority = 2;

		$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';

		$starter_type = get_option( 'twentig-customize-starter' );

		$wp_customize->add_section(
			'presets',
			array(
				'title'    => __( 'Browse presets', 'twentig' ),
				'priority' => 1,
			)
		);

		$wp_customize->add_setting(
			'starter_presets',
			array(
				'default'           => 'default',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
			)
		);

		$presets = $this->get_default_settings();
		$choices = array();

		if ( is_array( $presets ) ) {
			foreach ( array_keys( $presets ) as $index => $preset_key ) {
				$choices[ $preset_key ] = ucfirst( $starter_type ) . ' ' . ( $index + 1 );
			}
		}

		$wp_customize->add_control(
			new Twentig_Customizer_Radio_Control(
				$wp_customize,
				'starter_presets',
				array(
					'label'	  => __( 'Presets', 'twentig' ),
					'section' => 'presets',
					'choices' => $choices,
				)
			)
		);

		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.wp-block-site-title',
				'render_callback' => function() {
					return get_bloginfo( 'name', 'display' );
				},
			)
		);

		$wp_customize->add_setting(
			'logo_width',
			array(
				'default'           => 120,
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'absint',
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			new Twentig_Customizer_Range_Control(
				$wp_customize,
				'logo_width',
				array(
					'label'       => __( 'Logo Width', 'twentig' ),
					'section'     => 'title_tagline',
					'input_attrs' => array(
						'min'  => 40,
						'max'  => 300,
						'step' => 10,
					),
					'priority'    => 11,
				)
			)
		);

		$wp_customize->add_section(
			'navigation',
			array(
				'title' => __( 'Navigation', 'default' ),
			)
		);

		$menu_items = $this->get_menu_items();

		$wp_customize->add_setting(
			'menu',
			array(
				'default'           => array_keys( $menu_items ),
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_multi_choices' ),
			)
		);

		$wp_customize->add_control(
			new Twentig_Customizer_Checkbox_Multiple_Control(
				$wp_customize,
				'menu',
				array(
					'label'   => __( 'Pages', 'default' ),
					'section' => 'navigation',
					'choices' => $menu_items,
				)
			)
		);

		$social_networks = array(
			'instagram' => 'Instagram',
			'linkedin'  => 'LinkedIn',
			'x'         => 'X',
			'facebook'  => 'Facebook',
			'youtube'   => 'YouTube',
			'mail'      => esc_html_x( 'Mail', 'social link block variation name', 'default' ),
		);

		foreach ( $social_networks as $network => $label ) {
			$setting_id = 'social_' . $network;

			$wp_customize->add_setting(
				$setting_id,
				array(
					'default'           => in_array( $network, array( 'instagram', 'linkedin' ), true ) ? '#' : '',
					'sanitize_callback' => $network === 'mail' ? 'sanitize_email' : 'esc_url_raw',
					'capability'        => 'edit_theme_options',
					'transport'         => 'postMessage',
				)
			);

			$wp_customize->add_control(
				$setting_id,
				array(
					'label'       => $label,
					'section'     => 'navigation',
					'type'        => $network === 'mail' ? 'email' : 'url',
					'input_attrs' => array(
						'placeholder' => esc_html__( 'Enter social link', 'default' ),
					),
				)
			);
		}

		$wp_customize->selective_refresh->add_partial(
			'social_links',
			array(
				'selector'        => '.wp-block-social-links',
				'settings'        => array(
					'social_instagram',
					'social_linkedin',
					'social_x',
					'social_facebook',
					'social_youtube',
					'social_mail',
				),
				'render_callback' => array( $this, 'get_social_links' ),
			)
		);

		$wp_customize->add_section(
			'header',
			array(
				'title' => __( 'Header', 'twentig' ),
			)
		);

		$wp_customize->add_setting(
			'header_elements',
			array(
				'default'           => array(),
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_multi_choices' ),
			)
		);

		$wp_customize->add_control(
			new Twentig_Customizer_Checkbox_Multiple_Control(
				$wp_customize,
				'header_elements',
				array(
					'label'   => __( 'Elements', 'twentig' ),
					'section' => 'header',
					'choices' => array(
						'social' => __( 'Social Icons', 'default' ),
						'search' => __( 'Search', 'twentig' ),
						'button' => __( 'Button', 'twentig' ),
					),
				)
			)
		);

		$wp_customize->add_setting(
			'header_layout',
			array(
				'default'           => 'default',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
			)
		);

		$wp_customize->add_control(
			'header_layout',
			array(
				'label'   => __( 'Layout', 'twentig' ),
				'section' => 'header',
				'type'    => 'select',
				'choices' => array(
					'default'         => __( 'Classic', 'twentig' ),
					'left-navigation' => __( 'Left navigation', 'twentig' ),
					'hamburger'       => __( 'Hamburger', 'twentig' ),
				),
			)
		);

		$wp_customize->add_setting(
			'header_width',
			array(
				'default'           => 'full',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'header_width',
			array(
				'label'       => __( 'Width', 'default' ),
				'section'     => 'header',
				'type'        => 'select',
				'choices'     => array(
					'full' => __( 'Full', 'twentig' ),
					'wide' => __( 'Wide', 'twentig' ),
				),
			)
		);

		$wp_customize->add_setting(
			'header_style',
			array(
				'default'           => 'default',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
			)
		);

		$wp_customize->add_control(
			'header_style',
			array(
				'label'   => __( 'Style', 'twentig' ),
				'section' => 'header',
				'type'    => 'select',
				'choices' => array(
					'default' => __( 'Base', 'twentig' ),
					'subtle'  => __( 'Subtle', 'twentig' ),
					'inverse' => __( 'Inverse', 'twentig' ),
					'border'  => __( 'Border', 'twentig' ),
				),
			)
		);

		$wp_customize->add_setting(
			'header_position',
			array(
				'default'           => 'sticky',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
			)
		);

		$wp_customize->add_control(
			'header_position',
			array(
				'label'   => __( 'Position', 'twentig' ),
				'section' => 'header',
				'type'    => 'select',
				'choices' => array(
					'sticky'           => __( 'Sticky', 'twentig' ),
					'sticky-scroll-up' => __( 'Sticky on scroll up', 'twentig' ),
					'static'           => __( 'Static', 'twentig' ),
				),
			)
		);

		$wp_customize->add_section(
			'footer',
			array(
				'title' => __( 'Footer', 'twentig' ),
			)
		);

		$wp_customize->add_setting(
			'footer_layout',
			array(
				'default'           => 'default',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
			)
		);

		$wp_customize->add_control(
			'footer_layout',
			array(
				'label'       => __( 'Layout', 'twentig' ),
				'section'     => 'footer',
				'type'        => 'select',
				'choices'     => array(
					'default'                      => __( 'Simple with social icons', 'twentig' ),
					'simple-navigation'            => __( 'Simple with navigation', 'twentig' ),
					'copyright'                    => __( 'Copyright only', 'twentig' ),
					'with-description'             => __( 'Site title with description', 'twentig' ),
					'2-column-navigation'          => __( '2-column navigation', 'twentig' ),
					'2-column-navigation-headings' => __( '2-column navigation with headings', 'twentig' ),
					'3-column'                     => __( '3-column navigation', 'twentig' ),
					'4-column'                     => __( '4-column with headings', 'twentig' ),
				),
			)
		);

		$wp_customize->add_setting(
			'footer_width',
			array(
				'default'           => 'full',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'footer_width',
			array(
				'label'   => __( 'Width', 'twentig' ),
				'section' => 'footer',
				'type'    => 'select',
				'choices' => array(
					'full' => __( 'Full', 'twentig' ),
					'wide' => __( 'Wide', 'twentig' ),
				),
			)
		);

		$wp_customize->add_setting( 'footer_style',
			array(
				'default'           => 'default',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'footer_style',
			array(
				'label'   => __( 'Style', 'twentig' ),
				'section' => 'footer',
				'type'    => 'select',
				'choices' => array(
					'default' => __( 'Base', 'twentig' ),
					'subtle'  => __( 'Subtle', 'twentig' ),
					'inverse' => __( 'Inverse', 'twentig' ),
					'border'  => __( 'Border', 'twentig' ),
				),
			)
		);

		$wp_customize->add_section(
			'homepage',
			array(
				'title'           => __( 'Homepage', 'twentig' ),
				'active_callback' => function() {
					return get_option( 'show_on_front' ) === 'page' && get_option( 'page_on_front' );
				},
			)
		);

		$wp_customize->add_setting(
			'home_layout',
			array(
				'default'           => get_option( 'page_on_front' ),
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'absint',
				'transport'         => 'postMessage',
			)
		);

		$pages_with_meta = get_pages( array(
			'meta_query' => array(
				array(
					'key'     => '_twentig_website_imported_post',
					'compare' => 'EXISTS',
				),
			),
		) );

		$matching_pages = array();
		foreach ( $pages_with_meta as $page ) {
			if ( str_starts_with( $page->post_name, 'home-' ) ) {
				$matching_pages[] = $page;
			}
		}

		$home_choices  = array();
		$front_page_id = (int) get_option( 'page_on_front' );

		if ( $front_page_id ) {
			$home_choices[ $front_page_id ] = get_the_title( $front_page_id );
		}

		foreach ( $matching_pages as $page ) {
			$home_choices[ $page->ID ] = $page->post_title;
		}

		$wp_customize->add_control(
			'home_layout',
			array(
				'label'   => __( 'Hero Layout', 'twentig' ),
				'section' => 'homepage',
				'type'    => 'select',
				'choices' => $home_choices,
			)
		);

		$wp_customize->add_section(
			'portfolio',
			array(
				'title'           => __( 'Portfolio', 'twentig' ),
				'active_callback' => function() {
					return ( post_type_exists( 'portfolio' ) && 'portfolio' === get_option( 'twentig-customize-starter' ) );
				},
			)
		);

		$wp_customize->add_setting(
			'portfolio_layout',
			array(
				'default'           => 'default',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
			)
		);

		$wp_customize->add_control(
			'portfolio_layout',
			array(
				'label'   => __( 'Portfolio Layout', 'twentig' ),
				'section' => 'portfolio',
				'type'    => 'select',
				'choices' => array(
					'default'          => __( '2-column', 'twentig' ),
					'2-column-covers'  => __( '2-column covers', 'twentig' ),
					'offset'           => __( '2-column offset', 'twentig' ),
					'3-column'         => __( '3-column', 'twentig' ),
					'3-column-cards'   => __( '3-column cards', 'twentig' ),
					'1-column'         => __( 'Single-column', 'twentig' ),
					'1-column-cover'   => __( 'Single-column covers', 'twentig' ),
					'1-2-column'       => __( 'Single-column 2-column', 'twentig' ),
					'alt-side-by-side' => __( 'Alternating side-by-side', 'twentig' ),
				),
			)
		);

		$wp_customize->add_setting(
			'portfolio_width',
			array(
				'default'           => 'wide',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'portfolio_width',
			array(
				'label'   => __( 'Portfolio Width', 'twentig' ),
				'section' => 'portfolio',
				'type'    => 'select',
				'choices' => array(
					'wide' => __( 'Wide', 'twentig' ),
					'full' => __( 'Full', 'twentig' ),
				),
			)
		);

		$wp_customize->add_setting(
			'portfolio_single_layout',
			array(
				'default'           => 'default',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'portfolio_single_layout',
			array(
				'label'   => __( 'Project Hero', 'twentig' ),
				'section' => 'portfolio',
				'type'    => 'select',
				'choices' => array(
					'default'          => __( 'Wide image', 'twentig' ),
					'overlap'          => __( 'Overlap image', 'twentig' ),
					'fullwidth-image'  => __( 'Full-width image', 'twentig' ),
					'image-banner'     => __( 'Image banner', 'twentig' ),
					'cover'            => __( 'Cover', 'twentig' ),
					'fullscreen-cover' => __( 'Fullscreen cover', 'twentig' ),
					'split'            => __( 'Split', 'twentig' ),
					'fullwidth-split'  => __( 'Full-width split', 'twentig' ),
					'title-only'       => __( 'Title only', 'twentig' ),
				),
			)
		);

		$wp_customize->add_setting(
			'portfolio_single_navigation',
			array(
				'default'           => 'default',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'portfolio_single_navigation',
			array(
				'label'   => __( 'Project Navigation', 'twentig' ),
				'section' => 'portfolio',
				'type'    => 'select',
				'choices' => array(
					'default' => __( 'Previous/Next', 'twentig' ),
					'related' => __( 'Related projects', 'twentig' ),
					'none'    => _x( 'None', 'Navigation option', 'twentig' ),
					
				),
			)
		);

		$wp_customize->add_section(
			'blog',
			array(
				'title' => __( 'Blog', 'twentig' ),
			)
		);

		$wp_customize->add_setting(
			'blog_layout',
			array(
				'default'           => 'default',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
			)
		);

		$wp_customize->add_control(
			'blog_layout',
			array(
				'label'   => __( 'Blog Layout', 'twentig' ),
				'section' => 'blog',
				'type'    => 'select',
				'choices' => array(
					'default'          => __( '2-column', 'twentig' ),
					'2-column-covers'  => __( '2-column covers', 'twentig' ),
					'2-column-text'    => __( '2-column text-only', 'twentig' ),
					'3-column'         => __( '3-column', 'twentig' ),
					'3-column-cards'   => __( '3-column cards', 'twentig' ),
					'1-column'         => __( 'Single-column', 'twentig' ),
					'1-column-sidebar' => __( 'Single-column with sidebar', 'twentig' ),
					'side-by-side'     => __( 'Side-by-side', 'twentig' ),
					'editorial-list'   => __( 'Editorial list', 'twentig' ),
				),
			)
		);

		$wp_customize->add_setting(
			'single_layout',
			array(
				'default'           => 'default',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'single_layout',
			array(
				'label'   => __( 'Post Hero', 'twentig' ),
				'section' => 'blog',
				'type'    => 'select',
				'choices' => array(
					'narrow'           => __( 'Narrow image', 'twentig' ),
					'default'          => __( 'Wide image', 'twentig' ),
					'overlap'          => __( 'Overlap image', 'twentig' ),
					'fullwidth-image'  => __( 'Full-width image', 'twentig' ),
					'cover'            => __( 'Cover', 'twentig' ),
					'fullscreen-cover' => __( 'Fullscreen cover', 'twentig' ),
					'split'            => __( 'Split', 'twentig' ),
					'fullwidth-split'  => __( 'Full-width split', 'twentig' ),
					'sidebar'          => __( 'With sidebar', 'twentig' ),
					'title-banner'     => __( 'Title banner', 'twentig' ),
				),
			)
		);

		$wp_customize->add_setting(
			'comments',
			array(
				'default'           => 'default',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'comments',
			array(
				'label'   => __( 'Comments', 'default' ),
				'section' => 'blog',
				'type'    => 'select',
				'choices' => array(
					'comments-avatar' => __( 'With avatar', 'twentig' ),
					'default'         => __( 'Without avatar', 'twentig' ),
					'none'            => _x( 'None', 'Comment option', 'twentig' ),
				),
			)
		);

		$wp_customize->add_setting(
			'single_navigation',
			array(
				'default'           => 'default',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
				'transport'         => 'postMessage',
			)
		);

		$wp_customize->add_control(
			'single_navigation',
			array(
				'label'   => __( 'Post navigation', 'default' ),
				'section' => 'blog',
				'type'    => 'select',
				'choices' => array(
					'default' => __( 'Previous/Next', 'twentig' ),
					'related' => __( 'Related posts', 'twentig' ),
					'none'    => _x( 'None', 'Navigation option', 'twentig' ),
				),
			)
		);

		$wp_customize->add_section(
			'colors',
			array(
				'title' => __( 'Colors', 'default' ),
			)
		);

		$wp_customize->add_setting(
			'color_palette',
			array(
				'default'           => '',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => array( $this, 'sanitize_select' ),
			)
		);

		$wp_customize->add_control(
			new Twentig_Customizer_Color_Palette_Control(
				$wp_customize,
				'color_palette',
				array(
					'label'   => __( 'Palettes', 'default' ),
					'section' => 'colors',
				)
			)
		);

		if ( $this->is_latin_language() ) {
			$wp_customize->add_section(
				'fonts',
				array(
					'title' => __( 'Typography', 'twentig' ),
				)
			);

			$wp_customize->add_setting(
				'typography',
				array(
					'default'           => '',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => array( $this, 'sanitize_select' ),
				)
			);

			$variations = WP_Theme_JSON_Resolver::get_style_variations();
			$font_choices = array( '' => __( 'System Fonts', 'twentig' ) );

			foreach ( $variations as $variation ) {
				if ( isset( $variation['title'] ) && isset( $variation['settings']['typography'] ) ) {
					$font_choices[ $variation['title'] ] = $variation['title'];
				}
			}

			$wp_customize->add_control(
				'typography',
				array(
					'label'   => __( 'Fonts', 'twentig' ),
					'section' => 'fonts',
					'type'    => 'select',
					'choices' => $font_choices,
				)
			);
		}

	}

	/**
	 * Get social links markup.
	 *
	 * @return string Social links HTML markup.
	 */
	public function get_social_links() {
		return do_blocks( Twentig_Customizer_Updater::get_social_links() );
	}

	/**
	 * Get menu items from navigation block.
	 *
	 * @return array Menu items array.
	 */
	private function get_menu_items() {
		$menu_items = array();

		$header_template = get_posts(
			array(
				'post_type'     => 'wp_template_part',
				'post_name__in' => array( 'header' ),
				'post_status'   => array( 'publish' ),
				'numberposts'   => 1,
				'tax_query'     => array(
					array(
						'taxonomy' => 'wp_theme',
						'field'    => 'name',
						'terms'    => wp_get_theme()->get_stylesheet(),
					),
				),
			)
		);

		$header_content = $header_template[0]->post_content ?? '';

		$nav_ref = Twentig_Customizer_Updater::get_navigation_ref( $header_content );
		if ( $nav_ref ) {
			$nav_post = get_post( $nav_ref );
			if ( $nav_post ) {
				$blocks = parse_blocks( $nav_post->post_content );
				foreach ( $blocks as $block ) {
					if ( $block['blockName'] === 'core/home-link' ) {
						$menu_items['home'] = $block['attrs']['label'] ?? __( 'Home', 'default' );
					} elseif ( $block['blockName'] === 'core/navigation-link' ) {
						$id = $block['attrs']['id'] ?? 0;
						if ( $id ) {
							$menu_items[ $id ] = $block['attrs']['label'] ?? '';
						}
					}
				}
			}
		}
		return $menu_items;
	}

	/**
	 * Sanitize multiple choice values.
	 *
	 * @param mixed $value Value to sanitize.
	 * @return array Sanitized array of values.
	 */
	public function sanitize_multi_choices( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}
		return array_map( 'sanitize_key', $value );
	}

	/**
	 * Sanitize a select/radio setting value against its allowed choices.
	 *
	 * @param string              $input   The value to sanitize.
	 * @param WP_Customize_Setting $setting The setting instance.
	 * @return string Sanitized value or default if invalid.
	 */
	public function sanitize_select( $input, $setting ) {
		$control = $setting->manager->get_control( $setting->id );
		if ( $control && isset( $control->choices[ $input ] ) ) {
			return $input;
		}
		return $setting->default;
	}

	/**
	 * Enqueue customizer control scripts and styles.
	 */
	public function enqueue_customizer_scripts() {

		wp_enqueue_style(
			'twentig-customizer',
			TWENTIG_ASSETS_URI . '/css/customizer.css',
			array(),
			TWENTIG_VERSION
		);

		wp_enqueue_script(
			'twentig-customizer-controls',
			TWENTIG_ASSETS_URI . '/js/customizer-controls.js',
			array( 'customize-controls', 'jquery' ),
			TWENTIG_VERSION,
			true
		);

		// Get the blog page URL
		$blog_url = get_option( 'show_on_front' ) === 'posts' ? home_url( '/' ) : get_permalink( get_option( 'page_for_posts' ) );

		// Get the first post URL
		$first_post = get_posts(
			array(
				'numberposts' => 1,
				'post_status' => 'publish'
			)
		);
		$post_url = ! empty( $first_post ) ? get_permalink( $first_post[0] ) : '';

		// Get portfolio page URL
		$portfolio_page = get_page_by_path( 'portfolio' );
		$portfolio_url  = $portfolio_page ? get_permalink( $portfolio_page ) : home_url( '/' );

		// Get first portfolio item URL
		$first_project = get_posts(
			array(
				'post_type'   => 'portfolio',
				'numberposts' => 1,
				'post_status' => 'publish'
			)
		);
		$project_url = ! empty( $first_project ) ? get_permalink( $first_project[0] ) : '';

		$default_preset = get_theme_mod( 'starter_presets', 'default' );
		$presets        = $this->get_default_settings();

		wp_add_inline_script(
			'twentig-customizer-controls',
			'var twentigCustomizer = ' . wp_json_encode( array(
				'nonce'           => wp_create_nonce( 'twentig_update_templates' ),
				'finishText'      => esc_html__( 'Finish', 'twentig' ),
				'homeUrl'         => esc_url( home_url( '/' ) ),
				'blogUrl'         => esc_url( $blog_url ),
				'firstPostUrl'    => esc_url( $post_url ),
				'portfolioUrl'    => esc_url( $portfolio_url ),
				'firstProjectUrl' => esc_url( $project_url ),
				'defaultSettings' => $presets[ $default_preset ] ?? null,
				'presets'         => $presets,
			) ) . ';',
			'before'
		);
	
	}

	/**
	 * Get default preset settings for different starter types.
	 *
	 * @return array Default settings array.
	 */
	private function get_default_settings() {
		$presets      = array();
		$starter_type = get_option( 'twentig-customize-starter', 'portfolio' );
		$latin        = $this->is_latin_language();

		switch ( $starter_type ) {
			case 'business':
				$presets = array(
					'default' => array(
						'social_instagram'  => '#',
						'social_linkedin'   => '#',
						'social_youtube'    => '#',
						'social_x'          => '#',
						'header_elements'   => array(),
						'header_width'      => 'full',
						'header_layout'     => 'default',
						'header_style'      => 'default',
						'header_position'   => 'sticky',
						'footer_layout'     => '2-column-navigation',
						'footer_width'      => 'full',
						'footer_style'      => 'inverse',
						'home_layout'       => get_option( 'page_on_front' ),
						'blog_layout'       => 'default',
						'single_layout'     => 'cover',
						'comments'          => 'none',
						'single_navigation' => 'default',
						'color_palette'     => '',
						'typography'        => $latin ? 'Google Sans' : '',
					),
					'business-2' => array(
						'social_instagram'  => '#',
						'social_linkedin'   => '#',
						'social_x'          => '#',
						'social_youtube'    => '#',
						'header_elements'   => array(),
						'header_width'      => 'wide',
						'header_layout'     => 'default',
						'header_style'      => 'border',
						'header_position'   => 'sticky-scroll-up',
						'footer_layout'     => '4-column',
						'footer_width'      => 'wide',
						'footer_style'      => 'border',
						'home_layout'       => get_page_by_path( 'home-2' )?->ID ?? 0,
						'blog_layout'       => 'editorial-list',
						'single_layout'     => 'default',
						'comments'          => 'none',
						'single_navigation' => 'related',
						'color_palette'     => esc_html_x( 'Black', 'Style variation name', 'twentig-one' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'typography'        => $latin ? 'Mona Sans' : '',		
					),
					'business-3' => array(
						'social_instagram'  => '#',
						'social_linkedin'   => '#',
						'social_x'          => '#',
						'social_youtube'    => '#',
						'header_elements'   => array( 'button' ),
						'header_layout'     => 'default',
						'header_width'      => 'full',
						'header_style'      => 'default',
						'header_position'   => 'sticky-scroll-up',
						'footer_layout'     => 'with-description',
						'footer_width'      => 'full',
						'footer_style'      => 'inverse',
						'home_layout'       => get_page_by_path( 'home-3' )?->ID ?? 0,
						'blog_layout'       => '3-column-cards',
						'single_layout'     => 'split',
						'comments'          => 'none',
						'single_navigation' => 'related',	
						'color_palette'     => esc_html_x( 'Off-White', 'Style variation name', 'twentig-one' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'typography'        => $latin ? 'Source Serif 4 · Source Sans 3' : '',
					),
					'business-4' => array(
						'social_instagram'  => '#',
						'social_linkedin'   => '#',
						'social_x'          => '#',
						'social_youtube'    => '#',
						'header_elements'   => array( 'social' ),
						'header_layout'     => 'left-navigation',
						'header_width'      => 'full',
						'header_style'      => 'default',
						'header_position'   => 'sticky',
						'footer_layout'     => 'simple-navigation',
						'footer_width'      => 'full',
						'footer_style'      => 'inverse',
						'home_layout'       => get_page_by_path( 'home-4' )?->ID ?? 0,
						'blog_layout'       => '2-column-text',
						'single_layout'     => 'sidebar',
						'comments'          => 'none',
						'single_navigation' => 'related',
						'color_palette'     => esc_html_x( 'Champagne', 'Style variation name', 'twentig-one' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'typography'        => $latin ? 'Roboto Flex · Roboto Serif' : '',
					),
				);
				break;
			case 'portfolio':
				$presets = array(
					'default'     => array(
						'social_instagram'            => '#',
						'social_linkedin'             => '#',
						'social_mail'                 => 'contact@example.com',
						'header_elements'             => array(),
						'header_layout'               => 'default',
						'header_width'                => 'full',
						'header_style'                => 'default',
						'header_position'             => 'sticky-scroll-up',
						'footer_layout'               => 'default',
						'footer_width'                => 'full',
						'footer_style'                => 'default',
						'home_layout'                 => get_option( 'page_on_front' ),
						'portfolio_layout'            => 'default',
						'portfolio_width'             => 'wide',
						'portfolio_single_layout'     => 'default',
						'portfolio_single_navigation' => 'default',
						'blog_layout'                 => '2-column-covers',
						'single_layout'               => 'fullwidth-split',
						'comments'                    => 'none',
						'single_navigation'           => 'default',
						'color_palette'               => '',
						'typography'                  => $latin ? 'Inter' : '',
					),
					'portfolio-2' => array(
						'social_instagram'            => '#',
						'social_linkedin'             => '#',
						'social_mail'                 => 'contact@example.com',
						'header_elements'             => array(),
						'header_layout'               => 'default',
						'header_width'                => 'full',
						'header_style'                => 'default',
						'header_position'             => 'sticky',
						'footer_layout'               => 'default',
						'footer_width'                => 'full',
						'footer_style'                => 'default',
						'home_layout'                 => get_page_by_path( 'home-2' )?->ID ?? 0,
						'portfolio_layout'            => '2-column-covers',
						'portfolio_width'             => 'wide',
						'portfolio_single_layout'     => 'fullscreen-cover',
						'portfolio_single_navigation' => 'related',
						'blog_layout'                 => 'side-by-side',
						'single_layout'               => 'default',
						'comments'                    => 'none',
						'single_navigation'           => 'related',
						'color_palette'               => esc_html_x( 'Black', 'Style variation name', 'twentig-one' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'typography'                  => $latin ? 'Syne · Mona Sans' : '',
					),
					'portfolio-3' => array(
						'social_instagram'            => '#',
						'social_linkedin'             => '#',
						'social_mail'                 => 'contact@example.com',
						'header_elements'             => array(),
						'header_layout'               => 'hamburger',
						'header_width'                => 'full',
						'header_style'                => 'default',
						'header_position'             => 'sticky-scroll-up',
						'footer_layout'               => 'default',
						'footer_width'                => 'full',
						'footer_style'                => 'default',
						'home_layout'                 => get_option( 'page_on_front' ),
						'portfolio_layout'            => '1-2-column',
						'portfolio_width'             => 'full',
						'portfolio_single_layout'     => 'fullwidth-image',
						'portfolio_single_navigation' => 'related',
						'blog_layout'                 => '1-column',
						'single_layout'               => 'narrow',
						'comments'                    => 'none',
						'single_navigation'           => 'default',
						'color_palette'               => esc_html_x( 'Gray', 'Style variation name', 'twentig-one' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'typography'                  => $latin ? 'Instrument Serif · Instrument Sans' : '',
					),
					'portfolio-4' => array(
						'social_instagram'            => '#',
						'social_linkedin'             => '#',
						'social_mail'                 => 'contact@example.com',
						'header_elements'             => array( 'social' ),
						'header_layout'               => 'left-navigation',
						'header_width'                => 'full',
						'header_style'                => 'default',
						'header_position'             => 'sticky-scroll-up',
						'footer_layout'               => 'simple-navigation',
						'footer_width'                => 'full',
						'footer_style'                => 'default',
						'home_layout'                 => get_page_by_path( 'home-4' )?->ID ?? 0,
						'portfolio_layout'            => '3-column',
						'portfolio_width'             => 'full',
						'portfolio_single_layout'     => 'fullwidth-split',
						'portfolio_single_navigation' => 'related',
						'blog_layout'                 => 'default',
						'single_layout'               => 'fullwidth-image',
						'comments'                    => 'none',
						'single_navigation'           => 'default',
						'color_palette'               => esc_html_x( 'Pink', 'Style variation name', 'twentig-one' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'typography'                  => $latin ? 'Space Grotesk' : '',
					),
				);
				break;

			case 'blog':
				$presets = array(
					'default' => array(
						'social_instagram'  => '#',
						'social_linkedin'   => '',
						'social_youtube'    => '#',
						'header_elements'   => array(),
						'header_layout'     => 'default',
						'header_width'      => 'full',
						'header_style'      => 'default',
						'header_position'   => 'sticky-scroll-up',
						'footer_layout'     => 'default',
						'footer_width'      => 'full',
						'footer_style'      => 'default',
						'blog_layout'       => '3-column',
						'single_layout'     => 'default',
						'comments'          => 'comments-avatar',
						'single_navigation' => 'default',
						'color_palette'     => '',
						'typography'        => $latin ? 'DM Sans' : '',
					),
					'blog-2' => array(
						'social_instagram'  => '#',
						'social_linkedin'   => '',
						'social_youtube'    => '#',
						'header_elements'   => array( 'social' ),
						'header_layout'     => 'default',
						'header_width'      => 'full',
						'header_style'      => 'default',
						'header_position'   => 'sticky-scroll-up',
						'footer_layout'     => 'simple-navigation',
						'footer_width'      => 'full',
						'footer_style'      => 'default',
						'blog_layout'       => '2-column-covers',
						'single_layout'     => 'fullscreen-cover',
						'comments'          => 'default',
						'single_navigation' => 'related',
						'color_palette'     => esc_html_x( 'Jet Black', 'Style variation name', 'twentig-one' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'typography'        => $latin ? 'Oswald · Source Serif 4' : '',
					),
					'blog-3' => array(
						'social_instagram'  => '#',
						'social_linkedin'   => '',
						'social_youtube'    => '#',
						'header_elements'   => array( 'search' ),
						'header_layout'     => 'default',
						'header_width'      => 'wide',
						'header_style'      => 'subtle',
						'header_position'   => 'sticky',
						'footer_layout'     => 'with-description',
						'footer_width'      => 'wide',
						'footer_style'      => 'subtle',
						'blog_layout'       => '1-column-sidebar',
						'single_layout'     => 'sidebar',
						'comments'          => 'comments-avatar',
						'single_navigation' => 'related',
						'color_palette'     => esc_html_x( 'Champagne', 'Style variation name', 'twentig-one' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'typography'        => $latin ? 'Fraunces · DM Sans' : '',
					),
					'blog-4' => array(
						'social_instagram'  => '#',
						'social_linkedin'   => '',
						'social_youtube'    => '#',
						'header_elements'   => array( 'social' ),
						'header_layout'     => 'left-navigation',
						'header_width'      => 'full',
						'header_style'      => 'default',
						'header_position'   => 'static',
						'footer_layout'     => 'simple-navigation',
						'footer_width'      => 'full',
						'footer_style'      => 'default',
						'blog_layout'       => 'default',
						'single_layout'     => 'overlap',
						'comments'          => 'default',
						'single_navigation' => 'related',
						'color_palette'     => esc_html_x( 'Light Gray', 'Style variation name', 'twentig-one' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'typography'        => $latin ? 'Mozilla Headline · Mozilla Text' : '',
					),
				);
				break;

			case 'personal':
				$presets = array(
					'default' => array(
						'social_instagram'  => '#',
						'social_linkedin'   => '#',
						'header_elements'   => array( 'social' ),
						'header_layout'     => 'default',
						'header_width'      => 'full',
						'header_style'      => 'default',
						'header_position'   => 'sticky',
						'footer_layout'     => 'simple-navigation',
						'footer_width'      => 'full',
						'footer_style'      => 'default',
						'home_layout'       => get_option( 'page_on_front' ),
						'blog_layout'       => '1-column',
						'single_layout'     => 'overlap',
						'comments'          => 'none',
						'single_navigation' => 'default',
						'color_palette'     => '',
						'typography'        => $latin ? 'TikTok Sans' : '',
					),
					'personal-2' => array(
						'social_instagram'  => '#',
						'social_linkedin'   => '#',
						'header_elements'   => array( 'social' ),
						'header_layout'     => 'left-navigation',
						'header_width'      => 'full',
						'header_style'      => 'default',
						'header_position'   => 'static',
						'footer_layout'     => 'default',
						'footer_width'      => 'full',
						'footer_style'      => 'default',
						'home_layout'       => get_page_by_path( 'home-2' )?->ID ?? 0,
						'blog_layout'       => '2-column-text',
						'single_layout'     => 'cover',
						'comments'          => 'none',
						'single_navigation' => 'related',
						'color_palette'     => esc_html_x( 'Dark Blue', 'Style variation name', 'twentig-one' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'typography'        => $latin ? 'Bricolage Grotesque · TikTok Sans' : '',
					),
					'personal-3' => array(
						'social_instagram'  => '#',
						'social_linkedin'   => '#',
						'header_elements'   => array(),
						'header_layout'     => 'default',
						'header_width'      => 'full',
						'header_style'      => 'default',
						'header_position'   => 'static',
						'footer_layout'     => 'with-description',
						'footer_width'      => 'full',
						'footer_style'      => 'subtle',
						'home_layout'       => get_page_by_path( 'home-3' )?->ID ?? 0,
						'blog_layout'       => 'editorial-list',
						'single_layout'     => 'split',
						'comments'          => 'none',
						'single_navigation' => 'default',
						'color_palette'     => esc_html_x( 'Mocha', 'Style variation name', 'twentig-one' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'typography'        => $latin ? 'Source Sans 3 · Inconsolata' : '',
					),
					'personal-4' => array(
						'social_instagram'  => '#',
						'social_linkedin'   => '#',
						'header_elements'   => array(),
						'header_layout'     => 'default',
						'header_width'      => 'full',
						'header_style'      => 'default',
						'header_position'   => 'sticky-scroll-up',
						'footer_layout'     => 'default',
						'footer_width'      => 'full',
						'footer_style'      => 'default',
						'home_layout'       => get_page_by_path( 'home-4' )?->ID ?? 0,
						'blog_layout'       => '3-column',
						'single_layout'     => 'default',
						'comments'          => 'none',
						'single_navigation' => 'related',
						'color_palette'     => esc_html_x( 'Teal', 'Style variation name', 'twentig-one' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
						'typography'        => $latin ? 'Literata' : '',
					),
				);
				break;

		}
		return $presets;
	}

	/**
	 * Check if current language uses Latin script.
	 *
	 * @return bool True if language uses Latin script, false otherwise.
	 */	
	private function is_latin_language() {
		$locale = get_option( 'WPLANG' );
		
		if ( ! $locale && is_multisite() ) {
			$locale = get_site_option( 'WPLANG' );
		}
		
		$lang = $locale ? strtok( strtolower( str_replace( '_', '-', $locale ) ), '-' ) : 'en';

		$latin_langs = array(
			'en', 'es', 'fr', 'de', 'pt', 'nl', 'it',
			'ca', 'cs', 'da', 'et', 'fi', 'hr', 'hu',
			'id', 'lt', 'lv', 'nb', 'pl', 'ro', 'sk',
			'sl', 'sv', 'tr',
		);

		return in_array( $lang, $latin_langs, true );
	}

	/**
	 * Remove customize menu from admin menu.
	 */
	public function remove_customize_menu() {
		global $submenu;
		if ( isset( $submenu['themes.php'] ) ) {
			foreach ( $submenu['themes.php'] as $key => $item ) {
				if ( $item[1] === 'customize' ) {
					unset( $submenu['themes.php'][$key] );
					break;
				}
			}
		}
	}

	/**
	 * Remove customize link from admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar object.
	 */
	public function remove_customize_from_admin_bar( $wp_admin_bar ) {
		$wp_admin_bar->remove_node( 'customize' );
	}

}
