<?php
/**
 * Admin view: Notice - Install.
 *
 * @package WooCommerce_pos_host/Admin/Views
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="message" class="updated woocommerce-message">
	<a class="woocommerce-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'pos-host-hide-notice', 'install' ), 'pos_host_hide_notices_nonce', '_pos_host_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'woocommerce-pos-host' ); ?></a>

	<p><?php echo wp_kses_post( __( '<strong>Welcome to pos.host!</strong>', 'woocommerce-pos-host' ) ); ?></p>
	<p class="submit">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=pos_settings' ) ); ?>" class="button-primary"><?php esc_html_e( 'Settings', 'woocommerce-pos-host' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pos_register' ) ); ?>" class="button-secondary"><?php esc_html_e( 'Registers', 'woocommerce-pos-host' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=pos_outlet' ) ); ?>" class="button-secondary"><?php esc_html_e( 'Outlets', 'woocommerce-pos-host' ); ?></a>
	</p>
</div>
