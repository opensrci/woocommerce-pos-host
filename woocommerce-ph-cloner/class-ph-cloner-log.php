<?php
/**
 * Cloner Logging class.
 *
 * @package PH_Cloner
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * PH_Cloner_Log class.
 *
 * Utility class for creating debug logs while running cloning processes.
 */
class PH_Cloner_Log {

	/**
	 * Filepath to summary log
	 *
	 * @var string
	 */
	private $log_file;

	/**
	 * File pointer for current log from fopen
	 *
	 * @var resource
	 */
	private $log_handle;
	
         /**
	 * start time
	 *
	 * @var
	 */
	private $start_time;
         
        /**
	 * end time
	 *
	 * @var
	 */
	private $end_time;
        
        	/**
	 * Singleton instance of this class
	 *
	 */
	private static $instance;
        
        	/**
	 * Singleton instance of this class
	 *
	 */
	private $report_key = 'ph_clone';
        
        	/**
	 * Singleton instance of this class
	 *
	 */
	private static $initialized;
        
	/**
	 * Regex for matching log filenames.
	 *
	 * @var string
	 */
	public $log_file_pattern = '/ph-cloner-(\d{8})-(\d{6})(?:-\w{8})?\.log/';

	/**
	 * Get singleton instance
	 *
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * PH_Cloner_Log constructor.
	 */
	public function __construct() {
	}

	/**
	 * Determine if logging is enabled or not
	 *
	 * @return bool
	 */
	public function is_debug() {
//@todo debug    
            return true;
	}

	/**
	 * Generate a new log filename
	 *
	 * @return string
	 */
	public function generate_file() {
		// Add a hash to the filename, to make it super hard to crawl for logs.
		$hash      = strtolower( wp_generate_password( 8, false ) );
		$timestamp = date( 'Ymd-His' );
		return PH_CLONER_LOG_DIR . "ph-cloner-{$timestamp}-{$hash}.log";
	}

	/**
	 * Set the log file and open append pointer/handle
	 *
	 * @param string $filename Path to log file.
	 */
	public function set_file( $filename ) {
		$this->log_file = apply_filters( 'ph_cloner_log_file', $filename );
		ph_cloner_request()->set( 'log_file', $this->log_file );
		ph_cloner_request()->save();
		// Open file pointer for logging.
		if ( ! empty( $this->log_file ) && is_writeable( dirname( $this->log_file ) ) 
                        ) {
			$this->log_handle = fopen( $this->log_file, 'a' );
		}
	}

	/**
	 * Start logging - define the log file path, and add header if needed
	 *
	 * This should be called at the beginning of any process where logging
	 * should be performed, because log() function calls made before start()
	 * will be ignored. This enables easy control of when logging should happen
	 * and shouldn't - i.e. not causing logging during validation, other misc
	 * ajax requests, etc.
	 */
	public function init() {
		// Make sure no loop.
		if ( $this->initialized) {
			return;
		}
                 $this->initialized = true;
                 
		// Make sure debug/logging is on.
		if ( ! $this->is_debug() ) {
			return;
		}
                
		// Set up log if needed (don't bother if start() was already called in this session).
		if ( ! is_resource( $this->log_handle ) ) {
                            // Define the default log file if it's not yet saved.
                            $this->set_file( $this->generate_file() );
                            $this->header();
		}
		// Check if log needs split due to size (larger than 5MB) for performance.
		if ( is_resource( $this->log_handle ) && filesize( $this->log_file ) > 5 * 1024 * 1024 ) {
			$old = $this->log_file;
			$new = $this->generate_file();
			$this->log( 'CONTINUING IN: <a href="' . $this->get_url( $new ) . '"></a>' . $new . '</a>' );
			$this->end( false );
			$this->set_file( $new );
			$this->header();
			$this->log( 'CONTINUING FROM: <a href="' . $this->get_url( $old ) . '">' . $old . '</a>' );
		}
	}

	/**
	 * Checks for a newer log file and switches to that one if so.
	 *
	 * Helpful for running in the middle of long background processes so
	 * logs don't way exceed the max log size.
	 */
	public function refresh() {
		$current_log =ph_cloner_request()->refresh()->get( 'log_file' );
		if ( ! empty( $current_log ) && $current_log !== $this->log_file ) {
			$this->end( false );
			$this->log_file   = $current_log;
			$this->log_handle = fopen( $this->log_file, 'a' );
		}
	}

	/**
	 * End logging - optionally add footer.
	 *
	 * Footer param is available because it will mess up formatting if we close a log
	 * that another background process is still writing to - if that's a possibility,
	 * don't worry about it and let the browser autoclose the tags.
	 *
	 * @param bool $do_footer Whether to output footer / closing tags.
	 */
	public function end( $do_footer = true ) {
		if ( $do_footer ) {
			$this->footer();
		}
		if ( is_resource( $this->log_handle ) ) {
			fclose( $this->log_handle );
		}
	}

	/**
	 * Run after a wpdb function call to check for and log any sql errors
	 *
	 * Also, log all queries in when additional debugging is on.
	 *
	 * @return void
	 */
	public function handle_any_db_errors() {
		if ( ! empty(ph_cloner()->db->last_error ) ) {
			// If there was an error, log it and the query it was for.
			$this->log( 'SQL ERROR: ' .ph_cloner()->db->last_error );
			$this->log( 'FOR QUERY: ' .ph_cloner()->db->last_query );
			ph_cloner()->process_manager->exit_processes(ph_cloner()->db->last_error );
		}
	}

	/*
	______________________________________
	|
	|  Log Outputs
	|_____________________________________
	*/

	/**
	 * Write data to log file
	 *
	 * This fails silently if the log file is not writable, so it's up to the caller to check
	 * for a writable log file and alert the user if there is a problem. Note that this accepts
	 * an array for the message. This is useful for including a string label to describe a
	 * variable (1st array element) followed by the variable itself for debugging (2nd el).
	 *
	 * @param mixed $message String or data to log.
	 * @param bool  $raw Whether to include timestamp and tr/td tags.
	 * @return bool
	 */
	public function log( $message, $raw = true ) {
                if (is_array($message)){
                    $message = implode(',',$message);
                }
		// If debug is off or the log directory isn't writable, don't log.
		if ( ! is_resource( $this->log_handle ) ) {
			return false;
		}

		// Shortcut if raw - don't bother formatting it.
		if ( true) {
			fwrite( $this->log_handle, $message );
			return true;
		}

	}

	/**
	 * Add separator line to log
	 * "break" name of the method class is not compatible with php 5.6
	 */
	public function log_break() {
		$this->log( '-----------------------------------------------------------------------------------------------------------' );
	}

	/**
	 * Open the HTML for a detail log and auto-log environment info
	 */
	public function header() {

		$open = "";
		$this->log( $open, true );

		$this->log( 'ENVIRONMENT DIAGNOSTICS:' );
		$this->log( 'WP Memory Limit:        ' . WP_MEMORY_LIMIT );
		$this->log( 'WP Debug Mode:          ' . WP_DEBUG );
		$this->log( 'WP Multisite:           ' . MULTISITE );
		$this->log( 'WP Subdomain Install:   ' . defined( 'SUBDOMAIN_INSTALL ' ) ? SUBDOMAIN_INSTALL : false );
		$this->log( 'PHP Post Max Size:      ' . ini_get( 'post_max_size') );
		$this->log( 'PHP Upload Max Size:    ' . ini_get( 'upload_max_size') );
		$this->log( 'PHP Memory Limit:       ' . ini_get( 'memory_limit') );
		$this->log( 'PHP Max Input Vars:     ' . ini_get( 'max_input_vars') );
		$this->log( 'PHP Max Execution Time: ' . ini_get( 'max_execution_time') );

		$this->log_break();

		$this->log( 'CLONER VERSION: ' .ph_cloner()->version );
		$this->log( 'ACTION:         ' .ph_cloner_request()->get( 'action' ) );
		$this->log( 'CLONING MODE:   ' .ph_cloner_request()->get( 'clone_mode' ) );

		$this->log_break();
	}

	/**
	 * 
	 */
	public function add_report( $log, $msg ) {
		$this->log( $log." : ".$msg );
	}
	/**
	 * Get an array of all saved report items
	 *
	 * @return array
	 */
	public function get_all_reports() {
		return get_site_option( $this->report_key );
	}
	/**
	 * Close the HTML of the log file - call when all logging is complete
	 */
	public function footer() {
		$this->log( $close, true );
	}

	/**
	 * Delete all log files from the logs directory
	 */
	public function delete_logs() {
		if ( is_dir( PH_CLONER_LOG_DIR ) ) {
			foreach ( scandir( PH_CLONER_LOG_DIR ) as $file ) {
				// Only include html log files.
				if ( ! preg_match( '/\.log$/', $file ) ) {
					continue;
				}
				unlink( PH_CLONER_LOG_DIR . "$file" );
			}
		}
	}
        	/**
	 * Save the start time for this cloning process
	 */
	public function set_start_time() {
                $this->start_time = microtime( true );
	}

	/**
	 * Get the start time for this cloning process
	 *
	 * @param bool $prepared Whether to format the raw timestamp before returning.
	 * @return string
	 */
	public function get_start_time( ) {
                 $date =$this->start_time;
		return $date ? $date->format( 'Y-m-d H:i:s' ) : '';
	}

	/**
	 * Save the end time for this cloning process
	 */
	public function set_end_time() {
                $this->end_time = microtime( true );
	}

	/**
	 * Get the end time for this cloning process
	 *
	 * @param bool $prepared Whether to format the raw timestamp before returning.
	 * @return string
	 */
	public function get_end_time( ) {
                 $date =$this->end_time;
		return $date ? $date->format( 'Y-m-d H:i:s' ) : '';
	}

	/**
	 * Get the amount of time elapsed since the saved start time
	 *
	 * @return float
	 */
	public function get_elapsed_time() {
                 $date =$this->end_time - $this->start_time;
		return $date ? $date->format( 'Y-m-d H:i:s' ) : '';
	}

	/**
	 * check if timeout
	 *
	 * @return float
	 */
	public function timeout() {
                 return ( microtime( true ) - $this->start_time > 100000 );
	}

}

/**
 * Get the current singleton request instance
 *
 * @return 
 */
function ph_cloner_log() {
	return PH_Cloner_Log::instance();
}

