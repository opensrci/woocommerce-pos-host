<?php
/**
 * Post Types Admin
 *
 * @package WooCommerce_pos_host/Classes/Admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Admin_Post_Types.
 *
 * Handles the edit posts views and some functionality on the edit post screen for the plugin post types.
 */
class POS_HOST_Admin_Post_Types {

	/**
	 * Constructor.
	 */
	public function __construct() {
		include_once dirname( __FILE__ ) . '/class-pos-host-admin-meta-boxes.php';

		// Add/edit a receipt.
		add_action( 'load-post.php', array( $this, 'pos_host_receipt' ) );
		add_action( 'load-post-new.php', array( $this, 'pos_host_receipt' ) );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );

		add_action( 'current_screen', array( $this, 'setup_screen' ) );
		add_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		add_action( 'admin_print_scripts', array( $this, 'disable_autosave' ) );
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );
		add_action( 'edit_form_advanced', array( $this, 'require_post_title' ) );

		add_filter( 'pre_trash_post', array( $this, 'disable_delete_default_posts' ), 10, 2 );
		add_filter( 'pre_delete_post', array( $this, 'disable_delete_default_posts' ), 10, 2 );

		add_filter( 'pre_trash_post', array( $this, 'disable_delete_open_registers' ), 10, 2 );
		add_filter( 'pre_delete_post', array( $this, 'disable_delete_open_registers' ), 10, 2 );

		add_action( 'trashed_post', array( $this, 'delete_post' ) );
		add_action( 'deleted_post', array( $this, 'delete_post' ) );
	}

	/**
	 * Loads the receipt customiser on pos_host_receipt screen.
	 */
	public function pos_host_receipt() {
		$action    = 'load-post.php' === current_action() ? 'edit' : 'new';
		$screen_id = false;

		if ( function_exists( 'get_current_screen' ) ) {
			$screen    = get_current_screen();
			$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
		}

		if ( ! empty( $_REQUEST['screen'] ) ) {
			$screen_id = wc_clean( wp_unslash( $_REQUEST['screen'] ) );
		}

		if ( ! $screen_id || 'pos_host_receipt' !== $screen_id ) {
			return;
		}

		if ( 'edit' === $action && isset( $_GET['action'] ) && 'edit' !== $_GET['action'] ) {
			return;
		}

		$receipt_object = 'edit' === $action && isset( $_GET['post'] ) ? pos_host_get_receipt( (int) $_GET['post'] ) : new POS_HOST_Receipt();

		// Load only what we need here.
		require_once ABSPATH . 'wp-admin/admin-header.php';
		include_once POS_HOST_ABSPATH . '/includes/admin/views/html-admin-receipt-customize.php';
		include ABSPATH . 'wp-admin/admin-footer.php';

		// Stop here. Don't load anything else.
		die();
	}

	/**
	 * Add CSS classes to the body tag.
	 *
	 * @param  string $class
	 * @return string
	 */
	public function admin_body_class( $class ) {
		$screen_id = false;

		if ( function_exists( 'get_current_screen' ) ) {
			$screen    = get_current_screen();
			$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
		}

		if ( ! empty( $_REQUEST['screen'] ) ) { // WPCS: input var ok.
			$screen_id = wc_clean( wp_unslash( $_REQUEST['screen'] ) ); // WPCS: input var ok, sanitization ok.
		}

		if ( $screen_id && 'pos_host_receipt' === $screen_id ) {
			$class = 'wp-customizer';
		}

		return $class;
	}

	/**
	 * Looks at the current screen and loads the correct list table handler.
	 */
	public function setup_screen() {
		global $wc_list_table;

		$screen_id = false;

		if ( function_exists( 'get_current_screen' ) ) {
			$screen    = get_current_screen();
			$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
		}

		if ( ! empty( $_REQUEST['screen'] ) ) { // WPCS: input var ok.
			$screen_id = wc_clean( wp_unslash( $_REQUEST['screen'] ) ); // WPCS: input var ok, sanitization ok.
		}

		switch ( $screen_id ) {
			case 'edit-pos_host_register':
				include_once POS_HOST_ABSPATH . '/includes/admin/list-tables/class-pos-host-admin-list-table-registers.php';
				$wc_list_table = new POS_HOST_Admin_List_Table_Registers();
				break;
			case 'edit-pos_host_outlet':
				include_once POS_HOST_ABSPATH . '/includes/admin/list-tables/class-pos-host-admin-list-table-outlets.php';
				$wc_list_table = new POS_HOST_Admin_List_Table_Outlets();
				break;
			case 'edit-pos_host_grid':
				include_once POS_HOST_ABSPATH . '/includes/admin/list-tables/class-pos-host-admin-list-table-grids.php';
				$wc_list_table = new POS_HOST_Admin_List_Table_Grids();
				break;
			case 'edit-pos_host_receipt':
				include_once POS_HOST_ABSPATH . '/includes/admin/list-tables/class-pos-host-admin-list-table-receipts.php';
				$wc_list_table = new POS_HOST_Admin_List_Table_Receipts();
				break;
		}

		// Ensure the table handler is only loaded once. Prevents multiple loads if a plugin calls check_ajax_referer many times.
		remove_action( 'current_screen', array( $this, 'setup_screen' ) );
		remove_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );
	}

	/**
	 * Change messages when a post type is updated.
	 *
	 * @param  array $messages Array of messages.
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		global $post;

		// Registers.
		$messages['pos_host_register'] = array(
			0 => '', // Unused. Messages start at index 1.
			4 => __( 'Register Updated.', 'woocommerce-pos-host' ),
			6 => __( 'Register Created.', 'woocommerce-pos-host' ),
		);

		// Outlets.
		$messages['pos_host_outlet'] = array(
			0 => '', // Unused. Messages start at index 1.
			4 => __( 'Outlet Updated.', 'woocommerce-pos-host' ),
			6 => __( 'Outlet Created.', 'woocommerce-pos-host' ),
		);

		// Grids.
		$messages['pos_host_grid'] = array(
			0 => '', // Unused. Messages start at index 1.
			4 => __( 'Grid Updated.', 'woocommerce-pos-host' ),
			6 => __( 'Grid Created.', 'woocommerce-pos-host' ),
		);

		return $messages;
	}

	/**
	 * Disable the auto-save functionality for our custom post types.
	 */
	public function disable_autosave() {
		global $post;

		if ( $post && in_array( get_post_type( $post->ID ), array( 'pos_host_register', 'pos_host_outlet', 'pos_host_grid' ), true ) ) {
			wp_dequeue_script( 'autosave' );
		}
	}

	/**
	 * Change title boxes in admin.
	 *
	 * @param string  $text Text to show.
	 * @param WP_Post $post Current post object.
	 *
	 * @return string
	 */
	public function enter_title_here( $text, $post ) {
		switch ( $post->post_type ) {
			case 'pos_host_register':
				$text = esc_html__( 'Register name', 'woocommerce-pos-host' );
				break;
			case 'pos_host_outlet':
				$text = esc_html__( 'Outlet name', 'woocommerce-pos-host' );
				break;
			case 'pos_host_grid':
				$text = esc_html__( 'Grid name', 'woocommerce-pos-host' );
				break;
		}

		return $text;
	}

	public function require_post_title( $post ) {
		$post_types = array(
			'pos_host_register',
			'pos_host_outlet',
			'pos_host_grid',
		);

		if ( ! in_array( $post->post_type, $post_types, true ) ) {
			return;
		}
		?>
		<script type='text/javascript'>
			( function ( $ ) {
				$( document ).ready( function () {
					$( "#title" ).prop( 'required', true );
				} );
			} ( jQuery ) );
		</script>
		<?php
	}

	/**
	 * Prevent deleting the default posts.
	 *
	 * @param WP_Post $post Post object.
	 * @return bool
	 */
	public function disable_delete_default_posts( $check, $post ) {
		if (
			( 'pos_host_register' === $post->post_type && pos_host_is_default_register( $post->ID ) ) ||
			( 'pos_host_outlet' === $post->post_type && pos_host_is_default_outlet( $post->ID ) ) ||
			( 'pos_host_receipt' === $post->post_type && pos_host_is_default_receipt( $post->ID ) )
		) {
			return false;
		}

		return null;
	}

	/**
	 * Prevent deleting open registers.
	 *
	 * @param WP_Post $post Post object.
	 * @return bool
	 */
	public function disable_delete_open_registers( $check, $post ) {
		if ( 'pos_host_register' !== $post->post_type ) {
			return $check;
		}

		$locked = pos_host_is_register_locked( $post->ID );
		if ( $locked ) {
			$by = get_user_by( 'id', $locked );

			/* translators: %s Display name */
			wp_die( sprintf( esc_html__( 'This register is opened by %s and cannot be deleted at the moment.', 'woocommerce-pos-host' ), esc_html( $by->display_name ) ) );
		}

		if ( pos_host_is_register_open( $post->ID ) ) {
			wp_die( esc_html__( 'This register is opened and cannot be deleted at the moment. Please make sure to close the register before you can delete it.', 'woocommerce-pos-host' ) );
		}

		return $check;
	}

	/**
	 * Do specific actions if a post is trashed/deleted.
	 *
	 * @param $post_id Post ID.
	 */
	public function delete_post( $post_id ) {
		if ( ! $post_id ) {
			return;
		}

		global $wpdb;

		// Register.
		if ( 'pos_host_register' === get_post_type( $post_id ) ) {
			// Re-assign the orders assigned to this register to the default register.
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID AND p.post_type = 'shop_order'
				SET pm.meta_value = %d
				WHERE pm.meta_key = 'pos_host_host_register_id' AND pm.meta_value = %d
				",
					absint( get_option( 'pos_host_default_register' ) ),
					$post_id
				)
			);
		}

		// Outlet.
		if ( 'pos_host_outlet' === get_post_type( $post_id ) ) {
			// Re-assign the registers assigned to this outlet to the default outlet.
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID AND p.post_type = 'pos_host_register'
				SET pm.meta_value = %d
				WHERE pm.meta_key = 'outlet' AND pm.meta_value = %d
				",
					absint( get_option( 'pos_host_default_outlet' ) ),
					$post_id
				)
			);
		}

		// Grid.
		if ( 'pos_host_grid' === get_post_type( $post_id ) ) {
			// Re-assign the registers assigned to this grid to the categories grid.
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID AND p.post_type = 'pos_host_register'
				SET pm.meta_value = 0
				WHERE pm.meta_key = 'grid' AND pm.meta_value = %d
				",
					$post_id
				)
			);
		}

		// Receipt.
		if ( 'pos_host_receipt' === get_post_type( $post_id ) ) {
			// Re-assign registers assigned to this receipt template to the default receipt.
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->postmeta} pm
				LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID AND p.post_type = 'pos_host_register'
				SET pm.meta_value = %d
				WHERE pm.meta_key = 'receipt' AND pm.meta_value = %d
				",
					absint( get_option( 'pos_host_default_receipt' ) ),
					$post_id
				)
			);
		}
	}
}

new POS_HOST_Admin_Post_Types();
