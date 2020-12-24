<?php
/**
 * Emails Controller
 *
 * Handles the sending of emails and email templates.
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Emails', false ) ) {
	return new POS_HOST_Emails();
}

/**
 * POS_HOST_Emails class.
 */
class POS_HOST_Emails {

	/**
	 * Email actions.
	 *
	 * @var array Actions list.
	 */
	public $actions;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->actions = array( 'pos_host_end_of_day_report' );

		add_filter( 'woocommerce_email_actions', array( $this, 'email_actions' ) );
		add_filter( 'woocommerce_email_classes', array( $this, 'email_classes' ) );
	}

	/**
	 * Updates the actions list.
	 *
	 * @since 5.2.0
	 *
	 * @param array $actions Actions list.
	 * @return array The updated actions list.
	 */
	public function email_actions( $actions ) {
		return array_merge( $actions, $this->actions );
	}

	/**
	 * Registers the new email classes.
	 *
	 * @since 0.0.1
	 *
	 * @param $emails array Email classes.
	 * @return array The updated email classes array.
	 */
	public function email_classes( $emails ) {
		$emails['POS_HOST_Email_New_Order']            = include 'emails/class-wc-pos-email-new-order.php';
		$emails['POS_HOST_Email_Customer_New_Account'] = include 'emails/class-wc-pos-email-customer-new-account.php';
		$emails['POS_HOST_Email_End_Of_Day_Report']    = include 'emails/class-wc-pos-email-end-of-day-report.php';

		return $emails;
	}
}

return new POS_HOST_Emails();
