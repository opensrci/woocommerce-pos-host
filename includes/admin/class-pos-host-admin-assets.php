<?php
/**
 * Load Admin Assets
 *
 * @package WooCommerce_pos_host/Classes/Admin
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Admin_Assets', false ) ) {
	return new POS_HOST_Admin_Assets();
}

/**
 * POS_HOST_Admin_Assets.
 *
 * Handles assets loading in admin.
 */
class POS_HOST_Admin_Assets {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Lower priorty to allow WC assets to be registered.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ), 99 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 99 );
	}

	/**
	 * Enqueue admin styles.
	 */
	public function admin_styles() {
		global $wp_scripts;
		$screen           = get_current_screen();
		$screen_id        = $screen ? $screen->id : '';
		$pos_host_screen_id = POS_HOST()->plugin_screen_id();

		// Register admin styles.
		wp_register_style( 'pos-host-fonts', POS_HOST()->plugin_url() . '/assets/dist/css/fonts.min.css', array(), POS_HOST_VERSION );
                wp_register_style( 'pos-host-print', POS_HOST()->plugin_url() . '/assets/dist/css/print.min.css', array(), POS_HOST_VERSION );
		wp_register_style( 'pos-host-admin-meta-boxes', POS_HOST()->plugin_url() . '/assets/dist/css/admin/meta-boxes.min.css', array(), POS_HOST_VERSION );
		wp_register_style( 'pos-host-barcode-options', POS_HOST()->plugin_url() . '/assets/dist/css/admin/barcode-options.min.css', array(), POS_HOST_VERSION );
		wp_register_style( 'pos-host-admin', POS_HOST()->plugin_url() . '/assets/dist/css/admin/admin.min.css', array(), POS_HOST_VERSION );

		// Load fonts.css globally.
		wp_enqueue_style( 'pos-host-fonts' );

		// Admin pages that are created/modified by the plugin.
		if ( in_array( $screen_id, pos_host_get_screen_ids(), true ) ) {
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_enqueue_style( 'jquery-ui-style' );
			wp_enqueue_style( 'woocommerce-layout' ); // @todo This should not be used in admin. Make sure it's unneeded then remove it.
			wp_enqueue_style( 'pos-host-print' );
			wp_enqueue_style( 'pos-host-admin' );
		}

		// Barcode page.
		if ( $pos_host_screen_id . '_page_pos-host-barcodes' === $screen_id ) {
			wp_enqueue_style( 'pos-host-barcode-options' );
		}

		// Add/edit receipt.
		if ( 'pos_receipt' === $screen_id ) {
			wp_enqueue_style( 'customize-controls' );
		}

		// Our custom post type pages.
		if ( in_array( $screen_id, array( 'pos_host_register', 'pos_outlet', 'pos_grid' ), true ) ) {
			wp_enqueue_style( 'pos-host-admin-meta-boxes' );
		}
	}

	/**
	 * Enqueue admin scripts.
	 */
	public function admin_scripts() {
		global $wp_scripts, $wp_styles, $post;

		$screen           = get_current_screen();
		$screen_id        = $screen ? $screen->id : '';
		$pos_host_screen_id = POS_HOST()->plugin_screen_id();
		$wc_screen_id     = POS_HOST()->wc_screen_id();

		// The scripts needed for loading pos-host-admin.
		$pos_host_admin_scripts = array(
			'jquery',
			'jquery-ui-core',
			'jquery-ui-datepicker',
			'jquery-blockui',
			'jquery-tiptip',
			'wc-enhanced-select',
			'editor',
			'thickbox',
			'postbox',
		);
		if ( wp_script_is( 'woocommerce_admin' ) ) {
			$pos_host_admin_scripts[] = 'woocommerce_admin';
		}

		// Register admin scripts.
		wp_register_script( 'qrcode', POS_HOST()->plugin_url() . '/assets/vendor/qrcode.min.js', array( 'jquery' ), POS_HOST_VERSION, true );
		wp_register_script( 'bwip-js', POS_HOST()->plugin_url() . '/assets/vendor/bwip-js-min.js', array( 'jquery' ), POS_HOST_VERSION, true );
		wp_register_script( 'anysearch', POS_HOST()->plugin_url() . '/assets/vendor/anysearch.js', array( 'jquery' ), POS_HOST_VERSION, true );
		wp_register_script( 'less-js', POS_HOST()->plugin_url() . '/assets/vendor/less.min.js', array(), POS_HOST_VERSION, true );
		
                wp_register_script( 'pos-host-reports', POS_HOST()->plugin_url() . '/assets/dist/js/admin/reports.min.js', array( 'woocommerce_admin' ), POS_HOST_VERSION, true );
		wp_register_script( 'pos-host-admin', POS_HOST()->plugin_url() . '/assets/dist/js/admin/admin.min.js', $pos_host_admin_scripts, POS_HOST_VERSION, true );
		
                wp_localize_script(
			'pos-host-admin',
			'pos_host_params',
			apply_filters(
				'pos_host_params',
				array(
					'wc_version'                       => intval( WC_VERSION ),
					'ajax_url'                         => admin_url( 'admin-ajax.php' ),
					'admin_url'                        => admin_url(),
					'ajax_loader_url'                  => apply_filters( 'woocommerce_ajax_loader_url', WC()->plugin_url() . '/assets/images/ajax-loader@2x.gif' ),
					'search_products_and_variations'   => wp_create_nonce( 'search-products' ),
					'search_customers'                 => wp_create_nonce( 'search-customers' ),
					'check_user_card_uniqueness_nonce' => wp_create_nonce( 'check-user-card-uniqueness' ),
					'get_user_by_card_number_nonce'    => wp_create_nonce( 'get-user-by-card-number' ),
					'paymentsense_eod_report_nonce'    => wp_create_nonce( 'paymentsense-eod-report' ),
					'i18n_confirm_delete_register'     => __( 'Orders placed by the deleted register will be assigned to the Default Register.', 'woocommerce-point-of-sale' ),
					'i18n_confirm_delete_registers'    => __( 'Orders placed by the deleted registers will be assigned to the Default Register.', 'woocommerce-point-of-sale' ),
				)
			)
		);

		wp_register_script( 'pos-host-admin-meta-boxes', POS_HOST()->plugin_url() . '/assets/dist/js/admin/meta-boxes.min.js', array( 'jquery', 'wc-enhanced-select', 'selectWoo' ), POS_HOST_VERSION, true );
		wp_localize_script(
			'pos-host-admin-meta-boxes',
			'pos_host_admin_meta_boxes_params',
			array(
				'countries'                      => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
				'base_address_1'                 => WC()->countries->get_base_address(),
				'base_address_2'                 => WC()->countries->get_base_address_2(),
				'base_city'                      => WC()->countries->get_base_city(),
				'base_postcode'                  => WC()->countries->get_base_postcode(),
				'base_country'                   => WC()->countries->get_base_country(),
				'base_state'                     => '*' === WC()->countries->get_base_state() ? '' : WC()->countries->get_base_state(),
				'i18n_select_state_text'         => esc_attr__( 'Select an option&hellip;', 'woocommerce-point-of-sale' ),
				'i18n_email_error'               => __( 'Please enter in a valid email address.', 'woocommerce-point-of-sale' ),
				'i18n_phone_error'               => __( 'Please enter in a valid phone number.', 'woocommerce-point-of-sale' ),
				'i18n_fax_error'                 => __( 'Please enter in a valid fax number.', 'woocommerce-point-of-sale' ),
				'i18n_url_error'                 => __( 'Please enter in a valid URL.', 'woocommerce-point-of-sale' ),
				'i18n_confirm_use_store_address' => __( 'Are you sure you want to fill out the fields from the store address?', 'woocommerce-point-of-sale' ),
			)
		);

		wp_register_script( 'pos-host-admin-grids', POS_HOST()->plugin_url() . '/assets/dist/js/admin/grids.min.js', array( 'jquery' ), POS_HOST_VERSION, true );
		wp_localize_script(
			'pos-host-admin-grids',
			'pos_host_admin_grids_params',
			array(
				'grid_id'               => isset( $post->ID ) ? $post->ID : '',
				'grid_tile_nonce'       => wp_create_nonce( 'grid-tile' ),
				'i18n_delete_all_tiles' => esc_js( __( 'Are you sure you want to delete all tiles in this grid? This cannot be undone.', 'woocommerce-point-of-sale' ) ),
				'i18n_delete_tile'      => esc_js( __( 'Are you sure you want to delete this tile? This cannot be undone.', 'woocommerce-point-of-sale' ) ),
			)
		);

		wp_register_script( 'pos-host-admin-receipts', POS_HOST()->plugin_url() . '/assets/dist/js/admin/receipts.min.js', array( 'jquery', 'wp-codemirror', 'bwip-js' ), POS_HOST_VERSION, true );
		wp_localize_script(
			'pos-host-admin-receipts',
			'pos_host_admin_receipts_params',
			array(
				'receipt_id'                => isset( $_GET['post'] ) ? (int) $_GET['post'] : '',
				'update_receipt_nonce'      => wp_create_nonce( 'update-receipt' ),
				'date_i18n_nonce'           => wp_create_nonce( 'date-i18n' ),
				'i18n_field_name_empty'     => __( 'The field “Receipt Name” cannot be empty.', 'woocommerce-point-of-sale' ),
				'i18n_field_width_empty'    => __( 'The field “Receipt Width” cannot be empty.', 'woocommerce-point-of-sale' ),
				'i18n_field_width_negative' => __( 'The field “Receipt Width” cannot be a negative value.', 'woocommerce-point-of-sale' ),
			)
		);

		wp_register_script( 'pos-host-barcode-options', POS_HOST()->plugin_url() . '/assets/dist/js/admin/barcode-options.min.js', array( 'jquery' ), POS_HOST_VERSION, true );
		wp_localize_script(
			'pos-host-barcode-options',
			'pos_host_barcode',
			array(
				'ajax_url'                    => WC()->ajax_url(),
				'product_for_barcode_nonce'   => wp_create_nonce( 'product_for_barcode' ),
				'remove_item_notice'          => __( 'Are you sure you want to remove the selected items?', 'woocommerce-point-of-sale' ),
				'select_placeholder_category' => __( 'Search for a category&hellip;', 'woocommerce-point-of-sale' ),
			)
		);

		wp_register_script( 'pos-host-admin-settings', POS_HOST()->plugin_url() . '/assets/dist/js/admin/settings.min.js', array( 'jquery' ), POS_HOST_VERSION, true );
		wp_localize_script(
			'pos-host-admin-settings',
			'pos_host_admin_settings_params',
			array(
				'time'        => time(),
				'i18n_note'   => __( 'Note', 'woocommerce-point-of-sale' ),
				'i18n_coin'   => __( 'Coin', 'woocommerce-point-of-sale' ),
				'i18n_remove' => __( 'Remove', 'woocommerce-point-of-sale' ),
			)
		);

		// Receipt printing page.
		if ( isset( $_GET['print_pos_receipt'] ) ) {
			// Dequeue all registered scripts so far.
			$wp_scripts->queue = array();
			$wp_styles->queue  = array();

			// We only need to enqueue the following scripts here.
			wp_enqueue_script( 'less-js' );
			wp_enqueue_script( 'bwip-js' );

			// Exit to prevent enqueuing any other assets.
			return;
		}

		// Load the necessary assets for the media JS APIs.
		if (
			in_array( $screen_id, array( 'pos_receipt' ), true ) ||
			$pos_host_screen_id . '_page_pos-host-settings' === $screen_id
		) {
			wp_enqueue_media();
		}

		// Admin pages that are created/modified by the plugin.
		if ( in_array( $screen_id, pos_host_get_screen_ids(), true ) ) {
			wp_enqueue_script( 'postbox' );
			wp_enqueue_script( 'pos-host-admin' );
		}

		// Orders page.
		if ( in_array( $screen_id, array( 'shop_order', 'edit-shop_order' ), true ) ) {
			wp_enqueue_script( 'anysearch' );
		}

		// Reports page.
		if ( $wc_screen_id . '_page_wc-reports' === $screen_id && isset( $_GET['tab'] ) && 'pos' === $_GET['tab'] ) {
			wp_enqueue_script( 'pos-host-reports' );
		}

		// Barcodes page.
		if ( $pos_host_screen_id . '_page_pos-host-barcodes' === $screen_id ) {
			wp_enqueue_script( 'jquery-cardswipe' );
			wp_enqueue_script( 'qrcode' );
			wp_enqueue_script( 'pos-host-barcode-options' );
		}

		// Stock controller page.
		if ( $pos_host_screen_id . '_page_pos-host-stock-controller' === $screen_id ) {
			wp_enqueue_script( 'anysearch' );
		}

		// Profile and User edit page.
		if ( in_array( $screen_id, array( 'profile', 'user-edit' ), true ) ) {
			wp_enqueue_script( 'jquery-cardswipe' );
		}

		// Our custom post type pages.
		if ( in_array( $screen_id, array( 'pos_host_register', 'pos_host_outlet', 'pos_grid' ), true ) ) {
			wp_enqueue_script( 'wc-admin-meta-boxes' );
			wp_enqueue_script( 'pos-host-admin-meta-boxes' );
		}

		// Add/edit outlets.
		if ( 'pos_host_outlet' === $screen_id ) {
			wp_enqueue_script( 'wc-users' );
		}

		// Add/edit grids.
		if ( 'pos_grid' === $screen_id ) {
			wp_enqueue_script( 'wc-backbone-modal' );
			wp_enqueue_script( 'pos-host-admin-grids' );
		}

		// Add/edit receipt.
		if ( 'pos_receipt' === $screen_id ) {
			wp_enqueue_code_editor( array() );
			wp_enqueue_script( 'customize-controls' );
			wp_enqueue_script( 'pos-host-admin-receipts' );
			wp_enqueue_script( 'less-js' );
			wp_enqueue_script( 'bwip-js' );
		}

		// Settings page.
		if ( $pos_host_screen_id . '_page_pos-host-settings' === $screen_id ) {
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'pos-host-admin-settings' );
		}
	}
}

return new POS_HOST_Admin_Assets();
