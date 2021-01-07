<?php
/**
 * Register Settings - Denomination Options
 *
 * @var array $denominations
 *
 * @package WooCommerce_POS_HOST/Admin/Settings/Views
 */
?>
<h2><?php esc_html_e( 'Denomination Options', 'woocommerce-pos-host' ); ?></h2>
<div id="denomination_options-description">
	<p><?php esc_html_e( 'The following table defines the denominations that your point of sale system use when handling cash', 'woocommerce-pos-host' ); ?></p>
</div>
<table class="pos-host-register-denomination-options widefat" id="pos-host-register-denomination-options">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Value', 'woocommerce-pos-host' ); ?></th>
			<th><?php esc_html_e( 'Type', 'woocommerce-pos-host' ); ?></th>
			<th><?php esc_html_e( 'Color', 'woocommerce-pos-host' ); ?></th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $denominations as $index => $denomination ) : ?>
			<?php
			// Starting from 1 because having an index of 0 breaks the checked attribute.
			$index++;
			?>
		<tr class="denomination-row" data-index="<?php echo esc_attr( $index ); ?>">
			<td class="denomination-value">
				<input type="number" step="0.01" name="<?php echo 'pos_host_cash_denominations[' . esc_attr( $index ) . '][value]'; ?>" value="<?php echo esc_attr( $denomination['value'] ); ?>" />
			</td>
			<td class="denomination-type">
				<input type="radio" id="<?php echo 'type_note_' . esc_attr( $index ); ?>" name="<?php echo 'pos_host_cash_denominations[' . esc_attr( $index ) . '][type]'; ?>" value="note" <?php checked( $denomination['type'], 'note', true ); ?> />
				<label for="<?php echo 'type_note_' . esc_attr( $index ); ?>"><?php esc_html_e( 'Note', 'woocommerce-pos-host' ); ?></label>
				<input type="radio" id="<?php echo 'type_coin_' . esc_attr( $index ); ?>" name="<?php echo 'pos_host_cash_denominations[' . esc_attr( $index ) . '][type]'; ?>" value="coin" <?php checked( $denomination['type'], 'coin', true ); ?> />
				<label for="<?php echo 'type_coin_' . esc_attr( $index ); ?>"><?php esc_html_e( 'Coin', 'woocommerce-pos-host' ); ?></label>
			</td>
			<td class="denomination-color">
				<input type="text" class="color-pick" name="<?php echo 'pos_host_cash_denominations[' . esc_attr( $index ) . '][color]'; ?>" value="<?php echo esc_attr( $denomination['color'] ); ?>" />
			</td>
			<td>
				<button class="button button-secondary remove-denomination"><?php esc_html_e( 'Remove', 'woocommerce-pos-host' ); ?></button>
			</td>
		</tr>
		<?php endforeach; ?>
		<?php if ( ! count( $denominations ) ) : ?>
		<tr class="no-denominations">
			<td colspan="4"><?php esc_html_e( 'No denominations have been added yet.', 'woocommerce-pos-host' ); ?></td>
		</tr>
		<?php endif; ?>
	</tbody>
	<tfoot>
		<tr class="actions">
			<td colspan="4">
				<button class="button button-primary add-denomination"><?php esc_html_e( 'Add Denomination', 'woocommerce-pos-host' ); ?></button>
			</td>
		</tr>
	</tfoot>
</table>
