<?php
/**
 * Trx Host Terminals API
 *
 * @package WooCommerce_POS_HOST/Gateways
 */

defined( 'ABSPATH' ) || exit;
/**
 * POS_HOST_Gateway_Trx_Host_API.
 */
class POS_HOST_Gateway_Trx_Host_API {

    private $base_url = '';
    private $timeout  = '';
    private $tpn  = '';
    private $auth_key  = '';
    /*
    private $payload= array( 
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
	public function __construct( $method ) {
            if ( false === strpos( $method, "pos_host_terminal" ) ) return; 
            self::init( $method );
	}
        
        private function init($method){
            //load settings
            $settings = get_option("woocommerce_".$method."_settings",array());
            $this->base_url = $settings['host_address'];
            $this->timeout =  $settings['timeout']; 
            $this->tpn =  $settings['terminal_id'];
            $this->auth_key =  $settings['security_key']; 

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
	 * Process a sale payment by data.
	 *
	 * @param array $payload data
	 * @return array|WP_Error
	 */
        public function do_order( $order ) {
            if (!$order) return false;
            
            
            $ret = array(
                'error_code'=>0,
                'error_msg'=>'',
                'result_code'=>1,
                'result_msg'=>'',
                
            );
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
               $ret['error_msg'] = $result->get_error_message();
               $ret['error_code'] = $result->get_error_code();                
            }else{
               $xml = simplexml_load_string($result['body']);
               $ret['result_code'] = (int)$xml->response->ResultCode;
               $ret['result_msg'] = (string)$xml->response->Message;
               $ret['trx_id'] = (string)$xml->response->PNRef;
                
            } 
            return $ret;
        }
        /**
	 * Retrieve a payment by RefId.
	 *
	 * @param array $payload data
	 * @return array|WP_Error
	 */
        public function retrieve_trx( $RefId ) {
            if (!$RefId) return false;
            
            $ret = array(
                'error_code'=>0,
                'error_msg'=>'',
                'result_code'=>1,
                'result_msg'=>'',
                
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
