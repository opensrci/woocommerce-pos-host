<?php
/**
 * Receipt Functions
 *
 * @since 0.0.1
 *
 * @package WooCommerce_pos_host/Functions
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get receipt.
 *
 * @since 0.0.1
 *
 * @param int|POS_HOST_Receipt $receipt Receipt ID or object.
 *
 * @throws Exception If receipt cannot be read/found and $data parameter of POS_HOST_Receipt class constructor is set.
 * @return POS_HOST_Receipt|null
 */
function pos_host_get_receipt( $receipt ) {
	$receipt_object = new POS_HOST_Receipt( (int) $receipt );

	// If getting the default receipt and it does not exist, create a new one and return it.
	if ( pos_host_is_default_receipt( $receipt ) && ! $receipt_object->get_id() ) {
		delete_option( 'pos_host_default_receipt' );
		POS_HOST_Install::create_default_posts();

		return pos_host_get_receipt( (int) get_option( 'pos_host_default_receipt' ) );
	}

	return 0 !== $receipt_object->get_id() ? $receipt_object : null;
}

/**
 * Get receipt data.
 *
 * @since 0.0.1
 *
 * @param int|POS_HOST_Receipt $receipt Receipt ID or object.
 *
 * @throws Exception If receipt cannot be read/found and $data parameter of POS_HOST_Receipt class constructor is set.
 * @return array|null
 */
function pos_host_get_receipt_data( $receipt ) {
        $receipt_object = pos_host_get_receipt( $receipt );
        $receipt_data   = array();

        if ( $receipt_object ) {
                $receipt_data = $receipt_object->get_data();
                $logo     = wp_get_attachment_image_src( (int) $receipt_object->get_logo(), 'full' );
                $logo_src = $logo ? $logo[0] : '';
                $receipt_data['logo_url'] = $logo_src;
        }

        return apply_filters( 'pos_host_receipt_params', $receipt_data );
}

/**
 * Check if a specific receipt is the default one.
 *
 * @since 0.0.1
 *
 * @param int $receipt_id Receipt ID.
 * @return bool
 */
function pos_host_is_default_receipt( $receipt_id ) {
	return (int) get_option( 'pos_host_default_receipt', 0 ) === $receipt_id;
}
