<?php
/**
 * Outlet options meta box.
 *
 * @package WooCommerce_pos_host/Admin/Meta_Boxes/Views
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="outlet_options" class="panel-wrap outlet_options">
	<div class="wc-tabs-back"></div>
	<ul class="outlet_options_tabs wc-tabs">
		<?php foreach ( self::get_outlet_options_tabs() as $key => $settings_tab ) : ?>
			<li class="<?php echo esc_attr( $key ); ?>_options <?php echo esc_attr( $key ); ?>_tab <?php echo esc_attr( implode( ' ', (array) $settings_tab['class'] ) ); ?>">
				<a href="#<?php echo esc_html( $settings_tab['target'] ); ?>">
					<span><?php echo esc_html( $settings_tab['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
		self::output_tabs();
		do_action( 'pos_host_outlet_options_panels', $thepostid, $outlet_object );
	?>
	<div class="clear"></div>
</div>
