<?php
/**
 * Receipt Customizer - Order Details
 *
 * @var object $receipt_object
 *
 * @package WooCommerce_pos_host/Admin/Views
 */

$date_formats   = array_unique( apply_filters( 'pos_host_receipt_order_date_formats', array( 'F j, Y', 'Y-m-d', 'm/d/Y', 'd/m/Y' ) ) );
$date_formats[] = '';
$time_formats   = array_unique( apply_filters( 'pos_host_receipt_order_time_formats', array( 'g:i a', 'g:i A', 'H:i' ) ) );
$time_formats[] = '';

// Is custom date/time format?
$is_custom_date_format = in_array( $receipt_object->get_order_date_format( 'edit' ), $date_formats, true ) ? false : true;
$is_custom_time_format = in_array( $receipt_object->get_order_time_format( 'edit' ), $time_formats, true ) ? false : true;
?>
<!-- Show Order Date -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_order_date" name="show_order_date" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_order_date(), true, true ); ?>>
		<label for="show_order_date"><?php esc_html_e( 'Order Date', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print the order date.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Order Date Format -->
<li class="customize-control customize-control-checkbox">
	<label class="customize-control-title" for="order_date_format"><?php esc_html_e( 'Order Date Format', 'woocommerce-pos-host' ); ?></label>
	<span class="description customize-control-description"><?php esc_html_e( 'Choose the format of the order date that is printed.', 'woocommerce-pos-host' ); ?></span>
	<?php foreach ( $date_formats as $index => $format ) : ?>
		<span class="customize-inside-control-row">
			<input name="order_date_format" id="order_date_format_<?php echo esc_attr( $index ); ?>" type="radio" value="<?php echo esc_attr( $format ); ?>" <?php checked( $is_custom_date_format ? '' : $receipt_object->get_order_date_format( 'edit' ), $format, true ); ?> />
			<label title="<?php echo esc_attr( $format ); ?>" for="order_date_format_<?php echo esc_attr( $index ); ?>">
				<?php echo esc_html( empty( $format ) ? __( 'Custom', 'woocommerce-pos-host' ) : date_i18n( $format, time() ) ); ?>
			</label>
		</span>
	<?php endforeach; ?>
	<input name="order_date_format_custom" id="order_date_format_custom" type="text" value="<?php echo esc_attr( $is_custom_date_format ? $receipt_object->get_order_date_format( 'edit' ) : '' ); ?>">
</li>

<!-- Order Time Format -->
<li class="customize-control customize-control-checkbox">
	<label class="customize-control-title" for="order_time_format"><?php esc_html_e( 'Order Time Format', 'woocommerce-pos-host' ); ?></label>
	<span class="description customize-control-description"><?php esc_html_e( 'Choose the format of the order time that is printed.', 'woocommerce-pos-host' ); ?></span>
	<?php foreach ( $time_formats as $index => $format ) : ?>
		<span class="customize-inside-control-row">
			<input name="order_time_format" id="order_time_format_<?php echo esc_attr( $index ); ?>" type="radio" value="<?php echo esc_attr( $format ); ?>" <?php checked( $is_custom_time_format ? '' : $receipt_object->get_order_time_format( 'edit' ), $format, true ); ?> />
			<label title="<?php echo esc_attr( $format ); ?>" for="order_time_format_<?php echo esc_attr( $index ); ?>">
				<?php echo esc_html( empty( $format ) ? __( 'Custom', 'woocommerce-pos-host' ) : date_i18n( $format, time() ) ); ?>
			</label>
		</span>
	<?php endforeach; ?>
	<input name="order_time_format_custom" id="order_time_format_custom" type="text" value="<?php echo esc_attr( $is_custom_time_format ? $receipt_object->get_order_time_format( 'edit' ) : '' ); ?>">
</li>

<!-- Show Customer Name -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_customer_name" name="show_customer_name" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_customer_name(), true, true ); ?>>
		<label for="show_customer_name"><?php esc_html_e( 'Customer Name', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print the customer name.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Show Customer Email -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_customer_email" name="show_customer_email" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_customer_email(), true, true ); ?>>
		<label for="show_customer_email"><?php esc_html_e( 'Customer Email', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print the customer email.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Show Customer Phone -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_customer_phone" name="show_customer_phone" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_customer_phone(), true, true ); ?>>
		<label for="show_customer_phone"><?php esc_html_e( 'Customer Phone', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print the customer billing phone number.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Show Customer Shipping Address -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_customer_shipping_address" name="show_customer_shipping_address" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_customer_shipping_address(), true, true ); ?>>
		<label for="show_customer_shipping_address"><?php esc_html_e( 'Customer Shipping Address', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print the customer shipping address.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Show Cashier Name -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_cashier_name" name="show_cashier_name" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_cashier_name(), true, true ); ?>>
		<label for="show_cashier_name"><?php esc_html_e( 'Cashier Name', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print the name of the cashier that placed the order.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Cashier Name Format -->
<li class="customize-control customize-control-select">
	<label class="customize-control-title" for="cashier_name_format"><?php esc_html_e( 'Cashier Name Format', 'woocommerce-pos-host' ); ?></label>
	<span class="description customize-control-description"><?php esc_html_e( 'Choose the format of the cashiers name that is printed.', 'woocommerce-pos-host' ); ?></span>
	<select id="cashier_name_format" name="cashier_name_format">
		<option value="user_nicename" <?php selected( $receipt_object->get_cashier_name_format(), 'user_nicename', true ); ?> ><?php esc_html_e( 'Nickname', 'woocommerce-pos-host' ); ?></option>
		<option value="display_name" <?php selected( $receipt_object->get_cashier_name_format(), 'display_name', true ); ?>><?php esc_html_e( 'Display Name', 'woocommerce-pos-host' ); ?></option>
		<option value="user_login" <?php selected( $receipt_object->get_cashier_name_format(), 'user_login', true ); ?>><?php esc_html_e( 'Username', 'woocommerce-pos-host' ); ?></option>
	</select>
</li>

<!-- Show Register Name -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_register_name" name="show_register_name" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_register_name(), true, true ); ?>>
		<label for="show_register_name"><?php esc_html_e( 'Register Name', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print name of the register that the order was placed through.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

