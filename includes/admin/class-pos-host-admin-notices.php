<?php
/**
 * Admin Notices
 *
 * @package WooCommerce_pos_host/Classes/Admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Admin_Notices.
 */
class POS_HOST_Admin_Notices {

	/**
	 * Stores notices.
	 *
	 * @var array
	 */
	private static $notices = array();

	/**
	 * Array of notices - name => callback.
	 *
	 * @var array
	 */
	private static $core_notices = array(
		'install'        => 'install_notice',
		'update'         => 'update_notice',
		'wc-rest-api'    => 'wc_rest_api_notice',
	);

	/**
	 * Constructor.
	 */
	public static function init() {
		self::$notices = get_option( 'pos_host_admin_notices', array() );

		add_action( 'wp_loaded', array( __CLASS__, 'hide_notices' ) );
		add_action( 'shutdown', array( __CLASS__, 'store_notices' ) );

		if ( current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_print_styles', array( __CLASS__, 'add_notices' ) );
		}
	}

	/**
	 * See if a notice is being shown.
	 *
	 * @param string $name Notice name.
	 *
	 * @return boolean
	 */
	public static function has_notice( $name ) {
		return in_array( $name, self::get_notices(), true );
	}

	/**
	 * Store notices to DB
	 */
	public static function store_notices() {
		update_option( 'pos_host_admin_notices', self::get_notices() );
	}

	/**
	 * Get notices
	 *
	 * @return array
	 */
	public static function get_notices() {
		return self::$notices;
	}
	/**
	 * Remove all notices.
	 */
	public static function remove_all_notices() {
		self::$notices = array();
	}

	/**
	 * Show a notice.
	 *
	 * @param string $name Notice name.
	 */
	public static function add_notice( $name ) {
		self::$notices = array_unique( array_merge( self::get_notices(), array( $name ) ) );
	}

	/**
	 * Remove a notice from being displayed.
	 *
	 * @param string $name Notice name.
	 */
	public static function remove_notice( $name ) {
		self::$notices = array_diff( self::get_notices(), array( $name ) );
	}

	/**
	 * Hide a notice if the GET variable is set.
	 */
	public static function hide_notices() {
		if ( isset( $_GET['pos-host-hide-notice'] ) && isset( $_GET['_pos_host_notice_nonce'] ) ) { 
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_pos_host_notice_nonce'] ) ), 'pos_host_hide_notices_nonce' ) ) {
				wp_die( esc_html__( 'Failed. Need refresh.', 'woocommerce-pos-host' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( esc_html__( 'Permission dennied.', 'woocommerce-pos-host' ) );
			}

			$hide_notice = sanitize_text_field( wp_unslash( $_GET['pos-host-hide-notice'] ) );
                        self::remove_notice( $hide_notice );

			update_user_meta( get_current_user_id(), 'dismissed_' . $hide_notice . '_notice', true );

			do_action( 'pos_host_hide_' . $hide_notice . '_notice' );
		}
	}

	/**
	 * Add notices + styles if needed.
	 */
	public static function add_notices() {
		$notices = self::get_notices();

		if ( empty( $notices ) ) {
			return;
		}

		// Enqueue WC activation styles if not loaded.
		wp_enqueue_style( 'woocommerce-activation', plugins_url( '/assets/css/activation.css', WC_PLUGIN_FILE ), array(), WC_VERSION );
		wp_style_add_data( 'woocommerce-activation', 'rtl', 'replace' );

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$show_on_screens = array_merge(
			wc_pos_get_screen_ids(),
			array(
				'dashboard',
				'plugins',
			)
		);

		// Notices should only show on pos.host screens, the main dashboard, and on the plugins screen.
		if ( ! in_array( $screen_id, $show_on_screens, true ) ) {
			return;
		}

		foreach ( $notices as $notice ) {
			if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'pos_host_show_admin_notice', true, $notice ) ) {
				add_action( 'admin_notices', array( __CLASS__, self::$core_notices[ $notice ] ) );
			}
		}
	}

	/**
	 * If we need to update, include a message with the update button.
	 */
	public static function update_notice() {
		include dirname( __FILE__ ) . '/views/notices/html-notice-update.php';
	}

	/**
	 * If we have just installed, show a message with the install pages button.
	 */
	public static function install_notice() {
		include dirname( __FILE__ ) . '/views/notices/html-notice-install.php';
	}

	/**
	 * Show a notice if the WC REST API is blocked.
	 */
	public static function wc_rest_api_notice() {
		include dirname( __FILE__ ) . '/views/notices/html-notice-wc-rest-api.php';
	}

	/**
	 * Show a notice if the Stripe gateway is required.
	 */
	public static function stripe_gateway_notice() {
		include dirname( __FILE__ ) . '/views/notices/html-notice-stripe-gateway.php';
	}
}

POS_HOST_Admin_Notices::init();
