<?php
/**
 * Outlet Functions
 *
 * @since 0.0.1
 *
 * @package WooCommerce_pos_host/Functions
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get outlet.
 *
 * @since 0.0.1
 *
 * @param int|string|POS_HOST_Outlet $outlet Outlet ID, slug or object.
 *
 * @throws Exception If outlet cannot be read/found and $data parameter of POS_HOST_Outlet class constructor is set.
 * @return POS_HOST_Outlet|null
 */
function pos_host_get_outlet( $outlet ) {
	$outlet_object = new POS_HOST_Outlet( $outlet );

	// If getting the default outlet and it does not exist, create a new one and return it.
	if ( pos_host_is_default_outlet( $outlet ) && ! $outlet_object->get_id() ) {
		delete_option( 'pos_host_default_outlet' );
		POS_HOST_Install::create_default_posts();

		return pos_host_get_outlet( (int) get_option( 'pos_host_default_outlet' ) );
	}

	return 0 !== $outlet_object->get_id() ? $outlet_object : null;
}

/**
 * Check if a specific outlet is the default one.
 *
 * @since 0.0.1
 *
 * @param int $outlet_id Receipt ID.
 * @return bool
 */
function pos_host_is_default_outlet( $outlet_id ) {
	return (int) get_option( 'pos_host_default_outlet', 0 ) === $outlet_id;
}

/**
 * Get all outlets 
 * .
 *
 * @since 0.0.1
 * @return array
 */
function pos_host_get_outlets() {
	$get_posts = get_posts(
		array(
			'numberposts' => -1,
			'post_type'   => 'pos_host_outlet',
			'orderby'     => 'post_name',
			'order'       => 'asc',
		)
	);
	$outlets  = array();
        for ($i=0;$i<count($get_posts);$i++){
		$outlets[$i]['id'] =  $get_posts[$i]->ID;
		$outlets[$i]['name'] =  $get_posts[$i]->post_title;            
        }

	return $outlets;
}
