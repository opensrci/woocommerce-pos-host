<?php
/**
 * Installation-related functions and actions
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Install.
 */
class POS_HOST_Install {

        /**
	 * Init.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install' ), 6 );
                
		if ( ! defined( 'IFRAME_REQUEST' ) && POS_HOST_VERSION !== get_option( 'pos_host_db_version' ) ) {
                /*@todo DB need update */
                }
	}
        
        /* multisite install
         * @todo Deprecated function call
         * 
         *  */
	public static function new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		global $wpdb;

                if ( is_plugin_active_for_network( POS_HOST_PLUGIN_FILE ) ) {
			$old_blog = $wpdb->blogid;
			switch_to_blog( $blog_id );
			self::install();
			switch_to_blog( $old_blog );
		}
	}

	/**
	 * Installation.
	 */
	public static function install() {
		global $wpdb;
		if ( ! defined( 'POS_HOST_INSTALLING' ) ) {
			define( 'POS_HOST_INSTALLING', true );
		}

		self::create_default_posts();
		self::create_tables();
		self::create_custom_product();
		self::create_roles();
		self::update_options();
		self::update_user_meta();

		// Queue upgrades/setup wizard.
		$current_version = get_option( 'pos_host_db_version', null );

		// No versions? Then this is a new install.
		if ( is_null( $current_version ) && apply_filters( 'pos_host_enable_setup_wizard', true ) ) {
			POS_HOST_Admin_Notices::add_notice( 'install' );
			set_transient( '_pos_host_activation_redirect', 1, 30 );
			delete_transient( '_pos_host_activation_redirect' );

			/* Hide products columns.
                         * @todo: ?
                         */
			$admins = get_users( array( 'role' => 'administrator' ) );
			foreach ( $admins as $admin ) {
				update_user_meta( $admin->ID, 'manageedit-productcolumnshidden', array( 'pos_host_product_grid' ) );
			}
		}

		if ( ! is_null( $current_version ) && version_compare( $current_version, POS_HOST_VERSION , '<' ) ) {
			set_transient( '_pos_host_activation_redirect', 1, 30 );
			POS_HOST_Admin_Notices::add_notice( 'update' );
                        //@todo: shoul it continue?
		} else {
			self::update_pos_version( get_option( 'pos_host_db_version', POS_HOST_VERSION ) );
		}

		/*
		 * Deletes all expired transients. The multi-table delete syntax is used
		 * to delete the transient record from table a, and the corresponding
		 * transient_timeout record from table b.
		 *
		 * Based on code inside core's upgrade_network() function.
		 */
		$wpdb->query(
			$wpdb->prepare(
				"
				DELETE a, b FROM $wpdb->options a, $wpdb->options b
				WHERE a.option_name LIKE %s
				AND a.option_name NOT LIKE %s
				AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
				AND b.option_value < %d
				",
				$wpdb->esc_like( '_transient_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		// Trigger action.
		do_action( 'pos_host_installed' );
	}

	/**
	 * Handle updates.
	 */
        /* no support
        public static function update() {
		$current_db_version = get_option( 'pos_host_db_version' );
		foreach ( self::$db_updates as $version => $updater ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				include $updater;
				self::update_pos_version( $version );
			}
		}
		self::update_pos_version( get_option( 'pos_host_db_version' ) );
	}
        */
        
	/**
	 * Create the default posts for pos_host_register, pos_host_outlet and pos_host_register.
	 *
	 * @since 0.0.1
	 */
	public static function create_default_posts() {
		// Default receipt.
		if ( ! get_option( 'pos_host_default_receipt', false ) ) {
			$post_id = wp_insert_post(
				array(
					'post_type'   => 'pos_host_register',
					'post_status' => 'publish',
					'post_title'  => __( 'My Receipt', 'woocommerce-pos-host' ),
					'meta_input'  => array(
						'no_copies'                   => 1,
						'width'                       => 70,
						'order_date_format'           => 'm/d/Y',
						'order_time_format'           => 'g:i a',
						'cashier_name_format'         => 'display_name',
						'product_details_layout'      => 'single',
						'show_shop_name'              => 'yes',
						'show_outlet_address'         => 'yes',
						'show_outlet_contact_details' => 'yes',
						'show_order_date'             => 'yes',
						'show_product_sku'            => 'yes',
						'show_product_cost'           => 'yes',
					),
				)
			);

			if ( $post_id ) {
				update_option( 'pos_host_default_receipt', $post_id );
			}
		}

		// Default outlet.
		if ( ! get_option( 'pos_host_default_outlet', false ) ) {
			$post_id = wp_insert_post(
				array(
					'post_type'   => 'pos_host_outlet',
					'post_status' => 'publish',
					'post_title'  => __( 'My Location', 'woocommerce-pos-host' ),
					'meta_input'  => array(
						'address_1' => WC()->countries->get_base_address(),
						'address_2' => WC()->countries->get_base_address_2(),
						'city'      => WC()->countries->get_base_city(),
						'postcode'  => WC()->countries->get_base_postcode(),
						'country'   => WC()->countries->get_base_country(),
						'state'     => '*' === WC()->countries->get_base_state() ? '' : WC()->countries->get_base_state(),
					),
				)
			);

			if ( $post_id ) {
				update_option( 'pos_host_default_outlet', $post_id );

				// Assign installing user to the default outlet if they are not assigned to any.
				if ( empty( get_user_meta( get_current_user_id(), 'pos_host_assigned_outlets', true ) ) ) {
					update_user_meta( get_current_user_id(), 'pos_host_assigned_outlets', array( (string) $post_id ) );
				}
			}
		}

		// Default register.
		if ( ! get_option( 'pos_host_default_register', false ) ) {
			$post_id = wp_insert_post(
				array(
					'post_type'   => 'pos_host_register',
					'post_status' => 'publish',
					'post_title'  => __( 'Default Register', 'woocommerce-pos-host' ),
					'meta_input'  => array(
						'receipt' => (int) get_option( 'pos_host_default_receipt' ),
						'outlet'  => (int) get_option( 'pos_host_default_outlet' ),
					),
				)
			);

			if ( $post_id ) {
				update_option( 'pos_host_default_register', $post_id );
			}
		}
	}

	private static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();

		$installed_version = get_option( 'pos_host_db_version' );

		if ( POS_HOST_VERSION !== $installed_version ) {

			$collate = '';
			if ( $wpdb->has_cap( 'collation' ) ) {
				if ( ! empty( $wpdb->charset ) ) {
					$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
				}
				if ( ! empty( $wpdb->collate ) ) {
					$collate .= " COLLATE $wpdb->collate";
				}
			}

			// Initial install.
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			maybe_create_table(
				"{$wpdb->prefix}pos_host_grid_tiles",
				"CREATE TABLE {$wpdb->prefix}pos_host_grid_tiles (
					id BIGINT UNSIGNED NOT NULL auto_increment,
					type varchar(200) NOT NULL DEFAULT '',
					item_id BIGINT UNSIGNED NOT NULL,
					display_order BIGINT UNSIGNED NOT NULL,
					grid_id BIGINT UNSIGNED NOT NULL,
					PRIMARY KEY (id),
					KEY grid_id (grid_id)
				) $collate;"
			);
		}
	}

	/**
	 * Create roles and capabilities.
	 */
	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		// Dummy gettext calls to get strings in the catalog.
		/* translators: user role */
		_x( 'Register clerk', 'User role', 'woocommerce-pos-host' );
		/* translators: user role */
		_x( 'Location manager', 'User role', 'woocommerce-pos-host' );

		$register_clerk_caps = array(
			'view_register'             => true,
			'manage_product_terms'      => true,
			'read_private_products'     => true,
			'read_private_shop_orders'  => true,
			'read_private_shop_coupons' => true,
			'publish_shop_orders'       => true,
			'edit_shop_order'           => true,
			'edit_others_shop_orders'   => true,
			'list_users'                => true,
			'edit_users'                => true,
			'promote_users'             => true,
		);

		$outlet_manager_caps = array(
			'manage_woocommerce_point_of_sale' => true,
			'force_logout_register'            => true,
			'refund_orders'                    => true,
		);

		$shop_manager_caps = get_role( 'shop_manager' )->capabilities;

		$outlet_manager_caps = array_merge( $shop_manager_caps, $register_clerk_caps, $outlet_manager_caps );

		// Register clerk role.
		add_role( 'register_clerk', 'Register clerk', $register_clerk_caps );

		// Outlet manager role.
		add_role( 'outlet_manager', 'Location manager', $outlet_manager_caps );

		// Add outlet_manager caps to administrator and shop_manager.
		foreach ( $outlet_manager_caps as $cap => $status ) {
			if ( true === $status ) {
				$wp_roles->add_cap( 'administrator', $cap );
				$wp_roles->add_cap( 'shop_manager', $cap );
			}
		}
	}

	/**
	 * Update options.
	 */
	public static function update_options() {
		add_option( 'pos_host_guest_checkout', 'yes' );
	}

	/**
	 * Update WC POS version to current.
	 */
	public static function update_pos_version( $version = null ) {
		update_option( 'pos_host_db_version', is_null( $version ) ? POS_HOST_VERSION : $version );
	}

	/**
	 * Set default user meta for the installing user.
	 */
	public static function update_user_meta() {
		if ( empty( get_user_meta( get_current_user_id(), 'pos_host_enable_discount', true ) ) ) {
			update_user_meta( get_current_user_id(), 'pos_host_enable_discount', 'yes' );
		}

		if ( empty( get_user_meta( get_current_user_id(), 'pos_host_enable_tender_orders', true ) ) ) {
			update_user_meta( get_current_user_id(), 'pos_host_enable_tender_orders', 'yes' );
		}
	}

	/**
	 * Create a hidden custom product for internal use.
	 *
	 * @return void
	 */
	public static function create_custom_product() {
		// Exit if the custom product has already been created and saved.
		$custom_product_id = (int) get_option( 'pos_host_custom_product_id', null );
		if ( $custom_product_id && 'product' === get_post_type( $custom_product_id ) ) {
			return;
		}

		$custom_product = new WC_Product();
		$custom_product->set_name( 'WooCommerce pos.host Custom Product' );
		$custom_product->set_status( ' publish' );
		$custom_product->set_catalog_visibility( 'hidden' );
		$custom_product->set_price( 10 );
		$custom_product->set_regular_price( 10 );

		$custom_product_id = $custom_product->save();

		update_option( 'pos_host_custom_product_id', $custom_product_id );
	}

	public static function activate( $networkwide ) {
		self::flush_rewrite_rules();

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			// check if it is a network activation - if so, run the activation function for each blog id.
			if ( $networkwide ) {
				$old_blog = $wpdb->blogid;
				// Get all blog ids.
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::install();
				}

				switch_to_blog( $old_blog );

				return;
			}
		}
		self::install();
	}

	public static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

	/**
	 * Remove user roles.
	 */
	public static function remove_roles() {
		global $wp_roles;

		$capabilities = array(
			'view_register',
			'manage_woocommerce_point_of_sale',
			'refund_orders',
		);

		foreach ( $capabilities as $cap ) {
			$wp_roles->remove_cap( 'shop_manager', $cap );
			$wp_roles->remove_cap( 'administrator', $cap );
		}

		remove_role( 'outlet_manager' );
		remove_role( 'register_clerk' );
	}
}

POS_HOST_Install::init();
