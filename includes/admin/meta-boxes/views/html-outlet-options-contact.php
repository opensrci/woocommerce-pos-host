<?php
/**
 * Outlet contact options panel.
 *
 * @package WooCommerce_pos_host/Meta_Boxes/Views
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="contact_outlet_options" class="panel woocommerce_options_panel">
	<div class="options_group">
		<p class="options_group_description"><?php esc_html_e( 'Enter the contact details of the outlet as this will appear on receipts that are printed from registers at this outlet.', 'woocommerce-pos-host' ); ?></p>
		<?php
			// Email Address.
			woocommerce_wp_text_input(
				array(
					'id'          => 'email',
					'class'       => 'pos_host_input_email',
					'label'       => __( 'Email Address', 'woocommerce-pos-host' ),
					'description' => __( 'Enter an email address for this outlet.', 'woocommerce-pos-host' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'value'       => $outlet_object->get_email( 'edit' ),
				)
			);

			// Phone Number.
			woocommerce_wp_text_input(
				array(
					'id'          => 'phone',
					'class'       => 'pos_host_input_phone',
					'label'       => __( 'Phone Number', 'woocommerce-pos-host' ),
					'description' => __( 'Enter a phone number for this outlet.', 'woocommerce-pos-host' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'value'       => $outlet_object->get_phone( 'edit' ),
				)
			);

			// Fax Number.
			woocommerce_wp_text_input(
				array(
					'id'          => 'fax',
					'class'       => 'pos_host_input_fax',
					'label'       => __( 'Fax Number', 'woocommerce-pos-host' ),
					'description' => __( 'Enter a fax number for this outlet.', 'woocommerce-pos-host' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'value'       => $outlet_object->get_fax( 'edit' ),
				)
			);

			// Website.
			woocommerce_wp_text_input(
				array(
					'id'          => 'website',
					'class'       => 'pos_host_input_url',
					'label'       => __( 'Website', 'woocommerce-pos-host' ),
					'description' => __( 'Enter a URL for this outlet.', 'woocommerce-pos-host' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'value'       => $outlet_object->get_website( 'edit' ),
				)
			);

			do_action( 'pos_host_outlet_options_general', $thepostid );
			?>
	</div>
</div>
