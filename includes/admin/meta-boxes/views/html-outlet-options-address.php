<?php
/**
 * Outlet address options panel.
 *
 * @var object $outlet_object
 *
 * @package WooCommerce_pos_host/Admin/Meta_Boxes/Views
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="address_outlet_options" class="panel woocommerce_options_panel">
	<p class="options_group_description">
	<?php
	/* translators: %1$s opening anchor tag %2$s closing anchor tag */
	echo wp_kses_post( sprintf( __( 'Enter the address details of the outlet or %1$sclick here%2$s to fill out the fields from the store address.', 'woocommerce-pos-host' ), '<a href="" id="use-store-address">', '</a>' ) );
	?>
	</p>
	<?php
		// Address line 1.
		woocommerce_wp_text_input(
			array(
				'id'       => 'address_1',
				'label'    => __( 'Address Line 1', 'woocommerce-pos-host' ),
				'type'     => 'text',
				'desc_tip' => false,
				'value'    => $outlet_object->get_address_1( 'edit' ),
			)
		);

		// Address line 2.
		woocommerce_wp_text_input(
			array(
				'id'       => 'address_2',
				'label'    => __( 'Address Line 2', 'woocommerce-pos-host' ),
				'type'     => 'text',
				'desc_tip' => false,
				'value'    => $outlet_object->get_address_2( 'edit' ),
			)
		);

		// City.
		woocommerce_wp_text_input(
			array(
				'id'       => 'city',
				'label'    => __( 'City', 'woocommerce-pos-host' ),
				'type'     => 'text',
				'desc_tip' => false,
				'value'    => $outlet_object->get_city( 'edit' ),
			)
		);

		// Postcode/ZIP.
		woocommerce_wp_text_input(
			array(
				'id'       => 'postcode',
				'label'    => __( 'Postcode/ZIP', 'woocommerce-pos-host' ),
				'type'     => 'text',
				'desc_tip' => false,
				'value'    => $outlet_object->get_postcode( 'edit' ),
			)
		);

		// Country.
		woocommerce_wp_select(
			array(
				'id'       => 'country',
				'class'    => 'js_field-country select short',
				'value'    => $outlet_object->get_country( 'edit' ),
				'options'  => array( '' => __( 'Select a country&hellip;', 'woocommerce-pos-host' ) ) + WC()->countries->get_allowed_countries(),
				'label'    => __( 'Country', 'woocommerce-pos-host' ),
				'desc_tip' => false,
			)
		);

		// State/County.
		woocommerce_wp_text_input(
			array(
				'id'          => 'state',
				'class'       => 'js_field-state select short',
				'value'       => $outlet_object->get_state( 'edit' ),
				'date_type'   => 'text',
				'label'       => __( 'State/County', 'woocommerce-pos-host' ),
				'description' => __( 'State/County or state code.', 'woocommerce-pos-host' ),
				'desc_tip'    => true,
			)
		);
		do_action( 'pos_host_outlet_options_address', $thepostid );
		?>
</div>
