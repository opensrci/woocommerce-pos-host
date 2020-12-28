<?php
/**
 * Product Grids
 *
 * Display the product grids meta box.
 *
 * @package WooCommerce_pos_host/Admin/Meta_Boxes
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Meta_Box_Product_Grids.
 */
class POS_HOST_Meta_Box_Product_Grids {

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post Post object.
	 */
	public static function output( $post ) {
		global $thepostid, $grid_object;

		$thepostid     = $post->ID;
		$product_grids = pos_host_get_tile_grids( $thepostid );
		$all_grids     = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => 'pos_host_grid',
			)
		);

		include 'views/html-product-grids-panel.php';
	}
}
