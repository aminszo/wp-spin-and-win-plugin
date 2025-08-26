<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;

class Plugin_Settings
{

    const OPTION_KEY = 'swn_deluxe_settings';

    public static function init()
    {
        add_action('admin_post_swn_deluxe_save_settings', [__CLASS__, 'save_settings']);
    }


    public static function get_settings()
    {
        $defaults = [
            'sms_api_username'      => '',
            'sms_api_password'      => '',
            'pattern_sender_number' => '',
            'text_sender_number'    => '',
            'user_phone_meta_key'   => '',
            'remove_data_on_uninstall' => 0,
        ];
        $options = get_option(self::OPTION_KEY, []);
        return wp_parse_args($options, $defaults);
    }

    public static function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = self::get_settings();

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

        $input = $_POST[self::OPTION_KEY] ?? [];
        $sanitized = [
            'sms_api_username'      => sanitize_text_field($input['sms_api_username'] ?? ''),
            'sms_api_password'      => sanitize_text_field($input['sms_api_password'] ?? ''),
            'pattern_sender_number' => sanitize_text_field($input['pattern_sender_number'] ?? ''),
            'text_sender_number'    => sanitize_text_field($input['text_sender_number'] ?? ''),
            'user_phone_meta_key'   => sanitize_text_field($input['user_phone_meta_key'] ?? 'digits_phone'),
            'remove_data_on_uninstall' => !empty($input['remove_data_on_uninstall']) ? 1 : 0,
        ];

        $result = update_option(self::OPTION_KEY, $sanitized);

        $message = $result !== false ? 'update-success' : '';
        
        wp_safe_redirect(admin_url('admin.php?page=' . Admin::MENU_SLUGS['PARENT_MENU']. '&message=' . $message));
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
