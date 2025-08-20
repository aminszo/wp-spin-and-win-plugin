<?php

namespace SWN_Deluxe\Handle_Spin;

defined('ABSPATH') || exit;

/**
 * Class AJAX
 *
 * Handles AJAX requests for spinning the prize wheel.
 *
 * @package SWN_Deluxe\Handle_Spin
 */
class AJAX
{

    /**
     * Initialize AJAX hooks.
     *
     * @return void
     */
    public static function init()
    {
        add_action('wp_ajax_swn_spin_wheel', [self::class, 'spin_wheel']);
        add_action('wp_ajax_nopriv_swn_spin_wheel', [self::class, 'spin_wheel']);
    }

    /**
     * Handle AJAX request to spin a wheel.
     *
     * This validates the nonce, creates a Spin_Handler instance to processes the spin,
     * and returns the result in JSON format.
     *
     * Expected POST parameters:
     * - security (string): nonce for verification.
     * - wheel_id (int): ID of the wheel to spin.
     *
     * Response JSON structure:
     * - success (bool)
     * - data (array|string) – details about the spin or error message
     *
     * @return void
     */
    public static function spin_wheel()
    {

        // Verify nonce
        if (! isset($_POST['security']) || ! check_ajax_referer('swn_spin_nonce', 'security', false)) {
            wp_send_json_error(['message' => __('Invalid security token.', 'swn-deluxe')]);
        }

        $wheel_id = (isset($_POST['wheel_id']) && is_numeric($_POST['wheel_id']))
            ? (int) $_POST['wheel_id']
            : null;

        // Process spin
        $handler = new Spin_Handler();
        $result  = $handler->process_spin($wheel_id, get_current_user_id());

        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['data']);
        }
    }
}
