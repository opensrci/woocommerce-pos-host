<?php
/**
 * Register Stripe Terminal options panel.
 *
 * @var POS_HOST_Register $register_object
 *
 * @package WooCommerce_pos_host/Gateways
 */

defined( 'ABSPATH' ) || exit;

$terminals         = array();
$stripe_api        = new POS_HOST_Stripe_API();
$terminals['none'] = __( 'None', 'woocommerce-pos-host' );

foreach ( $stripe_api->get_terminals() as $terminal ) {
	$terminals[ $terminal['id'] ] = $terminal['label'];
}
?>
<div id="stripe_terminal_register_options" class="panel woocommerce_options_panel">
	<?php
	woocommerce_wp_select(
		array(
			'id'          => 'stripe_terminal',
			'value'       => $register_object->get_meta( 'stripe_terminal', true ),
			'label'       => __( 'Terminal', 'woocommerce-pos-host' ),
			'options'     => $terminals,
			'desc_tip'    => true,
			'description' => __( 'Select the EMV terminal you want to use for this register.', 'woocommerce-pos-host' ),
		)
	);
	?>
</div>
