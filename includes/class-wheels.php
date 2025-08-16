<?php

namespace SWN_Deluxe;

if (! defined('ABSPATH')) exit; // Exit if accessed directly

class Wheels
{

    /**
     * Get all wheels
     *
     * @param string $order_by Column to order by
     * @param string $order ASC or DESC
     * @return array
     */
    public static function get_all($order_by = 'created_at', $order = 'DESC')
    {
        global $wpdb;

        $allowed_order_by = ['id', 'name', 'display_name', 'slug', 'status', 'created_at', 'updated_at'];
        if (! in_array($order_by, $allowed_order_by, true)) {
            $order_by = 'created_at';
        }

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $table = DB::get_table_name('wheels');
        return $wpdb->get_results("SELECT * FROM {$table} ORDER BY {$order_by} {$order}");
    }

    /**
     * Get a single wheel by ID
     *
     * @param int $id
     * @return object|null
     */
    public static function get($id)
    {
        global $wpdb;
        $table = DB::get_table_name('wheels');
        $id = intval($id);
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));
    }

    /**
     * Get a single wheel by Slug
     *
     * @param int $id
     * @return object|null
     */

    public static function get_by_slug($slug)
    {
        global $wpdb;
        $table = DB::get_table_name('wheels');

        // Query the wheel
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE slug = %s LIMIT 1", $slug),
            ARRAY_A
        );

        if (!$row) {
            return null;
        }

        return $row;
    }



    /**
     * Insert a new wheel
     *
     * @param array $data
     * @return int|false
     */
    public static function insert($data)
    {
        global $wpdb;
        $table = DB::get_table_name('wheels');

        $inserted = $wpdb->insert($table, $data);
        return $inserted ? $wpdb->insert_id : false;
    }

    /**
     * Update an existing wheel
     *
     * @param int $id
     * @param array $data
     * @return int|false
     */
    public static function update($id, $data)
    {
        global $wpdb;
        $table = DB::get_table_name('wheels');
        return $wpdb->update($table, $data, ['id' => $id]);
    }

    /**
     * Delete a wheel
     *
     * @param int $id
     * @return int|false
     */
    public static function delete($id)
    {
        global $wpdb;
        $table = DB::get_table_name('wheels');
        return $wpdb->delete($table, ['id' => $id]);
    }
}
