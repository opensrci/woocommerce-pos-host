<?php
/**
 * Responsible for the POS front-end
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Sell', false ) ) {
	return new POS_HOST_Sell();
}

/**
 * POS_HOST_Sell.
 */
class POS_HOST_Sell {

	/**
	 * The single instance of the class.
	 *
	 * @var POS_HOST_Sell
	 */
	protected static $_instance = null;

	/**
	 * Current register data.
	 *
	 * @var array
	 */
	public $data = null;

        /**
	 * Current loggedin register&outlet data.
	 *
	 * @var array
	 */
	public $loggedin = null;

	/**
	 * Current register ID.
	 *
	 * @var int
	 */
	public $id = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );      
		add_action( 'rest_api_init', array( $this, 'wc_api_init' ), 11 );
		add_action( 'rest_api_init', array( $this, 'wc_api_loaded' ), 12 );
		add_action( 'rest_api_init', array( $this, 'wc_api_classes' ), 15 );
		add_action( 'woocommerce_available_payment_gateways', array( $this, 'pos_host_available_payment_gateways' ), 100, 1 );
		add_action( 'option_woocommerce_stripe_settings', array( $this, 'woocommerce_stripe_settings' ), 100, 1 );
		add_action( 'init', array( $this, 'pos_host_checkout_gateways' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ), PHP_INT_MAX );
		add_action( 'wp_print_footer_scripts', array( $this, 'assets' ), PHP_INT_MAX );
		add_action( 'plugins_loaded', array( $this, 'init_addons_hooks' ), 99999 );
		add_filter( 'woocommerce_checkout_fields', array( $this, 'custom_order_fields' ) );
		add_filter( 'woocommerce_payment_complete_reduce_order_stock', array( $this, 'payment_complete_reduce_order_stock' ), 100, 2 );
	}

	/**
	 * Load POS assets and dequeue everything else.
	 */
	public function assets() {
		if ( is_pos() ) {
	
			wp_enqueue_style( 'pos-host-main', POS_HOST()->plugin_url() . '/assets/dist/css/register/pos_host_ui.css', array(), POS_HOST_VERSION );
                          //wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v3/', array(), POS_HOST_VERSION );
			//wp_enqueue_script( 'stripe-sdk', 'https://js.stripe.com/terminal/v1/', array(), POS_HOST_VERSION );
                          $main_js = POS_HOST()->plugin_url() . '/assets/dist/js/register/pos_host_ui.js';
			wp_enqueue_script( 'pos-host-main', $main_js , array() , '0.0.3' );
		}
	}


	public function init_addons_hooks() {
		if ( class_exists( 'WC_Subscriptions' ) ) {
			include_once 'class-pos-host-subscriptions.php';
		}
		if ( class_exists( 'WC_Product_Addons' ) && 'yes' === get_option( 'pos_host_force_enable_addons', 'pos_host_force_enable_addons' ) ) {
			include_once 'class-pos-host-product-addons.php';
		}

                include_once 'class-pos-host-payment-gateways.php';
                add_filter( 'bwp_minify_is_loadable', array( $this, 'bwp_minify' ) );
		
	}

	public function bwp_minify( $is_loadable ) {
		if ( is_pos() ) {
			$is_loadable = false;
		}
		return $is_loadable;
	}

	public function custom_order_fields( $checkout_fields ) {
		if ( is_pos() && is_plugin_active( 'woocommerce-admin-custom-order-fields/woocommerce-admin-custom-order-fields.php' ) ) {
			$custom_fields = array();
			foreach ( wc_admin_custom_order_fields()->get_order_fields() as $field_id => $field ) {
				$f = array(
					'type'        => $field->type,
					'label'       => $field->label,
					'description' => $field->description,
					'id'          => '_wc_acof_' . $field_id,
				);

				if ( 'select' === $field->type || 'checkbox' === $field->type || 'radio' === $field->type ) {
					$opt = array();

					foreach ( $field->get_options() as $val ) {
						$opt[ $val['value'] ] = $val['label'];
					}
					$f['options'] = $opt;
				} else {
					$f['default'] = $field->default;
				}
				$custom_fields[ $field->label ] = $f;
			}
			$checkout_fields['pos_custom_order'] = $custom_fields;
		}

		return $checkout_fields;
	}

	/**
	 * Display the POS front-end.
	 */
	public function template_redirect() {
                 global $wp;
		// Bail if not POS.
		if ( ! is_pos() ) {
			return;
		}

		// User not logged in? Redirect to the login page.
		if ( ! is_user_logged_in() ) {
			auth_redirect();
		}
                
		// Not authorized?
		if ( ! current_user_can( 'view_register' ) ) {
			wp_die( esc_html__( 'You are not allowed to view this page.', 'woocommerce-pos-host' ) );
		}
                $register_id = isset($wp->query_vars['register']) ? $wp->query_vars['register']:'';
                $outlet_id = isset($wp->query_vars['outlet']) ? $wp->query_vars['outlet']:'';
                
                $loggedin = isset($wp->query_vars['register']) && $wp->query_vars['register'] &&
                            isset($wp->query_vars['outlet']) && $wp->query_vars['outlet'];
                        
                // Update manifest.json.
                $home_url                     = home_url( $wp->request );
                $parsed                       = wp_parse_url($home_url);
                $home_host                    = $parsed['host'];
                $file                         = POS_HOST()->plugin_path() . '/assets/dist/images/manifest.json';
                $new_file                     = POS_HOST()->plugin_path() . '/assets/dist/images/manifest.'.$home_host.'.json';
                $contents                     = file_get_contents( $file );
                $contentsDecoded              = json_decode( $contents, true );
                $contentsDecoded['start_url'] = esc_url( $home_url );
                $json                         = json_encode( $contentsDecoded );

                //if ( is_writable( $new_file ) ) 
                {
                        file_put_contents( $new_file, $json );
                }
                
                include_once POS_HOST()->plugin_path() . '/includes/views/html-admin-pos.php';
                exit;
	}


	public function get_loggedin_user() {
		$loggedin_data['username'] = wp_get_current_user()->user_nicename;
		$loggedin_data['register_id'] = self::instance()->loggedin['register_id'];
		$loggedin_data['outlet_id'] = self::instance()->loggedin['outlet_id'];
                return $loggedin_data;
         }
        
	public function is_pos_referer() {
		$referer = wp_get_referer();
		$pos_url = get_home_url() . '/pos';

		if ( ! $referer ) {
			if ( isset( $_SERVER['HTTP_REFERER'] ) && strpos( wc_clean( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), 'pos' ) !== false ) {
				return true;
			};

			// Very rare case: could not get referer info for some reason such as it's being
			// stripped out by a proxy, firewall, etc.
			//
			// We mainly check referer when doing API requests, so we can check the endpoint
			// namespae. However, this might not always be the case so the TODO is to find a better
			// solution.
			if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( wc_clean( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'wp-json/pos' ) ) {
				return true;
			}
		}

		$parsed = wp_parse_url( $referer );
		if ( isset( $parsed['port'] ) ) {
			$pos_url = $parsed['scheme'] . '://' . $parsed['host'] . ':' . $parsed['port'] . $parsed['path'];
		}

		if ( strpos( $referer, $pos_url ) !== false ) {
			return true;
		}
		return false;
	}

	/**
	 * Instantiate the Product Class when making api requests
	 *
	 * @param  object $api_server WC_API_Server Object
	 */
	public function wc_api_init( $api_server ) {
		if ( true === $this->is_pos_referer() || is_pos() ) {
			include_once 'api/class-pos-host-api.php';
			$this->product = new POS_HOST_API();
		}
	}

	/**
	 * Include required files for REST API request
	 *
	 * @since 0.0.1
	 */
	public function wc_api_loaded() {
		include_once 'api/class-pos-host-api-orders.php';
		include_once 'api/class-pos-host-api-orders-refunds.php';
		include_once 'api/class-pos-host-rest-products-controller.php';
		include_once 'api/class-pos-host-rest-product-variations-controller.php';
		include_once 'api/class-pos-host-rest-product-categories-controller.php';
		include_once 'api/class-pos-host-rest-users-controller.php';
		include_once 'api/class-pos-host-rest-customers-controller.php';
		include_once 'api/class-pos-host-rest-options-controller.php';
                 /*@todo future 
                 include_once 'api/class-pos-host-rest-taxes-controller.php';
		include_once 'api/class-pos-host-rest-coupons-controller.php';
		include_once 'api/class-pos-host-rest-data-countries-controller.php';
		include_once 'api/class-pos-host-rest-shipping-zones-controller.php';
		include_once 'api/class-pos-host-rest-shipping-zone-locations-controller.php';
		include_once 'api/class-pos-host-rest-shipping-zone-methods-controller.php';
                  * 
                  */
	}

	/**
	 * Register available API resources
	 *
	 * @since 0.0.1
	 * @param WC_API_Server $server the REST server
	 */
	public function wc_api_classes() {
		$api_classes = array(
			'POS_HOST_REST_Orders',
			'POS_HOST_REST_Orders_Refunds',
			'POS_HOST_REST_Products_Controller',
			'POS_HOST_REST_Product_Variations_Controller',
			'POS_HOST_REST_Product_Categories_Controller',
			'POS_HOST_REST_Users_Controller',
			'POS_HOST_REST_Customers_Controller',
			'POS_HOST_REST_Options_Controller',
                           /*@todo future 
			'POS_HOST_REST_Coupons_Controller',
			'POS_HOST_REST_Taxes_Controller',
			'POS_HOST_REST_Data_Countries_Controller',
			'POS_HOST_REST_Shipping_Zones_Controller',
			'POS_HOST_REST_Shipping_Zone_Locations_Controller',
			'POS_HOST_REST_Shipping_Zone_Methods_Controller',
                            * 
                            */
		);

		foreach ( $api_classes as $api_class ) {
			$this->$api_class = new $api_class();
			$this->$api_class->register_routes();
		}

	}

	public function pos_host_available_payment_gateways( $_available_gateways ) {
		if ( is_pos() || $this->is_pos_api() ) {
			$_available_gateways = array();
			$payment_gateways    = WC()->payment_gateways->payment_gateways;
			$enabled_gateways    = pos_host_get_payment_gateways_ids( true );

			foreach ( $payment_gateways as $gateway ) {
				if ( in_array( $gateway->id, $enabled_gateways, true ) ) {
					$_available_gateways[ $gateway->id ] = $gateway;
				}
			}
		}

		return $_available_gateways;
	}

	private function is_pos_api() {
		 global $wp;
		$result = false;

		if ( isset( $wp->query_vars ) && isset( $wp->query_vars['wc-api-route'] ) && strpos( $wp->query_vars['wc-api-route'], 'pos_host_orders' ) !== false ) {
			$result = true;
		}

		return $result;
	}

	public function woocommerce_stripe_settings( $value ) {
		if ( is_pos() ) {
			$value['saved_cards']     = 'no';
			$value['stripe_checkout'] = 'no';
		}
		return $value;
	}

	public function pos_host_checkout_gateways() {
		if ( is_pos() ) {
			$enabled_gateways   = pos_host_get_payment_gateways_ids( true );
			$pos_exist_gateways = pos_host_get_payment_gateways_ids( false );

			foreach ( $pos_exist_gateways as $gateway_id ) {
				if ( ! in_array( $gateway_id, $enabled_gateways, true ) ) {
					add_filter( 'option_woocommerce_' . $gateway_id . '_settings', array( $this, 'disable_gateway' ) );
				} else {
					if ( 'pos_host_cash' === $gateway_id ) {
						add_filter( 'pre_option_woocommerce_' . $gateway_id . '_settings', array( $this, 'enable_gateway_cod' ) );
					} else {
						add_filter( 'option_woocommerce_' . $gateway_id . '_settings', array( $this, 'enable_gateway' ) );
					}
				}
			}
		}
	}

	public function disable_gateway( $val ) {
		$val['enabled'] = 'no';
		return $val;
	}

	public function enable_gateway( $val ) {

		$val['enabled'] = 'yes';
		if ( isset( $val['enable_for_virtual'] ) ) {
			$val['enable_for_virtual'] = 'yes';
		}

		if ( isset( $val['enable_for_methods'] ) ) {
			$val['enable_for_methods'] = array();
		}

		return $val;
	}

	public function enable_gateway_cod() {
		$val                       = array();
		$val['enabled']            = 'yes';
		$val['enable_for_virtual'] = 'yes';
		$val['enable_for_methods'] = array();

		return $val;
	}

	/**
	 * Returns register data.
	 *
	 * @param int|string $register Register ID or slug.
	 * @return array
	 */
	public function get_register( $register ) {

		// Get register data.
		$register_data = pos_host_get_register_data( $register );

		$this->data = $register_data;
		$this->id   = $register_data['id'];

		return apply_filters( 'pos_host_register_data', $register_data );
	}


        /*
         */
	public static function get_params() {
		$pos_icon = wp_get_attachment_image_src( get_option( 'pos_host_theme_logo' ), 0 );
		$pos_icon = $pos_icon ? $pos_icon[0] : POS_HOST()->plugin_url() . '/assets/dist/images/pos-host-logo-icon.png';

		$params = apply_filters(
			'pos_host_params',
			array(
				'version'                        => POS_HOST_VERSION."-".current_time("Ymdhis"),
				'site_url'                       => home_url(),
				'date_format'                    => get_option( 'date_format' ),
				'pos_icon'                       => $pos_icon,
				'avatar'                         => function_exists( 'get_avatar_url' ) ? get_avatar_url( 0, array( 'size' => 64 ) ) : '',
				'ajax_url'                       => admin_url( 'admin-ajax.php' ),
				'admin_url'                      => admin_url(),
				'ajax_loader_url'                => apply_filters( 'woocommerce_ajax_loader_url', WC()->plugin_url() . '/assets/images/ajax-loader@2x.gif' ),
				'offline_url'                    => POS_HOST()->plugin_url() . '/assets/vendor/offline/blank.png',
				'wc_api_url'                     => POS_HOST()->wc_api_url(),
				'pos_host_api_url'               => POS_HOST()->pos_host_api_url(),
				'rest_nonce'                     => wp_create_nonce( 'wp_rest' ),
				'logout_nonce'                   => wp_create_nonce( 'logout' ),
				'auth_user_nonce'                => wp_create_nonce( 'auth-user' ),
				'select_register_nonce'          => wp_create_nonce( 'select-register' ),
				'replace_grid_tile_nonce'        => wp_create_nonce( 'replace-grid-tile' ),
				'locale'                         => get_locale(),
				'auto_logout_session'            => (int) get_option( 'pos_host_auto_logout', 0 ),
				'gmt_offset'                     => get_option( 'gmt_offset' ),
				'theme_primary_color'            => empty( get_option( 'pos_host_theme_primary_color' ) ) ? '#7f54b3' : get_option( 'pos_host_theme_primary_color', '#7f54b3' ),
				'default_country'                => get_option( 'pos_host_default_country' ),
				'show_out_of_stock'              => 'yes' === get_option( 'pos_host_show_out_of_stock_products', 'no' ),
				'enable_pos_visibility'          => 'yes' === get_option( 'pos_host_visibility', 'no' ),
				'hide_optional_fields'           => get_option( 'pos_host_hide_not_required_fields', 'no' ),
				'payment_gateways'               => pos_host_get_available_payment_gateways(),
				'cash_management_nonce'          => wp_create_nonce( 'cash-management' ),
				'tax_number'                     => get_option( 'pos_host_tax_number' ),
				'hide_tender_suggestions'        => 'yes' === get_option( 'pos_host_hide_tender_suggestions', 'no' ),
                                   'pos_calc_taxes'                 => pos_host_tax_enabled(),
                          ));
                return $params;

	}

	public static function get_wc_params($outlet_data) {
		$pos_tax_based_on = get_option( 'pos_host_calculate_tax_based_on', 'outlet' );
		if ( 'default' === $pos_tax_based_on ) {
			$pos_tax_based_on = get_option( 'woocommerce_tax_based_on' );
		}
		$precision = function_exists( 'wc_get_rounding_precision' ) ? wc_get_rounding_precision() : ( defined( 'WC_ROUNDING_PRECISION' ) ? WC_ROUNDING_PRECISION : 4 );

		$params = apply_filters(
			'pos_host_wc_params',
			array(
				'tax_display_shop'               => get_option( 'woocommerce_tax_display_shop' ),
				'calc_taxes'                     => get_option( 'woocommerce_calc_taxes' ),
				'prices_include_tax'             => wc_prices_include_tax(),
				'tax_round_at_subtotal'          => get_option( 'woocommerce_tax_round_at_subtotal' ),
				'tax_display_cart'               => get_option( 'woocommerce_tax_display_cart' ),
				'calc_discounts_seq'             => get_option( 'woocommerce_calc_discounts_sequentially', 'no' ),
				'pos_tax_based_on'               => $pos_tax_based_on,
				'precision'                      => $precision,
				'all_rates'                      => pos_host_get_all_tax_rates(),
				'outlet_rates'                   => pos_host_get_outlet_tax_rates($outlet_data),
				'shop_location'                  => pos_host_get_shop_location(),
				'tax_enabled'                    => wc_tax_enabled(),
				'european_union_countries'       => WC()->countries->get_european_union_countries(),
				'base_country'                   => WC()->countries->get_base_country(),
				'base_state'                     => WC()->countries->get_base_state(),
				'base_postcode'                  => WC()->countries->get_base_postcode(),
				'base_city'                      => WC()->countries->get_base_city(),
				'registration_generate_username' => get_option( 'woocommerce_registration_generate_username', 'yes' ),
				'registration_generate_password' => get_option( 'woocommerce_registration_generate_password', 'yes' ),
				'shipping_enabled'               => get_option( 'woocommerce_ship_to_countries', '' ),
				'shipping_rates'                 => array(),
				'shop_name'                      => get_bloginfo( 'name' ),
			) 
		);

		return $params;
	}

	public static function get_cart_params() {
		$tax_classes = array();
		foreach ( WC_Tax::get_tax_classes() as $class ) {
			$tax_classes[ sanitize_title( $class ) ] = $class;
		}

		$tax_display_cart = get_option( 'woocommerce_tax_display_cart' );
		$params           = apply_filters(
			'pos_host_cart_params',
			array(
				'prices_include_tax'    => wc_prices_include_tax(),
				'calc_shipping'         => 'yes' === get_option( 'woocommerce_calc_shipping' ),
				'tax_round_at_subtotal' => 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ),
				'tax_total_display'     => get_option( 'woocommerce_tax_total_display' ),
				'tax_display_cart'      => $tax_display_cart,
				'dp'                    => wc_get_price_decimals(),
				'display_totals_ex_tax' => 'excl' === $tax_display_cart,
				'display_cart_ex_tax'   => 'excl' === $tax_display_cart,
				'enable_coupons'        => apply_filters( 'woocommerce_coupons_enabled', 'yes' === get_option( 'woocommerce_enable_coupons' ) ),
				'tax_or_vat'            => WC()->countries->tax_or_vat(),
				'ex_tax_or_vat'         => WC()->countries->ex_tax_or_vat(),
				'inc_tax_or_vat'        => WC()->countries->inc_tax_or_vat(),
				'shipping_tax_class'    => get_option( 'woocommerce_shipping_tax_class' ),
				'tax_classes'           => $tax_classes,
				'coupons_labels'        => self::get_coupons_labels(),
				'tax_round_half_up'     => self::tax_round_half_up(),
			)
		);
		return $params;
	}

	/**
	 * Check if WC will round the tax total half up/down.
	 *
	 * @todo This is replicated in POS_HOST_REST_Options_Controller. Make sure it's no longer used and
	 *       then remove it.
	 *
	 * @return bool
	 */
	public static function tax_round_half_up() {
		return 1.15 === wc_round_tax_total( 1.145, 2 ) ? true : false;
	}

	public function get_custom_product_params() {
		$product_id = (int) get_option( 'pos_host_custom_product_id', 0 );
		$product    = wc_get_product( $product_id );

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return json_encode( (object) array() );
		}

		$prices_precision = wc_get_price_decimals();
		$product_data     = array(
			'name'               => $product->get_title(),
			'id'                 => (int) $product->is_type( 'variation' ) ? $product->get_id() : $product->get_id(),
			'created_at'         => '',
			'updated_at'         => '',
			'type'               => $product->get_type(),
			'status'             => $product->get_status(),
			'downloadable'       => $product->is_downloadable(),
			'virtual'            => $product->is_virtual(),
			'permalink'          => $product->get_permalink(),
			'sku'                => $product->get_sku(),
			'price'              => wc_format_decimal( $product->get_price(), $prices_precision ),
			'regular_price'      => wc_format_decimal( $product->get_regular_price(), $prices_precision ),
			'sale_price'         => $product->get_sale_price() ? wc_format_decimal( $product->get_sale_price(), $prices_precision ) : null,
			'price_html'         => str_replace( '"', "'", $product->get_price_html() ), // Temp fix.
			'taxable'            => $product->is_taxable(),
			'tax_status'         => $product->get_tax_status(),
			'tax_class'          => $product->get_tax_class(),
			'manage_stock'       => $product->managing_stock(),
			'stock_quantity'     => $product->get_stock_quantity(),
			'in_stock'           => $product->is_in_stock(),
			'backorders_allowed' => $product->backorders_allowed(),
			'backordered'        => $product->is_on_backorder(),
			'sold_individually'  => $product->is_sold_individually(),
			'purchaseable'       => $product->is_purchasable(),
			'featured'           => $product->is_featured(),
			'visible'            => $product->is_visible(),
			'catalog_visibility' => $product->get_catalog_visibility(),
			'on_sale'            => $product->is_on_sale(),
			'product_url'        => $product->is_type( 'external' ) ? $product->get_product_url() : '',
			'button_text'        => $product->is_type( 'external' ) ? $product->get_button_text() : '',
			'weight'             => $product->get_weight() ? wc_format_decimal( $product->get_weight(), 2 ) : null,
			'dimensions'         => array(
				'length' => $product->get_length(),
				'width'  => $product->get_width(),
				'height' => $product->get_height(),
				'unit'   => get_option( 'woocommerce_dimension_unit' ),
			),
			'shipping_required'  => $product->needs_shipping(),
			'shipping_taxable'   => $product->is_shipping_taxable(),
			'shipping_class'     => $product->get_shipping_class(),
			'shipping_class_id'  => ( 0 !== $product->get_shipping_class_id() ) ? $product->get_shipping_class_id() : null,
			'description'        => wpautop( do_shortcode( get_post( $product->get_id() )->post_content ) ),
			'short_description'  => apply_filters( 'woocommerce_short_description', get_post( $product->get_id() )->post_excerpt ),
			'reviews_allowed'    => ( 'open' === get_post( $product->get_id() )->comment_status ),
			'average_rating'     => wc_format_decimal( $product->get_average_rating(), 2 ),
			'rating_count'       => (int) $product->get_rating_count(),
			'related_ids'        => array_map( 'absint', array_values( wc_get_related_products( $product->get_id() ) ) ),
			'upsell_ids'         => array_map( 'absint', $product->get_upsell_ids() ),
			'cross_sell_ids'     => array_map( 'absint', $product->get_cross_sell_ids() ),
			'parent_id'          => get_post( $product->get_id() )->post_parent,
			'categories'         => wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) ),
			'tags'               => wp_get_post_terms( $product->get_id(), 'product_tag', array( 'fields' => 'names' ) ),
			'featured_src'       => wp_get_attachment_url( get_post_thumbnail_id( $product->is_type( 'variation' ) ? $product->variation_id : $product->get_id() ) ),
			'attributes'         => array(),
			'downloads'          => array(),
			'download_limit'     => (int) $product->get_download_limit(),
			'download_expiry'    => (int) $product->get_download_expiry(),
			'download_type'      => $product->is_downloadable(),
			'purchase_note'      => wpautop( do_shortcode( wp_kses_post( $product->get_purchase_note() ) ) ),
			'total_sales'        => metadata_exists( 'post', $product->get_id(), 'total_sales' ) ? (int) get_post_meta( $product->get_id(), 'total_sales', true ) : 0,
			'variations'         => array(),
			'parent'             => array(),
			'images'             => array(),
		);

		return $product_data;
	}

	public static function get_coupons_labels() {
		$c = array( 'WC_POINTS_REDEMPTION' );
		$l = array();
		foreach ( $c as $code ) {
			$l[ $code ] = $code;
		}

		return $l;
	}

	/**
	 * Main POS_HOST_Sell Instance.
	 *
	 * Ensures only one instance of POS_HOST_Sell is loaded or can be loaded.
	 *
	 * @return POS_HOST_Sell Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function payment_complete_reduce_order_stock( $trigger_reduce, $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order || 'POS' !== $order->get_created_via() ) {
			return $trigger_reduce;
		}

		if ( $order->is_paid() ) {
			return true;
		}

		return false;
	}
        /*
         *  return all the post-login data for a register
         *  @return array
         */
        public static function get_post_login_data( $user_id, $outlet_id, $register_id ) {

                 /* get params */
		//$params    = self::get_params();
                 //$login_data['params'] = $params;

                 /* get cart params */
		$cart_params    = self::get_cart_params();
                 $login_data['cart_params'] = $cart_params;
                 
                 /* get categories */
		$categories    = pos_host_get_categories();
                 $login_data['categories'] = $categories;
                 
                 /* get custom_product */
		$custom_product    = self::get_custom_product_params();
                 $login_data['custom_product'] = $custom_product;
                 
                 /* return if no register or outlet */
                 if( !$outlet_id ||!$register_id ){
                    return $login_data;
                 }

                 /* get regiser */
		//$register_data   = pos_host_get_register_data( $register_id );
		$register_data   = pos_host_start_session ($user_id, $register_id );
		if ( ! $register_data ) {
                    return false;
		}
                
                 $login_data['register_data'] = $register_data;
                 self::instance()->loggedin['register_id'] = $register_data['id'];
                 
                 /* get outlet */
		$outlet_data    = pos_host_get_outlet_data( $outlet_id );
		if ( ! $outlet_data ) {
                    return false;
		}
                 $login_data['outlet_data'] = $outlet_data;
                 self::instance()->loggedin['outlet_id'] = $outlet_data['id'];
                 
                 /* get grid */
		$grid_data    = pos_host_get_grid_data( $register_data['grid']);
                 $login_data['grid_data'] = $grid_data;
                 
                 /* get receipt */
		$receipt_data    = pos_host_get_receipt_data( $register_data['receipt']);
                 $login_data['receipt_data'] = $receipt_data;
                 
                 /* get wc params */
		$wc_data    = self::get_wc_params($outlet_data);
                 $login_data['wc_data'] = $wc_data;
                 
                 return $login_data;
        }
        
}


return new POS_HOST_Sell();
