<?php
/**
 * Register Trx Host options panel.
 *
 * @var POS_HOST_Register $register_object
 *
 * @package WooCommerce_pos_host/Gateways
 */

defined( 'ABSPATH' ) || exit;

$terminals         = array();
$trx_host_api  = new POS_HOST_Gateway_Trx_Host_API();
$pac_terminals     = $trx_host_api->pac_terminals_response();
$terminals['none'] = __( 'None', 'woocommerce-pos-host' );

if ( isset( $pac_terminals['terminals'] ) && count( $pac_terminals['terminals'] ) ) {
	foreach ( $pac_terminals['terminals'] as $terminal ) {
		$terminals[ $terminal['tid'] ] = $terminal['tid'];
	}
}
?>
<div id="trx_host_register_options" class="panel woocommerce_options_panel">
	<?php
	woocommerce_wp_select(
		array(
			'id'          => 'trx_host_terminal',
			'value'       => $register_object->get_meta( 'trx_host_terminal', true ),
			'label'       => __( 'Terminal', 'woocommerce-pos-host' ),
			'options'     => $terminals,
			'desc_tip'    => true,
			'description' => __( 'Select the EMV terminal you want to use for this register.', 'woocommerce-pos-host' ),
		)
	);
	?>

	<p class="form-field">
		<label for="trx_host_eod_report"><?php esc_html_e( 'EOD Report', 'woocommerce-pos-host' ); ?></label>
		<button class="button" type="button" id="trx_host_eod_report"><?php esc_html_e( 'Print EOD', 'woocommerce-pos-host' ); ?></button>
		<span class="description"><?php esc_html_e( 'This will print a total of all EMV sales done for this terminal', 'woocommerce-pos-host' ); ?></span>
	</p>
</div>
