<?php
/**
 * Receipt Customizer
 *
 * @var string $action
 * @var object $receipt_object
 *
 * @package WooCommerce_pos_host/Admin/Views
 */

$submit_text = 'edit' === $action ? __( 'Update', 'woocommerce-pos-host' ) : __( 'Publish', 'woocommerce-pos-host' );
$sections    = array(
	'general'     => __( 'General Details', 'woocommerce-pos-host' ),
	'logo'        => __( 'Shop Logo', 'woocommerce-pos-host' ),
	'header'      => __( 'Header Details', 'woocommerce-pos-host' ),
	'order'       => __( 'Order Details', 'woocommerce-pos-host' ),
	'product'     => __( 'Product Details', 'woocommerce-pos-host' ),
	'style'       => __( 'Style Details', 'woocommerce-pos-host' ),
	'header-text' => __( 'Header Text', 'woocommerce-pos-host' ),
	'footer-text' => __( 'Footer Text', 'woocommerce-pos-host' ),
	'css'         => __( 'Custom CSS', 'woocommerce-pos-host' ),
);
?>
<div id="customize-controls" class="wp-full-overlay expanded preview-desktop">
	<form class="wrap wp-full-overlay-sidebar" action="<?php echo esc_attr( $action ); ?>" id="pos-host-receipt-form" method="post">
		<?php wp_nonce_field( 'save-receipt' ); ?>
		<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr( $action ); ?>"/>
		<input type="hidden" id="referredby" name="referredby" value="<?php echo esc_url( wp_get_referer() ); ?>"/>

		<div id="customize-header-actions" class="wp-full-overlay-header">
			<div id="customize-save-button-wrapper" class="customize-save-button-wrapper">
				<input type="submit" name="save" id="save" class="button button-primary" value="<?php esc_attr_e( $submit_text, 'woocommerce-pos-host' ); ?>" disabled>
			</div>
			<span class="spinner"></span>
			<a class="customize-controls-close" href="<?php echo esc_url( admin_url( 'edit.php?post_type=pos_host_receipt' ) ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Close', 'woocommerce-pos-host' ); ?></span>
			</a>
		</div>

		<div id="widgets-right" class="wp-clearfix">
			<div id="customize-notifications-area" class="customize-control-notifications-container" style="display: block;"><ul></ul></div>

			<div class="wp-full-overlay-sidebar-content" tabindex="-1">
				<?php if ( ! empty( $receipt_object->get_name() ) ) : ?>
					<div id="customize-info" class="accordion-section customize-info">
						<div class="accordion-section-title">
							<span class="preview-notice"><?php esc_html_e( 'You are customizing', 'woocommerce-pos-host' ); ?> <strong class="panel-title" id="receipt-name"><?php echo esc_html( $receipt_object->get_name() ); ?></strong></span>
						</div>
					</div>
				<?php endif; ?>

				<div id="customize-theme-controls">
					<ul class="customize-pane-parent">
					<?php foreach ( $sections as $slug => $section ) : ?>
						<li id="accordion-section-<?php echo esc_attr( $slug ); ?>" class="accordion-section control-section control-section-default" aria-owns="sub-accordion-section-<?php echo esc_attr( $slug ); ?>">
							<h3 class="accordion-section-title" tabindex="0">
								<?php echo esc_html( $section ); ?><span class="screen-reader-text"><?php esc_html_e( 'Press return or enter to open this section', 'woocommerce-pos-host' ); ?></span>
							</h3>
						</li>
					<?php endforeach; ?>
					</ul><!-- .customize-pane-parent -->

					<?php foreach ( $sections as $slug => $section ) : ?>
					<ul class="customize-pane-child accordion-section-content accordion-section control-section control-section-default" id="sub-accordion-section-<?php echo esc_attr( $slug ); ?>">
						<li class="customize-section-description-container section-meta ">
							<div class="customize-section-title">
								<button type="button" class="customize-section-back" tabindex="0"><span class="screen-reader-text"><?php esc_html_e( 'Back', 'woocommerce-pos-host' ); ?></span></button>
								<h3><span class="customize-action"><?php esc_html_e( 'Customizing', 'woocommerce-pos-host' ); ?></span> <?php echo esc_html( $section ); ?></h3>
							</div>
						</li>
						<?php include "html-admin-receipt-customize-section-{$slug}.php"; ?>
					</ul>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</form>
	<div class="wp-full-overlay-main iframe-ready">
		<div class="inside" id="print-receipt-preview-display">
			<?php require 'html-admin-receipt-customize-preview.php'; ?>
		</div>
	</div>
</div>
