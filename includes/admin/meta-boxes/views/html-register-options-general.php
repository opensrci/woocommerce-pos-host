<?php
/**
 * Register general options panel.
 *
 * @var object $register_object
 *
 * @package WooCommerce_pos_host/Admin/Meta_Boxes/Views
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="general_register_options" class="panel woocommerce_options_panel">
	<?php
		// Outlet.
		$outlet = $register_object->get_outlet( 'edit' );
		woocommerce_wp_select(
			array(
				'id'          => 'outlet',
				'value'       => $outlet ? $outlet : get_option( 'pos_host_default_outlet' ),
				'label'       => __( 'Outlet', 'woocommerce-pos-host' ),
				'options'     => pos_host_get_register_outlet_options(),
				'desc_tip'    => true,
				'description' => __( 'Select the outlet that this register is assigned to.', 'woocommerce-pos-host' ),
			)
		);

		// Product Grid.
		$grid = $register_object->get_grid( 'edit' );
		woocommerce_wp_select(
			array(
				'id'          => 'grid',
				'value'       => $grid ? $grid : 0,
				'label'       => __( 'Product Grid', 'woocommerce-pos-host' ),
				'options'     => pos_host_get_register_grid_options(),
				'desc_tip'    => true,
				'description' => __( 'Select the product grid that this register will use.', 'woocommerce-pos-host' ),
			)
		);

		// Grid Layout.
		woocommerce_wp_select(
			array(
				'id'          => 'grid_layout',
				'value'       => $register_object->get_grid_layout( 'edit' ),
				'label'       => __( 'Grid Layout', 'woocommerce-pos-host' ),
				'options'     => apply_filters(
					'pos_host_register_grid_layouts',
					array(
						'grid'        => __( 'Grid', 'woocommerce-pos-host' ),
						'rectangular' => __( 'Rectangular', 'woocommerce-pos-host' ),
						'list'        => __( 'List', 'woocommerce-pos-host' ),
					)
				),
				'desc_tip'    => true,
				'description' => __( 'Select the layout of the grid when the register loads.', 'woocommerce-pos-host' ),
			)
		);

		// Receipt Template.
		$receipt = $register_object->get_receipt( 'edit' );
		woocommerce_wp_select(
			array(
				'id'          => 'receipt',
				'value'       => $receipt ? $receipt : get_option( 'pos_host_default_receipt' ),
				'label'       => __( 'Receipt Template', 'woocommerce-pos-host' ),
				'options'     => pos_host_get_register_receipt_options(),
				'desc_tip'    => true,
				'description' => __( 'Select the receipt template that this register will use.', 'woocommerce-pos-host' ),
			)
		);

		// Prefix.
		woocommerce_wp_text_input(
			array(
				'id'          => 'prefix',
				'label'       => __( 'Prefix', 'woocommerce-pos-host' ),
				'description' => __( 'Enter the prefix of the orders from this register.', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'value'       => $register_object->get_prefix( 'edit' ),
			)
		);

		// Suffix.
		woocommerce_wp_text_input(
			array(
				'id'          => 'suffix',
				'label'       => __( 'Suffix', 'woocommerce-pos-host' ),
				'description' => __( 'Enter the suffix of the orders from this register.', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'value'       => $register_object->get_suffix( 'edit' ),
			)
		);

		// Customer.
		$customer_options = array();
		$customer_id      = $register_object->get_customer( 'edit' );
		if ( $customer_id ) {
			$customer         = get_user_by( 'id', $customer_id );
			$customer_options = array(
				''           => esc_html__( 'Guest', 'woocommerce-pos-host' ),
				$customer_id => implode( ' ', array( esc_html( $customer->first_name ), esc_html( $customer->last_name ) ) ),
			);
		}
		woocommerce_wp_select(
			array(
				'id'                => 'customer',
				'class'             => 'wc-customer-search short',
				'value'             => $customer_id,
				'options'           => $customer_options,
				'label'             => __( 'Customer', 'woocommerce-pos-host' ),
				'desc_tip'          => true,
				'description'       => __( 'Select what you want the default customer to be when the register is opened.', 'woocommerce-pos-host' ),
				'custom_attributes' => array(
					'data-allow_clear' => 'true',
					'data-placeholder' => esc_attr__( 'Guest', 'woocommerce-pos-host' ),
				),
			)
		);

		// Cash Management.
		woocommerce_wp_checkbox(
			array(
				'id'          => 'cash_management',
				'label'       => __( 'Cash Management', 'woocommerce-pos-host' ),
				'description' => __( 'Check this box if you want to manage the float of cash in the register.', 'woocommerce-pos-host' ),
				'desc_tip'    => false,
				'value'       => wc_bool_to_string( $register_object->get_cash_management( 'edit' ) ),
			)
		);

		// Dining Option.
		woocommerce_wp_select(
			array(
				'id'          => 'dining_option',
				'value'       => $register_object->get_dining_option( 'edit' ),
				'label'       => __( 'Dining Option', 'woocommerce-pos-host' ),
				'options'     => apply_filters(
					'pos_host_register_email_receipt_options',
					array(
						'none'      => __( 'None', 'woocommerce-pos-host' ),
						'eat_in'    => __( 'Eat In', 'woocommerce-pos-host' ),
						'take_away' => __( 'Take Away', 'woocommerce-pos-host' ),
						'delivery'  => __( 'Delivery', 'woocommerce-pos-host' ),
					)
				),
				'desc_tip'    => true,
				'description' => __( 'Select the dining option you want to be used by default in the register.', 'woocommerce-pos-host' ),
			)
		);

		// Default mode.
		woocommerce_wp_select(
			array(
				'id'          => 'default_mode',
				'value'       => $register_object->get_default_mode( 'edit' ),
				'label'       => __( 'Default Mode', 'woocommerce-pos-host' ),
				'options'     => array(
					'search' => __( 'Search products', 'woocommerce-pos-host' ),
					'scan'   => __( 'Scan product SKU', 'woocommerce-pos-host' ),
				),
				'desc_tip'    => true,
				'description' => __( 'Select the default mode for this register.', 'woocommerce-pos-host' ),
			)
		);

		do_action( 'pos_host_register_options_general', $thepostid );
		?>
</div>
