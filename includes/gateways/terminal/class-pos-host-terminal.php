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
	 * Gateway number.
	 *
	 * This is to allow having multiple Terminal gateways.
	 *
	 * @var int Number.
	 */
	public static $number = 0;
	protected $terminals = array();

	/**
	 * Constructor.
	 */
	public function __construct() { 
 	/**
	 * Includes.
	 */
		self::includes();
		self::add_ajax_events();
                
		if ( intval( get_option( 'pos_host_terminal_gateways_number', 1 ) ) === self::$number ) {
			self::$number = 0;
		}

		// Terminal ID.
		self::$number++;

		// Setup general properties.
		//$this->id   = 1 === self::$number ? 'pos_host_terminal' : 'pos_host_terminal_' . self::$number;
		$this->id   = 'pos_host_terminal_' . self::$number;
		$this->icon = apply_filters( 'pos_host_host_terminal_icon', '' );
		/* translators: %s gateway number */
		$this->method_title       = sprintf( __( 'POS Terminal %s', 'woocommerce-pos-host' ), $this->process_gateway_number( self::$number ) );
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
		add_filter( 'pos_host_params', array( $this, 'params' ) );
                
                //process payment
                //add_action( 'woocommerce_pos_new_order', array( $this, 'pos_process_payment' ), 10 );
                // Customer Emails.
                add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}
        
        public static function includes() {
            	include_once 'includes/class-pos-host-terminal-api.php';
	}

        	/**
	 * Hook in methods.
	 */
	public static function add_ajax_events() {
		$ajax_events_nopriv = array(
			'connect_terminal',
			'terminal_capture_payment',
			'terminal_process_payment',
		);

		foreach ( $ajax_events_nopriv as $ajax_event ) {
			add_action( 'wp_ajax_pos_host_' . $ajax_event, array( __CLASS__, 'ajax_' . $ajax_event ) );
			add_action( 'wp_ajax_nopriv_pos_host_' . $ajax_event, array( __CLASS__, 'ajax_' . $ajax_event ) );
		}
	}

         /**
	 * Initialise gateway settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'                  => array(
				'title'       => __( 'Enable/Disable', 'woocommerce-pos-host' ),
				/* translators: %s gateway number */
				'label'       => sprintf( __( 'Enable Terminal %s', 'woocommerce-pos-host' ), $this->process_gateway_number( self::$number ) ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'                    => array(
				'title'       => __( 'Title', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce-pos-host' ),
				/* translators: %s gateway number */
				'default'     => sprintf( __( 'POS Terminal %s', 'woocommerce-pos-host' ), $this->process_gateway_number( self::$number ) ),
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

		return parent::is_available();
	}

	/**
	 * Ajax: connect terminal payment.
	 */
	public static function ajax_connect_terminal() {
                //
                //@todo debug
                //check_ajax_referer( 'pos-host-terminal', 'security' );
                $payment_method  = isset( $_POST['payment_method'] ) ? wc_clean( wp_unslash( $_POST['payment_method'] ) ) : '';
//@todo debug
wp_die("connect terminal".$payment_method, 490);
                if ( !$payment_method ){
                    wp_send_json_error( 'empty payment method.' );
                }
                $api = new POS_HOST_Stripe_API($payment_method);
                $ret = $api->connect_terminal();
                return( json_encode($ret) );
	}

	/**
	 * Ajax: process payment.
          *  @param post[$order_id] - the woocommerce order id
	 */
	public static  function ajax_terminal_process_payment() {
                //
                //@todo debug
                //check_ajax_referer( 'pos-host-terminal', 'security' );

                $id  = isset( $_POST['order_id'] ) ? wc_clean( wp_unslash( $_POST['order_id'] ) ) : '';
                $payment_method  = isset( $_POST['payment_method'] ) ? wc_clean( wp_unslash( $_POST['payment_method'] ) ) : '';
                if ( !$id || !$payment_method ){
                    wp_send_json_error( 'empty order_id or payment method.' );
                }
                
                $api = new POS_HOST_Stripe_API($payment_method);
                $ret = $api->process_payment($id);
                return( json_encode($ret) );
	}

	/**
	 * Ajax: capture payment.
          *  @param post[refId] - 
	 */
	public static  function ajax_terminal_capture_payment() {
                //
                //@todo debug
                //check_ajax_referer( 'pos-host-terminal', 'security' );

                $id  = isset( $_POST['refId'] ) ? wc_clean( wp_unslash( $_POST['refId'] ) ) : '';
                $payment_method  = isset( $_POST['payment_method'] ) ? wc_clean( wp_unslash( $_POST['payment_method'] ) ) : '';
                if ( !$id || !$payment_method ){
                    wp_send_json_error( 'empty intentId or payment method.' );
                }
                
                $api = new POS_HOST_Stripe_API($payment_method);
                $ret = $api->capture_payment( $id );
                return( json_encode($ret) );
	}

	/**
	 * Change payment complete order status to completed for Terminal orders.
	 *
	 * @param  string         $status Current order status.
	 * @param  int            $order_id Order ID.
	 * @param  WC_Order|false $order Order object.
	 *
	 * @return string
	 */
	public function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
		if ( $order && $this->id === $order->get_payment_method() ) {
			$status = 'completed';
		}

		return $status;
	}

	/**
	 * Add content to the WC emails. (no use)
	 *
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin  Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && $this->id === $order->get_payment_method() ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
		}
	}

	/**
	 * Returns the payment gateway number string.
	 *
	 * @param int  $number        Gateway number.
	 * @param bool $leading_space Whether to add a leading space.
	 *
	 * @return string
	 */
	private function process_gateway_number( $number, $leading_space = true ) {
		/*
                if ( 1 === $number ) {
			return '';
		}
                 */

		return $leading_space ? ' ' . $number : $number;
	}

	/**
	 * Add gateway params.
	 *
	 * @param array $params
	 * @return array
	 */
	public function params( $params ) {
		$params['pos_host_terminal_process_payment_nonce']  = wp_create_nonce( 'pos-host-terminal-process-payment' );
		$params['pos_host_connect_terminal_nonce']  = wp_create_nonce( 'pos-host-connect-terminal' );
		$params['pos_host_terminal_capture_payment_nonce']  = wp_create_nonce( 'pos-host-terminal-capture-payment' );

                return $params;
	}

        
}
POS_HOST_Gateway_Terminal::init();
