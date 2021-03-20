<?php
/**

 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'PH_CLONER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PH_CLONER_LOG_DIR', PH_CLONER_PLUGIN_DIR . 'logs/' );
// Load external libraries.
require_once PH_CLONER_PLUGIN_DIR . 'vendor/autoload.php';
require_once PH_CLONER_PLUGIN_DIR . 'ph-cloner-starter.php';

/**
 * Main core of PH_Cloner plugin.
 *
 * This class is an umbrella for all cloner components - managing instances of each of the other utility classes,
 * addons, sections, background processes, etc. and letting them refer to each other. It also handles all the basic
 * admin hooks for menus, assets, notices, templates, etc.
 */
final class PH_Cloner {

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version = '1.0';

	/**
	 * Menu Slug
	 *
	 * @var string
	 */
	public $menu_slug = 'ph-cloner';

	/**
	 * Shortcut reference to access $wpdb without declaring a global in every method
	 *
	 * @var wpdb object
	 */
	public $db;

         /**
	 * Instance of PH_Cloner_Log
	 *
	 * @var PH_Cloner_Log object
	 */
	public $log;

	/**
	 * Prefix to add to temporary tables by modes that require them
	 *
	 * @var string
	 */
	public $temp_prefix = '_mig_';

	/**
	 * Singleton instance of PH_Cloner
	 *
	 * @var PH_Cloner
	 */
	private static $instance = null;

	/**
	 * Get singleton instance of PH_Cloner.
	 *
	 * @return PH_Cloner
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new PH_Cloner();
		}
		return self::$instance;
	}

	/**
	 * PH_Cloner constructor.
	 */
	private function __construct() {
		// Set instance to prevent infinite loop.
		self::$instance = $this;

		// Create $wpdb access shortcut to save declaring global every place it's used.
		global $wpdb;
		$this->db = $wpdb;
                //@todo debug
                add_shortcode('ph_cloner_start', 'ph_cloner_start');
                add_shortcode('ph_cloner_status', 'ph_cloner_status');

	}

	/**
	 * Initialize Cloner modes, sections, UI, etc.
	 *
	 * The difference between this and the constructor is that anything that needs to use localization has to go here.
	 */
	public function init() {
                 
		// Install custom tables after cloner init.
		//$this->install_tables();
                 // Register background processes.
                 $processes = apply_filters(
			'ph_cloner_core_processes',
			[
				'tables',
				'rows',
				'files',
			]
		);
		foreach ( $processes as $process ) {
			$this->register_process( $process );
		}

	}


	/**
	 * Retrieve list of database tables for a specific site.
	 *
	 * @param int  $site_id Database prefix of the site.
	 * @param bool $exclude_global Exclude global tables from the list (only relevant for main site).
	 * @return array
	 */
	public function get_site_tables( $site_id, $exclude_global = true ) {
		if ( empty( $site_id ) || ! is_multisite() ) {
			// All tables - don't filter by any id.
			$prefix = $this->db->esc_like( $this->db->base_prefix );
			$tables = $this->db->get_col( "SHOW TABLES LIKE '{$prefix}%'" );
		} elseif ( ! is_main_site( $site_id ) ) {
			// Sub site tables - a prefix like wp_2_ so we can get all matches without having to filter out global tables.
			$prefix = $this->db->esc_like( $this->db->get_blog_prefix( $site_id ) );
			$tables = $this->db->get_col( "SHOW TABLES LIKE '{$prefix}%'" );
		} else {
			// Root site tables - a main prefix like wp_ requires that we filter out both global and other subsites' tables.
			// Define patterns for both subsites (eg wp_2_...) and global tables (eg wp_blogs) which should not be copied.
			$wp_global_tables  = $this->db->tables( 'global', false, $site_id );
			$all_global_tables = apply_filters( 'ph_cloner_global_tables', $wp_global_tables );
			$global_pattern    = "/^{$this->db->base_prefix}(" . implode( '|', $all_global_tables ) . ')$/';
			$subsite_pattern   = "/^{$this->db->base_prefix}\d+_/";
			$temp_pattern      = '/^' . ph_cloner()->temp_prefix . '/';
			$prefix            = $this->db->esc_like( $this->db->base_prefix );
			$all_tables        = $this->db->get_col( "SHOW TABLES LIKE '{$prefix}%'" );
			$tables            = [];
			foreach ( $all_tables as $table ) {
				$is_global_table  = preg_match( $global_pattern, $table );
				$is_subsite_table = preg_match( $subsite_pattern, $table );
				$is_temp_table    = preg_match( $temp_pattern, $table );
				if ( ( ! $is_global_table || ! $exclude_global ) && ! $is_subsite_table && ! $is_temp_table ) {
					array_push( $tables, $table );
				}
			}
		}
		// Apply optional filter and return.
		return apply_filters( 'ph_cloner_site_tables', $tables, $site_id );
	}

	/**
	 * Check whether the current user can run a clone operation and whether nonce is valid, then optionally die or return false.
	 *
	 * @param bool $die Whether to die on failure.
	 * @return bool
	 */
	public function check_permissions( $die = true ) {

                return true;
	}
	/*______________________________________
	|
	|  Background Process Registration
	|_____________________________________
	*/

	/**
	 * Includes and registers a background process with the cloner.
	 *
	 * Similar to register_addon, this has no functional impact other than providing an
	 * easy way to reference a background process instance.
	 *
	 * @param string $id Lowercase, underscore separated unique part of process classname.
	 *                   Example: 'some_thing' for the class PH_Cloner_Some_Thing_Process.
	 * @param string $dir Grandparent path (containing /processes/{file}.php).
	 * @return bool
	 */
	public function register_process( $id, $dir = PH_CLONER_PLUGIN_DIR ) {
		$filename  = str_replace( '_', '-', strtolower( "class-ph-cloner-{$id}-process.php" ) );
		$path      = apply_filters( 'ph_cloner_process_path', trailingslashit( $dir ) . "processes/{$filename}", $id );
		$suffix    = implode(
			'_',
			array_map(
				function ( $word ) {
					return ucfirst( $word );
				},
				explode( '_', $id )
			)
		);
		$classname = "PH_Cloner_{$suffix}_Process";
		include_once $path;
		if ( class_exists( $classname ) ) {
			$this->processes[ $id ] = new $classname();
			return true;
		}
		return false;
	}

	/**
	 * Get the instance of an background process class by its id.
	 *
	 * @param string $id Lowercase, underscore separated unique part of process classname.
	 *                   Example: 'some_thing' for the class PH_Cloner_Some_Thing_Process.
	 * @return PH_Cloner_Process
	 */
	public function get_process( $id ) {
		return isset( $this->processes[ $id ] ) ? $this->processes[ $id ] : new $id();
	}
}

/**
 * Return singleton instance of PH_Cloner
 * 
 * @return PH_Cloner
 */
function ph_cloner() {
	return PH_Cloner::get_instance();
}
ph_cloner();
