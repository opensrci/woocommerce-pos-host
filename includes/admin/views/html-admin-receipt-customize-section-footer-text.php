<?php
/**
 * Receipt Customizer - Footer Text
 *
 * @var object $receipt_object
 *
 * @package WooCommerce_pos_host/Admin/Views
 */
?>
<!-- Footer Text -->
<li class="customize-control customize-control-textarea">
	<span class="customize-inside-control-row">
		<textarea id="footer_text" name="footer_text" rows="8"><?php echo esc_html( $receipt_object->get_footer_text( 'edit' ) ); ?></textarea>
		<span class="description customize-control-description"><?php esc_html_e( 'Text to be printed in the footer of the receipt.', 'woocommerce-pos-host' ); ?></span>
	</span>
</li>
