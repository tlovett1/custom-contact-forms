<?php

class CCF_Form_Renderer {

	/**
	 * Placeholder method
	 *
	 * @since 6.0
	 */
	public function __construct() {}

	/**
	 * Remember if we arent showing assets
	 *
	 * @since 7.2
	 */
	public $no_assets = false;

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
	 * @since 6.0
	 */
	public function action_wp_enqueue_scripts() {
		$option = get_option( 'ccf_settings' );

		// Conditionally load assets
		if ( ! empty( $option ) && ! empty( $option['asset_loading_restriction_enabled'] ) ) {
			if ( empty( $option['asset_loading_restrictions'] ) ) {
				return;
			}

			$post_id = null;
			$current_path = $_SERVER['REQUEST_URI'];

			$queried_object = get_queried_object();
			if ( ! empty( $queried_object->ID ) ) {
				$post_id = $queried_object->ID;
			}

			$match = false;

			foreach ( $option['asset_loading_restrictions'] as $asset ) {
				if ( ! empty( $asset['location'] ) ) {
					if ( 'post_id' === $asset['type'] ) {
						if ( (int) $asset['location'] === $post_id ) {
							$match = true;
							break;
						}
					} else {
						$asset_url_parts = parse_url( $asset['location'] );
						if ( ! empty( $asset_url_parts['path'] ) ) {
							$asset_path = trailingslashit( $asset_url_parts['path'] );
							if ( ! preg_match( '#^/#', $asset_path ) ) {
								$asset_path = '/' . $asset_path;
							}

							if ( $asset_path === $current_path ) {
								$match = true;
								break;
							}
						}
					}
				}
			}

			if ( ! $match ) {
				$this->no_assets = true;
				return;
			}
		}

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$css_form_path = '/assets/build/css/form.css';
			$js_path = '/assets/js/form.js';
		} else {
			$css_form_path = '/assets/build/css/form.min.css';
			$js_path = '/assets/build/js/form.min.js';
		}

		wp_enqueue_style( 'ccf-jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
		wp_enqueue_script( 'ccf-google-recaptcha', '//www.google.com/recaptcha/api.js?ver=2&onload=ccfRecaptchaOnload&render=explicit', array(), CCF_VERSION, true );
		wp_enqueue_style( 'ccf-form', plugins_url( $css_form_path, dirname( __FILE__ ) ), array(), CCF_VERSION );

		wp_enqueue_script( 'ccf-form', plugins_url( $js_path, dirname( __FILE__ ) ), array( 'jquery-ui-datepicker', 'underscore' ), CCF_VERSION, false );

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
			'fileExtension' => esc_html__( 'This is not an allowed file extension', 'custom-contact-forms' ),
			'fileSize' => esc_html__( 'This file is bigger than', 'custom-contact-forms' ),
			'unknown' => esc_html__( 'An unknown error occured.', 'custom-contact-forms' ),
			'website' => esc_html__( "This is not a valid URL. URL's must start with http(s)://", 'custom-contact-forms' ),
		);
		wp_localize_script( 'ccf-form', 'ccfSettings', apply_filters( 'ccf_localized_form_messages', $localized ) );
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

		if ( ! $this->no_assets ) {
			return $this->get_rendered_form( $atts['id'] );
		}

		return '';
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

		$pause = get_post_meta( $form_id, 'ccf_form_pause', true );

		if ( empty( $fields ) ) {
			return '';
		}

		ob_start();

		if ( ! empty( $pause ) ) {
			$pause_message = get_post_meta( $form_id, 'ccf_form_pause_message', true );
			?>

			<div class="ccf-form-pause form-id-<?php echo (int) $form_id; ?>">
				<?php if ( empty( $pause_message ) ) : ?>
					<?php esc_html_e( 'This form is paused right now. Check back later!', 'custom-contact-forms' ); ?>
				<?php else : ?>
					<?php echo esc_html( $pause_message ); ?>
				<?php endif; ?>
			</div>

			<?php
		} elseif ( ! empty( $_POST['ccf_form'] ) && ! empty( $_POST['form_id'] ) && $_POST['form_id'] == $form_id && empty( CCF_Form_Handler::factory()->errors_by_form[ $form_id ] ) ) {

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
			$contains_file = false;

			$fields_html = '';

			$conditionals = array();

			foreach ( $fields as $field_id ) {
				$field_id = (int) $field_id;

				$type = esc_attr( get_post_meta( $field_id, 'ccf_field_type', true ) );
				$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
				$conditionals_enabled = get_post_meta( $field_id, 'ccf_field_conditionalsEnabled', true );

				if ( ! empty( $conditionals_enabled ) ) {
					// Todo: escaping?
					$field_conditionals = get_post_meta( $field_id, 'ccf_attached_conditionals', true );

					if ( ! empty( $field_conditionals ) ) {
						$new_conditionals = array(
							'conditions' => $field_conditionals,
							'conditionalType' => get_post_meta( $field_id, 'ccf_field_conditionalType', true ),
							'conditionalFieldsRequired' => get_post_meta( $field_id, 'ccf_field_conditionalFieldsRequired', true ),
						);

						$conditionals[$slug] = $new_conditionals;
					}
				}

				if ( 'file' === $type ) {
					$contains_file = true;
				}

				$fields_html .= apply_filters( 'ccf_field_html', CCF_Field_Renderer::factory()->render_router( $type, $field_id, $form_id ), $type, $field_id );
			}

			if ( CCF_Field_Renderer::factory()->section_open ) {
				$fields_html .= '</div>';
			}

			$theme = get_post_meta( $form_id, 'ccf_form_theme', true );
			if ( empty( $theme ) ) {
				$theme = 'default';
			}
			?>

			<div class="ccf-form-wrapper form-id-<?php echo (int) $form_id; ?>" data-form-id="<?php echo (int) $form_id; ?>">
				<form <?php if ( $contains_file ) : ?>enctype="multipart/form-data"<?php endif; ?> class="ccf-form ccf-theme-<?php echo esc_attr( $theme ); ?>" method="post" action="" data-form-id="<?php echo (int) $form_id; ?>">

					<?php $title = get_the_title( $form_id ); if ( ! empty( $title ) && apply_filters( 'ccf_show_form_title', true, $form_id ) ) : ?>
						<div class="form-title">
							<?php echo $title; ?>
						</div>
					<?php endif; ?>

					<?php $description = get_post_meta( $form_id, 'ccf_form_description', true ); if ( ! empty( $description ) && apply_filters( 'ccf_show_form_description', true, $form_id ) ) : ?>
						<div class="form-description">
							<?php echo esc_html( $description ); ?>
						</div>
					<?php endif; ?>

					<?php echo $fields_html; ?>

					<div class="form-submit <?php echo esc_attr( get_post_meta( $form_id, 'ccf_form_buttonClass', true ) ); ?>">
						<input type="submit" class="btn btn-primary ccf-submit-button" value="<?php echo esc_attr( get_post_meta( $form_id, 'ccf_form_buttonText', true ) ); ?>">
						<img class="loading-img" src="<?php echo esc_url( site_url( '/wp-admin/images/wpspin_light.gif' ) ); ?>">
					</div>

					<script type="text/javascript">
					window.wp = window.wp || {};
					wp.ccf = wp.ccf || {};
					wp.ccf.conditionals = wp.ccf.conditionals || [];
					wp.ccf.conditionals[<?php echo (int) $form_id; ?>] = <?php echo wp_json_encode( $conditionals ); ?>;
					</script>

					<input type="hidden" name="form_id" value="<?php echo (int) $form_id; ?>">
					<input type="hidden" name="form_page" value="<?php echo esc_url( untrailingslashit( site_url() ) . $_SERVER['REQUEST_URI'] ); ?>">
					<input type="text" name="my_information" style="display: none;">
					<input type="hidden"  name="ccf_form" value="1">
					<input type="hidden" name="form_nonce" value="<?php echo wp_create_nonce( 'ccf_form' ); ?>">
				</form>

				<iframe class="ccf-form-frame" id="ccf_form_frame_<?php echo (int) $form_id; ?>" name="ccf_form_frame_<?php echo (int) $form_id; ?>"></iframe>
			</div>

			<?php
		}

		$form_html = ob_get_clean();
		
		CCF_Field_Renderer::factory()->reset();

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

/**
 * Output a custom contact form.
 *
 * @param $form_id
 * @since 6.3.4
 * @return string
 */
function ccf_output_form( $form_id ) {
	echo CCF_Form_Renderer::factory()->get_rendered_form( $form_id );
}

/**
 * Output a custom contact form. This is function is here for backwards compat.
 *
 * @param $form_id
 * @since 1.0?
 * @return string
 */
function serveCustomContactForm( $form_id ) {
	ccf_output_form( $form_id );
}
