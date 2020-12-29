<?php
/**
 * Point of Sale Tax Settings
 *
 * @package WooCommerce_pos_host/Classes/Admin/Settings
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Admin_Settings_Tax', false ) ) {
	return new POS_HOST_Admin_Settings_Tax();
}

/**
 * POS_HOST_Admin_Settings_Tax.
 */
class POS_HOST_Admin_Settings_Tax extends POS_HOST_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'tax';
		$this->label = __( 'Tax', 'woocommerce-pos-host' );

		parent::__construct();
	}

	/**
	 * Add this page to settings.
	 *
	 * @param array $pages Current pages.
	 * @return array|mixed
	 */
	public function add_settings_page( $pages ) {
		return 'yes' === get_option( 'woocommerce_calc_taxes' ) ? parent::add_settings_page( $pages ) : $pages;
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		global $woocommerce;

		$class = 'wc-enhanced-select';

		if ( 'yes' !== get_option( 'woocommerce_calc_taxes', 'no' ) ) {
			update_option( 'pos_host_tax_calculation', 'disabled' );
			$class = 'disabled_select';
		}

		$tax_calculation = array(
			'name'     => __( 'Tax Calculation', 'woocommerce-pos-host' ),
			'id'       => 'pos_host_tax_calculation',
			'css'      => '',
			'desc_tip' => __( 'Enables the calculation of tax using the WooCommerce configurations.', 'woocommerce-pos-host' ),
			'std'      => '',
			'type'     => 'select',
			'class'    => $class,
			'options'  => array(
				'enabled'  => __( 'Enabled (using WooCommerce configurations)', 'woocommerce-pos-host' ),
				'disabled' => __( 'Disabled', 'woocommerce-pos-host' ),
			),
		);
		$tax_based_on    = array(
			'name'     => __( 'Calculate Tax Based On', 'woocommerce-pos-host' ),
			'id'       => 'pos_host_calculate_tax_based_on',
			'css'      => '',
			'std'      => '',
			'class'    => 'wc-enhanced-select',
			'desc_tip' => __( 'This option determines which address used to calculate tax.', 'woocommerce-pos-host' ),
			'type'     => 'select',
			'default'  => 'outlet',
			'options'  => array(
				'default'  => __( 'Default WooCommerce', 'woocommerce-pos-host' ),
				'shipping' => __( 'Customer shipping address', 'woocommerce-pos-host' ),
				'billing'  => __( 'Customer billing address', 'woocommerce-pos-host' ),
				'base'     => __( 'Shop base address', 'woocommerce-pos-host' ),
				'outlet'   => __( 'Outlet address', 'woocommerce-pos-host' ),
			),
		);

		return apply_filters(
			'woocommerce_point_of_sale_tax_settings_fields',
			array(

				array(
					'title' => __( 'Tax Options', 'woocommerce-pos-host' ),
					'type'  => 'title',
					'desc'  => '',
					'id'    => 'tax_options',
				),
				$tax_calculation,
				$tax_based_on,
				array(
					'type' => 'sectionend',
					'id'   => 'tax_options',
				),

			)
		);
	}

	/**
	 * Save settings.
	 */
	public function save() {
		$settings = $this->get_settings();

		POS_HOST_Admin_Settings::save_fields( $settings );
	}

}

return new POS_HOST_Admin_Settings_Tax();
