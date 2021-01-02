<?php
/**
 * Orders API
 *
 * Handles requests to the /pos_host_orders endpoint.
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_API_POS_HOST_Orders.
 */
class POS_HOST_REST_Orders extends WC_REST_Orders_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'pos_host_orders';

	/**
	 * Prepare a single order for create or update.
	 *
	 * @throws WC_REST_Exception When fails to set any item.
	 * @param  WP_REST_Request $request Request object.
	 * @param  bool            $creating If is creating a new object.
	 * @return WP_Error|WC_Data
	 */
	protected function prepare_object_for_database( $request, $creating = false ) {
		$id        = isset( $request['id'] ) ? absint( $request['id'] ) : 0;
		$order     = new WC_Order( $id );
		$schema    = $this->get_item_schema();
		$data_keys = array_keys( array_filter( $schema['properties'], array( $this, 'filter_writable_props' ) ) );

		// POS data modifications.
		if ( isset( $request['create_post'] ) && is_array( $request['create_post'] ) ) {
			foreach ( $request['create_post'] as $post ) {
				if ( is_array( $post ) ) {
					foreach ( $post as $key => $value ) {
						$_POST[ $key ] = $value;
					}
				}
			}
		}

		$new_meta_data = $request->get_param( 'meta_data' );

		$server         = current(
			array_filter(
				$new_meta_data,
				function ( $meta_data ) {
					return 'pos_host_served_by' === $meta_data['key'];
				}
			)
		);
		$served_by      = get_userdata( $server['value'] );
		$served_by_name = '';

		if ( $served_by ) {
			$served_by_name = $served_by->display_name;
		}

		$new_meta_data[] = array(
			'key'   => 'pos_host_served_by_name',
			'value' => $served_by_name,
		);

		$new_meta_data[] = array(
			'key'   => '_order_number',
			'value' => isset( $request['order_number'] ) ? $request['order_number'] : $order->get_order_number(),
		);

		$request->set_param( 'customer_id', (int) $this->get_customer_id( $request ) );
		$request->set_param( 'meta_data', $new_meta_data );

		// Handle all writable props.
		foreach ( $data_keys as $key ) {
			$value = $request[ $key ];

			if ( ! is_null( $value ) ) {
				switch ( $key ) {
					case 'coupon_lines':
					case 'status':
						// Change should be done later so transitions have new data.
						break;
					case 'billing':
					case 'shipping':
						$this->update_address( $order, $value, $key );
						break;
					case 'line_items':
					case 'shipping_lines':
					case 'fee_lines':
						if ( is_array( $value ) ) {
							foreach ( $value as $item ) {
								if ( is_array( $item ) ) {
									if ( $this->item_is_null( $item ) || ( isset( $item['quantity'] ) && 0 === $item['quantity'] ) ) {
										$order->remove_item( $item['id'] );
									} else {
										$this->set_item( $order, $key, $item );
									}
								}
							}
						}
						break;
					case 'meta_data':
						if ( is_array( $value ) ) {
							foreach ( $value as $meta ) {
								$order->update_meta_data( $meta['key'], $meta['value'], isset( $meta['id'] ) ? $meta['id'] : '' );
							}
						}
						break;
					default:
						if ( is_callable( array( $order, "set_{$key}" ) ) ) {
							$order->{"set_{$key}"}( $value );
						}
						break;
				}
			}
		}

		if ( $creating ) {
			$order->set_date_created( new WC_DateTime() );
		}

		/**
		 * Filters an object before it is inserted via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`,
		 * refers to the object type slug.
		 *
		 * @param WC_Data         $order    Object object.
		 * @param WP_REST_Request $request  Request object.
		 * @param bool            $creating If is creating a new object.
		 */
		return apply_filters( "woocommerce_rest_pre_insert_{$this->post_type}_object", $order, $request, $creating );
	}

	/**
	 * Save an object data.
	 *
	 * @throws WC_REST_Exception But all errors are validated before returning any data.
	 * @param  WP_REST_Request $request  Full details about the request.
	 * @param  bool            $creating If is creating a new object.
	 * @return WC_Data|WP_Error
	 */
	protected function save_object( $request, $creating = false ) {
		try {
			if ( ! current_user_can( 'view_register' ) ) {
				return new WP_Error( 'woocommerce_api_user_cannot_create_order', __( 'You do not have permission to create orders.', 'woocommerce-pos-host' ), 401 );
			}

			$creating = 'create' === $request['action'];

			$object = $this->prepare_object_for_database( $request, $creating );

			if ( is_wp_error( $object ) ) {
				return $object;
			}

			// Make sure gateways are loaded so hooks from gateways fire on save/create.
			WC()->payment_gateways();

			$enable_guest_checkout = get_option( 'pos_host_guest_checkout', 'no' );
			if ( 0 === intval( $request['customer_id'] ) && 'no' === $enable_guest_checkout ) {
				throw new WC_REST_Exception( 'woocommerce_rest_invalid_customer', __( 'Guest checkout is not allowed.', 'woocommerce-pos-host' ), 403 );
			}

			if ( ! is_null( $request['customer_id'] ) && 0 !== $request['customer_id'] ) {
				$user = get_user_by( 'id', $request['customer_id'] );

				// Make sure customer exists.
				if ( false === $user ) {
					throw new WC_REST_Exception( 'woocommerce_rest_invalid_customer_id', __( 'Customer ID is invalid.', 'woocommerce-pos-host' ), 400 );
				}

				// Make sure customer is part of blog.
				if ( is_multisite() && ! is_user_member_of_blog( $request['customer_id'] ) ) {
					throw new WC_REST_Exception( 'woocommerce_rest_invalid_customer_id_network', __( 'Customer ID does not belong to this site.', 'woocommerce-pos-host' ), 400 );
				}

				$customer = new WC_Customer( $user->ID );
				if ( $customer ) {
					$billing_args = wp_parse_args( $request['billing'], $customer->get_billing() );
					foreach ( $billing_args as $key => $value ) {
						$action = "set_billing_$key";
						if ( method_exists( $customer, $action ) ) {
							$customer->$action( $value );
						}
					}

					$shipping_args = wp_parse_args( $request['shipping'], $customer->get_shipping() );
					foreach ( $shipping_args as $key => $value ) {
						$action = "set_shipping_$key";
						if ( method_exists( $customer, $action ) ) {
							$customer->$action( $value );
						}
					}
					$customer->save();
				}
			}

			// Validate order items.
			foreach ( $object->get_items() as $order_item ) {
				$this->validate_order_item( $order_item );
			}

			if ( $creating ) {
				$object->set_created_via( 'POS' );
				$object->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
				$object->calculate_totals();
			} else {
				// If items have changed, recalculate order totals.
				if ( isset( $request['billing'] ) || isset( $request['shipping'] ) || isset( $request['line_items'] ) || isset( $request['shipping_lines'] ) || isset( $request['fee_lines'] ) || isset( $request['coupon_lines'] ) ) {
					$object->calculate_totals( true );
				}
			}

			// Set coupons.
			$this->calculate_coupons( $request, $object );

			// Set status.
			$object->set_status( $this->get_status( $request ) );

			$object->save();

			$author = ! empty( $object->get_meta( 'pos_host_served_by' ) ) ? $object->get_meta( 'pos_host_served_by' ) : get_current_user_id();
			wp_update_post(
				array(
					'ID'          => $object->get_id(),
					'post_type'   => 'shop_order',
					'post_author' => $author,
				)
			);

			// Actions for after the order is saved.
			if ( true === $request['set_paid'] ) {
				if ( $creating || $object->needs_payment() ) {
					$object->payment_complete( $request['transaction_id'] );
				}
			}

			if ( ! empty( $request['order_note'] ) ) {
				wc_create_order_note( $object->get_id(), $request['order_note'] );
			}

			pos_host_sent_email_receipt( $object->get_id() );

			/**
			 * Fires after a new order is created via POS.
			 *
			 * @since 5.2.10
			 *
			 * @param $order_id Order ID.
			 */
			do_action( 'woocommerce_pos_new_order', $object->get_id() );

			return $this->get_object( $object->get_id() );
		} catch ( WC_Data_Exception $e ) {
			return new WP_Error( $e->getErrorCode(), $e->getMessage(), $e->getErrorData() );
		} catch ( WC_REST_Exception $e ) {
			return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
		}
	}

	/**
	 * Set item.
	 *
	 * @param WC_Order $order order object.
	 * @param string   $item_type The item type.
	 * @param array    $posted item provided in the request body.
	 * @throws WC_REST_Exception If item ID is not associated with order.
	 */
	protected function set_item( $order, $item_type, $posted ) {
		global $wpdb;

		if ( ! empty( $posted['id'] ) ) {
			$action = 'update';
		} else {
			$action = 'create';
		}

		$method = 'prepare_' . $item_type;
		$item   = null;

		// Verify provided line item ID is associated with order.
		if ( 'update' === $action ) {
			$item = $order->get_item( absint( $posted['id'] ), false );

			if ( ! $item ) {
				throw new WC_REST_Exception( 'woocommerce_rest_invalid_item_id', __( 'Order item ID provided is not associated with order.', 'woocommerce-pos-host' ), 400 );
			}
		}

		if ( ! empty( $posted['note'] ) ) {
			$meta_data   = ! empty( $posted['meta_data'] ) && is_array( $posted['meta_data'] ) ? $posted['meta_data'] : array();
			$meta_data[] = array(
				'key'   => 'Item Note',
				'value' => $posted['note'],
			);

			$posted['meta_data'] = $meta_data;
		}

		if ( 'line_items' === $item_type ) {
			$posted_tax_class = $posted['product_data']['tax_class'];

			if ( 'none' === $posted_tax_class ) {
				$posted_tax_class = '0';
			}

			if ( 'standard' === $posted_tax_class ) {
				$posted_tax_class = '';
			}

			if ( 'parent' !== $posted_tax_class ) {
				$posted['tax_class'] = $posted_tax_class;
			}
		}

		// Prepare item data.
		$item = $this->$method( $posted, $action, $item );

		do_action( 'woocommerce_rest_set_order_item', $item, $posted );

		// If creating the order, add the item to it.
		if ( 'create' === $action ) {
			$order->add_item( $item );
		} else {
			$item->save();
		}
	}

	/**
	 * Coupon calculate method
	 *
	 * @param WP_REST_Request $request
	 * @param WC_Order        $order
	 * @return bool
	 * @throws WC_REST_Exception
	 */
	protected function calculate_coupons( $request, $order ) {
		if ( ! isset( $request['coupon_lines'] ) || ! is_array( $request['coupon_lines'] ) ) {
			return false;
		}

		// Remove all coupons first to ensure calculation is correct.
		foreach ( $order->get_items( 'coupon' ) as $coupon ) {
			$order->remove_coupon( $coupon->get_code() );
		}

		foreach ( $request['coupon_lines'] as $item ) {
			if ( is_array( $item ) ) {
				if ( empty( $item['id'] ) ) {
					if ( empty( $item['code'] ) ) {
						throw new WC_REST_Exception( 'woocommerce_rest_invalid_coupon', __( 'Coupon code is required.', 'woocommerce-pos-host' ), 400 );
					}

					if ( strtolower( 'POS Discount' ) === $item['code'] ) {
						$coupon_item = new WC_Order_Item_Coupon();
						$coupon_item->set_code( $item['code'] );
						$coupon_item->set_discount( $item['amount'] );
						$coupon_item->add_meta_data( 'pos_host_discount_reason', $item['reason'] );

						if ( isset( $item['type'] ) && 'percent' === $item['type'] && isset( $item['pamount'] ) ) {
							$coupon_item->add_meta_data( 'discount_amount_percent', $item['pamount'] );
						}

						$order->add_item( $coupon_item );
					} elseif ( 'WC_POINTS_REDEMPTION' === $item['code'] && class_exists( 'WC_Points_Rewards' ) ) {
						global $wc_points_rewards;

						$discount_amount = $item['amount'];
						$points_redeemed = WC_Points_Rewards_Manager::calculate_points_for_discount( $discount_amount );

						// Deduct points.
						WC_Points_Rewards_Manager::decrease_points(
							$order->get_user_id(),
							$points_redeemed,
							'order-redeem',
							array(
								'discount_code'   => $item['code'],
								'discount_amount' => $discount_amount,
							),
							$order->get_id()
						);

						update_post_meta( $order->get_id(), '_wc_points_redeemed', $points_redeemed );

						// Add order note.
						$order->add_order_note(
							sprintf(
								/* translators: %1$d: points redeemed. %2$s: points reward label. %3$s: discount amount. */
								__( '%1$d %2$s redeemed for a %3$s discount.', 'woocommerce-pos-host' ),
								$points_redeemed,
								$wc_points_rewards->get_points_label( $points_redeemed ),
								wc_price( $discount_amount )
							)
						);
					} else {
						$results = $order->apply_coupon( wc_clean( $item['code'] ) );

						if ( is_wp_error( $results ) ) {
							throw new WC_REST_Exception( 'woocommerce_rest_' . $results->get_error_code(), $results->get_error_message(), 400 );
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Returns customer ID.
	 *
	 * @param WP_REST_Request $data
	 * @return int|WC_REST_Exception
	 */
	public function get_customer_id( $data ) {
		global $wpdb;

		if ( true === $data['create_account'] ) {
			$billing_data                 = $data['billing'];
			$wc_reg_generate_username_opt = get_option( 'woocommerce_registration_generate_username' );
			$wc_reg_generate_pass_opt     = get_option( 'woocommerce_registration_generate_password' );

			if ( 'yes' === $wc_reg_generate_username_opt ) {
				$username = wc_create_new_customer_username(
					$data['email'],
					array(
						'first_name' => $billing_data['first_name'],
						'last_name'  => $billing_data['last_name'],
					)
				);
			} else {
				$username = $billing_data['account_username'];
			}

			$user_name_check = $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM {$wpdb->users} WHERE user_login = %s LIMIT 1", $username ) );

			if ( $user_name_check ) {
				$suffix = 1;
				do {
					$alt_user_name   = _truncate_post_slug( $username, 60 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";
					$user_name_check = $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM {$wpdb->users} WHERE user_login = %s LIMIT 1", $alt_user_name ) );
					$suffix++;
				} while ( $user_name_check );

				$username = $alt_user_name;
			}

			add_filter( 'pre_option_woocommerce_registration_generate_password', 'pos_host_enable_generate_password' );

			$password = '';
			if ( 'yes' === $wc_reg_generate_pass_opt ) {
				$password = isset( $billing_data['account_password'] ) ? $billing_data['account_password'] : '';
			}

			$new_customer = wc_create_new_customer( $billing_data['email'], $username, $password );

			remove_filter( 'pre_option_woocommerce_registration_generate_password', 'pos_host_enable_generate_password' );

			if ( is_wp_error( $new_customer ) ) {
				return new WC_REST_Exception( 'woocommerce_api_cannot_create_customer_account', $new_customer->get_error_message(), 400 );
			}

			// Add customer info from other billing fields.
			if ( $billing_data['first_name'] && apply_filters( 'pos_host_checkout_update_customer_data', true, $this ) ) {
				$userdata = array(
					'ID'           => $new_customer,
					'first_name'   => $billing_data['first_name'] ? $billing_data['first_name'] : '',
					'last_name'    => $billing_data['last_name'] ? $billing_data['last_name'] : '',
					'display_name' => $billing_data['first_name'] ? $billing_data['first_name'] : '',
				);
				wp_update_user( apply_filters( 'pos_host_checkout_customer_userdata', $userdata, $this ) );
			}

			return $new_customer;
		} else {
			return $data['customer_id'];
		}
	}

	/**
	 * Get status.
	 *
	 * @param WP_REST_Request $data
	 * @return string
	 */
	public function get_status( $data ) {
		$parked_order_status = get_option( 'pos_host_parked_order_status', 'pending' );

		if ( 'creating' === $parked_order_status && $data['action'] ) {
			return $parked_order_status;
		}

		return $data['status'];
	}

	/**
	 * Get the Order's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = parent::get_item_schema();

		$schema['properties']['line_items']['items']['properties']['quantity']['type'] = 'number';

		return $schema;
	}

	/**
	 * Validates an existing order item and returns any errors.
	 *
	 * @throws Exception Exception if invalid data is detected.
	 *
	 * @param array $order_item Cart item array.
	 */
	public function validate_order_item( $order_item ) {
		$product = $order_item->get_product();

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		if ( ! $product->is_purchasable() ) {
			throw new WC_REST_Exception(
				'woocommerce_rest_cart_product_not_purchasable',
				sprintf(
					/* translators: %s: product name */
					__( 'Sorry, &quot;%s&quot; cannot be purchased.', 'woocommerce-pos-host' ),
					$product->get_name()
				),
				400
			);
		}

		if ( $product->is_sold_individually() && $order_item->get_quantity() > 1 ) {
			throw new WC_REST_Exception(
				'woocommerce_rest_cart_product_sold_individually',
				sprintf(
					/* translators: %s: product name */
					__( 'There are too many &quot;%s&quot; in the cart. Only 1 can be purchased.', 'woocommerce-pos-host' ),
					$product->get_name()
				),
				400
			);
		}

		if ( ! $product->is_in_stock() ) {
			throw new WC_REST_Exception(
				'woocommerce_rest_cart_product_no_stock',
				sprintf(
					/* translators: %s: product name */
					__( '&quot;%s&quot; is out of stock and cannot be purchased.', 'woocommerce-pos-host' ),
					$product->get_name()
				),
				400
			);
		}
	}
}
