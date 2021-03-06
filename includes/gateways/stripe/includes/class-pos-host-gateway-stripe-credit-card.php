<?php
/**
 * Stripe credit card payment gateway
 *
 * @package WooCommerce_pos_host/Gateways
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Gateway_Stripe_Credit_Card.
 */
class POS_HOST_Gateway_Stripe_Credit_Card extends WC_Payment_Gateway {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id           = 'pos_stripe_credit_card';
		$this->icon         = apply_filters( 'pos_host_stripe_credit_card_icon', '' );
		$this->method_title = __( 'Stripe Credit Card', 'woocommerce-pos-host' );
		/* translators: %s url */
		$this->method_description = sprintf( __( 'All other general Stripe settings can be adjusted <a href="%s">here</a>.', 'woocommerce-pos-host' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=stripe' ) );
		$this->has_fields         = true;

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->supports    = array( 'products', 'woocommerce-pos-host' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialize gateway settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-pos-host' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Stripe Cedit Card', 'woocommerce-pos-host' ),
				'default' => 'no',
			),
			'title'       => array(
				'title'       => __( 'Title', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the clerk sees during checkout.', 'woocommerce-pos-host' ),
				'default'     => __( 'Stripe Credit Card', 'woocommerce-pos-host' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce-pos-host' ),
				'description' => __( 'This controls the description which the clerk sees during checkout.', 'woocommerce-pos-host' ),
				'type'        => 'textarea',
				'default'     => __( 'Pay with Credit Card.', 'woocommerce-pos-host' ),
				'desc_tip'    => true,
			),
		);
	}

	/**
	 * Check if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( ! function_exists( 'is_pos' ) || ! is_pos() ) {
			return false;
		}

		if ( is_checkout() ) {
			return false;
		}

		// if ( empty( $this->get_option( 'publishable_key' ) ) ) {
		// return false;
		// }

		return parent::is_available();
	}

	/**
	 * Display payment fields.
	 */
	public function payment_fields() {
		if ( function_exists( 'is_pos' ) && is_pos() ) {
			include POS_HOST()->plugin_path() . '/includes/gateways/stripe/includes/views/html-stripe-credit-card-panel.php';

			return;
		}

		parent::payment_fields();
	}
}
