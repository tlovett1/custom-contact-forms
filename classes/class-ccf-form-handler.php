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
			'recaptcha' => array(
				'validator' => array( $this, 'valid_recaptcha' ),
			),
			'simple-captcha' => array(
				'validator' => array( $this, 'valid_simple_captcha' ),
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
			'file' => array(
				'sanitizer' => array( $this, 'handle_file' ),
				'validator' => array( $this, 'is_file' ),
			),
			'date' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'is_date' ),
			),
			'dropdown' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'not_empty_choiceable' ),
			),
			'checkboxes' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'not_empty_choiceable' ),
			),
			'radio' => array(
				'sanitizer' => 'sanitize_text_field',
				'validator' => array( $this, 'not_empty_choiceable' ),
			),
		) );
	}

	/**
	 * Upload file and return relevant attachment info
	 *
	 * @param string $value
	 * @param int    $field_id
	 * @since 6.4
	 * @return array|int
	 */
	public function handle_file( $value, $field_id ) {
		require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/file.php' );
		require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/image.php' );
		require_once( trailingslashit( ABSPATH ) . 'wp-admin/includes/media.php' );

		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );

		$file_id = media_handle_upload( 'ccf_field_' . $slug, 0 );

		if ( is_wp_error( $file_id ) ) {
			return 0;
		}

		$url = wp_get_attachment_url( $file_id );

		return array(
			'id' => $file_id,
			'url' => $url,
			'file_name' => basename( $url ),
		);
	}

	/**
	 * Validate a file upload.
	 *
	 * @param $value
	 * @param $field_id
	 * @param $required
	 * @since 6.4
	 * @return array|bool
	 */
	public function is_file( $value, $field_id, $required ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$errors = array();

		if ( $required ) {
			if ( empty( $_FILES[ 'ccf_field_' . $slug ] ) || 4 === $_FILES[ 'ccf_field_' . $slug ]['error'] ) {
				return array( 'required' => esc_html__( 'This field is required.', 'custom-contact-forms' ) );
			}
		} else {
			if ( ! empty( $_FILES[ 'ccf_field_' . $slug ] ) && 4 === $_FILES[ 'ccf_field_' . $slug ]['error'] ) {
				return true;
			}
		}

		$max_file_size = get_post_meta( $field_id, 'ccf_field_maxFileSize', true );

		if ( ! empty( $max_file_size ) && $_FILES[ 'ccf_field_' . $slug ]['size'] > ( $max_file_size * 1000 * 1000 ) || 1 === $_FILES[ 'ccf_field_' . $slug ]['error'] ) {
			$errors['file_size'] = sprintf( esc_html__( 'This file is too big (%d MB max)', 'custom-contact-forms' ), (int) $max_file_size );
		}

		if ( ! empty( $_FILES[ 'ccf_field_' . $slug ]['error'] ) || empty( $_FILES[ 'ccf_field_' . $slug ]['size'] ) ) {
			return array( 'file_upload' => esc_html__( 'An upload error occurred.', 'custom-contact-forms' ) );
		}

		$extension = strtolower( pathinfo( $_FILES[ 'ccf_field_' . $slug ]['name'], PATHINFO_EXTENSION ) );

		$valid_extensions = get_post_meta( $field_id, 'ccf_field_fileExtensions', true );

		if ( ! empty( $valid_extensions ) ) {
			$valid_extensions = strtolower( str_replace( ';', ',', $valid_extensions ) );
			$valid_extensions = explode( ',', $valid_extensions );

			foreach ( $valid_extensions as $key => $ext ) {
				$ext = trim( $ext );

				if ( empty( $ext ) ) {
					unset( $valid_extensions[ $key ] );
				} else {
					$valid_extensions[ $key ] = $ext;
				}
			}

			if ( ! empty( $valid_extensions ) && ! in_array( $extension, $valid_extensions ) ) {
				$errors['file_extension'] = esc_html__( 'File contains an invalid extension.', 'custom-contact-forms' );
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;
	}

	/**
	 * Get errors for a form. Optional slug allows you to get errors from a specific field within a form
	 *
	 * @param int    $form_id
	 * @param string $slug
	 * @since 6.0
	 * @return bool
	 */
	public function get_errors( $form_id, $slug = null ) {
		if ( ! empty( $this->errors_by_form[ $form_id ] ) && is_array( $this->errors_by_form[ $form_id ] ) ) {
			if ( ! empty( $slug ) ) {
				if ( ! empty( $this->errors_by_form[ $form_id ][ $slug ] ) ) {
					return $this->errors_by_form[ $form_id ][ $slug ];
				}
			} else {
				return $this->errors_by_form[ $form_id ];
			}
		}

		return false;
	}

	/**
	 * Simple callback to determine if a field is empty.
	 *
	 * @param mixed $value
	 * @param int   $field_id
	 * @param bool  $required
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
	 * Simple callback to determine if a choiceable is "empty"
	 *
	 * @param mixed $value
	 * @param int   $field_id
	 * @param bool  $required
	 * @since 6.4
	 * @return array|bool
	 */
	public function not_empty_choiceable( $value, $field_id, $required ) {
		$error = false;

		if ( $required ) {
			if ( ! is_array( $value ) ) {
				if ( empty( $value ) && $value !== '0' ) {
					$error = true;
				}
			} else {
				$error = true;

				if ( ! empty( $value ) ) {
					foreach ( $value as $something ) {
						if ( ! empty( $something ) ) {
							$error = false;
						}
					}
				}
			}
		}

		if ( $error ) {
			return array( 'required' => esc_html__( 'This field is required.', 'custom-contact-forms' ) );
		}

		return true;
	}

	public function valid_recaptcha( $value, $field_id, $required ) {
		$secret = get_post_meta( $field_id, 'ccf_field_secretKey', true );

		$response = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $value );

		$data = wp_remote_retrieve_body( $response );

		$data = json_decode( $data );

		if ( empty( $data->success ) ) {
			return array( 'recaptcha' => esc_html__( 'Your reCAPTCHA response was incorrect.', 'custom-contact-forms' ) );
		}

		return true;
	}

	/**
	 * Check if simple captcha response is valid
	 *
	 * @since  7.7
	 * @param  string $value
	 * @param  int $field_id
	 * @param  boolean $required
	 * @return boolean|array
	 */
	public function valid_simple_captcha( $value, $field_id, $required ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );

		if ( empty( $value ) || empty( $_SESSION['ccf_simple_captcha_' . $slug] ) || empty( $_SESSION['ccf_simple_captcha_' . $slug]['code'] ) || strtolower( $_SESSION['ccf_simple_captcha_' . $slug]['code'] ) !== strtolower( trim( $value ) ) ) {
			return array( 'simple-captcha' => esc_html__( 'Your CAPTCHA response was incorrect.', 'custom-contact-forms' ) );
		}

		return true;
	}

	/**
	 * Simple callback to determine if a phone number is valid
	 *
	 * @param mixed $value
	 * @param int   $field_id
	 * @param bool  $required
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

		if ( ! empty( $value ) && preg_match( '#[^0-9+.)(\- ]#', $value ) ) {
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
	 * @param int   $field_id
	 * @param bool  $required
	 * @since 6.0
	 * @return array|bool
	 */
	public function is_address( $value, $field_id, $required ) {
		$errors = array();

		$address_type = get_post_meta( $field_id, 'ccf_field_addressType', true );

		if ( $required && empty( $value['street'] ) ) {
			$errors['street_required'] = esc_html__( 'This field is required.', 'custom-contact-forms' );
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
	 * @param int   $field_id
	 * @param bool  $required
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
	 * @param int   $field_id
	 * @param bool  $required
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
	 * @param int   $field_id
	 * @param bool  $required
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
	 * @param int   $field_id
	 * @param bool  $required
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
	 * @param int   $field_id
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
		add_action( 'init', array( $this, 'submit_listen' ), 11 );
		add_action( 'init', array( $this, 'start_session' ) );
	}

	/**
	 * Start a session for captcha later
	 *
	 * @since  7.7
	 */
	public function start_session() {
		if ( session_id() === '' ) {
			session_start();
		}
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

		$submission_response = $this->process_submission();

		echo json_encode( $submission_response );
		exit;
	}

	/**
	 * Process a form submission
	 *
	 * @return array
	 */
	function process_submission() {
		if ( ! empty( $_POST['my_information'] ) ) {
			// Honeypot
			return array( 'error' => 'honeypot', 'success' => false );
		}

		if ( empty( $_POST['form_nonce'] ) || ! wp_verify_nonce( $_POST['form_nonce'], 'ccf_form' ) ) {
			return array( 'error' => 'nonce', 'success' => false );
		}

		$form_id = (int) $_POST['form_id'];

		$form = get_post( $form_id );

		if ( empty( $form ) ) {
			return array( 'error' => 'missing_form', 'success' => false );
		}

		$fields = get_post_meta( $form->ID, 'ccf_attached_fields', true );
		$field_slug_to_id = array();

		$errors = array();

		$submission = array();

		$skip_fields = apply_filters( 'ccf_skip_fields', array( 'html', 'section-header' ), $form->ID );
		$save_skip_fields = apply_filters( 'ccf_save_skip_fields', array( 'recaptcha', 'simple-captcha' ), $form->ID );
		$file_ids = array();
		$all_form_fields = array();

		foreach ( $fields as $field_id ) {
			$field_id = (int) $field_id;

			$type = get_post_meta( $field_id, 'ccf_field_type', true );

			if ( in_array( $type, $skip_fields ) ) {
				continue;
			}

			$slug = null;

			$field_metas = get_post_meta( $field_id );
			$new_field = array();

			foreach ( $field_metas as $meta_key => $meta_value ) {
				if ( 0 === stripos( $meta_key, 'ccf_field_' ) ) {
					if ( 'ccf_field_slug' === $meta_key ) {
						$slug = $meta_value[0];
					}

					$new_field[ $meta_key ] = wp_kses_post( $meta_value[0] );
				}
			}

			$all_form_fields[ $slug ] = $new_field;

			// We save this to reference later
			$field_slug_to_id[ $slug ] = array( 'id' => $field_id, 'type' => sanitize_text_field( $type ) );

			$custom_value_mapping = array( 'recaptcha' => 'g-recaptcha-response' );

			if ( in_array( $type, array_keys( $custom_value_mapping ) ) ) {
				$value = ( isset( $_POST[ $custom_value_mapping[ $type ] ] ) ) ? $_POST[ $custom_value_mapping[ $type ] ] : '';
			} else {
				$value = ( isset( $_POST[ 'ccf_field_' . $slug ] ) ) ? $_POST[ 'ccf_field_' . $slug ] : '';
			}

			$validation = $this->process_field( $field_id, $value );

			if ( $validation['error'] !== null ) {
				$errors[ $slug ] = $validation['error'];
			} else {
				if ( ! in_array( $type, $save_skip_fields ) ) {
					$submission[ $slug ] = $validation['sanitized_value'];

					if ( 'file' === $type ) {
						$file_ids[] = $submission[ $slug ]['id'];
					}
				}
			}
		}

		if ( ! empty( $errors ) ) {
			$this->errors_by_form[ $form_id ] = $errors;
			return array( 'error' => 'invalid_fields', 'field_errors' => $errors, 'success' => false );
		} else {
			$submission_id = wp_insert_post( array(
				'post_status' => 'publish',
				'post_type' => 'ccf_submission',
				'post_parent' => $form_id,
				'post_title' => 'Form Submission ' . $form_id,
			));

			if ( ! is_wp_error( $submission_id ) ) {
				update_post_meta( $submission_id, 'ccf_submission_data', $submission );

				/**
				 * @since 6.6
				 */
				update_post_meta( $submission_id, 'ccf_submission_data_map', $field_slug_to_id );

				/**
				 * @since 7.4.4
				 */
				update_post_meta( $submission_id, 'ccf_submission_form_fields', $all_form_fields );

				update_post_meta( $submission_id, 'ccf_submission_ip', sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) );

				/**
				 * @since  7.7
				 */
				if ( ! empty( $_POST['form_page'] ) ) {
					$form_page = $_POST['form_page'];
					update_post_meta( $submission_id, 'ccf_submission_form_page', esc_url_raw( $form_page ) );
				} else {
					$form_page = null;
				}

				$uploads = array();

				foreach ( $file_ids as $file_id ) {
					wp_update_post( array(
						'ID' => $file_id,
						'post_parent' => $submission_id,
					) );

					$uploads[] = get_attached_file( $file_id );
				}

				do_action( 'ccf_successful_submission', $submission_id, $form_id );
			} else {
				do_action( 'ccf_unsuccessful_submission', $form_id );

				return array( 'error' => 'could_not_create_submission', 'success' => false );
			}

			// Post creation
			$post_creation = get_post_meta( $form_id, 'ccf_form_post_creation', true );

			if ( ! empty( $post_creation ) ) {
				$post_creation_type = get_post_meta( $form_id, 'ccf_form_post_creation_type', true );
				$post_creation_status = get_post_meta( $form_id, 'ccf_form_post_creation_status', true );

				$mappings = get_post_meta( $form_id, 'ccf_form_post_field_mappings', true );

				if ( ! empty( $mappings ) ) {

					$args = array(
						'post_status' => ( ! empty( $post_creation_status ) ) ? $post_creation_status : 'draft',
						'post_type' => ( ! empty( $post_creation_type ) ) ? $post_creation_type : 'post',
					);

					$tags = array();
					$custom_fields = array();

					foreach ( $mappings as $mapping ) {
						if ( ! empty( $mapping['formField'] ) && isset( $submission[ $mapping['formField'] ] ) ) {
							$field_id = $field_slug_to_id[ $mapping['formField'] ]['id'];
							$field_type = get_post_meta( $field_id, 'ccf_field_type', true );

							$submission_value = $submission[ $mapping['formField'] ];
							if ( is_array( $submission_value ) && isset( $submission_value['email'] ) ) {
								$submission_value = $submission_value['email'];
							}

							if ( 'post_title' === $mapping['postField'] ) {
								$args['post_title'] = $this->_flatten_and_concat( $submission_value );
							} elseif ( 'post_content' === $mapping['postField'] ) {
								$args['post_content'] = $this->_flatten_and_concat( $submission_value );
							} elseif ( 'post_date' === $mapping['postField'] ) {
								$args['post_date'] = $this->_flatten_and_concat( $submission_value );
							} elseif ( 'post_excerpt' === $mapping['postField'] ) {
								$args['post_excerpt'] = $this->_flatten_and_concat( $submission_value );
							} elseif ( 'post_tag' === $mapping['postField'] ) {
								if ( 'checkboxes' === $field_type ) {
									$tags = array_merge( $tags, $submission_value );
								} elseif ( 'dropdown' == $field_type && is_array( $submission_value ) ) {
									$tags = array_merge( $tags, $submission_value );
								} else {
									$tags[] = $this->_flatten_and_concat( $submission[ $mapping['formField'] ] );
								}
							} elseif ( 'custom_field' === $mapping['postField'] && ! empty( $mapping['customFieldKey'] ) ) {
								$custom_fields[] = array(
									'key' => $mapping['customFieldKey'],
									'value' => $this->_flatten_and_concat( $submission_value ),
								);
							}
						}
					}

					if ( empty( $args['post_title'] ) ) {
						$args['post_title'] = apply_filters( 'ccf_default_post_creation_title', esc_html__( 'Post created by form', 'custom-contact-forms' ), $args, $form_id, $submission_id, $submission );
					}

					$post_creation_id = wp_insert_post( apply_filters( 'ccf_post_creation_args', $args, $form_id, $submission_id, $submission ) );

					if ( ! is_wp_error( $post_creation_id ) ) {
						update_post_meta( $post_creation_id, 'ccf_created_by_form', (int) $form_id );

						if ( ! empty( $tags ) ) {
							wp_set_object_terms( $post_creation_id, $tags, 'post_tag', true );
						}

						if ( ! empty( $custom_fields ) ) {
							foreach ( $custom_fields as $custom_field ) {
								// Todo: sanitization?
								add_post_meta( $post_creation_id, $custom_field['key'], $custom_field['value'] );
							}
						}
					}

					do_action( 'ccf_post_creation', $post_creation_id, $form_id, $submission_id, $submission );
				}
			}

			$output = array(
				'success' => true,
				'action_type' => get_post_meta( $form_id, 'ccf_form_completion_action_type', true ),
			);

			$notifications = get_post_meta( $form_id, 'ccf_form_notifications', true );

			if ( ! empty( $notifications ) ) {
				foreach ( $notifications as $notification ) {
					if ( ! empty( $notification['active'] ) && ! empty( $notification['addresses'] ) ) {

						$message = $notification['content'];

						// Variables
						if ( false !== stripos( $message, '[all_fields]' ) ) {
							$all_fields = '';

							ob_start();

							foreach ( $submission as $slug => $field ) {
								$field_id = $field_slug_to_id[ $slug ]['id'];
								$label = get_post_meta( $field_id, 'ccf_field_label', true );
								$type = get_post_meta( $field_id, 'ccf_field_type', true );

								if ( 'hidden' === $type ) {
									$label = esc_html__( '*Hidden Field*', 'custom-contact-forms' );
								}
								?>

								<div>
									<?php if ( ! empty( $label ) ) : ?>
										<b><?php echo esc_html( $label ); ?> <?php if ( apply_filters( 'ccf_show_slug_in_submission_email', false, $submission_id, $form_id ) ) : ?>(<?php echo esc_html( $slug ); ?>)<?php endif; ?>:</b>
									<?php else : ?>
										<b><?php echo esc_html( $slug ); ?>:</b>
									<?php endif; ?>
								</div>
								<div style="margin-bottom: 10px;">
									<?php if ( ! empty( $field ) || $field === '0') : ?>

										<?php if ( 'date' === $type ) : ?>

											<?php echo esc_html( stripslashes( CCF_Submission_CPT::factory()->get_pretty_field_date( $field, $field_id ) ) ); ?>

										<?php elseif ( 'name' === $type ) : ?>

											<?php echo esc_html( stripslashes( CCF_Submission_CPT::factory()->get_pretty_field_name( $field ) ) ); ?>

										<?php elseif ( 'file' === $type ) : ?>

											<a href="<?php echo esc_url( $field['url'] ); ?>"><?php echo esc_html( stripslashes( $field['file_name'] ) ); ?></a>

										<?php elseif ( 'address' === $type ) : ?>

											<?php echo esc_html( stripslashes( CCF_Submission_CPT::factory()->get_pretty_field_address( $field ) ) ); ?>

										<?php elseif ( 'email' === $type ) : ?>

											<?php if ( is_array( $field ) ) : ?>
												<?php echo esc_html( stripslashes( $field['email'] ) ); ?>
											<?php else : ?>
												<?php echo esc_html( stripslashes( $field ) ); ?>
											<?php endif; ?>

										<?php elseif ( 'dropdown' === $type || 'radio' === $type || 'checkboxes' === $type ) : ?>

											<?php if ( is_array( $field ) ) : ?>

												<?php $i = 0; foreach ( $field as $value ) : ?>
													<?php if ( ! empty( $value ) ) : ?>
														<?php if ( $i !== 0 ) : ?><br><?php endif; ?>
														<?php echo esc_html( stripslashes( $value ) ); ?>
														<?php $i++; ?>
													<?php endif; ?>
												<?php endforeach; ?>

												<?php if ( 0 === $i ) : ?>
													<span>-</span>
												<?php endif; ?>

											<?php else : ?>
												<?php echo esc_html( stripslashes( $field ) ); ?>
											<?php endif; ?>

										<?php else : ?>
											<?php echo esc_html( stripslashes( $field ) ); ?>
										<?php endif; ?>
									<?php else : ?>
										<span>-</span>
									<?php endif; ?>
								</div>

							<?php
							}

							if ( ! empty( $form_page ) ) {
								?>
								<div>
									<?php esc_html_e( 'Form submitted from', 'custom-contact-forms' ); ?>:
									<?php echo esc_url( $form_page ); ?>
								</div>
							<?php
							}

							if ( apply_filters( 'ccf_show_ip_in_submission_email', true, $submission_id, $form_id ) ) {
								?>
								<div>
									<?php esc_html_e( 'Form submitter IP', 'custom-contact-forms' ); ?>:
									<?php echo esc_html( $_SERVER['REMOTE_ADDR'] ); ?>
								</div>
								<?php
							}

							$all_fields .= ob_get_clean();

							$message = str_ireplace( '[all_fields]', $all_fields, $message );
						}

						if ( false !== stripos( $message, '[ip_address]' ) ) {
							$message = str_ireplace( '[ip_address]', $_SERVER['REMOTE_ADDR'], $message );
						}

						if ( false !== stripos( $message, '[current_date_time]' ) ) {
							$message = str_ireplace( '[current_date_time]', date( 'F j, Y, g:i a' ), $message );
						}

						if ( false !== stripos( $message, '[form_page_url]' ) ) {
							$message = str_ireplace( '[form_page_url]', esc_url_raw( $form_page ), $message );
						}

						foreach ( $fields as $field_id ) {
							$field_slug = get_post_meta( $field_id, 'ccf_field_slug', true );

							if ( ! empty( $field_slug ) && isset( $submission[ $field_slug ] ) ) {
								$value = $submission[ $field_slug ];
								if ( is_array( $value ) && isset( $value['email'] ) ) {
									$value = $value['email'];
								}

								$message = str_ireplace( '[' . $field_slug . ']', wp_kses_post( $this->_flatten_and_concat( $value ) ), $message );
							}
						}

						$headers = array( 'MIME-Version: 1.0', 'Content-type: text/html; charset=utf-8' );
						$name = null;
						$email = null;

						$reply_to_name = null;
						$reply_to_email = null;

						$sitename = strtolower( $_SERVER['SERVER_NAME'] );
						if ( substr( $sitename, 0, 4 ) === 'www.' ) {
							$sitename = substr( $sitename, 4 );
						}
						$default_from_email = 'wordpress@' . $sitename;

						if ( 'custom' === $notification['fromNameType'] ) {
							$name = $notification['fromName'];
						} else {
							$name_field = $notification['fromNameField'];

							if ( ! empty( $name_field ) && ! empty( $submission[ $name_field ] ) ) {
								if ( is_array( $submission[ $name_field ] ) ) {
									if ( ! empty( $submission[ $name_field ]['first'] ) || ! empty( $submission[ $name_field ]['last'] ) ) {
										$name = $submission[ $name_field ]['first'] . ' ' . $submission[ $name_field ]['last'];
									}
								} else {
									$name = $submission[ $name_field ];
								}
							}
						}

						if ( 'custom' === $notification['fromType'] ) {
							$email = $notification['fromAddress'];
						} elseif ( 'field' === $notification['fromType'] ) {
							$email_field = $notification['fromField'];

							if ( ! empty( $email_field ) && ! empty( $submission[ $email_field ] ) ) {
								if ( is_array( $submission[ $email_field ] ) && ! empty( $submission[ $email_field ]['confirm'] ) ) {
									$email = $submission[ $email_field ]['confirm'];
								} else {
									$email = $submission[ $email_field ];
								}
							}
						}

						if ( 'custom' === $notification['replyToNameType'] ) {
							$reply_to_name = $notification['replyToName'];
						} else {
							$name_field = $notification['replyToNameField'];

							if ( ! empty( $name_field ) && ! empty( $submission[ $name_field ] ) ) {
								if ( is_array( $submission[ $name_field ] ) ) {
									if ( ! empty( $submission[ $name_field ]['first'] ) || ! empty( $submission[ $name_field ]['last'] ) ) {
										$reply_to_name = $submission[ $name_field ]['first'] . ' ' . $submission[ $name_field ]['last'];
									}
								} else {
									$reply_to_name = $submission[ $name_field ];
								}
							}
						}

						if ( 'custom' === $notification['replyToType'] ) {
							$reply_to_email = $notification['replyToAddress'];
						} elseif ( 'field' === $notification['replyToType'] ) {
							$email_field = $notification['replyToField'];

							if ( ! empty( $email_field ) && ! empty( $submission[ $email_field ] ) ) {
								if ( is_array( $submission[ $email_field ] ) && ! empty( $submission[ $email_field ]['confirm'] ) ) {
									$reply_to_email = $submission[ $email_field ]['confirm'];
								} else {
									$reply_to_email = $submission[ $email_field ];
								}
							}
						}

						$reply_to = '';

						if ( ! empty( $name ) && ! empty( $email ) ) {
							$headers[] = 'From: ' . sanitize_text_field( $name ) . ' <' . sanitize_email( $email ) . '>';
							$reply_to = 'Reply-To: ' . sanitize_email( $email );
						} elseif ( ! empty( $name ) && empty( $email ) ) {
							$headers[] = 'From: ' . sanitize_text_field( $name ) . ' <' . sanitize_email( $default_from_email ) . '>';
						} elseif ( empty( $name ) && ! empty( $email ) ) {
							// @Todo: investigate how wp_mail handles From: email
							$headers[] = 'From: ' . sanitize_email( $email );
							$reply_to = 'Reply-To: ' . sanitize_email( $email );
						}

						if ( ! empty( $reply_to_name ) && ! empty( $reply_to_email ) ) {
							$reply_to = 'Reply-To: ' . sanitize_text_field( $reply_to_name ) . ' <' . sanitize_email( $reply_to_email ) . '>';
						} elseif ( ! empty( $reply_to_name ) && empty( $reply_to_email ) ) {
							$reply_to = 'Reply-To: ' . sanitize_text_field( $reply_to_name ) . ' <' . sanitize_email( $default_from_email ) . '>';
						} elseif ( empty( $reply_to_name ) && ! empty( $reply_to_email ) ) {
							$reply_to = 'Reply-To: ' . sanitize_email( $reply_to_email );
						}

						if ( ! empty( $reply_to ) ) {
							$headers[] = $reply_to;
						}

						$email_notification_subject_type = $notification['subjectType'];

						$subject = sprintf( __( '%s: Form Submission', 'custom-contact-forms' ), wp_specialchars_decode( get_bloginfo( 'name' ) ) );
						if ( ! empty( $form->post_title ) ) {
							$subject .= sprintf( __( ' to "%s"', 'custom-contact-forms' ), wp_specialchars_decode( $form->post_title ) );
						}

						if ( 'custom' === $email_notification_subject_type ) {
							$subject = $notification['subject'];
						} elseif ( 'field' === $email_notification_subject_type ) {
							$subject_field = $notification['subjectField'];

							if ( ! empty( $subject_field ) && ! empty( $submission[ $subject_field ] ) ) {
								$subject = $submission[ $subject_field ];
							}
						}

						$include_uploads = $notification['includeUploads'];

						foreach ( $notification['addresses'] as $address ) {

							if ( ! empty( $address['email'] ) || ! empty( $address['field'] ) ) {

								$email = '';

								if ( 'custom' === $address['type'] ) {
									$email = $address['email'];
								} else {
									$email_field = $address['field'];

									if ( ! empty( $email_field ) && ! empty( $submission[ $email_field ] ) ) {
										if ( is_array( $submission[ $email_field ] ) && ! empty( $submission[ $email_field ]['confirm'] ) ) {
											$email = $submission[ $email_field ]['confirm'];
										} else {
											$email = $submission[ $email_field ];
										}
									}
								}

								if ( ! empty( $email ) ) {
									if ( empty( $notification_content ) ) {
										$notification_content = ' '; // Hack to send email with empty body via PHPMailer
									}

									$subject = apply_filters( 'ccf_email_subject', $subject, $form_id, $email, $form_page, $notification );
									$notification_content = apply_filters( 'ccf_email_content', $message, $form_id, $email, $form_page, $notification );
									$notification_headers = apply_filters( 'ccf_email_headers', $headers, $form_id, $email, $form_page, $notification );

									if ( ! $include_uploads ) {
										$uploads = array();
									}

									$uploads = apply_filters( 'ccf_email_uploads', $uploads, $headers, $form_id, $email, $form_page, $file_ids, $notification );

									do_action( 'ccf_send_notification', $email, $subject, $notification_content, $notification_headers, $uploads, $notification );

									wp_mail( $email, $subject, $notification_content, $notification_headers, $uploads );
								}
							}
						}
					}
				}
			}

			if ( 'redirect' === $output['action_type'] ) {
				$output['completion_redirect_url'] = apply_filters( 'ccf_form_completion_redirect_url', get_post_meta( $form_id, 'ccf_form_completion_redirect_url', true ), $form_id );
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
	 * @param int    $field_id
	 * @param string $value
	 * @since 6.0
	 * @return array
	 */
	public function process_field( $field_id, $value ) {
		$return = array(
			'error' => null,
			'sanitized_value' => null,
		);

		$type = get_post_meta( $field_id, 'ccf_field_type', true );
		$required = get_post_meta( $field_id, 'ccf_field_required', true );

		$callback = ( ! empty( $this->field_callbacks[ $type ]['validator'] ) ) ? $this->field_callbacks[ $type ]['validator'] : null;
		$validator = apply_filters( 'ccf_field_validator', $callback, $value, $field_id, $type );

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
					if ( ! empty( $this->field_callbacks[ $type ]['sanitizer'] ) ) {
						$return['sanitized_value'][ $key ] = call_user_func( apply_filters( 'ccf_field_sanitizer', $this->field_callbacks[ $type ]['sanitizer'], $single_value, $field_id, $type ), $single_value, $field_id );
					}
				}
			} else {
				if ( ! empty( $this->field_callbacks[ $type ]['sanitizer'] ) ) {
					$return['sanitized_value'] = call_user_func( apply_filters( 'ccf_field_sanitizer', $this->field_callbacks[ $type ]['sanitizer'], $value, $field_id, $type ), $value, $field_id );
				}
			}
		}

		return $return;
	}

	/**
	 * Flatten and concatentate potential array
	 *
	 * @since 7.3
	 */
	public function _flatten_and_concat( $value, $delim = ' ' ) {
		if ( is_string( $value ) ) {
			return $value;
		}

		$output = '';

		if ( is_array( $value ) ) {
			foreach ( $value as $v ) {
				if ( '' !== $output ) {
					$output .= $delim;
				}

				$output .= $v;
			}
		}

		return $output;
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
