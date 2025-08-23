<?php

namespace SWN_Deluxe;

use SWN_Deluxe\DB;

defined('ABSPATH') || exit;


/**
 * Class Spin_Chance
 *
 * Manages spin chances for users (or guests via IP) per wheel.
 *
 * @package SWN_Deluxe
 */
class Spin_Chance
{

    /**
     * Get table name.
     *
     * @return string
     */
    protected static function table()
    {
        return DB::get_table_name('chances');
    }

    /**
     * Get spin chances for a user (or guest by IP) on a specific wheel.
     *
     * @param int $wheel_id
     * @param int|null $user_id
     * @param string|null $ip
     * @return object|null
     */
    public static function get(int $wheel_id, ?int $user_id = null, ?string $ip = null)
    {
        global $wpdb;
        $table = self::table();

        if ($user_id) {
            return $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$table} WHERE wheel_id = %d AND user_id = %d", $wheel_id, $user_id)
            );
        } elseif ($ip) {
            return $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$table} WHERE wheel_id = %d AND ip_address = %s", $wheel_id, $ip)
            );
        }

        return null;
    }

    /**
     * Set spin chances (insert or update).
     *
     * @param int         $wheel_id
     * @param int|null    $user_id
     * @param string|null $ip
     * @param int         $chances
     * @return void
     */
    public static function set(int $wheel_id, ?int $user_id, ?string $ip, int $chances)
    {
        global $wpdb;
        $table = self::table();

        $existing = self::get($wheel_id, $user_id, $ip);

        if ($existing) {
            $wpdb->update(
                $table,
                ['spin_chance' => $chances],
                ['id' => $existing->id],
                ['%d'],
                ['%d']
            );
        } else {
            $wpdb->insert(
                $table,
                [
                    'wheel_id'    => $wheel_id,
                    'user_id'     => $user_id,
                    'ip_address'  => $ip,
                    'spin_chance' => $chances,
                    'last_spin'   => null
                ],
                ['%d', '%d', '%s', '%d', '%s']
            );
        }
    }

    /**
     * Increment spin chances for a user or guest.
     *
     * @param int         $wheel_id
     * @param int|null    $user_id
     * @param string|null $ip
     * @param int         $amount
     * @return void
     */
    public static function increment(int $wheel_id, ?int $user_id, ?string $ip, int $amount = 1)
    {
        $current = self::get($wheel_id, $user_id, $ip);
        $new     = $current ? ($current->spin_chance + $amount) : $amount;

        self::set($wheel_id, $user_id, $ip, $new);
    }

    /**
     * Decrement spin chances (on spin).
     *
     * @param int         $wheel_id
     * @param int|null    $user_id
     * @param string|null $ip
     * @return bool True if decremented, false if no chances left.
     */
    public static function decrement(int $wheel_id, ?int $user_id, ?string $ip): bool
    {
        global $wpdb;
        $table   = self::table();
        $current = self::get($wheel_id, $user_id, $ip);

        if (! $current || $current->spin_chance <= 0) {
            return false;
        }

        $wpdb->update(
            $table,
            [
                'spin_chance' => $current->spin_chance - 1,
                'last_spin'   => current_time('mysql'),
            ],
            ['id' => $current->id],
            ['%d', '%s'],
            ['%d']
        );

        return true;
    }

    /**
     * Get remaining spin chances.
     *
     * @param int         $wheel_id
     * @param int|null    $user_id
     * @param string|null $ip
     * @return int
     */
    public static function remaining(int $wheel_id, ?int $user_id, ?string $ip): int
    {
        $row = self::get($wheel_id, $user_id, $ip);
        return $row ? intval($row->spin_chance) : 0;
    }
}
