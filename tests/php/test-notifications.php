<?php

class CCFTestNotifications extends CCFTestBase {

	/**
	 * Keep track of test notifications
	 *
	 * @since 7.1
	 */
	public $notifications = array();

	/**
	 * Test no notifications
	 *
	 * @since 7.1
	 */
	public function testNoNotifications() {
		$slug = 'single-line-text';
		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slug, 
					'type' => 'single-line-text', 
					'required' => true,
				)
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_send_notification', function( $email, $subject, $notification_content, $notification_headers, $notification ) {
			$this->notifications[] = array(
				'email' => $email,
				'subject' => $subject,
				'content' => $notification_content,
				'headers' => $notification_headers,
				'notification' => $notification,
			);
		}, 10, 5 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( $this->notifications ) );
	}

	/**
	 * Test one notification
	 *
	 * @since 7.1
	 */
	public function testOneNotifications() {
		$slug = 'single-line-text';
		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slug, 
					'type' => 'single-line-text', 
					'required' => true,
				),
			),
			array(
				'notifications' => array(
					array(
						'title' => '',
						'content' => '',
						'active' => true,
						'addresses' => array(
							array(
								'type' => 'custom',
								'email' => 'test@test.com',
								'field' => '',
							)
						),
						'fromType' => 'default',
						'fromAddress' => '',
						'fromField' => '',
						'subjectType' => 'default',
						'subject' => '',
						'subjectField' => '',
						'fromNameType' => 'custom',
						'fromName' => 'WordPress',
						'fromNameField' => '',
					),
				)
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_send_notification', function( $email, $subject, $notification_content, $notification_headers, $notification ) {
			$this->notifications[] = array(
				'email' => $email,
				'subject' => $subject,
				'content' => $notification_content,
				'headers' => $notification_headers,
				'notification' => $notification,
			);
		}, 10, 5 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertEquals( 1, count( $this->notifications ) );
	}

	/**
	 * Test multiple notifications
	 *
	 * @since 7.1
	 */
	public function testMultipleNotifications() {
		$slug = 'single-line-text';
		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slug, 
					'type' => 'single-line-text', 
					'required' => true,
				),
			),
			array(
				'notifications' => array(
					array(
						'title' => 'one',
						'content' => '',
						'active' => true,
						'addresses' => array(
							array(
								'type' => 'custom',
								'email' => 'test@test.com',
								'field' => '',
							)
						),
						'fromType' => 'default',
						'fromAddress' => '',
						'fromField' => '',
						'subjectType' => 'default',
						'subject' => '',
						'subjectField' => '',
						'fromNameType' => 'custom',
						'fromName' => 'WordPress',
						'fromNameField' => '',
					),
					array(
						'title' => 'two',
						'content' => '',
						'active' => true,
						'addresses' => array(
							array(
								'type' => 'custom',
								'email' => 'test@test.com',
								'field' => '',
							)
						),
						'fromType' => 'default',
						'fromAddress' => '',
						'fromField' => '',
						'subjectType' => 'default',
						'subject' => '',
						'subjectField' => '',
						'fromNameType' => 'custom',
						'fromName' => 'WordPress',
						'fromNameField' => '',
					),
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_send_notification', function( $email, $subject, $notification_content, $notification_headers, $notification ) {
			$this->notifications[] = array(
				'email' => $email,
				'subject' => $subject,
				'content' => $notification_content,
				'headers' => $notification_headers,
				'notification' => $notification,
			);
		}, 10, 5 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertEquals( 2, count( $this->notifications ) );
	}

	/**
	 * Test inactive notification
	 *
	 * @since 7.1
	 */
	public function testInactiveNotification() {
		$slug = 'single-line-text';
		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slug, 
					'type' => 'single-line-text', 
					'required' => true,
				),
			),
			array(
				'notifications' => array(
					array(
						'title' => 'one',
						'content' => '',
						'active' => false,
						'addresses' => array(
							array(
								'type' => 'custom',
								'email' => 'test@test.com',
								'field' => '',
							)
						),
						'fromType' => 'default',
						'fromAddress' => '',
						'fromField' => '',
						'subjectType' => 'default',
						'subject' => '',
						'subjectField' => '',
						'fromNameType' => 'custom',
						'fromName' => 'WordPress',
						'fromNameField' => '',
					),
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_send_notification', function( $email, $subject, $notification_content, $notification_headers, $notification ) {
			$this->notifications[] = array(
				'email' => $email,
				'subject' => $subject,
				'content' => $notification_content,
				'headers' => $notification_headers,
				'notification' => $notification,
			);
		}, 10, 5 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertEquals( 0, count( $this->notifications ) );
	}

	/**
	 * Test notification no addresses
	 *
	 * @since 7.1
	 */
	public function testNoAddressesNotification() {
		$slug = 'single-line-text';
		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slug, 
					'type' => 'single-line-text', 
					'required' => true,
				),
			),
			array(
				'notifications' => array(
					array(
						'title' => 'one',
						'content' => '',
						'active' => true,
						'addresses' => array(),
						'fromType' => 'default',
						'fromAddress' => '',
						'fromField' => '',
						'subjectType' => 'default',
						'subject' => '',
						'subjectField' => '',
						'fromNameType' => 'custom',
						'fromName' => 'WordPress',
						'fromNameField' => '',
					),
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_send_notification', function( $email, $subject, $notification_content, $notification_headers, $notification ) {
			$this->notifications[] = array(
				'email' => $email,
				'subject' => $subject,
				'content' => $notification_content,
				'headers' => $notification_headers,
				'notification' => $notification,
			);
		}, 10, 5 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertEquals( 0, count( $this->notifications ) );
	}

	/**
	 * Test notification custom subject
	 *
	 * @since 7.1
	 */
	public function testNotificationCustomSubject() {
		$slug = 'single-line-text';
		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slug, 
					'type' => 'single-line-text', 
					'required' => true,
				),
			),
			array(
				'notifications' => array(
					array(
						'title' => 'one',
						'content' => '',
						'active' => true,
						'addresses' => array(
							array(
								'type' => 'custom',
								'email' => 'test@test.com',
								'field' => '',
							)
						),
						'fromType' => 'default',
						'fromAddress' => '',
						'fromField' => '',
						'subjectType' => 'custom',
						'subject' => 'test',
						'subjectField' => '',
						'fromNameType' => 'custom',
						'fromName' => 'WordPress',
						'fromNameField' => '',
					),
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_send_notification', function( $email, $subject, $notification_content, $notification_headers, $notification ) {
			$this->notifications[] = array(
				'email' => $email,
				'subject' => $subject,
				'content' => $notification_content,
				'headers' => $notification_headers,
				'notification' => $notification,
			);
		}, 10, 5 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertEquals( 'test', $this->notifications[0]['subject'] );
	}

	/**
	 * Test notification field subject
	 *
	 * @since 7.1
	 */
	public function testNotificationFieldSubject() {
		$slug = 'single-line-text';
		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slug, 
					'type' => 'single-line-text', 
					'required' => true,
				),
			),
			array(
				'notifications' => array(
					array(
						'title' => 'one',
						'content' => '',
						'active' => true,
						'addresses' => array(
							array(
								'type' => 'custom',
								'email' => 'test@test.com',
								'field' => '',
							)
						),
						'fromType' => 'default',
						'fromAddress' => '',
						'fromField' => '',
						'subjectType' => 'field',
						'subject' => '',
						'subjectField' => $slug . '1',
						'fromNameType' => 'custom',
						'fromName' => 'WordPress',
						'fromNameField' => '',
					),
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_send_notification', function( $email, $subject, $notification_content, $notification_headers, $notification ) {
			$this->notifications[] = array(
				'email' => $email,
				'subject' => $subject,
				'content' => $notification_content,
				'headers' => $notification_headers,
				'notification' => $notification,
			);
		}, 10, 5 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertEquals( 'value', $this->notifications[0]['subject'] );
	}

	/**
	 * Test notification custom from name custom from email
	 *
	 * @since 7.1
	 */
	public function testNotificationCustomFromNameCustomFromEmail() {
		$slug = 'single-line-text';
		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slug, 
					'type' => 'single-line-text', 
					'required' => true,
				),
			),
			array(
				'notifications' => array(
					array(
						'title' => 'one',
						'content' => '',
						'active' => true,
						'addresses' => array(
							array(
								'type' => 'custom',
								'email' => 'test@test.com',
								'field' => '',
							)
						),
						'fromType' => 'custom',
						'fromAddress' => 'test@test.com',
						'fromField' => '',
						'subjectType' => 'field',
						'subject' => '',
						'subjectField' => '',
						'fromNameType' => 'custom',
						'fromName' => 'something',
						'fromNameField' => '',
					),
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_send_notification', function( $email, $subject, $notification_content, $notification_headers, $notification ) {
			$this->notifications[] = array(
				'email' => $email,
				'subject' => $subject,
				'content' => $notification_content,
				'headers' => $notification_headers,
				'notification' => $notification,
			);
		}, 10, 5 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( in_array( 'From: something <test@test.com>', $this->notifications[0]['headers'] ) );
	}

	/**
	 * Test notification custom from name custom default from email
	 *
	 * @since 7.1
	 */
	public function testNotificationCustomFromNameDefaultFromEmail() {
		$slug = 'single-line-text';
		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slug, 
					'type' => 'single-line-text', 
					'required' => true,
				),
			),
			array(
				'notifications' => array(
					array(
						'title' => 'one',
						'content' => '',
						'active' => true,
						'addresses' => array(
							array(
								'type' => 'custom',
								'email' => 'test@test.com',
								'field' => '',
							)
						),
						'fromType' => 'default',
						'fromAddress' => '',
						'fromField' => '',
						'subjectType' => 'field',
						'subject' => '',
						'subjectField' => '',
						'fromNameType' => 'custom',
						'fromName' => 'something',
						'fromNameField' => '',
					),
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_send_notification', function( $email, $subject, $notification_content, $notification_headers, $notification ) {
			$this->notifications[] = array(
				'email' => $email,
				'subject' => $subject,
				'content' => $notification_content,
				'headers' => $notification_headers,
				'notification' => $notification,
			);
		}, 10, 5 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( in_array( 'From: something', $this->notifications[0]['headers'] ) );
	}

	/**
	 * Test notification field from name custom default from email
	 *
	 * @since 7.1
	 */
	public function testNotificationFieldFromNameDefaultFromEmail() {
		$slug = 'name';
		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slug, 
					'type' => 'name', 
					'required' => true,
				),
			),
			array(
				'notifications' => array(
					array(
						'title' => 'one',
						'content' => '',
						'active' => true,
						'addresses' => array(
							array(
								'type' => 'custom',
								'email' => 'test@test.com',
								'field' => '',
							)
						),
						'fromType' => 'default',
						'fromAddress' => '',
						'fromField' => '',
						'subjectType' => 'field',
						'subject' => '',
						'subjectField' => '',
						'fromNameType' => 'field',
						'fromName' => '',
						'fromNameField' => $slug . '1',
					),
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1']['first'] = 'first';
		$_POST['ccf_field_' . $slug . '1']['last'] = 'last';

		add_action( 'ccf_send_notification', function( $email, $subject, $notification_content, $notification_headers, $notification ) {
			$this->notifications[] = array(
				'email' => $email,
				'subject' => $subject,
				'content' => $notification_content,
				'headers' => $notification_headers,
				'notification' => $notification,
			);
		}, 10, 5 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( in_array( 'From: first last', $this->notifications[0]['headers'] ) );
	}

	/**
	 * Test notification field from name custom default from email
	 *
	 * @since 7.1
	 */
	public function testNotificationCustomFromNameFieldFromEmail() {
		$slug = 'email';
		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slug, 
					'type' => 'email', 
					'required' => true,
				),
			),
			array(
				'notifications' => array(
					array(
						'title' => 'one',
						'content' => '',
						'active' => true,
						'addresses' => array(
							array(
								'type' => 'custom',
								'email' => 'hello@test.com',
								'field' => '',
							)
						),
						'fromType' => 'field',
						'fromAddress' => '',
						'fromField' => $slug . '1',
						'subjectType' => 'field',
						'subject' => '',
						'subjectField' => '',
						'fromNameType' => 'custom',
						'fromName' => 'WordPress',
						'fromNameField' => '',
					),
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'test@test.com';

		add_action( 'ccf_send_notification', function( $email, $subject, $notification_content, $notification_headers, $notification ) {
			$this->notifications[] = array(
				'email' => $email,
				'subject' => $subject,
				'content' => $notification_content,
				'headers' => $notification_headers,
				'notification' => $notification,
			);
		}, 10, 5 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( in_array( 'From: WordPress <test@test.com>', $this->notifications[0]['headers'] ) );
	}

	/**
	 * Test notification multiple addresses
	 *
	 * @since 7.1
	 */
	public function testNotificationMultipleAddresses() {
		$slug = 'single-line-text';
		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slug, 
					'type' => 'single-line-text', 
					'required' => true,
				),
			),
			array(
				'notifications' => array(
					array(
						'title' => 'one',
						'content' => '',
						'active' => true,
						'addresses' => array(
							array(
								'type' => 'custom',
								'email' => 'test@test.com',
								'field' => '',
							),
							array(
								'type' => 'custom',
								'email' => 'test1@test.com',
								'field' => '',
							)
						),
						'fromType' => 'default',
						'fromAddress' => '',
						'fromField' => '',
						'subjectType' => 'field',
						'subject' => '',
						'subjectField' => '',
						'fromNameType' => 'custom',
						'fromName' => 'WordPress',
						'fromNameField' => '',
					),
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'test';

		add_action( 'ccf_send_notification', function( $email, $subject, $notification_content, $notification_headers, $notification ) {
			$this->notifications[] = array(
				'email' => $email,
				'subject' => $subject,
				'content' => $notification_content,
				'headers' => $notification_headers,
				'notification' => $notification,
			);
		}, 10, 5 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertEquals( 2, count( $this->notifications ) );

		$this->assertEquals( 'test@test.com', $this->notifications[0]['email'] );
		$this->assertEquals( 'test1@test.com', $this->notifications[1]['email'] );
	}
}
