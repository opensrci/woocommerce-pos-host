<?php
/**
 * Orders Page
 *
 * @package WooCommerce_pos_host/Classes/Admin
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Admin_Orders_Page', false ) ) {
	return new POS_HOST_Admin_Orders_Page();
}

/**
 * POS_HOST_Admin_Orders_Page.
 */
class POS_HOST_Admin_Orders_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'update_customer_name' ), 2 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'display_order_type_column' ), 2 );
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_type_column' ), 9999 );
		add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_orders' ), 5 );
		add_action( 'add_meta_boxes', array( $this, 'add_pos_print_metabox' ) );
		add_filter( 'woocommerce_order_item_get_code', array( $this, 'filter_pos_discount_code' ), 99, 2 );
		add_filter( 'woocommerce_order_item_get_discount', array( $this, 'filter_pos_discount_amount' ), 99, 2 );
	}

	/**
	 * Update customer name for POS guest orders.
	 */
	public function update_customer_name() {
		global $post, $the_order;

		if ( empty( $the_order ) || $post->ID !== $the_order->get_id() ) {
			$the_order = wc_get_order( $post->ID );

			if ( ! $the_order ) {
				return;
			}
		}

		if ( ! $the_order->get_billing_first_name() && 'POS' === $the_order->get_data()['created_via'] ) {
			$the_order->set_billing_first_name( __( 'Walk-in Customer', 'woocommerce-pos-host' ) );
		}
	}

	/**
	 * Displays the value of the order type column fo
	 *
	 * @param [type] $column
	 * @return void
	 */
	public function display_order_type_column( $column ) {
		global $post, $woocommerce, $the_order;

		if ( empty( $the_order ) || $post->ID !== $the_order->get_id() ) {
			$the_order = wc_get_order( $post->ID );

			if ( ! $the_order ) {
				return;
			}
		}

		if ( 'pos_host_order_type' === $column ) {
			$order_id    = $the_order->get_id();
			$created_via = get_post_meta( $order_id, '_created_via', true );
			if ( 'checkout' === $created_via ) {
				$order_type = __( '<span class="order-type-web tips" data-tip="Website"><span>', 'woocommerce-pos-host' );
			} else {
				$order_type = __( '<span class="order-type-staff tips" data-tip="Manual"><span>', 'woocommerce-pos-host' );
			}
			$amount_change = get_post_meta( $order_id, 'pos_host_order_type', true );
			if ( $amount_change ) {
				$order_type = __( '<span class="order-type-pos tips" data-tip="Point of Sale"><span>', 'woocommerce-pos-host' );
			}

			echo wp_kses_post( $order_type );
		}
	}

	/**
	 * Adds an order type column to the orders listing table.
	 *
	 * @param array $columns
	 * @return array
	 */
	public function add_order_type_column( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			if ( 'order_number' === $key ) {
				$new_columns['pos_host_order_type'] = __( '<span class="order-type tips" data-tip="Order Type">Order Type</span>', 'woocommerce-pos-host' );
			}

			$new_columns[ $key ] = $value;
		}
		return $new_columns;
	}

	public function restrict_manage_orders( $value = '' ) {
		global $woocommerce, $typenow;

		if ( 'shop_order' !== $typenow ) {
			return;
		}

		$req_type = isset( $_REQUEST['shop_order_pos_host_order_type'] ) ? wc_clean( wp_unslash( $_REQUEST['shop_order_pos_host_order_type'] ) ) : '';
		$req_reg  = isset( $_REQUEST['shop_order_pos_host_filter_register'] ) ? wc_clean( wp_unslash( $_REQUEST['shop_order_pos_host_filter_register'] ) ) : '';
		$req_out  = isset( $_REQUEST['shop_order_pos_host_filter_outlet'] ) ? wc_clean( wp_unslash( $_REQUEST['shop_order_pos_host_filter_outlet'] ) ) : '';
		?>
	<select name='shop_order_pos_host_order_type' id='dropdown_shop_order_pos_host_order_type'>
		<option value=""><?php esc_attr_e( 'All types', 'woocommerce-pos-host' ); ?></option>
		<option value="online" <?php selected( $req_type, 'online', true ); ?> ><?php esc_html_e( 'Online', 'woocommerce-pos-host' ); ?></option>
		<option value="POS" <?php selected( $req_type, 'POS', true ); ?> ><?php esc_html_e( 'POS', 'woocommerce-pos-host' ); ?></option>
	</select>
		<?php
		$filters = get_option( 'pos_host_order_filters', array( 'registers' ) );

		if ( ! $filters || ! is_array( $filters ) ) {
			return;
		}

		if ( in_array( 'registers', $filters, true ) ) {
			$registers = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => 'pos_register',
				)
			);
			if ( $registers ) {
				?>
		<select name='shop_order_pos_host_filter_register' id='shop_order_pos_host_filter_register'>
		<option value=""><?php esc_html_e( 'All registers', 'woocommerce-pos-host' ); ?></option>
				<?php
				foreach ( $registers as $register ) {
					echo '<option value="' . esc_attr( $register->ID ) . '" ' . selected( $req_reg, $register->ID, false ) . ' >' . esc_html( $register->post_title ) . '</option>';
				}
				?>
		</select>
				<?php
			}
		}
		if ( in_array( 'outlets', $filters, true ) ) {
			$outlets = get_posts(
				array(
					'numberposts' => - 1,
					'post_type'   => 'pos_outlet',
				)
			);
			if ( $outlets ) {
				?>
		<select name='shop_order_pos_host_filter_outlet' id='shop_order_pos_host_filter_outlet'>
		<option value=""><?php esc_html_e( 'All outlets', 'woocommerce-pos-host' ); ?></option>
				<?php
				foreach ( $outlets as $outlet ) {
					echo '<option value="' . esc_attr( $outlet->ID ) . '" ' . selected( $req_out, $outlet->ID, false ) . ' >' . esc_html( $outlet->post_title ) . '</option>';
				}
				?>
		</select>
				<?php
			}
		}

	}

	public function add_pos_print_metabox() {
		global $post_id;

		if ( 'POS' !== get_post_meta( $post_id, 'pos_host_order_type', true ) ) {
			return;
		}

		add_meta_box( 'wc-pos-print', __( 'Point of Sale', 'woocommerce-pos-host' ), array( $this, 'pos_print_output' ), 'shop_order', 'side' );
	}

	public function pos_print_output() {
		global $post_id;

		$register  = pos_host_get_register( absint( get_post_meta( $post_id, 'pos_host_register_id', true ) ) );
		$cashier   = get_post_meta( $post_id, 'pos_host_served_by_name', true );
		$amount    = get_post_meta( $post_id, 'pos_host_amount_pay', true );
		$change    = get_post_meta( $post_id, 'pos_host_amount_change', true );
		$order     = get_post_meta( $post_id, 'pos_host_prefix_suffix_order_number', true );
		$signature = get_post_meta( $post_id, 'pos_host_signature', true );
		?>
		<?php if ( $register ) : ?>
		<div class="register_meta_data">
			<?php esc_html_e( 'Register:', 'woocommerce-pos-host' ); ?> <span><strong><?php echo esc_html( $register->get_name() ); ?></strong></span>
		</div>
		<?php endif; ?>
		<div class="register_meta_data">
			<?php esc_html_e( 'Cashier:', 'woocommerce-pos-host' ); ?> <span><strong><?php echo esc_html( $cashier ); ?></strong></span>
		</div>
		<div class="register_meta_data">
			<?php esc_html_e( 'Total:', 'woocommerce-pos-host' ); ?> <span><strong><?php echo wp_kses_post( wc_price( get_post_meta( $post_id, '_order_total', true ) ) ); ?></strong></span>
		</div>
		<div class="register_meta_data">
			<?php esc_html_e( 'Tendered:', 'woocommerce-pos-host' ); ?> <span><strong><?php echo wp_kses_post( wc_price( $amount ) ); ?></strong></span>
		</div>
		<?php if ( $change > 0 ) : ?>
		<div class="register_meta_data">
			<?php esc_html_e( 'Change:', 'woocommerce-pos-host' ); ?> <span><strong><?php echo wp_kses_post( wc_price( $change ) ); ?></strong></span>
		</div>
		<?php endif; ?>
		<?php if ( $change < 0 ) : ?>
		<div class="register_meta_data amount_due">
			<?php esc_html_e( 'Amount Due:', 'woocommerce-pos-host' ); ?> <span><strong><?php echo wp_kses_post( wc_price( $change ) ); ?></strong></span>
		</div>
		<?php endif; ?>
		<div class="register_meta_data">
			<?php esc_html_e( 'Order #:', 'woocommerce-pos-host' ); ?> <span><strong><?php echo esc_html( $order ); ?></strong></span>
		</div>
		<?php
		$coupons = wc_get_order( $post_id )->get_items( 'coupon' );
		$reason  = '';
		foreach ( $coupons as $coupon ) {
			if ( 'POS Discount' === $coupon->get_name() ) {
				$reason = wc_get_order_item_meta( $coupon->get_id(), 'pos_host_discount_reason', true );
				break;
			}
		}
		?>
		<?php if ( ! empty( $reason ) ) : ?>
		<div class="register_meta_data">
			<?php esc_html_e( 'Discount Reason:', 'woocommerce-pos-host' ); ?> <span><strong><?php echo esc_html( $reason ); ?></strong></span>
		</div>
		<?php endif; ?>
		<?php if ( 'null' !== $signature ) : ?>
			<?php add_thickbox(); ?>
			<div id="signature-content" style="display:none;">
				<img src="data:image/png;base64,<?php echo esc_attr( str_replace( 'data:image/png;base64,', '', $signature ) ); ?>" alt="signature" id="signature-img" />
			</div>
			<a href="#TB_inline?width=300&height=300&inlineId=signature-content" id="preview-signature" class="thickbox button button-block"><?php esc_html_e( 'Signature', 'woocommerce-pos-host' ); ?></a>
		<?php endif; ?>
		<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?print_pos_receipt=true&print_from_wc=true&order_id=' . $post_id ), 'print_pos_receipt' ) ); ?>" target="_blank" id="view-post-receipt" class="button button-block">
			<?php esc_html_e( 'View Receipt', 'woocommerce-pos-host' ); ?>
		</a>
		<button id="print-post-receipt" class="button button-primary button-block"><?php esc_html_e( 'Print Receipt', 'woocommerce-pos-host' ); ?></button>
		<?php
	}

	/**
	 * Filter discount code.
	 *
	 * @param string               $value
	 * @param WC_Order_Item_Coupon $coupon
	 *
	 * @return string
	 */
	public function filter_pos_discount_code( $value, $coupon ) {
		if ( 'pos discount' === $coupon->get_data()['code'] ) {
			$value .= ' - ' . $coupon->get_meta( 'pos_host_discount_reason' );
		}

		return $value;
	}


	/**
	 * Filter discount amount.
	 *
	 * @param float                $value
	 * @param WC_Order_Item_Coupon $coupon
	 *
	 * @return string
	 */
	public function filter_pos_discount_amount( $value, $coupon ) {
		if ( 'pos discount' === $coupon->get_data()['code'] ) {
			$value_meta = $coupon->get_meta( 'discount_amount_percent' );
			if ( $value_meta ) {
				$value = $value_meta;
			}
		}

		return $value;
	}
}

return new POS_HOST_Admin_Orders_Page();
