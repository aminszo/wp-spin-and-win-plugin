<?php

namespace SWN_Deluxe;

if (! defined('ABSPATH')) exit; // Exit if accessed directly

class DB
{

    private static $tables = [
        'wheels'      => 'swn_wheels',
        'items'       => 'swn_wheel_items',
        'results'     => 'swn_spin_logs',
        'user_limits' => 'swn_spin_user_limits'
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
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY wheel_id (wheel_id)
        ) $charset_collate;";

        // RESULTS TABLE
        $sql[] = "CREATE TABLE " . $wpdb->prefix . self::$tables['results'] . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wheel_id BIGINT UNSIGNED NOT NULL,
            item_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            session_id VARCHAR(255) NULL,
            spin_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            prize_claimed TINYINT(1) NOT NULL DEFAULT 0,
            extra_data LONGTEXT NULL,
            PRIMARY KEY (id),
            KEY wheel_id (wheel_id),
            KEY item_id (item_id),
            KEY user_id (user_id),
            KEY ip_address (ip_address)
        ) $charset_collate;";

        // USER LIMITS TABLE
        $sql[] = "CREATE TABLE " . $wpdb->prefix . self::$tables['user_limits'] . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wheel_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            ip_address VARCHAR(45) NULL,
            last_spin DATETIME NULL,
            spin_count INT UNSIGNED NOT NULL DEFAULT 0,
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
}
