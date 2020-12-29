<?php
/**
 * Point of Sale Advanced Settings
 *
 * @package WooCommerce_pos_host/Classes/Admin/Settings
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Admin_Settings_Advanced', false ) ) {
	return new POS_HOST_Admin_Settings_Advanced();
}

/**
 * POS_HOST_Admin_Settings_Advanced.
 */
class POS_HOST_Admin_Settings_Advanced extends POS_HOST_Settings_Page {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'advanced';
		$this->label = __( 'Advanced', 'woocommerce-pos-host' );

		parent::__construct();

		add_action( 'woocommerce_admin_field_database_options', array( $this, 'output_database_options' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = apply_filters(
			'pos_host_advanced_settings',
			array(
				array(
					'title' => __( 'Advanced Options', 'woocommerce-pos-host' ),
					'type'  => 'title',
					'id'    => 'advanced_options',
				),
				array(
					'title'             => __( 'Maximum Concurrent Requests', 'woocommerce-pos-host' ),
					'desc'              => __( 'Set the maximum number of API requests to the same endpoint', 'woocommerce-pos-host' ),
					'desc_tip'          => __( 'Use the maximum value for a faster loading experience.', 'woocommerce-pos-host' ),
					'id'                => 'pos_host_max_concurrent_requests',
					'default'           => '30',
					'type'              => 'number',
					'custom_attributes' => array(
						'min'  => '1',
						'max'  => '30',
						'step' => '1',
					),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'advanced_options',
				),
				array(
					'title' => __( 'Database', 'woocommerce-pos-host' ),
					'type'  => 'title',
					'id'    => 'database_options',
				),
				array( 'type' => 'database_options' ),
				array(
					'type' => 'sectionend',
					'id'   => 'database_options',
				),
			)
		);

		return apply_filters( 'pos_host_get_settings_' . $this->id, $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		$settings = $this->get_settings();
		POS_HOST_Admin_Settings::save_fields( $settings );
	}

	/**
	 * Prints out database options.
	 *
	 * @todo Move to a view file.
	 */
	public function output_database_options() {
		include dirname( __FILE__ ) . '/views/html-admin-page-advanced-database.php';
	}
}

return new POS_HOST_Admin_Settings_Advanced();
