<?php
/**
 * POS HOST
 *
 * Renders the POS HOST UI.
 * @params $loggedin
 * @params $outlet_id, $register_id
 * @var $loggedin true|false
 */

defined( 'ABSPATH' ) || exit;
?>

<!DOCTYPE html>
<html>
	<head>
		<title><?php 
                            if($register_data) {
                                echo esc_html( $register_data['name'] ); 
                            }else{
                                echo esc_html__( 'POS', 'woocommerce-pos-host' ); 
                            }    
                        ?>
                 </title>
		<link rel="manifest" href="<?php
                        echo esc_url( POS_HOST()->plugin_url() ) . '/assets/dist/images/manifest.'.$home_host.'.json'; 
                        
                        ?>">
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
		<meta name="theme-color" content="<?php echo esc_attr( $primary_color ); ?>">
		<meta http-equiv="Content-Type" name="viewport" charset="<?php echo esc_attr( get_option( 'blog_charset' ) ); ?>" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="<?php echo esc_attr( $primary_color ); ?>" />
	</head>
	<body>
		<div id="q-app">
			<app></app>
		</div>
		<script data-cfasync="false" type="text/javascript" class="pos_host_params" >
			window.pos_host_params = <?php
                                                    if($loggedin){
                                                        echo wp_kses_post( json_encode(POS_HOST_Sell::get_params())); 
                                                    }else{
                                                        echo wp_kses_post( json_encode(POS_HOST_Sell::get_params("light"))); 
                                                    }
                                            ?>;
			window.pos_host_params_ext = <?php
                                                    if($loggedin){
                                                        $params = POS_HOST_Sell::get_post_login_data($outlet_id, $register_id);
                                                        echo wp_kses_post( json_encode($params)); 
                                                    }else{
                                                        echo wp_kses_post('{}'); 
                                                    }
                                            ?>;
			window.pos_host_options = <?php
                                                    if($loggedin){
                                                        echo wp_kses_post('{"db_loaded":true}'); 
                                                    }else{
                                                        echo wp_kses_post('{"db_loaded":false}'); 
                                                    }
                                            ?>;
			window.pos_host_loggedin_user = <?php echo wp_kses_post( json_encode(POS_HOST_Sell::get_loggedin_user()));?>;
			window.pos_host_outlets = <?php echo wp_kses_post( json_encode( pos_host_get_outlets() ) ); ?>;
			window.pos_host_registers = <?php echo wp_kses_post( json_encode( pos_host_get_registers() ) ); ?>;
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
	</body>
</html>
