<?php
/**
 * Receipt Customizer - Product Details
 *
 * @var object $receipt_object
 *
 * @package WooCommerce_pos_host/Admin/Views
 */
?>
<!-- Product Details Layout -->
<li class="customize-control customize-control-select">
	<label class="customize-control-title" for="product_details_layout"><?php esc_html_e( 'Product Details Layout', 'woocommerce-pos-host' ); ?></label>
	<span class="description customize-control-description"><?php esc_html_e( 'Select how to display the product item details.', 'woocommerce-pos-host' ); ?></span>
	<select id="product_details_layout" name="product_details_layout">
		<option value="single" <?php selected( $receipt_object->get_product_details_layout(), 'single', true ); ?> ><?php esc_html_e( 'Single Line', 'woocommerce-pos-host' ); ?></option>
		<option value="multiple" <?php selected( $receipt_object->get_product_details_layout(), 'multiple', true ); ?>><?php esc_html_e( 'Multiple Lines', 'woocommerce-pos-host' ); ?></option>
	</select>
</li>

<!-- Show Product Image -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_product_image" name="show_product_image" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_product_image(), true, true ); ?>>
		<label for="show_product_image"><?php esc_html_e( 'Product Image', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print the product image.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Show Product SKU -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_product_sku" name="show_product_sku" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_product_sku(), true, true ); ?>>
		<label for="show_product_sku"><?php esc_html_e( 'Product SKU', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print the product SKU.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Show Product Cost -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_product_cost" name="show_product_cost" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_product_cost(), true, true ); ?>>
		<label for="show_product_cost"><?php esc_html_e( 'Product Price', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print the product cost.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Show Product Discount -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_product_discount" name="show_product_discount" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_product_discount(), true, true ); ?>>
		<label for="show_product_discount"><?php esc_html_e( 'Product Discount', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print the discount.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Show Number of Items -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_no_items" name="show_no_items" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_no_items(), true, true ); ?>>
		<label for="show_no_items"><?php esc_html_e( 'Number of Items', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print the total number of items.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Show Tax Summary -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_tax_summary" name="show_tax_summary" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_tax_summary(), true, true ); ?>>
		<label for="show_tax_summary"><?php esc_html_e( 'Tax Summary', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print tax summary.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Order Barcode -->
<li class="customize-control customize-control-checkbox">
	<span class="customize-inside-control-row">
		<input id="show_order_barcode" name="show_order_barcode" type="checkbox" value="yes" <?php checked( $receipt_object->get_show_order_barcode(), true, true ); ?>>
		<label for="show_order_barcode"><?php esc_html_e( 'Order Barcode', 'woocommerce-pos-host' ); ?></label>
		<span class="description customize-control-description"><?php esc_html_e( 'Print the order barcode.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>

<!-- Barcode Type -->
<li class="customize-control customize-control-select">
	<label class="customize-control-title" for="barcode_type"><?php esc_html_e( 'Barcode type', 'woocommerce-pos-host' ); ?></label>
	<span class="description customize-control-description"><?php esc_html_e( 'Select the barcode type.', 'woocommerce-pos-host' ); ?></span>
	<select id="barcode_type" name="barcode_type">
		<option value="code128" <?php selected( $receipt_object->get_barcode_type(), 'code128', true ); ?> ><?php esc_html_e( 'Code 128', 'woocommerce-pos-host' ); ?></option>
		<option value="qrcode" <?php selected( $receipt_object->get_barcode_type(), 'qrcode', true ); ?>><?php esc_html_e( 'Quick Response (QR)', 'woocommerce-pos-host' ); ?></option>
	</select>
</li>
