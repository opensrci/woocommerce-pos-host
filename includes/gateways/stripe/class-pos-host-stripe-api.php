<?php
/**
 * Stripe API Handler
 *
 * @package WooCommerce_pos_host/Gateways
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Stripe_API.
 */
class POS_HOST_Stripe_API {
	private $secret_key;

	public function __construct() {
		$this->init();
	}

	protected function init() {
		$this->secret_key = POS_HOST_Stripe::get_secret_key();
	}

	public function create_token() {
		\Stripe\Stripe::setApiKey( $this->secret_key );

		$token = \Stripe\Terminal\ConnectionToken::create();

		return $token->toArray();
	}

	public function create_payment_intent( $amount, $currency, $payment_method_types, $capture_method ) {
		\Stripe\Stripe::setApiKey( $this->secret_key );

		$payment_intent = \Stripe\PaymentIntent::create(
			array(
				'amount'               => $amount,
				'currency'             => $currency,
				'payment_method_types' => $payment_method_types,
				'capture_method'       => $capture_method,
			)
		);

		return $payment_intent->toArray();
	}

	public function capture_payment( $id ) {
		\Stripe\Stripe::setApiKey( $this->secret_key );

		$intent   = \Stripe\PaymentIntent::retrieve( $id );
		$captured = $intent->capture();

		return $captured->toArray();
	}

	public function get_locations() {
		$locations = array();

		try {
			\Stripe\Stripe::setApiKey( $this->secret_key );
			$locations = \Stripe\Terminal\Location::all( array( 'limit' => 20 ) )->data;
		} catch ( Exception $e ) {
			return array();
		}

		return $locations;
	}

	public function get_terminals() {
		$terminals = array();

		try {
			\Stripe\Stripe::setApiKey( $this->secret_key );
			$terminals = \Stripe\Terminal\Reader::all( array( 'limit' => 100 ) )->data;
		} catch ( Exception $e ) {
			return array();
		}

		return $terminals;
	}
}
