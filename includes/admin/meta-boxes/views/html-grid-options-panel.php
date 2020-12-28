<?php
/**
 * Grid options meta box.
 *
 * @package WooCommerce_pos_host/Admin/Meta_Boxes/Views
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="grid_options" class="panel-wrap grid_options">
	<div id="general_grid_options" class="panel woocommerce_options_panel">
		<?php
			woocommerce_wp_select(
				array(
					'id'          => 'sort_by',
					'value'       => $grid_object->get_sort_by( 'edit' ),
					'label'       => __( 'Default Sort Order', 'woocommerce-pos-host' ),
					'options'     => apply_filters(
						'pos_host_grid_sort_by_options',
						array(
							'name'   => __( 'Name', 'woocommerce-pos-host' ),
							'custom' => __( 'Custom ordering', 'woocommerce-pos-host' ),
						)
					),
					'desc_tip'    => true,
					'description' => __( 'Determines the sort order of the products on the POS page. If using custom ordering, you can drag and drop the products in this grid.', 'woocommerce-pos-host' ),
				)
			);
			?>
	</div>
	<?php do_action( 'pos_host_grid_options_panels', $thepostid, $grid_object ); ?>
	<div class="clear"></div>
</div>
