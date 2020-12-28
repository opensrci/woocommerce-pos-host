<?php
/**
 * Admin View: Quick Edit Product
 *
 * @package WooCommerce_pos_host/Admin/Views
 */

defined( 'ABSPATH' ) || exit;
?>
<fieldset class="inline-edit-col-left clear">
	<div id="visibility-fields" class="inline-edit-col">
		<h4><?php esc_html_e( 'Point of Sale', 'woocommerce-pos-host' ); ?></h4>
		<div class="inline-edit-group">
			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Visibility', 'woocommerce-pos-host' ); ?></span>
				<span class="input-text-wrap">
					<select class="pos_visibility" name="_pos_visibility">
					<?php
					$pos_visibility = get_post_meta( $post->ID, '_pos_visibility', true );
					$pos_visibility = $pos_visibility ? $pos_visibility : 'pos_online';

					$visibility_options = apply_filters(
						'pos_host_visibility_options',
						array(
							'pos_online' => __( 'POS &amp; Online', 'woocommerce-pos-host' ),
							'pos'        => __( 'POS Only', 'woocommerce-pos-host' ),
							'online'     => __( 'Online Only', 'woocommerce-pos-host' ),
						)
					);

					foreach ( $visibility_options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $pos_visibility, false ) . '>' . esc_html( $value ) . '</option>';
					}
					?>
					</select>
				</span>
			</label>
		</div>
	</div>
</fieldset>
