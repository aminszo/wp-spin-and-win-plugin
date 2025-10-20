<?php
// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Load WPDB for DB access
global $wpdb;

// Check the cleanup option before doing anything destructive
$cleanup_enabled = get_option( 'spinandwin_cleanup_on_uninstall', false );

if ( ! $cleanup_enabled ) {
    return; // User did not request data removal
}

// List of plugin tables
$tables = [
    $wpdb->prefix . 'spin_wheels',
    $wpdb->prefix . 'spin_wheel_items',
    $wpdb->prefix . 'spin_results',
    $wpdb->prefix . 'spin_user_limits',
];

// Drop each table
foreach ( $tables as $table_name ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
}

// Remove plugin-specific options
delete_option( 'spinandwin_cleanup_on_uninstall' );
// delete_option( 'spinandwin_version' );
// delete_option( 'spinandwin_settings' );
