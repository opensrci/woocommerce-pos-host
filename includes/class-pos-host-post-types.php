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
							'name'                  => __( 'Registers', 'woocommerce-pos-host' ),
							'singular_name'         => __( 'Register', 'woocommerce-pos-host' ),
							'all_items'             => __( 'Registers', 'woocommerce-pos-host' ),
							'menu_name'             => _x( 'Registers', 'Admin menu name', 'woocommerce-pos-host' ),
							'add_new'               => __( 'Add New', 'woocommerce-pos-host' ),
							'add_new_item'          => __( 'Add new register', 'woocommerce-pos-host' ),
							'edit'                  => __( 'Edit', 'woocommerce-pos-host' ),
							'edit_item'             => __( 'Edit register', 'woocommerce-pos-host' ),
							'new_item'              => __( 'New register', 'woocommerce-pos-host' ),
							'view_item'             => __( 'View register', 'woocommerce-pos-host' ),
							'view_items'            => __( 'View registers', 'woocommerce-pos-host' ),
							'search_items'          => __( 'Search registers', 'woocommerce-pos-host' ),
							'not_found'             => __( 'No registers found', 'woocommerce-pos-host' ),
							'not_found_in_trash'    => __( 'No registers found in trash', 'woocommerce-pos-host' ),
							'parent'                => __( 'Parent register', 'woocommerce-pos-host' ),
							'insert_into_item'      => __( 'Insert into register', 'woocommerce-pos-host' ),
							'uploaded_to_this_item' => __( 'Uploaded to this register', 'woocommerce-pos-host' ),
							'filter_items_list'     => __( 'Filter registers', 'woocommerce-pos-host' ),
							'items_list_navigation' => __( 'Registers navigation', 'woocommerce-pos-host' ),
							'items_list'            => __( 'Registers list', 'woocommerce-pos-host' ),
						),
						'description'         => __( 'This is where you can add new registers to the Point of Sale.', 'woocommerce-pos-host' ),
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
							'name'                  => __( 'Outlets', 'woocommerce-pos-host' ),
							'singular_name'         => __( 'Outlet', 'woocommerce-pos-host' ),
							'all_items'             => __( 'Outlets', 'woocommerce-pos-host' ),
							'menu_name'             => _x( 'Outlets', 'Admin menu name', 'woocommerce-pos-host' ),
							'add_new'               => __( 'Add New', 'woocommerce-pos-host' ),
							'add_new_item'          => __( 'Add new outlet', 'woocommerce-pos-host' ),
							'edit'                  => __( 'Edit', 'woocommerce-pos-host' ),
							'edit_item'             => __( 'Edit outlet', 'woocommerce-pos-host' ),
							'new_item'              => __( 'New outlet', 'woocommerce-pos-host' ),
							'view_item'             => __( 'View outlet', 'woocommerce-pos-host' ),
							'view_items'            => __( 'View outlets', 'woocommerce-pos-host' ),
							'search_items'          => __( 'Search outlets', 'woocommerce-pos-host' ),
							'not_found'             => __( 'No outlets found', 'woocommerce-pos-host' ),
							'not_found_in_trash'    => __( 'No outlets found in trash', 'woocommerce-pos-host' ),
							'parent'                => __( 'Parent outlet', 'woocommerce-pos-host' ),
							'featured_image'        => __( 'Outlet image', 'woocommerce-pos-host' ),
							'set_featured_image'    => __( 'Set outlet image', 'woocommerce-pos-host' ),
							'remove_featured_image' => __( 'Remove outlet image', 'woocommerce-pos-host' ),
							'use_featured_image'    => __( 'Use as outlet image', 'woocommerce-pos-host' ),
							'insert_into_item'      => __( 'Insert into outlet', 'woocommerce-pos-host' ),
							'uploaded_to_this_item' => __( 'Uploaded to this outlet', 'woocommerce-pos-host' ),
							'filter_items_list'     => __( 'Filter outlets', 'woocommerce-pos-host' ),
							'items_list_navigation' => __( 'Outlets navigation', 'woocommerce-pos-host' ),
							'items_list'            => __( 'Outlets list', 'woocommerce-pos-host' ),
						),
						'description'         => __( 'This is where you can add new outlets to the Point of Sale.', 'woocommerce-pos-host' ),
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
							'name'                  => __( 'Grids', 'woocommerce-pos-host' ),
							'singular_name'         => __( 'Grid', 'woocommerce-pos-host' ),
							'all_items'             => __( 'Grids', 'woocommerce-pos-host' ),
							'menu_name'             => _x( 'Grids', 'Admin menu name', 'woocommerce-pos-host' ),
							'add_new'               => __( 'Add New', 'woocommerce-pos-host' ),
							'add_new_item'          => __( 'Add new grid', 'woocommerce-pos-host' ),
							'edit'                  => __( 'Edit', 'woocommerce-pos-host' ),
							'edit_item'             => __( 'Edit grid', 'woocommerce-pos-host' ),
							'new_item'              => __( 'New grid', 'woocommerce-pos-host' ),
							'view_item'             => __( 'View grid', 'woocommerce-pos-host' ),
							'view_items'            => __( 'View grids', 'woocommerce-pos-host' ),
							'search_items'          => __( 'Search grids', 'woocommerce-pos-host' ),
							'not_found'             => __( 'No grids found', 'woocommerce-pos-host' ),
							'not_found_in_trash'    => __( 'No grids found in trash', 'woocommerce-pos-host' ),
							'parent'                => __( 'Parent grid', 'woocommerce-pos-host' ),
							'featured_image'        => __( 'Grid image', 'woocommerce-pos-host' ),
							'set_featured_image'    => __( 'Set grid image', 'woocommerce-pos-host' ),
							'remove_featured_image' => __( 'Remove grid image', 'woocommerce-pos-host' ),
							'use_featured_image'    => __( 'Use as grid image', 'woocommerce-pos-host' ),
							'insert_into_item'      => __( 'Insert into grid', 'woocommerce-pos-host' ),
							'uploaded_to_this_item' => __( 'Uploaded to this grid', 'woocommerce-pos-host' ),
							'filter_items_list'     => __( 'Filter grids', 'woocommerce-pos-host' ),
							'items_list_navigation' => __( 'Grids navigation', 'woocommerce-pos-host' ),
							'items_list'            => __( 'Grids list', 'woocommerce-pos-host' ),
						),
						'description'         => __( 'This is where you can add new grids to the Point of Sale.', 'woocommerce-pos-host' ),
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

		if ( ! post_type_exists( 'pos_host_receipt' ) ) {
			register_post_type(
				'pos_host_receipt',
				apply_filters(
					'pos_host_register_post_type_pos_host_receipt',
					array(
						'labels'              => array(
							'name'                  => __( 'Receipts', 'woocommerce-pos-host' ),
							'singular_name'         => __( 'Receipt', 'woocommerce-pos-host' ),
							'all_items'             => __( 'Receipts', 'woocommerce-pos-host' ),
							'menu_name'             => _x( 'Receipts', 'Admin menu name', 'woocommerce-pos-host' ),
							'add_new'               => __( 'Add New', 'woocommerce-pos-host' ),
							'add_new_item'          => __( 'Add new receipt', 'woocommerce-pos-host' ),
							'edit'                  => __( 'Edit', 'woocommerce-pos-host' ),
							'edit_item'             => __( 'Edit receipt', 'woocommerce-pos-host' ),
							'new_item'              => __( 'New receipt', 'woocommerce-pos-host' ),
							'view_item'             => __( 'View receipt', 'woocommerce-pos-host' ),
							'view_items'            => __( 'View receipts', 'woocommerce-pos-host' ),
							'search_items'          => __( 'Search receipts', 'woocommerce-pos-host' ),
							'not_found'             => __( 'No receipts found', 'woocommerce-pos-host' ),
							'not_found_in_trash'    => __( 'No receipts found in trash', 'woocommerce-pos-host' ),
							'parent'                => __( 'Parent receipt', 'woocommerce-pos-host' ),
							'featured_image'        => __( 'Receipt image', 'woocommerce-pos-host' ),
							'set_featured_image'    => __( 'Set receipt image', 'woocommerce-pos-host' ),
							'remove_featured_image' => __( 'Remove receipt image', 'woocommerce-pos-host' ),
							'use_featured_image'    => __( 'Use as receipt image', 'woocommerce-pos-host' ),
							'insert_into_item'      => __( 'Insert into receipt', 'woocommerce-pos-host' ),
							'uploaded_to_this_item' => __( 'Uploaded to this receipt', 'woocommerce-pos-host' ),
							'filter_items_list'     => __( 'Filter receipts', 'woocommerce-pos-host' ),
							'items_list_navigation' => __( 'Receipts navigation', 'woocommerce-pos-host' ),
							'items_list'            => __( 'Receipts list', 'woocommerce-pos-host' ),
						),
						'description'         => __( 'This is where you can add new grids to the Point of Sale.', 'woocommerce-pos-host' ),
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

		if ( ! post_type_exists( 'pos_host_session' ) ) {
			register_post_type(
				'pos_host_session',
				apply_filters(
					'pos_host_register_post_type_pos_host_session',
					array(
						'labels'              => array(
							'name'                  => __( 'Sessions', 'woocommerce-pos-host' ),
							'singular_name'         => __( 'Session', 'woocommerce-pos-host' ),
							'all_items'             => __( 'Sessions', 'woocommerce-pos-host' ),
							'menu_name'             => _x( 'Sessions', 'Admin menu name', 'woocommerce-pos-host' ),
							'add_new'               => __( 'Add New', 'woocommerce-pos-host' ),
							'add_new_item'          => __( 'Add new session', 'woocommerce-pos-host' ),
							'edit'                  => __( 'Edit', 'woocommerce-pos-host' ),
							'edit_item'             => __( 'Edit session', 'woocommerce-pos-host' ),
							'new_item'              => __( 'New session', 'woocommerce-pos-host' ),
							'view_item'             => __( 'View session', 'woocommerce-pos-host' ),
							'view_items'            => __( 'View sessions', 'woocommerce-pos-host' ),
							'search_items'          => __( 'Search sessions', 'woocommerce-pos-host' ),
							'not_found'             => __( 'No sessions found', 'woocommerce-pos-host' ),
							'not_found_in_trash'    => __( 'No sessions found in trash', 'woocommerce-pos-host' ),
							'parent'                => __( 'Parent session', 'woocommerce-pos-host' ),
							'featured_image'        => __( 'Session image', 'woocommerce-pos-host' ),
							'set_featured_image'    => __( 'Set session image', 'woocommerce-pos-host' ),
							'remove_featured_image' => __( 'Remove session image', 'woocommerce-pos-host' ),
							'use_featured_image'    => __( 'Use as session image', 'woocommerce-pos-host' ),
							'insert_into_item'      => __( 'Insert into session', 'woocommerce-pos-host' ),
							'uploaded_to_this_item' => __( 'Uploaded to this session', 'woocommerce-pos-host' ),
							'filter_items_list'     => __( 'Filter sessions', 'woocommerce-pos-host' ),
							'items_list_navigation' => __( 'Sessions navigation', 'woocommerce-pos-host' ),
							'items_list'            => __( 'Sessions list', 'woocommerce-pos-host' ),
						),
						'description'         => __( 'This is where you can add new sessions to the Point of Sale.', 'woocommerce-pos-host' ),
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
