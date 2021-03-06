<?php
/**
 * Stripe location options panel.
 *
 * @var $locations, $current_location
 *
 * @package WooCommerce_pos_host/Gateways
 */

defined( 'ABSPATH' ) || exit;
?>

<div id="stripe_location_outlet_options" class="panel woocommerce_options_panel">

<?php
        woocommerce_wp_select(
                array(
                        'id'          => 'stripe_location',
                        'value'       => $current_location,
                        'label'       => __( 'Stripe Location', 'woocommerce-pos-host' ),
                        'options'     => $locations,
                        'desc_tip'    => true,
                        'description' => __( 'Select the Stripe location you want to use for this location.', 'woocommerce-pos-host' ),
                )
        );
?>
</div>
