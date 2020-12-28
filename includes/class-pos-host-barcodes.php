<?php
/**
 * Barcodes Page
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Barcodes Class
 */
class POS_HOST_Barcodes {

	/**
	 * The single instance of the class.
	 *
	 * @var POS_HOST_Barcodes
	 */
	protected static $_instance = null;

	/**
	 * Main POS_HOST_Barcodes Instance.
	 *
	 * Ensures only one instance of POS_HOST_Barcodes is loaded or can be loaded.
	 *
	 * @return POS_HOST_Barcodes Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {}

	public function display_single_barcode_page() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Barcode Label Printing', 'woocommerce-pos-host' ); ?></h2>
			<p><?php esc_html_e( 'Barcode labels for your store can be printed here. To change the the fields to print in the label, you can check the boxes for the labels to print in the panel to the right.', 'woocommerce-pos-host' ); ?></p>
			
			<div id="lost-connection-notice" class="error hidden">
				<p><span class="spinner"></span> <?php echo wp_kses_post( __( '<strong>Connection lost.</strong> Saving has been disabled until you&#8217;re reconnected.', 'woocommerce-pos-host' ) ); ?>
				<span class="hide-if-no-sessionstorage"><?php esc_html_e( 'We&#8217;re backing up this post in your browser, just in case.', 'woocommerce-pos-host' ); ?></span>
				</p>
			</div>
			<form action="" method="post" id="edit_pos_host_barcode" onsubmit="return false;">
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2">
						<div id="postbox-container-2" class="postbox-container">
							<div class="postbox products_list">
								<div class="inside">
									<?php include_once 'views/html-admin-barcode-options.php'; ?>
								</div>
							</div>
						</div>
						<div id="postbox-container-1" class="postbox-container">
							<div class="postbox ">
								<h3 class="hndle">
									<label ><?php esc_html_e( 'Print Settings', 'woocommerce-pos-host' ); ?></label>
								</h3>
								<div class="inside" id="barcode_print_settings">
									<div>
										<label for="number_of_labels"><?php esc_html_e( 'Number of Labels', 'woocommerce-pos-host' ); ?></label>
										<input type="number" step="1" name="number_of_labels" id="number_of_labels">
									</div>
									<div>
										<label for="label_type"><?php esc_html_e( 'Label Type', 'woocommerce-pos-host' ); ?></label>
										<select id="label_type" name="label_type" class="wc-enhanced-select">
											<optgroup label="<?php esc_attr_e( 'Continuous', 'woocommerce-pos-host' ); ?>">
											<option value="continuous_feed"><?php esc_html_e( 'Continuous Feed', 'woocommerce-pos-host' ); ?></option>
											<option value="con_4_3"><?php esc_html_e( 'Continuous Feed (4cm x 3cm)', 'woocommerce-pos-host' ); ?></option>
											<option value="con_4_2"><?php esc_html_e( 'Continuous Feed (40mm x 20mm)', 'woocommerce-pos-host' ); ?></option>
											</optgroup>
											<optgroup label="<?php esc_attr_e( 'A4', 'woocommerce-pos-host' ); ?>">
											<option value="a4"><?php esc_html_e( '2 x 7', 'woocommerce-pos-host' ); ?></option>
											<option value="a4_30"><?php esc_html_e( '3 x 7', 'woocommerce-pos-host' ); ?></option>
											<option value="a4_27"><?php esc_html_e( '3 x 9', 'woocommerce-pos-host' ); ?></option>
											</optgroup>
											<optgroup label="<?php esc_html_e( 'Letter', 'woocommerce-pos-host' ); ?>">
											<option value="letter"><?php esc_html_e( '4 x 5', 'woocommerce-pos-host' ); ?></option>
											<option value="per_sheet_30"><?php esc_html_e( '3 x 10', 'woocommerce-pos-host' ); ?></option>
											<option value="per_sheet_80"><?php esc_html_e( '4 x 20', 'woocommerce-pos-host' ); ?></option>
											</optgroup>
											<optgroup label="<?php esc_attr_e( 'Other', 'woocommerce-pos-host' ); ?>">
											<option value="jew_50_10"><?php esc_html_e( 'Jewellery Tag (50mm x 10mm)', 'woocommerce-pos-host' ); ?></option>
											</optgroup>
										</select>
									</div>
									<div>
										<label for="label_fields"><?php esc_html_e( 'Product Fields', 'woocommerce-pos-host' ); ?></label>
										<div><label><input type="checkbox" name="fields_print" class="fields_to_print" id="field_barcode" checked="checked"><?php esc_html_e( 'Barcode', 'woocommerce-pos-host' ); ?></label></div>
										<div><label><input type="checkbox" name="fields_print" class="fields_to_print" id="field_sku" checked="checked"><?php esc_html_e( 'SKU', 'woocommerce-pos-host' ); ?></label></div>
										<div><label><input type="checkbox" name="fields_print" class="fields_to_print" id="field_sku_label"><?php esc_html_e( 'SKU Label', 'woocommerce-pos-host' ); ?></label></div>
										<div><label><input type="checkbox" name="fields_print" class="fields_to_print" id="field_name" checked="checked"><?php esc_html_e( 'Product Name', 'woocommerce-pos-host' ); ?></label></div>
										<div><label><input type="checkbox" name="fields_print" class="fields_to_print" id="field_price" checked="checked"><?php esc_html_e( 'Price', 'woocommerce-pos-host' ); ?></label></div>
										<div><label><input type="checkbox" name="fields_print" class="fields_to_print" id="field_meta_value"><?php esc_html_e( 'Variation', 'woocommerce-pos-host' ); ?></label></div>
										<div><label><input type="checkbox" name="fields_print" class="fields_to_print" id="field_meta_title"><?php esc_html_e( 'Variation Label', 'woocommerce-pos-host' ); ?></label></div>
									</div>
									<div>
										<p class="description" style="margin-top: 1em;"><?php esc_html_e( 'Note: set your paper size to the corresponding template size. Printing margins should be set to none to ensure accurate printing.', 'woocommerce-pos-host' ); ?></p>
									</div>
									
								</div>
								<div id="major-publishing-actions">
									<div id="publishing-action">
										<span class="spinner"></span>
										<input type="button" value="Print" class="button button-primary button-large" id="print_barcode">
									</div>
									<div class="clear"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="clear"></div>
			</form>
		</div>
		<?php
	}
}
