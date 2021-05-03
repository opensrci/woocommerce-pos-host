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
	private $terminals = array();
	private $api;

	/**
	 * Constructor.
	 */
	public function __construct() { 
 	/**
	 * Includes.
	 */
		self::includes();

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
                
                //process payment
                //add_action( 'woocommerce_pos_new_order', array( $this, 'pos_process_payment' ), 10 );
                // Customer Emails.
                add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}
        
        public static function includes() {
            	include_once 'includes/class-trx-host-api.php';
//		/include_once 'includes/class-trx-host-admin.php';
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
	 * Add content to the WC emails.
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
	 * Process Payment before save order
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
        public function pos_process_payment($order_id) {
                 if( !$order_id ) return false;
                 $trx_id = '';
                 
                 $order = wc_get_order( $order_id );
wp_die("Order:".var_dump($order), 410);

                 if ( $order->get_total() > 0 ) {
                    $api = new POS_HOST_Gateway_Trx_Host_API( $order->get_payment_method() );
                
                 $ret = $api->do_order( $order );
                 
                 //retry if failed
                 if( '0' != $ret['error_code']){
                    //comm error, need retrieve transaction id
                    sleep(30);
                    $ret = $api->retrieve_trx($order->get_id());

                 } 
                    
                 if ( '0' == $ret['result_code'] ){
                        //approved
                        $order->update_status( apply_filters( 'woocommerce_pos_host_trx_process_payment_order_status', $order->has_downloadable_item() ? 'on-hold' : 'processing', $order ),
                                    __( 'Payment completed.', 'woocommerce-pos-host' ) );
                        $trx_id = $ret['trx_id'];
                    }
                 }
                 
                 // $trx_id <> '', success, or failed.
                 $order->payment_complete( $trx_id );
                
                 return true;
                 
	}
        
}
