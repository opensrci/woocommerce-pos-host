<?php
/**
 * trx_host for POS.
 *
 * @package WooCommerce_Point_Of_Sale/Gateways
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_trx_host.
 */
class POS_HOST_trx_host {

	/**
	 * Init.
	 */
	public static function init() {
		self::includes();

		add_filter( 'pos_host_params', array( __CLASS__, 'params' ) );
		add_filter( 'pos_host_register_options_tabs', array( __CLASS__, 'register_options_tabs' ) );
		add_action( 'pos_host_register_options_panels', array( __CLASS__, 'register_options_panels' ), 10, 2 );
		add_action( 'pos_host_register_options_save', array( __CLASS__, 'save_register_data' ), 10, 2 );
		add_filter( 'pos_host_register_data', array( __CLASS__, 'add_register_data' ) );
	}

	/**
	 * Includes.
	 */
	public static function includes() {
		include_once 'includes/class-trx-host-api.php';
		include_once 'includes/class-trx-host-gateway.php';
	}

	/**
	 * Add gateway params.
	 *
	 * @param array $params
	 * @return array
	 */
	public static function params( $params ) {
		$trx_host_data = get_option( 'woocommerce_pos_trx_host_settings', array() );

		$params['trx_host_host_address']      = isset( $trx_host_data['host_address'] ) ? set_url_scheme( esc_url( $trx_host_data['host_address'] ), 'https' ) : '';
		$params['trx_host_security_key']           = isset( $trx_host_data['security_key'] ) ? $trx_host_data['security_key'] : '';
		$params['trx_host_merchant_id']      = isset( $trx_host_data['merchant_id'] ) ? $trx_host_data['merchant_id'] : '';
		
		return $params;
	}

	/**
	 * Add trx_host tab to the register data meta box.
	 *
	 * @param array $tabs
	 * @return array
	 */
	public static function register_options_tabs( $tabs ) {
		$trx_host_data = get_option( 'woocommerce_pos_trx_host_settings', array() );

		if ( isset( $trx_host_data['enabled'] ) && 'yes' === $trx_host_data['enabled'] ) {
			$tabs['trx_host'] = array(
				'label'  => __( 'Payment Terminal', 'woocommerce-pos-host' ),
				'target' => 'trx_host_register_options',
				'class'  => '',
			);
		}

		return $tabs;
	}

	/**
	 * Display the trx_host tab content.
	 *
	 * @param int             $thepostid
	 * @param POS_HOST_Register $register
	 */
	public static function register_options_panels( $thepostid, $register_object ) {
		include_once 'includes/views/html-admin-register-options-trx-host.php';
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

		$terminal = ! empty( $_POST['trx_host_terminal'] ) ? wc_clean( wp_unslash( $_POST['trx_host_terminal'] ) ) : 'none';
		update_post_meta( $post_id, 'trx_host_terminal', $terminal );
	}

	/**
	 * Add trx_host data to register data.
	 *
	 * @param array $register_data
	 * @return array
	 */
	public static function add_register_data( $register_data ) {
		$register_data['trx_host_terminal'] = get_post_meta( $register_data['id'], 'trx_host_terminal', true );

		return $register_data;
	}

}

POS_HOST_trx_host::init();
