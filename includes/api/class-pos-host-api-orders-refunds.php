<?php


class POS_HOST_REST_Orders_Refunds extends WC_REST_Order_Refunds_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'pos_host_orders/(?P<order_id>[\d]+)/refunds';


	/**
	 * Prepares one object for create or update operation.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @param bool            $creating If is creating a new object.
	 *
	 * @return WP_Error|WC_Data The prepared item, or WP_Error object on failure.
	 * @throws Exception
	 * @since  3.0.0
	 */
	protected function prepare_object_for_database( $request, $creating = false ) {
		$order = wc_get_order( (int) $request['order_id'] );

		if ( ! $order ) {
			return new WP_Error( 'woocommerce_rest_invalid_order_id', __( 'Invalid order ID.', 'woocommerce-pos-host' ), 404 );
		}

		if ( 0 > $request['amount'] ) {
			return new WP_Error( 'woocommerce_rest_invalid_order_refund', __( 'Refund amount must be greater than zero.', 'woocommerce-pos-host' ), 400 );
		}

		// Create the refund.
		$refund = wc_create_refund(
			array(
				'order_id'       => $order->get_id(),
				'amount'         => $request['amount'],
				'reason'         => empty( $request['reason'] ) ? null : $request['reason'],
				'line_items'     => empty( $request['line_items'] ) ? array() : $request['line_items'],
				'refund_payment' => is_bool( $request['api_refund'] ) ? $request['api_refund'] : true,
				'restock_items'  => $request['restock_items'],
			)
		);

		if ( is_wp_error( $refund ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_create_order_refund', $refund->get_error_message(), 500 );
		}

		if ( ! $refund ) {
			return new WP_Error( 'woocommerce_rest_cannot_create_order_refund', __( 'Cannot create order refund, please try again.', 'woocommerce-pos-host' ), 500 );
		}

		if ( ! empty( $request['meta_data'] ) && is_array( $request['meta_data'] ) ) {
			foreach ( $request['meta_data'] as $meta ) {
				$refund->update_meta_data( $meta['key'], $meta['value'], isset( $meta['id'] ) ? $meta['id'] : '' );
			}
			$refund->save_meta_data();
		}

		/**
		 * Filters an object before it is inserted via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`,
		 * refers to the object type slug.
		 *
		 * @param WC_Data         $coupon   Object object.
		 * @param WP_REST_Request $request  Request object.
		 * @param bool            $creating If is creating a new object.
		 */
		return apply_filters( "woocommerce_rest_pre_insert_{$this->post_type}_object", $refund, $request, $creating );
	}
}
