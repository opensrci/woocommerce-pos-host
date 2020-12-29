<?php
/**
 * Point of Sale Customer Settings
 *
 * @package WooCommerce_pos_host/Classes/Admin/Settings
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Admin_Settings_Customer', false ) ) {
	return new POS_HOST_Admin_Settings_Customer();
}

/**
 * POS_HOST_Admin_Settings_Customer.
 */
class POS_HOST_Admin_Settings_Customer extends POS_HOST_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'customer';
		$this->label = __( 'Customer', 'woocommerce-pos-host' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			''                => __( 'Customer', 'woocommerce-pos-host' ),
			'payment_methods' => __( 'End of Sale', 'woocommerce-pos-host' ),
		);

		return apply_filters( 'woocommerce_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		global $woocommerce;
		if ( 'payment_methods' === $current_section ) {
			return apply_filters(
				'woocommerce_point_of_sale_payment_methods_settings_fields',
				array(
					array(
						'title' => __( 'End of Sale Actions', 'woocommerce-pos-host' ),
						'desc'  => __( 'The following options affect the actions presented at the end of the checkout process.', 'woocommerce-pos-host' ),
						'type'  => 'title',
						'id'    => 'wc_settings_customer',
					),

					array(
						'title'         => __( 'Signature Capture', 'woocommerce-pos-host' ),
						'desc'          => __( 'Enable signature capture', 'woocommerce-pos-host' ),
						'desc_tip'      => __( 'Presents a modal window to capture the signature of user or customer.', 'woocommerce-pos-host' ),
						'id'            => 'pos_host_signature',
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => 'start',
					),
					array(
						'title'         => __( 'Signature Required', 'woocommerce-pos-host' ),
						'desc'          => __( 'Enforce capturing of signature', 'woocommerce-pos-host' ),
						'desc_tip'      => __( 'Allows you to force user to enter signature before proceeding with register commands.', 'woocommerce-pos-host' ),
						'id'            => 'pos_host_signature_required',
						'class'         => 'pos_signature',
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => 'start',
					),
					array(
						'title'    => __( 'Signature Commands', 'woocommerce-pos-host' ),
						'desc_tip' => __( 'Choose which commands would you like the signature panel to be shown for.', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_signature_required_on',
						'class'    => 'wc-enhanced-select pos_signature',
						'default'  => 'pay',
						'type'     => 'multiselect',
						'options'  => array(
							'pay'  => __( 'Pay', 'woocommerce-pos-host' ),
							'save' => __( 'Hold', 'woocommerce-pos-host' ),
						),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'wc_settings_customer',
					),

				)
			);
		} else {
			return apply_filters(
				'woocommerce_point_of_sale_general_settings_fields',
				array(
					array(
						'title' => __( 'Customer Options', 'woocommerce-pos-host' ),
						'desc'  => __( 'The following options affect the account creation process when creating customers.', 'woocommerce-pos-host' ),
						'type'  => 'title',
						'id'    => 'wc_settings_customer_end_of_sale',
					),
					array(
						'name'     => __( 'Cache Customers', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_cache_customers',
						'type'     => 'checkbox',
						'desc'     => __( 'Enable caching of customer data', 'woocommerce-pos-host' ),
						'desc_tip' => __( 'Check this box to load all customer data onto the register upon initialisation.', 'woocommerce-pos-host' ),
						'default'  => 'yes',
						'autoload' => true,
					),
					array(
						'title'    => __( 'Default Country', 'woocommerce-pos-host' ),
						'desc_tip' => __( 'Sets the default country for shipping and customer accounts.', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_default_country',
						'css'      => 'min-width:350px;',
						'default'  => 'GB',
						'type'     => 'single_select_country',
					),
					array(
						'name'     => __( 'Guest Checkout', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_guest_checkout',
						'type'     => 'checkbox',
						'desc'     => __( 'Enable guest checkout', 'woocommerce-pos-host' ),
						'desc_tip' => __( 'Allows register cashiers to process and fulfil an order without choosing a customer.', 'woocommerce-pos-host' ),
						'default'  => 'yes',
						'autoload' => true,
					),
					array(
						'title'         => __( 'Customer Cards', 'woocommerce-pos-host' ),
						'desc'          => __( 'Enable customer cards', 'woocommerce-pos-host' ),
						'desc_tip'      => __( 'Allow the ability to scan customers cards to load their account instantly.', 'woocommerce-pos-host' ),
						'id'            => 'pos_host_enable_user_card',
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => 'start',
					),
					array(
						'name'     => __( 'Required Fields', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_customer_create_required_fields',
						'type'     => 'multiselect',
						'class'    => 'wc-enhanced-select-required-fields',
						'desc_tip' => __( 'Select the fields that are required when creating a customer through the register.', 'woocommerce-pos-host' ),
						'options'  => array(
							'billing_first_name'  => __( 'Billing First Name', 'woocommerce-pos-host' ),
							'billing_last_name'   => __( 'Billing Last Name', 'woocommerce-pos-host' ),
							'billing_email'       => __( 'Billing Email', 'woocommerce-pos-host' ),
							'billing_company'     => __( 'Billing Company', 'woocommerce-pos-host' ),
							'billing_address_1'   => __( 'Billing Address 1', 'woocommerce-pos-host' ),
							'billing_address_2'   => __( 'Billing Address 2', 'woocommerce-pos-host' ),
							'billing_city'        => __( 'Billing City', 'woocommerce-pos-host' ),
							'billing_state'       => __( 'Billing State', 'woocommerce-pos-host' ),
							'billing_postcode'    => __( 'Billing Postcode', 'woocommerce-pos-host' ),
							'billing_country'     => __( 'Billing Country', 'woocommerce-pos-host' ),
							'billing_phone'       => __( 'Billing Phone', 'woocommerce-pos-host' ),
							'shipping_first_name' => __( 'Shipping First Name', 'woocommerce-pos-host' ),
							'shipping_last_name'  => __( 'Shipping Last Name', 'woocommerce-pos-host' ),
							'shipping_company'    => __( 'Shipping Company', 'woocommerce-pos-host' ),
							'shipping_address_1'  => __( 'Shipping Address 1', 'woocommerce-pos-host' ),
							'shipping_address_2'  => __( 'Shipping Address 2', 'woocommerce-pos-host' ),
							'shipping_city'       => __( 'Shipping City', 'woocommerce-pos-host' ),
							'shipping_state'      => __( 'Shipping State', 'woocommerce-pos-host' ),
							'shipping_postcode'   => __( 'Shipping Postcode', 'woocommerce-pos-host' ),
							'shipping_country'    => __( 'Shipping Country', 'woocommerce-pos-host' ),
						),
						'default'  => array(
							'billing_first_name',
							'billing_last_name',
							'billing_email',
							'billing_address_1',
							'billing_city',
							'billing_state',
							'billing_postcode',
							'billing_country',
							'billing_phone',
						),
					),
					array(
						'name'     => __( 'Optional Fields', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_hide_not_required_fields',
						'type'     => 'checkbox',
						'desc'     => __( 'Hide optional fields when adding customer', 'woocommerce-pos-host' ),
						'desc_tip' => __( 'Optional fields will not be shown to make capturing of customer data easier for the cashier.', 'woocommerce-pos-host' ),
						'default'  => 'no',
						'autoload' => true,
					),
					array(
						'title'         => __( 'Save Customer', 'woocommerce-pos-host' ),
						'desc'          => __( 'Toggle save customer by default', 'woocommerce-pos-host' ),
						'desc_tip'      => __( 'Check this to turn on the Save Customer toggle by default.', 'woocommerce-pos-host' ),
						'id'            => 'pos_host_save_customer_default',
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => 'start',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'wc_settings_customer_end_of_sale',
					),

				)
			); // End general settings
		}
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;
		$settings = $this->get_settings( $current_section );

		POS_HOST_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;
		$settings = $this->get_settings( $current_section );
		POS_HOST_Admin_Settings::save_fields( $settings );
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
			echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=pos-host-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) ) . '" class="' . esc_attr( $current_section === $id ? 'current' : '' ) . '">' . esc_html( $label ) . '</a> ' . ( end( $array_keys ) === $id ? '' : '|' ) . ' </li>';
		}

		echo '</ul><br class="clear" />';
	}
}

return new POS_HOST_Admin_Settings_Customer();
