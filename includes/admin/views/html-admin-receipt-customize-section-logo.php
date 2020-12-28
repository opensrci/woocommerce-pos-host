<?php
/**
 * Receipt Customizer - Logo Details
 *
 * @var object $receipt_object
 *
 * @package WooCommerce_pos_host/Admin/Views
 */

$logo     = wp_get_attachment_image_src( (int) $receipt_object->get_logo( 'edit' ), 'full' );
$logo_src = $logo ? $logo[0] : '';
?>
<!-- Shop Logo -->
<li id="customize-control-logo" class="customize-control<?php echo $logo ? ' selected' : ''; ?>">
	<span class="customize-control-title"><?php esc_html_e( 'Shop Logo', 'woocommerce-pos-host' ); ?></span>
	<span class="description customize-control-description"><?php esc_html_e( 'Upload a logo representing your shop or business.', 'woocommerce-pos-host' ); ?></span>
	<div class="attachment-media-view">
		<button type="button" class="upload button-add-media"><?php esc_html_e( 'Select image', 'woocommerce-pos-host' ); ?></button>
		<div class="thumbnail thumbnail-image">
			<img class="attachment-thumb" src="<?php echo esc_url( $logo_src ); ?>" />
			<input type="hidden" name="logo" id="logo" value="<?php echo esc_attr( $receipt_object->get_logo( 'edit' ) ); ?>" />
		</div>
		<div class="actions"><button type="button" class="button remove"><?php esc_html_e( 'Remove', 'woocommerce-pos-host' ); ?></button></div>
	</div>
</li>

<!-- Logo Position -->
<li class="customize-control customize-control-select">
	<label class="customize-control-title" for="logo_position"><?php esc_html_e( 'Logo Position', 'woocommerce-pos-host' ); ?></label>
	<span class="description customize-control-description"><?php esc_html_e( 'Choose the position of the shop logo that is printed.', 'woocommerce-pos-host' ); ?></span>
	<select id="logo_position" name="logo_position">
		<option value="left" <?php selected( $receipt_object->get_logo_position(), 'left', true ); ?> ><?php esc_html_e( 'Left', 'woocommerce-pos-host' ); ?></option>
		<option value="center" <?php selected( $receipt_object->get_logo_position(), 'center', true ); ?>><?php esc_html_e( 'Center', 'woocommerce-pos-host' ); ?></option>
		<option value="right" <?php selected( $receipt_object->get_logo_position(), 'right', true ); ?>><?php esc_html_e( 'Right', 'woocommerce-pos-host' ); ?></option>
	</select>
</li>

<!-- Logo Size -->
<li class="customize-control customize-control-select">
	<label class="customize-control-title" for="logo_size"><?php esc_html_e( 'Logo Size', 'woocommerce-pos-host' ); ?></label>
	<span class="description customize-control-description"><?php esc_html_e( 'Choose the size of the shop logo that is printed.', 'woocommerce-pos-host' ); ?></span>
	<select id="logo_size" name="logo_size">
		<option value="small" <?php selected( $receipt_object->get_logo_size(), 'small', true ); ?>><?php esc_html_e( 'Small', 'woocommerce-pos-host' ); ?></option>
		<option value="normal" <?php selected( $receipt_object->get_logo_size(), 'normal', true ); ?> ><?php esc_html_e( 'Normal', 'woocommerce-pos-host' ); ?></option>
		<option value="large" <?php selected( $receipt_object->get_logo_size(), 'large', true ); ?>><?php esc_html_e( 'Large', 'woocommerce-pos-host' ); ?></option>
	</select>
</li>
