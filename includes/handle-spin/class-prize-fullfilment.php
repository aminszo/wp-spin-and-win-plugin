<?php

namespace SWN_Deluxe;

class Prize_Fulfillment {
    public function award( $prize, int $wheel_id, int $user_id ) {
        // Example: If coupon
        if ( $prize->type === 'coupon' ) {
            SWN_Coupon_Code::create_for_user( $user_id, $prize->value );
        }
        // If SMS
        if ( $prize->type === 'sms' ) {
            SWN_SMS::send_to_user( $user_id, $prize->message );
        }
        return true;
    }
}
