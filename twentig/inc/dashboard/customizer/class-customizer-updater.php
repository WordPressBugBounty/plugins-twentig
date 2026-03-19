<?php
/**
 * Customizer Updater Class.
 *
 * Handles saving and previewing template customizations made through
 * the Twentig Customizer, including blog, single post, portfolio,
 * header, footer, and global style updates.
 *
 * @package Twentig
 */

defined( 'ABSPATH' ) || exit;

/**
 * Applies customizer settings to block templates and template parts.
 */
class Twentig_Customizer_Updater {

	public function __construct() {
		add_action( 'customize_preview_init', array( $this, 'enqueue_customizer_preview_script' ) );
		add_action( 'wp_ajax_twentig_update_templates', array( $this, 'save_templates' ) );

		if ( is_customize_preview() ) {
			add_filter( 'render_block_core/site-title', array( $this, 'filter_home_url' ), 10, 2 );
			add_filter( 'render_block_core/home-link', array( $this, 'filter_home_url' ), 10, 2 );
			add_filter( 'get_block_templates', array( $this, 'modify_templates' ), 10, 2 );
			add_filter( 'render_block_core/template-part', array( $this, 'modify_template_part' ), 10, 2 );
			add_filter( 'wp_theme_json_data_user', array( $this, 'set_style_variation' ) );
			add_filter( 'render_block_data', array( $this, 'set_navigation_attributes' ) );
			add_filter( 'render_block_core/query', array( $this, 'customize_query_block' ), 10, 2 );
		}
	}

	/**
	 * Enqueues script for customizer preview.
	 */
	public function enqueue_customizer_preview_script() {
		wp_enqueue_script(
			'twentig-customizer-preview',
			TWENTIG_ASSETS_URI . '/js/customizer-preview.js',
			array( 'customize-preview', 'jquery' ),
			TWENTIG_VERSION,
			true
		);
		wp_add_inline_style(
			'customize-preview',
			'.has-border-top { border-top: 1px solid var(--wp--preset--color--tertiary); } .customize-partial-edit-shortcut, .wp-block-navigation-item:has(a.hidden) { display: none !important;}'
		);
	}

	/**
	 * Saves all template modifications from the customizer.
	 */
	public function save_templates() {
		check_ajax_referer( 'twentig_update_templates', 'nonce' );

		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
			return;
		}

		$this->save_blog_templates();
		$this->save_single_post_template();
		$this->save_portfolio_templates();
		$this->save_single_portfolio_template();
		$this->save_home_template();
		$this->save_header_template();
		$this->save_footer_template();
		$this->update_global_styles();
		$this->cleanup_theme_mods();

		wp_send_json_success( array( 'message' => 'Customization process completed' ) );
	}

	/**
	 * Saves blog index and archive templates.
	 */
	private function save_blog_templates() {
		$blog_layout = get_theme_mod( 'blog_layout', 'default' );

		if ( $blog_layout === 'default' ) {
			return;
		}

		$query_search    = '#<!--\s*wp:query(?!-)\b[^>]*-->.*?<!--\s*/wp:query\s*-->#is';
		$pattern_content = $this->get_pattern_content( 'post-loop-' . $blog_layout );
		$default_pattern = $this->get_selected_pattern( 'post-loop-default' );

		foreach ( array( 'index', 'archive' ) as $name ) {
			$id = $this->get_or_create_template( $name );
			if ( ! $id ) {
				continue;
			}
			$post_content = get_post_field( 'post_content', $id );

			if ( empty( $post_content ) ) {
				$post_content = $this->get_pattern_content( 'template-' . $name . '-default' );
			}

			$result = preg_replace( $query_search, $pattern_content, $post_content, 1 );
			if ( null !== $result ) {
				$post_content = $result;
			}
			$post_content = str_replace( $default_pattern, $pattern_content, $post_content );

			$this->update_template_post( $id, $post_content );
		}
	}

	/**
	 * Saves the single post template.
	 */
	private function save_single_post_template() {
		$single_layout = get_theme_mod( 'single_layout', 'default' );
		$comments      = get_theme_mod( 'comments', 'default' );
		$navigation    = get_theme_mod( 'single_navigation', 'default' );

		if ( $single_layout === 'default' && $comments === 'default' && $navigation === 'default' ) {
			return;
		}

		$id              = $this->get_or_create_template( 'single' );
		$pattern_content = $this->build_single_post_content();

		$this->update_template_post( $id, $pattern_content );
	}

	/**
	 * Builds single post template content with comments and navigation adjustments.
	 *
	 * @return string The assembled template content.
	 */
	private function build_single_post_content() {
		$layout          = get_theme_mod( 'single_layout', 'default' );
		$comments        = get_theme_mod( 'comments', 'default' );
		$navigation      = get_theme_mod( 'single_navigation', 'default' );
		$blog_layout     = get_theme_mod( 'blog_layout', 'default' );

		$pattern_content = $this->get_pattern_content( 'template-single-post-' . $layout );

		if ( $comments === 'none' ) {
			$pattern_content = str_replace( $this->get_selected_pattern( 'comments-default' ), '', $pattern_content );
		} elseif ( $comments === 'comments-avatar' ) {
			$pattern_content = str_replace( $this->get_selected_pattern( 'comments-default' ), $this->get_selected_pattern( 'comments-avatar' ), $pattern_content );
		}

		if ( $navigation === 'none' ) {
			if ( $layout === 'sidebar' ) {
				$pattern_content = str_replace( $this->get_selected_pattern( 'post-navigation-narrow' ), '', $pattern_content );
			} else {
				$pattern_content = str_replace( $this->get_selected_pattern( 'post-navigation' ), '', $pattern_content );
			}
		} elseif ( $navigation === 'related' ) {
			if ( $layout === 'sidebar' ) {
				$pattern_content = str_replace( $this->get_selected_pattern( 'post-navigation-narrow' ), $this->get_selected_pattern( 'related-posts-sidebar' ), $pattern_content );
			} elseif ( $blog_layout === 'editorial-list' ) {
				$pattern_content = str_replace( $this->get_selected_pattern( 'post-navigation' ), $this->get_selected_pattern( 'related-posts-default' ), $pattern_content );
			} else {
				$registry        = WP_Block_Patterns_Registry::get_instance();
				$related_pattern = $registry->is_registered( 'twentigone/related-posts-' . $blog_layout ) ? 'related-posts-' . $blog_layout : 'related-posts';
				$pattern_content = str_replace( $this->get_selected_pattern( 'post-navigation' ), $this->get_selected_pattern( $related_pattern ), $pattern_content );
			}
		}

		return $pattern_content;
	}

	/**
	 * Saves portfolio archive templates and the portfolio page.
	 */
	private function save_portfolio_templates() {
		$portfolio_layout = get_theme_mod( 'portfolio_layout', 'default' );
		$portfolio_width  = get_theme_mod( 'portfolio_width', 'wide' );

		if ( $portfolio_layout === 'default' && $portfolio_width === 'wide' ) {
			return;
		}

		$query_search    = '#<!--\s*wp:query(?!-)\b[^>]*-->.*?<!--\s*/wp:query\s*-->#is';
		$pattern_content = $this->get_pattern_content( 'portfolio-loop-' . $portfolio_layout );
		$default_pattern = $this->get_selected_pattern( 'portfolio-loop-default' );

		$page = get_page_by_path( 'portfolio' );
		if ( ! $page ) {
			$front_page_id = (int) get_option( 'page_on_front' );
			if ( $front_page_id > 0 ) {
				$page = get_post( $front_page_id );
			}
		}

		if ( $page ) {
			$portfolio_content = $page->post_content;
			$result            = preg_replace( $query_search, $pattern_content, $portfolio_content, 1 );
			if ( null !== $result ) {
				$portfolio_content = $result;
			}
			$portfolio_content = str_replace( '"inherit":true', '"inherit":false', $portfolio_content );

			if ( 'full' === $portfolio_width ) {
				$portfolio_content = str_replace(
					'"metadata":{"id":"portfolio-section"},"layout":{"type":"constrained"}',
					'"metadata":{"id":"portfolio-section"}',
					$portfolio_content
				);
			}

			$this->update_template_post( $page->ID, $portfolio_content );
		}

		foreach ( array( 'taxonomy-portfolio_category', 'taxonomy-portfolio_tag' ) as $name ) {
			$id = $this->get_or_create_template( $name );
			if ( ! $id ) {
				continue;
			}
			$portfolio_content = get_post_field( 'post_content', $id );

			if ( empty( $portfolio_content ) ) {
				$portfolio_content = $this->get_pattern_content( 'template-archive-portfolio' );
			}

			$result = preg_replace( $query_search, $pattern_content, $portfolio_content, 1 );
			if ( null !== $result ) {
				$portfolio_content = $result;
			}
			$portfolio_content = str_replace( $default_pattern, $pattern_content, $portfolio_content );

			if ( 'full' === $portfolio_width ) {
				$portfolio_content = str_replace(
					'"metadata":{"id":"portfolio-section"},"layout":{"type":"constrained"}',
					'"metadata":{"id":"portfolio-section"}',
					$portfolio_content
				);
			}

			$this->update_template_post( $id, $portfolio_content );
		}
	}

	/**
	 * Saves the single portfolio template.
	 */
	private function save_single_portfolio_template() {
		$portfolio_single = get_theme_mod( 'portfolio_single_layout', 'default' );
		$portfolio_nav    = get_theme_mod( 'portfolio_single_navigation', 'default' );

		if ( $portfolio_single === 'default' && $portfolio_nav === 'default' ) {
			return;
		}

		$id              = $this->get_or_create_template( 'single-portfolio' );
		$pattern_content = $this->build_single_portfolio_content();

		$this->update_template_post( $id, $pattern_content );
	}

	/**
	 * Builds single portfolio template content with navigation adjustments.
	 *
	 * @return string The assembled template content.
	 */
	private function build_single_portfolio_content() {
		$layout           = get_theme_mod( 'portfolio_single_layout', 'default' );
		$navigation       = get_theme_mod( 'portfolio_single_navigation', 'default' );
		$pattern_content  = $this->get_pattern_content( 'template-single-portfolio-' . $layout );

		if ( $navigation === 'none' ) {
			$pattern_content = str_replace( $this->get_selected_pattern( 'post-navigation' ), '', $pattern_content );
		} elseif ( $navigation === 'related' ) {
			$portfolio_layout = get_theme_mod( 'portfolio_layout', 'default' );
			$portfolio_width  = get_theme_mod( 'portfolio_width', 'wide' );
			$registry         = WP_Block_Patterns_Registry::get_instance();
			$related_pattern  = $registry->is_registered( 'twentigone/related-projects-' . $portfolio_layout ) ? 'related-projects-' . $portfolio_layout : 'related-projects';
			$related_content  = $this->get_selected_pattern( $related_pattern );

			if ( 'full' === $portfolio_width ) {
				$related_content = $this->get_pattern_content( $related_pattern );
				$related_content = str_replace( ',"layout":{"type":"constrained"}', '', $related_content );
			}

			$pattern_content = str_replace( $this->get_selected_pattern( 'post-navigation' ), $related_content, $pattern_content );
		}

		return $pattern_content;
	}

	/**
	 * Saves the home template and cleans up duplicate home pages.
	 */
	private function save_home_template() {
		$front_page_id = get_option( 'page_on_front' );

		if ( ! $front_page_id ) {
			return;
		}

		$pages_with_meta = get_posts( array(
			'post_type'      => 'page',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => '_twentig_website_imported_post',
					'compare' => 'EXISTS',
				),
			),
		) );

		$home_title = __( 'Home', 'default' );
		foreach ( $pages_with_meta as $page ) {
			if ( $page->ID === (int) $front_page_id ) {
				continue;
			}
			if ( str_starts_with( $page->post_name, 'home-' ) || $page->post_title === $home_title ) {
				// Force-delete only if the post has never been modified. If the user has since edited it,
				// send it to Trash instead so no content is silently lost.
				wp_delete_post( $page->ID, $page->post_date === $page->post_modified );
			}
		}

		wp_update_post( array(
			'ID'         => $front_page_id,
			'post_title' => $home_title,
			'post_name'  => sanitize_title( $home_title ),
		) );
	}

	/**
	 * Saves the header template part.
	 */
	private function save_header_template() {
		$header_layout  = get_theme_mod( 'header_layout', 'default' );
		$header_id      = $this->get_or_create_template( 'header', 'wp_template_part' );
		$header_content = $this->get_selected_pattern( 'header-' . $header_layout );
		$header_content = $this->modify_header_template( $header_content );

		$this->update_template_post( $header_id, $header_content );
		$this->save_navigation( $header_content );
	}

	/**
	 * Saves the footer template part.
	 */
	private function save_footer_template() {
		$footer_layout  = get_theme_mod( 'footer_layout', 'default' );
		$footer_id      = $this->get_or_create_template( 'footer', 'wp_template_part' );
		$footer_content = $this->get_selected_pattern( 'footer-' . $footer_layout );
		$footer_content = $this->modify_footer_template( $footer_content );

		$this->update_template_post( $footer_id, $footer_content );
	}

	/**
	 * Cleans up theme mods after saving, preserving only the custom logo.
	 */
	private function cleanup_theme_mods() {
		foreach ( TwentigDashboard::get_theme_mod_keys() as $mod_key ) {
			remove_theme_mod( $mod_key );
		}

		delete_option( 'twentig-customize-starter' );
	}

	/**
	 * Gets or creates a template.
	 *
	 * @param string $template_name Template name.
	 * @param string $template_type Template type (default: wp_template).
	 * @return int|null Template ID or null on error.
	 */
	private function get_or_create_template( $template_name, $template_type = 'wp_template' ) {
		$stylesheet = wp_get_theme()->get_stylesheet();
		$template = get_posts( array(
			'post_name__in' => array( $template_name ),
			'post_type'     => $template_type,
			'post_status'   => 'publish',
			'numberposts'   => 1,
			'tax_query'     => array(
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => $stylesheet,
				),
			),
		) );

		if ( ! empty( $template ) ) {
			return $template[0]->ID;
		}

		$template_data = array(
			'post_title'   => ucwords( str_replace( array( '-', '_' ), ' ', $template_name ) ),
			'post_name'    => $template_name,
			'post_type'    => $template_type,
			'post_status'  => 'publish',
			'post_content' => '',
			'meta_input'   => array(
				'origin' => 'theme',
			),
			'tax_input'    => array_merge(
				array( 'wp_theme' => array( $stylesheet ) ),
				$template_type === 'wp_template_part' ? array( 'wp_template_part_area' => array( $template_name ) ) : array()
			),
		);

		$default_template_types  = get_default_block_template_types();
		$template_part_areas     = get_allowed_block_template_part_areas();
		$template_part_templates = array_reduce( $template_part_areas, function( $acc, $area ) {
			$acc[ $area['area'] ] = array(
				'title' => $area['label'],
			);
			return $acc;
		}, array() );

		$all_templates = $default_template_types + $template_part_templates;

		if ( isset( $all_templates[ $template_name ] ) ) {
			$template_data['post_title'] = $all_templates[ $template_name ]['title'];
			if ( isset( $all_templates[ $template_name ]['description'] ) ) {
				$template_data['post_excerpt'] = $all_templates[ $template_name ]['description'];
			}
		}

		$template_id = wp_insert_post( $template_data );

		if ( is_wp_error( $template_id ) ) {
			return null;
		}

		return $template_id;
	}

	/**
	 * Updates a template post's content.
	 *
	 * @param int    $id      Template ID.
	 * @param string $content Template content.
	 */
	private function update_template_post( $id, $content ) {
		if ( $content && $id ) {
			wp_update_post(
				array(
					'ID'           => $id,
					'post_content' => wp_slash( $content ),
				)
			);
		}
	}

	/**
	 * Builds updated global styles content from style variations.
	 *
	 * @param array $content Existing global styles data.
	 * @return array|null Updated content or null if no changes needed.
	 */
	private function get_updated_global_styles_content( $content ) {
		$selected_palette    = get_theme_mod( 'color_palette' );
		$selected_typography = get_theme_mod( 'typography' );

		if ( empty( $selected_palette ) && empty( $selected_typography ) ) {
			return null;
		}

		if ( ! isset( $content['styles'] ) ) {
			$content['styles'] = array();
		}

		$variations = WP_Theme_JSON_Resolver::get_style_variations();

		if ( $selected_palette ) {
			$variation = current( array_filter( $variations, function( $var ) use ( $selected_palette ) {
				return isset( $var['title'] ) && $var['title'] === $selected_palette;
			} ) );

			if ( isset( $variation['settings']['color'] ) ) {
				$content['settings']['color'] = $variation['settings']['color'];
			}

			if ( isset( $variation['styles'] ) ) {
				$content['styles'] = array_replace_recursive( $content['styles'], $variation['styles'] );
			}
		}

		if ( $selected_typography ) {
			$variation = current( array_filter( $variations, function( $var ) use ( $selected_typography ) {
				return isset( $var['title'] ) && $var['title'] === $selected_typography;
			} ) );

			if ( isset( $variation['settings']['typography']['fontFamilies'] ) ) {
				$content['settings']['typography']['fontFamilies'] = $variation['settings']['typography']['fontFamilies'];
			}

			if ( isset( $variation['styles'] ) ) {
				$content['styles'] = array_replace_recursive( $content['styles'] ?? array(), $variation['styles'] );
			}
		}

		return $content;
	}

	/**
	 * Persists global styles to the database.
	 */
	private function update_global_styles() {
		$user_data = WP_Theme_JSON_Resolver::get_user_data_from_wp_global_styles( wp_get_theme(), true );
		$post_id   = $user_data['ID'] ?? null;

		if ( ! $post_id ) {
			return;
		}

		$content         = json_decode( $user_data['post_content'], true );
		$updated_content = $this->get_updated_global_styles_content( $content );

		if ( $updated_content ) {
			wp_update_post(
				array(
					'ID'           => $post_id,
					'post_content' => wp_slash( wp_json_encode( $updated_content ) ),
				)
			);
		}
	}

	/**
	 * Filters global styles for customizer preview.
	 *
	 * @param WP_Theme_JSON_Data $theme_json Theme JSON data.
	 * @return WP_Theme_JSON_Data Modified theme JSON data.
	 */
	public function set_style_variation( $theme_json ) {
		$existing_data   = $theme_json->get_data();
		$updated_content = $this->get_updated_global_styles_content( $existing_data );

		if ( $updated_content ) {
			$theme_json->update_with( $updated_content );
		}

		return $theme_json;
	}

	/**
	 * Returns a pattern reference block comment.
	 *
	 * @param string $pattern_name Pattern name (without namespace).
	 * @return string Pattern block markup.
	 */
	private function get_selected_pattern( $pattern_name ) {
		return '<!-- wp:pattern {"slug":"twentigone/' . $pattern_name . '"} /-->';
	}

	/**
	 * Gets resolved pattern content from the registry, or falls back to a pattern reference.
	 *
	 * @param string $pattern_name Pattern name (without namespace).
	 * @return string Pattern content or pattern block markup.
	 */
	private function get_pattern_content( $pattern_name ) {
		if ( ! preg_match( '/^[a-z0-9-]+$/', $pattern_name ) ) {
			return '';
		}

		$pattern_name = 'twentigone/' . $pattern_name;
		$registry     = WP_Block_Patterns_Registry::get_instance();

		if ( $registry->is_registered( $pattern_name ) ) {
			$pattern = $registry->get_registered( $pattern_name );
			return $pattern['content'];
		}

		return '<!-- wp:pattern {"slug":"' . $pattern_name . '"} /-->';
	}

	/**
	 * Modifies templates for customizer preview.
	 *
	 * @param array  $templates Array of template objects.
	 * @param object $query     The query object.
	 * @return array Modified templates array.
	 */
	public function modify_templates( $templates, $query ) {
		foreach ( $templates as $key => $template ) {
			switch ( $template->slug ) {
				case 'single':
					$template->content = $this->build_single_post_content();
					$templates[ $key ] = $template;

					break;

				case 'single-portfolio':
					$template->content = $this->build_single_portfolio_content();
					$templates[ $key ] = $template;

					break;
			}
		}

		return $templates;
	}

	/**
	 * Customizes query block output for portfolio and blog pages in preview.
	 *
	 * @param string $block_content The block content.
	 * @param array  $block         The block data.
	 * @return string Modified block content.
	 */
	public function customize_query_block( $block_content, $block ) {
		if ( ( is_page() && ( $block['attrs']['namespace'] ?? '' ) === 'twentig/portfolio-list' )
			|| is_tax( array( 'portfolio_category', 'portfolio_tag' ) )
		) {
			$portfolio_layout  = get_theme_mod( 'portfolio_layout', 'default' );
			$portfolio_content = $this->get_pattern_content( 'portfolio-loop-' . $portfolio_layout );
			if ( is_page() ) {
				$portfolio_content = str_replace( '"inherit":true', '"inherit":false', $portfolio_content );
			}
			remove_filter( 'render_block_core/query', array( $this, 'customize_query_block' ) );
			return do_blocks( $portfolio_content );
		} elseif ( is_home() || is_archive() ) {
			$blog_layout  = get_theme_mod( 'blog_layout', 'default' );
			$blog_content = $this->get_pattern_content( 'post-loop-' . $blog_layout );
			remove_filter( 'render_block_core/query', array( $this, 'customize_query_block' ) );
			return do_blocks( $blog_content );
		}

		return $block_content;
	}

	/**
	 * Builds the navigation content with menu items and header elements.
	 *
	 * @param int $ref Navigation post ID.
	 * @return string|null Navigation content, or null if post not found.
	 */
	private function get_preview_navigation_content( $ref ) {
		if ( ! $ref ) {
			return null;
		}

		$nav_post = get_post( $ref );

		if ( ! $nav_post ) {
			return null;
		}

		return $this->build_navigation_content( $nav_post->post_content );
	}

	/**
	 * Persists navigation changes, including deleting removed pages.
	 *
	 * @param string $header_content Header template content containing the navigation ref.
	 */
	private function save_navigation( $header_content ) {
		$nav_ref = $this->get_navigation_ref( $header_content );

		if ( ! $nav_ref ) {
			return;
		}

		$nav_post = get_post( $nav_ref );

		if ( ! $nav_post ) {
			return;
		}

		$result = $this->build_navigation_content( $nav_post->post_content, true );

		if ( $result !== $nav_post->post_content ) {
			wp_update_post(
				array(
					'ID'           => $nav_ref,
					'post_content' => wp_slash( $result ),
				)
			);
		}
	}

	/**
	 * Filters navigation blocks based on menu settings and appends extras.
	 *
	 * @param string $nav_content  Raw navigation post content.
	 * @param bool   $delete_pages Whether to delete pages removed from the menu.
	 * @return string Processed navigation content.
	 */
	private function build_navigation_content( $nav_content, $delete_pages = false ) {
		$menu_items      = get_theme_mod( 'menu' );
		$header_elements = get_theme_mod( 'header_elements' );
		$header_layout   = get_theme_mod( 'header_layout' );

		if ( is_array( $menu_items ) ) {
			$blocks   = parse_blocks( $nav_content );
			$menu_ids = array_map( 'intval', $menu_items );

			if ( $delete_pages ) {
				$blog_page  = (int) get_option( 'page_for_posts' );
				$front_page = (int) get_option( 'page_on_front' );
			}

			foreach ( $blocks as $key => $block ) {
				if ( $block['blockName'] === 'core/home-link' ) {
					if ( ! in_array( 'home', $menu_items, true ) ) {
						unset( $blocks[ $key ] );
					}
				} elseif ( $block['blockName'] === 'core/navigation-link' ) {
					$id = (int) ( $block['attrs']['id'] ?? 0 );
					if ( $id && ! in_array( $id, $menu_ids, true ) ) {
						if ( $delete_pages && $id !== $blog_page && $id !== $front_page ) {
							$post        = get_post( $id );
							$is_imported = (bool) get_post_meta( $id, '_twentig_website_imported_post', true );
							if ( $post && $is_imported ) {
								wp_delete_post( $id, $post->post_date === $post->post_modified );
							}
						}
						unset( $blocks[ $key ] );
					}
				}
			}

			$nav_content = serialize_blocks( $blocks );
		}

		$more = $this->build_navigation_extras( $header_layout, $header_elements );

		if ( $more ) {
			$nav_content .= "\n\n" . $more;
		}

		return $nav_content;
	}

	/**
	 * Builds extra navigation elements (spacer, social links, search, button).
	 *
	 * @param string     $header_layout   The header layout.
	 * @param array|null $header_elements The selected header elements.
	 * @return string Extra block markup to append to navigation.
	 */
	private function build_navigation_extras( $header_layout, $header_elements ) {
		$more = '';

		if ( 'left-navigation' === $header_layout ) {
			$more .= '<!-- wp:spacer {"className":"tw-sm-hidden","style":{"layout":{"selfStretch":"fill","flexSize":null}}} --><div aria-hidden="true" class="wp-block-spacer tw-sm-hidden"></div><!-- /wp:spacer -->';
		}

		if ( ! empty( $header_elements ) ) {
			if ( in_array( 'social', $header_elements, true ) ) {
				$social_links = $this->get_social_links();
				if ( $social_links ) {
					$more .= '<!-- wp:social-links {"className":"is-style-logos-only header-social"} --><ul class="wp-block-social-links is-style-logos-only header-social">' . $social_links . '</ul><!-- /wp:social-links -->';
				}
			}
			if ( in_array( 'search', $header_elements, true ) ) {
				if ( 'hamburger' === $header_layout ) {
					$more .= '<!-- wp:search {"showLabel":false,"placeholder":"' . esc_html__( 'Search', 'default' ) . '","buttonPosition":"button-inside","buttonUseIcon":true,"className":"is-style-tw-underline"} /-->';
				} else {
					$more .= '<!-- wp:search {"showLabel":false,"placeholder":"' . esc_html__( 'Search', 'default' ) . '","width":220,"widthUnit":"px","buttonPosition":"button-only","buttonUseIcon":true,"isSearchFieldHidden":true,"className":"is-style-tw-underline"} /-->';
				}
			}
			if ( in_array( 'button', $header_elements, true ) ) {
				$more .= '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button">' . esc_html__( 'Get started', 'twentig' ) . '</a></div><!-- /wp:button --></div><!-- /wp:buttons -->';
			}
		}

		return $more;
	}

	/**
	 * Intercepts navigation block rendering for customizer preview.
	 *
	 * @param array $parsed_block The parsed block data.
	 * @return array Modified parsed block.
	 */
	public function set_navigation_attributes( $parsed_block ) {
		if ( 'core/navigation' === $parsed_block['blockName'] ) {
			$ref = $parsed_block['attrs']['ref'] ?? null;
			if ( $ref ) {
				$callback = function ( $fallback ) use ( $ref, &$callback ) {
					remove_filter( 'block_core_navigation_render_fallback', $callback );
					$updated_content = $this->get_preview_navigation_content( $ref );
					if ( $updated_content ) {
						return parse_blocks( $updated_content );
					}
					return array();
				};
				add_filter( 'block_core_navigation_render_fallback', $callback );
				unset( $parsed_block['attrs']['ref'] );
			}
		}

		return $parsed_block;
	}

	/**
	 * Modifies a template part's rendered output for customizer preview.
	 *
	 * @param string $block_content The rendered block content.
	 * @param array  $block         The block data.
	 * @return string Modified block content.
	 */
	public function modify_template_part( $block_content, $block ) {
		$slug = $block['attrs']['slug'] ?? '';

		if ( $slug === 'header' ) {
			return $this->modify_header_template( $block_content, false );
		} elseif ( $slug === 'footer' ) {
			return $this->modify_footer_template( $block_content, false );
		}

		return $block_content;
	}

	/**
	 * Builds header template content with logo, navigation, and style modifications.
	 *
	 * @param string $block_content The block content or pattern content.
	 * @param bool   $raw_content   Whether to return raw block markup (true) or rendered HTML (false).
	 * @return string Modified header content.
	 */
	private function modify_header_template( $block_content, $raw_content = true ) {
		$header_template_posts = get_posts( array(
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
		) );

		$default_header = $header_template_posts[0]->post_content ?? '';

		if ( empty( $default_header ) ) {
			return $block_content;
		}

		$nav_ref         = $this->get_navigation_ref( $default_header );
		$selected_header = get_theme_mod( 'header_layout', 'default' );
		$pattern_content = $selected_header === 'default' ? $default_header : $this->get_pattern_content( 'header-' . $selected_header );

		if ( $pattern_content ) {
			$pattern_content = $this->replace_header_content( $pattern_content, $nav_ref );

			if ( $raw_content ) {
				return $pattern_content;
			}

			$rendered = do_blocks( $pattern_content );
			$result   = preg_replace_callback(
				'/(<header[^>]*>).*?<\/header>/s',
				function ( $matches ) use ( $rendered ) {
					return $matches[1] . $rendered . '</header>';
				},
				$block_content
			);
			if ( null !== $result ) {
				$block_content = $result;
			}
		}

		return $block_content;
	}

	/**
	 * Applies logo, navigation ref, width, position, and style replacements to header content.
	 *
	 * @param string   $content The header block markup.
	 * @param int|null $nav_ref Navigation post ID.
	 * @return string Modified header content.
	 */
	private function replace_header_content( $content, $nav_ref = null ) {
		if ( has_custom_logo() ) {
			$logo_width = absint( get_theme_mod( 'logo_width', 120 ) );
			if ( ! str_contains( $content, 'wp:site-logo' ) ) {
				$content = str_replace(
					'<!-- wp:site-title {"level":0} /-->',
					'<!-- wp:site-logo {"width":' . $logo_width . '} /-->',
					$content
				);
			} else {
				$content = preg_replace(
					'/<!-- wp:site-logo\b[^>]*?\s*\/-->/',
					'<!-- wp:site-logo {"width":' . $logo_width . '} /-->',
					$content,
					1
				);
			}
		} elseif ( str_contains( $content, 'wp:site-logo' ) ) {
			$content = preg_replace(
				'/<!-- wp:site-logo\b[^>]*?\s*\/-->/',
				'<!-- wp:site-title {"level":0} /-->',
				$content,
				1
			);
		}

		if ( $nav_ref && ! str_contains( $content, 'wp:navigation {"ref"' ) ) {
			$content = str_replace(
				'<!-- wp:navigation {',
				'<!-- wp:navigation {"ref":' . (int) $nav_ref . ',',
				$content
			);
		}

		$header_width    = get_theme_mod( 'header_width', 'full' );
		$header_style    = get_theme_mod( 'header_style', 'default' );
		$header_position = get_theme_mod( 'header_position', 'sticky' );

		if ( $header_position !== 'sticky' ) {
			$replacements = array();
			switch ( $header_position ) {
				case 'static':
					$replacements = array(
						'<!-- wp:group {"style":{"position":{"type":"sticky","top":"0px"},' => '<!-- wp:group {"style":{',
					);
					break;

				case 'sticky-scroll-up':
					$replacements = array(
						'"backgroundColor":"base"'                 => '"backgroundColor":"base","className":"tw-sticky-scroll-up"',
						'has-base-background-color has-background' => 'tw-sticky-scroll-up has-base-background-color has-background',
					);
					break;
			}
			foreach ( $replacements as $search => $replace ) {
				$content = $this->replace_first_occurrence( $search, $replace, $content );
			}
		}

		if ( 'wide' === $header_width ) {
			$content = $this->replace_first_occurrence(
				'"backgroundColor":"base"',
				'"backgroundColor":"base","layout":{"type":"constrained"}',
				$content
			);
		}

		if ( $header_style !== 'default' ) {
			$replacements = array();
			switch ( $header_style ) {
				case 'subtle':
					$replacements = array(
						'"backgroundColor":"base"'                                      => '"backgroundColor":"base-2"',
						'has-base-background-color has-background'                      => 'has-base-2-background-color has-background',
						'"overlayBackgroundColor":"base","overlayTextColor":"contrast"' => '"overlayBackgroundColor":"base-2","overlayTextColor":"contrast"',
					);
					break;

				case 'inverse':
					$replacements = array(
						'"backgroundColor":"base"'                                      => '"backgroundColor":"contrast","textColor":"base"',
						'has-base-background-color has-background'                      => 'has-base-color has-contrast-background-color has-text-color has-background',
						'"overlayBackgroundColor":"base","overlayTextColor":"contrast"' => '"overlayBackgroundColor":"contrast","overlayTextColor":"base"',
					);
					break;

				case 'border':
					$replacements = array(
						'<!-- wp:group {"style":{'                          => '<!-- wp:group {"style":{"border":{"bottom":{"color":"var:preset|color|tertiary","width":"1px"}},',
						'has-base-background-color has-background" style="' => 'has-base-background-color has-background" style="border-bottom-color:var(--wp--preset--color--tertiary);border-bottom-width:1px;',
					);
					break;
			}

			foreach ( $replacements as $search => $replace ) {
				$content = $this->replace_first_occurrence( $search, $replace, $content );
			}
		}

		return $content;
	}

	/**
	 * Extracts the navigation block ref ID from template content.
	 *
	 * @param string $content Block markup containing a navigation block.
	 * @return int|null Navigation post ID or null.
	 */
	public static function get_navigation_ref( $content ) {
		$regex = '/<!-- wp:navigation\s+{[^}]*"ref":\s*(\d+)/';

		if ( preg_match( $regex, $content, $matches ) ) {
			return (int) $matches[1];
		}

		return null;
	}

	/**
	 * Builds footer template content with style and social link modifications.
	 *
	 * @param string $block_content The block content or pattern content.
	 * @param bool   $raw_content   Whether to return raw block markup (true) or rendered HTML (false).
	 * @return string Modified footer content.
	 */
	private function modify_footer_template( $block_content, $raw_content = true ) {
		$selected_footer = get_theme_mod( 'footer_layout', 'default' );
		$pattern_content = $this->get_pattern_content( 'footer-' . $selected_footer );
		$pattern_content = $this->replace_footer_content( $pattern_content );

		if ( $raw_content ) {
			return $pattern_content;
		}

		$rendered = do_blocks( $pattern_content );
		$result   = preg_replace_callback(
			'/(<footer[^>]*>).*?<\/footer>/s',
			function ( $matches ) use ( $rendered ) {
				return $matches[1] . $rendered . '</footer>';
			},
			$block_content
		);
		if ( null !== $result ) {
			$block_content = $result;
		}

		return $block_content;
	}

	/**
	 * Applies width, style, and social link replacements to footer content.
	 *
	 * @param string $content The footer block markup.
	 * @return string Modified footer content.
	 */
	private function replace_footer_content( $content ) {
		$footer_style = get_theme_mod( 'footer_style', 'default' );
		$footer_width = get_theme_mod( 'footer_width', 'full' );

		if ( 'wide' === $footer_width ) {
			$content = $this->replace_first_occurrence(
				'"backgroundColor":"base"',
				'"backgroundColor":"base","layout":{"type":"constrained"}',
				$content
			);
		}

		if ( $footer_style !== 'default' ) {
			$replacements = array();

			switch ( $footer_style ) {
				case 'subtle':
					$replacements = array(
						'"backgroundColor":"base"'                 => '"backgroundColor":"base-2"',
						'has-base-background-color has-background' => 'has-base-2-background-color has-background',
					);
					break;

				case 'inverse':
					$replacements = array(
						'"backgroundColor":"base"'                 => '"backgroundColor":"contrast","textColor":"base"',
						'has-base-background-color has-background' => 'has-base-color has-contrast-background-color has-text-color has-background',
					);
					break;

				case 'border':
					$replacements = array(
						'<!-- wp:group {"style":{'                          => '<!-- wp:group {"style":{"border":{"top":{"color":"var:preset|color|tertiary","width":"1px"}},',
						'has-base-background-color has-background" style="' => 'has-base-background-color has-background" style="border-top-color:var(--wp--preset--color--tertiary);border-top-width:1px;',
					);
					break;
			}

			foreach ( $replacements as $search => $replace ) {
				$content = $this->replace_first_occurrence( $search, $replace, $content );
			}
		}

		$result = preg_replace_callback(
			'/© \d{4}\./',
			function () {
				return sprintf( '© %s %s.', esc_html( gmdate( 'Y' ) ), esc_html( get_bloginfo( 'name' ) ) );
			},
			$content
		);
		if ( null !== $result ) {
			$content = $result;
		}

		if ( str_contains( $content, 'wp:social-links' ) ) {
			$social_links = $this->get_social_links();
			$regex  = '/(<ul\s+class="wp-block-social-links[^"]*">).*?(<\/ul>)/s';
			$result       = preg_replace_callback(
				$regex,
				function ( $matches ) use ( $social_links ) {
					return $matches[1] . $social_links . $matches[2];
				},
				$content
			);
			if ( null !== $result ) {
				$content = $result;
			}
		}

		return $content;
	}

	/**
	 * Builds social link blocks from theme mod values.
	 *
	 * @return string Social link block markup.
	 */
	public static function get_social_links() {
		$instagram    = get_theme_mod( 'social_instagram', '#' );
		$linkedin     = get_theme_mod( 'social_linkedin', '#' );
		$x            = get_theme_mod( 'social_x' );
		$facebook     = get_theme_mod( 'social_facebook' );
		$youtube      = get_theme_mod( 'social_youtube' );
		$mail         = get_theme_mod( 'social_mail' );
		$social_links = '';

		if ( $instagram ) {
			$social_links .= '<!-- wp:social-link {"url":"' . esc_url( $instagram ) . '","service":"instagram"} /-->';
		}
		if ( $linkedin ) {
			$social_links .= '<!-- wp:social-link {"url":"' . esc_url( $linkedin ) . '","service":"linkedin"} /-->';
		}
		if ( $x ) {
			$social_links .= '<!-- wp:social-link {"url":"' . esc_url( $x ) . '","service":"x"} /-->';
		}
		if ( $facebook ) {
			$social_links .= '<!-- wp:social-link {"url":"' . esc_url( $facebook ) . '","service":"facebook"} /-->';
		}
		if ( $youtube ) {
			$social_links .= '<!-- wp:social-link {"url":"' . esc_url( $youtube ) . '","service":"youtube"} /-->';
		}
		if ( $mail ) {
			$social_links .= '<!-- wp:social-link {"url":"' . sanitize_email( $mail ) . '","service":"mail"} /-->';
		}

		return $social_links;
	}

	/**
	 * Filters site title/home link blocks to ensure trailing slash in URL.
	 *
	 * @param string $block_content The block content.
	 * @param array  $block         The block attributes.
	 * @return string Modified block content.
	 */
	public function filter_home_url( $block_content, $block ) {
		if ( empty( $block_content ) ) {
			return $block_content;
		}

		$processor = new WP_HTML_Tag_Processor( $block_content );

		if ( $processor->next_tag( 'a' ) ) {
			$processor->set_attribute( 'href', home_url( '/' ) );
		}

		return $processor->get_updated_html();
	}

	/**
	 * Replaces the first occurrence of a string within another string.
	 *
	 * @param string $needle      The string to search for.
	 * @param string $replacement The replacement string.
	 * @param string $haystack    The string to search in.
	 * @return string The modified string.
	 */
	private function replace_first_occurrence( $needle, $replacement, $haystack ) {
		if ( $needle === '' ) {
			return $haystack;
		}

		$pos = strpos( $haystack, $needle );

		if ( $pos === false ) {
			return $haystack;
		}

		return substr_replace( $haystack, $replacement, $pos, strlen( $needle ) );
	}

}
