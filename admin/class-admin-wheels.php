<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


/**
 * Admin Wheels Class
 *
 * Handles the admin menu and pages for managing wheels.
 *
 * Responsible for:
 * - Registering the "Wheels" submenu and wheel edit page in the WordPress admin.
 * - Rendering the list of wheels.
 * - Rendering and processing the add/edit wheel form.
 * 
 * @package SWN_Deluxe
 */
class Admin_Wheels
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
     * Register admin menu items.
     *
     * - Adds "Wheels" submenu under the parent menu.
     * - Registers a hidden page for editing/creating wheels.
     *
     * @return void
     */
    public static function register_menu()
    {

        // Wheels list page.
        add_submenu_page(
            ADMIN::MENU_SLUGS['PARENT_MENU'],
            __('Wheels', 'swn-deluxe'),
            __('Wheels', 'swn-deluxe'),
            'manage_options',
            ADMIN::MENU_SLUGS['WHEELS_LIST_PAGE'],
            [__CLASS__, 'render_wheels_list'],
            2
        );

        // Hidden "Edit Wheel" page (no direct menu entry).
        add_submenu_page(
            null,
            __('Edit Wheel', 'swn-deluxe'),
            __('Edit Wheel', 'swn-deluxe'),
            'manage_options',
            ADMIN::MENU_SLUGS['WHEEL_EDIT_PAGE'],
            [__CLASS__, 'render_edit_wheel']
        );
    }


    /**
     * Render the Wheels list page.
     *
     * @return void
     */

    public static function render_wheels_list()
    {
        // Instantiate the list table
        $list_table = new Wheels_List_Table();
        $list_table->prepare_items();

        // Display notice if set
        Admin::display_notice(self::messages_list());


        include "views/wheels-list-view.php";
    }


    /**
     * Render the Edit Wheel page.
     *
     * Handles:
     * - Displaying the wheel form for create/edit.
     * - Processing form submissions (create/update).
     * - deleting wheel.
     *
     * @return void
     */
    public static function render_edit_wheel()
    {
        // Get the wheel ID from query string (if editing).
        $wheel_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $wheel = null;

        if ($wheel_id > 0) {
            $wheel = Wheels::get($wheel_id);
        }

        // Handle form submission.
        if (isset($_POST['swn_save_wheel']) && check_admin_referer('swn_save_wheel_action', 'swn_save_wheel_nonce')) {

            $settings = isset($_POST['settings']) ? (array) $_POST['settings'] : [];
            $settings = array_map('sanitize_text_field', $settings);

            $data = [
                'name'         => sanitize_text_field($_POST['name']),
                'display_name' => sanitize_text_field($_POST['display_name']),
                'slug'         => sanitize_title($_POST['slug']),
                'status'       => in_array($_POST['status'], ['active', 'inactive']) ? $_POST['status'] : 'inactive',
                'settings'    => wp_json_encode($settings),
            ];

            if ($wheel_id > 0) {
                $result = Wheels::update($wheel_id, $data);
                $message = $result ? 'update-success' : 'update-fail';

                // Redirect back to the wheels list after saving.
                wp_safe_redirect(admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEEL_EDIT_PAGE'] . '&id=' . $wheel_id . '&message=' . $message));
                exit;
            } else {
                $result = Wheels::insert($data);
                $message = $result ? 'create-success' : 'create-fail';

                // Redirect back to the wheels list after saving.
                wp_safe_redirect(admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEELS_LIST_PAGE'] . '&message=' . $message));
                exit;
            }
        }

        // Handle wheel deletion request 
        if (isset($_POST['swn_delete_wheel']) && check_admin_referer('swn_delete_wheel_action', 'swn_delete_wheel_nonce')) {
            $wheel_id_to_delete = intval($_POST['wheel_id']);
            if ($wheel_id_to_delete > 0) {
                $result = Wheels::delete($wheel_id_to_delete);
                $message = $result ? 'delete-success' : 'delete-fail';

                wp_safe_redirect(admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEELS_LIST_PAGE'] . '&message=' . $message));
                exit;
            }
        }

        // Display notice if set
        Admin::display_notice(self::messages_list());


        include "views/wheel-edit-view.php";
    }

    public static function messages_list()
    {
        return [
            'create-success' => [
                'type' => 'success',
                'message' => __('Wheel created successfully.', 'swn-deluxe'),
            ],
            'create-fail' => [
                'type' => 'error',
                'message' => __('Wheel createtion failed.', 'swn-deluxe'),
            ],
            'update-success' => [
                'type' => 'success',
                'message' => __('Wheel Updated successfully.', 'swn-deluxe'),
            ],
            'update-fail' => [
                'type' => 'error',
                'message' => __('Wheel Update failed.', 'swn-deluxe'),
            ],
            'delete-success' => [
                'type' => 'success',
                'message' => __('Wheel deleted successfully.', 'swn-deluxe'),
            ],
            'delete-fail' => [
                'type' => 'error',
                'message' => __('Wheel delete failed.', 'swn-deluxe'),
            ],
        ];
    }
}
