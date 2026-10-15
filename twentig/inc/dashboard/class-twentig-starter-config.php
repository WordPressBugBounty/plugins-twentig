<?php
/**
 * Manages theme-scoped Customizer configuration for imported starter sites.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

class Twentig_Starter_Config {

	const OPTION = 'twentig-starter-config';

	/** Saves a theme-scoped, validated starter configuration after import. */
	public static function save( $config, $starter ) {
		$type = $starter['type'] ?? '';
		if ( ! is_string( $type ) ) {
			return self::error();
		}
		$type = sanitize_key( $type );
		if ( '' === $type ) {
			self::clear();
			return true;
		}

		$has_portfolio = wp_validate_boolean( $starter['options']['portfolio'] ?? false );
		if ( ! is_array( $config ) ) {
			return self::error();
		}
		$presets = $config['presets'] ?? array();
		if ( ! is_array( $presets ) || ( $config && ! isset( $presets['default'] ) ) ) {
			return self::error();
		}
		$title = $config['title'] ?? $starter['title'] ?? '';
		if ( ! is_string( $title ) ) {
			return self::error();
		}
		$result = array(
			'theme'     => get_stylesheet(),
			'type'      => $type,
			'title'     => sanitize_text_field( $title ),
			'portfolio' => $has_portfolio,
			'presets'   => array(),
		);
		foreach ( $presets as $key => $settings ) {
			if ( sanitize_key( $key ) !== $key || ! is_array( $settings ) ) {
				return self::error();
			}
			$validated = self::validate_preset_settings( $settings );
			if ( false === $validated ) {
				return self::error();
			}
			$result['presets'][ $key ] = $validated;
		}
		update_option( self::OPTION, $result, false );
		return true;
	}

	/** Clears the current starter configuration. */
	public static function clear() {
		delete_option( self::OPTION );
	}

	/**
	 * Validates and sanitizes options from uploaded XML before importing content.
	 *
	 * @param array $options Options from the XML file.
	 * @return array|WP_Error Validated options or an error.
	 */
	public static function validate_uploaded_options( $options ) {
		if ( ! is_array( $options ) ) {
			return self::error();
		}

		$result = array();
		foreach ( array( 'title', 'front_page', 'blog_page' ) as $key ) {
			if ( array_key_exists( $key, $options ) ) {
				if ( ! is_string( $options[ $key ] ) ) {
					return self::error();
				}
				$result[ $key ] = sanitize_text_field( $options[ $key ] );
			}
		}
		foreach ( array( 'portfolio', 'spacing' ) as $key ) {
			if ( array_key_exists( $key, $options ) ) {
				if ( ! is_scalar( $options[ $key ] ) ) {
					return self::error();
				}
				$result[ $key ] = wp_validate_boolean( $options[ $key ] );
			}
		}
		if ( array_key_exists( 'posts_per_page', $options ) ) {
			$posts_per_page = $options['posts_per_page'];
			if ( ! is_int( $posts_per_page ) || ( 1 > $posts_per_page && -1 !== $posts_per_page ) ) {
				return self::error();
			}
			$result['posts_per_page'] = $posts_per_page;
		}
		if ( array_key_exists( 'theme_mods', $options ) ) {
			$result['theme_mods'] = self::validate_theme_mods( $options['theme_mods'] );
			if ( false === $result['theme_mods'] ) {
				return self::error();
			}
		}
		if ( array_key_exists( 'presets', $options ) ) {
			$presets = $options['presets'];
			if ( ! is_array( $presets ) || ! isset( $presets['default'] ) ) {
				return self::error();
			}
			$result['presets'] = array();
			foreach ( $presets as $key => $settings ) {
				if ( sanitize_key( $key ) !== $key || ! is_array( $settings ) ) {
					return self::error();
				}
				$validated = self::validate_preset_settings( $settings, true );
				if ( false === $validated ) {
					return self::error();
				}
				$result['presets'][ $key ] = $validated;
			}
		}

		return $result;
	}

	/**
	 * Saves uploaded XML starter configuration after resolving homepage IDs.
	 *
	 * @param array $options       Validated starter options from the XML file.
	 * @param array $processed_ids Map of XML post IDs to imported post IDs.
	 * @return true|WP_Error True on success, or an error for invalid presets.
	 */
	public static function save_from_upload( $options, $processed_ids ) {
		$starter = array(
			'title'   => $options['title'] ?? '',
			'type'    => 'custom',
			'options' => $options,
		);
		if ( ! array_key_exists( 'presets', $options ) ) {
			return self::save( array(), $starter );
		}
		$config = array(
			'presets' => $options['presets'],
		);

		foreach ( $config['presets'] as $key => $preset ) {
			if ( ! isset( $preset['home_layout'] ) ) {
				continue;
			}

			$source_id = $preset['home_layout'];
			$page_id   = $processed_ids[ $source_id ] ?? 0;
			if ( ! $page_id || 'page' !== get_post_type( $page_id ) ) {
				$page_id = 'page' === get_option( 'show_on_front' ) ? (int) get_option( 'page_on_front' ) : 0;
				if ( ! $page_id || 'page' !== get_post_type( $page_id ) ) {
					unset( $config['presets'][ $key ]['home_layout'] );
					continue;
				}
			}
			$config['presets'][ $key ]['home_layout'] = (int) $page_id;
		}

		return self::save( $config, $starter );
	}

	/** Accepts only Twentig Customizer settings, including layout choices. */
	private static function validate_preset_settings( $settings, $source_ids = false ) {
		$result = array();
		foreach ( $settings as $key => $value ) {
			if ( ! in_array( $key, TwentigDashboard::get_theme_mod_keys(), true ) || in_array( $key, array( 'starter_presets', 'menu' ), true ) ) {
				return false;
			}
			if ( 'home_layout' === $key ) {
				if ( ! is_int( $value ) || 0 >= $value || ( ! $source_ids && 'page' !== get_post_type( $value ) ) ) {
					return false;
				}
				$result[ $key ] = $value;
			} elseif ( 'header_elements' === $key ) {
				if ( ! is_array( $value ) || count( array_filter( $value, 'is_scalar' ) ) !== count( $value ) ) {
					return false;
				}
				$result[ $key ] = array_values( array_map( 'sanitize_text_field', $value ) );
			} elseif ( is_scalar( $value ) ) {
				$result[ $key ] = self::sanitize_scalar_setting( $key, $value );
			} else {
				return false;
			}
		}
		return $result;
	}

	/** Validates theme mods supplied with uploaded XML options. */
	private static function validate_theme_mods( $settings ) {
		if ( ! is_array( $settings ) ) {
			return false;
		}
		$result = array();
		foreach ( $settings as $key => $value ) {
			if ( ! in_array( $key, TwentigDashboard::get_theme_mod_keys(), true ) ) {
				continue;
			}
			if ( in_array( $key, array( 'header_elements', 'menu' ), true ) ) {
				if ( ! is_array( $value ) || count( array_filter( $value, 'is_scalar' ) ) !== count( $value ) ) {
					return false;
				}
				$result[ $key ] = array_values( array_map( 'sanitize_text_field', $value ) );
				if ( 'menu' === $key ) {
					$result[ $key ] = array_map( static function ( $item ) {
						return 'home' === $item ? 'home' : absint( $item );
					}, $result[ $key ] );
				}
			} elseif ( is_scalar( $value ) ) {
				$result[ $key ] = self::sanitize_scalar_setting( $key, $value );
			} else {
				return false;
			}
		}
		return $result;
	}

	/** Sanitizes a scalar Customizer setting according to its type. */
	private static function sanitize_scalar_setting( $key, $value ) {
		if ( 'social_mail' === $key ) {
			return sanitize_email( (string) $value );
		}
		if ( 0 === strpos( $key, 'social_' ) ) {
			return '#' === $value ? '#' : esc_url_raw( (string) $value );
		}
		return sanitize_text_field( (string) $value );
	}

	/** Returns only configuration belonging to the active stylesheet. */
	public static function get() {
		$config = get_option( self::OPTION, array() );
		return is_array( $config ) && ( $config['theme'] ?? '' ) === get_stylesheet() ? $config : array();
	}

	/** Returns the active theme's starter type. */
	public static function get_type() {
		$config = self::get();
		return $config['type'] ?? '';
	}

	/** Returns whether the imported starter supports Portfolio layouts. */
	public static function supports_portfolio() {
		$config = self::get();
		return ! empty( $config['portfolio'] ) || 'portfolio' === self::get_type();
	}

	/** Adapts stored PHP values to the existing Customizer setting format. */
	public static function presets( $latin ) {
		$config       = self::get();
		$presets      = $config['presets'] ?? array();
		$theme_domain = wp_get_theme()->get( 'TextDomain' );

		foreach ( $presets as &$preset ) {
			if ( ! empty( $preset['color_palette'] ) ) {
				// Look up the theme's translation of its dynamic style variation name.
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText, WordPress.WP.I18n.NonSingularStringLiteralDomain
				$preset['color_palette'] = _x( $preset['color_palette'], 'Style variation name', $theme_domain );
			}
			if ( ! $latin && isset( $preset['typography'] ) ) {
				$preset['typography'] = '';
			}
		}
		unset( $preset );
		return $presets;
	}

	private static function error() {
		return new WP_Error( 'invalid_starter_config', __( 'The starter configuration is invalid.', 'twentig' ) );
	}
}
