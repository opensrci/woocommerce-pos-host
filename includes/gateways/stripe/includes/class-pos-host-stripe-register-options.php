<?php
/**
 * Stripe for POS.
 *
 * @package WooCommerce_pos_host/Gateways
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Stripe.
 */
class POS_HOST_Stripe_register {

	/**
	 * Init.
	 */
	public static function init() {
		self::includes();
		self::add_ajax_events();

                 /*
                  * add stripe terminal option to register's options
                  * 
                  */                       
		add_filter( 'pos_host_register_options_tabs', array( __CLASS__, 'register_options_tabs' ) );
		add_action( 'pos_host_register_options_panels', array( __CLASS__, 'register_options_panels' ), 10, 2 );
		add_action( 'pos_host_register_options_save', array( __CLASS__, 'save_register_data' ), 10, 2 );
		add_filter( 'pos_host_register_data', array( __CLASS__, 'add_register_data' ) );

	}

	/**
	 * Includes.
	 */
	public static function includes() {
	}


	/**
	 * Hook in methods.
	 */
	public static function add_ajax_events() {
	}


	/**
	 * Add Stripe Terminal tab to the register data meta box.
	 *
	 * @param array $tabs
	 * @return array
	 */
	public static function register_options_tabs( $tabs ) {
		$stripe_terminal_data = get_option( 'woocommerce_pos_stripe_terminal_settings', array() );
		$enabled              = ! empty( $stripe_terminal_data['enabled'] ) && 'yes' === $stripe_terminal_data['enabled'];

		if ( $enabled ) {
			$tabs['stripe_terminal'] = array(
				'label'  => __( 'Stripe Terminal', 'woocommerce-pos-host' ),
				'target' => 'stripe_terminal_register_options',
				'class'  => '',
			);
		}

		return $tabs;
	}

	/**
	 * Display the Stripe Terminal tab content.
	 *
	 * @param int             $thepostid
	 * @param POS_HOST_Register $register
	 */
	public static function register_options_panels( $thepostid, $register_object ) {
		include_once 'views/html-admin-register-options-stripe-terminal.php';
	}

	/**
	 * On save register data.
	 *
	 * @param int             $post_id
	 * @param POS_HOST_Register $register
	 */
	public static function save_register_data( $post_id, $register ) {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'update-post_' . $post_id ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce-pos-host' ) );
		}

		$terminal = ! empty( $_POST['stripe_terminal'] ) ? wc_clean( wp_unslash( $_POST['stripe_terminal'] ) ) : 'none';
		update_post_meta( $post_id, 'stripe_terminal', $terminal );
	}

	/**
	 * Add Stripe Terminal data to register data.
	 *
	 * @param array $register_data
	 * @return array
	 */
	public static function add_register_data( $register_data ) {
		$register_data['stripe_terminal'] = get_post_meta( $register_data['id'], 'stripe_terminal', true );

		return $register_data;
	}
}

POS_HOST_Stripe_register::init();
