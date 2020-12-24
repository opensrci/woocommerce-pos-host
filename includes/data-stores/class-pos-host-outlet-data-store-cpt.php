<?php
/**
 * Outlet Data Store CPT
 *
 * @since 5.0.0
 *
 * @package WooCommerce_pos_host/Classes/Data_Stores
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Outlet_Data_Store_CPT.
 *
 * Stores the outlet data in a custom post type.
 */
class POS_HOST_Outlet_Data_Store_CPT extends POS_HOST_Data_Store_WP implements WC_Object_Data_Store_Interface {

	/**
	 * Data stored in meta keys, but not considered "meta" for an outlet.
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array();

	/**
	 * Internal meta type used to store outlet data.
	 *
	 * @var string
	 */
	protected $meta_type = 'post';

	/**
	 * Method to create a new outlet in the database.
	 *
	 * @param POS_HOST_Outlet $outlet Outlet object.
	 */
	public function create( &$outlet ) {
		$outlet->set_date_created( time() );

		$outlet_id = wp_insert_post(
			apply_filters(
				'pos_host_new_outlet_data',
				array(
					'post_type'     => 'pos_host_outlet',
					'post_status'   => 'publish',
					'post_author'   => get_current_user_id(),
					'post_title'    => $outlet->get_name( 'edit' ),
					'post_content'  => '',
					'post_excerpt'  => '',
					'post_date'     => gmdate( 'Y-m-d H:i:s', $outlet->get_date_created()->getOffsetTimestamp() ),
					'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $outlet->get_date_created()->getTimestamp() ),
				)
			),
			true
		);

		if ( $outlet_id ) {
			$outlet->set_id( $outlet_id );
			$this->update_post_meta( $outlet );
			$outlet->save_meta_data();
			$outlet->apply_changes();
			delete_transient( 'rest_api_pos_host_outlets_type_count' );
			do_action( 'pos_host_new_pos_host_outlet', $outlet_id, $outlet );
		}
	}

	/**
	 * Method to read an outlet.
	 *
	 * @param POS_HOST_Outlet $outlet Outlet object.
	 *
	 * @throws Exception If invalid outlet.
	 */
	public function read( &$outlet ) {
		$outlet->set_defaults();

		$post_object = get_post( $outlet->get_id() );

		if ( ! $outlet->get_id() || ! $post_object || 'pos_host_outlet' !== $post_object->post_type ) {
			throw new Exception( __( 'Invalid outlet.', 'woocommerce-pos-host' ) );
		}

		$outlet_id = $outlet->get_id();
		$outlet->set_props(
			array(
				'name'            => $post_object->post_title,
				'slug'            => $post_object->post_name,
				'date_created'    => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
				'date_modified'   => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
				'address_1'       => get_post_meta( $outlet_id, 'address_1', true ),
				'address_2'       => get_post_meta( $outlet_id, 'address_2', true ),
				'city'            => get_post_meta( $outlet_id, 'city', true ),
				'postcode'        => get_post_meta( $outlet_id, 'postcode', true ),
				'country'         => get_post_meta( $outlet_id, 'country', true ),
				'state'           => get_post_meta( $outlet_id, 'state', true ),
				'email'           => get_post_meta( $outlet_id, 'email', true ),
				'phone'           => get_post_meta( $outlet_id, 'phone', true ),
				'fax'             => get_post_meta( $outlet_id, 'fax', true ),
				'website'         => get_post_meta( $outlet_id, 'website', true ),
				'wifi_network'    => get_post_meta( $outlet_id, 'wifi_network', true ),
				'wifi_password'   => get_post_meta( $outlet_id, 'wifi_password', true ),
				'social_accounts' => array_filter( (array) get_post_meta( $outlet_id, 'social_accounts', true ) ),

			)
		);
		$outlet->read_meta_data();
		$outlet->set_object_read( true );
		do_action( 'pos_host_outlet_loaded', $outlet );
	}

	/**
	 * Updates an outlet in the database.
	 *
	 * @param POS_HOST_Outlet $outlet Outlet object.
	 */
	public function update( &$outlet ) {
		$outlet->save_meta_data();
		$changes = $outlet->get_changes();

		if ( array_intersect( array( 'name', 'date_created', 'date_modified' ), array_keys( $changes ) ) ) {
			$post_data = array(
				'post_title'        => $outlet->get_name( 'edit' ),
				'post_excerpt'      => '',
				'post_date'         => gmdate( 'Y-m-d H:i:s', $outlet->get_date_created( 'edit' )->getOffsetTimestamp() ),
				'post_date_gmt'     => gmdate( 'Y-m-d H:i:s', $outlet->get_date_created( 'edit' )->getTimestamp() ),
				'post_modified'     => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $outlet->get_date_modified( 'edit' )->getOffsetTimestamp() ) : current_time( 'mysql' ),
				'post_modified_gmt' => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $outlet->get_date_modified( 'edit' )->getTimestamp() ) : current_time( 'mysql', 1 ),
			);

			/**
			 * When updating this object, to prevent infinite loops, use $wpdb
			 * to update data, since wp_update_post spawns more calls to the
			 * save_post action.
			 *
			 * This ensures hooks are fired by either WP itself (admin screen save),
			 * or an update purely from CRUD.
			 */
			if ( doing_action( 'save_post' ) ) {
				$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $outlet->get_id() ) );
				clean_post_cache( $outlet->get_id() );
			} else {
				wp_update_post( array_merge( array( 'ID' => $outlet->get_id() ), $post_data ) );
			}
			$outlet->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
		}
		$this->update_post_meta( $outlet );
		$outlet->apply_changes();
		delete_transient( 'rest_api_pos_host_outlets_type_count' );
		do_action( 'pos_host_update_outlet', $outlet->get_id(), $outlet );
	}

	/**
	 * Deletes an outlet from the database.
	 *
	 * @param POS_HOST_Outlet $outlet Outlet object.
	 * @param array         $args Array of args to pass to the delete method.
	 */
	public function delete( &$outlet, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'force_delete' => false,
			)
		);

		$id = $outlet->get_id();

		if ( ! $id ) {
			return;
		}

		if ( $args['force_delete'] ) {
			wp_delete_post( $id );

			wp_cache_delete( WC_Cache_Helper::get_cache_prefix( 'outlets' ) . 'outlet_id_from_code_' . $outlet->get_code(), 'outlets' );

			$outlet->set_id( 0 );
			do_action( 'pos_host_delete_outlet', $id );
		} else {
			wp_trash_post( $id );
			do_action( 'pos_host_trash_outlet', $id );
		}
	}

	/**
	 * Helper method that updates all the post meta for an outlet based on it's settings in the POS_HOST_Outlet class.
	 *
	 * @param POS_HOST_Outlet $outlet Outlet object.
	 */
	private function update_post_meta( &$outlet ) {
		$updated_props     = array();
		$meta_key_to_props = array(
			'address_1'       => 'address_1',
			'address_2'       => 'address_2',
			'city'            => 'city',
			'postcode'        => 'postcode',
			'country'         => 'country',
			'state'           => 'state',
			'email'           => 'email',
			'phone'           => 'phone',
			'fax'             => 'fax',
			'website'         => 'website',
			'wifi_network'    => 'wifi_network',
			'wifi_password'   => 'wifi_password',
			'social_accounts' => 'social_accounts',
		);

		$props_to_update = $this->get_props_to_update( $outlet, $meta_key_to_props );
		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $outlet->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;

			$updated = $this->update_or_delete_post_meta( $outlet, $meta_key, $value );

			if ( $updated ) {
				$this->updated_props[] = $prop;
			}
		}

		do_action( 'pos_host_outlet_object_updated_props', $outlet, $updated_props );
	}
}
