<?php
/**
 * Autoloader
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Autoloader', false ) ) {
	return new POS_HOST_Autoloader();
}

/**
 * POS_HOST_Autoloader.
 */
class POS_HOST_Autoloader {

	/**
	 * Path to the includes directory.
	 *
	 * @var string
	 */
	private $include_path = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->include_path = untrailingslashit( plugin_dir_path( POS_HOST_PLUGIN_FILE ) ) . '/includes/';
	}

	/**
	 * Take a class name and turn it into a file name.
	 *
	 * @param  string $class Class Name.
	 * @return string File name.
	 */
	private function get_file_name_from_class( $class ) {
		return 'class-' . str_replace( '_', '-', $class ) . '.php';
	}

	/**
	 * Include a class file.
	 *
	 * @param string $path Class file path.
	 * @return bool Whether successful or not.
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once $path;
			return true;
		}
		return false;
	}

	/**
	 * Auto-load POS_HOST classes on demand to reduce memory consumption.
	 *
	 * @todo Load the new files.
	 *
	 * @param string $class Class name.
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );
		$file  = $this->get_file_name_from_class( $class );
		$path  = '';

		if ( 0 === strpos( $class, 'pos_host_screen' ) ) {
			$path = $this->include_path . 'screen/';
		} elseif ( 0 === strpos( $class, 'pos_host_table' ) ) {
			$path = $this->include_path . 'tables/';
		} elseif ( 0 === strpos( $class, 'pos_host_meta_box' ) ) {
			$path = $this->include_path . 'admin/meta-boxes/';
		} elseif ( 0 === strpos( $class, 'pos_host_admin' ) ) {
			$path = $this->include_path . 'admin/';
		}

		if ( empty( $path ) || ( ! $this->load_file( $path . $file ) && 0 === strpos( $class, 'pos_host_' ) ) ) {
			$this->load_file( $this->include_path . $file );
		}
	}
}

return new POS_HOST_Autoloader();
