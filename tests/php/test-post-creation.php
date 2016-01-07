<?php

class CCFTestPostCreation extends CCFTestBase {

	/**
	 * Keep track of test post creation
	 *
	 * @since 7.3
	 */
	public $post_creation = false;

	/**
	 * Test no post creations
	 *
	 * @since 7.3
	 */
	public function testNoPostCreations() {
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

		add_action( 'ccf_post_creation', function( $post_creation_id, $form_id, $submission_id, $submission ) {
			$this->post_creation = array(
				'post_creation_id' => $post_creation_id,
				'form_id' => $form_id,
				'submission_id' => $submission_id,
				'submission' => $submission,
			);
		}, 10, 4 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( empty( $this->post_creation ) );
	}

	/**
	 * Test simple post creation
	 *
	 * @since 7.3
	 */
	public function testSimplePostCreation() {
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
				'postCreation' => true,
				'postCreationType' => 'post',
				'postCreationStatus' => 'draft',
				'postFieldMappings' => array(
					array(
						'postField' => 'post_title',
						'formField' => 'single-line-text-1',
						'customFieldKey' => '',
					)
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_post_creation', function( $post_creation_id, $form_id, $submission_id, $submission ) {
			$this->post_creation = array(
				'post_creation_id' => $post_creation_id,
				'form_id' => $form_id,
				'submission_id' => $submission_id,
				'submission' => $submission,
			);
		}, 10, 4 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( $this->post_creation ) );
	}

	/**
	 * Test post creation post type
	 *
	 * @since 7.3
	 */
	public function testPostCreationType() {
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
				'postCreation' => true,
				'postCreationType' => 'page',
				'postCreationStatus' => 'draft',
				'postFieldMappings' => array(
					array(
						'postField' => 'post_title',
						'formField' => 'single-line-text1',
						'customFieldKey' => '',
					)
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_post_creation', function( $post_creation_id, $form_id, $submission_id, $submission ) {
			$this->post_creation = array(
				'post_creation_id' => $post_creation_id,
				'form_id' => $form_id,
				'submission_id' => $submission_id,
				'submission' => $submission,
			);
		}, 10, 4 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( $this->post_creation ) );
		$this->assertEquals( 'page', get_post_type( $this->post_creation['post_creation_id'] ) );
	}

	/**
	 * Test post creation post status
	 *
	 * @since 7.3
	 */
	public function testPostCreationStatus() {
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
				'postCreation' => true,
				'postCreationType' => 'post',
				'postCreationStatus' => 'publish',
				'postFieldMappings' => array(
					array(
						'postField' => 'post_title',
						'formField' => 'single-line-text1',
						'customFieldKey' => '',
					),
					array(
						'postField' => 'post_content',
						'formField' => 'single-line-text1',
						'customFieldKey' => '',
					)
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_post_creation', function( $post_creation_id, $form_id, $submission_id, $submission ) {
			$this->post_creation = array(
				'post_creation_id' => $post_creation_id,
				'form_id' => $form_id,
				'submission_id' => $submission_id,
				'submission' => $submission,
			);
		}, 10, 4 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( $this->post_creation ) );
		$this->assertEquals( 'publish', get_post_status( $this->post_creation['post_creation_id'] ) );
	}

	/**
	 * Test post creation no title
	 *
	 * @since 7.3
	 */
	public function testPostCreationNoTitle() {
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
				'postCreation' => true,
				'postCreationType' => 'post',
				'postCreationStatus' => 'publish',
				'postFieldMappings' => array(
					array(
						'postField' => 'post_excerpt',
						'formField' => 'single-line-text1',
						'customFieldKey' => '',
					),
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );
		$_POST['ccf_field_' . $slug . '1'] = 'value';

		add_action( 'ccf_post_creation', function( $post_creation_id, $form_id, $submission_id, $submission ) {
			$this->post_creation = array(
				'post_creation_id' => $post_creation_id,
				'form_id' => $form_id,
				'submission_id' => $submission_id,
				'submission' => $submission,
			);
		}, 10, 4 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( $this->post_creation ) );
		$this->assertEquals( 'publish', get_post_status( $this->post_creation['post_creation_id'] ) );
	}

	/**
	 * Test post creation fields
	 *
	 * @since 7.3
	 */
	public function testPostCreationFields() {
		$slugs = array(
			'single-line-text',
			'name',
			'paragraph-text',
			'paragraph-text',
			'email',
			'checkboxes',
			'radio',
			'dropdown',
		);

		$form_response = $this->_createForm(
			array(
				array(
					'slug' => $slugs[0], 
					'type' => 'single-line-text', 
					'required' => true,
				),
				array(
					'slug' => $slugs[1], 
					'type' => 'name', 
					'required' => true,
				),
				array(
					'slug' => $slugs[2], 
					'type' => 'paragraph-text', 
					'required' => true,
				),
				array(
					'slug' => $slugs[3], 
					'type' => 'paragraph-text', 
					'required' => true,
				),
				array(
					'slug' => $slugs[4], 
					'type' => 'email', 
					'required' => true,
					'confirm' => true,
				),
				array(
					'slug' => $slugs[5], 
					'type' => 'checkboxes', 
					'choices' => array(
						array(
							'label' => 'tag1',
							'value' => 'tag1',
							'selected' => false,
						),
						array(
							'label' => 'tag2',
							'value' => 'tag2',
							'selected' => false,
						),
						array(
							'label' => 'tag3',
							'value' => 'tag3',
							'selected' => false,
						),
					),
					'required' => true,
				),
				array(
					'slug' => $slugs[6], 
					'type' => 'radio', 
					'choices' => array(
						array(
							'label' => 'tag4',
							'value' => 'tag4',
							'selected' => false,
						),
					),
					'required' => true,
				),
				array(
					'slug' => $slugs[7], 
					'type' => 'dropdown', 
					'choices' => array(
						array(
							'label' => 'tag5',
							'value' => 'tag5',
							'selected' => false,
						),
					),
					'required' => true,
				),
			),
			array(
				'postCreation' => true,
				'postCreationType' => 'post',
				'postCreationStatus' => 'publish',
				'postFieldMappings' => array(
					array(
						'postField' => 'post_title',
						'formField' => $slugs[0] . '1',
						'customFieldKey' => '',
					),
					array(
						'postField' => 'custom_field',
						'formField' => $slugs[1] . '2',
						'customFieldKey' => 'name',
					),
					array(
						'postField' => 'post_content',
						'formField' => $slugs[2] . '3',
						'customFieldKey' => '',
					),
					array(
						'postField' => 'post_excerpt',
						'formField' => $slugs[3] . '4',
						'customFieldKey' => '',
					),
					array(
						'postField' => 'custom_field',
						'formField' => $slugs[4] . '5',
						'customFieldKey' => 'email',
					),
					array(
						'postField' => 'post_tag',
						'formField' => $slugs[5] . '6',
						'customFieldKey' => '',
					),
					array(
						'postField' => 'post_tag',
						'formField' => $slugs[6] . '7',
						'customFieldKey' => '',
					),
					array(
						'postField' => 'post_tag',
						'formField' => $slugs[7] . '8',
						'customFieldKey' => '',
					),
				),
			)
		);

		$_POST['form_id'] = $form_response->data['id'];
		$_POST['ccf_form'] = true;
		$_POST['form_nonce'] = wp_create_nonce( 'ccf_form' );

		$_POST['ccf_field_' . $slugs[0] . '1'] = 'title';
		$_POST['ccf_field_' . $slugs[1] . '2'] = array( 'first' => 'taylor', 'last' => 'lovett' );
		$_POST['ccf_field_' . $slugs[2] . '3'] = 'content';
		$_POST['ccf_field_' . $slugs[3] . '4'] = 'excerpt';
		$_POST['ccf_field_' . $slugs[4] . '5'] = array( 'email' => 'test@test.com', 'confirm' => 'test@test.com' );
		$_POST['ccf_field_' . $slugs[5] . '6'] = array( 'tag1', 'tag2', 'tag3' );
		$_POST['ccf_field_' . $slugs[6] . '7'] = 'tag4';
		$_POST['ccf_field_' . $slugs[7] . '8'] = 'tag5';

		add_action( 'ccf_post_creation', function( $post_creation_id, $form_id, $submission_id, $submission ) {
			$this->post_creation = array(
				'post_creation_id' => $post_creation_id,
				'form_id' => $form_id,
				'submission_id' => $submission_id,
				'submission' => $submission,
			);
		}, 10, 4 );

		CCF_Form_Handler::factory()->submit_listen();

		$this->assertTrue( ! empty( $this->post_creation ) );

		$post = get_post( $this->post_creation['post_creation_id'] );

		$this->assertEquals( 'title', $post->post_title );
		$this->assertEquals( 'taylor lovett', get_post_meta( $this->post_creation['post_creation_id'], 'name', true ) );
		$this->assertEquals( 'content', $post->post_content );
		$this->assertEquals( 'excerpt', $post->post_excerpt );
		$this->assertEquals( 'test@test.com', get_post_meta( $this->post_creation['post_creation_id'], 'email', true ) );

		$tags = wp_get_post_tags( $this->post_creation['post_creation_id'] );
		$this->assertEquals( 5, count( $tags ) );

		$i = 1;
		foreach ( $tags as $tag ) {
			$this->assertEquals( 'tag' . $i, $tag->name);
			$i++;
		}

	}
}
