<?php
/**
 * Point of Sale General Settings
 *
 * @package WooCommerce_pos_host/Classes/Admin/Settings
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Admin_Settings_General', false ) ) {
	return new POS_HOST_Admin_Settings_General();
}

/**
 * POS_HOST_Admin_Settings_General.
 */
class POS_HOST_Admin_Settings_General extends POS_HOST_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = __( 'General', 'woocommerce-pos-host' );

		parent::__construct();

		add_filter( 'pos_host_fulfilled_order_statuses', array( $this, 'fulfilled_order_statuses' ) );
		add_filter( 'pos_host_parked_order_statuses', array( $this, 'parked_order_statuses' ) );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		global $current_section;

		$order_statuses = pos_host_get_order_statuses_no_prefix();

		$settings = apply_filters(
			'pos_host_general_settings',
			array(
				array(
					'title' => __( 'General Options', 'woocommerce-pos-host' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'general_options',
				),
				array(
					'name'     => __( 'Filters', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'Select which filters appear on the Orders page.', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_order_filters',
					'class'    => 'wc-enhanced-select',
					'type'     => 'multiselect',
					'default'  => array( 'register' ),
					'options'  => array(
						'registers' => __( 'Registers', 'woocommerce-pos-host' ),
						'outlets'   => __( 'Outlets', 'woocommerce-pos-host' ),
					),
					'autoload' => true,
				),
				array(
					'title'         => __( 'Front End Access', 'woocommerce-pos-host' ),
					'desc'          => __( 'Enable front-end access to assigned registers', 'woocommerce-pos-host' ),
					'desc_tip'      => __( 'Allows cashiers to access their assigned registers from their My Account page.', 'woocommerce-pos-host' ),
					'id'            => 'pos_host_enable_frontend_access',
					'default'       => 'no',
					'type'          => 'checkbox',
					'checkboxgroup' => 'start',
				),
				array(
					'title'    => __( 'Auto Logout', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'Choose whether to automatically exit the register screen after inactive time. This will not close the register.', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_auto_logout',
					'default'  => '0',
					'class'    => 'wc-enhanced-select',
					'type'     => 'select',
					'options'  => array(
						0   => __( 'Disable', 'woocommerce-pos-host' ),
						1   => __( '1 min', 'woocommerce-pos-host' ),
						5   => __( '5 min', 'woocommerce-pos-host' ),
						15  => __( '15 min', 'woocommerce-pos-host' ),
						30  => __( '30 mins', 'woocommerce-pos-host' ),
						60  => __( '1 hour', 'woocommerce-pos-host' ),
						120 => __( '2 hours', 'woocommerce-pos-host' ),
					),
				),
				array(
					'name'     => __( 'Tax Number', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_tax_number',
					'desc_tip' => __( 'Enter the tax number which is applied to this particular register. This will be printed on receipts if tax number is enabled on receipt template.', 'woocommerce-pos-host' ),
					'type'     => 'text',
				),
				array(
					'name'     => __( 'Transitions and Effects', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_disable_transitions_effects',
					'std'      => '',
					'type'     => 'checkbox',
					'desc'     => __( 'Disable transitions and effects', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'Check this box to disable transitions and effects when using the register.', 'woocommerce-pos-host' ),
					'default'  => 'no',
					'autoload' => true,
				),
				array(
					'name'     => __( 'Dining', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_enable_dining',
					'std'      => '',
					'type'     => 'checkbox',
					'desc'     => __( 'Enable dining option', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'Check this box to enable dining when using the register.', 'woocommerce-pos-host' ),
					'default'  => 'no',
					'autoload' => true,
				),
				array(
					'name'     => __( 'Refresh Data on Load', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_refresh_on_load',
					'std'      => '',
					'type'     => 'checkbox',
					'desc'     => __( 'Refresh data on load', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'Enable this checkbox to force reload POS data (products, customers, orders, etc.) on register reload.', 'woocommerce-pos-host' ),
					'default'  => 'yes',
					'autoload' => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'general_options',
				),
				array(
					'title' => __( 'Status Options', 'woocommerce-pos-host' ),
					'desc'  => __( 'The following options affect the status of the orders when using the register.', 'woocommerce-pos-host' ),
					'type'  => 'title',
					'id'    => 'status_options',
				),
				array(
					'name'     => __( 'Fulfilled Orders', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'Select the order status of completed orders when using the register.', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_fulfilled_order_status',
					'css'      => '',
					'std'      => '',
					'class'    => 'wc-enhanced-select',
					'type'     => 'select',
					'options'  => apply_filters( 'pos_host_fulfilled_order_statuses', $order_statuses ),
					'default'  => 'processing',
				),
				array(
					'name'     => __( 'Parked Orders', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'Select the order status of saved orders when using the register.', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_parked_order_status',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'options'  => apply_filters( 'pos_host_parked_order_statuses', $order_statuses ),
					'default'  => 'pending',
				),
				array(
					'name'     => __( 'Fetch Orders ', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'Select the order statuses of loaded orders when using the register.', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_fetch_order_statuses',
					'class'    => 'wc-enhanced-select',
					'type'     => 'multiselect',
					'options'  => apply_filters( 'pos_host_fetch_order_statuses', $order_statuses ),
					'default'  => array( 'pending', 'on-hold' ),
				),
				array(
					'name'     => __( 'Website Orders ', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_load_website_orders',
					'std'      => '',
					'type'     => 'checkbox',
					'desc'     => __( 'Load website orders', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'Loads orders placed through the web store.', 'woocommerce-pos-host' ),
					'default'  => 'no',
					'autoload' => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'status_options',
				),
			)
		);

		return apply_filters( 'pos_host_get_settings_' . $this->id, $settings, $current_section );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings();
		POS_HOST_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		$settings = $this->get_settings();
		POS_HOST_Admin_Settings::save_fields( $settings );
	}

	/**
	 * Filter the list of options for the pos_host_fulfilled_order_status option.
	 *
	 * @since 5.0.0
	 *
	 * @param array $statuses Order statuses.
	 * @return array
	 */
	public function fulfilled_order_statuses( $statuses ) {
		unset( $statuses['on-hold'] );
		unset( $statuses['pending'] );
		unset( $statuses['cancelled'] );
		unset( $statuses['refunded'] );
		unset( $statuses['failed'] );

		return $statuses;
	}

	/**
	 * Filter the list of options for the pos_host_parked_order_status option.
	 *
	 * @since 5.0.0
	 *
	 * @param array $statuses Order statuses.
	 * @return array
	 */
	public function parked_order_statuses( $statuses ) {
		$remove = array_unique(
			array_merge(
				array(
					'cancelled',
					'refunded',
					'failed',
					'completed',
					'processing',
				),
				wc_get_is_paid_statuses()
			)
		);

		foreach ( $remove as $status ) {
			unset( $statuses[ $status ] );
		}

		return $statuses;
	}
}

return new POS_HOST_Admin_Settings_General();
