<?php

class CCF_Form_Handler {

	/**
	 * Contains sanitizer and validator callbacks for each field type
	 *
	 * @var array
	 * @since 6.0
	 */
	public $field_callbacks;

	/**
	 * Cache errors for each form based on field
	 *
	 * @var array
	 * @since 6.0
	 */
	public $errors_by_form = array();

	/**
	 * Setup callbacks
	 *
	 * @since 6.0
	 */
	public function __construct() {
		$this->field_callbacks = apply_filters( 'ccf_field_callbacks', array(
			'single-line-text' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'not_empty' ),
			),
			'paragraph-text' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'not_empty' ),
			),
			'hidden' => array(
				'sanitizer' => 'sanitize_text_field',
			),
			'email' => array(
				'sanitizer' => 'sanitize_email',
				'validator' => array( $this, 'is_email' ),
			),
			'phone' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'is_phone' ),
			),
			'website' => array(
				'sanitizer' => 'esc_url_raw',
				'validator' => array( $this, 'is_website' ),
			),
			'name' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'is_name' ),
			),
			'address' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'is_address' ),
			),
			'date' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'is_date' ),
			),
			'dropdown' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'not_empty' ),
			),
			'checkboxes' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'not_empty' ),
			),
			'radio' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'not_empty' ),
			),
		) );
	}

	/**
	 * Get errors for a form. Optional slug allows you to get errors from a specific field within a form
	 *
	 * @param int $form_id
	 * @param string $slug
	 * @since 6.0
	 * @return bool
	 */
	public function get_errors( $form_id, $slug = null ) {
		if ( ! empty( $this->errors_by_form[$form_id] ) && is_array( $this->errors_by_form[$form_id] ) ) {
			if ( ! empty( $slug ) ) {
				if ( ! empty( $this->errors_by_form[$form_id][$slug] ) ) {
					return $this->errors_by_form[$form_id][$slug];
				}
			} else {
				return $this->errors_by_form[$form_id];
			}
		}

		return false;
	}

	/**
	 * Simple callback to determine if a field is empty.
	 *
	 * @param mixed $value
	 * @param int $field_id
	 * @param bool $required
	 * @since 6.0
	 * @return array|bool
	 */
	public function not_empty( $value, $field_id, $required ) {
		if ( $required && empty( $value ) ) {
			return array( 'required' => esc_html__( 'This field is required.', 'custom-contact-forms' ) );
		}

		return true;
	}

	/**
	 * Simple callback to determine if a phone number is valid
	 *
	 * @param mixed $value
	 * @param int $field_id
	 * @param bool $required
	 * @since 6.0
	 * @return array|bool
	 */
	public function is_phone( $value, $field_id, $required ) {
		$errors = array();

		if ( $required && empty( $value ) ) {
			$errors['required'] = esc_html__( 'This field is required', 'custom-contact-forms' );
			return $errors;
		}

		if ( ! empty( $value ) && strlen( $value ) < 7 ) {
			$errors['digits'] = esc_html__( 'This phone number is too short', 'custom-contact-forms' );
		}

		$format = get_post_meta( $field_id, esc_html__( 'ccf_field_phoneFormat', 'custom-contact-forms' ), true );

		if ( ! empty( $value ) && preg_match( '#[^0-9+.)(\-]#', $value ) ) {
			$errors['chars'] = esc_html__( 'This phone number contains invalid characters.', 'custom-contact-forms' );
		}

		if ( ! empty( $value ) && $format === 'us' ) {
			$stripped_number = preg_replace( '#[^0-9]#', '', $value );
			if ( strlen( $stripped_number ) !== 10 ) {
				$errors['digits'] = esc_html__( 'This phone number is not 10 digits.', 'custom-contact-forms' );
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;
	}

	/**
	 * Simple callback to determine if an address is valid
	 *
	 * @param mixed $value
	 * @param int $field_id
	 * @param bool $required
	 * @since 6.0
	 * @return array|bool
	 */
	public function is_address( $value, $field_id, $required ) {
		$errors = array();

		$address_type = get_post_meta( $field_id, 'ccf_field_addressType', true );

		if ( $required && empty( $value['street'] ) ) {
			$errors['street_required'] = esc_html__( 'This field is required.', 'custom-contact-forms' );
		}

		if ( $required && empty( $value['line_two'] ) ) {
			$errors['line_two_required'] = esc_html__( 'This field is required.', 'custom-contact-forms' );
		}

		if ( $required && empty( $value['city'] ) ) {
			$errors['city_required'] = esc_html__( 'This field is required.', 'custom-contact-forms' );
		}

		if ( $required && empty( $value['state'] ) ) {
			$errors['state_required'] = esc_html__( 'This field is required.', 'custom-contact-forms' );
		}

		if ( $required && empty( $value['zipcode'] ) ) {
			$errors['zipcode_required'] = esc_html__( 'This field is required.', 'custom-contact-forms' );
		}

		if ( 'international' === $address_type ) {
			if ( $required && empty( $value['country'] ) ) {
				$errors['country_required'] = esc_html__( 'This field is required.', 'custom-contact-forms' );
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;
	}

	/**
	 * Simple callback to determine if an email is valid
	 *
	 * @param mixed $value
	 * @param int $field_id
	 * @param bool $required
	 * @since 6.0
	 * @return array|bool
	 */
	public function is_email( $value, $field_id, $required ) {
		$errors = array();

		if ( is_array( $value ) ) {
			if ( $required && empty( $value['email'] ) ) {
				$errors['email_required'] = esc_html__( 'This field is required.', 'custom-contact-forms' );
			} else {
				if ( ! empty( $value['email'] ) && ! is_email( $value['email'] ) ) {
					$errors['email'] = esc_html__( 'This is not a valid email', 'custom-contact-forms' );
				}
			}

			if ( $required && empty( $value['confirm'] ) ) {
				$errors['confirm_required'] = esc_html__( 'This field is required.', 'custom-contact-forms' );
			} else {
				if ( $value['email'] !== $value['confirm'] ) {
					$errors['match'] = esc_html__( 'Emails do not match.', 'custom-contact-forms' );
				}
			}
		} else {
			if ( $required && empty( $value ) ) {
				$errors['email_required'] = esc_html__( 'This field is required.', 'custom-contact-forms' );
			} else {
				if ( ! empty( $value ) && ! is_email( $value ) ) {
					$errors['email'] = esc_html__( 'This is not a valid email', 'custom-contact-forms' );
				}
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;
	}

	/**
	 * Simple callback to determine if a name is valid
	 *
	 * @param mixed $value
	 * @param int $field_id
	 * @param bool $required
	 * @since 6.0
	 * @return array|bool
	 */
	public function is_name( $value, $field_id, $required ) {
		$errors = array();

		if ( $required && empty( $value['first'] ) ) {
			$errors['first_required'] = esc_html__( 'First name is required.', 'custom-contact-forms' );
		}

		if ( $required && empty( $value['last'] ) ) {
			$errors['last_required'] = esc_html__( 'Last name is required.', 'custom-contact-forms' );
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;
	}

	/**
	 * Simple callback to determine if a website is valid
	 *
	 * @param mixed $value
	 * @param int $field_id
	 * @param bool $required
	 * @since 6.0
	 * @return array|bool
	 */
	public function is_website( $value, $field_id, $required ) {
		$errors = array();
		if ( $required && empty( $value ) ) {
			$errors['website_required'] = esc_html__( 'This field is required.', 'custom-contact-forms' );
		} else {
			if ( ! empty( $value ) && ! preg_match( '/^http(s?)\:\/\/(([a-zA-Z0-9\-\._]+(\.[a-zA-Z0-9\-\._]+)+)|localhost)(\/?)([a-zA-Z0-9\-\.\?\,\'\/\\\+&amp;%\$#_]*)?([\d\w\.\/\%\+\-\=\&amp;\?\:\\\&quot;\'\,\|\~\;]*)$/i', $value ) ) {
				$errors['website'] = esc_html__( "This is not a valid URL. URL's must start with http(s)://", 'custom-contact-forms' );
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;
	}

	/**
	 * Simple callback to determine if a date is valid
	 *
	 * @param mixed $value
	 * @param int $field_id
	 * @param bool $required
	 * @since 6.0
	 * @return array|bool
	 */
	public function is_date( $value, $field_id, $required ) {
		$errors = array();

		$show_date = get_post_meta( $field_id, 'ccf_field_showDate', true );
		$show_time = get_post_meta( $field_id, 'ccf_field_showTime', true );

		if ( ! empty( $show_date ) && empty( $show_time ) ) {
			if ( $required && empty( $value['date'] ) ) {
				$errors['date_required'] = esc_html__( 'Date is required.', 'custom-contact-forms' );
			} else {
				if ( ! empty( $value['date'] ) && ! preg_match( '#^([0-9]|/)+$#', $value['date'] ) ) {
					$errors['date'] = esc_html__( 'This date is not valid.', 'custom-contact-forms' );
				}
			}
		} elseif ( empty( $show_date ) && ! empty( $show_time ) ) {
			if ( $required && empty( $value['hour'] ) ) {
				$errors['hour_required'] = esc_html__( 'Hour is required.', 'custom-contact-forms' );
			} else {
				if ( ! empty( $value['hour'] ) && ! preg_match( '#^([0-9]|/)+$#', $value['hour'] ) ) {
					$errors['date'] = esc_html__( 'This is not a valid hour.', 'custom-contact-forms' );
				}
			}

			if ( $required && empty( $value['minute'] ) ) {
				$errors['minutes_required'] = esc_html__( 'Minute is required.', 'custom-contact-forms' );
			} else {
				if ( ! empty( $value['minute'] ) && ! preg_match( '#^[0-9]+$#', $value['minute'] ) ) {
					$errors['hour'] = esc_html__( 'This is not a valid minute.', 'custom-contact-forms' );
				}
			}

			if ( $required && empty( $value['am-pm'] ) ) {
				$errors['am-pm_required'] = esc_html__( 'AM/PM is required.', 'custom-contact-forms' );
			}

			if ( ! empty( $errors ) ) {
				return $errors;
			}
		} else {
			if ( $required && empty( $value['date'] ) ) {
				$errors['date_required'] = esc_html__( 'Date is required.', 'custom-contact-forms' );
			} else {
				if ( ! empty( $value['date'] ) && ! preg_match( '#^([0-9]|/)+$#', $value['date'] ) ) {
					$errors['date'] = esc_html__( 'This date is not valid.', 'custom-contact-forms' );
				}
			}

			if ( $required && empty( $value['hour'] ) ) {
				$errors['hour_required'] = esc_html__( 'Hour is required.', 'custom-contact-forms' );
			} else {
				if ( ! empty( $value['hour'] ) && ! preg_match( '#^[0-9]+$#', $value['hour'] ) ) {
					$errors['hour'] = esc_html__( 'This is not a valid hour.', 'custom-contact-forms' );
				}
			}

			if ( $required && empty( $value['minute'] ) ) {
				$errors['minutes_required'] = esc_html__( 'Minute is required.', 'custom-contact-forms' );
			} else {
				if ( ! empty( $value['minute'] ) && ! preg_match( '#^[0-9]+$#', $value['minute'] ) ) {
					$errors['minute'] = esc_html__( 'This is not a valid minute.', 'custom-contact-forms' );
				}
			}

			if ( $required && empty( $value['am-pm'] ) ) {
				$errors['am-pm_required'] = esc_html__( 'AM/PM is required.', 'custom-contact-forms' );
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;
	}

	/**
	 * Simple callback to sanitize a phone number
	 *
	 * @param mixed $value
	 * @param int $field_id
	 * @since 6.0
	 * @return string
	 */
	public function sanitize_phone( $value, $field_id ) {
		return preg_replace( '#[^0-9+]#', '', $value );
	}

	/**
	 * Register form submission listener
	 *
	 * @since 6.0
	 */
	public function setup() {
		add_action( 'init', array( $this, 'submit_listen' ) );
	}

	/**
	 * Parse form requests
	 *
	 * @since 6.0
	 */
	public function submit_listen() {
		if ( empty( $_POST['ccf_form'] ) || empty( $_POST['form_id'] ) ) {
			return;
		}

		$submission = $this->process_submission();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$response = false;

			if ( is_array( $submission ) ) {
				$response = $submission;
				$response['success'] = true;
			}

			wp_send_json( $response );
		} else {
			if ( is_array( $submission ) ) {
				if ( ! empty( $submission['completion_redirect_url'] ) ) {
					wp_redirect( esc_url_raw( $submission['completion_redirect_url'] ) );
					exit;
				}
			}
		}
	}

	/**
	 * Process a form submission
	 *
	 * @return array|bool
	 */
	function process_submission() {
		if ( ! empty( $_POST['my_information'] ) ) {
			// Honeypot
			return false;
		}

		if ( empty( $_POST['form_nonce'] ) || ! wp_verify_nonce( $_POST['form_nonce'], 'ccf_form' ) ) {
			return false;
		}

		$form_id = (int) $_POST['form_id'];

		$form = get_post( $form_id );

		if ( empty( $form ) ) {
			return false;
		}

		$fields = get_post_meta( $form->ID, 'ccf_attached_fields', true );

		$errors = array();

		$submission = array();

		$skip_fields = apply_filters( 'ccf_skip_fields', array( 'html', 'section-header' ), $form->ID );

		foreach ( $fields as $field_id ) {
			$field_id = (int) $field_id;

			$type = get_post_meta( $field_id, 'ccf_field_type', true );
			if ( in_array( $type, $skip_fields ) ) {
				continue;
			}

			$slug = get_post_meta( $field_id, 'ccf_field_slug', true );

			$value = ( isset( $_POST['ccf_field_' . $slug] ) ) ? $_POST['ccf_field_' . $slug] : '';

			$validation = $this->process_field( $field_id, $value );

			if ( $validation['error'] !== null ) {
				$errors[$slug] = $validation['error'];
			} else {
				$submission[$slug] = $validation['sanitized_value'];
			}
		}

		if ( ! empty( $errors ) ) {
			$this->errors_by_form[$form_id] = $errors;
			return false;
		} else {
			$submission_id = wp_insert_post( array(
				'post_status' => 'publish',
				'post_type' => 'ccf_submission',
				'post_parent' => $form_id,
				'post_title' => 'Form Submission ' . $form_id,
			));

			if ( ! is_wp_error( $submission_id ) ) {
				update_post_meta( $submission_id, 'ccf_submission_data', $submission );
			} else {
				return false;
			}

			$output = array(
				'action_type' => get_post_meta( $form_id, 'ccf_form_completion_action_type', true ),
			);

			$send_email_notifications = get_post_meta( $form_id, 'ccf_form_send_email_notifications', true );
			$email_addresses_field = get_post_meta( $form_id, 'ccf_form_email_notification_addresses', true );

			if ( ! empty( $send_email_notifications ) && ! empty( $email_addresses_field ) ) {
				$email_addresses = explode( ',', $email_addresses_field );
				$email_addresses = array_map( 'trim', $email_addresses );

				if ( ! empty( $email_addresses ) ) {
					$message = '';

					ob_start();

					foreach ( $submission as $slug => $field ) {
						?>

						<div>
							<b><?php echo esc_html( $slug ); ?>:</b>
						</div>
						<div style="margin-bottom: 10px;">
							<?php if ( ! empty( $field ) ) { ?>
								<?php if ( is_array( $field ) ) { ?>
									<?php if ( CCF_Submission_CPT::factory()->is_field_date( $field ) ) { ?>

										<?php echo esc_html( CCF_Submission_CPT::factory()->get_pretty_field_date( $field ) ); ?>

									<?php } elseif ( CCF_Submission_CPT::factory()->is_field_name( $field ) ) { ?>

										<?php echo esc_html( CCF_Submission_CPT::factory()->get_pretty_field_name( $field ) ); ?>

									<?php } elseif ( CCF_Submission_CPT::factory()->is_field_address( $field ) ) { ?>

										<?php echo esc_html( CCF_Submission_CPT::factory()->get_pretty_field_address( $field ) ); ?>

									<?php } else { ?>

										<?php foreach ( $field as $key => $value ) { ?>
											<?php if ( is_int( $key ) ) { ?>
												<strong><?php echo esc_html( $key ); ?>:</strong>
											<?php } ?>
											<?php echo esc_html( $value ); ?><br>
										<?php } ?>

									<?php } ?>
								<?php } else { ?>
									<?php echo esc_html( $field ); ?>
								<?php } ?>
							<?php } else { ?>
								<span>-</span>
							<?php } ?>
						</div>

						<?php
					}

					if ( ! empty( $_POST['form_page'] ) ) {
						?>
						<div>
							<?php esc_html_e( 'Form submitted from', 'custom-contact-forms' ); ?>:
							<?php echo esc_url( $_POST['form_page'] ); ?>
						</div>
						<?php
					}

					$message .= ob_get_clean();

					$headers = array( 'MIME-Version: 1.0', 'Content-type: text/html; charset=iso-8859-1' );

					foreach ( $email_addresses as $email ) {
						wp_mail( $email, sprintf( __( '%s: Form Submission to "%s"', 'custom-contact-forms' ), esc_html( get_bloginfo( 'name' ) ), esc_html( get_the_title( $form_id ) ) ), $message, $headers );
					}
				}
			}

			if ( 'redirect' === $output['action_type'] ) {
				$output['completion_redirect_url'] = get_post_meta( $form_id, 'ccf_form_completion_redirect_url', true );
			} else {
				$output['completion_message'] = get_post_meta( $form_id, 'ccf_form_completion_message', true );

				if ( empty( $output['completion_message'] ) ) {
					$output['completion_message'] = esc_html__( 'Thank you for your submission.', 'custom-contact-forms' );
				}
			}

			return $output;
		}
	}

	/**
	 * Process a field. Either return errors or sanitized form submission value.
	 *
	 * @param int $field_id
	 * @param string $value
	 * @return array
	 */
	public function process_field( $field_id, $value ) {
		$return = array(
			'error' => null,
			'sanitized_value' => null,
		);

		$type = get_post_meta( $field_id, 'ccf_field_type', true );
		$required = get_post_meta( $field_id, 'ccf_field_required', true );

		$validator = apply_filters( 'ccf_field_validator', $this->field_callbacks[$type]['validator'], $value, $field_id, $type );

		$is_valid = true;
		if ( ! empty( $validator ) ) {
			$is_valid = call_user_func( $validator, $value, $field_id, (bool) $required );
		}

		if ( $is_valid !== true ) {
			$return['error'] = $is_valid;
		} else {
			if ( is_array( $value ) ) {
				$return['sanitized_value'] = array();

				foreach ( $value as $key => $single_value ) {
					$return['sanitized_value'][$key] = call_user_func( apply_filters( 'ccf_field_sanitizer', $this->field_callbacks[$type]['sanitizer'], $single_value, $field_id, $type ), $single_value, $field_id );
				}
			} else {
				$return['sanitized_value'] = call_user_func( apply_filters( 'ccf_field_sanitizer', $this->field_callbacks[$type]['sanitizer'], $value, $field_id, $type ), $value, $field_id );
			}
		}

		return $return;
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