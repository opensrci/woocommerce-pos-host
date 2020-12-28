<?php
/**
 * POS Coupons
 *
 * Returns an array of strings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(
	100 => __( 'Coupon is not valid.', 'woocommerce-pos-host' ),
	/* translators: %s coupon code */
	101 => __( 'Sorry, it seems the coupon "%s" is invalid - it has now been removed from your order.', 'woocommerce-pos-host' ),
	/* translators: %s coupon code */
	102 => __( 'Sorry, it seems the coupon "%s" is not yours - it has now been removed from your order.', 'woocommerce-pos-host' ),
	103 => __( 'Coupon code already applied!', 'woocommerce-pos-host' ),
	/* translators: %s coupon code */
	104 => __( 'Sorry, coupon "%s" has already been applied and cannot be used in conjunction with other coupons.', 'woocommerce-pos-host' ),
	/* translators: %s coupon code */
	105 => __( 'Coupon "%s" does not exist!', 'woocommerce-pos-host' ),
	106 => __( 'Coupon usage limit has been reached.', 'woocommerce-pos-host' ),
	107 => __( 'This coupon has expired.', 'woocommerce-pos-host' ),
	/* translators: %s minimum spend */
	108 => __( 'The minimum spend for this coupon is %s.', 'woocommerce-pos-host' ),
	109 => __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce-pos-host' ),
	110 => __( 'Sorry, this coupon is not valid for sale items.', 'woocommerce-pos-host' ),
	111 => __( 'Please enter a coupon code.', 'woocommerce-pos-host' ),
	/* translators: %s maximum spend */
	112 => __( 'The maximum spend for this coupon is %s.', 'woocommerce-pos-host' ),
	/* translators: %s products */
	113 => __( 'Sorry, this coupon is not applicable to the products: %s.', 'woocommerce-pos-host' ),
	/* translators: %s categories */
	114 => __( 'Sorry, this coupon is not applicable to the categories: %s.', 'woocommerce-pos-host' ),
	200 => __( 'Coupon code applied successfully.', 'woocommerce-pos-host' ),
	201 => __( 'Coupon code removed successfully.', 'woocommerce-pos-host' ),
	202 => __( 'Discount added successfully.', 'woocommerce-pos-host' ),
	203 => __( 'Discount updated successfully.', 'woocommerce-pos-host' ),
);
