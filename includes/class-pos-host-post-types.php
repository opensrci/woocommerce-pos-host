<?php
/**
 * Post Types
 *
 * Registers post types and taxonomies.
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Post_Types class.
 *
 * @since 0.0.1
 */
class POS_HOST_Post_Types {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
		add_action( 'pos_host_after_register_post_type', array( __CLASS__, 'maybe_flush_rewrite_rules' ) );
		add_action( 'pos_host_installed', array( __CLASS__, 'flush_rewrite_rules' ) );
	}

	/**
	 * Register custom post types.
	 *
	 * @since 5.0.0
	 * @return void
	 */
	public static function register_post_types() {
		if ( ! post_type_exists( 'pos_host_register' ) ) {
			register_post_type(
				'pos_host_register',
				apply_filters(
					'pos_host_register_post_type_pos_host_register',
					array(
						'labels'              => array(
							'name'                  => __( 'Registers', 'pos_host_gridwoocommerce-pos-host' ),
							'singular_name'         => __( 'Register', 'pos_host_gridwoocommerce-pos-host' ),
							'all_items'             => __( 'Registers', 'pos_host_gridwoocommerce-pos-host' ),
							'menu_name'             => _x( 'Registers', 'Admin menu name', 'pos_host_gridwoocommerce-pos-host' ),
							'add_new'               => __( 'Add New', 'pos_host_gridwoocommerce-pos-host' ),
							'add_new_item'          => __( 'Add new register', 'pos_host_gridwoocommerce-pos-host' ),
							'edit'                  => __( 'Edit', 'pos_host_gridwoocommerce-pos-host' ),
							'edit_item'             => __( 'Edit register', 'pos_host_gridwoocommerce-pos-host' ),
							'new_item'              => __( 'New register', 'pos_host_gridwoocommerce-pos-host' ),
							'view_item'             => __( 'View register', 'pos_host_gridwoocommerce-pos-host' ),
							'view_items'            => __( 'View registers', 'pos_host_gridwoocommerce-pos-host' ),
							'search_items'          => __( 'Search registers', 'pos_host_gridwoocommerce-pos-host' ),
							'not_found'             => __( 'No registers found', 'pos_host_gridwoocommerce-pos-host' ),
							'not_found_in_trash'    => __( 'No registers found in trash', 'pos_host_gridwoocommerce-pos-host' ),
							'parent'                => __( 'Parent register', 'pos_host_gridwoocommerce-pos-host' ),
							'insert_into_item'      => __( 'Insert into register', 'pos_host_gridwoocommerce-pos-host' ),
							'uploaded_to_this_item' => __( 'Uploaded to this register', 'pos_host_gridwoocommerce-pos-host' ),
							'filter_items_list'     => __( 'Filter registers', 'pos_host_gridwoocommerce-pos-host' ),
							'items_list_navigation' => __( 'Registers navigation', 'pos_host_gridwoocommerce-pos-host' ),
							'items_list'            => __( 'Registers list', 'pos_host_gridwoocommerce-pos-host' ),
						),
						'description'         => __( 'This is where you can add new registers to the Point of Sale.', 'pos_host_gridwoocommerce-pos-host' ),
						'public'              => false,
						'show_ui'             => true,
						'capability_type'     => 'shop_order',
						'map_meta_cap'        => true,
						'publicly_queryable'  => false,
						'exclude_from_search' => true,
						'hierarchical'        => false,
						'query_var'           => true,
						'supports'            => array( 'title' ),
						'has_archive'         => false,
						'show_in_nav_menus'   => true,
						'show_in_menu'        => 'pos-host',
						'show_in_rest'        => false,
					)
				)
			);
		}

		if ( ! post_type_exists( 'pos_host_outlet' ) ) {
			register_post_type(
				'pos_host_outlet',
				apply_filters(
					'pos_host_register_post_type_pos_host_outlet',
					array(
						'labels'              => array(
							'name'                  => __( 'Outlets', 'pos_host_gridwoocommerce-pos-host' ),
							'singular_name'         => __( 'Outlet', 'pos_host_gridwoocommerce-pos-host' ),
							'all_items'             => __( 'Outlets', 'pos_host_gridwoocommerce-pos-host' ),
							'menu_name'             => _x( 'Outlets', 'Admin menu name', 'pos_host_gridwoocommerce-pos-host' ),
							'add_new'               => __( 'Add New', 'pos_host_gridwoocommerce-pos-host' ),
							'add_new_item'          => __( 'Add new outlet', 'pos_host_gridwoocommerce-pos-host' ),
							'edit'                  => __( 'Edit', 'pos_host_gridwoocommerce-pos-host' ),
							'edit_item'             => __( 'Edit outlet', 'pos_host_gridwoocommerce-pos-host' ),
							'new_item'              => __( 'New outlet', 'pos_host_gridwoocommerce-pos-host' ),
							'view_item'             => __( 'View outlet', 'pos_host_gridwoocommerce-pos-host' ),
							'view_items'            => __( 'View outlets', 'pos_host_gridwoocommerce-pos-host' ),
							'search_items'          => __( 'Search outlets', 'pos_host_gridwoocommerce-pos-host' ),
							'not_found'             => __( 'No outlets found', 'pos_host_gridwoocommerce-pos-host' ),
							'not_found_in_trash'    => __( 'No outlets found in trash', 'pos_host_gridwoocommerce-pos-host' ),
							'parent'                => __( 'Parent outlet', 'pos_host_gridwoocommerce-pos-host' ),
							'featured_image'        => __( 'Outlet image', 'pos_host_gridwoocommerce-pos-host' ),
							'set_featured_image'    => __( 'Set outlet image', 'pos_host_gridwoocommerce-pos-host' ),
							'remove_featured_image' => __( 'Remove outlet image', 'pos_host_gridwoocommerce-pos-host' ),
							'use_featured_image'    => __( 'Use as outlet image', 'pos_host_gridwoocommerce-pos-host' ),
							'insert_into_item'      => __( 'Insert into outlet', 'pos_host_gridwoocommerce-pos-host' ),
							'uploaded_to_this_item' => __( 'Uploaded to this outlet', 'pos_host_gridwoocommerce-pos-host' ),
							'filter_items_list'     => __( 'Filter outlets', 'pos_host_gridwoocommerce-pos-host' ),
							'items_list_navigation' => __( 'Outlets navigation', 'pos_host_gridwoocommerce-pos-host' ),
							'items_list'            => __( 'Outlets list', 'pos_host_gridwoocommerce-pos-host' ),
						),
						'description'         => __( 'This is where you can add new outlets to the Point of Sale.', 'pos_host_gridwoocommerce-pos-host' ),
						'public'              => false,
						'show_ui'             => true,
						'capability_type'     => 'shop_order',
						'map_meta_cap'        => true,
						'publicly_queryable'  => false,
						'exclude_from_search' => true,
						'hierarchical'        => false,
						'query_var'           => true,
						'supports'            => array( 'title' ),
						'has_archive'         => false,
						'show_in_nav_menus'   => true,
						'show_in_menu'        => 'pos-host',
						'show_in_rest'        => false,
					)
				)
			);
		}

		if ( ! post_type_exists( 'pos_host_grid' ) ) {
			register_post_type(
				'pos_host_grid',
				apply_filters(
					'pos_host_register_post_type_pos_host_grid',
					array(
						'labels'              => array(
							'name'                  => __( 'Grids', 'pos_host_gridwoocommerce-pos-host' ),
							'singular_name'         => __( 'Grid', 'pos_host_gridwoocommerce-pos-host' ),
							'all_items'             => __( 'Grids', 'pos_host_gridwoocommerce-pos-host' ),
							'menu_name'             => _x( 'Grids', 'Admin menu name', 'pos_host_gridwoocommerce-pos-host' ),
							'add_new'               => __( 'Add New', 'pos_host_gridwoocommerce-pos-host' ),
							'add_new_item'          => __( 'Add new grid', 'pos_host_gridwoocommerce-pos-host' ),
							'edit'                  => __( 'Edit', 'pos_host_gridwoocommerce-pos-host' ),
							'edit_item'             => __( 'Edit grid', 'pos_host_gridwoocommerce-pos-host' ),
							'new_item'              => __( 'New grid', 'pos_host_gridwoocommerce-pos-host' ),
							'view_item'             => __( 'View grid', 'pos_host_gridwoocommerce-pos-host' ),
							'view_items'            => __( 'View grids', 'pos_host_gridwoocommerce-pos-host' ),
							'search_items'          => __( 'Search grids', 'pos_host_gridwoocommerce-pos-host' ),
							'not_found'             => __( 'No grids found', 'pos_host_gridwoocommerce-pos-host' ),
							'not_found_in_trash'    => __( 'No grids found in trash', 'pos_host_gridwoocommerce-pos-host' ),
							'parent'                => __( 'Parent grid', 'pos_host_gridwoocommerce-pos-host' ),
							'featured_image'        => __( 'Grid image', 'pos_host_gridwoocommerce-pos-host' ),
							'set_featured_image'    => __( 'Set grid image', 'pos_host_gridwoocommerce-pos-host' ),
							'remove_featured_image' => __( 'Remove grid image', 'pos_host_gridwoocommerce-pos-host' ),
							'use_featured_image'    => __( 'Use as grid image', 'pos_host_gridwoocommerce-pos-host' ),
							'insert_into_item'      => __( 'Insert into grid', 'pos_host_gridwoocommerce-pos-host' ),
							'uploaded_to_this_item' => __( 'Uploaded to this grid', 'pos_host_gridwoocommerce-pos-host' ),
							'filter_items_list'     => __( 'Filter grids', 'pos_host_gridwoocommerce-pos-host' ),
							'items_list_navigation' => __( 'Grids navigation', 'pos_host_gridwoocommerce-pos-host' ),
							'items_list'            => __( 'Grids list', 'pos_host_gridwoocommerce-pos-host' ),
						),
						'description'         => __( 'This is where you can add new grids to the Point of Sale.', 'pos_host_gridwoocommerce-pos-host' ),
						'public'              => false,
						'show_ui'             => true,
						'capability_type'     => 'shop_order',
						'map_meta_cap'        => true,
						'publicly_queryable'  => false,
						'exclude_from_search' => true,
						'hierarchical'        => false,
						'query_var'           => true,
						'supports'            => array( 'title' ),
						'has_archive'         => false,
						'show_in_nav_menus'   => true,
						'show_in_menu'        => 'pos-host',
						'show_in_rest'        => false,
					)
				)
			);
		}

		if ( ! post_type_exists( 'pos_receipt' ) ) {
			register_post_type(
				'pos_receipt',
				apply_filters(
					'pos_host_register_post_type_pos_receipt',
					array(
						'labels'              => array(
							'name'                  => __( 'Receipts', 'pos_host_gridwoocommerce-pos-host' ),
							'singular_name'         => __( 'Receipt', 'pos_host_gridwoocommerce-pos-host' ),
							'all_items'             => __( 'Receipts', 'pos_host_gridwoocommerce-pos-host' ),
							'menu_name'             => _x( 'Receipts', 'Admin menu name', 'pos_host_gridwoocommerce-pos-host' ),
							'add_new'               => __( 'Add New', 'pos_host_gridwoocommerce-pos-host' ),
							'add_new_item'          => __( 'Add new receipt', 'pos_host_gridwoocommerce-pos-host' ),
							'edit'                  => __( 'Edit', 'pos_host_gridwoocommerce-pos-host' ),
							'edit_item'             => __( 'Edit receipt', 'pos_host_gridwoocommerce-pos-host' ),
							'new_item'              => __( 'New receipt', 'pos_host_gridwoocommerce-pos-host' ),
							'view_item'             => __( 'View receipt', 'pos_host_gridwoocommerce-pos-host' ),
							'view_items'            => __( 'View receipts', 'pos_host_gridwoocommerce-pos-host' ),
							'search_items'          => __( 'Search receipts', 'pos_host_gridwoocommerce-pos-host' ),
							'not_found'             => __( 'No receipts found', 'pos_host_gridwoocommerce-pos-host' ),
							'not_found_in_trash'    => __( 'No receipts found in trash', 'pos_host_gridwoocommerce-pos-host' ),
							'parent'                => __( 'Parent receipt', 'pos_host_gridwoocommerce-pos-host' ),
							'featured_image'        => __( 'Receipt image', 'pos_host_gridwoocommerce-pos-host' ),
							'set_featured_image'    => __( 'Set receipt image', 'pos_host_gridwoocommerce-pos-host' ),
							'remove_featured_image' => __( 'Remove receipt image', 'pos_host_gridwoocommerce-pos-host' ),
							'use_featured_image'    => __( 'Use as receipt image', 'pos_host_gridwoocommerce-pos-host' ),
							'insert_into_item'      => __( 'Insert into receipt', 'pos_host_gridwoocommerce-pos-host' ),
							'uploaded_to_this_item' => __( 'Uploaded to this receipt', 'pos_host_gridwoocommerce-pos-host' ),
							'filter_items_list'     => __( 'Filter receipts', 'pos_host_gridwoocommerce-pos-host' ),
							'items_list_navigation' => __( 'Receipts navigation', 'pos_host_gridwoocommerce-pos-host' ),
							'items_list'            => __( 'Receipts list', 'pos_host_gridwoocommerce-pos-host' ),
						),
						'description'         => __( 'This is where you can add new grids to the Point of Sale.', 'pos_host_gridwoocommerce-pos-host' ),
						'public'              => false,
						'show_ui'             => true,
						'capability_type'     => 'shop_order',
						'map_meta_cap'        => true,
						'publicly_queryable'  => false,
						'exclude_from_search' => true,
						'hierarchical'        => false,
						'query_var'           => true,
						'supports'            => array( 'title' ),
						'has_archive'         => false,
						'show_in_nav_menus'   => true,
						'show_in_menu'        => 'pos-host',
						'show_in_rest'        => false,
					)
				)
			);
		}

		if ( ! post_type_exists( 'pos_session' ) ) {
			register_post_type(
				'pos_session',
				apply_filters(
					'pos_host_register_post_type_pos_session',
					array(
						'labels'              => array(
							'name'                  => __( 'Sessions', 'pos_host_gridwoocommerce-pos-host' ),
							'singular_name'         => __( 'Session', 'pos_host_gridwoocommerce-pos-host' ),
							'all_items'             => __( 'Sessions', 'pos_host_gridwoocommerce-pos-host' ),
							'menu_name'             => _x( 'Sessions', 'Admin menu name', 'pos_host_gridwoocommerce-pos-host' ),
							'add_new'               => __( 'Add New', 'pos_host_gridwoocommerce-pos-host' ),
							'add_new_item'          => __( 'Add new session', 'pos_host_gridwoocommerce-pos-host' ),
							'edit'                  => __( 'Edit', 'pos_host_gridwoocommerce-pos-host' ),
							'edit_item'             => __( 'Edit session', 'pos_host_gridwoocommerce-pos-host' ),
							'new_item'              => __( 'New session', 'pos_host_gridwoocommerce-pos-host' ),
							'view_item'             => __( 'View session', 'pos_host_gridwoocommerce-pos-host' ),
							'view_items'            => __( 'View sessions', 'pos_host_gridwoocommerce-pos-host' ),
							'search_items'          => __( 'Search sessions', 'pos_host_gridwoocommerce-pos-host' ),
							'not_found'             => __( 'No sessions found', 'pos_host_gridwoocommerce-pos-host' ),
							'not_found_in_trash'    => __( 'No sessions found in trash', 'pos_host_gridwoocommerce-pos-host' ),
							'parent'                => __( 'Parent session', 'pos_host_gridwoocommerce-pos-host' ),
							'featured_image'        => __( 'Session image', 'pos_host_gridwoocommerce-pos-host' ),
							'set_featured_image'    => __( 'Set session image', 'pos_host_gridwoocommerce-pos-host' ),
							'remove_featured_image' => __( 'Remove session image', 'pos_host_gridwoocommerce-pos-host' ),
							'use_featured_image'    => __( 'Use as session image', 'pos_host_gridwoocommerce-pos-host' ),
							'insert_into_item'      => __( 'Insert into session', 'pos_host_gridwoocommerce-pos-host' ),
							'uploaded_to_this_item' => __( 'Uploaded to this session', 'pos_host_gridwoocommerce-pos-host' ),
							'filter_items_list'     => __( 'Filter sessions', 'pos_host_gridwoocommerce-pos-host' ),
							'items_list_navigation' => __( 'Sessions navigation', 'pos_host_gridwoocommerce-pos-host' ),
							'items_list'            => __( 'Sessions list', 'pos_host_gridwoocommerce-pos-host' ),
						),
						'description'         => __( 'This is where you can add new sessions to the Point of Sale.', 'pos_host_gridwoocommerce-pos-host' ),
						'public'              => false,
						'show_ui'             => true,
						'capability_type'     => 'shop_order',
						'map_meta_cap'        => true,
						'publicly_queryable'  => false,
						'exclude_from_search' => true,
						'hierarchical'        => false,
						'query_var'           => true,
						'supports'            => array(),
						'has_archive'         => false,
						'show_in_nav_menus'   => false,
						'show_in_menu'        => false,
						'show_in_rest'        => false,
					)
				)
			);
		}

		if ( ! post_type_exists( 'pos_temp_order' ) ) {
			wc_register_order_type(
				'pos_temp_order',
				apply_filters(
					'pos_host_register_post_type_pos_temp_order',
					array(
						'capability_type'                  => 'shop_order',
						'public'                           => false,
						'hierarchical'                     => false,
						'supports'                         => false,
						'exclude_from_orders_screen'       => false,
						'add_order_meta_boxes'             => false,
						'exclude_from_order_count'         => true,
						'exclude_from_order_views'         => true,
						'exclude_from_order_reports'       => true,
						'exclude_from_order_sales_reports' => true,
					)
				)
			);
		}

		do_action( 'pos_host_after_register_post_type' );
	}

	/**
	 * Flush rules if the event is queued.
	 *
	 * @since 5.0.0
	 */
	public static function maybe_flush_rewrite_rules() {
		if ( 'yes' === get_option( 'woocommerce_queue_flush_rewrite_rules' ) ) {
			update_option( 'woocommerce_queue_flush_rewrite_rules', 'no' );
			self::flush_rewrite_rules();
		}
	}

	/**
	 * Flush rewrite rules.
	 */
	public static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}
}

POS_HOST_Post_types::init();
