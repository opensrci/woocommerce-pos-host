<?php
/**
 * Admin Class
 *
 * @package WooCommerce_pos_host/Admin/Classes
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Admin', false ) ) {
	return new POS_HOST_Admin();
}

/**
 * POS_HOST_Admin.
 */
class POS_HOST_Admin {
      
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_footer', 'wc_print_js', 25 );
		add_filter( 'woocommerce_screen_ids', array( $this, 'woocommerce_screen_ids' ), 10, 1 );
		add_filter( 'woocommerce_reports_charts', array( $this, 'pos_reports_charts' ), 20, 1 );
		
		if ( class_exists( 'SitePress' ) ) {
			$settings = get_option( 'icl_sitepress_settings' );
			if ( 1 === $settings['urls']['directory_for_default_language'] ) {
				add_action( 'generate_rewrite_rules', array( $this, 'create_rewrite_rules_wpml' ), 9 );
			} else {
				add_filter( 'rewrite_rules_array', array( $this, 'create_rewrite_rules' ), 11, 1 );
			}
		} else {
			add_filter( 'rewrite_rules_array', array( $this, 'create_rewrite_rules' ), 11, 1 );
		}
		add_action( 'init', array( $this, 'on_rewrite_rule' ) );
		add_action( 'wp_loaded', array( $this, 'flush_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'parse_request', array( $this, 'parse_request' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( POS_HOST_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

		add_filter( 'woocommerce_prevent_admin_access', array( $this, 'prevent_admin_access' ), 10, 2 );

		add_action( 'save_post', array( $this, 'save_order_rounding_amount' ), 50, 3 );
		add_filter( 'woocommerce_get_formatted_order_total', array( $this, 'get_rounding_total' ), 50, 2 );

		// Product grids.
		add_filter( 'manage_edit-product_columns', array( $this, 'add_product_grid_column' ), 9999 );
		add_action( 'manage_product_posts_custom_column', array( $this, 'display_product_grid_column' ), 2 );
		add_action( 'admin_footer', array( $this, 'product_grid_bulk_actions' ), 11 );
		add_action( 'load-edit.php', array( $this, 'product_grid_bulk_actions_handler' ) );
		add_action( 'trashed_post', array( $this, 'remove_grid_product_tile' ) );
		add_action( 'deleted_post', array( $this, 'remove_grid_product_tile' ) );
		add_action( 'delete_term', array( $this, 'remove_grid_category_tile' ), 10, 3 );

		if ( 'yes' === get_option( 'pos_host_visibility', 'no' ) ) {
			add_action( 'post_submitbox_misc_actions', array( $this, 'product_pos_visibility' ) );
		}

		//add_action( 'admin_bar_menu', array( $this, 'show_admin_bar_pos_host_registers' ), 100 );
		add_action( 'woocommerce_admin_field_button', array( $this, 'wc_settings_button_field' ) );
		add_action( 'woocommerce_admin_field_range_slider', array( $this, 'wc_settings_range_slider' ) );
		add_action( 'woocommerce_admin_field_media_upload', array( $this, 'wc_settings_media_upload' ) );

		$this->init_users_hooks();
	}

	/**
	 * Add plugin admin screens to the WC screens.
	 *
	 * @param array $screen_ids
	 * @return array
	 */
	public function woocommerce_screen_ids( $screen_ids ) {
		return array_merge( $screen_ids, pos_host_get_screen_ids() );
	}

	public function product_pos_visibility() {
		global $post;

		if ( 'product' !== $post->post_type ) {
			return;
		}

		$pos_visibility = get_post_meta( $post->ID, '_pos_visibility', true );
		$pos_visibility = $pos_visibility ? $pos_visibility : 'pos_online';

		$visibility_options = apply_filters(
			'pos_host_visibility_options',
			array(
				'pos_online' => __( 'POS &amp; Online', 'woocommerce-pos-host' ),
				'pos'        => __( 'POS Only', 'woocommerce-pos-host' ),
				'online'     => __( 'Online Only', 'woocommerce-pos-host' ),
			)
		); ?>
		<div class="misc-pub-section" id="pos-host-visibility">
			<?php esc_html_e( 'POS visibility:', 'woocommerce-pos-host' ); ?>
			<strong id="pos-host-visibility-display">
				<?php echo isset( $visibility_options[ $pos_visibility ] ) ? esc_html( $visibility_options[ $pos_visibility ] ) : esc_html( $pos_visibility ); ?>
			</strong>

			<a href="#pos-host-visibility" class="edit-pos-host-visibility hide-if-no-js"><?php esc_html_e( 'Edit', 'woocommerce-pos-host' ); ?></a>

			<div id="pos-host-visibility-select" class="hide-if-js">

				<input type="hidden" name="current_pos_visibility" id="current_visibility" value="<?php echo esc_attr( $pos_visibility ); ?>"/>
				<?php
				foreach ( $visibility_options as $name => $label ) {
					echo '<input type="radio" name="_pos_visibility" id="pos_visibility_' . esc_attr( $name ) . '" value="' . esc_attr( $name ) . '" ' . checked( $pos_visibility, $name, false ) . ' data-label="' . esc_attr( $label ) . '" /> <label for="_visibility_' . esc_attr( $name ) . '" class="selectit">' . esc_html( $label ) . '</label><br />';
				}
				?>
				<p>
					<a href="#pos-host-visibility" class="save-post-visibility hide-if-no-js button"><?php esc_html_e( 'OK', 'woocommerce-pos-host' ); ?></a>
					<a href="#pos-host-visibility" class="cancel-post-visibility hide-if-no-js"><?php esc_html_e( 'Cancel', 'woocommerce-pos-host' ); ?></a>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once 'class-pos-host-admin-menus.php';
		include_once 'class-pos-host-admin-orders-page.php';
	}

	public function init_users_hooks() {
		add_action( 'show_user_profile', array( $this, 'add_customer_meta_fields' ) );
		add_action( 'edit_user_profile', array( $this, 'add_customer_meta_fields' ) );

		add_action( 'personal_options_update', array( $this, 'save_customer_meta_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_customer_meta_fields' ) );
	}

	public function pos_reports_charts( $reports ) {
		$reports['pos'] = array(
			'title'   => __( 'POS', 'woocommerce-pos-host' ),
			'reports' => array(
				'sales_by_register' => array(
					'title'       => __( 'Sales by register', 'woocommerce-pos-host' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( $this, 'get_report' ),
				),
				'sales_by_outlet'   => array(
					'title'       => __( 'Sales by outlet', 'woocommerce-pos-host' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( $this, 'get_report' ),
				),
				'sales_by_cashier'  => array(
					'title'       => __( 'Sales by cashier', 'woocommerce-pos-host' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( $this, 'get_report' ),
				),
			),
		);
		return $reports;
	}

	/**
	 * Get a report from our reports subfolder
	 */
	public static function get_report( $name ) {
		$name  = sanitize_title( str_replace( '_', '-', $name ) );
		$class = 'POS_HOST_Report_' . str_replace( '-', '_', $name );

		include_once apply_filters( 'pos_host_admin_reports_path', POS_HOST()->plugin_path() . '/includes/reports/class-pos-host-report-' . $name . '.php', $name, $class );

		if ( ! class_exists( $class ) ) {
			return;
		}

		$report = new $class();
		$report->output_report();
	}

	public static function get_sessions_table() {
		include_once POS_HOST_ABSPATH . '/includes/admin/list-tables/class-pos-host-admin-list-table-sessions.php';
		$table = new POS_HOST_Admin_List_Table_Sessions();
	}

	/**
	 * Show POS fields on edit user pages.
	 *
	 * @param mixed $user User (object) being displayed
	 */
	public function add_customer_meta_fields( $user ) {

		if ( ! current_user_can( 'manage_WooCommerce_pos_host' ) ) {
			return;
		}

		$show_fields = $this->get_customer_meta_fields();

		foreach ( $show_fields as $fieldset ) :
			?>
			<h3><?php echo esc_html( $fieldset['title'] ); ?></h3>
			<table class="form-table" id="pos_custom_user_fields">
				<?php
				foreach ( $fieldset['fields'] as $key => $field ) :
					?>
					<tr>
						<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
						</th>
						<td>
							<?php
							if ( isset( $field['type'] ) && 'select' === $field['type'] ) {
								$value_user_meta = (array) get_user_meta( $user->ID, $key, true );
								$multiple        = isset( $field['multiple'] ) && $field['multiple'] ? 'multiple' : '';
								?>
								<select name="<?php echo isset( $field['name'] ) ? esc_attr( $field['name'] ) : esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $multiple ); ?> style="min-width:350px;" class="wc-enhanced-select">
									<?php
									foreach ( $field['options'] as $label_value => $label ) {
										echo '<option value="' . esc_attr( $label_value ) . '" ' . ( ( in_array( $label_value, $value_user_meta ) ) ? 'selected' : '' ) . ' >' . esc_html( $label ) . '</option>';
									}
									?>
								</select>
							<?php } elseif ( 'input' === $field['type'] && 'pos_host_user_card_number' === $key ) { ?>
								<?php $card = get_user_meta( $user->ID, $key, true ); ?>
								<input type="text" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $card ); ?>" <?php echo ! empty( $card ) ? 'disabled' : ''; ?> class="regular-text"/>
								<a id="enable_card" class="button">
									<?php esc_html_e( 'Change Card Number', 'woocommerce-pos-host' ); ?>
								</a>
								<?php
							} elseif ( true ) {
								$val = get_user_meta( $user->ID, $key, true );
								?>
								<label for="<?php echo esc_attr( $key ); ?>">
									<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( isset( $val ) && 'yes' === $val ); ?>>
									<?php echo isset( $field['desc'] ) ? esc_html( $field['desc'] ) : ''; ?>
								</label>
							<?php } else { ?>
								<input type="text" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( get_user_meta( $user->ID, $key, true ) ); ?>" class="regular-text"/><br/>
							<?php } ?>
							<br>
							<span class="description"><?php echo isset( $field['description'] ) ? wp_kses_post( $field['description'] ) : ''; ?></span>
						</td>
					</tr>
					<?php
				endforeach;
				?>
			</table>
			<?php
		endforeach;
	}

	/**
	 * Save Fields on edit user pages
	 *
	 * @param mixed $user_id User ID of the user being saved
	 */
	public function save_customer_meta_fields( $user_id ) {
		check_admin_referer( 'update-user_' . $user_id );

		$save_fields = $this->get_customer_meta_fields();

		foreach ( $save_fields as $fieldset ) {
			foreach ( $fieldset['fields'] as $key => $field ) {
				if ( 'checkbox' === $field['type'] ) {
					update_user_meta( $user_id, $key, isset( $_POST[ $key ] ) ? 'yes' : 'no' );
				} elseif ( 'select' === $field['type'] && isset( $field['multiple'] ) && $field['multiple'] ) {
					update_user_meta( $user_id, $key, isset( $_POST[ $key ] ) ? wc_clean( $_POST[ $key ] ) : array() );
				} else {
					if ( isset( $_POST[ $key ] ) ) {
						update_user_meta( $user_id, $key, wc_clean( $_POST[ $key ] ) );
					}
				}
			}
		}
	}

	/**
	 * Get Fields for the edit user pages.
	 *
	 * @return array Fields to display which are filtered through pos_host_customer_meta_fields before being returned
	 */
	public function get_customer_meta_fields() {
		$show_fields = apply_filters(
			'pos_host_customer_meta_fields',
			array(
				'outlet_fields' => array(
					'title'  => __( 'POS HOST', 'woocommerce-pos-host' ),
					'fields' => array(
						'pos_host_assigned_outlets' => array(
							'label'       => __( 'Assigned Outlets', 'woocommerce-pos-host' ),
							'class'       => 'wc-enhanced-select enhanced',
							'type'        => 'select',
							'name'        => 'pos_host_assigned_outlets[]',
							'multiple'    => true,
							'options'     => pos_host_get_register_outlet_options(),
							'description' => __( 'Ensure the user is logged out before changing the outlet.', 'woocommerce-pos-host' ),
						),
						'pos_host_enable_discount'  => array(
							'label'       => __( 'Discount', 'woocommerce-pos-host' ),
							'type'        => 'select',
							'options'     => array(
								'yes' => 'Enable',
								'no'  => 'Disable',
							),
							'description' => 'Disable discount ability, user will only be able to enter coupons and add fees.',
						),
					),
				),
			)
		);

		if ( 'yes' === get_option( 'pos_host_enable_user_card', 'no' ) ) {
			$show_fields['outlet_fields']['fields']['pos_host_user_card_number'] = array(
				'label'       => __( 'Card Number', 'woocommerce-pos-host' ),
				'type'        => 'input',
				'description' => 'Enter the number of the card to associate this customer with.',
			);
		}

		$show_fields['outlet_fields']['fields']['pos_host_enable_tender_orders'] = array(
			'label'       => __( 'Tender Orders', 'woocommerce-pos-host' ),
			'description' => 'Disable tendering ability, user will only be able to hold orders.',
			'type'        => 'select',
			'options'     => array(
				'yes' => 'Enable',
				'no'  => 'Disable',
			),
		);
		return $show_fields;
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links
	 * @return  array
	 */
	public static function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=pos-host-settings' ) . '" title="' . esc_attr( __( 'View Settings', 'woocommerce-pos-host' ) ) . '">' . __( 'Settings', 'woocommerce-pos-host' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links Plugin Row Meta
	 * @param mixed $file Plugin Base file
	 * @return  array
	 */
	public static function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( POS_HOST_PLUGIN_FILE ) === $file ) {
			$row_meta = array(
				'docs'    => '<a href="' . esc_url( apply_filters( 'pos_host_docs_url', 'https://pos-host/support' ) ) . '" title="' . esc_attr( __( 'View Documentation', 'woocommerce-pos-host' ) ) . '">' . __( 'Docs', 'woocommerce-pos-host' ) . '</a>',
				'support' => '<a href="' . esc_url( apply_filters( 'pos_host_docs_url', 'https://pos.host/support/' ) ) . '" title="' . esc_attr( __( 'Visit Support', 'woocommerce-pos-host' ) ) . '">' . __( 'Support', 'woocommerce-pos-host' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}
 
	public static function create_rewrite_rules( $rules ) {
	       global $wp_rewrite;
                $pos_host_rewrite = '^pos\/$';
                $pos_host_dest = 'index.php?page=pos-host-registers&action=view';
		
                $newRule = array(
			  $pos_host_rewrite => $pos_host_dest,
			// '^pos-host/sw/?$'              => ltrim( str_replace( get_home_url(), '', POS_HOST()->plugin_url() ), '/' ) . '/assets/service-worker.js',
		);
                $newRules = $newRule + $rules;
                $pos_host_rewrite = '^pos$';
                $pos_host_dest = 'index.php?page=pos-host-registers&action=view';
		
                $newRule = array(
			  $pos_host_rewrite => $pos_host_dest,
		);
                $newRules = $newRule + $rules;
                
                
		return $newRules;
	}

	public static function create_rewrite_rules_wpml() {
		global $wp_rewrite;
                $pos_host_rewrite = '^pos\/$';
                $pos_host_dest = 'index.php?page=pos-host-registers&action=view';
                //$pos_host_dest = 'index.php?page=pos-host-registers&action=view&outlet=$matches[1]&register=$matches[2]';
		$newRule = array(
			  $pos_host_rewrite => $pos_host_dest,
			// '^pos-host/sw/?$'              => ltrim( str_replace( get_home_url(), '', POS_HOST()->plugin_url() ), '/' ) . '/assets/service-worker.js',
                    
		);

		$wp_rewrite->rules = $newRule + $wp_rewrite->rules;
	}

	public static function on_rewrite_rule() {
                $pos_host_rewrite = '^pos\/$';
                $pos_host_dest = 'index.php?page=pos-host-registers&action=view';
                // $pos_host_sw_dest = ltrim( str_replace( get_home_url(), '', POS_HOST()->plugin_url() ), '/' ) . '/assets/service-worker.js';
		add_rewrite_rule(   $pos_host_rewrite, $pos_host_dest, 'top' );
		// add_rewrite_rule( '^pos-host/sw/?$', $pos_host_sw_dest , 'top' );
	}

	public static function flush_rules() {
		$rules = get_option( 'rewrite_rules' );
                 $pos_host_rewrite = '^pos\/$';

		if ( ! isset( $rules[ $pos_host_rewrite ] ) || ! isset( $rules['^pos/sw/?$'] )  ) {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}
	}

	public static function add_query_vars( $public_query_vars ) {
		$public_query_vars[] = 'page';
		$public_query_vars[] = 'action';
		$public_query_vars[] = 'outlet';
		$public_query_vars[] = 'register';

		return $public_query_vars;
	}

	public static function parse_request( $wp ) {
		if ( isset( $wp->query_vars['page'] ) && 'pos-host-registers' === $wp->query_vars['page'] && isset( $wp->query_vars['action'] ) && 'view' === $wp->query_vars['action'] ) {
			POS_HOST()->is_pos = true;
		}
	}

	public static function prevent_admin_access( $prevent_access ) {
		if ( current_user_can( 'view_register' ) ) {
			$prevent_access = false;
		}
		return $prevent_access;
	}

	public function add_product_grid_column( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( 'product_tag' === $key ) {
				$new_columns['pos_host_product_grid'] = __( 'Product Grid', 'woocommerce-pos-host' );
			}
		}
		return $new_columns;
	}

	public function display_product_grid_column( $column ) {
		global $post, $woocommerce;

		if ( 'pos_host_product_grid' === $column ) {
			$product_id = $post->ID;
			$grids      = pos_host_get_tile_grids( $product_id, 'product', true );
			$links      = array();
			if ( ! empty( $grids ) ) {
				foreach ( $grids as $id => $name ) {
					$url     = admin_url( 'post.php?post=' . $id . '&action=edit' );
					$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>';
				}
				echo wp_kses_post( implode( ', ', $links ) );
			} else {
				echo '<span class="na">–</span>';
			}
		}
	}

	public function product_grid_bulk_actions() {
		global $post_type;
		if ( 'product' === $post_type ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					<?php
					$grids = get_posts(
						array(
							'numberposts' => -1,
							'post_type'   => 'pos_host_grid',
						)
					);
					if ( ! empty( $grids ) ) {
						foreach ( $grids as $grid ) {
							/* translators: %s grid name */
							$add_to_text = sprintf( __( 'Add to %s', 'woocommerce-pos-host' ), $grid->post_title );
							?>
							jQuery('<option>').val('pos_host_add_to_grid_<?php echo esc_js( $grid->ID ); ?>')
								.text('<?php echo esc_js( $add_to_text ); ?>').appendTo('select[name=action]');
							jQuery('<option>').val('pos_host_add_to_grid_<?php echo esc_js( $grid->ID ); ?>')
								.text('<?php echo esc_js( $add_to_text ); ?>').appendTo('select[name=action2]');
							<?php
						}
					}
					?>
				});
			</script>
			<?php
		}
	}

	public function product_grid_bulk_actions_handler() {
		if ( ! isset( $_REQUEST['post'] ) ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action        = $wp_list_table->current_action();

		global $wpdb;
		$changed  = 0;
		$post_ids = array_map( 'absint', (array) $_REQUEST['post'] );

		if ( ! strstr( $action, 'pos_host_add_to_grid_' ) ) {
			return;
		}

		$grid_id       = (int) substr( $action, strlen( 'pos_host_add_to_grid_' ) );
		$report_action = 'products_added_to_grid';

		foreach ( $post_ids as $post_id ) {
			if ( pos_host_is_in_grid( $grid_id, $post_id ) ) {
				continue;
			}

			$grid = pos_host_get_grid( $grid_id );
			$grid->add_tile(
				array(
					'type'    => 'product',
					'item_id' => $post_id,
				)
			);
			$grid->save();

			$changed++;
		}

		$sendback = esc_url_raw(
			add_query_arg(
				array(
					'post_type'    => 'product',
					$report_action => $changed,
					'ids'          => join( ',', $post_ids ),
				),
				''
			)
		);

		wp_redirect( $sendback );
		exit();
	}

	/**
	 * Removes a product tile from grids if trashed/deleted.
	 *
	 * @param $post_id Post ID.
	 */
	public function remove_grid_product_tile( $post_id ) {
		if ( ! $post_id || 'product' !== get_post_type( $post_id ) ) {
			return;
		}

		$grids = pos_host_get_tile_grids( $post_id, 'product' );

		foreach ( $grids as $grid_id ) {
			$grid    = pos_host_get_grid( $grid_id );
			$tile_id = pos_host_get_grid_tile_by_item_id( $grid_id, $post_id );

			$grid->delete_tile( $tile_id );
			$grid->save();
		}
	}

	/**
	 * Removes a category tile from grids if deleted.
	 *
	 * @param int $term     Term ID.
	 * @param int $tt_id    Term taxonomy ID.
	 * @param int $taxonomy Taxonomy slug.
	 */
	public function remove_grid_category_tile( $term, $tt_id, $taxonomy ) {
		if ( ! $term || 'product_cat' !== $taxonomy ) {
			return;
		}

		$grids = pos_host_get_tile_grids( $term, 'product_cat' );

		foreach ( $grids as $grid_id ) {
			$grid    = pos_host_get_grid( $grid_id );
			$tile_id = pos_host_get_grid_tile_by_item_id( $grid_id, $term, 'product_cat' );

			$grid->delete_tile( $tile_id );
			$grid->save();
		}
	}

	public function save_order_rounding_amount( $post_id, $post, $update ) {
		$post_type = get_post_type( $post_id );
		$order     = wc_get_order( $post_id );
		if ( 'shop_order' === $post_type && $update ) {
			$rounding_total = get_post_meta( $post_id, 'pos_host_rounding_total', true );
			if ( $rounding_total ) {
				$order->set_total( $rounding_total );
				$order->save();
			}
		}
	}

	public function get_rounding_total( $formatted_total, $instance ) {
		$rounding_total = get_post_meta( $instance->get_id(), 'pos_host_rounding_total', true );
		if ( $rounding_total ) {
			return $formatted_total . '<span class="woocommerce-help-tip" data-tip="Cash Rounding"></span>';
		} else {
			return $formatted_total;
		}

	}

	public function wc_settings_button_field( $value ) {
		$option_value      = get_option( $value['id'], $value['default'] );
		$field_description = WC_Admin_Settings::get_field_description( $value );
		$description       = $field_description['description'];
		$tooltip_html      = $field_description['tooltip_html'];
		$custom_attributes = array();

		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
		$value['class'] = empty( $value['class'] ) ? 'button' : $value['class'];
		?>
		<tr valign="top">
		<th scope="row" class="titledesc">
			<label for=""><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post( $tooltip_html ); ?></label>
		</th>
		<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
			<input
					name="<?php echo esc_attr( $value['id'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					type="<?php echo esc_attr( $value['type'] ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					value="<?php echo esc_attr( $value['button_title'] ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
				<?php echo esc_attr( implode( ' ', $custom_attributes ) ); ?>
			/><?php echo esc_html( $value['suffix'] ); ?> <?php echo esc_html( $description ); ?>
		</td>
		</tr>
		<?php
	}

	public function wc_settings_range_slider( $field ) {
		$option_value      = get_option( $field['id'], $field['default'] );
		$field_description = WC_Admin_Settings::get_field_description( $field );
		$description       = $field_description['description'];
		$tooltip_html      = $field_description['tooltip_html'];
		$custom_attributes = array();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
			foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for=""><?php echo esc_html( $field['title'] ); ?> <?php echo wp_kses_post( $tooltip_html ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>">
				<div class="range_slider_container">
					<span class="range_slider_value"><?php echo esc_attr( $option_value ); ?></span>
					<input
							name="<?php echo esc_attr( $field['id'] ); ?>"
							id="<?php echo esc_attr( $field['id'] ); ?>"
							style="<?php echo esc_attr( $field['css'] ); ?>"
							type="range"
							value="<?php echo esc_attr( $option_value ); ?>"
							class="range_slider <?php echo esc_attr( $field['class'] ); ?>"
							<?php echo esc_attr( implode( ' ', $custom_attributes ) ); ?>
					/><?php echo esc_html( $field['suffix'] ); ?> <?php echo esc_html( $description ); ?>
				</div>
			</td>
		</tr>
		<?php
	}

	public function wc_settings_media_upload( $field ) {
		$option_value      = get_option( $field['id'], $field['default'] );
		$field_description = WC_Admin_Settings::get_field_description( $field );
		$description       = $field_description['description'];
		$tooltip_html      = $field_description['tooltip_html'];
		$custom_attributes = array();
		$thumbnail_src     = wp_get_attachment_image_src( $option_value );
		$thumbnail_src     = $thumbnail_src ? $thumbnail_src[0] : wc_placeholder_img_src();

		if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
			foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for=""><?php echo esc_html( $field['title'] ); ?> <?php echo wp_kses_post( $tooltip_html ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $field['type'] ) ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>">
				<div class="image-thumbnail">
					<img src="<?php echo esc_url( $thumbnail_src ); ?>" width="60px" height="60px" />
				</div>
				<div style="margin-top: 10px;">
					<input
						type="hidden"
						name="<?php echo esc_attr( $field['id'] ); ?>"
						value="<?php echo esc_attr( $option_value ); ?>"
					/>
					<button type="button" class="upload-image-button button"><?php esc_html_e( 'Upload/Add image', 'woocommerce-pos-host' ); ?></button>
					<button type="button" class="remove-image-button button"><?php esc_html_e( 'Remove image', 'woocommerce-pos-host' ); ?></button>
				</div>
				<script type="text/javascript">
					( function ( $ ) {
						$( document ).ready( function () {
							var field_selector = '#<?php esc_html( $field['id'] ); ?>';
							var input_selector = '[name=<?php esc_html( $field['id'] ); ?>]';

							// Only show the "remove image" button when needed
							if ( ! $( input_selector ).val() ) {
								$( field_selector + ' .remove-image-button' ).hide();
							}

							// Uploading files
							var file_frame;

							$( document ).on( 'click', field_selector + ' .upload-image-button', function( event ) {

								event.preventDefault();

								// If the media frame already exists, reopen it.
								if ( file_frame ) {
									file_frame.open();
									return;
								}

								// Create the media frame.
								file_frame = wp.media.frames.downloadable_file = wp.media({
									title: '<?php esc_html_e( 'Choose an image', 'woocommerce-pos-host' ); ?>',
									button: {
										text: '<?php esc_html_e( 'Use image', 'woocommerce-pos-host' ); ?>'
									},
									multiple: false
								});

								// When an image is selected, run a callback.
								file_frame.on( 'select', function() {
									var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
									var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

									$( input_selector ).val( attachment.id );
									$( field_selector + ' .image-thumbnail' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
									$( field_selector + ' .remove-image-button' ).show();
								});

								// Finally, open the modal.
								file_frame.open();
							});

							$( document ).on( 'click', '.remove-image-button', function() {
								$( field_selector + ' .image-thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
								$( input_selector ).val( '' );
								$( field_selector + ' .remove-image-button' ).hide();
								return false;
							});
						} );
					} ( jQuery ) );
				</script>
			</td>
		</tr>
		<?php
	}

}

return new POS_HOST_Admin();
