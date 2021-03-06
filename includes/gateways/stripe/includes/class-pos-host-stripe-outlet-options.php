<?php
/**
 * Stripe outlet Options for POS Host.
 *
 * @package WooCommerce_pos_host/Gateways
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Stripe_outlet.
 */
class POS_HOST_Stripe_outlet {

	/**
	 * Init.
	 */
	public static function init() {
		self::includes();
		self::add_ajax_events();

                 /*
                  * add stripe location option to outlet's options
                  * 
                  */       
                 add_filter( 'pos_host_outlet_options_tabs', array( __CLASS__, 'outlet_options_tabs' ) );
		add_action( 'pos_host_outlet_options_panels', array( __CLASS__, 'outlet_options_panels' ), 10, 2 );
		add_action( 'pos_host_outlet_options_save', array( __CLASS__, 'save_outlet_data' ), 10, 2 );
		add_filter( 'pos_host_outlet_data', array( __CLASS__, 'add_outlet_data' ) );

	}

	/**
	 * Includes.
	 */
	public static function includes() {
	}

	/**
	 * Hook in methods.
	 */
	public static function add_ajax_events() {
	}

	/**
	 * Add Stripe location tab to the register data meta box.
	 *
	 * @param array $tabs
	 * @return array
	 */
	public static function outlet_options_tabs( $tabs ) {
		$stripe_location_data = get_option( 'woocommerce_pos_stripe_terminal_settings', array() );
		$enabled              = ! empty( $stripe_location_data['enabled'] ) && 'yes' === $stripe_location_data['enabled'];

		if ( $enabled ) {
			$tabs['stripe_location'] = array(
				'label'  => __( 'Stripe location', 'woocommerce-pos-host' ),
				'target' => 'stripe_location_outlet_options',
				'class'  => '',
			);
		}

		return $tabs;
	}
	/**
	 * Display the Stripe location tab content.
	 *
	 * @param int             $thepostid
	 * @param POS_HOST_outlet  $outlet_object
	 */
	public static function outlet_options_panels( $thepostid, $outlet_object ) {
                $stripe_api        = new POS_HOST_Stripe_API();
                $locations['none'] = __( 'None', 'woocommerce-pos-host' );
                /*
                 * current outlet                 */
                $current_location_saved = false;
                $current_location_name = $outlet_object->get_name();
                
                /*
                 * current Selected stripe location id
                 */
                $stripe_location = $outlet_object->get_meta( 'stripe_location', 'none' );
                $stripe_location_saved = false;
                
                foreach ( $stripe_api->get_all_locations() as $location ) {
                        $locations[$location['id'] ] = $location['display_name'];
                        if( $current_location_name == $location['display_name']  ) {
                            $location_id_saved =  $location['id'];
                            $location_saved = true;
                        }
                        if( $stripe_location == $location['id']  ) {
                            $stripe_location_saved = true;
                        }
                        
                }
                 /* Location is not on Stripe account anymore
                 * 
                 */
                 if ( !$current_location_saved ){
                        /* Current location is NOT on Stripe account, neither the selected one
                         * @todo: need add option "Use Current", id = STRIPE_UPLOAD_CURRENT_LOCATION
                         */
                      //   $locations= array('STRIPE_UPLOAD_CURRENT_LOCATION' => __( 'Use Current Location', 'woocommerce-pos-host' )) + $locations;
                }else{
                        /* Current location is on Stripe account, but not selected 
                         */
                         $locations= array( '$location_id_saved'=> __( 'Use Current Location', 'woocommerce-pos-host' )) +
                                    $locations;
                         
                }
                
                if ( ! $stripe_location_saved ){
                        /* selected location not available, need reset */
                        $stripe_location = 'none';
                 }
                
                
                include_once 'views/html-admin-outlet-options-stripe-location.php';
	}

	/**
	 * On save outlet data.
	 *
	 * @param int             $post_id
	 * @param POS_HOST_Register $outlet
	 */
	public static function save_outlet_data( $post_id, $outlet ) {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'update-post_' . $post_id ) ) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce-pos-host' ) );
		}
                
                 /*
                  * Option is set to save current location to Stripe
                  * 
                  * 
                  */
		
		$location = ! empty( $_POST['stripe_location'] ) ? wc_clean( wp_unslash( $_POST['stripe_location'] ) ) : 'none';
                 switch ( $location ){
                     case 'STRIPE_UPLOAD_CURRENT_LOCATION':
                         /*
                          * @todo need upload currentlocation to stripe account
                          * 
                          */
                         break;
                     case 'none':
                     default:
                         break;
                         
                 }
		update_post_meta( $post_id, 'stripe_location', $location );
	}

	/**
	 * Add Stripe location data to outlet data.
	 *
	 * @param array $outlet_data
	 * @return array
	 */
	public static function add_outlet_data( $outlet_data ) {
		$outlet_data['stripe_location'] = get_post_meta( $outlet_data['id'], 'stripe_location', true );

		return $outlet_data;
	}

}

POS_HOST_Stripe_outlet::init();
