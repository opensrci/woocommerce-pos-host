<?php
/**
 * REST API Users Controller
 *
 * Handles requests to pos-host/users.
 *
 * @package WooCommerce_pos_host/Classes/API
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_REST_Users_Controller.
 */
class POS_HOST_REST_Users_Controller extends WP_REST_Users_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->namespace = 'pos-host';
		$this->rest_base = 'users';

		add_filter( 'rest_user_query', array( $this, 'filter_user_api_query_args' ), 99, 2 );
		add_filter( 'rest_prepare_user', array( $this, 'filter_customer_response' ), 99, 3 );
	}

	public function filter_user_api_query_args( $args, $request ) {
		$referer = $request->get_header( 'referer' );
		if ( strpos( $referer, 'pos-host' ) === false ) {
			return $args;
		}

		$meta_query = isset( $args['meta_query'] ) ? (array) $args['meta_query'] : array();

		if ( array_key_exists( 'outlet_id', $request->get_params() ) ) {
			array_push(
				$meta_query,
				array(
					array(
						'key'     => 'pos_host_assigned_outlets',
						'value'   => sprintf( 's:%s:"%s";', strlen( $request->get_param( 'outlet_id' ) ), $request->get_param( 'outlet_id' ) ),
						'compare' => 'LIKE',
					),
				)
			);

			// when search has any value it tries to add search_columns arg automatically. so we mute it.
			$args['search'] = '';
		}

		$args['meta_query'] = $meta_query;

		// Remove the has_published_posts flag.
		unset( $args['has_published_posts'] );

		return $args;
	}

	public function filter_customer_response( $response, $user, $request ) {
		$user_data                   = $response->get_data();
		$user_data['points_balance'] = 0;
		$user_data['email']          = isset( $user_data['email'] ) ? $user_data['email'] : $user->user_email;
		$user_data['avatar_url']     = get_avatar_url( $user_data['email'], array( 'size' => '256' ) );
		$user_data['capabilities']   = $user->get_role_caps();
		$user_data['first_name']     = $user->first_name;
		$user_data['last_name']      = $user->last_name;
		$user_data['last_name']      = $user->last_name;
		$user_data['username']       = $user->user_login;

		global $wpdb;
		$roles              = (array) get_user_meta( $user->ID, $wpdb->prefix . 'capabilities', true );
		$user_data['roles'] = array_keys( $roles );

		$response->set_data( $user_data );

		return $response;
	}

	/**
	 * Get the query params for collections.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		if ( isset( $params['per_page'] ) ) {
			$params['per_page']['minimum'] = -1;

			// Use intval() instead of absint() for sanitization.
			$params['per_page']['sanitize_callback'] = array( $this, 'sanitize_per_page' );
		}

		return $params;
	}

	/**
	 * Sanitize the per_page param.
	 *
	 * @since 5.2.9
	 */
	public function sanitize_per_page( $value, $request, $param ) {
		return intval( $value, 10 );
	}
}
