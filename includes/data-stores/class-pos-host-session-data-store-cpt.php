<?php
/**
 * Session Data Store CPT
 *
 * @since 0.0.1
 *
 * @package WooCommerce_pos_host/Classes/Data_Stores
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Session_Data_Store_CPT.
 *
 * Stores the session data in a custom post type.
 */
class POS_HOST_Session_Data_Store_CPT extends POS_HOST_Data_Store_WP implements WC_Object_Data_Store_Interface {

	/**
	 * Data stored in meta keys, but not considered "meta" for a session.
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array(
		'_session_data',
	);

	/**
	 * Internal meta type used to store session data.
	 *
	 * @var string
	 */
	protected $meta_type = 'post';

	/**
	 * Method to create a new session in the database.
	 *
	 * @param POS_HOST_Session $session Session object.
	 */
	public function create( &$session ) {
		$session->set_date_created( time() );

		$session_id = wp_insert_post(
			apply_filters(
				'pos_host_new_session_data',
				array(
					'post_type'     => 'pos_host_session',
					'post_status'   => 'publish',
					'post_author'   => get_current_user_id(),
					'post_title'    => $session->get_name( 'edit' ),
					'post_content'  => '',
					'post_excerpt'  => '',
					'post_date'     => gmdate( 'Y-m-d H:i:s', $session->get_date_created()->getOffsetTimestamp() ),
					'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $session->get_date_created()->getTimestamp() ),
				)
			),
			true
		);

		if ( $session_id ) {
			$session->set_id( $session_id );

			$this->set_session_data( $session );
			$this->update_post_meta( $session );

			$session->save_meta_data();
			$session->apply_changes();

			delete_transient( 'rest_api_pos_host_sessions_type_count' );
			do_action( 'pos_host_new_pos_host_session', $session_id, $session );
		}
	}

	/**
	 * Method to read a session.
	 *
	 * @param POS_HOST_Session $session Session object.
	 *
	 * @throws Exception If invalid session.
	 */
	public function read( &$session ) {
		$session->set_defaults();

		$post_object = get_post( $session->get_id() );

		if ( ! $session->get_id() || ! $post_object || 'pos_host_session' !== $post_object->post_type ) {
			throw new Exception( __( 'Invalid session.', 'woocommerce-pos-host' ) );
		}

		$session_id = $session->get_id();
		$session->set_props(
			array(
				'name'               => $post_object->post_title,
				'slug'               => $post_object->post_name,
				'date_created'       => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
				'date_modified'      => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
				'date_opened'        => get_post_meta( $session_id, 'date_opened', true ),
				'date_closed'        => get_post_meta( $session_id, 'date_closed', true ),
				'open_first'         => (int) get_post_meta( $session_id, 'open_first', true ),
				'open_last'          => (int) get_post_meta( $session_id, 'open_last', true ),
				'register_id'        => (int) get_post_meta( $session_id, 'register_id', true ),
				'outlet_id'          => (int) get_post_meta( $session_id, 'outlet_id', true ),
				'opening_note'       => get_post_meta( $session_id, 'opening_note', true ),
				'closing_note'       => get_post_meta( $session_id, 'closing_note', true ),
				'opening_cash_total' => (float) get_post_meta( $session_id, 'opening_cash_total', true ),
				'counted_totals'     => (array) get_post_meta( $session_id, 'counted_totals', true ),
				'session_data'       => (array) get_post_meta( $session_id, '_session_data', true ),
			)
		);
		$session->read_meta_data();
		$session->set_object_read( true );
		do_action( 'pos_host_session_loaded', $session );
	}

	/**
	 * Updates a session in the database.
	 *
	 * @param POS_HOST_Session $session Session object.
	 */
	public function update( &$session ) {
		$session->save_meta_data();
		$changes = $session->get_changes();

		if ( array_intersect( array( 'name', 'slug', 'date_created', 'date_modified' ), array_keys( $changes ) ) ) {

			$post_data = array(
				'post_title'        => $session->get_name( 'edit' ),
				'post_name'         => $session->get_slug( 'edit' ),
				'post_excerpt'      => '',
				'post_date'         => gmdate( 'Y-m-d H:i:s', $session->get_date_created( 'edit' )->getOffsetTimestamp() ),
				'post_date_gmt'     => gmdate( 'Y-m-d H:i:s', $session->get_date_created( 'edit' )->getTimestamp() ),
				'post_modified'     => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $session->get_date_modified( 'edit' )->getOffsetTimestamp() ) : current_time( 'mysql' ),
				'post_modified_gmt' => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $session->get_date_modified( 'edit' )->getTimestamp() ) : current_time( 'mysql', 1 ),
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
				$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $session->get_id() ) );
				clean_post_cache( $session->get_id() );
			} else {
				wp_update_post( array_merge( array( 'ID' => $session->get_id() ), $post_data ) );
			}
			$session->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
		}
		$this->update_post_meta( $session );
		$session->apply_changes();
		delete_transient( 'rest_api_pos_host_sessions_type_count' );
		do_action( 'pos_host_update_session', $session->get_id(), $session );
	}

	/**
	 * Deletes a session from the database.
	 *
	 * @param POS_HOST_Session $session Session object.
	 * @param array          $args Array of args to pass to the delete method.
	 */
	public function delete( &$session, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'force_delete' => false,
			)
		);

		$id = $session->get_id();

		if ( ! $id ) {
			return;
		}

		if ( $args['force_delete'] ) {
			wp_delete_post( $id );

			wp_cache_delete( WC_Cache_Helper::get_cache_prefix( 'sessions' ) . 'session_id_from_code_' . $session->get_code(), 'sessions' );

			$session->set_id( 0 );
			do_action( 'pos_host_delete_session', $id );
		} else {
			wp_trash_post( $id );
			do_action( 'pos_host_trash_session', $id );
		}
	}

	/**
	 * Helper method that updates all the post meta for a session based on it's settings in the POS_HOST_Session class.
	 *
	 * @param POS_HOST_Session $session Session object.
	 */
	private function update_post_meta( &$session ) {
		$updated_props     = array();
		$meta_key_to_props = array(
			'date_opened'        => 'date_opened',
			'date_closed'        => 'date_closed',
			'open_first'         => 'open_first',
			'open_last'          => 'open_last',
			'register_id'        => 'register_id',
			'outlet_id'          => 'outlet_id',
			'opening_note'       => 'opening_note',
			'closing_note'       => 'closing_note',
			'opening_cash_total' => 'opening_cash_total',
			'counted_totals'     => 'counted_totals',
			'_session_data'      => 'session_data',
		);

		$props_to_update = $this->get_props_to_update( $session, $meta_key_to_props );
		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $session->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;
			switch ( $prop ) {
				case 'date_opened':
				case 'date_closed':
					$value = $value ? $value->getTimestamp() : null;
					break;
			}

			$updated = $this->update_or_delete_post_meta( $session, $meta_key, $value );

			if ( $updated ) {
				$this->updated_props[] = $prop;
			}
		}

		do_action( 'pos_host_session_object_updated_props', $session, $updated_props );
	}

	/**
	 * Sets the _session_data internal meta field.
	 *
	 * _session_data stores the meta data that can be lost if a register, an outlet
	 * or a user is deleted.
	 *
	 * @param POS_HOST_Session $session Session object.
	 */
	private function set_session_data( &$session ) {
		$register   = pos_host_get_register( $session->get_register_id() );
		$outlet     = pos_host_get_outlet( $session->get_outlet_id() );
		$open_first = get_user_by( 'id', $session->get_open_first() );
		$open_last  = get_user_by( 'id', $session->get_open_last() );

		$session_data = array(
			'register'   => $register ? $register->get_name() : '',
			'outlet'     => $outlet ? $outlet->get_name() : '',
			'open_first' => $open_first ? $open_first->display_name : '',
			'open_last'  => $open_last ? $open_last->display_name : '',
		);

		$session->set_session_data( $session_data );
	}
}
