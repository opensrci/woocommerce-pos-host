<?php
/**
 * Uninstall
 *
 * Uninstalling pos.host for WooCommerce deletes user roles, registers,
 * outlets, receipts, grids, tables and options.
 *
 * @package WooCommerce_pos_host
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// Delete the POS custom product.
wp_delete_post( (int) get_option( 'pos_host_custom_product_id' ), true );
delete_option( 'pos_host_custom_product_id' );

// Drop legacy tables if exist.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pos_host_outlets" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pos_host_registers" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pos_host_tiles" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pos_host_grids" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pos_host_receipts" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pos_host_reports" );

// Drop custom tables.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pos_host_grid_tiles" );

// Delete options.
//$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'pos\_host\_%';" );

// Delete usermeta.
//$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'pos\_host\_%';" );

// Delete site posts + data.
$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'pos_host_register', 'pos_host_outlet', 'pos_host_receipt' );" );
$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

// Remove user roles.
require_once 'includes/class-pos-host-install.php';
POS_HOST_Install::remove_roles();

// Clear any cached data that has been removed.
wp_cache_flush();
