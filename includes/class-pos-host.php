<?php
/**
 * The main class.
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST class.
 */
class POS_HOST {

	/**
	 * The plugin version.
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $version = '0.1.0';

	/**
	 * The single instance of POS_HOST.
	 *
	 * @var object
	 * @since 0.0.1
	 */
	private static $_instance = null;

	/**
	 * Is a register page.
	 *
	 * @var bool
	 */
	public $is_pos = null;

	/**
	 * menu slug.
	 *
	 * @var string
	 */
	public $menu_slug = 'pos-host';

	/**
	 * Store manu slug.
	 *
	 * @var string
	 */
	public $store_menu_slug = 'pos-host-store';

        /**
	 * Barcode page slug.
	 *
	 * @var string
	 */
	public $barcodes_page_slug = 'pos-host-barcode';

	/**
	 * Stock Controller page slug.
	 *
	 * @var string
	 */
	public $stock_controller_page_slug = 'pos-host-stock';

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	public $settings_page_slug = 'pos-host-settings';


	/**
	 * Users manage page slug.
	 *
	 * @var string
	 */
	public $users_page_slug = 'pos-host-users';

	/**
	 * The main POS_HOST instance.
	 *
	 * Ensures only one instance of POS_HOST is/can loaded be loaded.
	 *
	 * @since 0.0.1
	 * @see POS_HOST
	 *
	 * @return POS_HOST
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * The constructor.
	 */
	public function __construct() {
                $this->define_constants();
		$this->init_hooks();

		/**
		 * Hook: woocommerce_pos_host_loaded.
		 */

		do_action( 'woocommerce_pos_host_loaded' );
	}

	/**
	 * On plugin activation.
	 *
	 * @param bool $network_wide Whether the plugin is enabled for all sites in the network or just the current site.
	 */
	public function activate( $network_wide ) {
                include_once 'class-pos-host-install.php';
		/* No need 
                    include_once 'admin/class-pos-host-admin-notices.php';
                */
                
                global $wpdb;

		// If the plugin is being activated network wide, then run the activation code for each site.
		if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
			$current_blog = $wpdb->blogid;
			// Loop over blogs.
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				POS_HOST_Install::install();
			}

			switch_to_blog( $current_blog );
			return;
		}

		POS_HOST_Install::install();
	}

	/**
	 * On plugin deactivation.
	 */
	public function deactivate() {
		// Delete the hidden custom product as it will no longer be hidden after deactivation.
		// On re-activation a new custom product will be created.
		wp_delete_post( (int) get_option( 'pos_host_custom_product_id' ), true );
		delete_option( 'pos_host_custom_product_id' );
	}
        /**
	 * Check if the WC REST API is blocked (i.e. status code != 200).
	 */
	public function check_wc_rest_api() {
		try {
			$request     = new WP_REST_Request( 'GET', '/wc/v3' );
			$response    = rest_do_request( $request );
			$status_code = $response->get_status();
		} catch ( Exception $e ) {
			// Cannot get the status code (e.g. cURL error). Bypass the check.
			$status_code = 200;
		}

		if ( 200 !== $status_code ) {
                	return;
		}

	}
        
	/**
	 * Returns the WooCommerce API endpoint.
	 *
	 * @return string
	 */
	public function wc_api_url() {
		return get_home_url( null, 'wp-json/wc/v3/', is_ssl() ? 'https' : 'http' );
	}

	/**
	 * Returns the pos.host API endpoint.
	 *
	 * @return string
	 */
	public function pos_host_api_url() {
		return get_home_url( null, 'wp-json/pos-host/', is_ssl() ? 'https' : 'http' );
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', POS_HOST_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( POS_HOST_PLUGIN_FILE ) );
	}

	/**
	 * Returns the plugin barcode URL.
	 *
	 * @return string
	 */
	public function barcode_url() {
		return untrailingslashit( plugins_url( 'includes/vendor/barcode/image.php', POS_HOST_PLUGIN_FILE ) . '?filetype=PNG&dpi=72&scale=1&rotation=0&font_family=0&thickness=60&start=NULL&code=BCGcode128' );
	}

	/**
	 * Returns plugin menu screen ID.
	 *
	 * @return string
	 */
	public function plugin_screen_id() {
		return sanitize_title( __( 'pos-host', 'woocommerce-pos-host' ) );
	}

	/**
	 * Returns WooCommerce menu screen ID.
	 *
         * @todo : ?
	 * @return string
	 */
	public function wc_screen_id() {
		/*
		 * We cannot just use __( 'WooCommerce', 'woocommerce' ) to get the WC screen ID
		 * to avoid a PHPCS violation WordPress.WP.I18n.TextDomainMismatch.
		 */
		$wc_screen_ids = array_values(
			array_filter(
				wc_get_screen_ids(),
				function( $id ) {
					return false !== strpos( $id, '_page_wc-settings' );
				}
			)
		);

		$wc_screen_id = str_replace( '_page_wc-settings', '', $wc_screen_ids[0] );

		return $wc_screen_id;
	}

	/**
	 * Receives Heartbeat data and respond.
	 *
	 * @param array $response Heartbeat response data to pass back to front-end.
	 * @param array $data Data received from the front-end.
	 *
	 * @return array
	 */
	public function pos_host_register_status( $response, $data ) {
		if ( empty( $data['pos_host_register_id'] ) ) {
			return $response;
		}

		$is_lock = pos_host_is_register_locked( (int) $data['pos_host_register_id'] );
		if ( ! $is_lock ) {
			return $response;
		}

		$user_data = get_userdata( $is_lock )->to_array();

		$response['register_status_data'] = array(
			'ID'            => $user_data['ID'],
			'display_name'  => $user_data['display_name'],
			'user_nicename' => $user_data['user_nicename'],
		);

		return $response;
	}

	/**
	 * Defines POS_HOST Constants.
	 */
	private function define_constants() {
		$this->define( 'POS_HOST_ABSPATH', dirname( POS_HOST_PLUGIN_FILE ) );
		$this->define( 'POS_HOST_PLUGIN_BASENAME', plugin_basename( POS_HOST_PLUGIN_FILE ) );
		$this->define( 'POS_HOST_VERSION', $this->version );
		$this->define( 'POS_HOST_TOKEN', 'pos_host' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Checks the request type.
	 *
	 * @param string $type Request name.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Includes the required core files used in admin and on the front-end.
	 */
	public function includes() {
		/*
		 * Global includes.
		 */
		
                include_once 'pos-host-core-functions.php';
                include_once 'pos-host-register-functions.php';
                include_once 'pos-host-receipt-functions.php';
                include_once 'pos-host-outlet-functions.php';
                include_once 'pos-host-grid-functions.php';
                include_once 'pos-host-session-functions.php';
                include_once 'class-pos-host-post-types.php';
                include_once 'class-pos-host-emails.php';
                include_once 'class-pos-host-autoloader.php';
                include_once 'class-pos-host-stocks.php';
                include_once 'class-pos-host-my-account.php';
                include_once 'admin/class-pos-host-admin-post-types.php';
                include_once 'admin/class-pos-host-admin.php';
                include_once 'admin/class-pos-host-admin-assets.php';
            
		// On the front-end.
		if ( ! is_admin() ) {
			include_once 'class-pos-host-sell.php';

			if ( 'yes' === get_option( 'pos_host_enable_frontend_access', 'no' ) ) {
				include_once 'class-pos-host-my-account.php';
			}
		}

		// On Ajax requests.
		if ( defined( 'DOING_AJAX' ) ) {
			include_once 'class-pos-host-ajax.php';
		}
	}

	/**
	 * Hooks.
	 */
	public function init_hooks() {
            
                register_activation_hook( POS_HOST_PLUGIN_FILE, array( $this, 'activate' ) );
                register_deactivation_hook( POS_HOST_PLUGIN_FILE, array( $this, 'deactivate' ) );
                add_action( 'init', array( $this, 'visibility' ) );
                add_action( 'woocommerce_loaded', array( $this, 'includes' ) );
                add_action( 'admin_init', array( $this, 'force_country_display' ) );
                add_action( 'admin_init', array( $this, 'user_manage' ), 2 );
                /*
                 * add_action( 'admin_init', array( $this, 'store_manage' ), 2 );
                 */
                add_action( 'admin_init', array( $this, 'print_report' ), 100 );
                add_action( 'admin_notices', array( $this, 'check_wc_rest_api' ) );

                add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 0 );
                add_action( 'woocommerce_hidden_order_itemmeta', array( $this, 'hidden_order_itemmeta' ), 150, 1 );
                add_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'order_received_url' ) );
                add_filter( 'woocommerce_email_actions', array( $this, 'woocommerce_email_actions' ), 150, 1 );
                add_filter( 'woocommerce_admin_order_actions', array( $this, 'order_actions_reprint_receipts' ), 2, 20 );
                add_action( 'woocommerce_loaded', array( $this, 'woocommerce_delete_shop_order_transients' ) );
                add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ), 10, 1 );
                add_action( 'pre_get_posts', array( $this, 'hide_pos_custom_product' ), 99, 1 );
                add_filter( 'request', array( $this, 'orders_by_order_type' ) );
                add_filter( 'pos_host_discount_presets', array( $this, 'add_custom_discounts' ) );
                add_filter( 'woocommerce_data_stores', array( $this, 'register_data_stores' ), 10, 1 );
                add_filter( 'woocommerce_order_number', array( $this, 'add_prefix_suffix_order_number' ), 99, 2 );

                add_filter( 'rest_product_collection_params', array( $this, 'per_page_limits' ), 9999, 2 );
                add_filter( 'rest_shop_order_collection_params', array( $this, 'per_page_limits' ), 9999, 2 );
                add_filter( 'rest_shop_coupon_collection_params', array( $this, 'per_page_limits' ), 9999, 2 );
                add_action( 'woocommerce_loaded', array( $this, 'manage_floatval_quantity' ) );
		// For compatibility with WooCommerce Subscriptions.
		if ( in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', get_option( 'active_plugins' ), true ) ) {
			add_filter( 'woocommerce_subscription_payment_method_to_display', array( $this, 'get_subscription_payment_method' ), 10, 2 );
		}
		// If product visiblity is enabled.
		if ( 'yes' === get_option( 'pos_host_visibility', 'no' ) ) {
			add_action( 'quick_edit_custom_box', array( $this, 'quick_edit' ), 10, 2 );
			add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'bulk_edit' ), 10, 0 );
			add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'save_bulk_visibility' ), 15, 1 );
			add_action( 'woocommerce_process_product_meta', array( $this, 'save_visibility' ), 10, 2 );
			add_action( 'woocommerce_save_product_variation', array( $this, 'save_variation_visibility' ), 10, 2 );
		}
                //add_filter( 'woocommerce_order_get_payment_method', array( $this, 'pos_payment_gateway_labels' ), 10, 2 );
                add_filter( 'woocommerce_payment_gateways', array( $this, 'add_payment_gateways' ), 100 );
                add_action( 'plugins_loaded', array( $this, 'init_payment_gateways' ) );


            return; 
             

	}

	/**
	 * Change the default minimum value of 1 for the REST per_page param.
	 *
	 * This allows us to retrieve all items in one request by passing per_page=-1.
	 *
	 * @since 0.0.1
	 *
	 * @param array        $query_params JSON Schema-formatted collection parameters.
	 * @param WP_Post_Type $post_type    Post type object.
	 *
	 * @return array
	 */
	public function per_page_limits( $params, $post_type ) {
		if ( isset( $params['per_page'] ) ) {
			$params['per_page']['minimum'] = -1;

			// Use intval() instead of absint() for sanitization.
			$params['per_page']['sanitize_callback'] = array( $this, 'sanitize_per_page' );
		}

		return $params;
	}

	/**
	 * Sanitize the per_page param.
	 *
	 * @since 0.0.1
	 */
	public function sanitize_per_page( $value, $request, $param ) {
		return intval( $value, 10 );
	}

	/**
	 * Register screen ID.
	 *
	 * @param array $ids IDs.
	 * @return array
	 */
	public function screen_ids( $ids ) {
		$ids[] = 'pos-host';
		return $ids;
	}

	/**
	 * Manage product visibility.
         * @todo need check
	 */
	public function visibility() {
		if ( 'yes' === get_option( 'pos_host_visibility', 'no' ) ) {
			add_action( 'pre_get_posts', array( $this, 'query_visibility_filter' ), 15, 1 );
			add_filter( 'views_edit-product', array( $this, 'add_visibility_views' ) );
		}
	}


	/**
	 * Filter the WP_Query based on the value of pos_host_visibility.
	 *
	 * @todo Explain the different cases.
	 *
	 * @param WP_Query $query The query object.
	 * @return void
	 */
	public function query_visibility_filter( $query ) {
		// Case 1.
		if (
			! isset( $_GET['filter']['updated_at_min'] ) &&
			! is_admin() &&
			isset( $_SERVER['REQUEST_URI'] ) && false === strpos( wc_clean( $_SERVER['REQUEST_URI'] ), 'wp-json/wc' ) &&
			( isset( $query->query_vars['post_type'] ) && 'product' === $query->query_vars['post_type'] ) ||
			( is_product_category() && ! isset( $query->query_vars['post_type'] ) ) ||
			( is_product_tag() && ! isset( $query->query_vars['post_type'] ) )
		) {
			$query->query_vars['meta_query']['pos_host_visibility'] = array(
				'key'     => '_pos_host_visibility',
				'value'   => 'pos',
				'compare' => '!=',
			);
			$query->query_vars['meta_query']['relation']       = 'AND';
			$query->set( 'meta_query', $query->query_vars['meta_query'] );
		}

		// Case 2.
		if (
			isset( $query->query_vars['post_type'] ) &&
			'product' === $query->query_vars['post_type'] &&
			isset( $_GET['pos_only'] )
		) {
			$query->query_vars['meta_query']['pos_host_visibility'] = array(
				'key'     => '_pos_host_visibility',
				'value'   => 'pos',
				'compare' => '=',
			);
			$query->query_vars['meta_query']['relation']       = 'AND';
			$query->set( 'meta_query', $query->query_vars['meta_query'] );
		}

		// Case 3.
		if (
			isset( $query->query_vars['post_type'] ) &&
			'product' === $query->query_vars['post_type'] &&
			isset( $_GET['online_only'] )
		) {
			$query->query_vars['meta_query']['pos_host_visibility'] = array(
				'key'     => '_pos_host_visibility',
				'value'   => 'online',
				'compare' => '=',

			);
			$query->query_vars['meta_query']['relation'] = 'AND';
			$query->set( 'meta_query', $query->query_vars['meta_query'] );
		}

		// Case 4.
		if (
			! is_admin() &&
			isset( $query->query_vars['s'] ) &&
			! empty( $query->query_vars['s'] )
		) {
			$query->query_vars['meta_query']['pos_host_visibility'] = array(
				'key'     => '_pos_host_visibility',
				'value'   => 'pos',
				'compare' => '!=',
			);
			$query->query_vars['meta_query']['relation']       = 'AND';
			$query->set( 'meta_query', $query->query_vars['meta_query'] );
		}
	}

	/**
	 * Add visibility views on the edit product screen.
	 *
	 * @todo To be moved out of this class.
	 *
	 * @param  array $views Array of views.
	 * @return array
	 */
	public function add_visibility_views( $views ) {
		global $post_type_object;
		global $wpdb;

		$post_type = $post_type_object->name;

		// POS Only count.
		$count = $wpdb->get_var( "SELECT COUNT(post_id) FROM $wpdb->postmeta WHERE meta_key = '_pos_host_visibility' AND meta_value = 'pos'" );
		$count = $count ? $count : 0;

		if ( $count ) {
			$class             = ( isset( $_GET['pos_only'] ) ) ? 'current' : '';
			$views['pos_only'] = "<a href='edit.php?post_type={$post_type}&pos_only=1' class='{$class}'>" . __( 'POS Only', 'woocommerce-pos-host' ) . " ({$count}) " . '</a>';
		}

		// Online Only count.
		$count = $wpdb->get_var( "SELECT COUNT(post_id) FROM $wpdb->postmeta WHERE meta_key = '_pos_host_visibility' AND meta_value = 'online'" );
		$count = $count ? $count : 0;
		if ( $count ) {
			$class                = ( isset( $_GET['online_only'] ) ) ? 'current' : '';
			$views['online_only'] = "<a href='edit.php?post_type={$post_type}&online_only=1' class='{$class}'>" . __( 'Online Only', 'woocommerce-pos-host' ) . " ({$count}) " . '</a>';
		}

		return $views;
	}

	/**
	 * Add a quick edit column to the edit product screen.
	 *
	 * @todo To be moved out of this class.
	 *
	 * @param string $column_name Column being shown.
	 * @param string $post_type Post type being shown.
	 */
	public function quick_edit( $column_name, $post_type ) {
		global $post;

		if ( 'thumb' === $column_name && 'product' === $post_type ) {
			include_once $this->plugin_path() . '/includes/views/html-quick-edit-product.php';
		}
	}

	/**
	 * Bulk edit.
	 *
	 * @todo Move the presentation logic to a view file.
	 */
	public function bulk_edit() {
		global $post;
		?>
		<div class="inline-edit-group">
			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'POS Status', 'woocommerce-pos-host' ); ?></span>
				<span class="input-text-wrap">
					<select class="pos_host_visibility" name="_pos_bulk_visibility">
					<?php
					$visibility_options = apply_filters(
						'pos_host_visibility_options',
						array(
							''           => __( '— No Change —', 'woocommerce-pos-host' ),
							'pos_online' => __( 'POS & Online', 'woocommerce-pos-host' ),
							'pos'        => __( 'POS Only', 'woocommerce-pos-host' ),
							'online'     => __( 'Online Only', 'woocommerce-pos-host' ),
						)
					);
					foreach ( $visibility_options as $key => $value ) {
						echo "<option value='" . esc_attr( $key ) . "'>" . esc_html( $value ) . '</option>';
					}
					?>
					</select>
				</span>
			</label>
		</div>
		<?php
	}

	/**
	 * Save visibility on bulk edit.
	 *
	 * @todo To be moved out of this class.
	 * @param object $product
	 */
	public function save_bulk_visibility( $product ) {
		$product_id = $product->get_id();

		if ( ! current_user_can( 'edit_post', $product_id ) || ! isset( $_REQUEST['_pos_bulk_visibility'] ) ) {
			return;
		}

		update_post_meta( $product_id, '_pos_host_visibility', wc_clean( $_REQUEST['_pos_bulk_visibility'] ) );
	}

	/**
	 * Save product visibility.
	 */
	public function save_visibility( $post_id, $post ) {
		if ( 'product' !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'update-post_' . $post_id ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce-pos-host' ) );
		}

		$visibility = isset( $_POST['_pos_host_visibility'] ) ? wc_clean( wp_unslash( $_POST['_pos_host_visibility'] ) ) : 'pos_online';
		$product    = wc_get_product();

		if ( 'variable' === $product->get_type() ) {
			$variations = $product->get_available_variations();

			foreach ( $variations as $variation ) {
				update_post_meta( $variation['variation_id'], '_pos_host_visibility', $visibility );
			}
		}

		update_post_meta( $post_id, '_pos_host_visibility', $visibility );
	}

	/**
	 * Update product visibility when variations are saved.
	 *
	 * @param int $variation_id
	 * @param int $i
	 */
	public function save_variation_visibility( $variation_id, $i ) {
		$variation  = new WC_Product_Variation( $variation_id );
		$parent_id  = $variation->get_parent_id();
		$visibility = get_post_meta( $parent_id, '_pos_host_visibility', true );

		update_post_meta( $variation_id, '_pos_host_visibility', $visibility );
	}

	/**
	 * Hide our custom product created for internal use.
	 *
	 * @param WP_Query $query
	 * @return WP_Query
	 */
	public function hide_pos_custom_product( $query ) {
		// Bail if not querying products.
		if ( 'product' !== $query->get( 'post_type' ) ) {
			return;
		}
                
		$post__not_in = $query->get( 'post__not_in', array() );

		if ( ! is_array( $post__not_in ) ) {
			$post__not_in = array( $post__not_in );
		}

		$post__not_in[] = (int) get_option( 'pos_host_custom_product_id' );
		$query->set( 'post__not_in', $post__not_in );
	}

	/**
	 * Load localisation
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'woocommerce-pos-host', false, dirname( plugin_basename( POS_HOST_PLUGIN_FILE ) ) . '/i18n/languages/' );
	}

	/**
	 * Display admin notices.
	 *
	 * @todo To be moved to POS_HOSTT_Admin_Notices. See WC_Admin_Notices.
	 */
	public function admin_notices() {
		if ( empty( get_option( 'permalink_structure' ) ) ) {
			?>
			<div class="error">
				<p><?php esc_html_e( 'Incorrect Permalinks Structure.', 'woocommerce-pos-host' ); ?> <a href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>"><?php esc_html_e( 'Change Permalinks', 'woocommerce-pos-host' ); ?></a>
				</p>
			</div>
			<?php
		}
	}

	public function order_received_url( $order_received_url ) {
		if ( isset( $_GET['page'] ) && 'pos-host-registers' === $_GET['page'] && isset( $_GET['register'] ) && ! empty( $_GET['register'] ) && isset( $_GET['outlet'] ) && ! empty( $_GET['outlet'] ) ) {
			$register = wc_clean( $_GET['register'] );
			$outlet   = wc_clean( $_GET['outlet'] );

			$register_url = get_home_url() . "/pos-host/$outlet/$register";

			return $register_url;
		} else {
			return $order_received_url;
		}
	}

	public function orders_by_order_type( $vars ) {
		global $typenow, $wp_query;
		if ( 'shop_order' === $typenow ) {
                        
			if ( isset( $_GET['shop_order_pos_host_order_type'] ) && '' !== $_GET['shop_order_pos_host_order_type'] ) {

				if ( 'POS' === $_GET['shop_order_pos_host_order_type'] ) {
					$vars['meta_query'][] = array(
						'key'     => 'pos_host_order_type',
						'value'   => 'POS',
						'compare' => '=',
					);
				} elseif ( 'online' === $_GET['shop_order_pos_host_order_type'] ) {
					$vars['meta_query'][] = array(
						'key'     => 'pos_host_order_type',
						'compare' => 'NOT EXISTS',
					);
				}
			}

			if ( isset( $_GET['shop_order_pos_host_filter_register'] ) && '' !== $_GET['shop_order_pos_host_filter_register'] ) {
				$vars['meta_query'][] = array(
					'key'     => 'pos_host_register_id',
					'value'   => wc_clean( wp_unslash( $_GET['shop_order_pos_host_filter_register'] ) ),
					'compare' => '=',
				);

			}
			if ( isset( $_GET['shop_order_pos_host_filter_outlet'] ) && '' !== $_GET['shop_order_pos_host_filter_outlet'] ) {
				$registers            = pos_host_get_registers_by_outlet( wc_clean( wp_unslash( $_GET['shop_order_pos_host_filter_outlet'] ) ) );
				$vars['meta_query'][] = array(
					'key'     => 'pos_host_register_id',
					'value'   => $registers,
					'compare' => 'IN',
				);

			}
		}

		return $vars;
	}

	public function order_actions_reprint_receipts( $actions, $the_order ) {
		$amount_change = get_post_meta( $the_order->get_id(), 'pos_host_order_type', true );
		$register_id   = get_post_meta( $the_order->get_id(), 'pos_host_register_id', true );
		$register      = pos_host_get_register( absint( $register_id ) );

		if ( $amount_change && $register ) {
			$actions['reprint_receipts'] = array(
				'url'    => wp_nonce_url( admin_url( 'admin.php?print_pos_host_receipt=true&print_from_wc=true&order_id=' . $the_order->get_id() ), 'print_pos_host_receipt' ),
				'name'   => __( 'Reprints receipts', 'woocommerce-pos-host' ),
				'action' => 'reprint_receipts',
				'target' => '_parent',
			);
		}

		return $actions;
	}

	public function add_prefix_suffix_order_number( $order_id, $order ) {
		if ( ! $order instanceof WC_Order ) {
			return $order_id;
		}

		// Support WooCommerce Sequential Order Numbers Pro by SkyVerge.
		// TODO: third party integration.
		if ( function_exists( 'wc_seq_order_number_pro' ) ) {
			$order_number = get_post_meta( $order->get_id(), '_order_number', true );

			if ( $order->get_id() === intval( $order_number ) ) {
				update_post_meta( $order->get_id(), '_order_number', '' );
			}

			return $order_id;
		}

		global $wpdb;

		$register_id = absint( get_post_meta( $order->get_id(), 'pos_host_register_id', true ) );

		if ( $register_id ) {
			$_order_id = get_post_meta( $order->get_id(), 'pos_host_prefix_suffix_order_number', true );

			if ( empty( $_order_id ) ) {
				$register = pos_host_get_register( $register_id );

				if ( $register ) {
					$order_number = $order->get_id();

					// Support WooCommerce Sequential Order Numbers by SkyVerge.
					// TODO: third party integration.
					if ( class_exists( 'WC_Seq_Order_Number' ) ) {
						update_post_meta( $order->get_id(), '_order_number', '' );

						$seq = new WC_Seq_Order_Number();
						$seq->set_sequential_order_number( $order->get_id(), get_post( $order->get_id() ) );

						// Remove empty meta fields.
						$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_order_number' AND meta_value = ''", $order->get_id() ) );

						$sequential_order_number = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_order_number'", $order->get_id() ) );
						$order_number            = empty( $sequential_order_number ) ? $order_number : $sequential_order_number;
					}

					$_order_id = $register->get_prefix() . $order_number . $register->get_suffix();
					update_post_meta( $order->get_id(), 'pos_host_prefix_suffix_order_number', $_order_id, true );
				}
			}

			$order_id = str_replace( '#', '', $_order_id );
		}

		return $order_id;
	}

	/**
	 * Force WC()->countries->get_formatted_address() to always display
	 * country regardless if the same as base.
	 */
	public function force_country_display() {
		add_filter( 'woocommerce_formatted_address_force_country_display', '__return_true' );
	}

	public function print_report() {
		if ( ! isset( $_GET['print_pos_host_receipt'] ) || empty( $_GET['print_pos_host_receipt'] ) ) {
			return;
		}

		if ( ! isset( $_GET['order_id'] ) && ! isset( $_GET['refund_id'] ) ) {
			return;
		}

		if ( empty( $_GET['order_id'] ) && empty( $_GET['refund_id'] ) ) {
			return;
		}

		$order_id = isset( $_GET['order_id'] ) ? wc_clean( wp_unslash( $_GET['order_id'] ) ) : wc_clean( wp_unslash( $_GET['refund_id'] ) );
		$post     = get_post( absint( $order_id ) );
		$order    = 'shop_order' === $post->post_type ? wc_get_order( $post->ID ) : wc_get_order( $post->post_parent );

		if ( ! $order ) {
			return;
		}

		$nonce = isset( $_REQUEST['_wpnonce'] ) ? wc_clean( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'print_pos_host_receipt' ) || ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You are not allowed to view this page.', 'woocommerce-pos-host' ) );
		}

		$register_id = get_post_meta( $order_id, 'pos_host_register_id', true );

		$register = pos_host_get_register( absint( $register_id ) );
		if ( ! $register ) {
			$register = pos_host_get_register( absint( get_option( 'pos_host_default_register' ) ) );
		}

		$receipt = pos_host_get_receipt( $register->get_receipt() );
		$outlet  = pos_host_get_outlet( $register->get_outlet() );

		remove_action( 'wp_footer', 'wp_admin_bar_render', 1000 );

		include_once POS_HOST()->plugin_path() . '/includes/views/html-print-receipt.php';
	}

	/**
	 * Check if the current page is a POS register.
	 *
	 * @return bool
	 */
	public function is_pos() {
		global $wp_query;
		if ( isset( $this->is_pos ) && ! is_null( $this->is_pos ) ) {
			return $this->is_pos;
		} else {
			$q = $wp_query->query;
			if ( isset( $q['page'] ) && 'pos-host-registers' === $q['page'] && isset( $q['action'] ) && 'view' === $q['action'] ) {
				$this->is_pos = true;
			} else {
				$this->is_pos = false;
			}
			return $this->is_pos;
		}
	}

	public function woocommerce_delete_shop_order_transients() {
		$transients_to_clear = array(
			'pos_host_report_sales_by_register',
			'pos_host_report_sales_by_outlet',
			'pos_host_report_sales_by_cashier',
		);

		// Clear transients where we have names.
		foreach ( $transients_to_clear as $transient ) {
			delete_transient( $transient );
		}
	}

	public function hidden_order_itemmeta( $meta_keys = array() ) {
		$meta_keys[] = '_pos_host_custom_product';
		$meta_keys[] = '_price';
		return $meta_keys;
	}

	// @todo: is_pos() function does not work for some reason, be careful.
	public function woocommerce_email_actions( $email_actions ) {
		if (
			is_pos_referer() ||
			is_pos() ||
			( isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( wc_clean( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'pos_host_orders' ) )
		) {
			foreach ( $email_actions as $key => $action ) {
				if ( strpos( $action, 'woocommerce_order_status_' ) === 0 ) {
					unset( $email_actions[ $key ] );
				}
			}

			$aenc = 'no';
			if ( 'yes' !== $aenc ) {
				$new_actions = array();
				foreach ( $email_actions as $action ) {
					if ( 'woocommerce_created_customer' === $action ) {
						continue;
					}

					$new_actions[] = $action;
				}
				$email_actions = $new_actions;
			}
		}

		return $email_actions;
	}

	public function get_subscription_payment_method( $payment_method, $subscription ) {
		if ( 'POS' === get_post_meta( $subscription->get_order_number(), 'pos_host_order_type', true ) ) {
			$payment_method = get_post_meta( $subscription->get_order_number(), '_payment_method_title', true );
		}

		return $payment_method;
	}

	public function add_custom_discounts( $default ) {
		$discounts = get_option( 'pos_host_discount_presets', array() );
		$discounts = empty( $discounts ) ? array() : $discounts;

		foreach ( $discounts as $key => $value ) {
			if ( array_key_exists( $value, $default ) ) {
				continue;
			}

			$default[ $value ] = $value . __( '%', 'woocommerce-pos-host' );
		}

		return $default;
	}

	/**
	 * Init payment gateways.
	 *
	 * @since 0.0.1
	 */
	public function init_payment_gateways() {
                 include_once 'gateways/class-pos-host-cash.php';
 		include_once 'gateways/terminal/class-pos-host-terminal.php';
                 include_once 'gateways/stripe/class-pos-host-stripe.php';
            return;
	}

	/**
	 * Add payment gateways.
	 *
	 * @since 0.0.1
	 *
	 * @param array $methods Payment methods.
	 * @return array
	 */
	public function add_payment_gateways( $methods ) {
		$methods[] = 'POS_HOST_Gateway_Cash';
		$methods[] = 'POS_HOST_Gateway_Stripe_Terminal';
		$methods[] = 'POS_HOST_Gateway_Stripe_Credit_Card';
                 $methods[] = 'POS_HOST_Gateway_Terminal';

		return $methods;
	}

	/**
	 * Returns an instance of POS_HOST_store.
	 *
	 * @since 1.9.0
	 * @return POS_HOST_store
	 */
	public function store() {
		return POS_HOST_Store::instance();
	}

	/**
	 * Returns an instance of POS_HOST_Barcodes.
	 *
	 * @since 1.9.0
	 * @return POS_HOST_Barcodes
	 */
	public function barcode() {
		return POS_HOST_Barcodes::instance();
	}

	/**
	 * Returns an instance of POS_HOST_Stock.
	 *
	 * @since 3.0.0
	 * @return POS_HOST_Stock
	 */
	public function stock() {
		return POS_HOST_Stocks::instance();
	}

        /*
         * Manage Store 
         * accept actions: 
         *  pos-host-store-show, pos-host-store-update
        public function store_manage(){
            if ( 
                !isset( $_GET['action'] ) ||
                ( "pos-host-store-show" !== $_GET['action'] &&
                "pos-host-store-update" !== $_GET['action'] )  ) {
                return;
            }
            POS_HOST_Store::instance()->display_store_page();
            
        }
         * 
         */

        /*
         * Manage user roles, locations
         * accept actions: 
         *  pos-host-user-show, pos-host-user-update
         * 
         */
        public function user_manage(){
            if ( 
                !isset( $_GET['action'] ) ||
                ( "pos-host-user-show" !== $_GET['action'] &&
                "pos-host-user-update" !== $_GET['action'] ) ||
                !isset( $_GET['user_id'] ) || !$_GET['user_id'] ) {
                return;
            }
            $user_id = (int) ($_GET['user_id']);
            POS_HOST_Users::instance()->display_single_user_page($user_id);
            exit;
        }

	public function manage_floatval_quantity() {
		remove_filter( 'woocommerce_stock_amount', 'intval' );
		add_filter( 'woocommerce_stock_amount', 'floatval', 1 );
	}

	/**
	 * Register a new WC data stores for our CPTs.
	 *
	 * @param array $stores Data stores.
	 * @return array
	 */
	public function register_data_stores( $stores ) {
                include_once dirname( __FILE__ ) . '/data-stores/class-pos-host-data-store-wp.php';
                include_once dirname( __FILE__ ) . '/data-stores/class-pos-host-register-data-store-cpt.php';
                include_once dirname( __FILE__ ) . '/data-stores/class-pos-host-outlet-data-store-cpt.php';
                include_once dirname( __FILE__ ) . '/data-stores/class-pos-host-receipt-data-store-cpt.php';
                include_once dirname( __FILE__ ) . '/data-stores/class-pos-host-grid-data-store-cpt.php';
                include_once dirname( __FILE__ ) . '/data-stores/class-pos-host-session-data-store-cpt.php';

		$stores['pos_host_register'] = 'POS_HOST_Register_Data_Store_CPT';	
                 $stores['pos_host_outlet']   = 'POS_HOST_Outlet_Data_Store_CPT';
		$stores['pos_host_receipt']  = 'POS_HOST_Receipt_Data_Store_CPT';               
		$stores['pos_host_grid']     = 'POS_HOST_Grid_Data_Store_CPT';
		$stores['pos_host_session']  = 'POS_HOST_Session_Data_Store_CPT';

		return $stores;
	}
}
