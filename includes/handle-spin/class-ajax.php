<?php

namespace SWN_Deluxe\Handle_Spin;

defined( 'ABSPATH' ) || exit;

/**
 * Class AJAX
 *
 * Handles AJAX requests for spinning the prize wheel.
 *
 * @package SWN_Deluxe\Handle_Spin
 */
class AJAX {

    /**
     * Initialize AJAX hooks.
     *
     * Should be called once on plugin init.
     *
     * @return void
     */
    public static function init() {
        add_action( 'wp_ajax_swn_spin_wheel', [ self::class, 'spin_wheel' ] );
        add_action( 'wp_ajax_nopriv_swn_spin_wheel', [ self::class, 'spin_wheel' ] );
    }

    /**
     * Handle AJAX request to spin a wheel.
     *
     * This validates the nonce, creates a Spin_Handler instance,
     * processes the spin, and returns the result in JSON format.
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
    public static function spin_wheel() {
        // Verify nonce
        if ( ! isset( $_POST['security'] ) || ! check_ajax_referer( 'swn_spin_nonce', 'security', false ) ) {
            wp_send_json( [
                'success' => false,
                'data'    => __( 'Invalid security token.', 'swn-deluxe' ),
            ] );
        }

        $wheel_id = intval( $_POST['wheel_id'] ?? 0 );

        if ( $wheel_id <= 0 ) {
            wp_send_json( [
                'success' => false,
                'data'    => __( 'Invalid wheel ID.', 'swn-deluxe' ),
            ] );
        }

        // $result = [
        //     'success' => true,
        //     'message' => __( 'You won a prize!', 'swn-deluxe' ),
        //     'wheel_id' => $wheel_id,
        //     'user_id' => get_current_user_id()
        // ];

        //sample process spin 
        $result = \SWN_Deluxe\Handle_Spin\Spin_Handler::test();


        // Process spin
        // $handler = new Spin_Handler();
        // $result  = $handler->process_spin( $wheel_id, get_current_user_id() );

        wp_send_json( $result );
    }
}
