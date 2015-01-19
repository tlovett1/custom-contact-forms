<?php

class CCF_Form_Renderer {

	/**
	 * Placeholder method
	 *
	 * @since 6.0
	 */
	public function __construct() {}

	/**
	 * Setup shortcode
	 *
	 * @since 6.0
	 */
	public function setup() {
		add_shortcode( 'ccf_form', array( $this, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'action_wp_enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts for form
	 *
	 * @todo only enqueue when a form is present
	 * @since 6.0
	 */
	public function action_wp_enqueue_scripts() {
		if ( is_single() ) {
			global $post;

			if ( ! preg_match( '#\[ccf_form id="[0-9]+"\]#i', $post->post_content ) ) {
				return;
			}
		}

		if ( ! defined( WP_DEBUG ) || ! WP_DEBUG ) {
			$css_form_path = '/build/css/form.css';
			$js_path = '/js/form.js';
		} else {
			$css_form_path = '/build/css/form.min.css';
			$js_path = '/build/css/form.min.js';
		}

		wp_enqueue_style('ccf-jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		wp_enqueue_script( 'ccf-google-recaptcha', '//www.google.com/recaptcha/api.js?onload=ccfRecaptchaOnload&render=explicit' );
		wp_enqueue_style( 'ccf-form', plugins_url( $css_form_path, dirname( __FILE__ ) ) );

		wp_enqueue_script( 'ccf-form', plugins_url( $js_path, dirname( __FILE__ ) ), array( 'jquery-ui-datepicker', 'underscore', 'ccf-google-recaptcha' ), '1.1', false );

		$localized = array(
			'ajaxurl' => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
			'required' => esc_html__( 'This field is required.', 'custom-contact-forms' ),
			'date_required' => esc_html__( 'Date is required.', 'custom-contact-forms' ),
			'hour_required' => esc_html__( 'Hour is required.', 'custom-contact-forms' ),
			'minute_required' => esc_html__( 'Minute is required.', 'custom-contact-forms' ),
			'am-pm_required' => esc_html__( 'AM/PM is required.', 'custom-contact-forms' ),
			'match' => esc_html__( 'Emails do not match.', 'custom-contact-forms' ),
			'email' => esc_html__( 'This is not a valid email address.', 'custom-contact-forms' ),
			'recaptcha' => esc_html__( 'Your reCAPTCHA response was incorrect.', 'custom-contact-forms' ),
			'recaptcha_theme' => apply_filters( 'ccf_recaptcha_theme', 'light' ),
			'phone' => esc_html__( 'This is not a valid phone number.', 'custom-contact-forms' ),
			'digits' => esc_html__( 'This phone number is not 10 digits', 'custom-contact-forms' ),
			'hour' => esc_html__( 'This is not a valid hour.', 'custom-contact-forms' ),
			'date' => esc_html__( 'This date is not valid.', 'custom-contact-forms' ),
			'minute' => esc_html__( 'This is not a valid minute.', 'custom-contact-forms' ),
			'website' => esc_html__( "This is not a valid URL. URL's must start with http(s)://", 'custom-contact-forms' ),
		);
		wp_localize_script( 'ccf-form', 'ccfSettings', $localized );
	}

	/**
	 * Output form shortcode
	 *
	 * @param array $atts
	 * @since 6.0
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'id' => null,
		), $atts );

		if ( 'null' === $atts['id'] ) {
			return '';
		}

		return $this->get_rendered_form( $atts['id'] );
	}

	/**
	 * Return form HTML for a given form ID
	 *
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function get_rendered_form( $form_id ) {
		$form = get_post( (int) $form_id );

		if ( ! $form ) {
			return '';
		}

		$fields = get_post_meta( $form_id, 'ccf_attached_fields', true );

		if ( empty( $fields ) ) {
			return '';
		}

		ob_start();

		if ( ! empty( $_POST['ccf_form'] ) && ! empty( $_POST['form_id'] ) && $_POST['form_id'] == $form_id && empty( CCF_Form_Handler::factory()->errors_by_form[$form_id] ) ) {

			$completion_message = get_post_meta( $form_id, 'ccf_form_completion_message', true );
			?>

			<div class="ccf-form-complete form-id-<?php echo (int) $form_id; ?>">
				<?php if ( empty( $completion_message ) ) : ?>
					<?php esc_html_e( 'Thank you for your submission.', 'custom-contact-forms' ); ?>
				<?php else : ?>
					<?php echo esc_html( $completion_message ); ?>
				<?php endif; ?>
			</div>

			<?php
		} else {
			?>

			<div class="ccf-form-wrapper form-id-<?php echo (int) $form_id; ?>" data-form-id="<?php echo (int) $form_id; ?>">
				<form class="ccf-form" method="post" action="" data-form-id="<?php echo (int) $form_id; ?>">

					<?php $title = get_the_title( $form_id ); if ( ! empty( $title ) && apply_filters( 'ccf_show_form_title', true, $form_id ) ) : ?>
						<div class="form-title">
							<?php echo esc_html( $title ); ?>
						</div>
					<?php endif; ?>

					<?php $description = get_post_meta( $form_id, 'ccf_form_description', true ); if ( ! empty( $description ) && apply_filters( 'ccf_show_form_description', true, $form_id ) ) : ?>
						<div class="form-description">
							<?php echo esc_html( $description ); ?>
						</div>
					<?php endif; ?>

					<?php

					foreach ( $fields as $field_id ) {
						$field_id = (int) $field_id;

						$type = esc_attr( get_post_meta( $field_id, 'ccf_field_type', true ) );

						$field_html = apply_filters( 'ccf_field_html', CCF_Field_Renderer::factory()->render_router( $type, $field_id, $form_id ), $type, $field_id );

						echo $field_html;
					}

					?>
					<div class="form-submit">
						<input type="submit" class="ccf-submit-button" value="<?php echo esc_attr( get_post_meta( $form_id, 'ccf_form_buttonText', true ) ); ?>">
						<img class="loading-img" src="<?php echo esc_url( site_url( '/wp-admin/images/wpspin_light.gif' ) ); ?>">
					</div>

					<input type="hidden" name="form_id" value="<?php echo (int) $form_id; ?>">
					<input type="hidden" name="form_page" value="<?php echo esc_url( untrailingslashit( site_url() ) . $_SERVER['REQUEST_URI'] ); ?>">
					<input type="text" name="my_information" style="display: none;">
					<input type="hidden"  name="ccf_form" value="1">
					<input type="hidden" name="form_nonce" value="<?php echo wp_create_nonce( 'ccf_form' ); ?>">
				</form>
			</div>

			<?php
		}

		$form_html = ob_get_clean();

		return $form_html;
	}

	/**
	 * Return singleton instance of class
	 *
	 * @since 6.0
	 * @return object
	 */
	public static function factory() {
		static $instance;

		if ( ! $instance ) {
			$instance = new self();
			$instance->setup();
		}

		return $instance;
	}
}