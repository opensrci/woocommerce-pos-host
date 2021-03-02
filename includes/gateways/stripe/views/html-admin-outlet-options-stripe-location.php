<?php
/**
 * Register Stripe location options panel.
 *
 * @var POS_HOST_outlet $outlet_object
 *
 * @package WooCommerce_pos_host/Gateways
 */

defined( 'ABSPATH' ) || exit;

$stripe_api        = new POS_HOST_Stripe_API();
$locations['none'] = __( 'None', 'woocommerce-pos-host' );

foreach ( $stripe_api->get_locations() as $location ) {
	$locations[ $location['id'] ] = $location['display_name'];
}
?>
<div id="stripe_location_outlet_options" class="panel woocommerce_options_panel">
	<?php

	woocommerce_wp_select(
		array(
			'id'          => 'stripe_location',
			'value'       => $outlet_object->get_meta( 'stripe_location', 'None' ),
			'label'       => __( 'Stripe Location', 'woocommerce-pos-host' ),
			'options'     => $locations,
			'desc_tip'    => true,
			'description' => __( 'Select the Stripe location you want to use for this location.', 'woocommerce-pos-host' ),
		)
	);
        
	?>
</div>
