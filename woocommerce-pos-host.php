<?php
/**
 * Plugin Name: pos.host for WooCommerce
 * Plugin URI: 
 * Description: pos.host engine for WooCommerce.
 * Author: pos.host
 * Author URI: https://pos.host/about
 * Version: 0.0.1
 * Text Domain: woocommerce-pos-host
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2020 pos.host
 * Forked from WooCommerce Point of Sale Copyright: (c) 2013-2020 Actuality Extensions (info@actualityextensions.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WooCommerce_pos_host
 *
 * WC requires at least: 3.5.0
 * WC tested up to: 4.7.0
 */


defined( 'ABSPATH' ) || exit;

if ( ! defined( 'POS_HOST_PLUGIN_FILE' ) ) {
	define( 'POS_HOST_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'POS_HOST_VERSION' ) ) {
	define( 'POS_HOST_VERSION', "0.0.3");
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';


if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	add_action(
		'admin_notices',
		function() {
			/* translators: 1. URL link. */
			echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'POS HOST for WooCommerce requires WooCommerce to be installed and active. You can download %s here.', 'woocommerce-pos-host' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
		}
	);

	return;
}


/* Include the main POS_HOST class.
 * Entran point 
 * 
 */
if ( ! class_exists( 'POS_HOST', false ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-pos-host.php';
}

/**
 * Returns the main instance of POS_HOST.
 *
 * @since 0.0.1
 * @return POS_HOST
 */
function POS_HOST() {
	return POS_HOST::instance();
}

// Global for backwards compatibility.
$GLOBALS['pos_host'] = POS_HOST();
