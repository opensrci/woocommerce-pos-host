<?php
/**
 * Register Functions
 *
 * @since 0.0.1
 *
 * @package WooCommerce_pos_host/Functions
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get register.
 *
 * @since 0.0.1
 *
 * @param int|string|POS_HOST_Register $register Register ID, slug or object.
 *
 * @throws Exception If register cannot be read/found and $data parameter of POS_HOST_Register class constructor is set.
 * @return POS_HOST_Register|null
 */
function pos_host_get_register( $register ) {
	$register_object = new POS_HOST_Register( $register );

	// If getting the default register and it does not exist, create a new one and return it.
	if ( pos_host_is_default_register( $register ) && ! $register_object->get_id() ) {
		delete_option( 'pos_host_default_register' );
		POS_HOST_Install::create_default_posts();

		return pos_host_get_register( (int) get_option( 'pos_host_default_register' ) );
	}

	return 0 !== $register_object->get_id() ? $register_object : null;
}


/**
 * Get register grid options.
 *
 * @since 0.0.1
 * @return array
 */
function pos_host_get_register_grid_options() {
	$get_posts = get_posts(
		array(
			'numberposts' => -1,
			'post_type'   => 'pos_host_grid',
			'orderby'     => 'post_name',
			'order'       => 'asc',
		)
	);
	$grids     = array(
		0 => __( 'Categories Layout', 'woocommerce-pos-host' ),
	);

	foreach ( $get_posts as $post ) {
		$grids[ $post->ID ] = $post->post_title;
	}

	return $grids;
}

/**
 * Get all registers 
 * .
 *
 * @since 0.0.1
 * @return array
 */
function pos_host_get_registers() {
	$get_posts = get_posts(
		array(
			'numberposts' => -1,
			'post_type'   => 'pos_host_register',
			'orderby'     => 'post_name',
			'order'       => 'asc',
		)
	);
	$registers  = array();
        for ($i=0;$i<count($get_posts);$i++){
		$registers[$i]['id'] =  $get_posts[$i]->ID;
		$registers[$i]['name'] =  $get_posts[$i]->post_title;            
        }

	return $registers;
}

/**
 * Get register receipt options.
 *
 * @since 0.0.1
 * @return array
 */
function pos_host_get_register_receipt_options() {
	$get_posts = get_posts(
		array(
			'numberposts' => -1,
			'post_type'   => 'pos_host_receipt',
			'orderby'     => 'post_name',
			'order'       => 'asc',
		)
	);
	$receipts  = array();

	foreach ( $get_posts as $post ) {
		$receipts[ $post->ID ] = $post->post_title;
	}

	return $receipts;
}

/**
 * Get register outlet options.
 *
 * @since 0.0.1
 * @return array
 */
function pos_host_get_register_outlet_options() {
	$get_posts = get_posts(
		array(
			'post_type'   => 'pos_host_outlet',
			'numberposts' => -1,
			'orderby'     => 'post_name',
			'order'       => 'asc',
		)
	);
	$outlets   = array();

	foreach ( $get_posts as $post ) {
		$outlets[ $post->ID ] = $post->post_title;
	}

	return $outlets;
}

/**
 * Check if are register is locked by some user.
 *
 * @since 0.0.1
 *
 * @param int $register_id Register ID.
 * @return int|bool The ID of the user whom the register is locked by or false if not locked.
 */
function pos_host_is_register_locked( $register_id ) {
	$register = pos_host_get_register( $register_id );

	if ( ! $register ) {
		return false;
	}

	$open_last   = $register->get_open_last();
	$date_opened = $register->get_date_opened();
	$date_closed = $register->get_date_closed();

	if ( strtotime( $date_opened ) >= strtotime( $date_closed ) && get_current_user_id() !== $open_last ) {
		return $open_last;
	}

	return false;
}

/**
 * Check if are register is open at the present time.
 *
 * @since 0.0.1
 *
 * @param int $register_id Register ID.
 * @return bool
 */
function pos_host_is_register_open( $register_id ) {
	$register = pos_host_get_register( $register_id );

	if ( ! $register ) {
		return false;
	}

	$date_opened = $register->get_date_opened();
	$date_closed = $register->get_date_closed();

	if (
		$date_opened &&
		( ! $date_closed || ( $date_opened->getTimestamp() > $date_closed->getTimestamp() ) )
	) {
		return true;
	}

	return false;
}

/**
 * Check if the current user can open a specific register.
 *
 * @since 0.0.1
 *
 * @param int $register_id Register ID.
 * @return bool
 */
function pos_host_current_user_can_open_register( $register_id ) {
	if ( ! current_user_can( 'view_register' ) ) {
		return false;
	}

	$register = pos_host_get_register( $register_id );

	if ( ! $register ) {
		return false;
	}

	$user_outlets = (array) get_user_meta( get_current_user_id(), 'pos_host_assigned_outlets', true );
	$user_outlets = array_map(
		function( $id ) {
				return intval( $id );
		},
		$user_outlets
	);

	if ( in_array( $register->get_outlet(), $user_outlets, true ) ) {
		return true;
	}

	return false;
}

/**
 * Check if the current user can force logout others from opened registers.
 */
function pos_host_current_user_can_force_logout() {
	$force_logout = 'yes' === get_option( 'pos_host_force_logout', 'no' );
	$current_user = wp_get_current_user();

	if ( $current_user->has_cap( 'force_logout_register' ) && $force_logout ) {
		return true;
	}

	return false;
}

/**
 * Check if a specific register is the default one.
 *
 * @since 0.0.1
 *
 * @param int $register_id Register ID.
 * @return bool
 */
function pos_host_is_default_register( $register_id ) {
	return (int) get_option( 'pos_host_default_register', 0 ) === $register_id;
}

/**
 * Create a temporary order of the post type pos_host_temp_order.
 *
 * @param int $register_id Register ID.
 * @return int The temp order ID.
 */
function pos_host_create_temp_order( $register_id ) {
	$new_order = array(
		'post_title'  => 'Register #' . $register_id,
		'post_status' => 'publish',
		'post_author' => get_current_user_id(),
		'post_type'   => 'pos_host_temp_order',
	);

	// Insert the post into the database.
	$order_id = wp_insert_post( $new_order );

	// Update the order_id field of the register.
	$register = pos_host_get_register( $register_id );
        if( !$register_id)
            wp_die( "Reg id:".$register_id, 486);
	$register->set_temp_order( $order_id );
	$register->save();

	return $order_id;
}
