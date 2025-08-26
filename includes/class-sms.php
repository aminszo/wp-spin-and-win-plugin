<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


class SMS
{
    private static $username;
    private static $password;
    private static $default_pattern_sender_number = '+985000125475';
    private static $default_text_sender_number = '+9810004223';
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

        $url = "https://ippanel.com/services.jspd";
        $params = [
            'uname'   => self::$username,
            'pass'    => self::$password,
            'from'    => self::$default_text_sender_number,
            'message' => $message,
            'to'      => json_encode([$phone_number]),
            'op'      => 'send'
        ];

        $response = self::do_curl_request($url, $params, true);
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

        $from = self::$default_pattern_sender_number;

        $url = "https://ippanel.com/patterns/pattern?username=" . urlencode(self::$username)
            . "&password=" . urlencode(self::$password)
            . "&from={$from}&to=" . urlencode(json_encode([$phone_number]))
            . "&input_data=" . urlencode(json_encode($variables))
            . "&pattern_code=" . urlencode($pattern_code);

        $response = self::do_curl_request($url, $variables, false);
        $status = self::parse_response_status($response);

        return self::log_sms($user_id, $phone_number, "Pattern: {$pattern_code}", $status, json_encode($response));
    }

    /**
     * Get user phone number from usermeta, and normalize to international format
     */
    public static function get_user_phone($user_id)
    {
        $phone_meta_key = get_option('swn_user_phone_meta', self::$default_meta_key); // default fallback
        $raw = get_user_meta($user_id, $phone_meta_key, true);

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
        if (preg_match('/^98\d{10}$/', $phone)) {
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
        if (preg_match('/^\+98\d{10}$/', $phone)) {
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
    private static function do_curl_request($url, $params, $decode_response = false)
    {
        $ch = curl_init();
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => $params
        ];

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $response = ['curl_error' => curl_error($ch)];
        } else {
            if ($decode_response) {
                $response = json_decode($response, true);
            }
        }

        curl_close($ch);
        return $response;
    }


    /**
     * Normalize SMS API responses into a consistent structure.
     *
     * Why this exists:
     * ----------------
     * The SMS provider returns different response formats depending on the type of request:
     *
     *   1. Text SMS (normal send):
     *      - Response is a JSON string, which when decoded becomes an array.
     *      - Example success: [0, 45345345372]
     *        → index 0 = status code (0 = success)
     *        → index 1 = SMS ID (used for tracking delivery status later)
     *      - Example failure: [422, "failed for some reason"]
     *        → index 0 = error code
     *        → index 1 = error message
     *
     *   2. Pattern SMS (templated send):
     *      - Response is a plain string, NOT JSON.
     *      - Example success: "348723572857"
     *        → a numeric SMS ID string.
     *      - Example failure: "Invalid credentials" (or any other message)
     *        → a plain text error string.
     *
     * Because these formats are inconsistent, this method "normalizes" them into
     * a single predictable associative array that the rest of the plugin can safely consume.
     *
     * Normalized structure returned:
     * ------------------------------
     * [
     *     'success' => bool,          // true if sent successfully, false otherwise
     *     'code'    => int,           // 0 for success, 100 for provider error, 101 for invalid/parsing errors
     *     'message' => string|null,   // error message if failed, null if success
     *     'sms_id'  => string|null,   // SMS ID if success, null if failed
     *     'raw'     => mixed          // raw response from provider (kept for debugging/logging)
     * ]
     *
     * @param mixed $raw The raw response from the SMS provider (could be array, string, or unexpected type).
     * @return array Normalized response as described above.
     */
    private static function normalize_response($raw)
    {
        // Default fallback response for invalid or unexpected input.
        $response = [
            'success' => false,
            'code'    => 101, // default = parsing/invalid response
            'message' => 'Invalid response, unable to parse response.',
            'sms_id'  => null,
            'raw'     => $raw // keep raw response for debugging/logging
        ];

        // Case A: Response is an array (Text SMS API)
        if (is_array($raw)) {
            // Defensive extraction: provider *should* always return two values.
            $errorCode = $raw[0] ?? null;
            $secondVal = $raw[1] ?? null;

            // Success: [0, <sms_id>]
            if ($errorCode === 0 && $secondVal) {
                return [
                    'success' => true,
                    'code'    => 0,
                    'message' => null,
                    'sms_id'  => $secondVal,
                    'raw'     => $raw
                ];
            }

            // Failure: [<error_code>, <error_message>]
            return [
                'success' => false,
                'code'    => $errorCode ?? 100, // if missing, fallback to provider error
                'message' => $secondVal ?? 'Unknown provider error',
                'sms_id'  => null,
                'raw'     => $raw
            ];
        }

        // Case B: Response is a string (Pattern SMS API)
        if (is_string($raw)) {
            $trimmed = trim($raw);

            // Success: numeric string representing SMS ID
            if (ctype_digit($trimmed)) {
                return [
                    'success' => true,
                    'code'    => 0,
                    'message' => null,
                    'sms_id'  => $trimmed,
                    'raw'     => $raw
                ];
            }

            // Failure: string error message
            return [
                'success' => false,
                'code'    => 100, // provider error
                'message' => $trimmed ?: 'Empty provider response',
                'sms_id'  => null,
                'raw'     => $raw
            ];
        }

        // Case C: Unexpected type (neither array nor string)
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
