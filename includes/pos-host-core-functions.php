<?php
/**
 * Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @package WooCommerce_pos_host/Functions
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the order statuses with the wc- prefix stripped off.
 *
 * @since 0.0.1
 * @return array
 */
function pos_host_get_order_statuses_no_prefix() {
	foreach ( wc_get_order_statuses() as $key => $value ) {
		$statuses[ substr( $key, 3 ) ] = $value;
	}
	return $statuses;
}

/**
 * Returns the payment gateway IDs.
 *
 * @since 0.0.1
 *
 * @param boolean $available Only return the available (enabled) gateways.
 * @return array List of payment gateways IDs.
 */
function pos_host_get_payment_gateways_ids( $available = false ) {
	$gateways = WC()->payment_gateways()->payment_gateways();
	$results  = array();

	foreach ( $gateways as $id => $gateway ) {
		if ( $available && 'yes' !== $gateway->enabled ) {
			continue;
		}

		array_push( $results, $id );
	}

	return $results;
}

/**
 * Get all the screen ids that are created/modified by the plugin.
 *
 * @since 0.0.1
 * @return array
 */
function pos_host_get_screen_ids() {

    $pos_host_screen_id = POS_HOST()->plugin_screen_id();

	$screen_ids = array(
		'toplevel_page_' . $pos_host_screen_id,
		$pos_host_screen_id . '_page_pos-host-barcodes',
		$pos_host_screen_id . '_page_pos-host-stock-controller',
		$pos_host_screen_id . '_page_pos-host-settings',
		'edit-shop_order',
		'edit-product',
		'edit-pos_host_register',
		'edit-pos_host_outlet',
		'edit-pos_host_grid',
		'edit-pos_host_receipt',
		'edit-pos_host_report',
		'shop_order',
		'product',
		'pos_host_register',
		'pos_host_outlet',
		'pos_host_grid',
		'pos_host_receipt',
		'pos_host_report',
		'profile',
		'user-edit',
	);

	return apply_filters( 'pos_host_screen_ids', $screen_ids );
}

/**
 * Check if a specific post is the default one.
 *
 * @param int    $post_id   Post ID.
 * @param string $post_type Post type.
 *
 * @return bool
 */
function pos_host_is_default_post( $post_id, $post_type ) {
	if ( ! in_array( $post_type, array( 'pos_host_register', 'pos_host_outlet', 'pos_host_receipt' ) ) ) {
		return false;
	}

	return (int) get_option( 'pos_host_default_' . str_replace( 'pos_', '', $post_type ), 0 ) === (int) $post_id ? true : false;
}

function pos_host_sent_email_receipt( $order_id ) {
	$order_email_receipt = get_post_meta( $order_id, 'pos_payment_email_receipt', true );
	$order               = wc_get_order( $order_id );

	$mail = WC()->mailer();
	$mail->emails['POS_HOST_Email_New_Order']->trigger( $order_id );

	if ( ! empty( $order_email_receipt ) ) {

		switch ( $order->get_status() ) {
			case 'processing':
				$customer_email = $mail->emails['WC_Email_Customer_Processing_Order'];
				break;
			case 'on-hold':
				$customer_email = $mail->emails['WC_Email_Customer_On_Hold_Order'];
				break;
			case 'completed':
				$customer_email = $mail->emails['WC_Email_Customer_Completed_Order'];
				break;
			case 'cancelled':
				$customer_email = $mail->emails['WC_Email_Cancelled_Order'];
				break;
			case 'refunded':
				$customer_email = $mail->emails['WC_Email_Customer_Refunded_Order'];
				break;
			case 'failed':
				$customer_email = $mail->emails['WC_Email_Failed_Order'];
				break;
			default:
				break;
		}

		if ( isset( $customer_email ) ) {
			/**
			 * Override filters to enable email and sends only too the customer.
			 */
			add_filter( 'woocommerce_email_enabled_' . $customer_email->id, '__return_true' );
			remove_all_filters( 'woocommerce_email_recipient_' . $customer_email->id );

			$order->set_billing_email( $order_email_receipt );
			$customer_email->trigger( $order_id, $order );

			remove_filter( 'woocommerce_email_enabled_' . $customer_email->id, '__return_true' );
		}
	}

}

function pos_host_get_outlet_location( $id_register = 0 ) {
	$location = array();
	if ( ! $id_register && ! isset( $_GET['register'] ) ) {
		return $location;
	}

	$register_id = $id_register > 0 ? $id_register : wc_clean( $_GET['register'] );
	$register    = pos_host_get_register( $register_id );

	if ( $register ) {
		$location = POS_HOST_Sell::instance()->get_outlet( $register->get_outlet() );
	}

	return $location;
}

function pos_host_get_shop_location() {
	return array(
		'country'  => WC()->countries->get_base_country(),
		'state'    => WC()->countries->get_base_state(),
		'postcode' => WC()->countries->get_base_postcode(),
		'city'     => WC()->countries->get_base_city(),
	);
}

/**
 * Get all tax rates.
 *
 * @todo Refactor this to perform less number of database queries.
 * @return Array of rates.
 */
function pos_host_get_all_tax_rates() {
	global $wpdb;

	$tax_class   = '';
	$rates       = array();
	$found_rates = $wpdb->get_results(
		"SELECT tax_rates.*
		FROM {$wpdb->prefix}woocommerce_tax_rates as tax_rates
		LEFT OUTER JOIN {$wpdb->prefix}woocommerce_tax_rate_locations as locations ON tax_rates.tax_rate_id = locations.tax_rate_id
		LEFT OUTER JOIN {$wpdb->prefix}woocommerce_tax_rate_locations as locations2 ON tax_rates.tax_rate_id = locations2.tax_rate_id
		GROUP BY tax_rate_id
		ORDER BY tax_rate_priority, tax_rate_order"
	);

	foreach ( $found_rates as $key_rate => $found_rate ) {
		$found_postcodes = $wpdb->get_results( $wpdb->prepare( "SELECT location_code FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = %s AND location_type = 'postcode'", $found_rate->tax_rate_id ) );
		$postcode        = array();
		if ( $found_postcodes ) {
			foreach ( $found_postcodes as $code ) {
				$postcode[] = $code->location_code;
			}
		}

		$found_postcodes = $wpdb->get_results( $wpdb->prepare( "SELECT location_code FROM {$wpdb->prefix}woocommerce_tax_rate_locations WHERE tax_rate_id = %s AND location_type = 'city'", $found_rate->tax_rate_id ) );
		$city            = array();
		if ( $found_postcodes ) {
			foreach ( $found_postcodes as $code ) {
				$city[] = $code->location_code;
			}
		}

		$rates[ $found_rate->tax_rate_id ] = array(
			'rate'     => (float) $found_rate->tax_rate,
			'label'    => $found_rate->tax_rate_name,
			'shipping' => $found_rate->tax_rate_shipping ? 'yes' : 'no',
			'compound' => $found_rate->tax_rate_compound ? 'yes' : 'no',
			'country'  => $found_rate->tax_rate_country,
			'state'    => $found_rate->tax_rate_state,
			'city'     => implode( ';', $city ),
			'postcode' => implode( ';', $postcode ),
			'taxclass' => $found_rate->tax_rate_class,
			'priority' => $found_rate->tax_rate_priority,
		);
	}

	return $rates;
}

function pos_host_get_non_cat_products() {
	global $wpdb;
	$products = array();

	$taxonomy = $wpdb->get_results( "SELECT tax.term_taxonomy_id tax_id FROM {$wpdb->term_taxonomy} tax WHERE tax.taxonomy = 'product_cat'" );
	$t        = array();
	if ( $taxonomy ) {
		foreach ( $taxonomy as $tx ) {
			$t[] = $tx->tax_id;
		}
	}
	if ( ! empty( $t ) ) {
		$t = implode( ',', $t );
	} else {
		$t = 0;
	}

	$result = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT post.ID FROM {$wpdb->posts} post
			LEFT JOIN {$wpdb->term_relationships} rel ON(rel.object_id = post.ID AND rel.term_taxonomy_id IN( %d ) )
			WHERE post.post_type = 'product' AND post.post_status = 'publish' AND rel.object_id IS NULL
			",
			$t
		)
	);

	if ( $result ) {
		foreach ( $result as $value ) {
			$products[] = (int) $value->ID;
		}
	}

	return $products;
}

/**
 * Returns a list of registers that are assigned to a specific outlet.
 *
 * @param $outlet_id Outlet ID.
 * @return array List of register IDs.
 */
function pos_host_get_registers_by_outlet( $outlet_id = 0 ) {
	$registers = array();

	$get_posts = get_posts(
		array(
			'numberposts' => -1,
			'post_type'   => 'pos_host_register',
			'meta_key'    => 'outlet',
			'meta_value'  => $outlet_id,
		)
	);

	foreach ( $get_posts as $post ) {
		$registers[] = $post->ID;
	}

	return $registers;
}

function pos_host_enable_generate_password( $value ) {
	return 'yes';
}

function is_pos() {
	if ( is_callable( array( 'WP_Query', 'get_posts' ) ) ) {
		global $wp;

		if ( isset( $wp->query_vars ) ) {
			$q = $wp->query_vars;

			if ( isset( $q['page'] ) && 'pos-host-registers' === $q['page'] && isset( $q['action'] ) && 'view' === $q['action'] ) {
				return true;
			}
		}

		return false;
	}

}

function pos_host_get_available_payment_gateways() {
	$available_gateways = array();
	foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $gateway ) {
		array_push(
			$available_gateways,
			(object) array(
				'id'    => $gateway->id,
				'title' => $gateway->get_title(),
			)
		);
	}

	return $available_gateways;
}

/**
 * Is tax calculation enabled?
 *
 * @return bool
 */
function pos_host_tax_enabled() {
	if ( 'enabled' === get_option( 'pos_host_tax_calculation', 'enabled' ) && wc_tax_enabled() ) {
		return true;
	}

	return false;
}

function is_pos_referer() {
	$referer = wp_get_referer();
	$pos_url = get_home_url() . '/pos-host/';

	if ( strpos( $referer, $pos_url ) !== false ) {
		return true;
	}
	return false;
}

function pos_host_get_custom_order_fields() {
	$custom_fields = array();
	if ( function_exists( 'wc_admin_custom_order_fields' ) ) {
		foreach ( wc_admin_custom_order_fields()->get_order_fields() as $field_id => $field ) {

			$custom_fields[] = '_wc_acof_' . $field_id;
		}
	}
	return $custom_fields;
}

function pos_host_close_register( $register_id = 0, $data = array(), $force = false ) {
	$register = pos_host_get_register( $register_id );

	if ( ! $register ) {
		return false;
	}

	// Prepare session data before closing the register.
	$date_closed    = time(); // GMT.
	$open_last      = $data['open_last'];
	$closing_note   = $data['closing_note'];
	$counted_totals = $data['counted_totals'];

	$register->set_props(
		array(
			'date_closed' => $date_closed,
			'open_last'   => 0,
		)
	);

	// Save closing register data.
	$closed = $register->save();

	if ( $closed ) {
		$session = pos_host_get_session( $register->get_current_session() );

		if ( ! $session ) {
			return $closed;
		}

		$session->set_props(
			array(
				'date_closed'    => $date_closed,
				'open_last'      => $open_last,
				'closing_note'   => $closing_note,
				'counted_totals' => $counted_totals,
			)
		);

		$session_id = $session->save();

		/**
		 * The pos_host_end_of_day_report action.
		 *
		 * Triggers the end of day email notification.
		 *
		 * @param int            $session_id Session ID.
		 * @param POS_HOST_Session $session    Session object.
		 */
		do_action( 'pos_host_end_of_day_report', $session_id, $session );
	}

	return $closed;
}

function pos_host_switch_user( $register_id, $user_id = 0 ) {
	$register = pos_host_get_register( $register_id );

	if ( ! $register ) {
		return false;
	}

	$register->set_open_last( $user_id );

	if ( $register->save() ) {
		return true;
	}

	return false;
}

function pos_host_is_dev() {
	$headers = pos_host_getallheaders();

	if ( isset( $headers['Env'] ) && 'dev' === $headers['Env'] ) {
		return true;
	}

	return false;
}

/**
 * Is gateway supported?
 *
 * @param WC_Payment_Gateway $gateway
 * @return bool
 */
function pos_host_is_pos_supported_gateway( $gateway ) {
	return $gateway->supports( 'woocommerce-pos-host' );
}

/**
 * Returns all the sent HTTP hearders.
 *
 * @since 0.0.1
 * @return array Array of headers.
 */
function pos_host_getallheaders() {

/* do no support */
    return false;
    
        $headers = array();

	foreach ( $_SERVER as $name => $value ) {
		if ( substr( $name, 0, 5 ) == 'HTTP_' ) {
			$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
		}
	}

	return $headers;
}
