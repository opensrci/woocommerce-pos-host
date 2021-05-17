<?php
/**
 * My Account Page
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_My_Account', false ) ) {
	return new POS_HOST_My_Account();
}

/**
 * POS_HOST_My_Account.
 */
class POS_HOST_My_Account {

	public function __construct() {
		add_action( 'init', array( $this, 'pos_host_endpoint' ) );
		add_filter( 'woocommerce_get_query_vars', array( $this, 'pos_host_query_vars' ), 9999, 1 );

		// Show this tab for the authorized users.
		if ( current_user_can( 'view_register' ) ) {
			add_filter( 'woocommerce_account_menu_items', array( $this, 'pos_host_myaccount_tab' ) );
			add_action( 'woocommerce_account_pos_host_endpoint', array( $this, 'pos_host_myaccount_content' ) );
		}

                add_shortcode('pos_host_myaccount', array( $this, 'pos_host_myaccount_content' ));

	}

	public function pos_host_endpoint() {
		add_rewrite_endpoint( 'pos-host', EP_ROOT | EP_PAGES );
		flush_rewrite_rules();
	}

	public function pos_host_query_vars( $vars ) {
		$vars[] = 'pos-host';
		return $vars;
	}

	public function pos_host_myaccount_tab( $items ) {
		if ( ! array_key_exists( 'edit-account', $items ) ) {
			$items['pos-host'] = 'pos host';

			return $items;
		}

		$new_items = array();

		foreach ( $items as $key => $item ) {
			$new_items[ $key ] = $item;
			if ( 'edit-account' === $key ) {
				$new_items['pos-host'] = 'pos host';
			}
		}

		return $new_items;
	}

	public static function pos_host_myaccount_content() {
                 ob_start();
		include_once 'views/html-my-account-tab.php';
                 return ob_get_clean();
	}
}

return new POS_HOST_My_Account();
