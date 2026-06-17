<?php
/**
 * Color Palette Control Class.
 *
 * @package Twentig
 */

if ( class_exists( 'WP_Customize_Control' ) ) {
	/**
	 * Color Palette Control class for theme style variations.
	 */
	class Twentig_Customizer_Color_Palette_Control extends WP_Customize_Control {
		/**
		 * Control type.
		 *
		 * @var string
		 */
		public $type = 'twentig_color_palette';

		/**
		 * Constructor.
		 *
		 * @param WP_Customize_Manager $manager Customizer bootstrap instance.
		 * @param string               $id      Control ID.
		 * @param array                $args    Optional. Array of properties for the new Control object.
		 */
		public function __construct( $manager, $id, $args = array() ) {
			parent::__construct( $manager, $id, $args );

			// Build choices for sanitize_select validation.
			$this->choices = array( '' => __( 'Default', 'twentig' ) );
			$variations    = $this->get_variation_colors();
			foreach ( $variations as $variation ) {
				$this->choices[ $variation['title'] ] = $variation['title'];
			}
		}

		/**
		 * Renders a color swatch.
		 *
		 * @param array $color Color data with name and color values.
		 */
		private function render_color_swatch( $color ) {
			if ( empty( $color['color'] ) ) {
				return;
			}
			?>
			<span class="color-swatch" style="background-color: <?php echo esc_attr( $color['color'] ); ?>"></span>
			<?php
		}

		/**
		 * Gets color palette from variations.
		 *
		 * @return array Array of variations with their colors.
		 */
		private function get_variation_colors() {
			$variations = WP_Theme_JSON_Resolver::get_style_variations();
			$color_variations = array();

			foreach ( $variations as $variation ) {
				if ( isset( $variation['settings']['color']['palette']['theme'] ) && isset( $variation['title'] ) ) {
					$color_variations[] = array(
						'title'  => $variation['title'],
						'id'     => sanitize_title( $variation['title'] ),
						'colors' => $variation['settings']['color']['palette']['theme'],
					);
				}
			}

			return $color_variations;
		}

		/**
		 * Renders a single palette option.
		 *
		 * @param string $id      Option ID.
		 * @param string $value   Option value.
		 * @param string $title   Option title.
		 * @param array  $colors  Array of colors for the palette.
		 */
		private function render_palette_option( $id, $value, $title, $colors ) {
			// Limit to 4 colors for display.
			$colors = array_slice( $colors, 0, 4 );

			if ( empty( $colors ) ) {
				return;
			}

			$id = $this->id . '-' . $id;
			?>
			<div class="twentig-palette-option">
				<input type="radio"
					id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( $this->id ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					<?php $this->link(); ?> 
				/>
				<label for="<?php echo esc_attr( $id ); ?>">
					<span class="screen-reader-text"><?php echo esc_html( $title ); ?></span>
					<div class="palette-colors" title="<?php echo esc_attr( $title ); ?>">
						<?php 
						foreach ( $colors as $color ) {
							$this->render_color_swatch( $color );
						}
						?>
					</div>
				</label>
			</div>
			<?php
		}

		/**
		 * Renders the control content.
		 */
		public function render_content() {
			?>
			<div class="customize-control-content">
				<?php if ( ! empty( $this->label ) ) : ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif; ?>

				<div class="twentig-color-palettes">
					<?php
					// Render default palette.
					$theme_data = WP_Theme_JSON_Resolver::get_theme_data()->get_data();
					if ( isset( $theme_data['settings']['color']['palette'] ) ) {
						$this->render_palette_option(
							'default',
							'',
							__( 'Default', 'twentig' ),
							$theme_data['settings']['color']['palette']
						);
					}

					// Render variation palettes.
					$variations = $this->get_variation_colors();
					foreach ( $variations as $variation ) {
						$this->render_palette_option(
							$variation['id'],
							$variation['title'],
							$variation['title'],
							$variation['colors']
						);
					}
					?>
				</div>
				<div class="twentig-paste-palette-panel is-collapsed">
					<button
						type="button"
						class="twentig-paste-palette-toggle"
						aria-expanded="false"
						aria-controls="twentig-palette-actions-color_palette"
					>
						<span><?php esc_html_e( 'More palettes', 'twentig' ); ?></span>
						<svg class="twentig-panel-toggle-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M17.5 11.6L12 16l-5.5-4.4.9-1.2L12 14l4.5-3.6 1 1.2z"></path></svg>
					</button>
					<div id="twentig-palette-actions-color_palette" class="twentig-palette-actions">
						<button
							type="button"
							class="button twentig-paste-palette-button"
						>
							<?php esc_html_e( 'Paste color palette', 'twentig' ); ?>
						</button>
						<a
							class="twentig-browse-palettes-link"
							href="<?php echo esc_url( 'https://twentig.com/wordpress-color-palettes/?utm_source=twentig-plugin&utm_medium=customizer&utm_campaign=color-palette' ); ?>"
							target="_blank"
							rel="noopener noreferrer"
						>
							<span><?php esc_html_e( 'Browse the Twentig palette library', 'twentig' ); ?></span>
							<svg class="twentig-external-link-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
								<path d="M19.5 4.5h-7V6h4.44l-5.97 5.97 1.06 1.06L18 7.06v4.44h1.5v-7Zm-13 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3H17v3a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h3V5.5h-3Z"/>
							</svg>
						</a>
						<p class="description twentig-paste-palette-feedback" aria-live="polite"></p>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
