<?php
/**
 * Admin Settings
 *
 * Handles setting pages in admin.
 *
 * @package WooCommerce_pos_host/Classes/Settings
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Admin_Settings.
 */
class POS_HOST_Admin_Settings extends WC_Admin_Settings {

	/**
	 * Setting pages.
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Error messages.
	 *
	 * @var array Error messages.
	 */
	private static $errors = array();

	/**
	 * Update messages.
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * Include the settings page classes.
	 */
	public static function get_settings_pages() {
		if ( empty( self::$settings ) ) {
			$settings = array();

			// Load POS_HOST_Settings_Page.
			include_once POS_HOST()->plugin_path() . '/includes/admin/settings/pos-host-settings-page.php';

			$settings[] = include POS_HOST()->plugin_path() . '/includes/admin/settings/pos-host-settings-general.php';
			$settings[] = include POS_HOST()->plugin_path() . '/includes/admin/settings/pos-host-settings-register.php';
			$settings[] = include POS_HOST()->plugin_path() . '/includes/admin/settings/pos-host-settings-tiles.php';
			// $settings[] = include POS_HOST()->plugin_path() . '/includes/admin/settings/pos-host-settings-products.php';
			$settings[] = include POS_HOST()->plugin_path() . '/includes/admin/settings/pos-host-settings-customer.php';
			$settings[] = include POS_HOST()->plugin_path() . '/includes/admin/settings/pos-host-settings-tax.php';
			$settings[] = include POS_HOST()->plugin_path() . '/includes/admin/settings/pos-host-settings-reports.php';
			$settings[] = include POS_HOST()->plugin_path() . '/includes/admin/settings/pos-host-settings-advanced.php';

			self::$settings = apply_filters( 'pos_host_get_settings_pages', $settings );
		}

		return self::$settings;
	}

	/**
	 * Save the settings
	 */
	public static function save() {
		global $current_tab;

		check_admin_referer( 'pos-host-settings' );

		// Trigger actions.
		do_action( 'pos_host_settings_save_' . $current_tab );
		do_action( 'pos_host_update_options_' . $current_tab );
		do_action( 'pos_host_update_options' );

		self::add_message( __( 'Your settings have been saved.', 'woocommerce-pos-host' ) );
		self::check_download_folder_protection();

		// Clear any unwanted data and flush rules.
		update_option( 'woocommerce_queue_flush_rewrite_rules', 'yes' );
		WC()->query->init_query_vars();
		WC()->query->add_endpoints();

		do_action( 'pos_host_settings_saved' );
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main settings page in admin.
	 */
	public static function output() {
		global $current_section, $current_tab;

		do_action( 'pos_host_settings_start' );

		// Get tabs for the settings page.
		$tabs = apply_filters( 'pos_host_settings_tabs_array', array() );

		include_once POS_HOST()->plugin_path() . '/includes/admin/views/html-admin-settings.php';
	}
}
