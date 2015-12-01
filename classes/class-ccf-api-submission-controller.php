<?php

class CCF_API_Submission_Controller extends WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since 7.0
	 */
	public function register_routes() {
		$version = '1';
		$namespace = 'ccf/v' . $version;

		register_rest_route( $namespace, '/submissions', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => array(),
			),
		) );

		register_rest_route( $namespace, '/submissions/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'            => array(
					'context'          => array(
				    	'default'      => 'view',
					),
				),
			),
		) );

		register_rest_route( $namespace, '/submissions/schema', array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	* Get a collection of items
	*
	* @param  WP_REST_Request $request Full data about the request.
	* @since  7.0
	* @return WP_Error|WP_REST_Response
	*/
	public function get_items( $request ) {
		$params = $request->get_params();

		$page = 1;
		if ( ! empty( $params['page'] ) ) {
			$page = $params['page'];
		}

		$query = new WP_Query( array(
			'post_type'   => 'ccf_submission',
			'page'        => (int) $page,
			'post_status' => 'publish',
		) );

		$data = array();

		foreach ( $query->posts as $item ) {
			$data[] = $this->prepare_item_for_response( (array) $item );
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	* Get one item from the collection
	*
	* @param  WP_REST_Request $request Full data about the request.
	* @since  7.0
	* @return WP_Error|WP_REST_Response
	*/
	public function get_item( $request ) {
		$params = $request->get_params();

		$item = get_post( $params['id'], ARRAY_A );

		$data = $this->prepare_item_for_response( $item );
		$data->ID = (int) $params['id'];

		if ( is_array( $data ) ) {
			return new WP_REST_Response( $data, 200 );
		} else {
			return new WP_Error( 'cant-find', __( 'Submission not found', 'custom-contact-forms' ) );
		}
	}

	/**
	* Check if a given request has access to get items
	*
	* @param WP_REST_Request $request Full data about the request.
	* @return WP_Error|bool
	*/
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'edit_posts' );
	}

	/**
	* Check if a given request has access to get a specific item
	*
	* @param  WP_REST_Request $request Full data about the request.
	* @since  7.0
	* @return WP_Error|bool
	*/
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	* Prepare the item for the REST response
	*
	* @param  int $item
	* @param  int $item_id
	* @since  7.0
	* @return array
	*/
	public function prepare_item_for_response( $item ) {
		$data = array(
			'id'           => $item->ID,
			'date'         => $this->prepare_date_response( $item->post_date_gmt, $item->post_date ),
			'date_gmt'     => $this->prepare_date_response( $item->post_date_gmt ),
			'guid'         => array(
				'raw'      => $item->guid,
			),
			'modified'     => $this->prepare_date_response( $item->post_modified_gmt, $item->post_modified ),
			'modified_gmt' => $this->prepare_date_response( $item->post_modified_gmt ),
			'slug'         => $item->post_name,
			'status'       => $item->post_status,
			'type'         => $item->post_type,
			'link'         => get_permalink( $item->ID ),
			'title'        => array(
				'raw'      => $item->post_title,
				'rendered' => get_the_title( $item->ID ),
			),
		);

		$data['data'] = get_post_meta( $item_id, 'ccf_submission_data', true );
		$daya['ip_address'] = esc_html( get_post_meta( $item_id, 'ccf_submission_ip', true ) );

		return $data;
	}

	/**
	 * Format date for response
	 * 
	 * @param  string $date_gmt
	 * @param  string $date
	 * @since  7.0
	 * @return string
	 */
	protected function prepare_date_response( $date_gmt, $date = null ) {
		if ( '0000-00-00 00:00:00' === $date_gmt ) {
			return null;
		}

		if ( isset( $date ) ) {
			return mysql_to_rfc3339( $date );
		}

		return mysql_to_rfc3339( $date_gmt );
	}

	/**
	* Get the query params for collections
	*
	* @since  7.0
	* @return array
	*/
	public function get_collection_params() {
		return array(
			'page'                   => array(
				'description'        => 'Current page of the collection.',
				'type'               => 'integer',
				'default'            => 1,
				'sanitize_callback'  => 'absint',
			),
			'per_page'               => array(
				'description'        => 'Maximum number of items to be returned in result set.',
				'type'               => 'integer',
				'default'            => 10,
				'sanitize_callback'  => 'absint',
			),
		);
	}
}
