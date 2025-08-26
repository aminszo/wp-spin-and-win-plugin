<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


class SMS
{
    private static $username;
    private static $password;
    private static $default_from = '+9810004223';
    private static $default_meta_key = 'digits_phone';

    public static function init()
    {
        // Load credentials from plugin settings
        self::$username = get_option('swn_sms_username', '');
        self::$password = get_option('swn_sms_password', '');
    }

    /**
     * Send SMS with plain text
     */
    public static function send_with_text($phone_number, $message, $user_id = null)
    {
        if (!self::validate_phone($phone_number)) {
            return self::log_sms($user_id, $phone_number, $message, 'failed', 'Invalid phone number');
        }

        $params = [
            'uname'   => self::$username,
            'pass'    => self::$password,
            'from'    => self::$default_from,
            'message' => $message,
            'to'      => json_encode([$phone_number]),
            'op'      => 'send'
        ];

        $response = self::do_curl_request("https://ippanel.com/services.jspd", $params);
        $status = self::parse_response_status($response);

        return self::log_sms($user_id, $phone_number, $message, $status, json_encode($response));
    }

    /**
     * Send SMS with pattern code
     */
    public static function send_with_pattern($phone_number, $pattern_code, $variables, $user_id = null)
    {
        if (!self::validate_phone($phone_number)) {
            return self::log_sms($user_id, $phone_number, json_encode($variables), 'failed', 'Invalid phone number');
        }

        $from = '+985000125475';
        $url = "https://ippanel.com/patterns/pattern?username=" . urlencode(self::$username)
            . "&password=" . urlencode(self::$password)
            . "&from={$from}&to=" . urlencode(json_encode([$phone_number]))
            . "&input_data=" . urlencode(json_encode($variables))
            . "&pattern_code={$pattern_code}";

        $response = self::do_curl_request($url, $variables, 'POST');
        $status = self::parse_response_status($response);

        return self::log_sms($user_id, $phone_number, "Pattern: {$pattern_code}", $status, json_encode($response));
    }

    /**
     * Get user phone number from usermeta, and normalize to international format
     */
    public static function get_user_phone($user_id)
    {
        $meta_key = get_option('swn_user_phone_meta', 'digits_phone'); // default fallback
        $raw = get_user_meta($user_id, $meta_key, true);

        if (!$raw) {
            return false;
        }

        $phone = self::normalize_phone($raw);

        return $phone ?: false;
    }

    /**
     * Normalize Iranian phone numbers into international format (+989XXXXXXXXX)
     */
    private static function normalize_phone($phone)
    {
        $phone = preg_replace('/\D/', '', $phone); // remove non-digits

        // Already starts with country code (98XXXXXXXXXX)
        if (preg_match('/^98\d{9,10}$/', $phone)) {
            return "+$phone";
        }

        // Starts with 0, like 0919...
        if (preg_match('/^0\d{10}$/', $phone)) {
            return '+98' . substr($phone, 1);
        }

        // Without 0 or country code, like 919...
        if (preg_match('/^\d{10}$/', $phone)) {
            return '+98' . $phone;
        }

        // Already normalized with plus sign
        if (preg_match('/^\+98\d{9,10}$/', $phone)) {
            return $phone;
        }

        return false; // unsupported format
    }

    /**
     * Validate phone number (basic Iran format + global digits)
     */
    private static function validate_phone($phone)
    {
        // must be +98XXXXXXXXXX (13 chars total)
        return preg_match('/^\+989\d{9}$/', $phone);
    }


    /**
     * Make cURL request
     */
    private static function do_curl_request($url, $params, $method = 'POST')
    {
        $ch = curl_init();
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
        ];

        if ($method === 'POST') {
            $options[CURLOPT_CUSTOMREQUEST] = 'POST';
            $options[CURLOPT_POSTFIELDS] = $params;
        }

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $response = ['error' => curl_error($ch)];
        } else {
            $response = json_decode($response, true);
        }

        curl_close($ch);
        return $response;
    }

    /**
     * Parse API response and decide status
     */
    private static function parse_response_status($response)
    {
        if (isset($response['error'])) {
            return 'failed';
        }
        // Assume ippanel returns status code 0 or >0 for success
        return (is_array($response) && isset($response[0]) && $response[0] > 0) ? 'sent' : 'failed';
    }

    /**
     * Log SMS into database
     */
    private static function log_sms($user_id, $phone_number, $message, $status, $response)
    {
        global $wpdb;
        $table = DB::get_table_name('sms_logs');

        $wpdb->insert($table, [
            'user_id'      => $user_id,
            'phone_number' => $phone_number,
            'message'      => $message,
            'status'       => $status,
            'response'     => $response,
            'created_at'   => current_time('mysql')
        ]);

        return [
            'status'   => $status,
            'response' => $response,
        ];
    }
}
