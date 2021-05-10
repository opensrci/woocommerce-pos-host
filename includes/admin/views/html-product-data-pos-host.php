<?php
/**
 * pos host Data Panel
 *
 * @var int $thepostid
 *
 * @package WooCommerce_pos_host/Admin/Views
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="pos_host_product_data" class="panel woocommerce_options_panel">
	<div class="options_group">
		<?php
			woocommerce_wp_checkbox(
				array(
					'id'          => 'unit_of_measurement',
					'value'       => get_post_meta( $thepostid, 'unit_of_measurement', true ),
					'label'       => __( 'Unit of Measurement', 'woocommerce-pos-host' ),
					'description' => __( 'Change the unit of measurement of stock values.', 'woocommerce-pos-host' ),
				)
			);

			woocommerce_wp_checkbox(
				array(
					'id'          => 'uom_override_quantity',
					'value'       => get_post_meta( $thepostid, 'uom_override_quantity', true ),
					'label'       => __( 'Override Quantity', 'woocommerce-pos-host' ),
					'description' => __( 'Check this to override quantity when scanning price embedded barcodes.', 'woocommerce-pos-host' ),
				)
			);

			woocommerce_wp_select(
				array(
					'id'          => 'uom_unit',
					'value'       => get_post_meta( $thepostid, 'uom_unit', true ),
					'label'       => __( 'Unit', 'woocommerce-pos-host' ),
					'description' => __( 'Select unit of measurement.', 'woocommerce-pos-host' ),
					'desc_tip'    => true,
					'options'     => array(
						'kg'    => 'kg',
						'g'     => 'g',
						'lbs'   => 'lbs',
						'oz'    => 'oz',
						'km'    => 'km',
						'm'     => 'm',
						'cm'    => 'cm',
						'mm'    => 'mm',
						'in'    => 'in',
						'ft'    => 'ft',
						'yd'    => 'yd',
						'mi'    => 'mi (mile)',
						'ha'    => 'ha (hectare)',
						'sq-km' => 'sq km',
						'sq-m'  => 'sq m',
						'sq-cm' => 'sq cm',
						'sq-mm' => 'sq mm',
						'acs'   => 'acs (acre)',
						'sq-mi' => 'sq mi',
						'sq-yd' => 'sq yd',
						'sq-ft' => 'sq ft',
						'sq-in' => 'sq in',
						'cu-m'  => 'cu m',
						'l'     => 'l',
						'ml'    => 'ml',
						'gal'   => 'gal',
						'qt'    => 'qt',
						'pt'    => 'pt',
						'cup'   => 'ft',
						'yd'    => 'yd',
					),
				)
			);

			$starting_value = get_post_meta( $thepostid, 'uom_starting_value', true );
			woocommerce_wp_text_input(
				array(
					'id'                => 'uom_starting_value',
					'label'             => __( 'Starting Value', 'woocommerce-pos-host' ),
					'description'       => __( 'Select the starting value used for pre defined suggestions.', 'woocommerce-pos-host' ),
					'desc_tip'          => true,
					'type'              => 'number',
					'value'             => empty( $starting_value ) ? '0.25' : $starting_value,
					'custom_attributes' => array(
						'size' => '6',
						'step' => '0.01',
						'min'  => '0',
						'max'  => '10',
					),
				)
			);

			$suggestions       = get_post_meta( $thepostid, 'uom_suggestions', true );
			$suggestions_value = get_post_meta( $thepostid, 'uom_suggestions_value', true );
			?>
			<p class="form-field uom_suggestions_field">
				<label for="uom_suggestions"><?php esc_html_e( 'Suggestions', 'woocommerce-pos-host' ); ?></label>
				<span class="wrap">
					<select id="uom_suggestions" name="uom_suggestions" class="select">
						<option value="increments" <?php selected( $suggestions, 'increments', true ); ?>><?php esc_html_e( 'Increments of', 'woocommerce-pos-host' ); ?></option>
						<option value="multipliers" <?php selected( $suggestions, 'multipliers', true ); ?>><?php esc_html_e( 'Multiplied by', 'woocommerce-pos-host' ); ?></option>
					</select>
					<select id="uom_suggestions_value" name="uom_suggestions_value" class="select last">
						<option value="1" <?php selected( $suggestions_value, '1', true ); ?>><?php esc_attr_e( '1', 'woocommerce-pos-host' ); ?></option>
						<option value="2" <?php selected( $suggestions_value, '2', true ); ?>><?php esc_attr_e( '2', 'woocommerce-pos-host' ); ?></option>
						<option value="3" <?php selected( $suggestions_value, '3', true ); ?>><?php esc_attr_e( '3', 'woocommerce-pos-host' ); ?></option>
						<option value="4" <?php selected( $suggestions_value, '4', true ); ?>><?php esc_attr_e( '4', 'woocommerce-pos-host' ); ?></option>
						<option value="5" <?php selected( $suggestions_value, '5', true ); ?>><?php esc_attr_e( '5', 'woocommerce-pos-host' ); ?></option>
						<option value="6" <?php selected( $suggestions_value, '6', true ); ?>><?php esc_attr_e( '6', 'woocommerce-pos-host' ); ?></option>
						<option value="7" <?php selected( $suggestions_value, '7', true ); ?>><?php esc_attr_e( '7', 'woocommerce-pos-host' ); ?></option>
						<option value="8" <?php selected( $suggestions_value, '8', true ); ?>><?php esc_attr_e( '8', 'woocommerce-pos-host' ); ?></option>
						<option value="9" <?php selected( $suggestions_value, '9', true ); ?>><?php esc_attr_e( '9', 'woocommerce-pos-host' ); ?></option>
						<option value="10" <?php selected( $suggestions_value, '10', true ); ?>><?php esc_attr_e( '10', 'woocommerce-pos-host' ); ?></option>
					</select>
				</span>
				<?php echo wc_help_tip( __( 'Define the way the next suggestions are calculated.', 'woocommerce-pos-host' ) ); ?>
			</p>
	</div>

	<?php do_action( 'woocommerce_product_options_pos_host_product_data' ); ?>
</div>
