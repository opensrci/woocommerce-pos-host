<?php
/**
 * Load Assets
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Assets', false ) ) {
	return new POS_HOST_Assets();
}

/**
 * POS_HOST_Assets.
 *
 * Handles assets loading on the front-end.
 */
class POS_HOST_Assets {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'styles' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function styles() {
		// Register styles.
		wp_register_style( 'pos-host-fonts', POS_HOST()->plugin_url() . '/assets/dist/css/fonts.min.css', array(), POS_HOST_VERSION );
		wp_register_style( 'pos-host-frontend', POS_HOST()->plugin_url() . '/assets/dist/css/frontend.min.css', array(), POS_HOST_VERSION );

		// Enqueue styles.
		wp_enqueue_style( 'pos-host-fonts' );
		wp_enqueue_style( 'pos-host-frontend' );
	}
}

return new POS_HOST_Assets();
