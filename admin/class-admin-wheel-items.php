<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


/**
 * Class Admin_Wheel_Items
 *
 * Handles the admin interface for managing wheel items.
 * Provides functionality to add, edit, and delete items for a specific wheel.
 *
 * @package SWN_Deluxe
 */
class Admin_Wheel_Items
{

    /**
     * Initialize hooks.
     * Hooks the menu registration to the WordPress admin_menu action.
     * 
     * @return void
     */
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
    }

    /**
     * Registers a hidden page for editing/creating wheel items.
     * 
     * @return void
     */
    public static function register_menu()
    {
        add_submenu_page(
            null, // Hidden page, linked from edit wheels list page.
            __('Manage Wheel Items', 'swn-deluxe'),
            __('Manage Wheel Items', 'swn-deluxe'),
            'manage_options',
            Admin::MENU_SLUGS['WHEEL_ITEMS_LIST_PAGE'],
            [__CLASS__, 'render_items_list']
        );

        add_submenu_page(
            null, // Hidden page, linked from edit wheels list page.
            __('Edit Item', 'swn-deluxe'),
            __('Edit Item', 'swn-deluxe'),
            'manage_options',
            Admin::MENU_SLUGS['WHEEL_ITEM_EDIT_PAGE'],
            [__CLASS__, 'render_edit_item']
        );
    }


    /**
     * Render the wheel items management page.
     *
     * - Loads the wheel and its items.
     * - Handles saving new items or updating existing ones.
     * - Handles deleting items.
     * - Includes the wheel-items template for displaying the admin page.
     *
     * @return void
     */
    public static function render_items_list()
    {
        $wheel_id = isset($_GET['wheel_id']) ? intval($_GET['wheel_id']) : 0;
        if (!$wheel_id) {
            echo '<div class="notice notice-error"><p>' . __('Invalid wheel ID.', 'swn-deluxe') . '</p></div>';
            return;
        }

        $wheel = Wheels::get($wheel_id);
        $items = Wheel_Items::get_by_wheel($wheel_id);

        // Handle item delete
        if (isset($_POST['swn_delete_item']) && check_admin_referer('swn_delete_item_action', 'swn_delete_item_nonce')) {
            $item_id = intval($_POST['item_id']);
            if ($item_id > 0) {
                Wheel_Items::delete($item_id);
                $notice_message = __('Item deleted successfully.', 'swn-deluxe');
                // Refresh the items list
                $items = Wheel_Items::get_by_wheel($wheel_id);
            }
        }

        if ($notice_message) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($notice_message) . '</p></div>';
        }

        include "views/wheel-items-list-view.php";
    }

    public static function render_edit_item()
    {
        $wheel_id = isset($_GET['wheel_id']) ? intval($_GET['wheel_id']) : 0;
        if (!$wheel_id) {
            echo '<div class="notice notice-error"><p>' . __('Invalid wheel ID.', 'swn-deluxe') . '</p></div>';
            return;
        }

        $wheel = Wheels::get($wheel_id);
        $items = Wheel_Items::get_by_wheel($wheel_id);

        // Handle item save
        if (isset($_POST['swn_save_item']) && check_admin_referer('swn_save_item_action', 'swn_save_item_nonce')) {
            $data = [
                'wheel_id'      => $wheel_id,
                'name'          => sanitize_text_field($_POST['name']),
                'display_name'  => sanitize_text_field($_POST['display_name']),
                'type'          => sanitize_text_field($_POST['type']),
                'value'         => sanitize_text_field($_POST['value']),
                'probability'   => floatval($_POST['probability']),
                'segment_color' => sanitize_text_field($_POST['segment_color']),
                'sort_order'    => intval($_POST['sort_order']),
            ];

            $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
            if ($item_id > 0) {
                Wheel_Items::update($item_id, $data);
                $notice_message = __('Item updated successfully.', 'swn-deluxe');
            } else {
                Wheel_Items::insert($data);
                $notice_message = __('New Item added successfully.', 'swn-deluxe');
            }

            // Refresh the items list
            $items = Wheel_Items::get_by_wheel($wheel_id);
        }

        // Just before including the template
        if ($notice_message) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($notice_message) . '</p></div>';
        }

        include "views/wheel-item-edit-view.php";
    }
}
