<?php
/**
 * Admin Barcode Options
 *
 * @package WooCommerce_pos_host/Views
 */

defined( 'ABSPATH' ) || exit;

$products = array();
?>
<div id="woocommerce-order-items">
	<div class="woocommerce_order_items_wrapper wc-order-items-editable">
		<table cellspacing="0" cellpadding="0" class="woocommerce_order_items wp-list-table" id="barcode_generator">
			<thead>
			<tr>
				<th class="item" colspan="2"><?php esc_html_e( 'Item', 'woocommerce-pos-host' ); ?></th>
				<th class="item_cost"><?php esc_html_e( 'Cost', 'woocommerce-pos-host' ); ?></th>
				<th class="quantity"><?php esc_html_e( 'Qty', 'woocommerce-pos-host' ); ?></th>
				<th class="line_cost"><?php esc_html_e( 'Barcode', 'woocommerce-pos-host' ); ?></th>
				<th class="wc-order-edit-line-item" width="1%">&nbsp;</th>
			</tr>
			</thead>
			<tbody id="order_line_items">
			<?php
			if ( empty( $products ) ) {
				?>
				<tr class="no_products">
					<td colspan="8"><?php esc_html_e( 'Add products to generate barcodes.', 'woocommerce-pos-host' ); ?></td>
				</tr>
				<?php
			} else {
				foreach ( $products as $product_id ) {
					$_product = wc_get_product( $product_id );
					$class    = '';
					if ( ! $_product ) {
						continue;
					}

					include 'html-admin-barcode-item.php';
				}
			}
			?>
			</tbody>
		</table>
	</div>
	<div class="wc-order-data-row wc-order-bulk-actions wc-order-data-row-toggle">
		<button type="button" class="button bulk-delete-items" style="display:none;"><?php esc_html_e( 'Delete selected row(s)', 'woocommerce-pos-host' ); ?></button>
		<button type="button" class="button bulk-add-variations" style="display:none;"><?php esc_html_e( 'Add all variations', 'woocommerce-pos-host' ); ?></button>
		<button class="button add-line-item" type="button"><?php esc_html_e( 'Add product(s)', 'woocommerce-pos-host' ); ?></button>
		<button class="button add-line-item-category" type="button"><?php esc_html_e( 'Add category', 'woocommerce-pos-host' ); ?></button>

		<div class="barcode-edit-item">
			<button type="button" class="button cancel-action"><?php esc_html_e( 'Cancel', 'woocommerce-pos-host' ); ?></button>
			<button type="button" class="button button-primary save-action"><?php esc_html_e( 'Save', 'woocommerce-pos-host' ); ?></button>
		</div>
	</div>
</div>

<script type="text/template" id="pos_host_modal_barcode_add_products">
	<div id="pos-host-barcode-modal-dialog" tabindex="0">
		<div class="wc-backbone-modal">
			<div class="wc-backbone-modal-content">
				<section class="wc-backbone-modal-main" role="main">
					<header class="wc-backbone-modal-header">
						<h1><?php esc_html_e( 'Add products', 'woocommerce-pos-host' ); ?></h1>
						<button class="modal-close modal-close-link dashicons dashicons-no-alt">
							<span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'woocommerce-pos-host' ); ?></span>
						</button>
					</header>
					<article>
						<form action="" method="post">
							<?php if ( WC_VERSION >= 3 ) { ?>
								<select id="add_item_id" name="add_order_items" class="wc-product-search"
										style="width: 100%;"
										data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce-pos-host' ); ?>"
										multiple="multiple"></select>
							<?php } else { ?>
								<input type="hidden" id="add_item_id" name="add_order_items" class="wc-product-search"
									   style="width: 100%;"
									   data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce-pos-host' ); ?>"
									   data-multiple="true"/>
							<?php } ?>
						</form>
					</article>
					<footer>
						<div class="inner">
							<button id="add_products"
									class="button button-primary button-large"><?php esc_html_e( 'Add', 'woocommerce-pos-host' ); ?></button>
						</div>
					</footer>
				</section>
			</div>
		</div>
		<div class="wc-backbone-modal-backdrop modal-close"></div>
	</div>
</script>
<script type="text/template" id="pos_host_barcode_no_products">
	<tr class="no_products">
		<td colspan="8"><?php esc_html_e( 'Add products to generate barcodes', 'woocommerce-pos-host' ); ?></td>
	</tr>
</script>
