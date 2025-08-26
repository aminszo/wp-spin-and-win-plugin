<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;

class Plugin_Settings
{

    public static function init()
    {
        add_action('admin_post_swn_deluxe_save_settings', [__CLASS__, 'save_settings']);
    }


    public static function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $option_key = Settings::OPTION_KEY;
        $settings = Settings::get_settings();

        // Display notice if set
        Admin::display_notice(self::messages_list());

        include 'views/plugin-settings-view.php';
    }

    public static function save_settings()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized.', 'swn-deluxe'));
        }
        check_admin_referer('swn_deluxe_save_settings');

        $input = $_POST[Settings::OPTION_KEY] ?? [];

        $result = Settings::update_settings($input);

        $message = $result !== false ? 'update-success' : '';

        wp_safe_redirect(admin_url('admin.php?page=' . Admin::MENU_SLUGS['PARENT_MENU'] . '&message=' . $message));
        exit;
    }

    public static function messages_list()
    {
        return [
            'update-success' => [
                'type'    => 'success',
                'message' => __('Settings saved successfully', 'swn-deluxe'),
            ],
        ];
    }
}
