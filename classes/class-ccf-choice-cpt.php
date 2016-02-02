<?php


class CCF_Choice_CPT {

	/**
	 * Placeholder method
	 *
	 * @since 6.0
	 */
	public function __construct() {}

	/**
	 * Setup hooks for post type registration
	 *
	 * @since 6.0
	 */
	public function setup() {
		add_action( 'init', array( $this, 'setup_cpt' ) );
	}

	/**
	 * Register field choice post type. A field choice is an option for a dropdown, radio, or checkbox field.
	 *
	 * @since 6.0
	 */
	public function setup_cpt() {
		$args = array(
			'label' => esc_html__( 'Form Field Choices', 'custom-contact-forms' ),
			'public' => false,
			'has_archive' => false,
			'query_var' => false,
			'rewrite' => false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => false,
		);

		register_post_type( 'ccf_choice', $args );
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
