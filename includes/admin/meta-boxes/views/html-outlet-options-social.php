<?php
/**
 * Outlet social options panel.
 *
 * @package WooCommerce_pos_host/Admin/Meta_Boxes/Views
 */

$social_accoutns = $outlet_object->get_social_accounts( 'edit' );

defined( 'ABSPATH' ) || exit;
?>
<div id="social_outlet_options" class="panel woocommerce_options_panel">
	<div class="options_group">
		<p class="options_group_description"><?php esc_html_e( 'Enter the social details of the outlet as this will appear on receipts that are printed from registers at this outlet.', 'woocommerce-pos-host' ); ?></p>
		<?php
			// Twitter.
			woocommerce_wp_text_input(
				array(
					'id'          => 'social_accounts_twitter',
					'name'        => 'social_accounts[twitter]',
					'label'       => __( 'Twitter', 'woocommerce-pos-host' ),
					'description' => __( 'The Twitter name of the outlet. E.g. for twitter.com/acme enter just "acme".', 'woocommerce-pos-host' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'value'       => isset( $social_accoutns['twitter'] ) ? $social_accoutns['twitter'] : '',
				)
			);

			// Facebook.
			woocommerce_wp_text_input(
				array(
					'id'          => 'social_accounts_facebook',
					'name'        => 'social_accounts[facebook]',
					'label'       => __( 'Facebook', 'woocommerce-pos-host' ),
					'description' => __( 'The Facebook name of the outlet. E.g. for facebook.com/acme enter just "acme".', 'woocommerce-pos-host' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'value'       => isset( $social_accoutns['facebook'] ) ? $social_accoutns['facebook'] : '',
				)
			);

			// Instagram.
			woocommerce_wp_text_input(
				array(
					'id'          => 'social_accounts_instagram',
					'name'        => 'social_accounts[instagram]',
					'label'       => __( 'Instagram', 'woocommerce-pos-host' ),
					'description' => __( 'The Instagram name of the outlet. E.g. for instagram.com/acme enter just "acme".', 'woocommerce-pos-host' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'value'       => isset( $social_accoutns['instagram'] ) ? $social_accoutns['instagram'] : '',
				)
			);

			// Snapchat.
			woocommerce_wp_text_input(
				array(
					'id'          => 'social_accounts_snapchat',
					'name'        => 'social_accounts[snapchat]',
					'label'       => __( 'Snapchat', 'woocommerce-pos-host' ),
					'description' => __( 'The Snapchat name of the outlet. E.g. for snapchat.com/acme enter just "acme".', 'woocommerce-pos-host' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'value'       => isset( $social_accoutns['snapchat'] ) ? $social_accoutns['snapchat'] : '',
				)
			);

			do_action( 'pos_host_outlet_options_social', $thepostid );
			?>
	</div>
</div>
