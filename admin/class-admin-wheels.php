<?php

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

namespace SWN_Deluxe;

if (! defined('ABSPATH')) exit;


class Admin_Wheels
{

    /**
     * Initialize hooks.
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
            ADMIN::PARENT_MENU_SLUG,
            __('Wheels', 'swn-deluxe'),
            __('Wheels', 'swn-deluxe'),
            'manage_options',
            'swn-wheels',
            [__CLASS__, 'render_wheels_list'],
            2
        );

        // Hidden "Edit Wheel" page (no direct menu entry).
        add_submenu_page(
            null,
            __('Edit Wheel', 'swn-deluxe'),
            __('Edit Wheel', 'swn-deluxe'),
            'manage_options',
            'swn-edit-wheel',
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
        $wheels = Wheels::get_all();
        include "templates/wheels-list-page.php";
    }


    /**
     * Render the Edit Wheel page.
     *
     * Handles:
     * - Displaying the wheel form for create/edit.
     * - Processing form submissions (create/update).
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
            $data = [
                'name'         => sanitize_text_field($_POST['name']),
                'display_name' => sanitize_text_field($_POST['display_name']),
                'slug'         => sanitize_title($_POST['slug']),
                'status'       => in_array($_POST['status'], ['active', 'inactive']) ? $_POST['status'] : 'inactive',
                'settings'     => maybe_serialize($_POST['settings'] ?? []),
            ];

            if ($wheel_id > 0) {
                Wheels::update($wheel_id, $data);
            } else {
                $wheel_id = Wheels::insert($data);
            }

            // Redirect back to the wheels list after saving.
            wp_safe_redirect(admin_url('admin.php?page=swn-wheels'));
            exit;
        }

        // Handle wheel deletion request 
        if (isset($_POST['swn_delete_wheel']) && check_admin_referer('swn_delete_wheel_action', 'swn_delete_wheel_nonce')) {
            $wheel_id_to_delete = intval($_POST['wheel_id']);
            if ($wheel_id_to_delete > 0) {
                Wheels::delete($wheel_id_to_delete);
                wp_safe_redirect(admin_url('admin.php?page=swn-wheels'));
                exit;
            }
        }


        include "templates/wheel-edit-page.php";
    }
}
