<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


/**
 * Class Wheel_Items
 *
 * Handles CRUD operations for wheel items.
 *
 * @package SWN_Deluxe
 */
class Wheel_Items
{
    /**
     * Get all items for a specific wheel.
     *
     * @param int $wheel_id The ID of the wheel.
     * @return array List of wheel items as objects.
     */
    public static function get_by_wheel($wheel_id)
    {
        global $wpdb;
        $table = DB::get_table_name('items');
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table} WHERE wheel_id=%d ORDER BY sort_order ASC, id ASC", $wheel_id)
        );
    }


    /**
     * Get a single wheel item by ID.
     *
     * @param int $item_id The ID of the item.
     * @return object|null The item object, or null if not found.
     */
    public static function get($id)
    {
        global $wpdb;
        $table = DB::get_table_name('items');
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $id)
        );
    }


    /**
     * Insert a new wheel item into the database.
     *
     * @param array $data Associative array of item data.
     * @return int|false The inserted item ID on success, false on failure.
     */
    public static function insert($data)
    {
        global $wpdb;
        $table = DB::get_table_name('items');
        $wpdb->insert($table, $data);
        return $wpdb->insert_id;
    }


    /**
     * Update an existing wheel item.
     *
     * @param int   $item_id The ID of the item to update.
     * @param array $data    Associative array of updated item data.
     * @return int|false Number of rows updated, or false on failure.
     */
    public static function update($id, $data)
    {
        global $wpdb;
        $table = DB::get_table_name('items');
        $wpdb->update($table, $data, ['id' => $id]);
    }


    /**
     * Delete a wheel item by ID.
     *
     * @param int $item_id The ID of the item to delete.
     * @return int|false Number of rows deleted, or false on failure.
     */
    public static function delete($id)
    {
        global $wpdb;
        $table = DB::get_table_name('items');
        $wpdb->delete($table, ['id' => $id]);
    }


    /**
     * Delete all items associated with a specific wheel.
     *
     * This will remove every item linked to the given wheel ID from the database.
     *
     * @param int $wheel_id The ID of the wheel whose items should be deleted.
     * @return int|false Number of rows deleted, or false on failure.
     */
    public static function delete_by_wheel($wheel_id)
    {
        global $wpdb;
        $table = DB::get_table_name('items');
        $wpdb->delete($table, ['wheel_id' => $wheel_id]);
    }
}
