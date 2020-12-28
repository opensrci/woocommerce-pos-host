<?php
/**
 * Grid tiles meta box.
 *
 * @package WooCommerce_pos_host/Admin/Meta_Boxes/Views
 */

defined( 'ABSPATH' ) || exit;

$tiles = $grid_object->get_tiles();

?>
<div class="pos-host-grid-tiles-wrapper">
	<table cellpadding="0" cellspacing="0" class="pos-host-grid-tiles">
		<thead>
			<tr>
				<th class="handle" width="1%">&nbsp</th>
				<th class="tile" colspan="2"><?php esc_html_e( 'Tile', 'woocommerce-pos-host' ); ?></th>
				<th class="type center" width="1%"><span class="pos-host-grid-tile-type tips" data-tip="<?php esc_attr_e( 'Tile Type', 'woocommerce-pos-host' ); ?>"></span></th>
				<th class="pos-host-grid-edit-tile" width="1%">&nbsp;</th>
			</tr>
		</thead>
		<tbody id="grid_tiles">
			<?php
			foreach ( $tiles as $tile_id => $tile ) {
				do_action( 'pos_host_before_grid_tile_html', $tile_id, $tile, $grid_object );

				include 'html-grid-tile.php';

				do_action( 'pos_host_grid_tile_html', $tile_id, $tile, $grid_object );
			}
			?>
		</tbody>
	</table>
</div>

<?php if ( empty( $tiles ) ) : ?>
<div class="pos-host-grid-tiles-row no-tiles">
	<p><?php esc_html_e( 'Tiles let you define custom grids for your register. You can add a tile that represents a product or a category.', 'woocommerce-pos-host' ); ?></p>
</div>
<?php endif; ?>

<div class="pos-host-grid-tiles-row pos-host-grid-tiles-actions">
	<button type="button" class="button add-tile"><?php esc_html_e( 'Add Tile', 'woocommerce-pos-host' ); ?></button>
	<button type="button" class="button button-primary delete-all-tiles"><?php esc_html_e( 'Delete All Tiles', 'woocommerce-pos-host' ); ?></button>
</div>

<script type="text/template" id="tmpl-pos-host-modal-add-tile">
	<div class="wc-backbone-modal" id="pos-host-modal-add-tile">
		<div class="wc-backbone-modal-content">
			<section class="wc-backbone-modal-main" role="main">
				<header class="wc-backbone-modal-header">
					<h1><?php esc_html_e( 'Add tile', 'woocommerce-pos-host' ); ?></h1>
					<button class="modal-close modal-close-link dashicons dashicons-no-alt">
						<span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'woocommerce-pos-host' ); ?></span>
					</button>
				</header>
				<article>
					<form action="" method="post">
						<table class="widefat">
							<tbody>
								<tr>
									<td width="50%">
									<?php
										// Type.
										woocommerce_wp_select(
											array(
												'id'      => 'tile_type',
												'label'   => null,
												'options' => apply_filters(
													'pos_host_grid_tile_type_options',
													array(
														'product'     => __( 'Product', 'woocommerce-pos-host' ),
														'product_cat' => __( 'Product Category', 'woocommerce-pos-host' ),
													)
												),
											)
										);
										?>
									</td>
									<td>
										<?php
										// Product.
										woocommerce_wp_select(
											array(
												'class'   => 'wc-product-search',
												'id'      => 'product_id',
												'label'   => null,
												'options' => array(),
												'custom_attributes' => array(
													'data-allow_clear'   => 'true',
													'data-display_stock' => 'false',
													'data-placeholder'   => esc_attr__( 'Search for a product&hellip;', 'woocommerce-pos-host' ),
												),
											)
										);
										// Product Category.
										woocommerce_wp_select(
											array(
												'class'   => 'wc-category-search',
												'id'      => 'product_cat',
												'label'   => null,
												'options' => array(),
												'custom_attributes' => array(
													'data-allow_clear'   => 'true',
													'data-placeholder'   => esc_attr__( 'Search for a category&hellip;', 'woocommerce-pos-host' ),
												),
											)
										);
										?>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
				</article>
				<footer>
					<div class="inner">
						<button id="btn-ok" class="button button-primary button-large"><?php esc_html_e( 'Add', 'woocommerce-pos-host' ); ?></button>
					</div>
				</footer>
			</section>
		</div>
	</div>
	<div class="wc-backbone-modal-backdrop modal-close"></div>
</script>
