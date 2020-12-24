<?php
/**
 * Session Functions
 *
 * @since 0.0.1
 *
 * @package WooCommerce_pos_host/Functions
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get session.
 *
 * @since 0.0.1
 *
 * @param int|string|POS_HOST_Session $session Session ID, slug or object.
 *
 * @throws Exception If session cannot be read/found and $data parameter of POS_HOST_Session class constructor is set.
 * @return POS_HOST_Session|null
 */
function pos_host_get_session( $session ) {
	$session_object = new POS_HOST_Session( $session );
	return 0 !== $session_object->get_id() ? $session_object : null;
}

/**
 * Returns the session details.
 *
 * @since 0.0.1
 *
 * @param $session_id Session ID.
 * @return array Session details.
 */
function pos_host_get_session_details( $session_id ) {
	global $wpdb;

	$details = array();
	$session = pos_host_get_session( $session_id );

	if ( ! $session || ! is_a( $session, 'POS_HOST_Session' ) ) {
		return $details;
	}

	// Session data holds the meta data that can be lost if a register, an outlet or a user is
	// deleted.
	$session_data               = $session->get_session_data();
	$session_data['register']   = empty( $session_data['register'] ) ? __( 'Deleted Register', 'woocommerce-pos-host' ) : $session_data['register'];
	$session_data['outlet']     = empty( $session_data['outlet'] ) ? __( 'Deleted Outlet', 'woocommerce-pos-host' ) : $session_data['outlet'];
	$session_data['open_first'] = empty( $session_data['open_first'] ) ? __( 'Deleted User', 'woocommerce-pos-host' ) : $session_data['open_first'];
	$session_data['open_last']  = empty( $session_data['open_last'] ) ? __( 'Deleted User', 'woocommerce-pos-host' ) : $session_data['open_last'];

	$register        = pos_host_get_register( $session->get_register_id() );
	$outlet          = pos_host_get_outlet( $session->get_outlet_id() );
	$open_first_user = get_user_by( 'id', $session->get_open_first() );
	$open_last_user  = get_user_by( 'id', $session->get_open_last() );

	$details['register']  = $register ? $register->get_name() : $session_data['register'];
	$details['outlet']    = $outlet ? $outlet->get_name() : $session['outlet'];
	$details['opened_by'] = $open_first_user ? $open_first_user->display_name : $session_data['open_first'];
	$details['closed_by'] = $open_last_user ? $open_last_user->display_name : $session_data['open_last'];

	// Find session orders.
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} p
			 INNER JOIN {$wpdb->postmeta} pm
			 ON ( pm.post_id = p.ID AND pm.meta_key = 'pos_host_register_id' AND pm.meta_value = %d )
			 WHERE ( p.post_type = 'shop_order' OR p.post_type = 'shop_order_refund' )
			 AND p.post_date_gmt >= %s
			 AND p.post_date_gmt < %s
			",
			$session->get_register_id(),
			gmdate( 'Y-m-d H:i:s', $session->get_date_opened()->getTimestamp() ),
			gmdate( 'Y-m-d H:i:s', $session->get_date_closed()->getTimestamp() )
		)
	);

	$details['orders_count'] = 0;
	$details['total']        = 0;
	$details['tax_total']    = 0;
	$details['taxes']        = array();

	if ( $results ) {
		$payment_gateways = array();
		foreach ( WC()->payment_gateways()->payment_gateways() as $id => $gateway ) {
			$payment_gateways[ $id ] = $gateway->title;
		}

		foreach ( $results as $result ) {
			$order                = wc_get_order( $result->ID );
			$total                = $order->get_total();
			$taxes                = $order->get_tax_totals();
			$payment_method       = $order->get_payment_method();
			$payment_method_title = isset( $payment_gateways[ $payment_method ] ) ? $payment_gateways[ $payment_method ] : $payment_method;

			if ( ! isset( $details['payments'][ $payment_method ] ) ) {
				$details['payments'][ $payment_method ] = array(
					'title'        => $payment_method_title,
					'total'        => 0,
					'tax_total'    => 0,
					'orders_count' => 0,
					'orders'       => array(),
				);
			}

			array_push( $details['payments'][ $payment_method ]['orders'], $order );

			$details['payments'][ $payment_method ]['total']        += $total;
			$details['payments'][ $payment_method ]['orders_count'] += 1;

			$details['total']        += $total;
			$details['orders_count'] += 1;

			// Add taxes.
			foreach ( $taxes as $tax ) {
				$details['taxes'][ $tax->label ]  = isset( $details['taxes'][ $tax->label ] ) ? $details['taxes'][ $tax->label ] : 0;
				$details['taxes'][ $tax->label ] += $tax->amount;

				$details['tax_total']                    += $tax->amount;
				$details[ $payment_method ]['tax_total'] += $tax->amount;
			}
		}
	}

	return $details;
}
