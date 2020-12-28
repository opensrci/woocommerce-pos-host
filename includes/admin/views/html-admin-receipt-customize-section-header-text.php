<?php
/**
 * Receipt Customizer - Header Text
 *
 * @var object $receipt_object
 *
 * @package WooCommerce_pos_host/Admin/Views
 */
?>
<!-- Header Text -->
<li class="customize-control customize-control-textarea">
	<span class="customize-inside-control-row">
		<textarea id="header_text" name="header_text" rows="8"><?php echo esc_html( $receipt_object->get_header_text( 'edit' ) ); ?></textarea>
		<span class="description customize-control-description"><?php esc_html_e( 'Text to be printed in the header of the receipt.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>
