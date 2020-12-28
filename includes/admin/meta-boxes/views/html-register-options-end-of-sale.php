<?php
/**
 * Register end of sale options panel.
 *
 * @package WooCommerce_pos_host/Meta_Boxes/Views
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="end_of_sale_register_options" class="panel woocommerce_options_panel">
	<?php
		// Print Receipt.
		woocommerce_wp_checkbox(
			array(
				'id'          => 'print_receipt',
				'label'       => __( 'Print Receipt', 'woocommerce-pos-host' ),
				'description' => __( 'Check this box to print receipt at end of sale.', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'desc_tip'    => false,
				'value'       => wc_bool_to_string( $register_object->get_print_receipt( 'edit' ) ),
			)
		);

		// Gift Receipt.
		woocommerce_wp_checkbox(
			array(
				'id'          => 'gift_receipt',
				'label'       => __( 'Gift Receipt', 'woocommerce-pos-host' ),
				'description' => __( 'Check this box to print gift receipt at end of sale.', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'desc_tip'    => false,
				'value'       => wc_bool_to_string( $register_object->get_gift_receipt( 'edit' ) ),
			)
		);

		// Email Receipt.
		woocommerce_wp_select(
			array(
				'id'          => 'email_receipt',
				'value'       => $register_object->get_email_receipt( 'edit' ),
				'label'       => __( 'Email Receipt', 'woocommerce-pos-host' ),
				'options'     => apply_filters(
					'pos_host_register_email_receipt_options',
					array(
						'no'        => __( 'No', 'woocommerce-pos-host' ),
						'all'       => __( 'Yes, for all customers', 'woocommerce-pos-host' ),
						'non_guest' => __( 'Yes, for non-guest customers only', 'woocommerce-pos-host' ),
					)
				),
				'desc_tip'    => true,
				'description' => __( 'Select whether to email receipt at end of sale.', 'woocommerce-pos-host' ),
			)
		);

		// Note Request.
		woocommerce_wp_select(
			array(
				'id'          => 'note_request',
				'value'       => $register_object->get_note_request( 'edit' ),
				'label'       => __( 'Note Request', 'woocommerce-pos-host' ),
				'options'     => apply_filters(
					'pos_host_register_note_request_options',
					array(
						'none'         => __( 'None', 'woocommerce-pos-host' ),
						'on_save'      => __( 'On save', 'woocommerce-pos-host' ),
						'on_all_sales' => __( 'On all sales', 'woocommerce-pos-host' ),
					)
				),
				'desc_tip'    => true,
				'description' => __( 'Select whether to add a note at end of sale.', 'woocommerce-pos-host' ),
			)
		);

		// Change User.
		woocommerce_wp_checkbox(
			array(
				'id'          => 'change_user',
				'label'       => __( 'Change Cashier', 'woocommerce-pos-host' ),
				'description' => __( 'Check this box if you want the user to be changed at end of sale.', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'desc_tip'    => false,
				'value'       => wc_bool_to_string( $register_object->get_change_user( 'edit' ) ),
			)
		);

		do_action( 'pos_host_register_options_end_of_sale', $thepostid );
		?>
</div>
