<?php
/**
 * POS Subscriptions Addon
 *
 * Returns an array of strings.
 */

defined( 'ABSPATH' ) || exit;

return array(
	0  => __( 'A subscription renewal has been removed from your cart. Multiple subscriptions can not be purchased at the same time.', 'woocommerce-pos-host' ),
	1  => __( 'A subscription has been removed from your cart. Due to payment gateway restrictions, different subscription products can not be purchased at the same time.', 'woocommerce-pos-host' ),
	2  => __( 'A subscription has been removed from your cart. Products and subscriptions can not be purchased at the same time.', 'woocommerce-pos-host' ),
	/* translators: %1$s number %2$s number */
	3  => __( '%1$s every %2$s', 'woocommerce-pos-host' ),
	/* translators: %1$s number %2$s number %3$s day */
	4  => __( '%1$s every %2$s on %3$s', 'woocommerce-pos-host' ),
	/* translators: %s number */
	5  => __( '%s on the last day of each month', 'woocommerce-pos-host' ),
	/* translators: %1$s number %2$s nth */
	6  => __( '%1$s on the %2$s of each month', 'woocommerce-pos-host' ),
	/* translators: %1$s number %2$s number */
	7  => __( '%1$s on the last day of every %2$s month', 'woocommerce-pos-host' ),
	/* translators: %1$s number %2$s nth %1$s number */
	8  => __( '%1$s on the %2$s day of every %3$s month', 'woocommerce-pos-host' ),
	/* translators: %1$s number %2$s number %3$s number */
	9  => __( '%1$s on %2$s %3$s each year', 'woocommerce-pos-host' ),
	/* translators: %1$s number %2$s number %3$s number %4$s nth */
	10 => __( '%1$s on %2$s %3$s every %4$s year', 'woocommerce-pos-host' ),
	11 => array(
		__( 'day', 'woocommerce-pos-host' ),
		/* translators: number of days %s */
		__( '%s days', 'woocommerce-pos-host' ),
		__( 'week', 'woocommerce-pos-host' ),
		/* translators: %s number of weeks*/
		__( '%s weeks', 'woocommerce-pos-host' ),
		__( 'month', 'woocommerce-pos-host' ),
		/* translators: %s number of months */
		__( '%s months', 'woocommerce-pos-host' ),
		__( 'year', 'woocommerce-pos-host' ),
		/* translators: %s number of years */
		__( '%s years', 'woocommerce-pos-host' ),
	),
	12 => array(
		/* translators: %s nth */
		__( '%sth', 'woocommerce-pos-host' ),
		/* translators: %s nst */
		__( '%sst', 'woocommerce-pos-host' ),
		/* translators: %s nnd */
		__( '%snd', 'woocommerce-pos-host' ),
		/* translators: %s nrd */
		__( '%srd', 'woocommerce-pos-host' ),
	),
	13 => array(
		/* translators: %1$s number %2$s number */
		__( '%1$s / %2$s', 'woocommerce-pos-host' ),
		/* translators: %1$s number %2$s number */
		__( ' %1$s every %2$s', 'woocommerce-pos-host' ),
	),
	/* translators: %1$s number %2$s number  */
	14 => __( '%1$s for %2$s', 'woocommerce-pos-host' ),
	/* translators: %1$s number %2$s number */
	15 => __( '%1$s with %2$s free trial', 'woocommerce-pos-host' ),
	16 => array(
		/* translators: %s something */
		__( '%s day', 'woocommerce-pos-host' ),
		/* translators: %s something */
		__( 'a %s-day', 'woocommerce-pos-host' ),
		/* translators: %s something */
		__( '%s week', 'woocommerce-pos-host' ),
		/* translators: %s something */
		__( 'a %s-week', 'woocommerce-pos-host' ),
		/* translators: %s something */
		__( '%s month', 'woocommerce-pos-host' ),
		/* translators: %s something */
		__( 'a %s-month', 'woocommerce-pos-host' ),
		/* translators: %s something */
		__( '%s year', 'woocommerce-pos-host' ),
		/* translators: %s something */
		__( 'a %s-year', 'woocommerce-pos-host' ),
	),
	/* translators: %1$s something %2$s something */
	17 => __( '%1$s and a %2$s sign-up fee', 'woocommerce-pos-host' ),
);
