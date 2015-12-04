<?php

class CCFTestBase extends WP_UnitTestCase {
	public $api;

	/**
	 * Default filed information
	 *
	 * @var array
	 * @since 6.0
	 */
	public $default_field = array(
		'type' => 'single-line-text',
		'label' => 'Field Label',
		'value' => 'value',
		'placeholder' => 'placeholder',
		'description' => 'test description',
		'slug' => 'slug',
		'required' => false,
		'className' => 'class-name',
		'ID' => null,
	);

	/**
	 * Advanced field data
	 *
	 * @var array
	 * @since 6.0
	 */
	public $advanced_fields = array(
		array(
			'type' => 'single-line-text',
		),
		array(
			'type' => 'paragraph-text',
		),
		array(
			'type' => 'single-line-text',
		),
		array(
			'type' => 'html',
		),
		array(
			'type' => 'section-header',
		),
		array(
			'type' => 'dropdown',
			'choices' => array(
				array(
					'label' => 'label1',
					'value' => 'value1',
					'selected' => true,
				),
				array(
					'label' => 'label 2',
					'value' => 'value 2',
					'selected' => false,
				),
				array(
					'label' => 'label 3',
					'value' => '3',
					'selected' => true,
				),
			),
		),
		array(
			'type' => 'checkbox',
			'choices' => array(
				array(
					'label' => 'check label1',
					'value' => 'check value1',
					'selected' => false,
				),
				array(
					'label' => 'check label 2',
					'value' => 'check value 2',
					'selected' => true,
				),
				array(
					'label' => 'check label 3',
					'value' => 'check 3',
					'selected' => true,
				),
			),
		),
		array(
			'type' => 'phone',
		),
		array(
			'type' => 'hidden',
		),
	);

	/**
	 * Advanced field data
	 *
	 * @var array
	 * @since 6.0
	 */
	public $advanced_fields2 = array(
		array(
			'type' => 'single-line-text',
			'label' => 'special label',
		),
		array(
			'type' => 'dropdown',
			'label' => 'special label',
			'choices' => array(
				array(
					'label' => 'label1',
					'value' => 'value1',
					'selected' => true,
				),
				array(
					'label' => 'label 3',
					'value' => '3',
					'selected' => true,
				),
			),
		),
	);

	/**
	 * Test creating a submission
	 *
	 * @param int $form_id
	 * @since 6.0
	 * @return object
	 */
	public function _createSubmission( $form_id ) {
		$submission_id = wp_insert_post( array(
			'post_status' => 'publish',
			'post_type' => 'ccf_submission',
			'post_parent' => $form_id,
			'post_author' => 1,
			'post_title' => 'Form Submission ' . $form_id,
		));

		$submission = array(
			'test_key' => 'test value',
			'test_key2' => 'test value 2',
			'test_key3' => 3,
			'test_key4' => array( 1, 2, 3 ),
		);

		if ( ! is_wp_error( $submission_id ) ) {
			update_post_meta( $submission_id, 'ccf_submission_data', $submission );
		}

		return get_post( $submission_id );
	}

	/**
	 * Create a form for testing
	 *
	 * @param array $fields
	 * @since 6.0
	 * @return object
	 */
	public function _createForm( $fields = array( array( 'type' => 'single-line-text' ) ) ) {

		$i = 1;
		foreach ( $fields as &$field ) {
			$field = wp_parse_args( $field, $this->default_field );
			$field['label'] .= ' ' . $i;
			$field['value'] .= ' ' . $i;
			$field['placeholder'] .= ' ' . $i;
			$field['slug'] .= $i;
			$field['className'] .= $i;

			$i++;
		}

		$data = array(
			'fields' => $fields,
			'type' => 'ccf_form',
			'status' => 'publish',
			'ID' => null,
			'title' => array( 'raw' => 'Test Form', ),
			'description' => 'Test form description',
			'buttonText' => 'Submit Text',
			'author' => array(),
			'excerpt' => '',
			'link' => '',
			'parent' => 0,
			'format' => 'standard',
			'slug' => '',
			'guid' => '',
			'comment_status' => 'open',
			'ping_status' => 'open',
			'menu_order' => 0,
			'terms' => array(),
			'post_meta' => array(),
			'meta' => array(
				'links' => array(),
			),
			'ping_status' => false,
			'featured_image' => null,
		);

		$request = new WP_REST_Request();
		$request->set_body( json_encode( $data ) );

		return $this->api->create_item( $request );
	}

	/**
	 * Setup plugin for testing
	 *
	 * @since 6.0
	 */
	public function setUp() {
		set_time_limit(0);

		if ( ! self::$hooks_saved ) {
			$this->_backup_hooks();
		}

		global $wpdb;
		$wpdb->suppress_errors = false;
		$wpdb->show_errors = true;
		$wpdb->db_connect();
		ini_set('display_errors', 1 );
		$this->factory = new WP_UnitTest_Factory;
		$this->clean_up_global_scope();
		$this->start_transaction();
		$this->expectDeprecated();
		add_filter( 'wp_die_handler', array( $this, 'get_wp_die_handler' ) );

		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );

		wp_set_current_user( $admin_id );

		$this->api = new CCF_API_Form_Controller;
	}
}