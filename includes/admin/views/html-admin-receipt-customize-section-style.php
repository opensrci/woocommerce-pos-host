<?php
/**
 * Receipt Customizer - Style Details
 *
 * @var object $receipt_object
 *
 * @package WooCommerce_pos_host/Admin/Views
 */
?>
<!-- Text Size -->
<li class="customize-control customize-control-select">
	<label class="customize-control-title" for="text_size"><?php esc_html_e( 'Text Size', 'woocommerce-pos-host' ); ?></label>
	<span class="description customize-control-description"><?php esc_html_e( 'Select the size of the text that is printed.', 'woocommerce-pos-host' ); ?></span>
	<select id="text_size" name="text_size">
		<option value="tiny" <?php selected( $receipt_object->get_text_size(), 'tiny', true ); ?>><?php esc_html_e( 'Tiny', 'woocommerce-pos-host' ); ?></option>
		<option value="small" <?php selected( $receipt_object->get_text_size(), 'small', true ); ?> ><?php esc_html_e( 'Small', 'woocommerce-pos-host' ); ?></option>
		<option value="normal" <?php selected( $receipt_object->get_text_size(), 'normal', true ); ?>><?php esc_html_e( 'Normal', 'woocommerce-pos-host' ); ?></option>
		<option value="large" <?php selected( $receipt_object->get_text_size(), 'large', true ); ?>><?php esc_html_e( 'Large', 'woocommerce-pos-host' ); ?></option>
	</select>
</li>
