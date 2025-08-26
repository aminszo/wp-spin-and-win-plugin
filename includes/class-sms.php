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
        $plugin_settings = get_option('swn_deluxe_settings', []);

        // Load credentials from plugin settings
        self::$username = $plugin_settings['sms_api_username'];
        self::$password = $plugin_settings['sms_api_password'];
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
        $response = self::normalize_response($response);

        $status = $response['success'] ? 'success' : 'failed';

        return self::log_sms($user_id, $phone_number, $message, $status, $response['raw']);
    }

    /**
     * Send SMS with pattern code
     */
    public static function send_with_pattern($phone_number, $pattern_code, $variables, $user_id = null)
    {
        if (!self::validate_phone($phone_number)) {
            return self::log_sms($user_id, $phone_number, "Pattern: {$pattern_code}", 'failed', 'Invalid phone number');
        }

        $from = self::$default_pattern_sender_number;

        $url = "https://ippanel.com/patterns/pattern?username=" . urlencode(self::$username)
            . "&password=" . urlencode(self::$password)
            . "&from={$from}&to=" . urlencode(json_encode([$phone_number]))
            . "&input_data=" . urlencode(json_encode($variables))
            . "&pattern_code=" . urlencode($pattern_code);

        $response = self::do_curl_request($url, $variables, false);
        $response = self::normalize_response($response);

        $status = $response['success'] ? 'success' : 'failed';

        return self::log_sms($user_id, $phone_number, "Pattern: {$pattern_code}",  $status, $response['raw']);
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
     * Normalize Iranian phone numbers
     */
    public static function normalize_phone($phone)
    {
        $phone = preg_replace('/\D/', '', $phone); // remove non-digits

        // Already without 0 or country code, like 919...
        if (preg_match('/^\d{10}$/', $phone)) {
            return $phone;
        }

        // Starts with 0, like 0919...
        if (preg_match('/^0\d{10}$/', $phone)) {
            return substr($phone, 1);
        }

        // starts with country code (98XXXXXXXXXX)
        if (preg_match('/^98\d{10}$/', $phone)) {
            return substr($phone, 2);
        }

        // Starts with plus sign followed by country code, like +98XXXXXXXXXX
        if (preg_match('/^\+98\d{10}$/', $phone)) {
            return substr($phone, 3);
        }

        // Starts with 00 followed by country code, like 0098XXXXXXXXXX
        if (preg_match('/^0098\d{10}$/', $phone)) {
            return substr($phone, 4);
        }

        return false; // unsupported format
    }


    /**
     * Validate phone number (basic Iran format)
     */
    public static function validate_phone($phone)
    {
        // must be 9XXXXXXXXX (10 chars total)
        return preg_match('/^9\d{9}$/', $phone);
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
            // cURL-level error (network, timeout, etc.)
            $response = [
                'success' => false,
                'code'    => 102, // Curl error code
                'message' => 'cURL error: ' . curl_error($ch),
                'sms_id'  => null
            ];
        } else {
            if ($decode_response) {
                $decoded = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $response = $decoded;
                } else {
                    // Invalid JSON from provider
                    $response = [
                        'success' => false,
                        'code'    => 101, // parsing error
                        'message' => 'Invalid response, unable to parse response.',
                        'sms_id'  => null,
                        'raw'     => $response
                    ];
                }
            } // else: leave response as raw string; it will be normalized later

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
     *     'code'    => int,           // 0 for success, 100 for unknown provider error, 101 for parsing error / invalid response
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

        // Already normalized (e.g., cURL or JSON parse error)
        if (is_array($raw) && isset($raw['success'])) {
            return $raw;
        }

        // Default fallback response for invalid or unexpected input.
        $response = [
            'success' => false,
            'code'    => 101, // default = parsing error / invalid response
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
                'code'    => $errorCode ?? 100, // if missing, fallback to unknown provider error
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
                'code'    => 100, // unknown provider error
                'message' => $trimmed ?: 'Unknown provider error',
                'sms_id'  => null,
                'raw'     => $raw
            ];
        }

        // Case C: Unexpected type (neither array nor string)
        return $response;
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
