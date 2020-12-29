<?php
/**
 * Point of Sale Settings Page/Tab
 *
 * @package WooCommerce_pos_host/Classes/Admin/Settings
 */

defined( 'ABSPATH' ) || exit;

// Load WC_Settings_Page.
require_once WC()->plugin_path() . '/includes/admin/settings/class-wc-settings-page.php';

/**
 * POS_HOST_Admin_Settings_Page.
 */
abstract class POS_HOST_Settings_Page extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'pos_host_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'pos_host_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'pos_host_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'pos_host_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Output sections.
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();

		if ( empty( $sections ) || 1 === count( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';

		$array_keys = array_keys( $sections );

		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=pos-host-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) ) . '" class="' . esc_attr( $current_section === $id ? 'current' : '' ) . '">' . esc_html( $label ) . '</a> ' . esc_html( end( $array_keys ) === $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}
}
