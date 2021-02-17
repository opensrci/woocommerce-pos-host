<?php
/**
 * Pay on Delivery (COD) payment gateway
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

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( intval( get_option( 'pos_host_number_terminal_gateways', 1 ) ) === self::$number ) {
			self::$number = 0;
		}

		// Gateway ID.
		self::$number++;

		// Setup general properties.
		$this->setup_properties();

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

		// Customer Emails.
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Setup general properties for the gateway.
	 */
	protected function setup_properties() {
		$this->id   = 1 === self::$number ? 'pos_host_terminal' : 'pos_host_terminal_' . self::$number;
		$this->icon = apply_filters( 'pos_host_host_terminal_icon', '' );
		/* translators: %s gateway number */
		$this->method_title       = sprintf( __( 'POS Cash on Delivery (POS-COD) %s', 'woocommerce-pos-host' ), $this->process_gateway_number( self::$number ) );
		$this->method_description = __( 'Pay cash or other means on Delivery for POS gateway.', 'woocommerce-pos-host' );
		$this->has_fields         = false;
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
				'default'     => sprintf( __( 'Terminal %s', 'woocommerce-pos-host' ), $this->process_gateway_number( self::$number ) ),
				'desc_tip'    => true,
			),
			'description'              => array(
				'title'       => __( 'Description', 'woocommerce-pos-host' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on your website.', 'woocommerce-pos-host' ),
				'default'     => __( 'Pay cash or other means on Delivery for POS.', 'woocommerce-pos-host' ),
				'desc_tip'    => true,
			),
			'require_reference_number' => array(
				'title'       => __( 'Reference Number', 'woocommerce-pos-host' ),
				'type'        => 'checkbox',
				'label'       => __( 'Require reference number', 'woocommerce-pos-host' ),
				'description' => __( 'Check this box to make the reference number mandatory filed.', 'woocommerce-pos-host' ),
				'default'     => 'no',
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
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'pos_page' !== $screen->id ) {
			return false;
		}

		return parent::is_available();
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$order->update_status( apply_filters( "woocommerce_{$this->id}_process_payment_order_status", 'completed', $order ), __( 'Payment to be made upon delivery.', 'woocommerce-pos-host' ) );

		// Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'  => 'success',
			'message' => __( 'Success!', 'woocommerce-pos-host' ),
		);
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
	 * @since 5.2.7
	 *
	 * @return string
	 */
	private function process_gateway_number( $number, $leading_space = true ) {
		if ( 1 === $number ) {
			return '';
		}

		return $leading_space ? ' ' . $number : $number;
	}
}
