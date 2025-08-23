<?php

namespace SWN_Deluxe;

use SWN_Deluxe\DB;

defined('ABSPATH') || exit;


/**
 * Class Log
 *
 * Handles storage and retrieval of wheel spin logs.
 *
 * @package SWN_Deluxe
 */
class Spin_Log {

    /**
     * Get table name.
     *
     * @return string
     */
    protected static function table(): string {
        return DB::get_table_name('logs');
    }

    /**
     * Record a new spin log entry.
     *
     * @param int         $wheel_id
     * @param int         $item_id
     * @param int|null    $user_id
     * @param string|null $ip
     * @param string|null $session_id
     * @param string|null $note
     * @param array|null  $extra_data
     * @return int|false Inserted row ID on success, false on failure.
     */
    public static function add(
        int $wheel_id,
        int $item_id,
        ?int $user_id = null,
        ?string $ip = null,
        ?string $session_id = null,
        ?string $note = null,
        ?array $extra_data = null
    ) {
        global $wpdb;
        $table = self::table();

        $data = [
            'wheel_id'   => $wheel_id,
            'item_id'    => $item_id,
            'user_id'    => $user_id,
            'ip_address' => $ip,
            'session_id' => $session_id,
            'note'       => $note,
            'extra_data' => $extra_data ? maybe_serialize( $extra_data ) : null,
            'spin_time'  => current_time( 'mysql' ),
        ];

        $formats = [ '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s' ];

        $result = $wpdb->insert( $table, $data, $formats );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get logs for a wheel.
     *
     * @param int $wheel_id
     * @param int $limit
     * @return array
     */
    public static function get_by_wheel( int $wheel_id, int $limit = 50 ): array {
        global $wpdb;
        $table = self::table();

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE wheel_id = %d ORDER BY spin_time DESC LIMIT %d",
                $wheel_id,
                $limit
            )
        );
    }

    /**
     * Get logs for a user.
     *
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public static function get_by_user( int $user_id, int $limit = 50 ): array {
        global $wpdb;
        $table = self::table();

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE user_id = %d ORDER BY spin_time DESC LIMIT %d",
                $user_id,
                $limit
            )
        );
    }

    /**
     * Get logs for a guest by IP.
     *
     * @param string $ip
     * @param int    $limit
     * @return array
     */
    public static function get_by_ip( string $ip, int $limit = 50 ): array {
        global $wpdb;
        $table = self::table();

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE ip_address = %s ORDER BY spin_time DESC LIMIT %d",
                $ip,
                $limit
            )
        );
    }

    /**
     * Delete all logs for a wheel.
     *
     * @param int $wheel_id
     * @return int|false Number of rows deleted, or false on failure.
     */
    public static function delete_by_wheel( int $wheel_id ) {
        global $wpdb;
        $table = self::table();

        return $wpdb->delete( $table, [ 'wheel_id' => $wheel_id ], [ '%d' ] );
    }

    /**
     * Delete logs for a specific user.
     *
     * @param int $user_id
     * @return int|false
     */
    public static function delete_by_user( int $user_id ) {
        global $wpdb;
        $table = self::table();

        return $wpdb->delete( $table, [ 'user_id' => $user_id ], [ '%d' ] );
    }
}
