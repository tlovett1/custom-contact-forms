<?php

class CCF_API extends WP_JSON_Posts {

	/**
	 * Array of field attributes with sanitization/escaping callbacks
	 *
	 * @var array
	 * @since 6.0
	 */
	protected $field_attribute_keys;

	/**
	 * Array of field choice attributes with sanitization/escaping callbacks
	 *
	 * @var array
	 * @since 6.0
	 */
	protected $choice_attribute_keys;

	/**
	 * Setup hook to prepare returned form. Setup field/choice attributes with callbacks
	 *
	 * @param WP_JSON_ResponseHandler $server
	 * @since 6.0
	 */
	public function __construct( $server ) {
		parent::__construct( $server );

		add_filter( 'json_prepare_post', array( $this, 'filter_prepare_post' ), 10, 3 );
		add_filter( 'json_pre_dispatch', array( $this, 'filter_json_pre_dispatch' ), 10, 2 );

		$this->field_attribute_keys = apply_filters( 'ccf_field_attributes', array(
			'type' => array(
				'sanitize' => 'esc_attr',
				'escape' => 'esc_attr',
			),
			'slug' => array(
				'sanitize' => 'esc_attr',
				'escape' => 'esc_attr',
			),
			'placeholder' => array(
				'sanitize' => 'esc_attr',
				'escape' => 'esc_attr',
			),
			'className' => array(
				'sanitize' => 'esc_attr',
				'escape' => 'esc_attr',
			),
			'label' => array(
				'sanitize' => 'sanitize_text_field',
				'escape' => 'esc_html',
			),
			'description' => array(
				'sanitize' => 'sanitize_text_field',
				'escape' => 'esc_html',
			),
			'value' => array(
				'sanitize' => 'sanitize_text_field',
				'escape' => 'esc_html',
			),
			'required' => array(
				'sanitize' => array( $this, 'boolval' ),
				'escape' => array( $this, 'boolval' ),
			),
			'showDate' => array(
				'sanitize' => array( $this, 'boolval' ),
				'escape' => array( $this, 'boolval' ),
			),
			'addressType' => array(
				'sanitize' => 'esc_attr',
				'escape' => 'esc_attr',
			),
			'siteKey' => array(
				'sanitize' => 'esc_attr',
				'escape' => 'esc_attr',
			),
			'secretKey' => array(
				'sanitize' => 'esc_attr',
				'escape' => 'esc_attr',
			),
			'phoneFormat' => array(
				'sanitize' => 'esc_attr',
				'escape' => 'esc_attr',
			),
			'emailConfirmation' => array(
				'sanitize' => array( $this, 'boolval' ),
				'escape' => array( $this, 'boolval' ),
			),
			'showTime' => array(
				'sanitize' => array( $this, 'boolval' ),
				'escape' => array( $this, 'boolval' ),
			),
			'heading' => array(
				'sanitize' => 'sanitize_text_field',
				'escape' => 'esc_html',
			),
			'subheading' => array(
				'sanitize' => 'sanitize_text_field',
				'escape' => 'esc_html',
			),
			'html' => array(
				'sanitize' => 'wp_kses_post',
				'escape' => 'wp_kses_post',
			),
			'maxFileSize' => array(
				'sanitize' => 'intval',
				'escape' => 'intval',
			),
			'fileExtensions' => array(
				'sanitize' => 'sanitize_text_field',
				'escape' => 'esc_html',
			),
		) );

		$this->choice_attribute_keys = apply_filters( 'ccf_choice_attributes', array(
			'label' => array(
				'sanitize' => 'sanitize_text_field',
				'escape' => 'esc_html',
			),
			'value' => array(
				'sanitize' => 'esc_attr',
				'escape' => 'esc_attr',
			),
			'selected' => array(
				'sanitize' => array( $this, 'boolval' ),
				'escape' => array( $this, 'boolval' ),
			),
		) );
	}

	/**
	 * Allow Backbone to emulate HTTP
	 *
	 * @param $result
	 * @param object $server
	 * @since 6.6.5
	 */
	function filter_json_pre_dispatch( $result, $server ) {
		if ( isset( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ) {
			$server->method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
		}
	}

	/**
	 * Retrieve a post.
	 *
	 * @uses get_post()
	 * @since 6.4.9
	 * @param int $id Post ID
	 * @param string $context The context; 'view' (default) or 'edit'.
	 * @return array Post entity
	 */
	public function get_post( $id, $context = 'view' ) {
		$id = (int) $id;

		$post = get_post( $id, ARRAY_A );

		if ( empty( $id ) || empty( $post['ID'] ) ) {
			return new WP_Error( 'json_post_invalid_id', __( 'Invalid post ID.' ), array( 'status' => 404 ) );
		}

		if ( ! json_check_post_permission( $post, 'read' ) ) {
			return new WP_Error( 'json_user_cannot_read', __( 'Sorry, you cannot read this post.' ), array( 'status' => 401 ) );
		}

		// Link headers (see RFC 5988)

		$response = new WP_JSON_Response();
		// Modified now, no cache
		$response->header( 'Cache-Control', 'no-cache, no-store, must-revalidate' );
		$response->header( 'Expires', 'Wed, 11 Jan 1984 05:00:00 GMT' );
		$response->header( 'Pragma', 'no-cache' );
		$response->header( 'Last-Modified', gmdate( 'D, d M Y H:i:s' ) . ' GMT'  );

		$post = $this->prepare_post( $post, $context );

		if ( is_wp_error( $post ) ) {
			return $post;
		}

		foreach ( $post['meta']['links'] as $rel => $url ) {
			$response->link_header( $rel, $url );
		}

		$response->link_header( 'alternate',  get_permalink( $id ), array( 'type' => 'text/html' ) );
		$response->set_data( $post );

		return $response;
	}

	/**
	 * Retrieve posts. We need to override last modified date
	 *
	 * @since 6.4.9
	 *
	 * The optional $filter parameter modifies the query used to retrieve posts.
	 * Accepted keys are 'post_type', 'post_status', 'number', 'offset',
	 * 'orderby', and 'order'.
	 *
	 * @uses wp_get_recent_posts()
	 * @see get_posts() for more on $filter values
	 *
	 * @param array $filter Parameters to pass through to `WP_Query`
	 * @param string $context The context; 'view' (default) or 'edit'.
	 * @param string|array $type Post type slug, or array of slugs
	 * @param int $page Page number (1-indexed)
	 * @return stdClass[] Collection of Post entities
	 */
	public function get_posts( $filter = array(), $context = 'edit', $type = 'post', $page = 1 ) {
		$query = array();

		// Validate post types and permissions
		$query['post_type'] = array();

		foreach ( (array) $type as $type_name ) {
			$post_type = get_post_type_object( $type_name );

			if ( ! ( (bool) $post_type ) || ! $post_type->show_in_json ) {
				return new WP_Error( 'json_invalid_post_type', sprintf( __( 'The post type "%s" is not valid' ), $type_name ), array( 'status' => 403 ) );
			}

			$query['post_type'][] = $post_type->name;
		}

		global $wp;

		// Allow the same as normal WP
		$valid_vars = apply_filters('query_vars', $wp->public_query_vars);

		// If the user has the correct permissions, also allow use of internal
		// query parameters, which are only undesirable on the frontend
		//
		// To disable anyway, use `add_filter('json_private_query_vars', '__return_empty_array');`

		if ( current_user_can( $post_type->cap->edit_posts ) ) {
			$private = apply_filters( 'json_private_query_vars', $wp->private_query_vars );
			$valid_vars = array_merge( $valid_vars, $private );
		}

		// Define our own in addition to WP's normal vars
		$json_valid = array( 'posts_per_page' );
		$valid_vars = array_merge( $valid_vars, $json_valid );

		// Filter and flip for querying
		$valid_vars = apply_filters( 'json_query_vars', $valid_vars );
		$valid_vars = array_flip( $valid_vars );

		// Exclude the post_type query var to avoid dodging the permission
		// check above
		unset( $valid_vars['post_type'] );

		foreach ( $valid_vars as $var => $index ) {
			if ( isset( $filter[ $var ] ) ) {
				$query[ $var ] = apply_filters( 'json_query_var-' . $var, $filter[ $var ] );
			}
		}

		// Special parameter handling
		$query['paged'] = absint( $page );

		$post_query = new WP_Query();
		$posts_list = $post_query->query( $query );
		$response   = new WP_JSON_Response();
		$response->query_navigation_headers( $post_query );

		if ( ! $posts_list ) {
			$response->set_data( array() );
			return $response;
		}

		// holds all the posts data
		$struct = array();

		// Modified now, no cache
		$response->header( 'Cache-Control', 'no-cache, no-store, must-revalidate' );
		$response->header( 'Expires', 'Wed, 11 Jan 1984 05:00:00 GMT' );
		$response->header( 'Pragma', 'no-cache' );
		$response->header( 'Last-Modified', gmdate( 'D, d M Y H:i:s' ) . ' GMT'  );

		foreach ( $posts_list as $post ) {
			$post = get_object_vars( $post );

			// Do we have permission to read this post?
			if ( ! json_check_post_permission( $post, 'read' ) ) {
				continue;
			}

			$response->link_header( 'item', json_url( '/posts/' . $post['ID'] ), array( 'title' => $post['post_title'] ) );
			$post_data = $this->prepare_post( $post, $context );
			if ( is_wp_error( $post_data ) ) {
				continue;
			}

			$struct[] = $post_data;
		}
		$response->set_data( $struct );

		return $response;
	}


	/**
	 * Ensure value is boolean
	 *
	 * @param mixed $value
	 * @since 6.0
	 * @return bool
	 */
	protected function boolval( $value ) {
		return !! $value;
	}

	/**
	 * Register API routes. We only need to get all forms and get specific forms. Right now specific endpoints
	 * for fields/choices are not really necessary.
	 *
	 * @param array $routes
	 * @since 6.0
	 * @return array
	 */
	public function register_routes( $routes ) {
		$routes['/ccf/forms'] = array(
			array( array( $this, 'get_forms'), WP_JSON_Server::READABLE ),
			array( array( $this, 'create_form'), WP_JSON_Server::CREATABLE | WP_JSON_Server::ACCEPT_JSON ),
		);

		$routes['/ccf/forms/(?P<id>\d+)'] = array(
			array( array( $this, 'get_form'), WP_JSON_Server::READABLE ),
			array( array( $this, 'edit_form'), WP_JSON_Server::EDITABLE | WP_JSON_Server::ACCEPT_JSON ),
			array( array( $this, 'delete_form'), WP_JSON_Server::DELETABLE ),
		);

		$routes['/ccf/forms/(?P<id>\d+)/fields'] = array(
			array( array( $this, 'get_fields'), WP_JSON_Server::READABLE ),
		);

		$routes['/ccf/forms/(?P<id>\d+)/submissions'] = array(
			array( array( $this, 'get_submissions'), WP_JSON_Server::READABLE ),
		);

		return $routes;
	}

	/**
	 * Handle field deletion. We need to delete choices attached to the field too. Not an API route.
	 *
	 * @param int $form_id
	 * @since 6.0
	 */
	public function delete_fields( $form_id ) {
		$attached_fields = get_post_meta( $form_id, 'ccf_attached_fields', true );

		if ( ! empty( $attached_fields ) ) {
			foreach ( $attached_fields as $field_id ) {
				$this->delete_choices( $field_id );

				wp_delete_post( $field_id, true );
			}
		}
	}

	/**
	 * Delete all submissionws associated with a post.
	 *
	 * @param int $form_id
	 * @since 6.0
	 */
	public function delete_submission( $form_id ) {
		$submissions = get_children( array( 'post_parent' => $form_id, 'post_type' => 'ccf_submission', 'numberposts' => apply_filters( 'ccf_max_submissions', 5000, get_post( $form_id ) ) ) );

		if ( ! empty( $submissions ) ) {
			foreach ( $submissions as $submission ) {
				wp_delete_post( $submission->ID, true );
			}
		}
	}

	/**
	 * Delete field choices. Not an API route.
	 *
	 * @param int $field_id
	 * @since 6.0
	 */
	public function delete_choices( $field_id ) {
		$attached_choices = get_post_meta( $field_id, 'ccf_attached_choices', true );

		if ( ! empty( $attached_choices ) ) {
			foreach ( $attached_choices as $choice_id ) {
				wp_delete_post( $choice_id, true );
			}
		}

	}

	/**
	 * Add in some extra attributes unique to forms to return from the API
	 *
	 * @param array $_post
	 * @param array $post
	 * @param string $context
	 * @since 6.0
	 * @return array
	 */
	public function filter_prepare_post( $_post, $post, $context ) {
		if ( 'ccf_form' === $_post['type'] ) {
			$_post['fields'] = $this->_get_fields( $post['ID'] );

			$_post['buttonText'] = esc_attr( get_post_meta( $post['ID'], 'ccf_form_buttonText', true ) );
			$_post['description'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_description', true ) );
			$_post['completionActionType'] = esc_attr( get_post_meta( $post['ID'], 'ccf_form_completion_action_type', true ) );
			$_post['completionRedirectUrl'] = esc_url_raw( get_post_meta( $post['ID'], 'ccf_form_completion_redirect_url', true ) );
			$_post['completionMessage'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_completion_message', true ) );
			$_post['sendEmailNotifications'] = (bool) get_post_meta( $post['ID'], 'ccf_form_send_email_notifications', true );
			$_post['pause'] = (bool) get_post_meta( $post['ID'], 'ccf_form_pause', true );
			$_post['pauseMessage'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_pause_message', true ) );
			$_post['emailNotificationAddresses'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_email_notification_addresses', true ) );
			
			$_post['emailNotificationFromType'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_email_notification_from_type', true ) );
			$_post['emailNotificationFromAddress'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_email_notification_from_address', true ) );
			$_post['emailNotificationFromField'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_email_notification_from_field', true ) );

			$_post['emailNotificationSubjectType'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_email_notification_subject_type', true ) );
			$_post['emailNotificationSubject'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_email_notification_subject', true ) );
			$_post['emailNotificationSubjectField'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_email_notification_subject_field', true ) );

			$_post['emailNotificationFromNameType'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_email_notification_from_name_type', true ) );
			$_post['emailNotificationFromName'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_email_notification_from_name', true ) );
			$_post['emailNotificationFromNameField'] = esc_html( get_post_meta( $post['ID'], 'ccf_form_email_notification_from_name_field', true ) );

			$submissions = get_children( array( 'post_parent' => $post['ID'], 'numberposts' => array( 'ccf_max_submissions', 5000, $post ) ) );
			$_post['submissions'] = esc_html( count( $submissions ) );
		} elseif ( 'ccf_submission' === $_post['type'] ) {
			$_post['data'] = get_post_meta( $_post['ID'], 'ccf_submission_data', true );
			$_post['ip_address'] = esc_html( get_post_meta( $_post['ID'], 'ccf_submission_ip', true ) );
		}

		return $_post;
	}

	/**
	 * Get fields. This is an API endpoint.
	 *
	 * @param int $id
	 * @since 6.0
	 * @return WP_Error|WP_JSON_Response
	 */
	public function get_fields( $id ) {
		$id = (int) $id;

		if ( empty( $id ) ) {
			return new WP_Error( 'json_invalid_id_ccf_form', esc_html__( 'Invalid form ID.', 'custom-contact-forms' ), array( 'status' => 404 ) );
		}

		$post_type = get_post_type_object( 'ccf_form' );
		if ( ! current_user_can( $post_type->cap->edit_posts, $id ) ) {
			return new WP_Error( 'json_cannot_view_ccf_forms', esc_html__( 'Sorry, you cannot view forms.', 'custom-contact-forms' ), array( 'status' => 403 ) );
		}

		$fields = $this->_get_fields( $id );

		$response = new WP_JSON_Response();
		$response->set_status( 200 );

		$response->set_data( $fields );

		return $response;
	}

	/**
	 * Get fields given a form ID. Not an API route.
	 *
	 * @param int $form_id
	 * @return array
	 */
	public function _get_fields( $form_id ) {
		$fields = array();

		$attached_fields = get_post_meta( $form_id, 'ccf_attached_fields', true );

		if ( ! empty( $attached_fields ) ) {
			foreach ( $attached_fields as $field_id ) {
				$field = array( 'ID' => $field_id );

				foreach ( $this->field_attribute_keys as $key => $functions ) {
					$value = get_post_meta( $field_id, 'ccf_field_' . $key );

					if ( isset( $value[0] ) ) {
						$field[$key] = call_user_func( $functions['escape'], $value[0] );
					}
				}

				$choices = get_post_meta( $field_id, 'ccf_attached_choices' );

				if ( ! empty( $choices ) ) {
					$field['choices'] = array();

					if ( ! empty( $choices[0] ) ) {
						foreach ( $choices[0] as $choice_id ) {
							$choice = array( 'ID' => $choice_id );

							foreach ( $this->choice_attribute_keys as $key => $functions ) {
								$value = get_post_meta( $choice_id, 'ccf_choice_' . $key );

								if ( isset( $value[0] ) ) {
									$choice[$key] = call_user_func( $functions['escape'], $value[0] );
								}
							}

							$field['choices'][] = $choice;
						}
					}
				}

				$fields[] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Create a form. This is an API endpoint
	 *
	 * @param array $data
	 * @since 6.0
	 * @return int|WP_Error|WP_JSON_ResponseInterface
	 */
	public function create_form( $data ) {
		unset( $data['ID'] );

		// @todo: remove hack. Needed for broken API
		if ( isset( $data['author'] ) ) {
			unset( $data['author'] );
		}

		// @todo: remove hack. Needed for broken API
		if ( isset( $data['date'] ) ) {
			unset( $data['date'] );
		}

		// @todo: remove hack. Needed for broken API
		if ( isset( $data['date_gmt'] ) ) {
			unset( $data['date_gmt'] );
		}

		$result = $this->insert_post( $data );
		if ( $result instanceof WP_Error ) {
			return $result;
		}

		if ( ! empty( $data['fields'] ) ) {
			$this->create_and_map_fields( $data['fields'], $result );
		}

		if ( isset( $data['buttonText'] ) ) {
			update_post_meta( $result, 'ccf_form_buttonText', sanitize_text_field( $data['buttonText'] ) );
		}

		if ( isset( $data['description'] ) ) {
			update_post_meta( $result, 'ccf_form_description', sanitize_text_field( $data['description'] ) );
		}

		if ( isset( $data['completionActionType'] ) ) {
			update_post_meta( $result, 'ccf_form_completion_action_type', sanitize_text_field( $data['completionActionType'] ) );
		}

		if ( isset( $data['completionMessage'] ) ) {
			update_post_meta( $result, 'ccf_form_completion_message', sanitize_text_field( $data['completionMessage'] ) );
		}

		if ( isset( $data['completionRedirectUrl'] ) ) {
			update_post_meta( $result, 'ccf_form_completion_redirect_url', esc_url_raw( $data['completionRedirectUrl'] ) );
		}

		if ( isset( $data['sendEmailNotifications'] ) ) {
			update_post_meta( $result, 'ccf_form_send_email_notifications', (bool) $data['sendEmailNotifications'] );
		}

		if ( isset( $data['pause'] ) ) {
			update_post_meta( $result, 'ccf_form_pause', (bool) $data['pause'] );
		}

		if ( isset( $data['pauseMessage'] ) ) {
			update_post_meta( $result, 'ccf_form_pause_message', sanitize_text_field( $data['pauseMessage'] ) );
		}

		if ( isset( $data['emailNotificationAddresses'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_addresses', sanitize_text_field( $data['emailNotificationAddresses'] ) );
		}

		if ( isset( $data['emailNotificationFromType'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_from_type', sanitize_text_field( $data['emailNotificationFromType'] ) );
		}

		if ( isset( $data['emailNotificationFromAddress'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_from_address', sanitize_text_field( $data['emailNotificationFromAddress'] ) );
		}

		if ( isset( $data['emailNotificationFromField'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_from_field', sanitize_text_field( $data['emailNotificationFromField'] ) );
		}

		if ( isset( $data['emailNotificationSubjectType'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_subject_type', sanitize_text_field( $data['emailNotificationSubjectType'] ) );
		}

		if ( isset( $data['emailNotificationSubject'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_subject', sanitize_text_field( $data['emailNotificationSubject'] ) );
		}

		if ( isset( $data['emailNotificationSubjectField'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_subject_field', sanitize_text_field( $data['emailNotificationSubjectField'] ) );
		}

		if ( isset( $data['emailNotificationFromNameType'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_from_name_type', sanitize_text_field( $data['emailNotificationFromNameType'] ) );
		}

		if ( isset( $data['emailNotificationFromName'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_from_name', sanitize_text_field( $data['emailNotificationFromName'] ) );
		}

		if ( isset( $data['emailNotificationFromNameField'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_from_name_field', sanitize_text_field( $data['emailNotificationFromNameField'] ) );
		}

		$response = json_ensure_response( $this->get_post( $result ) );
		$response->set_status( 201 );
		$response->header( 'Location', json_url( '/ccf/forms/' . $result ) );

		return $response;
	}

	/**
	 * Create field choices and attach them to fields. Not an API route.
	 *
	 * @param array $choices
	 * @param int $field_id
	 * @since 6.0
	 */
	public function create_and_map_choices( $choices, $field_id ) {
		$new_choices = array();

		foreach ( $choices as $choice ) {
			if ( ! empty( $choice['label'] ) ) {
				if ( empty( $choice['ID'] ) ) {
					$args = array(
						'post_title' => $choice['label'] . '-' . (int) $field_id,
						'post_status' => 'publish',
						'post_parent' => $field_id,
						'post_type' => 'ccf_choice',
					);

					$choice_id = wp_insert_post( $args );
				} else {
					$choice_id = $choice['ID'];
				}

				if ( ! is_wp_error( $choice_id ) ) {
					foreach ( $this->choice_attribute_keys as $key => $functions ) {
						if ( isset( $choice[$key] ) ) {
							update_post_meta( $choice_id, 'ccf_choice_' . $key, call_user_func( $functions['sanitize'], $choice[$key] ) );
						}
					}

					$new_choices[] = $choice_id;
				}
			} else {
				if ( ! empty( $choice['ID'] ) ) {
					wp_delete_post( $choice['ID'], true );
				}
			}
		}

		$current_choices = get_post_meta( $field_id, 'ccf_attached_choices', true );
		$new_choices = array_map( 'absint', $new_choices );

		if ( ! empty( $current_choices ) ) {
			$deleted_choices = array_diff( $current_choices, $new_choices );
			foreach ( $deleted_choices as $choice_id ) {
				wp_delete_post( $choice_id, true );
			}
		}

		update_post_meta( $field_id, 'ccf_attached_choices', array_map( 'absint', $new_choices ) );
	}

	/**
	 * Create fields and map them to forms. Not an API route.
	 *
	 * @param array $fields
	 * @param int $form_id
	 * @since 6.0
	 */
	public function create_and_map_fields( $fields, $form_id ) {
		$new_fields = array();

		foreach ( $fields as $field ) {
			if ( empty( $field['ID'] ) ) {
				$args = array(
					'post_title' => $field['slug'] . '-' . (int) $form_id,
					'post_status' => 'publish',
					'post_parent' => $form_id,
					'post_type' => 'ccf_field',
				);

				$field_id = wp_insert_post( $args );
			} else {
				$field_id = $field['ID'];
			}

			if ( ! is_wp_error( $field_id ) ) {
				foreach ( $this->field_attribute_keys as $key => $functions ) {
					if ( isset( $field[$key] ) ) {
						update_post_meta( $field_id, 'ccf_field_' . $key, call_user_func( $functions['sanitize'], $field[$key] ) );
					}
				}

				if ( isset( $field['choices'] ) ) {
					$choices = ( empty( $field['choices'] ) ) ? array() : $field['choices'];
					$this->create_and_map_choices( $choices, $field_id );
				}

				$new_fields[] = $field_id;
			}
		}

		$current_fields = get_post_meta( $form_id, 'ccf_attached_fields', true );
		$new_fields = array_map( 'absint', $new_fields );

		if ( ! empty( $current_fields ) ) {
			$deleted_fields = array_diff( $current_fields, $new_fields );
			foreach ( $deleted_fields as $field_id ) {
				wp_delete_post( $field_id, true );
			}
		}

		update_post_meta( $form_id, 'ccf_attached_fields', $new_fields );
	}

	/**
	 * Setup custom routes
	 *
	 * @since 6.0
	 */
	public function register_filters() {
		add_filter( 'json_endpoints', array( $this, 'register_routes' ) );
	}

	/**
	 * Return forms. This is an API endpoint.
	 *
	 * @param array $filter
	 * @param string $context
	 * @param string $type
	 * @param int $page
	 * @since 6.0
	 * @return object|WP_Error
	 */
	public function get_forms( $filter = array(), $context = 'edit', $type = null, $page = 1 ) {
		$post_type = get_post_type_object( 'ccf_form' );
		if ( ! current_user_can( $post_type->cap->edit_posts ) ) {
			return new WP_Error( 'json_cannot_view_ccf_forms', esc_html__( 'Sorry, you cannot view forms.', 'custom-contact-forms' ), array( 'status' => 403 ) );
		}

		return $this->get_posts( $filter, $context, 'ccf_form', $page );
	}

	/**
	 * Return submissions. This is an API endpoint.
	 *
	 * @since 6.0
	 */
	public function get_submissions( $id, $filter = array(), $context = 'edit', $type = null, $page = 1 ) {
		$id = (int) $id;

		if ( empty( $id ) ) {
			return new WP_Error( 'json_invalid_id_ccf_form', esc_html__( 'Invalid form ID.', 'custom-contact-forms' ), array( 'status' => 404 ) );
		}

		$post_type = get_post_type_object( 'ccf_form' );
		if ( ! current_user_can( $post_type->cap->edit_posts, $id ) ) {
			return new WP_Error( 'json_cannot_view_ccf_forms', esc_html__( 'Sorry, you cannot view forms.', 'custom-contact-forms' ), array( 'status' => 403 ) );
		}

		$filter['post_parent'] = $id;

		return $this->get_posts( $filter, $context, 'ccf_submission', $page );
	}

	/**
	 * Return a form given an ID. This is an API endpoint.
	 *
	 * @param int $id
	 * @param string $context
	 * @since 6.0
	 * @return array|WP_Error
	 */
	public function get_form( $id, $context = 'view' ) {
		$id = (int) $id;

		if ( empty( $id ) ) {
			return new WP_Error( 'json_invalid_id_ccf_form', esc_html__( 'Invalid form ID.', 'custom-contact-forms' ), array( 'status' => 404 ) );
		}

		$form = get_post( $id, ARRAY_A );

		if ( empty( $form ) ) {
			return new WP_Error( 'json_invalid_ccf_form', esc_html__( 'Invalid form.', 'custom-contact-forms' ), array( 'status' => 404 ) );
		}

		if ( ! json_check_post_permission( $form, 'read' ) ) {
			return new WP_Error( 'json_cannot_view_ccf_form', esc_html__( 'Sorry, you cannot view this form.', 'custom-contact-forms' ), array( 'status' => 403 ) );
		}

		return $this->get_post( $id, $context );
	}

	/**
	 * Edit a form given an ID. This is an API endpoint.
	 *
	 * @param int $id
	 * @param array $data
	 * @param array $_headers
	 * @since 6.0
	 * @return int|WP_Error|WP_JSON_ResponseInterface
	 */
	function edit_form( $id, $data, $_headers = array() ) {
		$id = (int) $id;

		if ( empty( $id ) ) {
			return new WP_Error( 'json_invalid_id_ccf_form', esc_html__( 'Invalid form ID.', 'custom-contact-forms' ), array( 'status' => 404 ) );
		}

		$form = get_post( $id, ARRAY_A );

		if ( empty( $form['ID'] ) ) {
			return new WP_Error( 'json_invalid_ccf_form', esc_html__( 'Invalid form.', 'custom-contact-forms' ), array( 'status' => 404 ) );
		}

		// @todo: remove hack. Needed for broken API
		if ( isset( $data['author'] ) ) {
			unset( $data['author'] );
		}

		// @todo: remove hack. Needed for broken API
		if ( isset( $data['date'] ) ) {
			unset( $data['date'] );
		}

		// @todo: remove hack. Needed for broken API
		if ( isset( $data['date_gmt'] ) ) {
			unset( $data['date_gmt'] );
		}

		$result = $this->insert_post( $data );
		if ( $result instanceof WP_Error ) {
			return $result;
		}

		if ( isset( $data['fields'] ) ) {
			if ( empty( $data['fields'] ) ) {
				$data['fields'] = array();
			}

			$this->create_and_map_fields( $data['fields'], $result );
		}

		if ( isset( $data['buttonText'] ) ) {
			update_post_meta( $result, 'ccf_form_buttonText', sanitize_text_field( $data['buttonText'] ) );
		}

		if ( isset( $data['description'] ) ) {
			update_post_meta( $result, 'ccf_form_description', sanitize_text_field( $data['description'] ) );
		}

		if ( isset( $data['completionActionType'] ) ) {
			update_post_meta( $result, 'ccf_form_completion_action_type', sanitize_text_field( $data['completionActionType'] ) );
		}

		if ( isset( $data['completionMessage'] ) ) {
			update_post_meta( $result, 'ccf_form_completion_message', sanitize_text_field( $data['completionMessage'] ) );
		}

		if ( isset( $data['pause'] ) ) {
			update_post_meta( $result, 'ccf_form_pause', (bool) $data['pause'] );
		}

		if ( isset( $data['pauseMessage'] ) ) {
			update_post_meta( $result, 'ccf_form_pause_message', sanitize_text_field( $data['pauseMessage'] ) );
		}

		if ( isset( $data['completionRedirectUrl'] ) ) {
			update_post_meta( $result, 'ccf_form_completion_redirect_url', esc_url_raw( $data['completionRedirectUrl'] ) );
		}

		if ( isset( $data['sendEmailNotifications'] ) ) {
			update_post_meta( $result, 'ccf_form_send_email_notifications', (bool) $data['sendEmailNotifications'] );
		}

		if ( isset( $data['emailNotificationAddresses'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_addresses', sanitize_text_field( $data['emailNotificationAddresses'] ) );
		}

		if ( isset( $data['emailNotificationFromType'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_from_type', sanitize_text_field( $data['emailNotificationFromType'] ) );
		}

		if ( isset( $data['emailNotificationFromAddress'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_from_address', sanitize_text_field( $data['emailNotificationFromAddress'] ) );
		}

		if ( isset( $data['emailNotificationFromField'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_from_field', sanitize_text_field( $data['emailNotificationFromField'] ) );
		}

		if ( isset( $data['emailNotificationFromNameType'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_from_name_type', sanitize_text_field( $data['emailNotificationFromNameType'] ) );
		}

		if ( isset( $data['emailNotificationFromName'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_from_name', sanitize_text_field( $data['emailNotificationFromName'] ) );
		}

		if ( isset( $data['emailNotificationFromNameField'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_from_name_field', sanitize_text_field( $data['emailNotificationFromNameField'] ) );
		}

		if ( isset( $data['emailNotificationSubjectType'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_subject_type', sanitize_text_field( $data['emailNotificationSubjectType'] ) );
		}

		if ( isset( $data['emailNotificationSubject'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_subject', sanitize_text_field( $data['emailNotificationSubject'] ) );
		}

		if ( isset( $data['emailNotificationSubjectField'] ) ) {
			update_post_meta( $result, 'ccf_form_email_notification_subject_field', sanitize_text_field( $data['emailNotificationSubjectField'] ) );
		}

		$response = json_ensure_response( $this->get_post( $result ) );

		$response->set_status( 201 );
		$response->header( 'Location', json_url( '/ccf/forms/' . $result ) );

		return $response;
	}

	/**
	 * Delete a form given an ID. This is an API endpoint.
	 *
	 * @param int $id
	 * @param bool $force
	 * @since 6.0
	 * @return true|WP_Error
	 */
	public function delete_form( $id, $force = false ) {
		$id = (int) $id;

		if ( empty( $id ) ) {
			return new WP_Error( 'json_invalid_id_ccf_form', esc_html__( 'Invalid form ID.', 'custom-contact-forms' ), array( 'status' => 404 ) );
		}

		if ( $force ) {
			$this->delete_fields( $id );
			$this->delete_submissions( $id );
		}

		$result = wp_trash_post( $id );

		if ( ! $result ) {
			return new WP_Error( 'json_cannot_delete', esc_html__( 'The form cannot be deleted.', 'custom-contact-forms' ), array( 'status' => 500 ) );
		}

		if ( $force ) {
			return array( 'message' => esc_html__( 'Permanently deleted form', 'custom-contact-forms' ) );
		} else {
			// TODO: return a HTTP 202 here instead
			return array( 'message' => esc_html__( 'Deleted post', 'custom-contact-forms' ) );
		}
	}
}