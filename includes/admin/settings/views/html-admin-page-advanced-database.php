<?php
/**
 * Advanced Settings - Database Options
 *
 * @todo Please clean up the mess here.
 * @package WooCommerce_pos_host/Admin/Settings/Views
 */
global $wpdb;


if ( ! empty( $_POST['pos_host_force_update_database'] ) ) {
	check_admin_referer( 'pos-host-settings' );

	$last_update['date'] = gmdate( 'Y-m-d H:i' );
	foreach ( $this->force_updates as $version => $update ) {
		include $update;
		$last_update['version'] = $version;
	}

	WC_POS_Install::update_pos_version( $last_update['version'] );
	update_option( 'pos_host_last_force_db_update', $last_update );
}

if ( ! empty( $_POST['pos_host_reset_settings'] ) ) {
	check_admin_referer( 'pos-host-settings' );

	$wpdb->query(
		"DELETE FROM {$wpdb->options}
		WHERE option_name LIKE 'pos\_host\_%'
		AND option_name NOT IN (
			'pos_host_db_version',
			'pos_host_last_force_db_update',
			'pos_host_admin_notices',
			'pos_host_custom_product_id',
			'pos_host_default_outlet',
			'pos_host_default_receipt',
			'pos_host_default_register',
			'pos_host_meta_box_errors',
			'pos_host_force_refresh_db'
		);" );
}

$last_update = get_option( 'pos_host_last_force_db_update', '' );
$last_update = empty( $last_update ) ? array( 'date' => '' ) : $last_update;
?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php esc_html_e( 'Database Version', 'woocommerce-pos-host' ); ?>
			</th>
			<td class="forminp">
				<span><?php echo esc_html( get_option( 'pos_host_db_version' ) ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php esc_html_e( 'Last Force Update', 'woocommerce-pos-host' ); ?>
			</th>
			<td class="forminp">
				<span><?php echo empty( $last_update['date'] ) ? esc_html__( 'Database has never been force updated.', 'woocommerce-pos-host' ) : esc_html( $last_update['date'] ); ?></span>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php esc_html_e( 'Update Database', 'woocommerce-pos-host' ); ?>
			</th>
			<td class="forminp">
				<button name="pos_host_force_update_database" type="submit" class="button" value="1"><?php esc_html_e( 'Force Update', 'woocommerce-pos-host' ); ?></button>
				<p class="description">
					<?php esc_html_e( 'Use with caution. This tool will update the database to the latest version - useful when settings are not being applied as per configured in settings, registers, receipts and outlets.', 'woocommerce-pos-host' ); ?>
				</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<?php esc_html_e( 'Reset Settings', 'woocommerce-pos-host' ); ?>
			</th>
			<td class="forminp">
				<input id="pos_host_reset_settings" name="wc_pos_reset_settings" type="submit" class="button" value="<?php esc_attr_e( 'Reset Settings', 'woocommerce-pos-host' ); ?>">
				<p class="description">
					<?php esc_html_e( 'Reset all plugin settings.', 'woocommerce-pos-host' ); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>
