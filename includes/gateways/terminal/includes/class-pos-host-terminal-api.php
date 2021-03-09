<?php
/**
 * Trx Host Terminals API
 * This API will store the Payment intent information
 * @package WooCommerce_POS_HOST/Gateways
 */

defined( 'ABSPATH' ) || exit;
/**
 * POS_HOST_Gateway_Trx_Host_API.
 */
class POS_HOST_Gateway_Trx_Host_API {
    /* singleton for each payament method
     * 
     */
    private static $instance = [];
    private static $params = [];
    /* params
        $base_url = '';
        $timeout  = '';
        $tpn  = '';
        $auth_key  = '';
    payload
      $payload= array( 
            'TPN'=>'',
            'AuthKey'=>'',
            'Timeout'=>'',
            'PaymentType'=>'',
            'TransType'=>'',
            'InvNum'=>'',
            'Amount'=>'',
            'Tip'=>'',
            'RefId'=>'',
            'AuthCode'=>'',
            'ClerkId'=>'',
            'TableNum'=>'',
            'TicketNum'=>'',
            'CashbackAmount'=>'',
            'PrintReceipt'=>'',
            );
     * 
     */
        /**
	 * Constructor.
          */
	public function __construct() {
	}
        
        private function init($method){
            //load settings
            $settings = get_option("woocommerce_".$method."_settings",array());
            $this->params[$method][base_url] = $settings['host_address'];
            $this->params[$method][timeout] =  $settings['timeout']; 
            $this->params[$method][tpn] =  $settings['terminal_id'];
            $this->params[$method][auth_key] =  $settings['security_key']; 

        }
        /* returen Instance id for each payment method
         * @param id
         * @
         */
        public static function getInstance($method)
        {
            /* only work for pos terminal method
             * 
             */
            if ( false === strpos( $method, "pos_host_terminal" ) ) return; 
            
            if (self::$instance == null)
              {
                self::$instance[$method] = new POS_HOST_Gateway_Trx_Host_API();
                self::init( $method );
              }

              return self::$instance;
        }
              /**
	 * Returns API URL.
	 *
	 * @return string
	 */
	protected function get_base_url() {
		$url = esc_url( $this->base_url );
		if ( empty( $url ) ) {
			return '';
		}

		$parsed = parse_url( $url );

		if ( 'http' === $parsed['scheme'] ) {
			$url = str_replace( 'http', 'https', $url );
		}

		return trailingslashit( $url );
	}

        /**
	 * connect the terminal
	 *
	 * @return array|WP_Error
	 */
        public function connect_terminal() {
            /*@todo need support local connection */
            $ret ['success'] = true;
            $ret ['data']['connection'] ='remote';
            return $ret;
        }
        /**
	 * Process a sale payment by data.
	 *
	 * @param array $payload data
	 * @return array|WP_Error
	 */
        public function process_payment( $id ) {
            $order = wc_get_order( $order_id );
            $ret = array(
                'success'=> false,
                'data'=>null,
            );
            
            if (is_wp_error($order)){
                $ret['data'] = $order->get_error_message();
                return $ret;
            }
            
            $payload = array();
            $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><request></request>");
            
            $payload['TPN'] = $this->tpn;
            $payload['AuthKey'] = $this->auth_key;
            $payload['TransType'] =  'Sale';
            $payload['PaymentType'] =  'Card'; 
            $payload['InvNum'] =  $order->get_id(); 
            $payload['RefId']  =  $order->get_id(); 
            $payload['Amount'] =  $order->get_total(); 
     
            foreach ( $payload as $k => $v ) {
                if ($v)
                    $xml->addChild("$k",esc_html("$v"));
            }
            
            $url = $this->get_base_url()."spin/Transaction";
            $options = [
                'method'     => 'POST',
                'body'       => $xml->asXML(),
                'timeout'    => $this->timeout,
                'headers'    => [
                    'Content-Type' => 'application/xml',
                ],
                'sslverify'   => false,
            ];

            $result = wp_remote_post( $url, $options );
            if ( is_wp_error($result)){
                //remote post error
               $ret['data'] = $result->get_error_message();
            }else{
               $xml = simplexml_load_string($result['body']);
               
               $ret['sucess'] = true;
               $ret['data']['result_code'] = (int)$xml->response->ResultCode;
               $ret['data']['result_msg'] = (string)$xml->response->Message;
               $ret['data']['ref_id'] = (string)$xml->response->PNRef;
                
            } 
            return $ret;
        }
        /**
	 * Retrieve a payment by RefId.
	 *
	 * @param array $payload data
	 * @return array|WP_Error
	 */
        public function capture_payment( $id ) {
            if (!$RefId) return false;
            
            $ret = array(
                'success'=> false,
                'data'=>null,
            );
            $payload = $req = array();
            
            $payload['TPN'] = $this->tpn;
            $payload['AuthKey'] = $this->auth_key;
            $payload['TransType'] =  'Status';
            $payload['PaymentType'] =  'Card';
            $payload['RefId']  =  $RefId; 
     
            $req_str ="<request>";
             foreach ( $payload as $k => $v ) {
                if ($v)
                    //<key>val</key>
                    $req_str .="<".$k.">".$v."</".$k.">";
            }
            $req_str .="</request>";
            //remove xml version tag
            //$req['TerminalTransaction'] = $req_str; 
            $url = $this->get_base_url()."spin/cgi.html?TerminalTransaction=".$req_str;
            
            $result = wp_remote_get( $url,'');

            if ( is_wp_error($result)){
                //remote post error
               $ret['error_msg'] = $result->get_error_message();
               $ret['error_code'] = $result->get_error_code();                
            }else{
               $xml = new SimpleXMLElement();
               $xml = simplexml_load_string($result['body']);
               $ret['result_code'] = (int)$xml->response->ResultCode;
               $ret['result_msg'] = (string)$xml->response->Message;
               $ret['trx_id'] = (string)$xml->response->PNRef;
                
            } 
           
            return $ret;
        }
 }
