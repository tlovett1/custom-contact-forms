<?php

class CCF_Upgrader {

	/**
	 * Setup updater
	 *
	 * @since 6.1
	 */
	public function setup() {
		add_action( 'admin_init', array( $this, 'upgrade' ), 100 );
		add_action( 'admin_notices', array( $this, 'update_nag' ) );
	}

	/**
	 * Prompt user to do update.
	 *
	 * @since 6.1
	 */
	public function update_nag() {
		global $wpdb;

		$upgraded_forms = get_option( 'ccf_upgraded_forms', null );

		if ( null === $upgraded_forms ) {

			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}customcontactforms_forms'" ) === $wpdb->prefix . 'customcontactforms_forms' ) {

				$forms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}customcontactforms_forms" );

				if ( ! empty( $forms ) ) {
					$nonce = wp_create_nonce( 'ccf_upgrade' );

					?>
					<div class="update-nag">
						<p>
							<?php esc_html_e( 'Did you just upgrade to a post 6.0 version of Custom Contact Forms? If so, you might need to upgrade your database to use your old forms. Please backup your database before running the upgrade.', 'custom-contact-forms' ); ?>
							<a href="<?php echo esc_url( admin_url( '?ccf_upgrade=1&nonce=' . $nonce ) ); ?>" class="button"><?php esc_html_e( 'Upgrade', 'custom-contact-forms' ); ?></a>
							<a href="<?php echo esc_url( admin_url( '?ccf_upgrade=0&nonce=' . $nonce ) ); ?>" class="button"><?php esc_html_e( 'Dismiss', 'custom-contact-forms' ); ?></a>
						</p>
					</div>
					<?php
				} else {
					update_option( 'ccf_upgraded_forms', array() );
				}
			} else {
				update_option( 'ccf_upgraded_forms', array() );
			}
		}
	}

	/**
	 * Update completed message
	 *
	 * @since 6.1
	 */
	public function update_complete() {
		?>
		<div class="update-nag">
			<p>
				<?php esc_html_e( 'Database update complete.', 'custom-contact-forms' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Upgrade from old plugin version to 6.0+
	 *
	 * @since 6.0
	 */
	public function upgrade() {
		global $wpdb;

		if ( ! isset( $_GET['ccf_upgrade'] ) || ! isset( $_GET['nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['nonce'], 'ccf_upgrade' ) ) {
			return;
		}

		if ( empty( $_GET['ccf_upgrade'] ) ) {
			update_option( 'ccf_upgraded_forms', array() );
			return;
		}

		$forms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}customcontactforms_forms" );

		$upgraded_forms = get_option( 'ccf_upgraded_forms' );

		if ( empty( $upgraded_forms ) ) {
			$upgraded_forms = array();
		}

		$new_upgraded_forms = array();

		$type_mapping = array(
			'Dropdown' => 'dropdown',
			'Textarea' => 'paragraph-text',
			'Text' => 'single-line-text',
			'Checkbox' => 'checkboxes',
			'Radio' => 'radio',
			'fixedEmail' => 'email',
			'fixedWebsite' => 'website',
			'datePicker' => 'date',
		);

		foreach ( $forms as $form ) {
			$form_id = wp_insert_post( array(
				'post_type' => 'ccf_form',
				'post_title' => $form->form_title,
				'post_status' => 'publish',
			) );

			if ( is_wp_error( $form_id ) ) {
				continue;
			}

			update_post_meta( $form_id, 'ccf_old_mapped_id', (int) $form->id );

			$success_message = $form->form_success_message;
			$notification_email = $form->form_email;
			$submit_button_text = $form->submit_button_text;
			$redirect_url = $form->form_thank_you_page;

			update_post_meta( $form_id, 'ccf_form_buttonText', sanitize_text_field( $submit_button_text ) );
			update_post_meta( $form_id, 'ccf_form_email_notification_addresses', sanitize_text_field( $notification_email ) );
			update_post_meta( $form_id, 'ccf_form_completion_message', sanitize_text_field( $success_message ) );
			update_post_meta( $form_id, 'ccf_form_send_email_notifications', ( ! empty( $notification_email ) ) ? true : false );
			update_post_meta( $form_id, 'ccf_form_completion_redirect_url', esc_url_raw( $redirect_url ) );
			update_post_meta( $form_id, 'ccf_form_completion_action_type', ( ! empty( $redirect_url ) ) ? 'redirect' : 'text' );

			/**
			 * Move fields over
			 */

			$fields = unserialize( $form->form_fields );

			if ( ! empty( $fields ) ) {
				$form_fields = array();

				foreach ( $fields as $field_id ) {
					$field = $wpdb->get_row( sprintf( "SELECT * FROM {$wpdb->prefix}customcontactforms_fields WHERE ID='%d'", (int) $field_id ) );

					$type = $field->field_type;

					if ( ! empty( $type_mapping[ $type ] ) ) {
						$type = $type_mapping[ $type ];
					} else {
						continue;
					}

					$field_id = wp_insert_post( array(
						'post_type' => 'ccf_field',
						'post_title' => $form->form_title,
						'post_parent' => $form_id,
						'post_status' => 'publish',
					) );

					if ( ! is_wp_error( $field_id ) ) {
						$form_fields[] = $field_id;

						$slug = $field->field_slug;
						$label = $field->field_label;
						$required = $field->field_required;
						$class = $field->field_class;
						$value = $field->field_value;

						update_post_meta( $field_id, 'ccf_field_slug', sanitize_text_field( $slug ) );
						update_post_meta( $field_id, 'ccf_field_label', sanitize_text_field( $label ) );
						update_post_meta( $field_id, 'ccf_field_required', (bool) $required );
						update_post_meta( $field_id, 'ccf_field_type', sanitize_text_field( $type ) );
						update_post_meta( $field_id, 'ccf_field_className', sanitize_text_field( $class ) );
						update_post_meta( $field_id, 'ccf_field_value', sanitize_text_field( $value ) );

						if ( ( 'dropdown' === $type || 'radio' === $type || 'checkboxes' === $type ) && ! empty( $field->field_options ) ) {

							$choices = unserialize( $field->field_options );
							$new_choices = array();

							foreach ( $choices as $choice_id ) {
								$choice = $wpdb->get_row( sprintf( "SELECT * FROM {$wpdb->prefix}customcontactforms_field_options WHERE ID='%d'", (int) $choice_id ) );

								$label = $choice->option_label;
								$value = $choice->option_value;

								$choice_id = wp_insert_post( array(
									'post_type' => 'ccf_choice',
									'post_parent' => $field_id,
									'post_status' => 'publish',
								) );

								if ( ! is_wp_error( $choice_id ) ) {
									update_post_meta( $choice_id, 'ccf_choice_label', sanitize_text_field( $label ) );
									update_post_meta( $choice_id, 'ccf_choice_value', sanitize_text_field( $value ) );

									$new_choices[] = $choice_id;
								}
							}

							update_post_meta( $field_id, 'ccf_attached_choices', $new_choices );
						}
					}
				}

				update_post_meta( $form_id, 'ccf_attached_fields', $form_fields );
			}

			/**
			 * Move submissions over
			 */

			$submissions = $wpdb->get_results( sprintf( "SELECT * FROM {$wpdb->prefix}customcontactforms_user_data WHERE data_formid = '%d'" , (int) $form->id ) );

			foreach ( $submissions as $submission ) {
				$submission_id = wp_insert_post( array(
					'post_type' => 'ccf_submission',
					'post_parent' => $form_id,
					'post_status' => 'publish',
					'post_date' => date( 'Y-m-d H:m:s', $submission->data_time ),
				) );

				$data = $submission->data_value;
				$data_array = array();

				/**
				 * Terrible hack for unserializing weird data form
				 */
				while ( ! empty( $data ) ) {
					$key_length = $this->strstrb( $data, ':"' );
					$key_length = str_replace( 's:', '', $key_length );
					$piece_length = 6 + strlen( $key_length ) + (int) $key_length;
					$key = substr( $data, (4 + strlen( $key_length )), (int) $key_length );
					$data = substr( $data, $piece_length );
					$value_length = $this->strstrb( $data, ':"' );
					$value_length = str_replace( 's:', '', $value_length );
					$piece_length = 6 + strlen( $value_length ) + (int) $value_length;
					$value = substr( $data, (4 + strlen( $value_length )), (int) $value_length );
					$data = substr( $data, $piece_length );
					$data_array[ $key ] = $value;
				}

				if ( ! is_wp_error( $submission_id ) ) {
					update_post_meta( $submission_id, 'ccf_submission_data', $data_array );
				}
			}

			$upgraded_forms[] = (int) $form->id;
			$new_upgraded_forms[ (int) $form->id ] = $form_id;

			update_option( 'ccf_upgraded_forms', $upgraded_forms );
		}

		$posts = new WP_Query( array(
			'post_type' => 'any',
			'post_status' => 'publish',
			'posts_per_page' => 1500,
		) );

		if ( $posts->have_posts() ) {
			while ( $posts->have_posts() ) {
				$posts->the_post();
				global $post;

				$content = $post->post_content;
				$content_changed = false;

				foreach ( $new_upgraded_forms as $old_form_id => $new_form_id ) {
					if ( preg_match( '#\[customcontact form=.*' . $old_form_id . '.*\]#i', $content ) ) {
						$content_changed = true;

						$content = preg_replace( '#\[customcontact form=.*' . $old_form_id . '.*\]#i', '[ccf_form id="' . $new_form_id . '"]', $content );
					}
				}

				if ( $content_changed ) {
					wp_update_post( array(
						'ID' => $post->ID,
						'post_content' => $content,
					) );
				}
			}
		}

		wp_reset_postdata();

		add_action( 'admin_notices', array( $this, 'update_complete' ) );
	}

	/**
	 * Utility method
	 *
	 * @param $h
	 * @param $n
	 * @since 6.1
	 * @return mixed
	 */
	public function strstrb( $h, $n ) {
		return array_shift( explode( $n, $h, 2 ) );
	}

	/**
	 * Upgrade notifications for 7.1
	 *
	 * @since 7.1
	 */
	public function notifications_upgrade_71() {
		$forms = new WP_Query( array(
			'post_type' => 'ccf_form',
			'post_per_page' => 1000,
			'no_found_rows' => true,
			'fields' => 'ids',
		) );

		if ( ! empty( $forms->posts ) ) {
			foreach ( $forms->posts as $form_id ) {
				$send_notifications = get_post_meta( $form_id, 'ccf_form_send_email_notifications', true );

				$addresses = get_post_meta( $form_id, 'ccf_form_email_notification_addresses', true );
				$formatted_addresses = array();

				if ( ! empty( $addresses ) ) {
					$addresses = explode( ',', $addresses );

					foreach ( $addresses as $address ) {
						$formatted_addresses[] = array(
							'type' => 'custom',
							'email' => sanitize_text_field( $address ),
							'field' => '',
						);
					}
				}

				$notifications = array(
					array(
						'title' => '',
						'content' => '[all_fields]',
						'active' => ( ! empty( $send_notifications ) ) ? true : false,
						'addresses' => $formatted_addresses,
						'fromType' => sanitize_text_field( get_post_meta( $form_id, 'ccf_form_email_notification_from_type', true ) ),
						'fromAddress' => sanitize_text_field( get_post_meta( $form_id, 'ccf_form_email_notification_from_address', true ) ),
						'fromField' => sanitize_text_field( get_post_meta( $form_id, 'ccf_form_email_notification_from_field', true ) ),
						'subjectType' => sanitize_text_field( get_post_meta( $form_id, 'ccf_form_email_notification_subject_type', true ) ),
						'subject' => sanitize_text_field( get_post_meta( $form_id, 'ccf_form_email_notification_subject', true ) ),
						'subjectField' => sanitize_text_field( get_post_meta( $form_id, 'ccf_form_email_notification_subject_field', true ) ),
						'fromNameType' => sanitize_text_field( get_post_meta( $form_id, 'ccf_form_email_notification_from_name_type', true ) ),
						'fromName' => sanitize_text_field( get_post_meta( $form_id, 'ccf_form_email_notification_from_name', true ) ),
						'fromNameField' => sanitize_text_field( get_post_meta( $form_id, 'ccf_form_email_notification_from_name_field', true ) ),
					),
				);

				update_post_meta( $form_id, 'ccf_form_notifications', $notifications );
			}
		}
	}

	/**
	 * Return singleton instance of class
	 *
	 * @since 6.1
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
