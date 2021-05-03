<?php
/**
 * trx_host Report
 *
 * @param object $report_body
 */
?>
<div id="sale_report_overlay" class="overlay_order_popup" style="display: block;">
	<div id="sale_report_popup">
		<div class="media-frame-title">
			<h1><?php esc_html_e( 'Transaction Report', 'woocommerce-pos-host' ); ?></h1>
		</div>
		<span class="close_popup"></span>
		<div id="sale_report_popup_inner">
			<h3><?php esc_html_e( 'Totals', 'woocommerce-pos-host' ); ?></h3>
			<table class='wp-list-table widefat fixed striped posts endofday'>
				<thead>
				<tr>
					<td><?php esc_html_e( 'Total Sales Count', 'woocommerce-pos-host' ); ?></td>
					<td><?php esc_html_e( 'Total Sales Amount', 'woocommerce-pos-host' ); ?></td>
					<td><?php esc_html_e( 'Total Refunds Amount', 'woocommerce-pos-host' ); ?></td>
					<td><?php esc_html_e( 'Total Cashback Amount', 'woocommerce-pos-host' ); ?></td>
					<td><?php esc_html_e( 'Total Amount', 'woocommerce-pos-host' ); ?></td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td><?php echo esc_html( $report_body->balances->totalSalesCount ); ?></td>
					<td><?php echo wp_kses_post( wc_price( ( $report_body->balances->totalSalesAmount / 100 ) ) ); ?></td>
					<td><?php echo wp_kses_post( wc_price( ( $report_body->balances->totalRefundsAmount / 100 ) ) ); ?></td>
					<td><?php echo wp_kses_post( wc_price( ( $report_body->balances->totalCashbackAmount / 100 ) ) ); ?></td>
					<td><?php echo wp_kses_post( wc_price( ( $report_body->balances->totalAmount / 100 ) ) ); ?></td>
				</tr>
				</tbody>
			</table>
			<?php
			$bankings = get_object_vars( $report_body->balances->issuerTotals );
			if ( count( $bankings ) ) :
				?>
				<h3><?php esc_html_e( 'Issuer Totals', 'woocommerce-pos-host' ); ?></h3>
				<table class='wp-list-table widefat fixed striped posts endofday'>
					<thead>
					<tr>
						<th><?php esc_html_e( 'Issuer', 'woocommerce-pos-host' ); ?></th>
						<?php
						foreach ( $bankings as $bank_name => $banking ) {
							echo '<th>' . esc_html( $bank_name ) . '</th>';
						}
						?>
					</tr>
					</thead>
					<tbody>
					<tr>
						<th><?php esc_html_e( 'Sales Count', 'woocommerce-pos-host' ); ?></th>
						<?php
						foreach ( $bankings as $bank_name => $banking ) {
							echo '<td>' . esc_html( $banking->totalSalesCount ) . '</td>';
						}
						?>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Sales Amount', 'woocommerce-pos-host' ); ?></th>
						<?php
						foreach ( $bankings as $bank_name => $banking ) {
							echo '<td>' . esc_html( $banking->totalSalesAmount ) . '</td>';
						}
						?>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Refunds Amount', 'woocommerce-pos-host' ); ?></th>
						<?php
						foreach ( $bankings as $bank_name => $banking ) {
							echo '<td>' . esc_html( $banking->totalRefundsAmount ) . '</td>';
						}
						?>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Total Amount', 'woocommerce-pos-host' ); ?></th>
						<?php
						foreach ( $bankings as $bank_name => $banking ) {
							echo '<td>' . esc_html( $banking->totalAmount ) . '</td>';
						}
						?>
					</tr>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<div class="media-frame-footer"></div>
	</div>
</div>
