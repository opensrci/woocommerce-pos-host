<?php
/**
 * POS HOST
 *
 * Renders the POS HOST UI.
 *
 * @var $register_data
 * @var $outlet_data
 */

defined( 'ABSPATH' ) || exit;
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?php echo esc_html( $register_data['name'] ) . ' &lsaquo; ' . esc_html( $outlet_data['name'] ) . ' &lsaquo; ' . esc_html__( 'POS HOST', 'woocommerce-point-of-sale' ); ?></title>
		<link rel="manifest" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/manifest.json'; ?>">
		<link rel="apple-touch-icon" sizes="57x57" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/apple-icon-57x57.png'; ?>">
		<link rel="apple-touch-icon" sizes="60x60" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/apple-icon-60x60.png'; ?>">
		<link rel="apple-touch-icon" sizes="72x72" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/apple-icon-72x72.png'; ?>">
		<link rel="apple-touch-icon" sizes="76x76" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/apple-icon-76x76.png'; ?>">
		<link rel="apple-touch-icon" sizes="114x114" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/apple-icon-114x114.png'; ?>">
		<link rel="apple-touch-icon" sizes="120x120" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/apple-icon-120x120.png'; ?>">
		<link rel="apple-touch-icon" sizes="144x144" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/apple-icon-144x144.png'; ?>">
		<link rel="apple-touch-icon" sizes="152x152" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/apple-icon-152x152.png'; ?>">
		<link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/apple-icon-180x180.png'; ?>">
		<link rel="icon" type="image/png" sizes="192x192"  href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/android-icon-192x192.png'; ?>">
		<link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/favicon-32x32.png'; ?>">
		<link rel="icon" type="image/png" sizes="96x96" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/favicon-96x96.png'; ?>">
		<link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/favicon-16x16.png'; ?>">
		<link rel="mask-icon" href="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/safari-pinned-tab.svg'; ?>" color="#7f54b3">
		<meta name="msapplication-TileColor" content="<?php echo esc_attr( $primary_color ); ?>">
		<meta name="msapplication-TileImage" content="<?php echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/ms-icon-144x144.png'; ?>">
		<meta name="theme-color" content="<?php echo esc_attr( $primary_color ); ?>">
		<meta http-equiv="Content-Type" name="viewport" charset="<?php echo esc_attr( get_option( 'blog_charset' ) ); ?>" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="<?php echo esc_attr( $primary_color ); ?>" />
	</head>
	<body>
		<div id="pos-host-registers-edit">
			<app></app>
		</div>

		<script data-cfasync="false" type="text/javascript" class="pos_host_params" >
			window.pos_host_params = <?php echo wp_kses_post( POS_HOST_Sell::get_js_params() ); ?>;
			window.pos_host_register_data = <?php echo wp_kses_post( json_encode( $register_data ) ); ?>;
			window.pos_host_outlet_data = <?php echo wp_kses_post( json_encode( $outlet_data ) ); ?>;
			window.pos_host_receipt = <?php echo wp_kses_post( json_encode( POS_HOST_Sell::instance()->get_receipt( $register_data['receipt'] ) ) ); ?>;
			window.pos_host_grid = <?php echo wp_kses_post( $this->get_grid() ); ?>;
			window.pos_host_wc = <?php echo wp_kses_post( POS_HOST_Sell::get_js_wc_params() ); ?>;
			window.pos_host_cart = <?php echo wp_kses_post( POS_HOST_Sell::get_js_cart_params() ); ?>;
			window.pos_host_i18n = <?php echo wp_kses_post( json_encode( require_once POS_HOST()->plugin_path() . '/i18n/app.php' ) ); ?>;
			window.pos_host_coupon_i18n = <?php echo wp_kses_post( json_encode( require_once POS_HOST()->plugin_path() . '/i18n/coupon.php' ) ); ?>;
			window.pos_host_custom_product = <?php echo wp_kses_post( POS_HOST_Sell::instance()->get_custom_product_params() ); ?>;
		</script>

		<?php
			/*
			 * The following functions allow the POS enqueued scripts and styles to
			 * be loaded exclusively. Using wp_footer() would load more stuff that we
			 * do not need here.
			 */
			wp_enqueue_scripts();
			print_late_styles();
			print_footer_scripts();
		?>
		<?php require_once POS_HOST()->plugin_path() . '/includes/views/modal/html-modal-payments.php'; ?>
	</body>
</html>
