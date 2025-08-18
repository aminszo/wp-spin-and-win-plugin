<?php

namespace SWN_Deluxe\Handle_Spin;

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


        return [
            'success' => true,
            'data' => [
                'message' => __('you won a prize', 'swn-deluxe'),
                'prize' => $prize,
            ]
        ];


        // $awarder = new Prize_Awarder();
        // $award   = $awarder->award($prize, $wheel_id, $user_id);

        // Spin_History::log($wheel_id, $user_id, $prize['id']);

        // return [
        //     'success' => true,
        //     'prize'   => $prize,
        //     'message' => __('You won a prize!', 'swn-deluxe')
        // ];
    }
}
