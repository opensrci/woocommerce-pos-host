<?php
/**
 * Grid Tiles
 *
 * Display the grid tiles meta box.
 *
 * @package WooCommerce_pos_host/Admin/Meta_Boxes
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Meta_Box_Grid_Tiles.
 */
class POS_HOST_Meta_Box_Grid_Tiles {

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post Post object.
	 */
	public static function output( $post ) {
		global $thepostid, $grid_object;

		$thepostid   = $post->ID;
		$grid_object = pos_host_get_grid( $thepostid );

		include 'views/html-grid-tiles-panel.php';
	}
}
