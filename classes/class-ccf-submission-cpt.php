<?php

class CCF_Submission_CPT {
	public function __construct() {}

	/**
	 * Setup post type
	 *
	 * @since 6.0
	 */
	public function setup() {
		add_action( 'init', array( $this, 'setup_cpt' ) );
	}

	/**
	 * Register source feed post type
	 *
	 * @since 6.0
	 */
	public function setup_cpt() {
		$args = array(
			'labels' => false,
			'public' => false,
			'query_var' => false,
			'publicly_queryable' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => false,
		);

		register_post_type( 'ccf_submission', $args );
	}

	/**
	 * Check if a field is a date
	 *
	 * @param array|string $value
	 * @since 6.0
	 * @return bool
	 */
	public function is_field_date( $value ) {
		if ( ! empty( $value['date'] ) || ( ! empty( $value['hour'] ) && ! empty( $value['minute'] ) && ! empty( $value['am-pm'] ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a field is an name
	 *
	 * @param array|string $value
	 * @since 6.0
	 * @return bool
	 */
	public function is_field_name( $value ) {
		if ( ! empty( $value['first'] ) || ! empty( $value['last'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a field is an address
	 *
	 * @param array|string $value
	 * @since 6.0
	 * @return bool
	 */
	public function is_field_address( $value ) {
		if ( ! empty( $value['street'] ) && ! empty( $value['city'] ) && ! empty( $value['zipcode'] ) && ! empty( $value['line_two'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get prettified field date
	 *
	 * @param array $value
	 * @since 6.0
	 * @return bool|string
	 */
	public function get_pretty_field_date( $value ) {
		$dateString = '';

		if ( ! empty( $value['date'] ) ) {
			$dateString .= $value['date'];
		} else {
			$dateString .= date( 'n/j/Y' );
		}

		if ( ! empty( $value['hour'] ) && ! empty( $value['minute'] ) && ! empty( $value['am-pm'] ) ) {
			$dateString .= ' ' . $value['hour'] . ':' . $value['minute'] . ' ' . $value['am-pm'];
		}

		if ( empty( $dateString ) ) {
			return '-';
		}

		return date( 'n/j/Y h:i a', strtotime( $dateString ) );
	}

	/**
	 * Get a prettified name
	 *
	 * @param array $value
	 * @since 6.0
	 * @return string
	 */
	public function get_pretty_field_name( $value ) {
		$nameString = $value['first'];

		if ( ! empty( $nameString ) ) {
			$nameString .= ' ';
		}

		if ( ! empty( $value['last'] ) ) {
			$nameString .= $value['last'];
		}

		if ( empty( $nameString ) ) {
			$nameString = '-';
		}

		return $nameString;
	}

	/**
	 * Get a prettified address
	 *
	 * @param array $value
	 * @since 6.0
	 * @return string
	 */
	public function get_pretty_field_address( $value ) {
		if ( empty( $value['street'] ) || empty( $value['city'] ) ) {
			return '-';
		}

		$addressString = $value['street'];

		if ( ! empty( $value['line_two'] ) ) {
			$addressString .= ' ' . $value['line_two'];
		}

		$addressString .= ', ' . $value['city'];

		if ( ! empty( $value['state'] ) ) {
			$addressString .= ', ' . $value['state'];
		}

		if ( ! empty( $value['zipcode'] ) ) {
			$addressString .= ' ' . $value['zipcode'];
		}

		if ( ! empty( $value['country'] ) ) {
			$addressString .= ' ' . $value['country'];
		}

		return $addressString;
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
