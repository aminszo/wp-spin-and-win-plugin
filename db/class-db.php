<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


class DB
{

    private static $tables = [
        'wheels'      => 'swn_wheels',
        'items'       => 'swn_wheel_items',
        'logs'        => 'swn_spin_logs',
        'chances'     => 'swn_spin_chances'
    ];


    public static function get_table_name($key)
    {
        global $wpdb;
        return
            self::$tables[$key] ?
            $wpdb->prefix . self::$tables[$key] :
            null;
    }


    public static function create_tables()
    {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $charset_collate = $wpdb->get_charset_collate();

        // WHEELS TABLE
        $sql[] = "CREATE TABLE " . $wpdb->prefix . self::$tables['wheels'] . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            display_name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            status ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
            settings LONGTEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // ITEMS TABLE

        /*
        * 'Value' column is not used anymore and can be deleted, because now we save the value (percent, credit amount,...) in json in options coloumn.
        *
        */
        $sql[] = "CREATE TABLE " . $wpdb->prefix . self::$tables['items'] . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wheel_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            display_name VARCHAR(255) NOT NULL,
            type TEXT NOT NULL DEFAULT 'coupon',
            value TEXT NULL,
            probability DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            segment_color VARCHAR(20) NULL,
            sort_order INT UNSIGNED NOT NULL DEFAULT 0,
            options LONGTEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY wheel_id (wheel_id)
        ) $charset_collate;";

        // LOGS TABLE
        $sql[] = "CREATE TABLE " . $wpdb->prefix . self::$tables['logs'] . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wheel_id BIGINT UNSIGNED NOT NULL,
            item_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            session_id VARCHAR(255) NULL,
            spin_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            note VARCHAR(255) NULL,
            extra_data LONGTEXT NULL,
            PRIMARY KEY (id),
            KEY wheel_id (wheel_id),
            KEY item_id (item_id),
            KEY user_id (user_id),
            KEY ip_address (ip_address)
        ) $charset_collate;";

        // SPIN CHANCES TABLE
        $sql[] = "CREATE TABLE " . $wpdb->prefix . self::$tables['chances'] . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wheel_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            last_spin DATETIME NULL,
            spin_chance INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY wheel_id (wheel_id),
            KEY user_id (user_id),
            KEY ip_address (ip_address)
        ) $charset_collate;";

        // Execute all
        foreach ($sql as $query) {
            dbDelta($query);
        }
    }

    public static function delete_tables()
    {
        global $wpdb;

        foreach (self::$tables as $table_name) {
            $table_name = $wpdb->prefix . $table_name;
            $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        }
    }

    /**
     * Drop and recreate all plugin tables.
     * 
     * WARNING: This will delete ALL plugin data.
     * Intended for development/testing only, not for production.
     */

    public static function refresh()
    {
        self::delete_tables();
        self::create_tables();
    }


    /**
     * Drop, recreate, and seed all plugin tables with sample data.
     * 
     * WARNING: This is destructive. Use only in development or testing.
     */
    public static function refresh_with_seed()
    {
        self::refresh();
        Seeder::seed_sample_data();
    }
}
