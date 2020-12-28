<?php
/**
 * Subscriptions
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Subscriptions', false ) ) {
	return new POS_HOST_Subscriptions();
}

class POS_HOST_Subscriptions {

	/**
	 * Consturctor.
	 */
	public function __construct() {
		add_filter( 'pos_host_edit_product_types', array( $this, 'edit_product_types' ), 10 );
		add_filter( 'pos_host_enqueue_scripts', array( $this, 'pos_enqueue_scripts' ), 10, 1 );
		add_filter( 'pos_host_i18n_js', array( $this, 'include_i18n_js' ), 20, 1 );
		add_filter( 'pos_host_inline_js', array( $this, 'add_inline_js' ), 20, 1 );
		add_action( 'pos_host_modal_add_product_custom_meta', array( $this, 'modal_add_product_custom_meta' ), 30, 1 );
	}

	public function edit_product_types( $types ) {
		if ( ! is_array( $types ) ) {
			$types = array();
		}
		$types['subscription'] = __( 'Subscription', 'woocommerce-pos-host' );
		return $types;
	}

	public function pos_enqueue_scripts( $sctipts ) {
		$sctipts['pos-host-subscriptions'] = POS_HOST()->plugin_url() . '/assets/js/register/subscriptions.js';
		return $sctipts;
	}

	public function include_i18n_js( $i18n ) {
		$i18n['subscriptions_i18n'] = include_once POS_HOST()->plugin_path() . '/i18n/subscriptions.php';
		return $i18n;
	}
	public function add_inline_js( $inline_js ) {
		$options                            = array(
			'multiple_subscriptions' => WC_Subscriptions_Payment_Gateways::one_gateway_supports( 'multiple_subscriptions' ),
			'accept_manual_renewals' => 'yes' === get_option( WC_Subscriptions_Admin::$option_prefix . '_accept_manual_renewals', 'no' ),
			'multiple_purchase'      => 'yes' === get_option( WC_Subscriptions_Admin::$option_prefix . '_multiple_purchase', 'no' ),
			'syncing_enabled'        => 'yes' === get_option( WC_Subscriptions_Admin::$option_prefix . '_sync_payments', 'no' ),
			'months'                 => $this->get_months(),
			'weekdays'               => array(
				WC_Subscriptions_Synchroniser::get_weekday( 0 ),
				WC_Subscriptions_Synchroniser::get_weekday( 1 ),
				WC_Subscriptions_Synchroniser::get_weekday( 2 ),
				WC_Subscriptions_Synchroniser::get_weekday( 3 ),
				WC_Subscriptions_Synchroniser::get_weekday( 4 ),
				WC_Subscriptions_Synchroniser::get_weekday( 5 ),
				WC_Subscriptions_Synchroniser::get_weekday( 6 ),
				WC_Subscriptions_Synchroniser::get_weekday( 7 ),
			),
		);
		$array                              = json_encode( $options );
		$WCSubscriptions                    = json_encode(
			array(
				'subscriptionLengths' => wcs_get_subscription_ranges(),
			)
		);
		$inline_js['subscriptions_options'] = '<script type="text/javascript" class="pos_host_subscriptions_options" > var pos_host_subscriptions_options = ' . $array . '; </script>';
		$inline_js['WCSubscriptions']       = '<script type="text/javascript" class="pos_host_WCSubscriptions" >       var WCSubscriptions = ' . $WCSubscriptions . '; </script>';

		return $inline_js;
	}

	private function get_months() {
		 global $wp_locale;
		$months = array();
		for ( $i = 1; $i <= 12; $i++ ) {
			$l = $i;
			if ( 1 === strlen( $l ) ) {
				$l = '0' . $i;
			}
			$months[] = $wp_locale->month[ $l ];
		}
		return $months;
	}

	public function modal_add_product_custom_meta( $type ) {
		?>
		<div id="<?php echo esc_attr( $type ); ?>_subscription_fields">
		<h3><?php esc_html_e( 'Subscription', 'woocommerce-pos-host' ); ?></h3>
		<table class="subscription_pricing_table" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th>
						<?php
							// translators: $s currency symbol
							echo wp_kses_post( sprintf( __( 'Sign-up Fee (%s)', 'woocommerce-pos-host' ), get_woocommerce_currency_symbol() ) );
						?>
					</th>
					<th><?php esc_html_e( 'Interval', 'woocommerce-pos-host' ); ?></th>
					<th><?php esc_html_e( 'Period', 'woocommerce-pos-host' ); ?></th>
					<th><?php esc_html_e( 'Length', 'woocommerce-pos-host' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><input type="text" class="_subscription_sign_up_fee"></td>
					<td>
						<select class="_subscription_period_interval">
							<?php
							foreach ( wcs_get_subscription_period_interval_strings() as $key => $value ) {
								echo "<option value='" . esc_attr( $key ) . "'>" . esc_html( $value ) . '</option>';
							}
							?>
						</select>
					</td>
					<td>
						<select class="_subscription_period">
							<?php
							foreach ( wcs_get_subscription_period_strings() as $key => $value ) {
								echo "<option value='" . esc_attr( $key ) . "'>" . esc_html( $value ) . '</option>';
							}
							?>
						</select>
					</td>
					<td>
						<select class="_subscription_length">
							<?php
							foreach ( wcs_get_subscription_ranges( 'month' ) as $key => $value ) {
								echo "<option value='" . esc_attr( $key ) . "'>" . esc_html( $value ) . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		</div>
		<?php
	}

	/**
	 * Main POS_HOST_Subscriptions Instance
	 *
	 * Ensures only one instance of POS_HOST_Subscriptions is loaded or can be loaded.
	 *
	 * @since 1.9.0
	 * @return POS_HOST_Subscriptions Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

return new POS_HOST_Subscriptions();
