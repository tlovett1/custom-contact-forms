<?php

class CCFTestFieldErrors extends CCFTestBase {

	/**
	 * Test single line text field errors
	 *
	 * @since 6.0
	 */
	public function testSingleLineText() {
		$slug = 'single-line-text';
		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'single-line-text', 'required' => true ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ] = 'value';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test file field errors basic
	 *
	 * @since 6.4
	 */
	public function testFile() {
		$slug = 'file';
		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'file', 'required' => true ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_FILES[ 'ccf_field_' . $slug . '1' ] = array(
			'size' => 1000,
			'name' => 'test.jpg',
			'error' => 0,
		);

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test file field errors advanced
	 *
	 * @since 6.4
	 */
	public function testFileAdvanced() {
		$slug = 'file';
		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'file', 'required' => true, 'fileExtensions' => 'jpg, ', 'maxFileSize' => 1 ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		$_FILES[ 'ccf_field_' . $slug . '1' ] = array(
			'size' => 10000000,
			'name' => 'test.png',
			'error' => 0,
		);

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['file_size'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['file_extension'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_FILES[ 'ccf_field_' . $slug . '1' ] = array(
			'size' => 10000000,
			'name' => 'test.png',
			'error' => 5,
		);

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['file_upload'] ) );
	}

	/**
	 * Test that empty recaptcha field produces an error
	 *
	 * @since 6.2
	 */
	public function testRecaptchaText() {
		$slug = 'recaptcha';
		$form_response = $this->_createForm( array( array( 'type' => 'recaptcha', 'slug' => $slug ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['recaptcha'] ) );
	}

	/**
	 * Test paragraph field errors
	 *
	 * @since 6.0
	 */
	public function testParagraphText() {
		$slug = 'paragraph-text';
		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'paragraph-text', 'required' => true ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ] = 'value';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test dropdown field errors
	 *
	 * @since 6.0
	 */
	public function testDropdown() {
		$slug = 'dropdown';
		$choices = array(
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
		);

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'dropdown', 'required' => true, 'choices' => $choices ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['key'] = 'value';
		$_POST[ 'ccf_field_' . $slug . '1' ]['key2'] = 'value2';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test radio field errors
	 *
	 * @since 6.0
	 */
	public function testRadio() {
		$slug = 'radio';

		$choices = array(
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
		);

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'radio', 'required' => true, 'choices' => $choices ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['key'] = 'value';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test checkboxes field errors
	 *
	 * @since 6.0
	 */
	public function testCheckboxes() {
		$slug = 'checkboxes';

		$choices = array(
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
		);

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'checkboxes', 'required' => true, 'choices' => $choices ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['key'] = 'value';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test phone field errors
	 *
	 * @since 6.0
	 */
	public function testPhone() {
		$slug = 'phone';

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'phone', 'required' => true ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ] = '5555a';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['digits'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['chars'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ] = '3019998999';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'phone', 'required' => true, 'phoneFormat' => 'us' ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST[ 'ccf_field_' . $slug . '1' ] = '12345678';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['digits'] ) );
	}

	/**
	 * Test email field errors
	 *
	 * @since 6.0
	 */
	public function testEmail() {
		$slug = 'email';

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'email', 'required' => true ) ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['email_required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ] = 'test';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['email'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ] = 'test@test.com';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test email confirm field errors
	 *
	 * @since 6.0
	 */
	public function testEmailConfirm() {
		$slug = 'email';

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'email', 'required' => true, 'emailConfirmation' => true ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		$_POST[ 'ccf_field_' . $slug . '1' ]['email'] = '';
		$_POST[ 'ccf_field_' . $slug . '1' ]['confirm'] = '';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['email_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['confirm_required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['email'] = 'test';
		$_POST[ 'ccf_field_' . $slug . '1' ]['confirm'] = 'test2';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['email'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['match'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['email'] = 'test@test.com';
		$_POST[ 'ccf_field_' . $slug . '1' ]['confirm'] = 'test@test.com';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test name field errors
	 *
	 * @since 6.0
	 */
	public function testName() {
		$slug = 'name';
		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'name', 'required' => true ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['first_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['last_required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['first'] = 'first';
		$_POST[ 'ccf_field_' . $slug . '1' ]['last'] = 'last';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test website field errors
	 *
	 * @since 6.0
	 */
	public function testWebsite() {
		$slug = 'phone';

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'website', 'required' => true ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['website_required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ] = '5555a';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['website'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ] = 'http://google.com';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test us address field errors
	 *
	 * @since 6.0
	 */
	public function testUSAddress() {
		$slug = 'address';

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'address', 'addressType' => 'us', 'required' => true ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['street_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['city_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['zipcode_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['state_required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['street'] = 'test';
		$_POST[ 'ccf_field_' . $slug . '1' ]['state'] = 'test';
		$_POST[ 'ccf_field_' . $slug . '1' ]['city'] = 'test';
		$_POST[ 'ccf_field_' . $slug . '1' ]['zipcode'] = 'test';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test us address field errors
	 *
	 * @since 6.0
	 */
	public function testInternationalAddress() {
		$slug = 'address';

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'address', 'addressType' => 'international', 'required' => true ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['street_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['city_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['zipcode_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['state_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['country_required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['street'] = 'test';
		$_POST[ 'ccf_field_' . $slug . '1' ]['line_two'] = 'test';
		$_POST[ 'ccf_field_' . $slug . '1' ]['state'] = 'test';
		$_POST[ 'ccf_field_' . $slug . '1' ]['city'] = 'test';
		$_POST[ 'ccf_field_' . $slug . '1' ]['zipcode'] = 'test';
		$_POST[ 'ccf_field_' . $slug . '1' ]['country'] = 'test';

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test date only field errors
	 *
	 * @since 6.4
	 */
	public function testDateOnly() {
		$slug = 'date';

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'date', 'showDate' => true, 'showTime' => false, 'required' => true ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['date_required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['date'] = 'test';
		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['date'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['date'] = '5/12/2015';
		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test time only field errors
	 *
	 * @since 6.4
	 */
	public function testTimeOnly() {
		$slug = 'date';

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'date', 'showDate' => false, 'showTime' => true, 'required' => true ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['hour_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['minutes_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['am-pm_required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['hour'] = 'a';
		$_POST[ 'ccf_field_' . $slug . '1' ]['minute'] = 'b';
		$_POST[ 'ccf_field_' . $slug . '1' ]['am-pm'] = 'am';
		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['minute'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['hour'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['hour'] = '10';
		$_POST[ 'ccf_field_' . $slug . '1' ]['minute'] = '12';
		$_POST[ 'ccf_field_' . $slug . '1' ]['am-pm'] = 'am';
		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}

	/**
	 * Test date/time field errors
	 *
	 * @since 6.4
	 */
	public function testDateTime() {
		$slug = 'date';

		$form_response = $this->_createForm( array( array( 'slug' => $slug, 'type' => 'date', 'showDate' => true, 'showTime' => true, 'required' => true ) ) );

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['date_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['hour_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['minutes_required'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['am-pm_required'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['date'] = 'a';
		$_POST[ 'ccf_field_' . $slug . '1' ]['hour'] = 'a';
		$_POST[ 'ccf_field_' . $slug . '1' ]['minute'] = 'b';
		$_POST[ 'ccf_field_' . $slug . '1' ]['am-pm'] = 'am';
		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['minute'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['hour'] ) );
		$this->assertTrue( ! empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ]['date'] ) );

		CCF_Form_Handler::factory()->errors_by_form = array();

		$_POST[ 'ccf_field_' . $slug . '1' ]['date'] = '10/1/1900';
		$_POST[ 'ccf_field_' . $slug . '1' ]['hour'] = '10';
		$_POST[ 'ccf_field_' . $slug . '1' ]['minute'] = '12';
		$_POST[ 'ccf_field_' . $slug . '1' ]['am-pm'] = 'am';
		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( CCF_Form_Handler::factory()->errors_by_form[ $form_response->data['id'] ][ $slug . '1' ] ) );
	}
}
