<?php
/**
 * Customize Radio Control class.
 *
 * @package twentig
 */

if ( class_exists( 'WP_Customize_Control' ) ) {
	/**
	 * Radio control with visual selector.
	 */
	class Twentig_Customizer_Radio_Control extends WP_Customize_Control {
		/**
		 * Type.
		 *
		 * @var string
		 */
		public $type = 'radios-selector';

		/**
		 * Render the content of the radio control.
		 */
		public function render_content() {
			if ( empty( $this->choices ) ) {
				return;
			}
			?>
			<span class="customize-control-title screen-reader-text"><?php echo esc_html( $this->label ); ?></span>
				<?php if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php endif; ?>
			<ul>
			<?php foreach ( $this->choices as $value => $label ) : ?>
				<li>
					<label>
						<input class="radio-selector screen-reader-text" type="radio" value="<?php echo esc_attr( $value ); ?>" name="_customize-radio-<?php echo esc_attr( $this->id ); ?>" <?php $this->link(); checked( $this->value(), $value ); ?> />
						<div class="radio-text-container"><?php echo esc_html( $label ); ?></div>
					</label>
				</li>
			<?php endforeach; ?>
			</ul>
			<?php
		}
	}
}