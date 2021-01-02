<?php
/**
 * REST API Shipping Zone Methods Controller
 *
 * Handles requests to pos-host/shipping/zones/<id>/methods.
 *
 * @package WooCommerce_pos_host/Classes/API
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_REST_Shipping_Zone_Methods_Controller.
 */
class POS_HOST_REST_Shipping_Zone_Methods_Controller extends WC_REST_Shipping_Zone_Methods_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'pos-host';

	/**
	 * Check whether a given request has permission to read shipping zone methods.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_shipping_enabled() ) {
			return new WP_Error( 'rest_no_route', __( 'Shipping is disabled.', 'woocommerce-pos-host' ), array( 'status' => 404 ) );
		}

		if ( ! current_user_can( 'view_register' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce-pos-host' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
}
