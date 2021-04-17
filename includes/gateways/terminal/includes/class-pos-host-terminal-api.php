<?php
/**
 * Trx Host Terminals API
 * This API will store the Payment intent information
 * @package WooCommerce_POS_HOST/Gateways
 */

defined( 'ABSPATH' ) || exit;
/**
 * POS_HOST_Gateway_Terminal_API.
 */
class POS_HOST_Gateway_Terminal_API {
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
            self::init();
	}
        
        private function init(){
            //load settings
            $settings = get_option("woocommerce_pos_host_terminal_settings",array());
            $this->params['base_url'] = $settings['host_address'];
            $this->params['timeout'] =  $settings['timeout']; 
            $this->params['tpn'] =  $settings['terminal_id'];
            $this->params['auth_key'] =  $settings['security_key']; 

        }

         /**
	 * Returns API URL.
	 *
	 * @return string
	 */
	protected function get_base_url() {
		$url = esc_url( $this->params['base_url']  );
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
            $ret = false;
            $url = $this->get_base_url()."spin/GetTerminalStatus?tpn=".$this->params['tpn'] ;
            
            $result = wp_remote_get( $url,'');

            if ( is_wp_error($result) ){
                //remote post error
                $ret = false;
            }else {
                $ret['status'] = strtolower($result['body']);
            } 
           
            return $ret;
        }
        /**
	 * Process a sale payment by data.
	 *
	 * @param array $payload data
	 * @return array|WP_Error
	 */
        public function process_payment( $id ) {
            $order = wc_get_order( $id );
            $ret = false;
            
            if ( is_wp_error($order) || !$order ){
                return $ret;
            }
            
            $payload = array();
            $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><request></request>");
            
            $payload['TPN'] = $this->params['tpn'] ;
            $payload['AuthKey'] = $this->params['auth_key'] ;
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
                'timeout'    => $this->params['timeout'],
                'headers'    => [
                    'Content-Type' => 'application/xml',
                ],
                'sslverify'   => false,
            ];
//@todo debug
//            wp_die(var_dump($options));

            $result = wp_remote_post( $url, $options );
            if ( is_wp_error($result)){
                //remote post error
                return $ret;
            }else{
               $xml = simplexml_load_string($result['body']);
               
               $ret['result_code'] = (int)$xml->response->ResultCode;
               $ret['result_msg'] = (string)$xml->response->Message;
               $ret['ref_id'] = (string)$xml->response->PNRef;
                
            } 
            return $ret;
        }
        /**
	 * Retrieve a payment by RefId.
	 *
	 * @param array $payload data
	 * @return array|WP_Error
	 */
        public function capture_payment( $RefId ) {
            $ret = false;
            if (!$RefId)
                return $ret;
            
            $payload = $req = array();
            
            $payload['TPN'] = $this->params['tpn'];
            $payload['AuthKey'] = $this->params['auth_key'];
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
//@todo debug
//wp_die(var_dump($result));                
                $ret = false;
            }else{
               $xml = new SimpleXMLElement("<?xml version=\"1.0\"?><request></request>");
               $xml = simplexml_load_string($result['body']);
               $ret['result_code'] = (int)$xml->response->ResultCode;
               $ret['result_msg'] = (string)$xml->response->Message;
               $ret['trx_id'] = (string)$xml->response->PNRef;
                
            } 
           
            return $ret;
        }
 }
