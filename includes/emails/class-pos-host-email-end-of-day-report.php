<?php
/**
 * Class POS_HOST_Email_End_Of_Day_Report file.
 *
 * @package WooCommerce_pos_host/Classes/Emails
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'POS_HOST_Email_End_Of_Day_Report', false ) ) {
	return new POS_HOST_Email_End_Of_Day_Report();
}

/**
 * End of Day Email.
 *
 * An email sent to chosen recipient(s) when a register is closed.
 */
class POS_HOST_Email_End_Of_Day_Report extends WC_Email {

	/**
	 * Register object.
	 *
	 * @var POS_HOST_Register
	 */
	public $register = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = 'pos_end_of_day_report';
		$this->title          = __( 'End of Day Report', 'woocommerce-pos-host' );
		$this->description    = __( 'End of day reports are sent to chosen recipient(s) when a register is closed.', 'woocommerce-pos-host' );
		$this->template_html  = 'emails/pos-end-of-day-report.php';
		$this->template_plain = 'emails/plain/pos-end-of-day-report.php';
		$this->placeholders   = array(
			'{register_name}' => '',
		);

		// Triggers for this email.
		add_action( 'pos_host_end_of_day_report_notification', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor.
		parent::__construct();

		// Other settings.
		$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );

		// Templates path.
		$this->template_base = POS_HOST_ABSPATH . '/templates/';
	}

	/**
	 * Get email subject.
	 *
	 * @since 5.2.0
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'End of Day Report', 'woocommerce-pos-host' );
	}

	/**
	 * Get email heading.
	 *
	 * @since 5.2.0
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'End of Day Report', 'woocommerce-pos-host' );
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int                  $session_id The session ID.
	 * @param POS_HOST_Session|false $session    Session object.
	 */
	public function trigger( $session_id, $session = false ) {
		$this->setup_locale();

		if ( ! $session ) {
			$session = pos_host_get_session( $session_id );
		}

		if ( $session_id && is_a( $session, 'POS_HOST_Session' ) ) {
			$register = pos_host_get_register( $session->get_register_id() );

			$this->object   = $session;
			$this->register = $register;
		}

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'session'            => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'              => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'session'            => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => true,
				'email'              => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Default content to show below main email content.
	 *
	 * @since 5.2.0
	 * @return string
	 */
	public function get_default_additional_content() {
		return __( 'Congratulations on the sales', 'woocommerce-pos-host' );
	}

	/**
	 * Return content from the additional_content field.
	 *
	 * Displayed above the footer.
	 *
	 * @return string
	 */
	public function get_additional_content() {
		$content = $this->get_option( 'additional_content', '' );

		return apply_filters( 'woocommerce_email_additional_content_' . $this->id, $this->format_string( $content ), $this->object, $this );
	}

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields() {
		/* translators: %s: list of placeholders */
		$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'woocommerce-pos-host' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' );
		$this->form_fields = array(
			'enabled'            => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-pos-host' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'woocommerce-pos-host' ),
				'default' => 'yes',
			),
			'recipient'          => array(
				'title'       => __( 'Recipient(s)', 'woocommerce-pos-host' ),
				'type'        => 'text',
				/* translators: %s: WP admin email */
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'woocommerce-pos-host' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => true,
			),
			'subject'            => array(
				'title'       => __( 'Subject', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading'            => array(
				'title'       => __( 'Email heading', 'woocommerce-pos-host' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'additional_content' => array(
				'title'       => __( 'Additional content', 'woocommerce-pos-host' ),
				'description' => __( 'Text to appear below the main email content.', 'woocommerce-pos-host' ) . ' ' . $placeholder_text,
				'css'         => 'width:400px; height: 75px;',
				'placeholder' => __( 'N/A', 'woocommerce-pos-host' ),
				'type'        => 'textarea',
				'default'     => $this->get_default_additional_content(),
				'desc_tip'    => true,
			),
			'email_type'         => array(
				'title'       => __( 'Email type', 'woocommerce-pos-host' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'woocommerce-pos-host' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}
}

return new POS_HOST_Email_End_Of_Day_Report();
