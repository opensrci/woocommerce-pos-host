<?php
/**
 * REST API Data Options Controller
 *
 * Handles requests to pos-host/options.
 *
 * @package WooCommerce_pos_host/Classes/API
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_REST_Options_Controller.
 *
 */
class POS_HOST_REST_Options_Controller extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'pos-host';


	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'options';


	/**
	 * Registers the necessary REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);
	}

	/**
	 * Check if a given request has access to read POS options.
	 *
	 * @since 5.3.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'view_register' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce-pos-host' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retrieve Poin of Sale options.
	 *
	 * @since 5.3.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure
	 */
	public function get_items( $request ) {
		$tax_classes = array_map(
			function( $class ) {
				return array(
					'slug' => sanitize_title( $class ),
					'name' => $class,
				);
			},
			WC_Tax::get_tax_classes()
		);

		$options = apply_filters(
			'pos_host_options',
			array(
				'localeconv'                      => localeconv(),
				'coupons_enabled'                 => wc_coupons_enabled(),
				'prices_include_tax'              => wc_prices_include_tax(),
				'tax_enabled'                     => wc_tax_enabled(),
				'rounding_precision'              => wc_get_rounding_precision(),
				'price_decimal_separator'         => wc_get_price_decimal_separator(),
				'price_decimals'                  => wc_get_price_decimals(),
				'tax_classes'                     => $tax_classes,
				'base_location'                   => pos_host_get_shop_location(),
				'tax_round_half_up'               => self::tax_round_half_up(),
				// Options.
				'tax_based_on'                    => get_option( 'woocommerce_tax_based_on' ),
				'default_customer_address'        => get_option( 'woocommerce_default_customer_address' ),
				'tax_round_at_subtotal'           => 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ),
				'tax_display_cart'                => get_option( 'woocommerce_tax_display_cart' ),
				'cache_customers'                 => 'yes' === get_option( 'pos_host_cache_customers', 'yes' ),
				'enable_weight_embedded_barcodes' => 'yes' === get_option( 'pos_host_enable_weight_embedded_barcodes', 'no' ),
				'upca_type'                       => get_option( 'pos_host_upca_type', 'price' ),
				'upca_multiplier'                 => get_option( 'pos_host_upca_multiplier', 100 ),
				'max_concurrent_requests'         => get_option( 'pos_host_max_concurrent_requests', 30 ),
				// Filters.
				'adjust_non_base_location_prices' => apply_filters( 'woocommerce_adjust_non_base_location_prices', true ),
				'apply_base_tax_for_local_pickup' => apply_filters( 'woocommerce_apply_base_tax_for_local_pickup', true ),
				'local_pickup_methods'            => apply_filters( 'woocommerce_local_pickup_methods', array( 'legacy_local_pickup', 'local_pickup' ) ),
			)
		);

		return rest_ensure_response( $options );
	}

	/**
	 * Check if WC will round the tax total half up/down.
	 *
	 * @return bool
	 */
	protected static function tax_round_half_up() {
		return 1.15 === wc_round_tax_total( 1.145, 2 ) ? true : false;
	}
}
