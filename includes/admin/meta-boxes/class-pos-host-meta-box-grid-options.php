<?php
/**
 * Grid Options
 *
 * Display the grid options meta box.
 *
 * @package WooCommerce_pos_host/Admin/Meta_Boxes
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Meta_Box_Grid_Options.
 */
class POS_HOST_Meta_Box_Grid_Options {

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post Post object.
	 */
	public static function output( $post ) {
		global $thepostid, $grid_object;

		$thepostid   = $post->ID;
		$grid_object = pos_host_get_grid( $thepostid );

		wp_nonce_field( 'pos_host_save_options', 'pos_host_meta_nonce' );

		include 'views/html-grid-options-panel.php';
	}

	/**
	 * Save meta box data.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public static function save( $post_id, $post ) {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'update-post_' . $post_id ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce-pos-host' ) );
		}

		$grid = new POS_HOST_Grid( $post_id );

		// Generate a unique post slug.
		$slug = wp_unique_post_slug( sanitize_title( $post->post_title ), $post_id, $post->post_status, $post->post_type, $post->post_parent );

		/*
		 * At this point, the post_title has already been saved by wp_insert_post().
		 */

		$grid->set_props(
			array(
				'slug'    => $slug,
				'sort_by' => isset( $_POST['sort_by'] ) ? wc_clean( wp_unslash( $_POST['sort_by'] ) ) : 'name',
			)
		);

		$grid->save();

		do_action( 'pos_host_grid_options_save', $post_id, $grid );
	}
}
