<?php
/**
 * Admin Menus
 *
 * Handles pos host menu in admin.
 *
 * @package WooCommerce_pos_host/Classes/Admin
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Admin_Menus', false ) ) {
	return new POS_HOST_Admin_Menus();
}

/**
 * POS_HOST_Admin_Menus.
 */
class POS_HOST_Admin_Menus {

	/**
	 * Construct.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_filter( 'submenu_file', array( $this, 'submenu_file' ), 10, 2 );

		// Handle saving settings earlier than load-{page} hook
		// to avoid race conditions in conditional menus.
		add_action( 'wp_loaded', array( $this, 'save_settings' ) );
	}

	/**
	 * Add the menu.
	 */
	public function add_menu() {
		// Add the POS HOST Menu.
		add_menu_page(
			__( 'POS HOST', 'woocommerce-pos-host' ), // Page title.
			__( 'POS HOST', 'woocommerce-pos-host' ), // Menu title.
			'manage_woocommerce_pos_host',
			POS_HOST()->menu_slug,
			array( $this, 'registers_page' ),
			null,
			'55.8'
		);

		// Add barcodes page.
		add_submenu_page(
			POS_HOST()->menu_slug,
			__( 'Barcodes', 'woocommerce-pos-host' ),
			__( 'Barcodes', 'woocommerce-pos-host' ),
			'manage_woocommerce_pos_host',
			POS_HOST()->barcodes_page_slug,
			array( $this, 'barcodes_page' )
		);

		// Add stock controller page.
		add_submenu_page(
			POS_HOST()->menu_slug,
			__( 'Stock', 'woocommerce-pos-host' ),
			__( 'Stock', 'woocommerce-pos-host' ),
			'manage_woocommerce_pos_host',
			POS_HOST()->stock_controller_page_slug,
			array( $this, 'stock_controller_page' )
		);

		// Add settings page.
		add_submenu_page(
			POS_HOST()->menu_slug,
			__( 'Settings', 'woocommerce-pos-host' ),
			__( 'Settings', 'woocommerce-pos-host' ),
			'manage_woocommerce_pos_host',
			POS_HOST()->settings_page_slug,
			array( $this, 'settings_page' )
		);


		// Hide screen options on POS screens.
		if ( isset( $_GET['page'] ) ) {
			$curent_screen = substr( sanitize_key( $_GET['page'] ), 0, 9 );

			if ( 'pos_host_' === $curent_screen ) {
				add_filter( 'screen_options_show_screen', '__return_false' );
			}
		}
	}

	/**
	 * Highlights the correct top level admin menu item for post type add screens.
	 *
	 * @param string $submenu_file
	 * @param string $parent_file
	 *
	 * @return string
	 */
	public function submenu_file( $submenu_file, $parent_file ) {
		global $post_type;

		switch ( $post_type ) {
			case 'pos_host_register':
				$submenu_file = 'edit.php?post_type=pos_host_register';
				break;
			case 'pos_host_outlet':
				$submenu_file = 'edit.php?post_type=pos_host_outlet';
				break;
			case 'pos_host_grid':
				$submenu_file = 'edit.php?post_type=pos_host_grid';
				break;
		}

		return $submenu_file;
	}

	/**
	 * Init the barcodes page.
	 */
	public function barcodes_page() {
		POS_HOST()->barcode()->display_single_barcode_page();
	}

	/**
	 * Init the stock controller page.
	 */
	public function stock_controller_page() {
		POS_HOST()->stock()->display_single_stocks_page();
	}
	/**
	 * Init the settings page.
	 */
	public function settings_page() {
		// Add any posted errors.
		if ( ! empty( $_GET['wc_error'] ) ) { // WPCS: input var okay, CSRF ok.
			POS_HOST_Admin_Settings::add_error( wp_kses_post( wp_unslash( $_GET['wc_error'] ) ) ); // WPCS: input var okay, CSRF ok.
		}

		// Add any posted messages.
		if ( ! empty( $_GET['wc_message'] ) ) { // WPCS: input var okay, CSRF ok.
			POS_HOST_Admin_Settings::add_message( wp_kses_post( wp_unslash( $_GET['wc_message'] ) ) ); // WPCS: input var okay, CSRF ok.
		}

		POS_HOST_Admin_Settings::output();
	}

	/**
	 * Handle saving of settings.
	 */
	public function save_settings() {
		global $current_tab, $current_section;

		// We should only save on the settings page.
		if ( ! is_admin() || ! isset( $_GET['page'] ) || 'pos-host-settings' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			return;
		}

		// Include settings pages.
		POS_HOST_Admin_Settings::get_settings_pages();

		// Get current tab/section.
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( wp_unslash( $_GET['tab'] ) );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( wp_unslash( $_REQUEST['section'] ) );

		// Save settings if data has been posted.
		if ( '' !== $current_section && apply_filters( "pos_host_save_settings_{$current_tab}_{$current_section}", ! empty( $_POST['save'] ) ) ) {
			check_admin_referer( 'pos-host-settings' );
			POS_HOST_Admin_Settings::save();
		} elseif ( '' === $current_section && apply_filters( "pos_host_save_settings_{$current_tab}", ! empty( $_POST['save'] ) ) ) {
			check_admin_referer( 'pos-host-settings' );
			POS_HOST_Admin_Settings::save();
		}
	}
}

return new POS_HOST_Admin_Menus();

