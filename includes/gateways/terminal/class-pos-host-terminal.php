<?php
/**
 * POS HOST Terminal 
 *
 * @since 0.0.1
 *
 * @package WooCommerce_pos_host/Gateways
 */

defined( 'ABSPATH' ) || exit;
/**
 * POS_HOST_Terminal.
 */
class POS_HOST_Terminal {

	/**
	 * Constructor.
	 */
	public static function init() { 
 	/**
	 * Includes.
	 */
		self::includes();
		self::add_ajax_events();
		add_filter( 'pos_host_params', array(  __CLASS__, 'params' ) );
	}
        
        public static function includes() {
            	include_once 'includes/class-pos-host-terminal-api.php';
            	include_once 'includes/class-pos-host-gateway-terminal.php';
	}

        	/**
	 * Hook in methods.
	 */
	public static function add_ajax_events() {
		$ajax_events_nopriv = array(
			'connect_terminal',
			'terminal_capture_payment',
			'terminal_process_payment',
			'terminal_process_refund',
		);

		foreach ( $ajax_events_nopriv as $ajax_event ) {
			add_action( 'wp_ajax_pos_host_' . $ajax_event, array( __CLASS__, 'ajax_' . $ajax_event ) );
			add_action( 'wp_ajax_nopriv_pos_host_' . $ajax_event, array( __CLASS__, 'ajax_' . $ajax_event ) );
		}
	}

	/**
	 * Ajax: connect terminal payment.
	 */
	public static function ajax_connect_terminal() {
                check_ajax_referer( 'pos-host-connect-terminal', 'security' );

            $api = new POS_HOST_Gateway_Terminal_API();
                if ( $ret = $api->connect_terminal() ){
                    wp_send_json_success($ret);
                }else{
                    wp_send_json_error( 'Terminal connection error.' );
                }
         
                return( $ret );
	}

	/**
	 * Ajax: process payment.
          *  @param post[$order_id] - the woocommerce order id
	 */
	public static  function ajax_terminal_process_payment() {
                check_ajax_referer( 'pos-host-terminal-process-payment', 'security' );

                $id  = isset( $_POST['id'] ) ? wc_clean( wp_unslash( $_POST['id'] ) ) : '';
                if ( !$id ){
                    wp_send_json_error( 'empty id.' );
                }
                
                $api = new POS_HOST_Gateway_Terminal_API();
                if ( $ret = $api->process_payment($id) ){
                    wp_send_json_success( $ret);
                }else{
                    wp_send_json_error( 'Terminal process payment error.' );
                }
	}

	/**
	 * Ajax: process payment.
          *  @param post[order_id] - the woocommerce order id
	 */
	public static  function ajax_terminal_process_refund() {
                check_ajax_referer( 'pos-host-terminal-process-refund', 'security' );
                $refund_order = array();
                //InvNum as orderId
                $refund_order['InvNum']   = isset( $_POST['id'] ) ? wc_clean( wp_unslash( $_POST['id'] ) ) : '';
                $refund_order['RefId']   = isset( $_POST['transaction_id'] ) ? wc_clean( wp_unslash( $_POST['transaction_id'] ) ) : '';
                $refund_order['Amount']   = isset( $_POST['amount'] ) ? wc_clean( wp_unslash( $_POST['amount'] ) ) : '';
                if ( !$refund_order['InvNum'] ){
                    wp_send_json_error( 'empty id.' );
                }
                
                $api = new POS_HOST_Gateway_Terminal_API();
                if ( $ret = $api->process_refund($refund_order) ){
                    wp_send_json_success( $ret);
                }else{
                    wp_send_json_error( 'Terminal process refund error.' );
                }
	}

	/**
	 * Ajax: capture payment.
          *  @param post[refId] - 
	 */
	public static  function ajax_terminal_capture_payment() {
                check_ajax_referer( 'pos-host-terminal-capture-payment', 'security' );

                $id  = isset( $_POST['refId'] ) ? wc_clean( wp_unslash( $_POST['refId'] ) ) : '';
                if ( !$id ){
                    wp_send_json_error( 'empty refId.' );
                }
                
                $api = new POS_HOST_Gateway_Terminal_API();
                if ( $ret = $api->capture_payment( $id ) ){
                    wp_send_json_success($ret);
                }else{
                     $err = var_dump($ret);
                    wp_send_json_error( 'Terminal process payment error.'.$err );
                }
	}

	/**
	 * Change payment complete order status to completed for Terminal orders.
	 *
	 * @param  string         $status Current order status.
	 * @param  int            $order_id Order ID.
	 * @param  WC_Order|false $order Order object.
	 *
	 * @return string
	public static function change_payment_complete_order_status( $status, $order_id = 0, $order = false ) {
		if ( $order && $this->id === $order->get_payment_method() ) {
			$status = 'completed';
		}

		return $status;
	}

	*/

	/**
	 * Add Terminal params.
	 *
	 * @param array $params
	 * @return array
	 */
	public static function params( $params ) {
		$params['pos_host_terminal_process_payment_nonce']  = wp_create_nonce( 'pos-host-terminal-process-payment' );
		$params['pos_host_terminal_process_refund_nonce']  = wp_create_nonce( 'pos-host-terminal-process-refund' );
		$params['pos_host_connect_terminal_nonce']  = wp_create_nonce( 'pos-host-connect-terminal' );
		$params['pos_host_terminal_capture_payment_nonce']  = wp_create_nonce( 'pos-host-terminal-capture-payment' );

                return $params;
	}

        
}
POS_HOST_Terminal::init();
