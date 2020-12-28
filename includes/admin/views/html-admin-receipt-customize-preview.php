<?php
/**
 * Receipt Customizer - Preview
 *
 * @var object $receipt_object
 * @var string $logo_src
 *
 * @package WooCommerce_pos_host/Admin/Views
 */

$get_current_user = wp_get_current_user();
$default_register = pos_host_get_register( absint( get_option( 'pos_host_default_register' ) ) );
$default_outlet   = pos_host_get_outlet( absint( get_option( 'pos_host_default_outlet' ) ) );
$address          = WC()->countries->get_formatted_address(
	array(
		'address_1' => $default_outlet->get_address_1(),
		'address_2' => $default_outlet->get_address_2(),
		'city'      => $default_outlet->get_city(),
		'postcode'  => $default_outlet->get_postcode(),
		'state'     => empty( $default_outlet->get_state() ) ? $default_outlet->get_state() : '',
		'country'   => $default_outlet->get_country(),
	)
);
$social_accounts  = $default_outlet->get_social_accounts();
?>
<div id="receipt-preview">
	<div id="receipt-title"><?php esc_html_e( 'Receipt', 'woocommerce-pos-host' ); ?></div>
	<div id="receipt-logo"><img src="<?php echo esc_url( $logo_src ); ?>" /></div>

	<div id="receipt-outlet-details" class="receipt-outlet-details">
		<div id="receipt-shop-name"><?php echo esc_html( bloginfo( 'name' ) ); ?></div>
		<div id="receipt-outlet-name"><?php esc_html_e( 'Outlet Name', 'woocommerce-pos-host' ); ?></div>
		<div id="receipt-outlet-address">
			<?php echo wp_kses_post( $address ); ?>
		</div>
		<div id="receipt-outlet-contact-details">
			<div><?php esc_html_e( 'Phone:', 'woocommerce-pos-host' ); ?> <?php echo ! empty( $default_outlet->get_phone() ) ? esc_html( $default_outlet->get_phone() ) : esc_html_x( '0161 154 6783', 'Receipt preview phone example', 'woocommerce-pos-host' ); ?></div>
			<div><?php esc_html_e( 'Fax:', 'woocommerce-pos-host' ); ?> <?php echo ! empty( $default_outlet->get_fax() ) ? esc_html( $default_outlet->get_fax() ) : esc_html_x( '0161 154 6784', 'Receipt preview fax example', 'woocommerce-pos-host' ); ?></div>
			<div><?php esc_html_e( 'Email:', 'woocommerce-pos-host' ); ?> <?php echo ! empty( $default_outlet->get_email() ) ? esc_html( $default_outlet->get_email() ) : esc_html( bloginfo( 'admin_email' ) ); ?></div>
			<div><?php esc_html_e( 'Website:', 'woocommerce-pos-host' ); ?> <?php echo ! empty( $default_outlet->get_website() ) ? esc_html( $default_outlet->get_website() ) : esc_url( bloginfo( 'url' ) ); ?></div>
		</div>
		<div id="receipt-wifi-details">
			<span><?php esc_html_e( 'Wi-Fi Network:', 'woocommerce-pos-host' ); ?> <?php esc_html( $default_outlet->get_wifi_network() ); ?></span><br />
			<span><?php esc_html_e( 'Wi-Fi Password:', 'woocommerce-pos-host' ); ?> <?php esc_html( $default_outlet->get_wifi_password() ); ?></span>
		</div>
		<div id="receipt-social-details">
			<div id="receipt-social-twitter"><?php esc_html_e( 'Twitter:', 'woocommerce-pos-host' ); ?> <?php echo isset( $social_accounts['twitter'] ) ? esc_html( $social_accounts['twitter'] ) : esc_html_x( 'OutletTW', 'Receipt Twitter account example', 'woocommerce-pos-host' ); ?></div>
			<div id="receipt-social-facebook"><?php esc_html_e( 'Facebook:', 'woocommerce-pos-host' ); ?> <?php echo isset( $social_accounts['facebook'] ) ? esc_html( $social_accounts['facebook'] ) : esc_html_x( 'OutletFB', 'Receipt Facebook account example', 'woocommerce-pos-host' ); ?></div>
			<div id="receipt-social-instagram"><?php esc_html_e( 'Instagram:', 'woocommerce-pos-host' ); ?> <?php echo isset( $social_accounts['instagram'] ) ? esc_html( $social_accounts['instagram'] ) : esc_html_x( 'OutletIN', 'Receipt Instagram account example', 'woocommerce-pos-host' ); ?></div>
			<div id="receipt-social-snapchat"><?php esc_html_e( 'Snapchat:', 'woocommerce-pos-host' ); ?> <?php echo isset( $social_accounts['snapchat'] ) ? esc_html( $social_accounts['snapchat'] ) : esc_html_x( 'OutletSC', 'Receipt Snapchat account example', 'woocommerce-pos-host' ); ?></div>
		</div>
		<div id="receipt-tax-number">
			<span id="receipt-tax-number-label"></span> <?php echo esc_html( get_option( 'pos_host_tax_number', '' ) ); ?>
		</div>
	</div>

	<div id="receipt-header-text">
		<?php echo esc_html( $receipt_object->get_header_text( 'edit' ) ); ?>
	</div>

	<table id="receipt-order-details">
		<tbody>
			<tr id="receipt-order-number">
				<th><?php esc_html_e( 'Order', 'woocommerce-pos-host' ); ?></th>
				<td><?php echo esc_html_x( 'WC1234AE', 'Receipt preview order number example', 'woocommerce-pos-host' ); ?></td>
			</tr>
			<tr id="receipt-order-date">
				<th><?php esc_html_e( 'Date', 'woocommerce-pos-host' ); ?></th>
				<td>
					<span class="date"><?php echo esc_html( date_i18n( $receipt_object->get_order_date_format( 'edit' ), time() ) ); ?></span>
					<span class="at"> <?php echo esc_html_x( 'at', 'At time', 'woocommerce-pos-host' ); ?> </span>
					<span class="time"><?php echo esc_html( date_i18n( $receipt_object->get_order_time_format( 'edit' ), time() ) ); ?></span>
				</td>
			</tr>
			<tr id="receipt-customer-name">
				<th><?php esc_html_e( 'Customer', 'woocommerce-pos-host' ); ?></th>
				<td><?php echo esc_html_x( 'John Doe', 'Receipt preview customer example', 'woocommerce-pos-host' ); ?></td>
			</tr>
			<tr id="receipt-customer-email">
				<th><?php esc_html_e( 'Email', 'woocommerce-pos-host' ); ?></th>
				<td><?php echo esc_html_x( 'mail@example.com', 'Receipt preview email example', 'woocommerce-pos-host' ); ?></td>
			</tr>
			<tr id="receipt-customer-phone">
				<th><?php esc_html_e( 'Phone', 'woocommerce-pos-host' ); ?></th>
				<td><?php echo ! empty( $default_outlet->get_phone() ) ? esc_html( $default_outlet->get_phone() ) : esc_html_x( '0161 154 6783', 'Receipt preview phone example', 'woocommerce-pos-host' ); ?></td>
			</tr>
			<tr id="receipt-customer-shipping-address">
				<th><?php esc_html_e( 'Shipping', 'woocommerce-pos-host' ); ?></th>
				<td><?php echo wp_kses_post( $address ); ?></td>
			</tr>
			<tr id="receipt-cashier-name" data-user_nicename="<?php echo esc_attr( $get_current_user->user_nicename ); ?>" data-display_name="<?php echo esc_attr( $get_current_user->display_name ); ?>" data-user_login="<?php echo esc_attr( $get_current_user->user_login ); ?>">
				<th><?php esc_html_e( 'Served by', 'woocommerce-pos-host' ); ?></th>
				<td>
					<span class="cashier"><?php echo esc_html( $get_current_user->{ $receipt_object->get_cashier_name_format( 'edit' ) } ); ?></span>
					<span id="receipt-register-name"> <?php echo esc_html_x( 'on Default Register', 'Receipt preview register name example', 'woocommerce-pos-host' ); ?> </span>
				</td>
			</tr>
			<tr id="receipt-order-notes">
				<th><?php echo esc_html_e( 'Order Notes', 'woocommerce-pos-host' ); ?></th>
				<td><?php echo esc_html_e( 'Please deliver this tomorrow at 12pm.', 'woocommerce-pos-host' ); ?></td>
			</tr>
			<tr id="receipt-dining-option">
				<th><?php echo esc_html_e( 'Dining Option', 'woocommerce-pos-host' ); ?></th>
				<td><?php echo esc_html_e( 'Take Away', 'woocommerce-pos-host' ); ?></td>
			</tr>
		</tbody>
	</table>

	<table id="receipt-product-details">
		<thead class="receipt-product-details-layout-single">
			<tr>
				<th class="qty"><?php esc_html_e( 'Qty', 'woocommerce-pos-host' ); ?></th>
				<th class="image">&nbsp;</th>
				<th class="product"><?php esc_html_e( 'Product', 'woocommerce-pos-host' ); ?></th>
				<th class="cost"><?php esc_html_e( 'Price', 'woocommerce-pos-host' ); ?></th>
				<th class="total"><?php esc_html_e( 'Total', 'woocommerce-pos-host' ); ?></th>
			</tr>
		</thead>
		<thead class="receipt-product-details-layout-multiple">
			<tr>
				<th class="item" colspan="3"><?php esc_html_e( 'Item', 'woocommerce-pos-host' ); ?></th>
				<th class="total"><?php esc_html_e( 'Total', 'woocommerce-pos-host' ); ?></th>
			</tr>
		</thead>
		<tbody class="receipt-product-details-layout-single">
			<tr>
				<td class="qty"><?php echo esc_html_x( '2', 'Receipt preview quantity example', 'woocommerce-pos-host' ); ?></td>
				
				<td class="product">
					<strong><?php esc_attr_e( 'Mobile Phone', 'woocommerce-pos-host' ); ?></strong>
					<small class="receipt-product-sku" class="receipt-product-sku"><?php echo esc_html_x( 'SKU: PRDCT123', 'Receipt preview SKU example', 'woocommerce-pos-host' ); ?></small>
					<small class="receipt-product-image"><?php echo esc_html_x( 'Size: 32GB', 'Receipt preview product size example', 'woocommerce-pos-host' ); ?></small>
					<small class="receipt-product-image"><?php echo esc_html_x( 'Color: Silver', 'Receipt preview product color example', 'woocommerce-pos-host' ); ?></small>
				</td>
				<td class="image receipt-product-image"><?php echo wp_kses_post( wc_placeholder_img( 'thumbnail' ) ); ?></td>
				<td class="cost receipt-product-cost">
					<div class="line-through-if-discount-enabled"><?php echo esc_html_x( '£59.00', 'Receipt preview product cost example', 'woocommerce-pos-host' ); ?></div>
					<div class="show-if-discount-enabled"><?php echo esc_html_x( '£55.00', 'Receipt preview product discounted cost example', 'woocommerce-pos-host' ); ?></div>
				</td>
				<td class="total">
					<div class="line-through-if-discount-enabled"><?php echo esc_html_x( '£118.00', 'Receipt preview product total example', 'woocommerce-pos-host' ); ?></div>
					<div class="show-if-discount-enabled"><?php echo esc_html_x( '£110.00', 'Receipt preview product discounted total example', 'woocommerce-pos-host' ); ?></div>
				</td>
			</tr>
		</tbody>
		<tbody class="receipt-product-details-layout-multiple">
			<tr>
				<td class="product" colspan="3">
					<strong><?php esc_html_e( 'Mobile Phone', 'woocommerce-pos-host' ); ?></strong>
					<span class="receipt-product-sku"> – <?php echo esc_html_x( 'SKU: PRDCT123', 'Receipt preview SKU example', 'woocommerce-pos-host' ); ?></span><br>
					<span class="size"><?php echo esc_html_x( 'Size: 32GB', 'Receipt preview product size example', 'woocommerce-pos-host' ); ?></span><br>
					<span class="color"><?php echo esc_html_x( 'Color: Silver', 'Receipt preview product color example', 'woocommerce-pos-host' ); ?></span><br>					
					<span><?php esc_html_e( 'Qty:', 'woocommerce-pos-host' ); ?> <?php echo esc_html_x( '2', 'Receipt preview quantity example', 'woocommerce-pos-host' ); ?></span>
					<span class="cost receipt-product-cost"></span>
					<span> &times; </span>
					<span class="line-through-if-discount-enabled"><?php echo esc_html_x( '£59.00', 'Receipt preview product cost example', 'woocommerce-pos-host' ); ?></span>
					<span class="show-if-discount-enabled"><?php echo esc_html_x( '£55.00', 'Receipt preview product discounted cost example', 'woocommerce-pos-host' ); ?></span>
					</span>
				</td>
				<td class="total">
					<div class="line-through-if-discount-enabled"><?php echo esc_html_x( '£118.00', 'Receipt preview product total example', 'woocommerce-pos-host' ); ?></div>
					<div class="show-if-discount-enabled"><?php echo esc_html_x( '£110.00', 'Receipt preview product discounted total example', 'woocommerce-pos-host' ); ?></div>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<th scope="row" colspan="3"><?php echo esc_html_e( 'Subtotal', 'woocommerce-pos-host' ); ?></th>
				<td>
					<div class="line-through-if-discount-enabled"><?php echo esc_html_x( '£118.00', 'Receipt preview subtotal example', 'woocommerce-pos-host' ); ?></div>
					<div class="show-if-discount-enabled"><?php echo esc_html_x( '£110.00', 'Receipt preview discounted subtotal example', 'woocommerce-pos-host' ); ?></div>
				</td>
			</tr>
			<tr id="receipt-tax">
				<th scope="row" colspan="3"><?php echo esc_html_e( 'Tax', 'woocommerce-pos-host' ); ?></th>
				<td>
					<div class="line-through-if-discount-enabled"><?php echo esc_html_x( '£23.60', 'Receipt preview tax example', 'woocommerce-pos-host' ); ?></div>
					<div class="show-if-discount-enabled"><?php echo esc_html_x( '£22.00', 'Receipt preview discounted tax example', 'woocommerce-pos-host' ); ?></div>
				</td>
			</tr>
			<tr>
				<th scope="row" colspan="3"><?php echo esc_html_e( 'Payment Type Sales', 'woocommerce-pos-host' ); ?></th>
				<td>
					<div class="line-through-if-discount-enabled"><?php echo esc_html_x( '£141.60', 'Receipt preview sales example', 'woocommerce-pos-host' ); ?></div>
					<div class="show-if-discount-enabled"><?php echo esc_html_x( '£132.00', 'Receipt preview discounted sales example', 'woocommerce-pos-host' ); ?></div>
				</td>
			</tr>
			<tr>
				<th scope="row" colspan="3"><?php echo esc_html_e( 'Total', 'woocommerce-pos-host' ); ?></th>
				<td><?php echo esc_html_x( '£141.60', 'Receipt preview total example', 'woocommerce-pos-host' ); ?></td>
			</tr>
			<tr>
				<th scope="row" colspan="3"><?php echo esc_html_e( 'Change', 'woocommerce-pos-host' ); ?></th>
				<td><?php echo esc_html_x( '£0.00', 'Receipt preview change example', 'woocommerce-pos-host' ); ?></td>
			</tr>
			<tr id="receipt-no-items">
				<th scope="row" colspan="3"><?php echo esc_html_e( 'Number of Items', 'woocommerce-pos-host' ); ?></th>
				<td><?php echo esc_html_x( '2', 'Receipt preview number of items example', 'woocommerce-pos-host' ); ?></td>
			</tr>
		<tfoot>
	</table>

	<table id="receipt-tax-summary">
		<thead>
			<tr>
				<th class="tax-name"><?php echo esc_html_e( 'Tax Name', 'woocommerce-pos-host' ); ?></th>
				<th class="tax-rate"><?php echo esc_html_e( 'Tax Rate', 'woocommerce-pos-host' ); ?></th>
				<th class="tax"><?php echo esc_html_e( 'Tax', 'woocommerce-pos-host' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>&nbsp;</td>
				<td><?php echo esc_html_x( 'GB-VAT-20', 'Receipt preview tax name example', 'woocommerce-pos-host' ); ?></td>
				<td><?php echo esc_html_x( '20.00', 'Receipt preview tax rate example', 'woocommerce-pos-host' ); ?></td>
				<td><?php echo esc_html_x( '£23.60', 'Receipt preview tax example', 'woocommerce-pos-host' ); ?></td>
			</tr>
		</tbody>
	</table>

	<div id="receipt-outlet-details-footer" class="receipt-outlet-details"></div>

	<div id="receipt-order-barcode">
		<img />
	</div>

	<div id="receipt-footer-text">
		<?php echo esc_html( $receipt_object->get_footer_text( 'edit' ) ); ?>
	</div>
</div>
<style type="text/less" id="receipt-custom-css"></style>
<script>
	(function($) {
		$(function() {
			var barcodeCanvas = document.createElement('canvas');
			bwipjs.toCanvas(barcodeCanvas, {
				bcid: '<?php echo esc_html( ! empty( $receipt_object->get_barcode_type( 'edit' ) ) ? $receipt_object->get_barcode_type( 'edit' ) : 'code128' ); ?>',
				text: 'WC1234AE',
				scale: 2,
				includetext: true,
				textxalign:  'center',
			});
			jQuery( '#receipt-order-barcode img' ).attr( 'src', barcodeCanvas.toDataURL('image/png') );
		});
	})(jQuery);
</script>
