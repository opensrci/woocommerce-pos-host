<?php
/**
 * Point of Sale Tiles Settings
 *
 * @package WooCommerce_pos_host/Classes/Admin/Settings
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Admin_Settings_Tiles', false ) ) {
	return new POS_HOST_Admin_Settings_Tiles();
}

/**
 * POS_HOST_Admin_Settings_Tiles.
 */
class POS_HOST_Admin_Settings_Tiles extends POS_HOST_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'tiles';
		$this->label = __( 'Tiles', 'woocommerce-pos-host' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			''                    => __( 'Tiles', 'woocommerce-pos-host' ),
			'unit-of-measurement' => __( 'Units of Measurement', 'woocommerce-pos-host' ),
		);

		return apply_filters( 'pos_host_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		global $woocommerce, $current_section;

		if ( 'unit-of-measurement' === $current_section ) {
			return apply_filters(
				'pos_host_register_tiles_unit_of_measurement_settings',
				array(

					array(
						'title' => __( 'Unit of Measurement Options', 'woocommerce-pos-host' ),
						'type'  => 'title',
						'id'    => 'uom_options',
					),
					array(
						'title'    => __( 'Units of Measurement', 'woocommerce-pos-host' ),
						'desc'     => __( 'Enable decimal stock counts and change of unit of measurement.', 'woocommerce-pos-host' ),
						'desc_tip' => __( 'Allows you to sell your stock in decimal quantities and set the default unit of measurement of stock values. Useful for those who want to sell weight or linear based products.', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_decimal_quantities',
						'default'  => 'no',
						'type'     => 'checkbox',
					),
					array(
						'title'    => __( 'Embedded Barcodes', 'woocommerce-pos-host' ),
						'desc'     => __( 'Enable the use of price and weight embedded barcodes', 'woocommerce-pos-host' ),
						'desc_tip' => __( 'Price or weight-based barcodes can be scanned from the register. Supported formats are EAN-13 and UPC-A.', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_enable_weight_embedded_barcodes',
						'default'  => 'no',
						'type'     => 'checkbox',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'tile_options',
					),
					array(
						'title' => __( 'Universal Product Code', 'woocommerce-pos-host' ),
						/* translators: %1$s code tag %2$s closing code tag */
						'desc'  => sprintf( __( 'Adjust how the scanned UPC-A barcodes are processed before adding to cart. UPC-A barcodes follow the pattern %1$s2IIIIICVVVVC%2$s, where %1$sI%2$s is the product identifier, %1$sC%2$s are check digits and %1$sV%2$s is the value of the barcode.', 'woocommerce-pos-host' ), '<code>', '</code>' ),
						'type'  => 'title',
						'id'    => 'upca_options',
					),
					array(
						'title'   => __( 'Barcode Type', 'woocommerce-pos-host' ),
						/* translators: %1$s code tag %2$s closing code tag */
						'desc'    => sprintf( __( 'Choose what the value %1$sV%2$s represents i.e. price or weight.', 'woocommerce-pos-host' ), '<code>', '</code>' ),
						'id'      => 'pos_host_upca_type',
						'default' => 'price',
						'type'    => 'select',
						'options' => array(
							'price'  => 'Price',
							'weight' => 'Weight',
						),
					),
					array(
						'title'    => __( 'Multiplier', 'woocommerce-pos-host' ),
						/* translators: %1$s code tag %2$s closing code tag */
						'desc'     => sprintf( __( 'Choose how the value %1$sV%2$s is calculated.', 'woocommerce-pos-host' ), '<code>', '</code>' ),
						'desc_tip' => __( 'E.g. a multiplier of 10 means that the embdded value will be divided by 10 before adding to cart.', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_upca_multiplier',
						'default'  => '100',
						'type'     => 'select',
						'options'  => array(
							1   => '1',
							10  => '10',
							100 => '100',
						),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'upca_options',
					),
				)
			);
		}

		return apply_filters(
			'pos_host_register_tiles_settings',
			array(

				array(
					'title' => __( 'Tile Options', 'woocommerce-pos-host' ),
					'desc'  => __( 'The following options affect how the tiles appear on the product grid.', 'woocommerce-pos-host' ),
					'type'  => 'title',
					'id'    => 'tile_options',
				),
				array(
					'title'    => __( 'Default Tile Sorting', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'This controls the default sort order of the tile.', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_default_tile_orderby',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'default'  => 'menu_order',
					'type'     => 'select',
					'options'  => apply_filters(
						'woocommerce_default_catalog_orderby_options',
						array(
							'menu_order' => __( 'Default sorting (custom ordering + name)', 'woocommerce-pos-host' ),
							'popularity' => __( 'Popularity (sales)', 'woocommerce-pos-host' ),
							'rating'     => __( 'Average Rating', 'woocommerce-pos-host' ),
							'date'       => __( 'Sort by most recent', 'woocommerce-pos-host' ),
							'price'      => __( 'Sort by price (asc)', 'woocommerce-pos-host' ),
							'price-desc' => __( 'Sort by price (desc)', 'woocommerce-pos-host' ),
							'title-asc'  => __( 'Name (asc)', 'woocommerce-pos-host' ),
						)
					),
				),
				array(
					'name'     => __( 'Product Previews', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_show_product_preview',
					'type'     => 'checkbox',
					'desc'     => __( 'Enable product preview panels', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'Shows a button on each tile for cashiers to view full product details.', 'woocommerce-pos-host' ),
					'default'  => 'no',
					'autoload' => true,
				),
				array(
					'name'     => __( 'Out of Stock', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_show_out_of_stock_products',
					'type'     => 'checkbox',
					'desc'     => __( 'Show out of stock products', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'Display out of stock products in the product grid.', 'woocommerce-pos-host' ),
					'default'  => 'no',
					'autoload' => true,
				),
				array(
					'title'         => __( 'Product Visiblity', 'woocommerce-pos-host' ),
					'desc'          => __( 'Enable product visibility control', 'woocommerce-pos-host' ),
					'desc_tip'      => __( 'Allows you to show and hide products from either the POS, web or both shops.', 'woocommerce-pos-host' ),
					'id'            => 'pos_host_visibility',
					'default'       => 'no',
					'type'          => 'checkbox',
					'checkboxgroup' => 'start',
				),
				array(
					'title'    => __( 'Add to Cart Behaviour', 'woocommerce-pos-host' ),
					'desc'     => __( 'Control what happens to the grid after a product is added to the basket.', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'Allows shop managers to choose the behaviour of grids when adding products to the cart.', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_after_add_to_cart_behavior',
					'default'  => 'category',
					'class'    => 'wc-enhanced-select',
					'type'     => 'select',
					'options'  => array(
						'product'  => __( 'Stay on the selected product', 'woocommerce-pos-host' ),
						'category' => __( 'Return to selected category', 'woocommerce-pos-host' ),
						'home'     => __( 'Return to home grid', 'woocommerce-pos-host' ),
					),
				),
				array(
					'title'    => __( 'Publish Product', 'woocommerce-pos-host' ),
					'desc'     => __( 'Toggle publishing of product by default', 'woocommerce-pos-host' ),
					'desc_tip' => __( 'User roles and capabilities are required to publish products.', 'woocommerce-pos-host' ),
					'id'       => 'pos_host_publish_product_default',
					'default'  => 'yes',
					'type'     => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'tile_options',
				),
			)
		);
	}

	/**
	 * Save settings
	 */
	public function save() {
		$settings = $this->get_settings();
		POS_HOST_Admin_Settings::save_fields( $settings );
	}

}

return new POS_HOST_Admin_Settings_Tiles();
