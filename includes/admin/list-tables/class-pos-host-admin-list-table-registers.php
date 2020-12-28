<?php
/**
 * List tables: registers.
 *
 * @package WooCommerce_pos_host/Clases/Admin/List_Tables
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Admin_List_Table', false ) ) {
	include_once WC_ABSPATH . '/includes/admin/list-tables/abstract-class-wc-admin-list-table.php';
}

/**
 * WC_Admin_List_Table_Registers.
 */
class POS_HOST_Admin_List_Table_Registers extends WC_Admin_List_Table {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $list_table_type = 'pos_host_register';

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Define primary column.
	 *
	 * @return string
	 */
	protected function get_primary_column() {
		return 'register';
	}

	/**
	 * Get row actions to show in the list table.
	 *
	 * @param array   $actions Array of actions.
	 * @param WP_Post $post Current post object.
	 *
	 * @return array
	 */
	protected function get_row_actions( $actions, $post ) {
		unset( $actions['inline hide-if-no-js'] );

		if ( pos_host_is_default_register( $this->object->get_id() ) ) {
			unset( $actions['trash'] );
		}

		if ( ! current_user_can( 'manage_woocommerce_pos_host' ) ) {
			$actions = array();
		}

		return $actions;
	}

	/**
	 * Define which columns are sortable.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function define_sortable_columns( $columns ) {
		$custom = array(
			'register' => 'title',
		);
		return wp_parse_args( $custom, $columns );
	}

	/**
	 * Define which columns to show on this screen.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function define_columns( $columns ) {
		if ( empty( $columns ) && ! is_array( $columns ) ) {
			$columns = array();
		}

		// Remove default CPT columns.
		unset( $columns['title'], $columns['comments'], $columns['date'] );

		$show_columns                    = array();
		$show_columns['cb']              = '<input type="checkbox" />';
		$show_columns['register']        = esc_html__( 'Register', 'woocommerce-pos-host' );
		$show_columns['status']          = '<span class="status_head tips" data-tip="' . esc_attr__( 'Status', 'woocommerce-pos-host' ) . '">' . esc_html__( 'Status', 'woocommerce-pos-host' ) . '</span>';
		$show_columns['change_user']     = '<span class="pos-host-change-user tips" data-tip="' . esc_attr__( 'Change Cashier', 'woocommerce-pos-host' ) . '">' . esc_html__( 'Change User', 'woocommerce-pos-host' ) . '</span>';
		$show_columns['email_receipt']   = '<span class="pos-host-email-receipt tips" data-tip="' . esc_attr__( 'Email Receipt', 'woocommerce-pos-host' ) . '">' . esc_html__( 'Email Receipt', 'woocommerce-pos-host' ) . '</span>';
		$show_columns['print_receipt']   = '<span class="pos-host-print-receipt tips" data-tip="' . esc_attr__( 'Print Receipt', 'woocommerce-pos-host' ) . '">' . esc_html__( 'Print Receipt', 'woocommerce-pos-host' ) . '</span>';
		$show_columns['note_request']    = '<span class="pos-host-note-request tips" data-tip="' . esc_attr__( 'Note Request', 'woocommerce-pos-host' ) . '">' . esc_html__( 'Note Request', 'woocommerce-pos-host' ) . '</span>';
		$show_columns['cash_management'] = '<span class="pos-host-cash-management tips" data-tip="' . esc_attr__( 'Cash Management', 'woocommerce-pos-host' ) . '">' . esc_html__( 'Cash Management', 'woocommerce-pos-host' ) . '</span>';
		$show_columns['access']          = esc_html__( 'Access', 'woocommerce-pos-host' );

		return array_merge( $show_columns, $columns );
	}

	/**
	 * Pre-fetch any data for the row each column has access to it. the_register global is there for bw compat.
	 *
	 * @param int $post_id Post ID being shown.
	 */
	protected function prepare_row_data( $post_id ) {
		global $the_register;

		if ( empty( $this->object ) || $this->object->get_id() !== $post_id ) {
			$the_register = pos_host_get_register( $post_id );
			$this->object = $the_register;
		}
	}

	/**
	 * Render column: register.
	 */
	protected function render_register_column() {
		$outlet  = $this->object->get_outlet();
		$receipt = $this->object->get_receipt();
		$grid    = $this->object->get_grid();
		$actions = array();

		$grid_text     = $grid ? get_the_title( $grid ) : __( 'Category Layout', 'woocommerce-pos-host' );
		$receipt_text  = $receipt ? get_the_title( $receipt ) : '';
		$name          = empty( $this->object->get_name() ) ? __( '(no-title)', 'woocommerce-pos-host' ) : $this->object->get_name();
		$register_text = '<strong><a href="' . admin_url( "post.php?post={$this->object->get_id()}&action=edit" ) . '">' . $name . '</a></strong>';
		$outlet_text   = $outlet ? '<a href="' . admin_url( "post.php?post=${outlet}&action=edit" ) . '">' . get_the_title( $outlet ) . '</a>' : '';
		$heading_text  = $outlet ? implode( ' ', array( $register_text, __( 'located in', 'woocommerce-pos-host' ), $outlet_text ) ) : $register_text;

		$output = $heading_text
				. '<small class="meta grid">' . esc_html( $grid_text ) . '</small>'
				. '<small class="meta receipt">' . esc_html( $receipt_text ) . '</small>';

		/*
		 * We cannot modify the cb column for some reason. So we will put this tooltip here and
		 * move it via JavaScript to the cb column.
		 */
		if ( pos_host_is_default_register( $this->object->get_id() ) ) {
			$output .= wc_help_tip( __( 'This is the default register and it cannot be deleted.', 'woocommerce-pos-host' ) );

			wc_enqueue_js(
				"(function( $ ) {
					'use strict';
					var register = $( 'tr#post-" . absint( $this->object->get_id() ) . "' );
					register.find( 'th' ).empty();
					register.find( 'td.register .woocommerce-help-tip' ).detach( '.woocommerce-help-tip' ).appendTo( register.find( 'th' ) );
				})( jQuery );"
			);
		}

		echo wp_kses_post( $output );
	}

	/**
	 * Render columm: status.
	 */
	protected function render_status_column() {
		if ( pos_host_is_register_open( $this->object->get_id() ) ) {
			echo wp_kses_post( $this->_get_row_icon( 'open', __( 'Open', 'woocommerce-pos-host' ) ) );
		} else {
			echo wp_kses_post( $this->_get_row_icon( 'closed', __( 'Closed', 'woocommerce-pos-host' ) ) );
		}
	}

	/**
	 * Render columm: change_user.
	 */
	protected function render_change_user_column() {
		if ( $this->object->get_change_user() ) {
			echo wp_kses_post( $this->_get_row_icon( 'yes', __( 'User changes after sale.', 'woocommerce-pos-host' ) ) );
		} else {
			echo wp_kses_post( $this->_get_row_icon( 'no', __( 'User does not change after sale.', 'woocommerce-pos-host' ) ) );
		}
	}

	/**
	 * Render columm: email_receipt.
	 */
	protected function render_email_receipt_column() {
		switch ( $this->object->get_email_receipt() ) {
			case 'all':
				echo wp_kses_post( $this->_get_row_icon( 'yes', __( 'Receipt is emailed to all customers.', 'woocommerce-pos-host' ) ) );
				break;
			case 'non_guest':
				echo wp_kses_post( $this->_get_row_icon( 'yes', __( 'Receipt is emailed to non-guest customers only.', 'woocommerce-pos-host' ) ) );
				break;
			default:
				echo wp_kses_post( $this->_get_row_icon( 'no', __( 'Receipt is not emailed.', 'woocommerce-pos-host' ) ) );
				break;
		}
	}

	/**
	 * Render columm: print_receipt.
	 */
	protected function render_print_receipt_column() {
		if ( $this->object->get_print_receipt() ) {
			echo wp_kses_post( $this->_get_row_icon( 'yes', __( 'Receipt is printed.', 'woocommerce-pos-host' ) ) );
		} else {
			echo wp_kses_post( $this->_get_row_icon( 'no', __( 'Receipt is not printed.', 'woocommerce-pos-host' ) ) );
		}
	}

	/**
	 * Render columm: note_request.
	 */
	protected function render_note_request_column() {
		switch ( $this->object->get_note_request() ) {
			case 'on_save':
				echo wp_kses_post( $this->_get_row_icon( 'yes', __( 'Note is taken on save.', 'woocommerce-pos-host' ) ) );
				break;
			case 'on_all_sales':
				echo wp_kses_post( $this->_get_row_icon( 'yes', __( 'Note is taken on all sales.', 'woocommerce-pos-host' ) ) );
				break;
			default:
				echo wp_kses_post( $this->_get_row_icon( 'no', __( 'Note is not taken.', 'woocommerce-pos-host' ) ) );
				break;
		}
	}

	/**
	 * Render columm: cash_management.
	 */
	protected function render_cash_management_column() {
		if ( $this->object->get_cash_management() ) {
			echo wp_kses_post( $this->_get_row_icon( 'yes', __( 'Cash is managed.', 'woocommerce-pos-host' ) ) );
		} else {
			echo wp_kses_post( $this->_get_row_icon( 'no', __( 'Cash is not managed.', 'woocommerce-pos-host' ) ) );
		}
	}

	/**
	 * Render columm: access.
	 */
	protected function render_access_column() {
		$errors = array();

		// Register details.
		$outlet_id  = $this->object->get_outlet();
		$receipt_id = $this->object->get_receipt();
		$outlet     = pos_host_get_outlet( $outlet_id );
		$receipt    = pos_host_get_receipt( $receipt_id );

		/*
		 * Determine whether the register is closed.
		 */
		if ( ! $outlet ) {
			$errors[] = __( 'No outlet assigned.', 'woocommerce-pos-host' );
		}

		if ( ! $receipt ) {
			$errors[] = __( 'No receipt template assigned.', 'woocommerce-pos-host' );
		}

		if ( ! empty( $errors ) ) {
			echo '<a class="button button-secondary tips" data-tip="' . esc_attr( implode( '<br />', $errors ) ) . '" disabled>' . esc_html__( 'Closed', 'woocommerce-pos-host' ) . '</a>';

			return;
		}

		/*
		 * Register link.
		 */
		$register_link = get_home_url() . '/pos-host/' . $outlet->get_slug() . '/' . $this->object->get_slug();

		if ( class_exists( 'SitePress' ) ) {
			$settings = get_option( 'icl_sitepress_settings' );

			if ( 1 === $settings['urls']['directory_for_default_language'] && defined( 'ICL_LANGUAGE_CODE' ) ) {
				$register_url = get_home_url() . '/' . ICL_LANGUAGE_CODE . "/pos-host/$outlet/$register";
			}
		}

		$can_force_logout   = pos_host_current_user_can_force_logout();
		$can_open_register  = pos_host_current_user_can_open_register( $this->object->get_id() );
		$is_register_locked = pos_host_is_register_locked( $this->object->get_id() );
		$is_register_open   = pos_host_is_register_open( $this->object->get_id() );

		if ( $can_open_register && ! $is_register_locked ) {
			$button = $is_register_open ? __( 'Enter', 'woocommerce-pos-host' ) : __( 'Open', 'woocommerce-pos-host' );
			$tip    = $is_register_open ? __( 'Enter Register', 'woocommerce-pos-host' ) : __( 'Open Register', 'woocommerce-pos-host' );

			echo '<a class="button button-primary tips" href="' . esc_attr( $register_link ) . '" data-tip="' . esc_attr( $tip ) . '">' . esc_html( $button ) . '</a>';

			return;
		}

		if ( $can_open_register && $is_register_locked ) {
			$user           = get_userdata( $is_register_locked );
			$logged_in_user = trim( $user->first_name . ' ' . $user->last_name );
			$logged_in_user = empty( $logged_in_user ) ? $user->user_login : $logged_in_user;

			/* translators: %s: user full name */
			$tip = sprintf( __( '%s is currently logged on this register.', 'woocommerce-pos-host' ), $logged_in_user );

			if ( $can_force_logout ) {
				echo '<a class="button button-primary tips" href="' . esc_attr( $register_link ) . '" data-tip="' . esc_attr( $tip ) . '">' . esc_html__( 'Open', 'woocommerce-pos-host' ) . '</a>';
			} else {
				echo '<a class="button button-primary tips disabled" data-tip="' . esc_attr( $tip ) . '">' . esc_html__( 'Open', 'woocommerce-pos-host' ) . '</a>';
			}

			return;
		}

		// User cannot access the register.
		echo '<a class="button button-primary tips disabled" data-tip="' . esc_attr__( 'You are not assigned to the register outlet.', 'woocommerce-pos-host' ) . '">' . esc_html__( 'Open', 'woocommerce-pos-host' ) . '</a>';
	}

	/**
	 * Render any custom filters and search inputs for the list table.
	 */
	protected function render_filters() {
		$filters = apply_filters(
			'pos_host_registers_admin_list_table_filters',
			array(
				'outlet' => array( $this, 'render_registers_outlet_filter' ),
			)
		);

		ob_start();
		foreach ( $filters as $filter_callback ) {
			call_user_func( $filter_callback );
		}
		$output = ob_get_clean();

		echo wp_kses(
			apply_filters( 'pos_host_register_filters', $output ),
			array(
				'select' => array( 'name' => true ),
				'option' => array( 'value' => true ),
			)
		);
	}

	/**
	 * Render the registers outlet filter for the list table.
	 */
	protected function render_registers_outlet_filter() {
		global $allowedposttags;

		$current_outlet = isset( $_REQUEST['outlet'] ) ? absint( $_REQUEST['outlet'] ) : false;
		$outlets        = get_posts(
			array(
				'numberposts' => -1,
				'post_type'   => 'pos_host_outlet',
				'orderby'     => 'post_name',
				'order'       => 'asc',
			)
		);
		$output         = '<select name="outlet"><option value="">' . esc_html__( 'Filter by outlet', 'woocommerce-pos-host' ) . '</option>';

		foreach ( $outlets as $outlet ) {
			$output .= '<option value="' . esc_attr( $outlet->ID ) . '" ' . selected( $outlet->ID, $current_outlet, false ) . '>' . esc_html( $outlet->post_title ) . '</option>';
		}

		$output .= '</select>';

		echo wp_kses(
			$output,
			array(
				'select' => array( 'name' => true ),
				'option' => array( 'value' => true ),
			)
		);
	}

	/**
	 * Handle any custom filters.
	 *
	 * @param array $query_vars Query vars.
	 * @return array
	 */
	protected function query_filters( $query_vars ) {
		// Stock status filter.
		if ( ! empty( $_GET['outlet'] ) ) {
			add_filter( 'posts_clauses', array( $this, 'filter_outlet_post_clauses' ) );
		}

		return $query_vars;
	}

	/**
	 * Filter by outlet.
	 *
	 * @param array $args Query args.
	 * @return array
	 */
	public function filter_outlet_post_clauses( $args ) {
		global $wpdb;

		if ( ! empty( $_GET['outlet'] ) ) {
			$args['join']   = $this->append_postmeta_lookup_join( $args['join'] );
			$args['where'] .= $wpdb->prepare( " AND postmeta_lookup.meta_key = 'outlet' AND postmeta_lookup.meta_value = %s ", wc_clean( wp_unslash( $_GET['outlet'] ) ) );
		}

		return $args;
	}

	/**
	 * Join postmeta_lookup to posts if not already joined.
	 *
	 * @param string $sql SQL join.
	 * @return string
	 */
	private function append_postmeta_lookup_join( $sql ) {
		global $wpdb;

		if ( ! strstr( $sql, 'postmeta_lookup' ) ) {
			$sql .= " LEFT JOIN {$wpdb->postmeta} postmeta_lookup ON $wpdb->posts.ID = postmeta_lookup.post_id ";
		}

		return $sql;
	}

	/**
	 * Returns the HTML for a row icon.
	 *
	 * @internal
	 *
	 * @param string $icon Icon type. Default: 'yes'.
	 * @param string $tip  Tip text. Default: ''.
	 *
	 * @return string
	 */
	private function _get_row_icon( $icon = 'yes', $tip = '' ) {
		$icon = in_array( $icon, array( 'yes', 'no', 'open', 'closed' ) ) ? $icon : 'yes';

		return '<span class="icon icon-' . $icon . ' tips" data-tip="' . esc_attr__( $tip, 'woocommerce-pos-host' ) . '"></span>';
	}
}
