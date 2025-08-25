<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;

require_once 'class-admin-wheels.php';
require_once 'class-admin-settings.php';
require_once 'class-wheels-list-table.php';
require_once 'class-admin-wheel-items.php';


class Admin
{
    public const MENU_SLUGS = [
        'PARENT_MENU' => 'swn-settings',
        'WHEELS_LIST_PAGE' => 'swn-wheels-list',
        'WHEEL_EDIT_PAGE' => 'swn-edit-wheel',
        'WHEEL_ITEMS_LIST_PAGE' => 'swn-wheel-items-list',
        'WHEEL_ITEM_EDIT_PAGE' => 'swn-edit-wheel-item',
    ];


    public static function init()
    {
        add_action('admin_menu', array(self::class, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array(self::class, 'enqueue_admin_assets'));

        Admin_Wheels::init();
        Admin_Wheel_Items::init();
    }


    public static function add_admin_menu()
    {
        // Parent Menu
        add_menu_page(
            __('Spin & Win', 'swn-deluxe'),
            __('Spin & Win', 'swn-deluxe'),
            'manage_options', // Capability
            self::MENU_SLUGS['PARENT_MENU'],   // Menu slug
            [Admin_Settings::class, 'render_settings_page'], // this method does not exist yet
            'data:image/svg+xml;base64,' . base64_encode(
                file_get_contents(SWN_DELUXE_PLUGIN_DIR . '/assets/image/menu-icon.svg')
            ), //'dashicons-awards', // Icon
            30 // Position
        );

        // Settings page.
        add_submenu_page(
            ADMIN::MENU_SLUGS['PARENT_MENU'],
            __('Settings', 'swn-deluxe'),
            __('Settings', 'swn-deluxe'),
            'manage_options',
            ADMIN::MENU_SLUGS['PARENT_MENU'], // Menu slug (same as parent to make it "default") 
            [Admin_Settings::class, 'render_settings_page'],
            2
        );
    }


    /**
     * Enqueues admin scripts and styles in plugin admin pages.
     *
     * @param string $hook_suffix Current admin page hook suffix.
     * @return void
     */
    public static function enqueue_admin_assets($hook_suffix)
    {
        // Load admin assets only on plugin's admin pages
        if (! self::is_plugin_admin_page($hook_suffix)) return;

        wp_enqueue_style('swn-admin-css', SWN_DELUXE_PLUGIN_URL . 'assets/css/swn-admin.css', array(), SWN_DELUXE_VERSION);
        wp_enqueue_script('swn-admin-js', SWN_DELUXE_PLUGIN_URL . 'assets/js/swn-admin.js', array('jquery'), 111, true);
        wp_localize_script('swn-admin-js', 'swnData', array(
            'params' => [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('swn_admin_nonce')
            ],
            'translations' => [
                'copied' => __('Copied!', 'swn-deluxe'),
                'copy_shortcode' => __('Copy Shortcode', 'swn-deluxe'),
            ]
        ));
    }


    public static function is_plugin_admin_page(string $hook_suffix): bool
    {
        foreach (self::MENU_SLUGS as $slug) {
            if (strpos($hook_suffix, $slug) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function display_notice($messages_list)
    {
        if (!empty($_GET['message'])) {
            $message_id = $_GET['message'];

            $notice = $messages_list[$message_id] ?? null;

            if (!empty($notice)) {
                echo '<div class="notice notice-' . $notice['type'] . ' is-dismissible"><p>' . esc_html($notice['message']) . '</p></div>';
            }
        }
    }
}
