<?php
/**
 * Point of Sale Register Settings
 *
 * @package WooCommerce_pos_host/Classes/Admin/Settings
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Admin_Settings_Register', false ) ) {
	return new POS_HOST_Admin_Settings_Register();
}

/**
 * POS_HOST_Admin_Settings_Register.
 */
class POS_HOST_Admin_Settings_Register extends POS_HOST_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'register';
		$this->label = __( 'Register', 'woocommerce-pos-host' );

		parent::__construct();

		add_action( 'woocommerce_admin_field_cash_denominations', array( $this, 'output_cash_denominations' ) );
		add_filter( 'pos_host_scanning_fields', array( $this, 'filter_scanning_fields' ) );
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			''                => __( 'Register', 'woocommerce-pos-host' ),
			'cash-management' => __( 'Cash Management', 'woocommerce-pos-host' ),
		);

		return apply_filters( 'pos_host_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		global $current_section;
		$order_statuses = pos_host_get_order_statuses_no_prefix();

		if ( 'cash-management' === $current_section ) {
			$settings = apply_filters(
				'pos_host_cash_management_settings',
				array(
					array(
						'title' => __( 'Cash Management Options', 'woocommerce-pos-host' ),
						'type'  => 'title',
						'desc'  => __( 'The following options affect the settings that are applied when using the cash management function.', 'woocommerce-pos-host' ),
						'id'    => 'cash_management_options',
					),
					array(
						'name'     => __( 'Order Status Criteria ', 'woocommerce-pos-host' ),
						'desc_tip' => __( 'Select the order statuses to be included to the cash management.', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_cash_management_order_statuses',
						'class'    => 'wc-enhanced-select',
						'type'     => 'multiselect',
						'options'  => apply_filters( 'pos_host_cash_management_order_status', $order_statuses ),
						'default'  => array( 'processing' ),
					),
					array(
						'title'         => __( 'Currency Rounding', 'woocommerce-pos-host' ),
						'desc'          => __( 'Enable currency rounding', 'woocommerce-pos-host' ),
						'desc_tip'      => __( 'Rounds the total to the nearest value defined below. Used by some countries where not all denominations are available.', 'woocommerce-pos-host' ),
						'id'            => 'pos_host_enable_currency_rounding',
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => 'start',
					),
					array(
						'title'    => __( 'Rounding Value', 'woocommerce-pos-host' ),
						'desc_tip' => __( 'Select the rounding value which you want the register to round nearest to.', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_currency_rounding_value',
						'default'  => 'no',
						'type'     => 'select',
						'class'    => 'wc-enhanced-select',
						'options'  => apply_filters(
							'pos_host_currency_rounding_values',
							array(
								'0.01' => __( '0.01', 'woocommerce-pos-host' ),
								'0.05' => __( '0.05', 'woocommerce-pos-host' ),
								'0.10' => __( '0.10', 'woocommerce-pos-host' ),
								'0.50' => __( '0.50', 'woocommerce-pos-host' ),
								'1.00' => __( '1.00', 'woocommerce-pos-host' ),
								'5.00' => __( '5.00', 'woocommerce-pos-host' ),
							)
						),
					),
					array(
						'type' => 'sectionend',
						'id'   => 'cash_management_options',
					),
					array( 'type' => 'cash_denominations' ),
				)
			);
		} else {
			return apply_filters(
				'pos_host_register_settings',
				array(
					array(
						'title' => __( 'Register Options', 'woocommerce-pos-host' ),
						'type'  => 'title',
						'desc'  => __( 'The following options affect the settings that are applied when loading all registers.', 'woocommerce-pos-host' ),
						'id'    => 'register_options',
					),
					array(
						'name'              => __( 'Keypad Presets', 'woocommerce-pos-host' ),
						'desc_tip'          => __( 'Define the preset keys that appear when applying discounts in the register.', 'woocommerce-pos-host' ),
						'id'                => 'pos_host_discount_presets',
						'class'             => 'wc-enhanced-select',
						'type'              => 'multiselect',
						'options'           => apply_filters(
							'pos_host_discount_presets',
							array(
								'5'   => __( '5%', 'woocommerce-pos-host' ),
								'10'  => __( '10%', 'woocommerce-pos-host' ),
								'15'  => __( '15%', 'woocommerce-pos-host' ),
								'20'  => __( '20%', 'woocommerce-pos-host' ),
								'25'  => __( '25%', 'woocommerce-pos-host' ),
								'30'  => __( '30%', 'woocommerce-pos-host' ),
								'35'  => __( '35%', 'woocommerce-pos-host' ),
								'40'  => __( '40%', 'woocommerce-pos-host' ),
								'45'  => __( '45%', 'woocommerce-pos-host' ),
								'50'  => __( '50%', 'woocommerce-pos-host' ),
								'55'  => __( '55%', 'woocommerce-pos-host' ),
								'60'  => __( '60%', 'woocommerce-pos-host' ),
								'65'  => __( '65%', 'woocommerce-pos-host' ),
								'70'  => __( '70%', 'woocommerce-pos-host' ),
								'75'  => __( '75%', 'woocommerce-pos-host' ),
								'80'  => __( '80%', 'woocommerce-pos-host' ),
								'85'  => __( '85%', 'woocommerce-pos-host' ),
								'90'  => __( '90%', 'woocommerce-pos-host' ),
								'95'  => __( '95%', 'woocommerce-pos-host' ),
								'100' => __( '100%', 'woocommerce-pos-host' ),
							)
						),
						'default'           => array( '5', '10', '15', '20' ),
						'custom_attributes' => array( 'data-maximum-selection-length' => 4 ),
					),
					array(
						'title'    => __( 'Keyboard Shortcuts', 'woocommerce-pos-host' ),
						'desc'     => __( 'Enable keyboard shortcuts', 'woocommerce-pos-host' ),
						// translators: 1: opening anchor tag for the shortcuts link 2: closing anchor tag
						'desc_tip' => sprintf( __( 'Allows you to use keyboard shortcuts to execute popular and frequent actions. Click %1$shere%2$s for the list of keyboard shortcuts.', 'woocommerce-pos-host' ), '<a href="http://actualityextensions.com/woocommerce-pos-host/keyboard-shortcuts/" target="_blank">', '</a>' ),
						'id'       => 'pos_host_keyboard_shortcuts',
						'default'  => 'no',
						'type'     => 'checkbox',
					),
					array(
						'name'              => __( 'Scanning Fields', 'woocommerce-pos-host' ),
						'desc_tip'          => __( 'Control what fields are used when using the scanner on the register. You can select multiple fields. Default is SKU.', 'woocommerce-pos-host' ),
						'id'                => 'pos_host_scanning_fields',
						'class'             => 'wc-enhanced-select',
						'type'              => 'multiselect',
						'options'           => apply_filters(
							'pos_host_scanning_fields',
							array( '_sku' => __( 'WooCommerce SKU', 'woocommerce-pos-host' ) )
						),
						'default'           => array( '_sku' ),
						'custom_attributes' => array( 'data-tags' => 'true' ),
					),
					array(
						'name'              => __( 'Search Includes', 'woocommerce-pos-host' ),
						'desc_tip'          => __( 'Select the fields to be used for the search.', 'woocommerce-pos-host' ),
						'id'                => 'pos_host_search_includes',
						'class'             => 'wc-enhanced-select',
						'type'              => 'multiselect',
						'options'           => apply_filters(
							'pos_host_search_includes',
							array(
								'title'      => __( 'Product Title', 'woocommerce-pos-host' ),
								'sku'        => __( 'Product SKU', 'woocommerce-pos-host' ),
								'content'    => __( 'Product Description', 'woocommerce-pos-host' ),
								'excerpt'    => __( 'Product Short Description', 'woocommerce-pos-host' ),
								'attributes' => __( 'Product Attributes', 'woocommerce-pos-host' ),
							)
						),
						'default'           => array( 'title' ),
						'custom_attributes' => array( 'data-tags' => 'true' ),
					),
					array(
						'name'     => __( 'Required Product Fields', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_custom_product_required_fields',
						'type'     => 'multiselect',
						'class'    => 'wc-enhanced-select-required-fields',
						'desc_tip' => __( 'Select the fields that are required when creating a custom product through the register.', 'woocommerce-pos-host' ),
						'options'  => array(
							'sku' => __( 'SKU', 'woocommerce-pos-host' ),
						),
						'default'  => array(),
					),
					array(
						'title'         => __( 'Force Logout', 'woocommerce-pos-host' ),
						'desc'          => __( 'Enable taking over of registers', 'woocommerce-pos-host' ),
						'desc_tip'      => __( 'Allows shop managers to take over an already opened register.', 'woocommerce-pos-host' ),
						'id'            => 'pos_host_force_logout',
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => 'start',
					),
					array(
						'title'    => __( 'Additional Payment Methods', 'woocommerce-pos-host' ),
						'desc_tip' => __( 'Select the number of Terminal Gateways to show in WooCommerce > Payments.', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_number_terminal_gateways',
						'default'  => 'no',
						'type'     => 'select',
						'options'  => apply_filters(
							'pos_host_number_terminal_gateways',
							array(
								1 => '1',
								2 => '2',
								3 => '3',
								4 => '4',
								5 => '5',
							)
						),
					),
					array(
						'title'         => __( 'Hide Tender Suggestions', 'woocommerce-pos-host' ),
						'desc'          => __( 'Hide tender suggestions', 'woocommerce-pos-host' ),
						'desc_tip'      => __( 'Check this to hide the suggested cash tender amounts.', 'woocommerce-pos-host' ),
						'id'            => 'pos_host_hide_tender_suggestions',
						'default'       => 'no',
						'type'          => 'checkbox',
						'checkboxgroup' => 'start',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'register_options',
					),
					array(
						'title' => __( 'Theme', 'woocommerce-pos-host' ),
						'type'  => 'title',
						'desc'  => __( 'The following options affect the layout of the register.', 'woocommerce-pos-host' ),
						'id'    => 'theme',
					),
					array(
						'title'    => __( 'Logo', 'woocommerce-pos-host' ),
						'desc'     => __( 'Register logo image.', 'woocommerce-pos-host' ),
						'desc_tip' => __( 'Upload an image to replace the default WooCommerce logo.', 'woocommerce-pos-host' ),
						'id'       => 'pos_host_theme_logo',
						'type'     => 'media_upload',
					),
					array(
						'name'              => __( 'Primary Color', 'woocommerce-pos-host' ),
						'desc_tip'          => __( 'The primary color of the theme.', 'woocommerce-pos-host' ),
						'id'                => 'pos_host_theme_primary_color',
						'class'             => 'color-pick',
						'custom_attributes' => array( 'data-default-color' => '#7f54b3' ),
						'type'              => 'text',
						'default'           => '#7f54b3',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'theme',
					),
				)
			);
		}

		return apply_filters( 'pos_host_get_settings_' . $this->id, $settings, $current_section );
	}

	/**
	 * Filter scanning fields.
	 *
	 * @param $fields Fields.
	 */
	public function filter_scanning_fields( $fields ) {
		global $wpdb;

		$product_meta_keys = get_transient( 'pos_host_product_meta_keys' );

		if ( ! $product_meta_keys ) {
			// Get used meta keys from the database.
			$result            = $wpdb->get_results( "SELECT DISTINCT pm.meta_key FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE p.post_type = 'product'" );
			$product_meta_keys = array_map(
				function( $item ) {
					return $item->meta_key;
				},
				$result
			);
			set_transient( 'pos_host_product_meta_keys', $product_meta_keys, 60 * 60 * 24 );
		}

		if ( $product_meta_keys ) {
			foreach ( $product_meta_keys as $key ) {
				// Filter known meta keys.
				switch ( $key ) {
					case 'total_sales':
					case '_edit_last':
					case '_edit_lock':
					case '_tax_status':
					case '_tax_class':
					case '_manage_stock':
					case '_backorders':
					case '_sold_individually':
					case '_virtual':
					case '_downloadable':
					case '_download_limit':
					case '_download_expiry':
					case '_wc_average_rating':
					case '_wc_review_count':
					case '_product_version':
					case '_wpcom_is_markdown':
					case '_wp_old_slug':
					case '_product_image_gallery':
					case '_thumbnail_id':
					case '_product_attributes':
					case '_price':
					case '_regular_price':
					case '_sale_price':
					case '_downloadable_files':
					case '_children':
					case '_product_url':
					case '_button_text':
					case '_stock':
					case '_stock_status':
					case '_variation_description':
					case '_sku':
					case '_pos_visibility':
					case '_wpm_gtin_code_label':
						continue 2;
					case 'hwp_product_gtin':
					case '_wpm_gtin_code':
						$label = __( 'GTIN', 'woocommerce-pos-host' );
						break;
					default:
						$label = $key;
				}

				$fields[ $key ] = $label;
			}
		}

		return $fields;
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings();
		POS_HOST_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'pos-host-settings' ) ) {
			return;
		}

		global $current_section;

		if ( 'cash-management' === $current_section ) {
			$denominations = ( isset( $_POST['pos_host_cash_denominations'] ) ) ? array_map( 'wc_clean', (array) $_POST['pos_host_cash_denominations'] ) : array();

			update_option( 'pos_host_cash_denominations', array_values( $denominations ) );
		}

		$settings = $this->get_settings();
		POS_HOST_Admin_Settings::save_fields( $settings );
	}

	public function output_cash_denominations( $field ) {
		$denominations = get_option( 'pos_host_cash_denominations', array() );

		include_once dirname( __FILE__ ) . '/views/html-admin-page-register-denominations.php';
	}
}

return new POS_HOST_Admin_Settings_Register();
