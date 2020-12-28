<?php
/**
 * POS_HOST_Data_Store_WP
 *
 * @since 0.0.1
 *
 * @package WooCommerce_pos_host/Classes/Data_Stores
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Data_Store_WP.
 */
class POS_HOST_Data_Store_WP extends WC_Data_Store_WP {

	/**
	 *@todo future 
         */
	protected function update_or_delete_post_meta( $object, $meta_key, $meta_value ) {
		if ( in_array( $meta_value, array( array(), '' ), true ) && ! in_array( $meta_key, $this->must_exist_meta_keys, true ) ) {
			$updated = delete_post_meta( $object->get_id(), $meta_key );
		} else {
			$updated = update_post_meta( $object->get_id(), $meta_key, $meta_value );
		}

		return (bool) $updated;
	}
}
