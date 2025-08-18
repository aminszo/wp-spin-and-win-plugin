<?php

namespace SWN_Deluxe;

class Spin_Validator {
    public function validate( int $wheel_id, int $user_id ): array {
        if ( ! $wheel_id || ! Wheels::exists( $wheel_id ) ) {
            return [ 'success' => false, 'message' => __( 'Invalid wheel.', 'swn-deluxe' ) ];
        }

        $wheel = Wheels::get( $wheel_id );
        if ( $wheel->status !== 'active' ) {
            return [ 'success' => false, 'message' => __( 'Wheel is not active.', 'swn-deluxe' ) ];
        }

        // Example: Check spin limit per user
        if ( Spin_History::user_has_spun_today( $wheel_id, $user_id ) ) {
            return [ 'success' => false, 'message' => __( 'You have already spun today.', 'swn-deluxe' ) ];
        }

        return [ 'success' => true ];
    }
}
