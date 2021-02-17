<?php
/**
 * The Trx Host Gateway For Terminals
 *
 * @package WooCommerce_pos_host/Gateways
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Gateway_trx_host.
 */
class POS_HOST_Gateway_trx_host extends WC_Payment_Gateway {

	private $api;
	private $terminals = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                 = 'pos_host_trx_host';
		$this->icon               = apply_filters( 'pos_host_trx_host_icon', '' );
		$this->method_title       = __( 'Payment Terminal', 'woocommerce-pos-host' );
		$this->method_description = __( 'Take payments in person via EMV Terminals. More commonly known as Chip & PIN terminals.', 'woocommerce-pos-host' );
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
		$this->api                = new POS_HOST_Gateway_Trx_Host_API();
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'change_payment_complete_order_status' ), 10, 3 );
                add_action( 'woocommerce_pos_new_order', array( $this, 'pos_process_payment' ), 10 );
	}

	/**
	 * Initialize gateway settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'           => array(
				'title'       => __( 'Enable/Disable', 'woocommerce-pos-host' ),
				'label'       => __( 'Enable POS Terminals', 'woocommerce-pos-host' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'             => array(
				'title'       => __( 'Title', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-pos-host' ),
				'default'     => __( 'Payment Terminals', 'woocommerce-pos-host' ),
				'desc_tip'    => true,
			),
			'description'       => array(
				'title'       => __( 'Description', 'woocommerce-pos-host' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your website.', 'woocommerce-pos-host' ),
				'default'     => __( 'Take payments in person via EMV Terminals. More commonly known as Chip & PIN terminals.', 'woocommerce-pos-host' ),
				'desc_tip'    => true,
			),
			'credentials'       => array(
				'title'       => __( 'Credentials', 'woocommerce-pos-host' ),
				'type'        => 'title',
				'description' => __( 'Enter the settings given to you by Payment POS terminal providers when setting up your account. This includes the Host Address and an API Key.', 'woocommerce-pos-host' ),
			),
			'merchant_id'      => array(
				'title'       => __( 'Merchant ID', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'description' => __( 'Enter the Merchant ID.', 'woocommerce-pos-host' ),
				'desc_tip'    => true,
			),
			'host_address'      => array(
				'title'       => __( 'Host Address', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'description' => __( 'Enter the Host Address.', 'woocommerce-pos-host' ),
				'desc_tip'    => true,
			),
			'security_key'           => array(
				'title'       => __( 'Security Key', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'description' => __( 'Enter the Security key.', 'woocommerce-pos-host' ),
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
	 * Displays admin options.
	 *
	 * @return string Admin options.
	 */
	public function admin_options() {
		$this->test_connection();
		$this->display_errors();

		parent::admin_options();

		if ( count( $this->terminals ) ) :
			?>
		<h3 class="wc-settings-sub-title"><?php esc_html_e( 'Available Terminals', 'woocommerce-pos-host' ); ?></h3>
		<ol>
			<?php
			foreach ( $this->terminals as $terminal ) {
				echo '<li>' . esc_html( $terminal['tid'] ) . '</li>';
			}
			?>
		</ol>
			<?php
		endif;
	}


        /**
	 * Process Payment before save order
	 *
	 *        return array(
	 *            'result'   => 'success',
	 *            'redirect' => $this->get_return_url( $order )
	 *        );
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
        public function pos_process_payment($order_id) {
            

                 $ret = array(
                      'result'   => 'success',
	             'redirect' => $this->get_return_url( $order )
                      );  
		$order = wc_get_order( $order_id );

		if ( $order->get_total() > 0 ) {
                    $this->api->test();
wp_die("api:".var_dump($this->api->test()),488 );                        
       
                        // Mark as completed.
                        $order->update_status( apply_filters( 'woocommerce_pos_host_trx_process_payment_order_status', $order->has_downloadable_item() ? 'on-hold' : 'processing', $order ),
                                __( 'Payment completed.', 'woocommerce-pos-host' ) );
                        
		} else {
                        $order->payment_complete();
		}
//wp_die("pos process payment.".$order_id,487);         
                    
                return $ret;
	}
	/**
	 * Display payment fields.
	 */
	public function payment_fields() {
		if ( function_exists( 'is_pos' ) && is_pos() ) {
			include_once 'views/html-trx-host-payment-panel.php';

			return;
		}

		parent::payment_fields();
	}
        
}
