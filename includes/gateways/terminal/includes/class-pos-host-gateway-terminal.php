<?php
/**
 * Terminal payment gateway via TRX HOST
 *
 * @since 0.0.1
 *
 * @package WooCommerce_pos_host/Gateways
 */

defined( 'ABSPATH' ) || exit;
/**
 * POS_HOST_Gateway_Terminal.
 */
class POS_HOST_Gateway_Terminal extends WC_Payment_Gateway {

	/**
	 * Constructor.
	 */
	public function __construct() { 
 	/**
	 * Includes.
	 */

		// Setup general properties.
		$this->id   = 'pos_host_terminal';
		$this->icon = apply_filters( 'pos_host_host_terminal_icon', '' );
		/* translators: %s gateway number */
		$this->method_title       = __( 'POS Terminal', 'woocommerce-pos-host' );
		$this->method_description = __( 'Payment on POS Terminals.', 'woocommerce-pos-host' );
		$this->has_fields         = false;

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Get settings.
		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->instructions       = $this->get_option( 'instructions' );
		$this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );
		$this->enable_for_virtual = $this->get_option( 'enable_for_virtual', 'yes' ) === 'yes';
		$this->supports           = array( 'products', 'woocommerce-pos-host' );
	       
                 add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                 add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );
                
	}
        

         /**
	 * Initialise gateway settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'                  => array(
				'title'       => __( 'Enable/Disable', 'woocommerce-pos-host' ),
				'label'       => __( 'Enable Terminal', 'woocommerce-pos-host' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'                    => array(
				'title'       => __( 'Title', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce-pos-host' ),
				'default'     => __( 'POS Terminal %s', 'woocommerce-pos-host' ),
				'desc_tip'    => true,
			),
			'description'              => array(
				'title'       => __( 'Description', 'woocommerce-pos-host' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your website.', 'woocommerce-pos-host' ),
				'default'     => __( 'Pay on your POS Terminals.', 'woocommerce-pos-host' ),
				'desc_tip'    => true,
			),
			'host_address'      => array(
				'title'       => __( 'Host Address', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'description' => __( 'Enter the Host Address.', 'woocommerce-pos-host' ),
				'default'     => 'spinpos.net/spin',
                                   'desc_tip'    => true,
			),
			'terminal_id'      => array(
				'title'       => __( 'Terminal ID', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'description' => __( 'Enter the Terminal ID.', 'woocommerce-pos-host' ),
				'desc_tip'    => true,
			),
			'security_key'           => array(
				'title'       => __( 'Security Key', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'description' => __( 'Enter the Security key.', 'woocommerce-pos-host' ),
				'desc_tip'    => true,
                          ),
			'timeout'           => array(
				'title'       => __( 'Connection Timeout', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'description' => __( 'Enter the timeout connection in seconds.', 'woocommerce-pos-host' ),
				'default'     => '30',
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
		
                 if ( empty( POS_HOST ) ) {
			return false;
		}
		return parent::is_available();
	}

        
}

