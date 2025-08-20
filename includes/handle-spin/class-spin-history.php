<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


class Spin_History
{
    public static function log(int $wheel_id, int $user_id, int $prize_id)
    {
        global $wpdb;
        $table = DB::get_table_name('spin_history');
        $wpdb->insert($table, [
            'wheel_id'  => $wheel_id,
            'user_id'   => $user_id,
            'prize_id'  => $prize_id,
            'date'      => current_time('mysql')
        ]);
    }

    public static function user_has_spun_today(int $wheel_id, int $user_id): bool
    {
        global $wpdb;
        $table = DB::get_table_name('spin_history');
        $today = date('Y-m-d');
        return (bool) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE wheel_id = %d AND user_id = %d AND DATE(date) = %s",
                $wheel_id,
                $user_id,
                $today
            )
        );
    }
}
