<?php

namespace SWN_Deluxe\Handle_Spin;

use SWN_Deluxe\Prize_Fulfillment;
use SWN_Deluxe\Spin_Chance;
use SWN_Deluxe\Spin_Log;
use SWN_Deluxe\SMS;

defined('ABSPATH') || exit;


class Spin_Handler
{

    public function process_spin($wheel_id, int $user_id)
    {
        $validator = new Spin_Validator();

        $validation = $validator->validate($wheel_id, $user_id);
        if (! $validation['success']) {
            return $validation; // Return error response
        }

        $selector = new Prize_Selector();
        $prize    = $selector->select_random_prize($wheel_id);

        if (! $prize) {

            Spin_Log::add($wheel_id, 0, $user_id, null, null, __('No prizes available.', 'swn-deluxe'));

            return [
                'success' => false,
                'data' => ['message' => __('No prizes available.', 'swn-deluxe')]
            ];
        }

        $result = Prize_Fulfillment::award($prize, $wheel_id, $user_id);
        $prize_awarded_details = $result['details'];

        Spin_Log::add($wheel_id, $prize->id, $user_id);

        $sms_patern_code = $result['sms']['pattern_code'];
        $sms_variables = $result['sms']['variables'];

        if ($sms_patern_code && $sms_variables) {
            // send sms here
        }

        Spin_Chance::decrement($wheel_id, $user_id, null);

        return [
            'success' => true,
            'data' => [
                'message' => __('you won a prize', 'swn-deluxe'),
                'prize' => $prize,
                'prize_details' => $prize_awarded_details,
                'remaining_spins' => Spin_Chance::remaining($wheel_id, $user_id, null)
            ]
        ];
    }
}
