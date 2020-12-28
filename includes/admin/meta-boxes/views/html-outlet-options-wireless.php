<?php
/**
 * Outlet wireless options panel.
 *
 * @package WooCommerce_pos_host/Admin/Meta_Boxes/Views
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="wireless_outlet_options" class="panel woocommerce_options_panel">
	<div class="options_group">
		<p class="options_group_description"><?php esc_html_e( 'Enter the wireless details of your outlet. This is useful for shop managers who want to share their wireless internet conncectivity to paid customers by sharing their wireless details on the printed receipt.', 'woocommerce-pos-host' ); ?></p>
		<?php
			// Wi-Fi Network.
			woocommerce_wp_text_input(
				array(
					'id'       => 'wifi_network',
					'label'    => __( 'Wi-Fi Network', 'woocommerce-pos-host' ),
					'type'     => 'text',
					'desc_tip' => false,
					'value'    => $outlet_object->get_wifi_network( 'edit' ),
				)
			);

			// Wi-Fi Password.
			woocommerce_wp_text_input(
				array(
					'id'       => 'wifi_password',
					'label'    => __( 'Wi-Fi Password', 'woocommerce-pos-host' ),
					'type'     => 'text',
					'desc_tip' => false,
					'value'    => $outlet_object->get_wifi_password( 'edit' ),
				)
			);

			do_action( 'pos_host_outlet_options_wireless', $thepostid );
			?>
	</div>
</div>
