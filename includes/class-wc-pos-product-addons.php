<?php
/**
 * Product Add-Ons
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;


if ( class_exists( 'POS_HOST_Product_Addons', false ) ) {
	return new POS_HOST_Product_Addons();
}

/**
 * POS_HOST_Product_Addons.
 */
class POS_HOST_Product_Addons {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'pos_host_enqueue_scripts', array( $this, 'pos_enqueue_scripts' ), 10, 1 );
		add_filter( 'pos_host_i18n_js', array( $this, 'include_i18n_js' ), 20, 1 );
		add_filter( 'pos_host_inline_js', array( $this, 'add_inline_js' ), 20, 1 );
	}

	public function pos_enqueue_scripts( $sctipts ) {
		$sctipts['pos-host-product-addon'] = POS_HOST()->plugin_url() . '/assets/js/register/product-addon.js';
		return $sctipts;
	}
	public function include_i18n_js( $i18n ) {
		$i18n['product_addons_i18n'] = include_once POS_HOST()->plugin_path() . '/i18n/product-addons.php';
		return $i18n;
	}

	public function add_inline_js( $inline_js ) {
		$params = array(
			'price_display_suffix'   => esc_attr( get_option( 'woocommerce_price_display_suffix' ) ),
			'ajax_url'               => WC()->ajax_url(),
			'currency_format_symbol' => get_woocommerce_currency_symbol(),
		);

		$global_add_on = $this->get_product_addons();
		$array         = json_encode( $params );

		$inline_js['product_addons_params'] = '<script type="text/javascript" class="pos_host_product_addons_params" > var pos_host_product_addons_params = ' . $array . '; </script>';
		$inline_js['product_global_add_on'] = '<script type="text/javascript" class="pos_host_product_global_add_on" > var pos_host_product_global_add_on = ' . $global_add_on . '; </script>';

		return $inline_js;
	}

	private function get_product_addons() {
		 $raw_addons = array();
		$addons      = array();

		$args = array(
			'posts_per_page'   => -1,
			'orderby'          => 'meta_value',
			'order'            => 'ASC',
			'meta_key'         => '_priority',
			'post_type'        => 'global_product_addon',
			'post_status'      => 'publish',
			'suppress_filters' => true,
		);

		$global_addons = get_posts( $args );

		if ( $global_addons ) {
			foreach ( $global_addons as $global_addon ) {
				$priority     = get_post_meta( $global_addon->ID, '_priority', true );
				$all_products = get_post_meta( $global_addon->ID, '_all_products', true );
				$args         = array(
					'orderby' => 'name',
					'order'   => 'ASC',
					'fields'  => 'ids',
				);

				$raw_addons[ $priority ][ $global_addon->ID ] = array(
					'all_products' => '1' === $all_products || 1 === $all_products ? true : false,
					'categories'   => wp_get_post_terms( $global_addon->ID, 'product_cat', $args ),
					'addons'       => apply_filters( 'get_product_addons_fields', array_filter( (array) get_post_meta( $global_addon->ID, '_product_addons', true ) ), $global_addon->ID ),
				);
			}
			ksort( $raw_addons );

			foreach ( $raw_addons as $addon_group ) {
				if ( $addon_group ) {
					foreach ( $addon_group as $addon ) {
						$addons[] = $addon;
					}
				}
			}
		}
		return json_encode( $addons );
	}


	/**
	 * Main POS_HOST_Product_Addons Instance
	 *
	 * Ensures only one instance of POS_HOST_Product_Addons is loaded or can be loaded.
	 *
	 * @static
	 * @return POS_HOST_Product_Addons Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

return new POS_HOST_Product_Addons();
