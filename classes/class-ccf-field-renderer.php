<?php

class CCF_Field_Renderer {

	/**
	 * Placeholder method
	 *
	 * @since 6.0
	 */
	public function __construct() {}

	/**
	 * Keep track of sections
	 */
	public $section_open = false;

	/**
	 * Get single-line-text field HTML, including any errors from the last form submission. if there is an
	 * error the field will remember it's last submitted value.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function single_line_text( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		$value = get_post_meta( $field_id, 'ccf_field_value', true );
		$placeholder = get_post_meta( $field_id, 'ccf_field_placeholder', true );
		$required = get_post_meta( $field_id, 'ccf_field_required', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );
		$all_errors = CCF_Form_Handler::factory()->get_errors( $form_id );

		if ( ! empty( $all_errors ) ) {
			if ( apply_filters( 'ccf_show_last_field_value', true, $field_id ) ) {
				if ( ! empty( $_POST[ 'ccf_field_' . $slug ] ) ) {
					$post_value = $_POST[ 'ccf_field_' . $slug ];
				}
			}
		}

		ob_start();
		?>

		<div data-field-type="single-line-text" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-single-line-text field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<?php if ( ! empty( $required ) ) : ?><span class="required">*</span><?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</label>
			<input class="form-control <?php if ( ! empty( $errors ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> type="text" name="ccf_field_<?php echo esc_attr( $slug ); ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php if ( ! empty( $post_value ) ) { echo esc_attr( $post_value ); } else { echo esc_attr( $value ); } ?>">

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $errors ) ) : ?>
				<div class="error"><?php echo esc_html( $errors['required'] ); ?></div>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get file field HTML, including any errors from the last form submission. if there is an
	 * error the field will remember it's last submitted value.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.4
	 * @return string
	 */
	public function file( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		$value = get_post_meta( $field_id, 'ccf_field_value', true );
		$placeholder = get_post_meta( $field_id, 'ccf_field_placeholder', true );
		$required = get_post_meta( $field_id, 'ccf_field_required', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$max_file_size = get_post_meta( $field_id, 'ccf_field_maxFileSize', true );
		$file_extensions = get_post_meta( $field_id, 'ccf_field_fileExtensions', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );
		$all_errors = CCF_Form_Handler::factory()->get_errors( $form_id );

		if ( ! empty( $all_errors ) ) {
			if ( apply_filters( 'ccf_show_last_field_value', true, $field_id ) ) {
				if ( ! empty( $_POST[ 'ccf_field_' . $slug ] ) ) {
					$post_value = $_POST[ 'ccf_field_' . $slug ];
				}
			}
		}

		$max_upload_size = wp_max_upload_size();
		if ( ! $max_upload_size ) {
			$max_upload_size = 0;
		}

		$formatted_file_size = size_format( $max_upload_size );

		if ( $max_file_size ) {
			$formatted_file_size = $max_file_size;
		}

		ob_start();
		?>

		<div data-max-file-size="<?php echo esc_attr( $max_file_size ); ?>" data-file-extensions="<?php echo esc_attr( $file_extensions ); ?>" data-field-type="file" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-file field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<?php if ( ! empty( $required ) ) : ?><span class="required">*</span><?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</label>

			<input class="form-control <?php if ( ! empty( $errors ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> type="file" name="ccf_field_<?php echo esc_attr( $slug ); ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php if ( ! empty( $post_value ) ) { echo esc_attr( $post_value );
} else { echo esc_attr( $value ); } ?>" accept="<?php echo esc_attr( preg_replace( '/([^,\s]+)/', '.$1', $file_extensions ) ); ?>">

			<div class="field-description help-block text-muted">
				<?php if ( ! empty( $file_extensions ) ) : ?>
					<?php echo sprintf( esc_html__( 'Allowed file extensions are %s. ', 'custom-contact-forms' ), implode( ', ', explode( ',', str_replace( ' ', '', $file_extensions ) ) ) ); ?>
				<?php endif; ?>
				<?php echo sprintf( esc_html__( 'Max file size is %d MB. ', 'custom-contact-forms' ), (int) $formatted_file_size ); ?>
				<?php echo esc_html( $description ); ?>
			</div>

			<?php if ( ! empty( $errors ) ) : ?>
				<?php foreach ( $errors as $error ) : ?>
					<div class="error"><?php echo esc_html( $error ); ?></div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get reCAPTCHA field HTML, including any errors from the last form submission.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6..2
	 * @return string
	 */
	public function recaptcha( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$site_key = get_post_meta( $field_id, 'ccf_field_siteKey', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );

		ob_start();
		?>

		<div data-field-type="recaptcha" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-recaptcha field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<span class="required">*</span>
				<?php echo esc_html( $label ); ?>
			</label>
			<div class="ccf-recaptcha-wrapper" data-form-id="<?php echo (int) $form_id; ?>" data-sitekey="<?php echo esc_attr( $site_key ); ?>"></div>

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $errors ) ) : ?>
				<div class="error"><?php echo esc_html( $errors['recaptcha'] ); ?></div>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get somple CAPTCHA field HTML, including any errors from the last form submission.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6..2
	 * @return string
	 */
	public function simple_captcha( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$placeholder = get_post_meta( $field_id, 'ccf_field_placeholder', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );

		require_once( dirname( __FILE__ ) . '/../vendor/abeautifulsite/simple-php-captcha/simple-php-captcha.php' );

		$_SESSION['ccf_simple_captcha_' . $slug] = simple_php_captcha();

		ob_start();
		?>

		<div data-field-type="simple-captcha" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-simple-captcha field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<span class="required">*</span>
				<?php echo esc_html( $label ); ?>
			</label>
			<div class="ccf-simple-captcha-wrapper">
				<img src="<?php echo esc_url( $_SESSION['ccf_simple_captcha_' . $slug]['image_src'] ); ?>">
			</div>

			<input class="form-control <?php if ( ! empty( $errors ) ) : ?>field-error-input<?php endif; ?> field-input" required aria-required="true" type="text" name="ccf_field_<?php echo esc_attr( $slug ); ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>">

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $errors ) ) : ?>
				<div class="error"><?php echo esc_html( $errors['simple_captcha'] ); ?></div>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get section header layout field HTML
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function section_header( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$heading = get_post_meta( $field_id, 'ccf_field_heading', true );
		$subheading = get_post_meta( $field_id, 'ccf_field_subheading', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );

		ob_start();

		if ( $this->section_open ) {
			echo '</div>';
		}

		?>

		<div class="ccf-section">

			<div data-field-type="section-header" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="field skip-field <?php echo esc_attr( $slug ); ?> field-type-section-header field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?>">
				<div class="heading">
					<?php echo esc_html( $heading ); ?>
				</div>
				<div class="subheading">
					<?php echo esc_html( $subheading ); ?>
				</div>
			</div>

		<?php

		$this->section_open = true;


		return ob_get_clean();
	}

	/**
	 * Get html layout field HTML
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function html( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$html = get_post_meta( $field_id, 'ccf_field_html', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );

		ob_start();
		?>

		<div data-field-type="html" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="field skip-field <?php echo esc_attr( $slug ); ?> field-type-html field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?>">
			<?php echo wp_kses_post( $html ); ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get meta info for a given choice
	 *
	 * @param int $choice_id
	 * @since 6.0
	 * @return array
	 */
	private function get_choice( $choice_id ) {
		$choice = array(
			'value' => get_post_meta( $choice_id, 'ccf_choice_value', true ),
			'label' => get_post_meta( $choice_id, 'ccf_choice_label', true ),
			'selected' => get_post_meta( $choice_id, 'ccf_choice_selected', true ),
		);

		return $choice;
	}

	/**
	 * Get dropdown field HTML, including any errors from the last form submission. if there is an error the
	 * field will remember it's last submitted value.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function dropdown( $field_id, $form_id ) {
		$choice_ids = get_post_meta( $field_id, 'ccf_attached_choices', true );

		$choices = array();
		$selected = 0;
		if ( ! empty( $choice_ids ) ) {
			foreach ( $choice_ids as $choice_id ) {
				$choices[ $choice_id ] = $this->get_choice( $choice_id );

				if ( ! empty( $choices[ $choice_id ]['selected'] ) ) {
					$selected++;
				}
			}
		}

		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$required = get_post_meta( $field_id, 'ccf_field_required', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$use_values = get_post_meta( $field_id, 'ccf_field_useValues', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );
		$all_errors = CCF_Form_Handler::factory()->get_errors( $form_id );

		if ( ! empty( $all_errors ) ) {
			if ( apply_filters( 'ccf_show_last_field_value', true, $field_id ) ) {
				if ( ! empty( $_POST[ 'ccf_field_' . $slug ] ) ) {
					$post_value = $_POST[ 'ccf_field_' . $slug ];
				}
			}
		}

		ob_start();
		?>

		<div data-field-type="dropdown" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-dropdown field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<?php if ( ! empty( $required ) ) : ?><span class="required">*</span><?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</label>
			<select class="form-control <?php if ( ! empty( $errors ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> <?php if ( $selected > 1 ) : ?>multiple<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<?php foreach ( $choices as $choice ) :
					$selected = '';
					if ( ! empty( $post_value ) ) {
						if ( $choice['value'] == $post_value ) {
							$selected = 'selected';
						}
					} else {
						if ( ! empty( $choice['selected'] ) ) {
							$selected = 'selected';
						}
					}

					$value = $choice['value'];

					if ( empty( $use_values ) ) {
						$value = $choice['label'];
					}

					?>
					<option <?php echo $selected; ?> value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $choice['label'] ); ?></option>
				<?php endforeach; ?>
			</select>

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $errors['required'] ) ) : ?>
				<div class="error"><?php echo esc_html( $errors['required'] ); ?></div>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get checkboxes field HTML, including any errors from the last form submission. if there is an error the
	 * field will remember it's last submitted value.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function checkboxes( $field_id, $form_id ) {
		$choice_ids = get_post_meta( $field_id, 'ccf_attached_choices', true );

		$choices = array();
		$selected = 0;
		if ( ! empty( $choice_ids ) ) {
			foreach ( $choice_ids as $choice_id ) {
				$choices[ $choice_id ] = $this->get_choice( $choice_id );

				if ( ! empty( $choices[ $choice_id ]['selected'] ) ) {
					$selected++;
				}
			}
		}

		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		;
		$required = get_post_meta( $field_id, 'ccf_field_required', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );
		$all_errors = CCF_Form_Handler::factory()->get_errors( $form_id );

		if ( ! empty( $all_errors ) ) {
			if ( apply_filters( 'ccf_show_last_field_value', true, $field_id ) ) {
				if ( ! empty( $_POST[ 'ccf_field_' . $slug ] ) ) {
					$post_value = $_POST[ 'ccf_field_' . $slug ];
				}
			}
		}

		ob_start();
		?>

		<div data-field-type="checkboxes" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-checkboxes field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<?php if ( ! empty( $required ) ) : ?><span class="required">*</span><?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</label>
			<?php foreach ( $choices as $choice ) :
				$checked = '';
				if ( ! empty( $post_value ) ) {
					if ( in_array( $choice['value'], $post_value ) ) {
						$checked = 'checked';
					}
				} else {
					if ( ! empty( $choice['selected'] ) ) {
						$checked = 'checked';
					}
				}
				?>
				<div class="choice checkbox">
					<label><input class="field-input" name="ccf_field_<?php echo esc_attr( $slug ); ?>[]" type="checkbox" <?php echo $checked; ?> value="<?php echo esc_attr( $choice['value'] ); ?>"> <span><?php echo esc_html( $choice['label'] ); ?></span></label>
				</div>
			<?php endforeach; ?>

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $errors ) ) : ?>
				<div class="error"><?php echo esc_html( $errors['required'] ); ?></div>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get radio field HTML, including any errors from the last form submission. if there is an error the
	 * field will remember it's last submitted value.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function radio( $field_id, $form_id ) {
		$choice_ids = get_post_meta( $field_id, 'ccf_attached_choices', true );

		$choices = array();
		$selected = 0;
		if ( ! empty( $choice_ids ) ) {
			foreach ( $choice_ids as $choice_id ) {
				$choices[ $choice_id ] = $this->get_choice( $choice_id );

				if ( ! empty( $choices[ $choice_id ]['selected'] ) ) {
					$selected++;
				}
			}
		}

		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		;
		$required = get_post_meta( $field_id, 'ccf_field_required', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );
		$all_errors = CCF_Form_Handler::factory()->get_errors( $form_id );

		if ( ! empty( $all_errors ) ) {
			if ( apply_filters( 'ccf_show_last_field_value', true, $field_id ) ) {
				if ( ! empty( $_POST[ 'ccf_field_' . $slug ] ) ) {
					$post_value = $_POST[ 'ccf_field_' . $slug ];
				}
			}
		}

		ob_start();
		?>

		<div data-field-type="radio" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field field-type-radio <?php echo esc_attr( $slug ); ?> field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<?php if ( ! empty( $required ) ) : ?><span class="required">*</span><?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</label>
			<?php foreach ( $choices as $choice ) :
				$checked = '';
				if ( ! empty( $post_value ) ) {
					if ( $choice['value'] == $post_value ) {
						$checked = 'checked';
					}
				} else {
					if ( ! empty( $choice['selected'] ) ) {
						$checked = 'checked';
					}
				}
				?>
				<div class="choice radio">
					<label><input class="field-input" name="ccf_field_<?php echo esc_attr( $slug ); ?>" type="radio" <?php echo $checked; ?> value="<?php echo esc_attr( $choice['value'] ); ?>"> <span><?php echo esc_html( $choice['label'] ); ?></span></label>
				</div>
			<?php endforeach; ?>

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $errors ) ) : ?>
				<div class="error"><?php echo esc_html( $errors['required'] ); ?></div>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get address field HTML, including any errors from the last form submission. if there is an error the
	 * field will remember it's last submitted value.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function address( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		$address_type = get_post_meta( $field_id, 'ccf_field_addressType', true );
		$required = get_post_meta( $field_id, 'ccf_field_required', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );
		$default_country = get_post_meta( $field_id, 'ccf_field_defaultCountry', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );
		$all_errors = CCF_Form_Handler::factory()->get_errors( $form_id );

		if ( ! empty( $all_errors ) ) {
			if ( apply_filters( 'ccf_show_last_field_value', true, $field_id ) ) {
				if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['street'] ) ) {
					$street_post_value = $_POST[ 'ccf_field_' . $slug ]['street'];
				}

				if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['line_two'] ) ) {
					$line_two_post_value = $_POST[ 'ccf_field_' . $slug ]['line_two'];
				}

				if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['city'] ) ) {
					$city_post_value = $_POST[ 'ccf_field_' . $slug ]['city'];
				}

				if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['state'] ) ) {
					$state_post_value = $_POST[ 'ccf_field_' . $slug ]['state'];
				}

				if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['country'] ) ) {
					$country_post_value = $_POST[ 'ccf_field_' . $slug ]['country'];
				}

				if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['zipcode'] ) ) {
					$zipcode_post_value = $_POST[ 'ccf_field_' . $slug ]['zipcode'];
				}
			}
		}

		ob_start();
		?>

		<div data-field-type="address" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-address field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<?php if ( ! empty( $required ) ) : ?><span class="required">*</span><?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</label>
			<div class="full">
				<input value="<?php if ( ! empty( $street_post_value ) ) { echo esc_attr( $street_post_value ); } ?>" class="form-control <?php if ( ! empty( $errors['street_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> id="ccf_field_<?php echo esc_attr( $slug ); ?>-street" type="text" name="ccf_field_<?php echo esc_attr( $slug ); ?>[street]">
				<?php if ( ! empty( $errors['street_required'] ) ) : ?>
					<div class="error"><?php echo esc_html( $errors['street_required'] ); ?></div>
				<?php endif; ?>
				<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-street" class="sub-label help-block text-muted"><?php esc_html_e( 'Street Address', 'custom-contact-forms' ); ?></label>
			</div>
			<div class="full">
				<input value="<?php if ( ! empty( $line_two_post_value ) ) { echo esc_attr( $line_two_post_value ); } ?>" class="form-control  field-input" id="ccf_field_<?php echo esc_attr( $slug ); ?>-line_two" type="text" name="ccf_field_<?php echo esc_attr( $slug ); ?>[line_two]">
				<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-line_two" class="sub-label help-block text-muted"><?php esc_html_e( 'Address Line 2', 'custom-contact-forms' ); ?></label>
			</div>
			<div class="left">
				<input value="<?php if ( ! empty( $city_post_value ) ) { echo esc_attr( $city_post_value ); } ?>" class="form-control <?php if ( ! empty( $errors['city_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> type="text" name="ccf_field_<?php echo esc_attr( $slug ); ?>[city]" id="ccf_field_<?php echo esc_attr( $slug ); ?>-city">
				<?php if ( ! empty( $errors['city_required'] ) ) : ?>
					<div class="error"><?php echo esc_html( $errors['city_required'] ); ?></div>
				<?php endif; ?>
				<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-city" class="sub-label help-block text-muted"><?php esc_html_e( 'City', 'custom-contact-forms' ); ?></label>

			</div>
			<?php if ( $address_type === 'us' ) { ?>
				<div class="right">
					<select class="<?php if ( ! empty( $errors['state_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>[state]" id="ccf_field_<?php echo esc_attr( $slug ); ?>-state">
						<?php foreach ( CCF_Constants::factory()->get_us_states() as $state ) : ?>
							<option <?php if ( ! empty( $street_post_value ) ) { selected( $street_post_value, $state ); } ?>><?php echo $state; ?></option>
						<?php endforeach; ?>
					</select>
					<?php if ( ! empty( $errors['state_required'] ) ) : ?>
						<div class="error"><?php echo esc_html( $errors['state_required'] ); ?></div>
					<?php endif; ?>
					<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-state" class="sub-label help-block text-muted"><?php esc_html_e( 'State', 'custom-contact-forms' ); ?></label>

				</div>
				<div class="left">
					<input value="<?php if ( ! empty( $zipcode_post_value ) ) { echo esc_attr( $zipcode_post_value ); } ?>" class="form-control <?php if ( ! empty( $errors['zipcode_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> type="text" name="ccf_field_<?php echo esc_attr( $slug ); ?>[zipcode]" id="ccf_field_<?php echo esc_attr( $slug ); ?>-zipcode">
					<?php if ( ! empty( $errors['zipcode_required'] ) ) : ?>
						<div class="error"><?php echo esc_html( $errors['zipcode_required'] ); ?></div>
					<?php endif; ?>
					<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-zipcode" class="sub-label help-block text-muted"><?php esc_html_e( 'ZIP Code', 'custom-contact-forms' ); ?></label>

				</div>
				<div class="ccf-clear"></div>
			<?php } else if ( $address_type === 'international' ) { ?>
				<div class="right">
					<input value="<?php if ( ! empty( $state_post_value ) ) { echo esc_attr( $state_post_value ); } ?>" class="form-control <?php if ( ! empty( $errors['state_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> type="text" name="ccf_field_<?php echo esc_attr( $slug ); ?>[state]" id="ccf_field_<?php echo esc_attr( $slug ); ?>-state">
					<?php if ( ! empty( $errors['state_required'] ) ) : ?>
						<div class="error"><?php echo esc_html( $errors['state_required'] ); ?></div>
					<?php endif; ?>
					<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-state" class="sub-label help-block text-muted"><?php esc_html_e( 'State / Region / Province', 'custom-contact-forms' ); ?></label>

				</div>
				<div class="left">
					<input value="<?php if ( ! empty( $zipcode_post_value ) ) { echo esc_attr( $zipcode_post_value ); } ?>" class="form-control <?php if ( ! empty( $errors['zipcode_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> type="text" name="ccf_field_<?php echo esc_attr( $slug ); ?>[zipcode]" id="ccf_field_<?php echo esc_attr( $slug ); ?>-zipcode">
					<?php if ( ! empty( $errors['zipcode_required'] ) ) : ?>
						<div class="error"><?php echo esc_html( $errors['zipcode_required'] ); ?></div>
					<?php endif; ?>
					<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-zipcode" class="sub-label help-block text-muted"><?php esc_html_e( 'ZIP / Postal Code', 'custom-contact-forms' ); ?></label>

				</div>
				<div class="right">
					<select class="<?php if ( ! empty( $errors['country_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>[country]" id="ccf_field_<?php echo esc_attr( $slug ); ?>-country">
						<?php foreach ( CCF_Constants::factory()->get_countries() as $country ) : ?>
							<option <?php if ( $country === $default_country ) : ?>selected<?php endif; ?> <?php if ( ! empty( $country_post_value ) ) { selected( $country_post_value, $country ); } ?>><?php echo $country; ?></option>
						<?php endforeach; ?>
					</select>
					<?php if ( ! empty( $errors['country_required'] ) ) : ?>
						<div class="error"><?php echo esc_html( $errors['country_required'] ); ?></div>
					<?php endif; ?>
					<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-country" class="sub-label help-block text-muted"><?php esc_html_e( 'Country', 'custom-contact-forms' ); ?></label>

				</div>
				<div class="ccf-clear"></div>
			<?php } ?>

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get phone field HTML, including any errors from the last form submission. if there is an error the
	 * field will remember it's last submitted value.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function phone( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		$value = get_post_meta( $field_id, 'ccf_field_value', true );
		$placeholder = get_post_meta( $field_id, 'ccf_field_placeholder', true );
		$required = get_post_meta( $field_id, 'ccf_field_required', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$phone_format = get_post_meta( $field_id, 'ccf_field_phoneFormat', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );
		$all_errors = CCF_Form_Handler::factory()->get_errors( $form_id );

		if ( ! empty( $all_errors ) ) {
			if ( apply_filters( 'ccf_show_last_field_value', true, $field_id ) ) {
				if ( ! empty( $_POST[ 'ccf_field_' . $slug ] ) ) {
					$post_value = $_POST[ 'ccf_field_' . $slug ];
				}
			}
		}

		ob_start();
		?>

		<div data-phone-format="<?php echo esc_attr( $phone_format ); ?>" data-field-slug="<?php echo esc_attr( $slug ); ?>" data-field-type="phone" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-phone field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<?php if ( ! empty( $required ) ) : ?><span class="required">*</span><?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</label>
			<input class="form-control <?php if ( ! empty( $errors ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> type="text" name="ccf_field_<?php echo esc_attr( $slug ); ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php if ( ! empty( $post_value ) ) { echo esc_attr( $post_value );
} else { echo esc_attr( $value ); } ?>">

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $errors ) ) : ?>
				<?php foreach ( $errors as $error ) : ?>
					<div class="error"><?php echo esc_html( $error ); ?></div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get website field HTML, including any errors from the last form submission. if there is an error the
	 * field will remember it's last submitted value.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function website( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		$value = get_post_meta( $field_id, 'ccf_field_value', true );
		$placeholder = get_post_meta( $field_id, 'ccf_field_placeholder', true );
		$required = get_post_meta( $field_id, 'ccf_field_required', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );
		$all_errors = CCF_Form_Handler::factory()->get_errors( $form_id );

		if ( ! empty( $all_errors ) ) {
			if ( apply_filters( 'ccf_show_last_field_value', true, $field_id ) ) {
				if ( ! empty( $_POST[ 'ccf_field_' . $slug ] ) ) {
					$post_value = $_POST[ 'ccf_field_' . $slug ];
				}
			}
		}

		ob_start();
		?>

		<div data-field-type="website" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-website field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<?php if ( ! empty( $required ) ) : ?><span class="required">*</span><?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</label>
			<input class="form-control <?php if ( ! empty( $errors ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> type="text" name="ccf_field_<?php echo esc_attr( $slug ); ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php if ( ! empty( $post_value ) ) { echo esc_attr( $post_value );
} else { echo esc_attr( $value ); } ?>">

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $errors ) ) : foreach ( $errors as $error ) : ?>
				<div class="error"><?php echo esc_html( $error ); ?></div>
			<?php endforeach;
endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get email field HTML, including any errors from the last form submission. if there is an error the
	 * field will remember it's last submitted value.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function email( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		$value = get_post_meta( $field_id, 'ccf_field_value', true );
		$placeholder = get_post_meta( $field_id, 'ccf_field_placeholder', true );
		$email_confirmation = get_post_meta( $field_id, 'ccf_field_emailConfirmation', true );
		$required = get_post_meta( $field_id, 'ccf_field_required', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );
		$all_errors = CCF_Form_Handler::factory()->get_errors( $form_id );

		if ( ! empty( $all_errors ) ) {
			if ( apply_filters( 'ccf_show_last_field_value', true, $field_id ) ) {
				if ( ! empty( $email_confirmation ) ) {
					if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['email'] ) ) {
						$email_post_value = $_POST[ 'ccf_field_' . $slug ]['email'];
					}

					if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['confirm'] ) ) {
						$confirm_post_value = $_POST[ 'ccf_field_' . $slug ]['confirm'];
					}
				} else {
					$email_post_value = $_POST[ 'ccf_field_' . $slug ];
				}
			}
		}

		ob_start();
		?>

		<div data-field-type="email" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-email field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<?php if ( ! empty( $required ) ) : ?><span class="required">*</span><?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</label>
			<?php if ( empty( $email_confirmation ) ) { ?>
				<input class="form-control <?php if ( ! empty( $errors ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>"  placeholder="<?php if ( ! empty( $placeholder ) ) { ?><?php echo esc_attr( $placeholder ) ?><?php } else { ?><?php esc_html_e( 'email@example.com', 'custom-contact-forms' ); ?><?php } ?>" type="text" value="<?php if ( ! empty( $email_post_value ) ) { echo esc_attr( $email_post_value );
} else { echo esc_attr( $value ); } ?>">
				<?php if ( ! empty( $errors ) ) : foreach ( $errors as $error ) : ?>
					<div class="error"><?php echo esc_html( $error ); ?></div>
				<?php endforeach;
endif; ?>
			<?php } else { ?>
				<div class="left">
					<input class="form-control field-input <?php if ( ! empty( $errors['email_required'] ) || ! empty( $errors['match'] ) || ! empty( $errors['email'] ) ) : ?>field-error-input<?php endif; ?>" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>[email]" id="ccf_field_<?php echo esc_attr( $slug ); ?>" value="<?php if ( ! empty( $email_post_value ) ) { echo esc_attr( $email_post_value ); }?>" type="text">
					<?php if ( ! empty( $errors['email_required'] ) ) : ?>
						<div class="error"><?php echo esc_html( $errors['email_required'] ); ?></div>
					<?php endif; ?>
					<label for="ccf_field_<?php echo esc_attr( $slug ); ?>" class="sub-label help-block text-muted"><?php esc_html_e( 'Email', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="right">
					<input class="form-control field-input <?php if ( ! empty( $errors['confirm_required'] ) || ! empty( $errors['match'] ) || ! empty( $errors['email'] ) ) : ?>field-error-input<?php endif; ?>" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>[confirm]" id="ccf_field_<?php echo esc_attr( $slug ); ?>-confirm" value="<?php if ( ! empty( $confirm_post_value ) ) { echo esc_attr( $confirm_post_value ); } ?>" type="text">
					<?php if ( ! empty( $errors['confirm_required'] ) ) : ?>
						<div class="error"><?php echo esc_html( $errors['confirm_required'] ); ?></div>
					<?php endif; ?>
					<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-confirm" class="sub-label help-block text-muted"><?php esc_html_e( 'Confirm Email', 'custom-contact-forms' ); ?></label>
				</div>
				<?php if ( ! empty( $errors['match'] ) ) : ?>
					<div class="error"><?php echo esc_html( $errors['match'] ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $errors['email'] ) ) : ?>
					<div class="error"><?php echo esc_html( $errors['email'] ); ?></div>
				<?php endif; ?>
				<div class="ccf-clear"></div>
			<?php } ?>

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get name field HTML, including any errors from the last form submission. if there is an error the
	 * field will remember it's last submitted value.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function name( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		$required = get_post_meta( $field_id, 'ccf_field_required', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );
		$all_errors = CCF_Form_Handler::factory()->get_errors( $form_id );

		if ( ! empty( $all_errors ) ) {
			if ( apply_filters( 'ccf_show_last_field_value', true, $field_id ) ) {
				if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['first'] ) ) {
					$first_post_value = $_POST[ 'ccf_field_' . $slug ]['first'];
				}

				if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['last'] ) ) {
					$last_post_value = $_POST[ 'ccf_field_' . $slug ]['last'];
				}
			}
		}

		ob_start();
		?>

		<div data-field-type="name" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-name field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label">
				<?php if ( ! empty( $required ) ) : ?><span class="required">*</span><?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</label>
			<div class="left">
				<input value="<?php if ( ! empty( $first_post_value ) ) { echo esc_attr( $first_post_value ); } ?>" class="form-control <?php if ( ! empty( $errors['first_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> type="text" name="ccf_field_<?php echo esc_attr( $slug ); ?>[first]" id="ccf_field_<?php echo esc_attr( $slug ); ?>-first">
				<?php if ( ! empty( $errors['first_required'] ) ) : ?>
					<div class="error"><?php echo esc_html( $errors['first_required'] ); ?></div>
				<?php endif; ?>
				<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-first" class="sub-label help-block text-muted"><?php esc_html_e( 'First', 'custom-contact-forms' ); ?></label>
			</div>
			<div class="right">
				<input value="<?php if ( ! empty( $last_post_value ) ) { echo esc_attr( $last_post_value ); } ?>" class="form-control <?php if ( ! empty( $errors['last_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> type="text" name="ccf_field_<?php echo esc_attr( $slug ); ?>[last]" id="ccf_field_<?php echo esc_attr( $slug ); ?>-last">
				<?php if ( ! empty( $errors['last_required'] ) ) : ?>
					<div class="error"><?php echo esc_html( $errors['last_required'] ); ?></div>
				<?php endif; ?>
				<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-last" class="sub-label help-block text-muted"><?php esc_html_e( 'Last', 'custom-contact-forms' ); ?></label>
			</div>

			<div class="ccf-clear"></div>

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get date field HTML, including any errors from the last form submission. if there is an error the
	 * field will remember it's last submitted value.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function date( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		$required = get_post_meta( $field_id, 'ccf_field_required', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$show_date = get_post_meta( $field_id, 'ccf_field_showDate', true );
		$show_time = get_post_meta( $field_id, 'ccf_field_showTime', true );
		$date_format = get_post_meta( $field_id, 'ccf_field_dateFormat', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );
		$all_errors = CCF_Form_Handler::factory()->get_errors( $form_id );

		$value = get_post_meta( $field_id, 'ccf_field_value', true );

		if ( ! empty( $all_errors ) ) {
			if ( apply_filters( 'ccf_show_last_field_value', true, $field_id ) ) {
				if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['date'] ) ) {
					$date_post_value = $_POST[ 'ccf_field_' . $slug ]['date'];
				}

				if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['hour'] ) ) {
					$hour_post_value = $_POST[ 'ccf_field_' . $slug ]['hour'];
				}

				if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['minute'] ) ) {
					$minute_post_value = $_POST[ 'ccf_field_' . $slug ]['minute'];
				}

				if ( ! empty( $_POST[ 'ccf_field_' . $slug ]['am-pm'] ) ) {
					$am_pm_post_value = $_POST[ 'ccf_field_' . $slug ]['am-pm'];
				}
			}
		}

		ob_start();
		?>

		<div data-field-type="date" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-date field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<?php if ( ! empty( $required ) ) : ?><span class="required">*</span><?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</label>
			<?php if ( ! empty( $show_date ) && empty( $show_time ) ) { ?>
				<input data-date-format="<?php echo esc_attr( $date_format ); ?>" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>[date]" value="<?php if ( ! empty( $date_post_value ) ) { echo esc_attr( $date_post_value );
} else { echo esc_attr( $value ); } ?>" class="form-control <?php if ( ! empty( $errors ) ) : ?>field-error-input<?php endif; ?> ccf-datepicker field-input" id="ccf_field_<?php echo esc_attr( $slug ); ?>" type="text">
			<?php } else if ( empty( $show_date ) && ! empty( $show_time ) ) { ?>
				<div class="hour">
					<input maxlength="2" class="form-control <?php if ( ! empty( $errors['hour_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>[hour]" value="<?php if ( ! empty( $hour_post_value ) ) { echo esc_attr( $hour_post_value ); } ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>-hour" type="text">
					<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-hour" class="sub-label help-block text-muted"><?php esc_html_e( 'HH', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="minute">
					<input maxlength="2" class="form-control <?php if ( ! empty( $errors['minutes_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>[minute]" value="<?php if ( ! empty( $minute_post_value ) ) { echo esc_attr( $minute_post_value ); } ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>-minute" type="text">
					<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-minute" class="sub-label help-block text-muted"><?php esc_html_e( 'MM', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="am-pm">
					<select class="<?php if ( ! empty( $errors['am-pm_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>[am-pm]" id="ccf_field_<?php echo esc_attr( $slug ); ?>-am-pm">
						<option <?php if ( ! empty( $am_pm_post_value ) ) { selected( 'am', $am_pm_post_value ); } ?> value="am"><?php esc_html_e( 'AM', 'custom-contact-forms' ); ?></option>
						<option <?php if ( ! empty( $am_pm_post_value ) ) { selected( 'pm', $am_pm_post_value ); } ?> value="pm"><?php esc_html_e( 'PM', 'custom-contact-forms' ); ?></option>
					</select>
				</div>
				<div class="ccf-clear"></div>
			<?php } else { ?>
				<div class="left">
					<input data-date-format="<?php echo esc_attr( $date_format ); ?>" value="<?php if ( ! empty( $date_post_value ) ) { echo esc_attr( $date_post_value ); } ?>" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>[date]" class="form-control <?php if ( ! empty( $errors['date_required'] ) ) : ?>field-error-input<?php endif; ?> ccf-datepicker field-input" id="ccf_field_<?php echo esc_attr( $slug ); ?>-date" type="text">
					<label for="ccf_field_<?php echo esc_attr( $slug ); ?>-date" class="sub-label help-block text-muted"><?php esc_html_e( 'Date', 'custom-contact-forms' ); ?></label>
				</div>
				<div class="right">
					<div class="hour">
						<input class="form-control <?php if ( ! empty( $errors['hour_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> maxlength="2" name="ccf_field_<?php echo esc_attr( $slug ); ?>[hour]" value="<?php if ( ! empty( $hour_post_value ) ) { echo esc_attr( $hour_post_value ); } ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>-hour" type="text">
						<label class="sub-label help-block text-muted" for="ccf_field_<?php echo esc_attr( $slug ); ?>-hour"><?php esc_html_e( 'HH', 'custom-contact-forms' ); ?></label>
					</div>
					<div class="minute">
						<input class="form-control <?php if ( ! empty( $errors['minutes_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> maxlength="2" name="ccf_field_<?php echo esc_attr( $slug ); ?>[minute]" value="<?php if ( ! empty( $minute_post_value ) ) { echo esc_attr( $minute_post_value ); } ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>-minute" type="text">
						<label class="sub-label help-block text-muted" for="ccf_field_<?php echo esc_attr( $slug ); ?>-minute"><?php esc_html_e( 'MM', 'custom-contact-forms' ); ?></label>
					</div>
					<div class="am-pm">
						<select class="<?php if ( ! empty( $errors['am-pm_required'] ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>[am-pm]" id="ccf_field_<?php echo esc_attr( $slug ); ?>-am-pm">
							<option <?php if ( ! empty( $am_pm_post_value ) ) { selected( 'am', $am_pm_post_value ); } ?> value="am"><?php esc_html_e( 'AM', 'custom-contact-forms' ); ?></option>
							<option <?php if ( ! empty( $am_pm_post_value ) ) { selected( 'pm', $am_pm_post_value ); } ?> value="pm"><?php esc_html_e( 'PM', 'custom-contact-forms' ); ?></option>
						</select>
					</div>
				</div>
				<div class="ccf-clear"></div>
			<?php } ?>

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $errors ) ) : foreach ( $errors as $error ) : ?>
				<div class="error"><?php echo esc_html( $error ); ?></div>
			<?php endforeach;
endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get paragraph-text field HTML, including any errors from the last form submission. if there is an
	 * error the field will remember it's last submitted value.
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function paragraph_text( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$label = get_post_meta( $field_id, 'ccf_field_label', true );
		$value = get_post_meta( $field_id, 'ccf_field_value', true );
		$placeholder = get_post_meta( $field_id, 'ccf_field_placeholder', true );
		$required = get_post_meta( $field_id, 'ccf_field_required', true );
		$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
		$description = get_post_meta( $field_id, 'ccf_field_description', true );

		$errors = CCF_Form_Handler::factory()->get_errors( $form_id, $slug );
		$all_errors = CCF_Form_Handler::factory()->get_errors( $form_id );

		if ( ! empty( $all_errors ) ) {
			if ( apply_filters( 'ccf_show_last_field_value', true, $field_id ) ) {
				if ( ! empty( $_POST[ 'ccf_field_' . $slug ] ) ) {
					$post_value = $_POST[ 'ccf_field_' . $slug ];
				}
			}
		}

		ob_start();
		?>

		<div data-field-type="paragraph-text" data-field-slug="<?php echo esc_attr( $slug ); ?>" class="form-group <?php if ( ! empty( $errors ) ) : ?>field-error has-error<?php endif; ?> field <?php echo esc_attr( $slug ); ?> field-type-paragraph-text field-<?php echo (int) $field_id; ?> <?php echo esc_attr( $class_name ); ?> <?php if ( ! empty( $required ) ) : ?>field-required<?php endif; ?>">
			<label class="main-label" for="ccf_field_<?php echo esc_attr( $slug ); ?>">
				<?php if ( ! empty( $required ) ) : ?><span class="required">*</span><?php endif; ?>
				<?php echo esc_html( $label ); ?>
			</label>
			<textarea class="form-control <?php if ( ! empty( $errors ) ) : ?>field-error-input<?php endif; ?> field-input" <?php if ( ! empty( $required ) ) : ?>required aria-required="true"<?php endif; ?> name="ccf_field_<?php echo esc_attr( $slug ); ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php if ( ! empty( $post_value ) ) { echo esc_attr( $post_value );
} else { echo esc_attr( $value ); } ?></textarea>

			<?php if ( ! empty( $description ) ) : ?>
				<div class="field-description help-block text-muted">
					<?php echo esc_html( $description ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $errors ) ) : ?>
				<div class="error"><?php echo esc_html( $errors['required'] ); ?></div>
			<?php endif; ?>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Get hidden field HTML
	 *
	 * @param int $field_id
	 * @param int $form_id
	 * @since 6.0
	 * @return string
	 */
	public function hidden( $field_id, $form_id ) {
		$slug = get_post_meta( $field_id, 'ccf_field_slug', true );
		$value = get_post_meta( $field_id, 'ccf_field_value', true );

		ob_start();
		?>

		<input type="hidden" name="ccf_field_<?php echo esc_attr( $slug ); ?>" id="ccf_field_<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $value ); ?>">

		<?php
		return ob_get_clean();
	}

	/**
	 * Route field rendering requests to field specific method and return html for given field
	 *
	 * @param string $type
	 * @param int    $field_id
	 * @param int    $form_id
	 * @since 6.0
	 * @return string
	 */
	public function render_router( $type, $field_id, $form_id ) {
		$field_html = '';

		switch ( $type ) {
			case 'single-line-text':
				$field_html = $this->single_line_text( $field_id, $form_id );
				break;
			case 'hidden':
				$field_html = $this->hidden( $field_id, $form_id );
				break;
			case 'paragraph-text':
				$field_html = $this->paragraph_text( $field_id, $form_id );
				break;
			case 'dropdown':
				$field_html = $this->dropdown( $field_id, $form_id );
				break;
			case 'checkboxes':
				$field_html = $this->checkboxes( $field_id, $form_id );
				break;
			case 'radio':
				$field_html = $this->radio( $field_id, $form_id );
				break;
			case 'recaptcha':
				$field_html = $this->recaptcha( $field_id, $form_id );
				break;
			case 'simple-captcha':
				$field_html = $this->simple_captcha( $field_id, $form_id );
				break;
			case 'html':
				$field_html = $this->html( $field_id, $form_id );
				break;
			case 'section-header':
				$field_html = $this->section_header( $field_id, $form_id );
				break;
			case 'name':
				$field_html = $this->name( $field_id, $form_id );
				break;
			case 'date':
				$field_html = $this->date( $field_id, $form_id );
				break;
			case 'file':
				$field_html = $this->file( $field_id, $form_id );
				break;
			case 'address':
				$field_html = $this->address( $field_id, $form_id );
				break;
			case 'website':
				$field_html = $this->website( $field_id, $form_id );
				break;
			case 'phone':
				$field_html = $this->phone( $field_id, $form_id );
				break;
			case 'email':
				$field_html = $this->email( $field_id, $form_id );
				break;
		}

		return $field_html;
	}
	
	/**
	 * Resets the instance defaults
	 */
	public function reset() {
		$this->section_open = false;
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
		}

		return $instance;
	}
}
