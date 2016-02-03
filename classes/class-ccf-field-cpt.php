<?php


class CCF_Field_CPT {

	/**
	 * Placeholder method
	 *
	 * @since 6.0
	 */
	public function __construct() {}

	/**
	 * Setup field CPT
	 *
	 * @since 6.0
	 */
	public function setup() {
		add_action( 'init', array( $this, 'setup_cpt' ) );
	}

	/**
	 * Register field post type
	 *
	 * @since 6.0
	 */
	public function setup_cpt() {

		$args = array(
			'label' => esc_html__( 'Form Fields', 'custom-contact-forms' ),
			'public' => false,
			'query_var' => false,
			'rewrite' => false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => false,
			'has_archive' => false,
		);

		register_post_type( 'ccf_field', $args );
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
