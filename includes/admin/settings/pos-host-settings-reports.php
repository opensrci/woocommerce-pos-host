<?php
/**
 * Point of Sale Reports Settings
 *
 * @package WooCommerce_pos_host/Classes/Admin/Settings
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Admin_Settings_Reports', false ) ) {
	return new POS_HOST_Admin_Settings_Reports();
}

/**
 * POS_HOST_Admin_Settings_Reports.
 */
class POS_HOST_Admin_Settings_Reports extends POS_HOST_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'reports';
		$this->label = __( 'Reports', 'woocommerce-pos-host' );

		parent::__construct();
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		global $woocommerce;

		$order_statuses = pos_host_get_order_statuses_no_prefix();

		return apply_filters(
			'woocommerce_point_of_sale_general_settings_fields',
			array(

				array(
					'title' => __( 'Report Options', 'woocommerce-pos-host' ),
					'desc'  => __( 'The following options affect the reports that are displayed when closing the register.', 'woocommerce-pos-host' ),
					'type'  => 'title',
					'id'    => 'pos_host_settings_reports',
				),
				array(
					'title'         => __( 'Closing Reports', 'woocommerce-pos-host' ),
					'desc'          => __( 'Display end of day report when closing register', 'woocommerce-pos-host' ),
					'desc_tip'      => __( 'End of day report displayed with total sales when register closes.', 'woocommerce-pos-host' ),
					'id'            => 'pos_host_display_end_of_day_report',
					'default'       => 'no',
					'type'          => 'checkbox',
					'checkboxgroup' => 'start',
				),
				array(
					'title'             => __( 'Report Orders', 'woocommerce-pos-host' ),
					'desc_tip'          => __( 'Select which order statuses to include in the final counts displayed in the end of day report.', 'woocommerce-pos-host' ),
					'id'                => 'pos_host_end_of_day_order_statuses',
					'class'             => 'wc-enhanced-select',
					'type'              => 'multiselect',
					'custom_attributes' => array( 'required' => 'required' ),
					'default'           => 'processing',
					'options'           => $order_statuses,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'pos_host_settings_reports',
				),
				array(
					'title' => __( 'End of Day Email', 'woocommerce-pos-host' ),
					/* translators: %1$s opening anchor tag %2$s closing anchor tag */
					'desc'  => sprintf( __( 'The end of day email notification can be customized in %1$sWooCommerce &gt; Emails%2$s.', 'woocommerce-pos-host' ), '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=email&section=pos_host_email_end_of_day_report' ) . '">', '</a>' ),
					'type'  => 'title',
					'id'    => 'end_of_day_email',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'status_options',
				),
			)
		);
	}

	/**
	 * Save settings
	 */
	public function save() {
		$settings = $this->get_settings();

		POS_HOST_Admin_Settings::save_fields( $settings );
	}
}

return new POS_HOST_Admin_Settings_Reports();
