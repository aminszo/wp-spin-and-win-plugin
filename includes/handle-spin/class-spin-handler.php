<?php

namespace SWN_Deluxe\Handle_Spin;

use SWN_Deluxe\Prize_Fulfillment;
use SWN_Deluxe\Spin_Chance;
use SWN_Deluxe\Spin_Log;

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
            return [
                'success' => false,
                'data' => ['message' => __('No prizes available.', 'swn-deluxe')]
            ];
        }


        // $award   = Prize_Fulfillment::award($prize, $wheel_id, $user_id);

        Spin_Log::add($wheel_id, $prize->id, $user_id);

        Spin_Chance::decrement($wheel_id, $user_id, null);

        return [
            'success' => true,
            'data' => [
                'message' => __('you won a prize', 'swn-deluxe'),
                'prize' => $prize,
            ]
        ];
    }
}
