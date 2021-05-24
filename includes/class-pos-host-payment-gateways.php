<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class POS_HOST_Payment_Gateways {

	public static function init() {
		 // add_filter('pos_host_enqueue_scripts',   array(__CLASS__, 'pos_enqueue_scripts'), 10, 1);
		add_action( 'pos_admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
		add_filter( 'woocommerce_is_checkout', array( __CLASS__, 'woocommerce_is_checkout' ) );

		add_action( 'option_woocommerce_securesubmit_settings', array( __CLASS__, 'woocommerce_securesubmit_settings' ), 100, 1 );
                                /* remove some default useless woo gw */
                 add_filter( 'woocommerce_payment_gateways', array( __CLASS__,'woocommerce_remove_default_gateway'), 99, 1 );

	}

        public static function woocommerce_remove_default_gateway( $load_gateways ){
            $remove_gw = array(
                0, //BACS
                1, //Cheque
                2, //COD
                5,
                6,
                7,
                8,
                9.,
                10,
                11,
                12,
                13,//Strip bs


            );
            foreach ($remove_gw as $gw){
                unset( $load_gateways[$gw] );
            }
            return $load_gateways;
        }

	public static function woocommerce_is_checkout( $is_checkout ) {
		if ( is_pos() ) {
			$is_checkout = true;
		}
		return $is_checkout;
	}

	public static function woocommerce_securesubmit_settings( $value ) {
		if ( is_pos() ) {
			$value['use_iframes'] = 'no';
		}
		return $value;
	}

	public static function pos_enqueue_scripts( $sctipts ) {
		if ( class_exists( 'WooCommerceSecureSubmitGateway' ) ) {
			$sctipts['WooCommerceSecureSubmitGateway'] = POS_HOST()->plugin_url() . '/assets/js/register/subscriptions.js';
		}
		return $sctipts;
	}
	public static function admin_enqueue_scripts( $sctipts ) {
		if ( class_exists( 'WC_Gateway_SecureSubmit' ) ) {
			$ss = new WC_Gateway_SecureSubmit();
			$ss->payment_scripts();
		}
	}
}

POS_HOST_Payment_Gateways::init();
