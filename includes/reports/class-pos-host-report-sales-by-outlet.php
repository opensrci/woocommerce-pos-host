<?php
/**
 * Sales Report by Outlet
 *
 * @package WooCommerce_pos_host/Classes/Reports
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Report_Sales_By_Outlet.
 */
class POS_HOST_Report_Sales_By_Outlet extends WC_Admin_Report {

	public $chart_colours     = array();
	public $register_ids      = array();
	public $outlet_ids        = array();
	public $outlet_ids_titles = array();
	private $report_data;

	/**
	 * Constructor
	 */
	public function __construct() {
		if ( isset( $_GET['outlet_ids'] ) && is_array( $_GET['outlet_ids'] ) ) {
			$this->outlet_ids = array_map( 'absint', $_GET['outlet_ids'] );
		} elseif ( isset( $_GET['outlet_ids'] ) ) {
			$this->outlet_ids = array( absint( $_GET['outlet_ids'] ) );
		}

		if ( $this->outlet_ids && is_array( $this->outlet_ids ) ) {
			foreach ( $this->outlet_ids as $outlet_id ) {
				$regs = pos_host_get_registers_by_outlet( $outlet_id );
				if ( $regs ) {
					$this->register_ids = empty( $this->register_ids ) ? $regs : array_merge( $regs, $this->register_ids );
				}
			}
		}
	}

	/**
	 * Get report data
	 *
	 * @return array
	 */
	public function get_report_data() {
		if ( empty( $this->report_data ) ) {
			$this->query_report_data();
		}
		return $this->report_data;
	}

	/**
	 * Get all data needed for this report and store in the class
	 */
	private function query_report_data() {
		$this->report_data = new stdClass();

		$parked_order_status = get_option( 'pos_host_parked_order_status', 'pending' );

		$this->report_data->orders = (array) $this->get_order_report_data(
			array(
				'data'                => array(
					'_order_total'        => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_sales',
					),
					'_order_shipping'     => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_shipping',
					),
					'_order_tax'          => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_tax',
					),
					'_order_shipping_tax' => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_shipping_tax',
					),
					'post_date'           => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'where_meta'          => array(
					'relation' => 'AND',
					array(
						'meta_key'   => array( 'pos_host_order_type' ),
						'meta_value' => 'POS',
						'operator'   => '=',
					),
					array(
						'meta_key'   => array( 'pos_host_register_id' ),
						'meta_value' => $this->register_ids,
						'operator'   => 'IN',
					),
				),
				'group_by'            => $this->group_by_query,
				'order_by'            => 'post_date ASC',
				'query_type'          => 'get_results',
				'filter_range'        => true,
				'order_types'         => array_merge( array( 'shop_order_refund' ), wc_get_order_types( 'sales-reports' ) ),
				'order_status'        => array( 'completed', 'processing', 'on-hold' ),
				'parent_order_status' => array( 'completed', 'processing', 'on-hold' ),
			)
		);

		$this->report_data->order_counts = (array) $this->get_order_report_data(
			array(
				'data'         => array(
					'ID'        => array(
						'type'     => 'post_data',
						'function' => 'COUNT',
						'name'     => 'count',
						'distinct' => true,
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'where_meta'   => array(
					'relation' => 'AND',
					array(
						'meta_key'   => array( 'pos_host_order_type' ),
						'meta_value' => 'POS',
						'operator'   => '=',
					),
					array(
						'meta_key'   => array( 'pos_host_register_id' ),
						'meta_value' => $this->register_ids,
						'operator'   => 'IN',
					),
				),
				'group_by'     => $this->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => true,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'completed', 'processing', 'on-hold' ),
			)
		);

		$this->report_data->saved_orders = (array) $this->get_order_report_data(
			array(
				'data'                => array(
					'_order_total'        => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_sales',
					),
					'_order_shipping'     => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_shipping',
					),
					'_order_tax'          => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_tax',
					),
					'_order_shipping_tax' => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_shipping_tax',
					),
					'post_date'           => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'where_meta'          => array(
					'relation' => 'AND',
					array(
						'meta_key'   => array( 'pos_host_order_type' ),
						'meta_value' => 'POS',
						'operator'   => '=',
					),
					array(
						'meta_key'   => array( 'pos_host_register_id' ),
						'meta_value' => $this->register_ids,
						'operator'   => 'IN',
					),
				),
				'group_by'            => $this->group_by_query,
				'order_by'            => 'post_date ASC',
				'query_type'          => 'get_results',
				'filter_range'        => true,
				'order_types'         => array_merge( array( 'shop_order_refund' ), wc_get_order_types( 'sales-reports' ) ),
				'order_status'        => array( $parked_order_status ),
				'parent_order_status' => array( $parked_order_status ),
			)
		);

		$this->report_data->coupons = (array) $this->get_order_report_data(
			array(
				'data'         => array(
					'order_item_name' => array(
						'type'     => 'order_item',
						'function' => '',
						'name'     => 'order_item_name',
					),
					'discount_amount' => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'coupon',
						'function'        => 'SUM',
						'name'            => 'discount_amount',
					),
					'post_date'       => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'where_meta'   => array(
					'relation' => 'AND',
					array(
						'meta_key'   => array( 'pos_host_order_type' ),
						'meta_value' => 'POS',
						'operator'   => '=',
					),
					array(
						'meta_key'   => array( 'pos_host_register_id' ),
						'meta_value' => $this->register_ids,
						'operator'   => 'IN',
					),
				),
				'where'        => array(
					array(
						'key'      => 'order_items.order_item_type',
						'value'    => 'coupon',
						'operator' => '=',
					),
				),
				'group_by'     => $this->group_by_query . ', order_item_name',
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => true,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'completed', 'processing', 'on-hold' ),
			)
		);

		$this->report_data->order_items = (array) $this->get_order_report_data(
			array(
				'data'         => array(
					'_qty'      => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => 'SUM',
						'name'            => 'order_item_count',
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'where_meta'   => array(
					'relation' => 'AND',
					array(
						'meta_key'   => array( 'pos_host_order_type' ),
						'meta_value' => 'POS',
						'operator'   => '=',
					),
					array(
						'meta_key'   => array( 'pos_host_register_id' ),
						'meta_value' => $this->register_ids,
						'operator'   => 'IN',
					),
				),
				'where'        => array(
					array(
						'key'      => 'order_items.order_item_type',
						'value'    => 'line_item',
						'operator' => '=',
					),
				),
				'group_by'     => $this->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => true,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'completed', 'processing', 'on-hold' ),
			)
		);

		$this->report_data->refunded_order_items = (array) $this->get_order_report_data(
			array(
				'data'         => array(
					'_qty'      => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => 'SUM',
						'name'            => 'order_item_count',
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'where_meta'   => array(
					'relation' => 'AND',
					array(
						'meta_key'   => array( 'pos_host_order_type' ),
						'meta_value' => 'POS',
						'operator'   => '=',
					),
					array(
						'meta_key'   => array( 'pos_host_register_id' ),
						'meta_value' => $this->register_ids,
						'operator'   => 'IN',
					),
				),
				'where'        => array(
					array(
						'key'      => 'order_items.order_item_type',
						'value'    => 'line_item',
						'operator' => '=',
					),
				),
				'group_by'     => $this->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => true,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => array( 'refunded' ),
			)
		);

		$this->report_data->partial_refunds = (array) $this->get_order_report_data(
			array(
				'data'                => array(
					'_refund_amount' => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_refund',
					),
					'post_date'      => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
					'_qty'           => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => 'SUM',
						'name'            => 'order_item_count',
					),
				),
				'where_meta'          => array(
					'relation' => 'AND',
					array(
						'meta_key'   => array( 'pos_host_order_type' ),
						'meta_value' => 'POS',
						'operator'   => '=',
					),
					array(
						'meta_key'   => array( 'pos_host_register_id' ),
						'meta_value' => $this->register_ids,
						'operator'   => 'IN',
					),
				),
				'group_by'            => $this->group_by_query,
				'order_by'            => 'post_date ASC',
				'query_type'          => 'get_results',
				'filter_range'        => true,
				'order_status'        => false,
				'parent_order_status' => array( 'completed', 'processing', 'on-hold' ),
			)
		);

		foreach ( $this->report_data->partial_refunds as $key => $value ) {
			$this->report_data->partial_refunds[ $key ]->order_item_count = $this->report_data->partial_refunds[ $key ]->order_item_count * -1;
		}

		$this->report_data->order_items = array_merge( $this->report_data->order_items, $this->report_data->partial_refunds );

		$this->report_data->total_order_refunds = array_sum(
			(array) absint(
				$this->get_order_report_data(
					array(
						'data'         => array(
							'ID' => array(
								'type'     => 'post_data',
								'function' => 'COUNT',
								'name'     => 'total_orders',
							),
						),
						'query_type'   => 'get_var',
						'filter_range' => true,
						'order_types'  => wc_get_order_types( 'order-count' ),
						'order_status' => array( 'refunded' ),
					)
				)
			)
		);

		$this->report_data->full_refunds = (array) $this->get_order_report_data(
			array(
				'data'         => array(
					'_order_total' => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_refund',
					),
					'post_date'    => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'where_meta'   => array(
					'relation' => 'AND',
					array(
						'meta_key'   => array( 'pos_host_order_type' ),
						'meta_value' => 'POS',
						'operator'   => '=',
					),
					array(
						'meta_key'   => array( 'pos_host_register_id' ),
						'meta_value' => $this->register_ids,
						'operator'   => 'IN',
					),
				),
				'group_by'     => $this->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => true,
				'order_status' => array( 'refunded' ),
			)
		);

		$this->report_data->refunds               = array_merge( $this->report_data->partial_refunds, $this->report_data->full_refunds );
		$this->report_data->total_sales           = wc_format_decimal( array_sum( wp_list_pluck( $this->report_data->orders, 'total_sales' ) ), 2 );
		$this->report_data->saved_sales           = wc_format_decimal( array_sum( wp_list_pluck( $this->report_data->saved_orders, 'total_sales' ) ), 2 );
		$this->report_data->total_tax             = wc_format_decimal( array_sum( wp_list_pluck( $this->report_data->orders, 'total_tax' ) ), 2 );
		$this->report_data->total_shipping        = wc_format_decimal( array_sum( wp_list_pluck( $this->report_data->orders, 'total_shipping' ) ), 2 );
		$this->report_data->total_shipping_tax    = wc_format_decimal( array_sum( wp_list_pluck( $this->report_data->orders, 'total_shipping_tax' ) ), 2 );
		$this->report_data->total_refunds         = wc_format_decimal( array_sum( wp_list_pluck( $this->report_data->partial_refunds, 'total_refund' ) ) + array_sum( wp_list_pluck( $this->report_data->full_refunds, 'total_refund' ) ), 2 );
		$this->report_data->total_coupons         = number_format( array_sum( wp_list_pluck( $this->report_data->coupons, 'discount_amount' ) ), 2 );
		$this->report_data->total_orders          = absint( array_sum( wp_list_pluck( $this->report_data->order_counts, 'count' ) ) );
		$this->report_data->total_partial_refunds = array_sum( wp_list_pluck( $this->report_data->partial_refunds, 'order_item_count' ) ) * -1;
		$this->report_data->total_item_refunds    = array_sum( wp_list_pluck( $this->report_data->refunded_order_items, 'order_item_count' ) ) * -1;
		$this->report_data->total_items           = absint( array_sum( wp_list_pluck( $this->report_data->order_items, 'order_item_count' ) ) * -1 );
		$this->report_data->average_sales         = wc_format_decimal( $this->report_data->total_sales / ( $this->chart_interval + 1 ), 2 );
		$this->report_data->net_sales             = wc_format_decimal( $this->report_data->total_sales - $this->report_data->total_shipping - $this->report_data->total_tax - $this->report_data->total_shipping_tax, 2 );
	}

	/**
	 * Get the legend for the main chart sidebar
	 *
	 * @return array
	 */
	public function get_chart_legend() {

		if ( ! $this->register_ids ) {
			return array();
		}

		$legend = array();
		$data   = $this->get_report_data();

		switch ( $this->chart_groupby ) {
			case 'day':
				/* translators: %s average sales */
				$average_sales_title = sprintf( __( '%s average daily sales', 'woocommerce-pos-host' ), '<strong>' . wc_price( $data->average_sales ) . '</strong>' );
				break;
			case 'month':
			default:
				/* translators: %s average sales */
				$average_sales_title = sprintf( __( '%s average monthly sales', 'woocommerce-pos-host' ), '<strong>' . wc_price( $data->average_sales ) . '</strong>' );
				break;
		}

		$legend[] = array(
			/* translators: %s total sales */
			'title'            => sprintf( __( '%s gross sales from this outlet', 'woocommerce-pos-host' ), '<strong>' . wc_price( $data->total_sales ) . '</strong>' ),
			'placeholder'      => __( 'This is the sum of the order totals after any refunds and including shipping and taxes.', 'woocommerce-pos-host' ),
			'color'            => $this->chart_colours['sales_amount'],
			'highlight_series' => 6,
		);
		$legend[] = array(
			/* translators: %s net sales */
			'title'            => sprintf( __( '%s net sales from this outlet', 'woocommerce-pos-host' ), '<strong>' . wc_price( $data->net_sales ) . '</strong>' ),
			'placeholder'      => __( 'This is the sum of the order totals after any refunds and excluding shipping and taxes.', 'woocommerce-pos-host' ),
			'color'            => $this->chart_colours['net_sales_amount'],
			'highlight_series' => 7,
		);
		$legend[] = array(
			'title'            => $average_sales_title,
			'color'            => $this->chart_colours['average'],
			'highlight_series' => 2,
		);
		$legend[] = array(
			/* translators: %s saved sales */
			'title'            => sprintf( __( '%s sales from this register for saved orders', 'woocommerce-pos-host' ), '<strong>' . wc_price( $data->saved_sales ) . '</strong>' ),
			'placeholder'      => __( 'This is the sum of the saved order totals after any refunds and including shipping and taxes.', 'woocommerce-pos-host' ),
			'color'            => $this->chart_colours['saved_sales_amount'],
			'highlight_series' => 8,
		);
		$legend[] = array(
			/* translators: %s number of orders */
			'title'            => sprintf( __( '%s orders placed', 'woocommerce-pos-host' ), '<strong>' . ( $data->total_order_refunds + $data->total_orders !== $data->total_orders ? '<del>' . ( $data->total_order_refunds + $data->total_orders ) . '</del> ' : '' ) . $data->total_orders . '</strong>' ),
			'color'            => $this->chart_colours['order_count'],
			'highlight_series' => 1,
		);

		$legend[] = array(
			/* translators: %s number of items */
			'title'            => sprintf( __( '%s items purchased', 'woocommerce-pos-host' ), '<strong>' . ( $data->total_item_refunds + $data->total_partial_refunds > 0 ? '<del>' . ( $data->total_item_refunds + $data->total_partial_refunds + $data->total_items ) . '</del> ' : '' ) . $data->total_items . '</strong>' ),
			'color'            => $this->chart_colours['item_count'],
			'highlight_series' => 0,
		);

		$legend[] = array(
			/* translators: %s total shipping */
			'title'            => sprintf( __( '%s charged for shipping', 'woocommerce-pos-host' ), '<strong>' . wc_price( $data->total_shipping ) . '</strong>' ),
			'color'            => $this->chart_colours['shipping_amount'],
			'highlight_series' => 5,
		);
		$legend[] = array(
			/* translators: %s total refunds */
			'title'            => sprintf( __( '%s in refunds', 'woocommerce-pos-host' ), '<strong>' . wc_price( $data->total_refunds ) . '</strong>' ),
			'color'            => $this->chart_colours['refund_amount'],
			'highlight_series' => 4,
		);
		$legend[] = array(
			/* translators: %s total coupons */
			'title'            => sprintf( __( '%s worth of coupons used', 'woocommerce-pos-host' ), '<strong>' . wc_price( $data->total_coupons ) . '</strong>' ),
			'color'            => $this->chart_colours['coupon_amount'],
			'highlight_series' => 3,
		);

		return $legend;
	}

	/**
	 * Output the report
	 */
	public function output_report() {

		$ranges = array(
			'year'       => __( 'Year', 'woocommerce-pos-host' ),
			'last_month' => __( 'Last Month', 'woocommerce-pos-host' ),
			'month'      => __( 'This Month', 'woocommerce-pos-host' ),
			'7day'       => __( 'Last 7 Days', 'woocommerce-pos-host' ),
		);

		$this->chart_colours = array(
			'sales_amount'       => '#b1d4ea',
			'net_sales_amount'   => '#3498db',
			'saved_sales_amount' => '#499273',
			'average'            => '#95a5a6',
			'order_count'        => '#dbe1e3',
			'item_count'         => '#ecf0f1',
			'shipping_amount'    => '#5cc488',
			'coupon_amount'      => '#f1c40f',
			'refund_amount'      => '#e74c3c',
		);

		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';

		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ), true ) ) {
			$current_range = '7day';
		}

		$this->calculate_current_range( $current_range );

		include WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php';
	}

	/**
	 * [get_chart_widgets description]
	 *
	 * @return array
	 */
	public function get_chart_widgets() {

		$widgets = array();

		if ( ! empty( $this->register_ids ) ) {
			$widgets[] = array(
				'title'    => __( 'Showing reports for:', 'woocommerce-pos-host' ),
				'callback' => array( $this, 'current_filters' ),
			);
		}

		$widgets[] = array(
			'title'    => '',
			'callback' => array( $this, 'products_widget' ),
		);

		return $widgets;
	}

	/**
	 * Show current filters
	 */
	public function current_filters() {

		$this->outlet_ids_titles = array();

		foreach ( $this->outlet_ids as $outlet_id ) {
			$outlet                    = pos_host_get_outlet( absint( $outlet_id ) );
			$this->outlet_ids_titles[] = $outlet ? $outlet->get_name() : '#' . $outlet_id;
		}

		echo '<p><strong>' . esc_html( implode( ', ', $this->outlet_ids_titles ) ) . '</strong></p>';
		echo '<p><a class="button" href="' . esc_url( remove_query_arg( 'outlet_ids' ) ) . '">' . esc_html__( 'Reset', 'woocommerce-pos-host' ) . '</a></p>';
	}

	/**
	 * Product selection
	 */
	public function products_widget() {
		?>
		<h4 class="section_title"><span><?php esc_html_e( 'Outlet Search', 'woocommerce-pos-host' ); ?></span></h4>
		<div class="section">
			<form method="GET">
				<div>
					<select class="wc-product-search" style="width:203px;" name="outlet_ids[]" data-placeholder="<?php esc_attr_e( 'Search for a outlet&hellip;', 'woocommerce-pos-host' ); ?>" data-action="pos_host_json_search_outlet" ></select>
					<input type="submit" class="submit button" value="<?php esc_attr_e( 'Show', 'woocommerce-pos-host' ); ?>" />
					<input type="hidden" name="range" value="
					<?php
					if ( ! empty( $_GET['range'] ) ) {
						echo esc_attr( wc_clean( wp_unslash( $_GET['range'] ) ) );
					}
					?>
					" />
					<input type="hidden" name="start_date" value="
					<?php
					if ( ! empty( $_GET['start_date'] ) ) {
						echo esc_attr( wc_clean( wp_unslash( $_GET['start_date'] ) ) );
					}
					?>
					" />
					<input type="hidden" name="end_date" value="
					<?php
					if ( ! empty( $_GET['end_date'] ) ) {
						echo esc_attr( wc_clean( wp_unslash( $_GET['end_date'] ) ) );
					}
					?>
					" />
					<input type="hidden" name="page" value="
					<?php
					if ( ! empty( $_GET['page'] ) ) {
						echo esc_attr( wc_clean( wp_unslash( $_GET['page'] ) ) );
					}
					?>
					" />
					<input type="hidden" name="tab" value="
					<?php
					if ( ! empty( $_GET['tab'] ) ) {
						echo esc_attr( wc_clean( wp_unslash( $_GET['tab'] ) ) );
					}
					?>
					" />
					<input type="hidden" name="report" value="
					<?php
					if ( ! empty( $_GET['report'] ) ) {
						echo esc_attr( wc_clean( wp_unslash( $_GET['report'] ) ) );
					}
					?>
					" />
				</div>
			</form>
		</div>
		<h4 class="section_title"><span><?php esc_html_e( 'Top Outlets', 'woocommerce-pos-host' ); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<?php
				global $wpdb;
				$debug           = false;
				$wpdb->registers = $wpdb->prefix . 'wc_poin_of_sale_registers';
				$order_types     = wc_get_order_types( 'order-count' );
				$order_types     = implode( "','", $order_types );
				$status          = '';
				$order_status    = apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) );

				if ( ! empty( $order_status ) ) {
					$status = "AND orders.post_status IN ( 'wc-" . implode( "','wc-", $order_status ) . "')";
				}

				$top_sellers = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT registers.ID as register_id, COUNT(pm_outlet.meta_value) as count, pm_outlet.meta_value as outlet_id
					FROM {$wpdb->posts} orders
					LEFT JOIN {$wpdb->postmeta} pm_reg ON (orders.ID = pm_reg.post_id AND pm_reg.meta_key = 'pos_host_register_id')
					LEFT JOIN {$wpdb->postmeta} pm_type ON (orders.ID = pm_type.post_id AND pm_type.meta_key = 'pos_host_order_type')
					LEFT JOIN {$wpdb->posts} registers ON registers.ID = pm_reg.meta_value
					LEFT JOIN {$wpdb->postmeta} pm_outlet ON (registers.ID = pm_outlet.post_id AND pm_outlet.meta_key = 'outlet')
					WHERE orders.post_type IN (%s)
					AND pm_type.meta_value = 'POS'
					AND orders.post_date >= %s AND orders.post_date < %s
					%s
					GROUP BY outlet_id ORDER BY count DESC LIMIT 12
					",
						$order_types,
						gmdate( 'Y-m-d', $this->start_date ),
						gmdate( 'Y-m-d', strtotime( '+1 DAY', $this->end_date ) ),
						$status
					)
				);

				if ( $top_sellers ) {
					foreach ( $top_sellers as $outlet ) {
						$get_outlet = pos_host_get_outlet( absint( $outlet->outlet_id ) );
						$title      = $get_outlet ? $get_outlet->get_name() : '#' . $outlet->outlet_id;

						echo '<tr class="' . esc_attr( in_array( $outlet->outlet_id, $this->outlet_ids ) ? 'active' : '' ) . '">
							<td class="count">' . esc_html( $outlet->count ) . '</td>
							<td class="name"><a href="' . esc_url( add_query_arg( 'outlet_ids', $outlet->outlet_id ) ) . '">' . esc_html( $title ) . '</a></td>
							<td class="sparkline">' . wp_kses_post( $this->event_sales_sparkline( $outlet->outlet_id, 7, 'count' ) ) . '</td>
						</tr>';
					}
				} else {
					echo '<tr><td colspan="3">' . esc_html__( 'No outlets found in range', 'woocommerce-pos-host' ) . '</td></tr>';
				}
				?>
			</table>
		</div>
		<h4 class="section_title"><span><?php esc_html_e( 'Top Earners', 'woocommerce-pos-host' ); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<?php

				$debug           = false;
				$wpdb->registers = $wpdb->prefix . 'wc_poin_of_sale_registers';
				$order_types     = wc_get_order_types( 'order-count' );
				$order_types     = implode( "','", $order_types );
				$status          = '';
				$order_status    = apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) );

				if ( ! empty( $order_status ) ) {
					$status = "AND orders.post_status IN ( 'wc-" . implode( "','wc-", $order_status ) . "')";
				}

				$top_earners = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT registers.ID as register_id, SUM(pm_total.meta_value) as order_total, pm_outlet.meta_value as outlet_id
					FROM {$wpdb->posts} orders
					LEFT JOIN {$wpdb->postmeta} pm_total ON (orders.ID = pm_total.post_id AND pm_total.meta_key = '_order_total' )
					LEFT JOIN {$wpdb->postmeta} pm_reg ON (orders.ID = pm_reg.post_id AND pm_reg.meta_key = 'pos_host_register_id')
					LEFT JOIN {$wpdb->postmeta} pm_type ON (orders.ID = pm_type.post_id AND pm_type.meta_key = 'pos_host_order_type')
					LEFT JOIN {$wpdb->posts} registers ON registers.ID = pm_reg.meta_value
					LEFT JOIN {$wpdb->postmeta} pm_outlet ON (registers.ID = pm_outlet.post_id AND pm_outlet.meta_key = 'outlet')
					WHERE orders.post_type IN (%s)
					AND pm_type.meta_value = 'POS'
					AND orders.post_date >= %s AND orders.post_date < %s
					%s
					GROUP BY outlet_id ORDER BY order_total DESC LIMIT 12
					",
						$order_types,
						gmdate( 'Y-m-d', $this->start_date ),
						gmdate( 'Y-m-d', strtotime( '+1 DAY', $this->end_date ) ),
						$status
					)
				);

				if ( $top_earners ) {
					foreach ( $top_earners as $outlet ) {
						$get_outlet = pos_host_get_outlet( absint( $outlet->outlet_id ) );
						$title      = $get_outlet ? $get_outlet->get_name() : '#' . $outlet->outlet_id;

						echo '<tr class="' . esc_attr( in_array( $outlet->outlet_id, $this->outlet_ids ) ? 'active' : '' ) . '">
							<td class="count">' . wp_kses_post( wc_price( $outlet->order_total ) ) . '</td>
							<td class="name"><a href="' . esc_url( add_query_arg( 'outlet_ids', $outlet->outlet_id ) ) . '">' . esc_html( $title ) . '</a></td>
							<td class="sparkline">' . wp_kses_post( $this->event_sales_sparkline( $outlet->outlet_id, 7, 'sales' ) ) . '</td>
						</tr>';
					}
				} else {
					echo '<tr><td colspan="3">' . esc_html__( 'No outlets found in range', 'woocommerce-pos-host' ) . '</td></tr>';
				}
				?>
			</table>
		</div>
		<?php
	}

	/**
	 * Prepares a sparkline to show sales in the last X days
	 *
	 * @param  int    $id ID of the product to show. Blank to get all orders.
	 * @param  int    $days Days of stats to get.
	 * @param  string $type Type of sparkline to get. Ignored if ID is not set.
	 * @return string
	 */
	public function event_sales_sparkline( $id = '', $days = 7, $type = 'sales' ) {
		if ( $id ) {
			$ids  = array();
			$regs = pos_host_get_registers_by_outlet( $id );
			if ( $regs ) {
				$ids = $regs;
			}
			$meta_key = 'sales' === $type ? '_order_total' : 'ID';

			if ( 'ID' === $meta_key ) {
				$meta_value = array(
					'type'     => 'post_data',
					'function' => 'COUNT',
					'name'     => 'sparkline_value',
					'distinct' => true,
				);
			} else {
				$meta_value = array(
					'type'     => 'meta',
					'function' => 'SUM',
					'name'     => 'sparkline_value',
				);
			}

			$data = $this->get_order_report_data(
				array(
					'data'         => array(
						$meta_key   => $meta_value,
						'post_date' => array(
							'type'     => 'post_data',
							'function' => '',
							'name'     => 'post_date',
						),
					),
					'where'        => array(
						array(
							'key'      => 'post_date',
							'value'    => gmdate( 'Y-m-d', strtotime( 'midnight -' . ( $days - 1 ) . ' days', current_time( 'timestamp' ) ) ),
							'operator' => '>',
						),
					),
					'where_meta'   => array(
						'relation' => 'AND',
						array(
							'meta_key'   => 'pos_host_order_type',
							'meta_value' => 'POS',
							'operator'   => '=',
						),
						array(
							'meta_key'   => 'pos_host_register_id',
							'meta_value' => $ids,
							'operator'   => 'IN',
						),
					),
					'group_by'     => 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)',
					'query_type'   => 'get_results',
					'filter_range' => false,
					'order_types'  => wc_get_order_types( 'order-count' ),
					'order_status' => array( 'completed', 'processing', 'on-hold' ),
				)
			);
		} else {

			$data = $this->get_order_report_data(
				array(
					'data'         => array(
						'_order_total' => array(
							'type'     => 'meta',
							'function' => 'SUM',
							'name'     => 'sparkline_value',
						),
						'post_date'    => array(
							'type'     => 'post_data',
							'function' => '',
							'name'     => 'post_date',
						),
					),
					'where'        => array(
						array(
							'key'      => 'post_date',
							'value'    => gmdate( 'Y-m-d', strtotime( 'midnight -' . ( $days - 1 ) . ' days', current_time( 'timestamp' ) ) ),
							'operator' => '>',
						),
					),
					'where_meta'   => array(
						array(
							'meta_key'   => 'pos_host_order_type',
							'meta_value' => 'POS',
							'operator'   => '=',
						),
					),
					'group_by'     => 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)',
					'query_type'   => 'get_results',
					'filter_range' => false,
					'order_types'  => wc_get_order_types( 'order-count' ),
					'order_status' => array( 'completed', 'processing', 'on-hold' ),
				)
			);
		}

			$total = 0;
		foreach ( $data as $d ) {
			$total += $d->sparkline_value;
		}

		if ( 'sales' === $type ) {
			/* translators: $1$s total worth %2$d last number of days */
			$tooltip = sprintf( __( 'Sold %1$s worth in the last %2$d days', 'woocommerce-pos-host' ), strip_tags( wc_price( $total ) ), $days );
		} else {
			/* translators: $1$s number of orders %2$d last number of days */
			$tooltip = sprintf( _n( '%1$d order placed in the last %2$d days', '%1$d orders placed in the last %2$d days', $total, 'woocommerce-pos-host' ), $total, $days );
		}

		$sparkline_data = array_values( $this->prepare_chart_data( $data, 'post_date', 'sparkline_value', $days - 1, strtotime( 'midnight -' . ( $days - 1 ) . ' days', current_time( 'timestamp' ) ), 'day' ) );

		return '<span class="wc_sparkline ' . esc_attr( 'sales' === $type ? 'lines' : 'bars' ) . ' tips" data-color="#777" data-tip="' . esc_attr( $tooltip ) . '" data-barwidth="' . 60 * 60 * 16 * 1000 . '" data-sparkline="' . esc_attr( json_encode( $sparkline_data ) ) . '"></span>';
	}

	/**
	 * Output an export link
	 */
	public function get_export_button() {

		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';
		?>
		<a
			href="#"
			download="report-<?php echo esc_attr( $current_range ); ?>-<?php echo esc_attr( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>.csv"
			class="export_csv"
			data-export="chart"
			data-xaxes="<?php esc_attr_e( 'Date', 'woocommerce-pos-host' ); ?>"
			data-groupby="<?php echo esc_attr( $this->chart_groupby ); ?>"
		>
			<?php esc_html_e( 'Export CSV', 'woocommerce-pos-host' ); ?>
		</a>
		<?php
	}

	/**
	 * Round our totals correctly
	 *
	 * @param  string $amount
	 * @return string
	 */
	private function round_chart_totals( $amount ) {
		if ( is_array( $amount ) ) {
			return array_map( array( $this, 'round_chart_totals' ), $amount );
		} else {
			return wc_format_decimal( $amount, wc_get_price_decimals() );
		}
	}

	/**
	 * Get the main chart
	 *
	 * @return string
	 */
	public function get_main_chart() {
		global $wp_locale;

		if ( ! $this->register_ids ) {
			?>
			<div class="chart-container">
				<p class="chart-prompt"><?php esc_html_e( '&larr; Choose outlet to view stats', 'woocommerce-pos-host' ); ?></p>
			</div>
			<?php
		} else {
			// Prepare data for report
			$order_counts         = $this->prepare_chart_data( $this->report_data->order_counts, 'post_date', 'count', $this->chart_interval, $this->start_date, $this->chart_groupby );
			$order_item_counts    = $this->prepare_chart_data( $this->report_data->order_items, 'post_date', 'order_item_count', $this->chart_interval, $this->start_date, $this->chart_groupby );
			$order_amounts        = $this->prepare_chart_data( $this->report_data->orders, 'post_date', 'total_sales', $this->chart_interval, $this->start_date, $this->chart_groupby );
			$saved_order_amounts  = $this->prepare_chart_data( $this->report_data->saved_orders, 'post_date', 'total_sales', $this->chart_interval, $this->start_date, $this->chart_groupby );
			$coupon_amounts       = $this->prepare_chart_data( $this->report_data->coupons, 'post_date', 'discount_amount', $this->chart_interval, $this->start_date, $this->chart_groupby );
			$shipping_amounts     = $this->prepare_chart_data( $this->report_data->orders, 'post_date', 'total_shipping', $this->chart_interval, $this->start_date, $this->chart_groupby );
			$refund_amounts       = $this->prepare_chart_data( $this->report_data->refunds, 'post_date', 'total_refund', $this->chart_interval, $this->start_date, $this->chart_groupby );
			$shipping_tax_amounts = $this->prepare_chart_data( $this->report_data->orders, 'post_date', 'total_shipping_tax', $this->chart_interval, $this->start_date, $this->chart_groupby );
			$tax_amounts          = $this->prepare_chart_data( $this->report_data->orders, 'post_date', 'total_tax', $this->chart_interval, $this->start_date, $this->chart_groupby );

			$net_order_amounts = array();

			foreach ( $order_amounts as $order_amount_key => $order_amount_value ) {
				$net_order_amounts[ $order_amount_key ]    = $order_amount_value;
				$net_order_amounts[ $order_amount_key ][1] = $net_order_amounts[ $order_amount_key ][1] - $shipping_amounts[ $order_amount_key ][1] - $shipping_tax_amounts[ $order_amount_key ][1] - $tax_amounts[ $order_amount_key ][1];
			}

			// Encode in json format
			$chart_data = json_encode(
				array(
					'order_counts'        => array_values( $order_counts ),
					'order_item_counts'   => array_values( $order_item_counts ),
					'order_amounts'       => array_map( array( $this, 'round_chart_totals' ), array_values( $order_amounts ) ),
					'saved_order_amounts' => array_map( array( $this, 'round_chart_totals' ), array_values( $saved_order_amounts ) ),
					'net_order_amounts'   => array_map( array( $this, 'round_chart_totals' ), array_values( $net_order_amounts ) ),
					'shipping_amounts'    => array_map( array( $this, 'round_chart_totals' ), array_values( $shipping_amounts ) ),
					'coupon_amounts'      => array_map( array( $this, 'round_chart_totals' ), array_values( $coupon_amounts ) ),
					'refund_amounts'      => array_map( array( $this, 'round_chart_totals' ), array_values( $refund_amounts ) ),
				)
			);
			?>
		<div class="chart-container">
			<div class="chart-placeholder main"></div>
		</div>
		<script type="text/javascript">

			var main_chart;

			jQuery(function(){
				var order_data = jQuery.parseJSON( '<?php echo wp_kses_post( $chart_data ); ?>' );
				var drawGraph = function( highlight ) {
					var series = [
						{
							label: "<?php echo esc_js( __( 'Number of items sold', 'woocommerce-pos-host' ) ); ?>",
							data: order_data.order_item_counts,
							color: '<?php echo esc_js( $this->chart_colours['item_count'] ); ?>',
							bars: { fillColor: '<?php echo esc_js( $this->chart_colours['item_count'] ); ?>', fill: true, show: true, lineWidth: 0, barWidth: <?php echo esc_js( $this->barwidth ); ?> * 0.5, align: 'center' },
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Number of orders', 'woocommerce-pos-host' ) ); ?>",
							data: order_data.order_counts,
							color: '<?php echo esc_js( $this->chart_colours['order_count'] ); ?>',
							bars: { fillColor: '<?php echo esc_js( $this->chart_colours['order_count'] ); ?>', fill: true, show: true, lineWidth: 0, barWidth: <?php echo esc_js( $this->barwidth ); ?> * 0.5, align: 'center' },
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Average sales amount', 'woocommerce-pos-host' ) ); ?>",
							data: [ [ <?php echo esc_js( min( array_keys( $order_amounts ) ) ); ?>, <?php echo esc_js( $this->report_data->average_sales ); ?> ], [ <?php echo esc_js( max( array_keys( $order_amounts ) ) ); ?>, <?php echo esc_js( $this->report_data->average_sales ); ?> ] ],
							yaxis: 2,
							color: '<?php echo esc_js( $this->chart_colours['average'] ); ?>',
							points: { show: false },
							lines: { show: true, lineWidth: 2, fill: false },
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Coupon amount', 'woocommerce-pos-host' ) ); ?>",
							data: order_data.coupon_amounts,
							yaxis: 2,
							color: '<?php echo esc_js( $this->chart_colours['coupon_amount'] ); ?>',
							points: { show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 2, fill: false },
							shadowSize: 0,
							<?php echo wp_kses_post( $this->get_currency_tooltip() ); ?>
						},
						{
							label: "<?php echo esc_js( __( 'Refund amount', 'woocommerce-pos-host' ) ); ?>",
							data: order_data.refund_amounts,
							yaxis: 2,
							color: '<?php echo esc_js( $this->chart_colours['refund_amount'] ); ?>',
							points: { show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 2, fill: false },
							shadowSize: 0,
							prepend_tooltip: "<?php echo wp_kses_post( get_woocommerce_currency_symbol() ); ?>"
						},
						{
							label: "<?php echo esc_js( __( 'Shipping amount', 'woocommerce-pos-host' ) ); ?>",
							data: order_data.shipping_amounts,
							yaxis: 2,
							color: '<?php echo esc_js( $this->chart_colours['shipping_amount'] ); ?>',
							points: { show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 2, fill: false },
							shadowSize: 0,
							prepend_tooltip: "<?php echo wp_kses_post( get_woocommerce_currency_symbol() ); ?>"
						},
						{
							label: "<?php echo esc_js( __( 'Gross Sales amount', 'woocommerce-pos-host' ) ); ?>",
							data: order_data.order_amounts,
							yaxis: 2,
							color: '<?php echo esc_js( $this->chart_colours['sales_amount'] ); ?>',
							points: { show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 2, fill: false },
							shadowSize: 0,
							<?php echo wp_kses_post( $this->get_currency_tooltip() ); ?>
						},
						{
							label: "<?php echo esc_js( __( 'Net Sales amount', 'woocommerce-pos-host' ) ); ?>",
							data: order_data.net_order_amounts,
							yaxis: 2,
							color: '<?php echo esc_js( $this->chart_colours['net_sales_amount'] ); ?>',
							points: { show: true, radius: 6, lineWidth: 4, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 5, fill: false },
							shadowSize: 0,
							<?php echo wp_kses_post( $this->get_currency_tooltip() ); ?>
						},
						{
							label: "<?php echo esc_js( __( 'Sales amount for saved orders', 'woocommerce-pos-host' ) ); ?>",
							data: order_data.saved_order_amounts,
							yaxis: 2,
							color: '<?php echo esc_js( $this->chart_colours['saved_sales_amount'] ); ?>',
							points: { show: true, radius: 6, lineWidth: 4, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 5, fill: false },
							shadowSize: 0,
							<?php echo wp_kses_post( $this->get_currency_tooltip() ); ?>
						}
					];

					if ( highlight !== 'undefined' && series[ highlight ] ) {
						highlight_series = series[ highlight ];

						highlight_series.color = '#9c5d90';

						if ( highlight_series.bars )
							highlight_series.bars.fillColor = '#9c5d90';

						if ( highlight_series.lines ) {
							highlight_series.lines.lineWidth = 5;
						}
					}

					main_chart = jQuery.plot(
						jQuery('.chart-placeholder.main'),
						series,
						{
							legend: {
								show: false
							},
							grid: {
								color: '#aaa',
								borderColor: 'transparent',
								borderWidth: 0,
								hoverable: true
							},
							xaxes: [ {
								color: '#aaa',
								position: "bottom",
								tickColor: 'transparent',
								mode: "time",
								timeformat: "<?php echo 'day' === $this->chart_groupby ? '%d %b' : '%b'; ?>",
								monthNames: <?php echo wp_kses_post( json_encode( array_values( $wp_locale->month_abbrev ) ) ); ?>,
								tickLength: 1,
								minTickSize: [1, "<?php echo esc_js( $this->chart_groupby ); ?>"],
								font: {
									color: "#aaa"
								}
							} ],
							yaxes: [
								{
									min: 0,
									minTickSize: 1,
									tickDecimals: 0,
									color: '#d4d9dc',
									font: { color: "#aaa" }
								},
								{
									position: "right",
									min: 0,
									tickDecimals: 2,
									alignTicksWithAxis: 1,
									color: 'transparent',
									font: { color: "#aaa" }
								}
							],
						}
					);

					jQuery('.chart-placeholder').resize();
				}

				drawGraph();

				jQuery('.highlight_series').hover(
					function() {
						drawGraph( jQuery(this).data('series') );
					},
					function() {
						drawGraph();
					}
				);
			});
		</script>
			<?php
		}
	}
}
