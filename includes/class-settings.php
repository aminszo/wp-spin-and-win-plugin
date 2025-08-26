<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;

class Settings
{
    const OPTION_KEY = 'swn_deluxe_settings';

    public static function get_settings()
    {
        return get_option(self::OPTION_KEY, []);
    }

    public static function update_settings($data)
    {

        $sanitized = [
            'sms_api_username'      => sanitize_text_field($data['sms_api_username'] ?? ''),
            'sms_api_password'      => sanitize_text_field($data['sms_api_password'] ?? ''),
            'pattern_sender_number' => sanitize_text_field($data['pattern_sender_number'] ?? ''),
            'text_sender_number'    => sanitize_text_field($data['text_sender_number'] ?? ''),
            'user_phone_meta_key'   => sanitize_text_field($data['user_phone_meta_key'] ?? 'digits_phone'),
            'remove_data_on_uninstall' => !empty($data['remove_data_on_uninstall']) ? 1 : 0,
        ];

        return update_option(self::OPTION_KEY, $sanitized);
    }
}
