<?php

class CCFTestAPI extends CCFTestBase {

	/**
	 * Test getting a single form
	 *
	 * @since 6.0
	 */
	public function testGetForm() {
		$this->_createForm();
		$form = $this->_createForm();
		$this->_createForm();

		$request = new WP_REST_Request();
		$request->set_param( 'id', $form->data['ID'] );

		$get_form_result = $this->api->get_item( $form->data['ID'] );

		$this->assertTrue( ! is_wp_error( $get_form_result ) );

		$this->assertTrue( ! empty( $get_form_result->data['ID'] ) );

		$form = get_post( $get_form_result->data['ID'] );

		$this->assertTrue( ! empty( $form ) );

		$button_text = get_post_meta( $get_form_result->data['ID'], 'ccf_form_buttonText', true );
		$this->assertTrue( ! empty( $button_text ) );
	}

	/**
	 * Test getting forms
	 *
	 * @since 6.0
	 */
	public function testGetForms() {
		$this->_createForm();
		$this->_createForm();
		$this->_createForm();
		$this->_createForm();
		$this->_createForm();

		$get_forms_result = $this->api->get_items();

		$forms = $get_forms_result->data;

		$this->assertEquals( 5, count( $forms ) );

		foreach ( $forms as $form_object ) {
			$form = get_post( $form_object['ID'] );

			$this->assertTrue( ! empty( $form ) );

			$button_text = get_post_meta( $form_object['ID'], 'ccf_form_buttonText', true );
			$this->assertTrue( ! empty( $button_text ) );
		}
	}

	/**
	 * Test creating a basic new form
	 *
	 * @since 6.0
	 */
	public function testNewForm() {

		$form_result = $this->_createForm();

		$this->assertTrue( ! is_wp_error( $form_result ) );

		$this->assertTrue( ! empty( $form_result->data['ID'] ) );

		$form = get_post( $form_result->data['ID'] );

		$this->assertTrue( ! empty( $form ) );

		$this->assertEquals( 'Test Form', get_the_title( $form_result->data['ID'] ) );

		$description = get_post_meta( $form_result->data['ID'], 'ccf_form_description', true );
		$this->assertEquals( 'Test form description', $description );

		$button_text = get_post_meta( $form_result->data['ID'], 'ccf_form_buttonText', true );
		$this->assertTrue( ! empty( $button_text ) );

		$fields = get_post_meta( $form_result->data['ID'], 'ccf_attached_fields', true );

		$this->assertTrue( ! empty( $fields ) );

		$field = get_post( $fields[0] );

		$this->assertTrue( ! empty( $field ) );
	}

	/**
	 * Test an advanced new form
	 *
	 * @since 6.0
	 */
	public function testNewFormAdvanced() {

		$this->_createForm();
		$form_result = $this->_createForm( $this->advanced_fields );
		$this->_createForm();
		$this->_createForm( $this->advanced_fields );

		$this->assertTrue( ! is_wp_error( $form_result ) );

		$this->assertTrue( ! empty( $form_result->data['ID'] ) );

		$form = get_post( $form_result->data['ID'] );

		$this->assertTrue( ! empty( $form ) );

		$this->assertEquals( 'Test Form', get_the_title( $form_result->data['ID'] ) );

		$description = get_post_meta( $form_result->data['ID'], 'ccf_form_description', true );
		$this->assertEquals( 'Test form description', $description );

		$button_text = get_post_meta( $form_result->data['ID'], 'ccf_form_buttonText', true );

		$this->assertTrue( ! empty( $button_text ) );

		$attached_fields = get_post_meta( $form_result->data['ID'], 'ccf_attached_fields', true );

		$this->assertTrue( ! empty( $attached_fields ) );

		foreach ( $attached_fields as $field_id ) {
			$field = get_post( $field_id );

			$this->assertTrue( ! empty( $field ) );

			$field_type = get_post_meta( $field_id, 'ccf_field_type', true );
			$this->assertTrue( ! empty( $field_type ) );

			$label = get_post_meta( $field_id, 'ccf_field_label', true );
			$this->assertTrue( ! empty( $label ) );

			$description = get_post_meta( $field_id, 'ccf_field_description', true );
			$this->assertTrue( ! empty( $description ) );

			$class_name = get_post_meta( $field_id, 'ccf_field_className', true );
			$this->assertTrue( ! empty( $class_name ) );

			$placeholder = get_post_meta( $field_id, 'ccf_field_placeholder', true );
			$this->assertTrue( ! empty( $placeholder ) );

			if ( in_array( $field_type, array( 'dropdown', 'checkbox', 'radio' ) ) ) {
				$choices = get_post_meta( $field_id, 'ccf_attached_choices', true );

				foreach ( $choices as $choice_id ) {
					$choice = get_post( $choice_id );

					$this->assertTrue( ! empty( $choice ) );

					$label = get_post_meta( $choice_id, 'ccf_choice_label', true );
					$this->assertTrue( ! empty( $label ) );
				}
			}
		}
	}

	/**
	 * Test editing a form
	 *
	 * @since 6.0
	 */
	public function testEditForm() {
		$this->_createForm();
		$this->_createForm();
		$form = $this->_createForm();
		$this->_createForm();
		$this->_createForm();

		$fields = $this->advanced_fields2;

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

		$edit_data = array(
			'fields' => $fields,
			'type' => 'ccf_form',
			'status' => 'publish',
			'ID' => null,
			'title' => 'Edit Test Form',
			'description' => 'Edit test form description',
			'buttonText' => 'Edit Submit Text',
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
		$request->set_param( 'id', $form->data['ID'] );
		$request->set_body( json_encode( $data ) );

		$edit_form_result = $this->api->update_item( $request );

		$this->assertTrue( ! empty( $edit_form_result->data['ID'] ) );

		$form = get_post( $edit_form_result->data['ID'] );

		$this->assertTrue( ! empty( $form ) );

		$this->assertEquals( 'Edit Test Form', get_the_title( $edit_form_result->data['ID'] ) );

		$description = get_post_meta( $edit_form_result->data['ID'], 'ccf_form_description', true );
		$this->assertEquals( 'Edit test form description', $description );

		$button_text = get_post_meta( $edit_form_result->data['ID'], 'ccf_form_buttonText', true );

		$this->assertEquals( 'Edit Submit Text', $button_text  );

		$attached_fields = get_post_meta( $edit_form_result->data['ID'], 'ccf_attached_fields', true );

		$this->assertTrue( ! empty( $attached_fields ) );

		$this->assertEquals( count( $attached_fields ), 2 );

		foreach ( $attached_fields as $field_id ) {
			$field_type = get_post_meta( $field_id, 'ccf_field_type', true );
			$field_label = get_post_meta( $field_id, 'ccf_field_label', true );

			$this->assertTrue( strpos( $field_label, 'special label' ) !== false );

			if ( in_array( $field_type, array( 'dropdown', 'checkbox', 'radio' ) ) ) {
				$choices = get_post_meta( $field_id, 'ccf_attached_choices', true );

				$this->assertEquals( count( $choices ), 2 );
			}
		}
	}

	/**
	 * Test form deletion
	 *
	 * @since 6.0
	 */
	public function testDeleteForm() {
		$this->_createForm();
		$this->_createForm();
		$form = $this->_createForm();
		$this->_createForm();
		$this->_createForm();

		$request = new WP_REST_Request();
		$request->set_param( 'id', $form->data['ID'] );

		$this->api->delete_item( $request );

		$form = get_post( $form->data['ID'] );

		$this->assertTrue( $form->post_status === 'trash' );
	}

	/**
	 * Test cleanup of fields/choices on form deletion
	 *
	 * @since 6.0
	 */
	public function testDeleteFormCleanup() {
		$this->_createForm();
		$this->_createForm();
		$form = $this->_createForm( $this->advanced_fields );
		$this->_createForm();
		$this->_createForm();

		$attached_fields = get_post_meta( $form->data['ID'], 'ccf_attached_fields', true );

		$attached_choices = get_post_meta( $attached_fields[5], 'ccf_attached_choices', true );

		wp_delete_post( $form->data['ID'], true );

		$form = get_post( $form->data['ID'] );

		$this->assertTrue( $form === null );

		foreach ( $attached_fields as $field_id ) {
			$field = get_post( $field_id );

			$this->assertTrue( $field === null );
		}

		foreach ( $attached_choices as $choice_id ) {
			$choice = get_post( $choice_id );

			$this->assertTrue( $choice === null );
		}
	}

	/**
	 * Test getting submissions
	 *
	 * @since 6.0
	 */
	public function testGetSubmissions() {
		$form = $this->_createForm();
		$this->_createSubmission( $form->data['ID'] );
		$this->_createSubmission( $form->data['ID'] );
		$this->_createSubmission( $form->data['ID'] );
		$this->_createSubmission( $form->data['ID'] );

		$request = new WP_REST_Request();
		$request->set_param( 'id', $form->data['ID'] );

		$get_submissions_result = $this->api->get_submissions( $request );

		$submissions = $get_submissions_result->data;

		$this->assertEquals( 4, count( $submissions ) );

		foreach ( $submissions as $submission_object ) {
			$submission = get_post( $submission_object['ID'] );

			$this->assertTrue( ! empty( $submission ) );

			$data = get_post_meta( $submission_object['ID'], 'ccf_submission_data', true );
			$this->assertTrue( ! empty( $data ) );
		}
	}
}
