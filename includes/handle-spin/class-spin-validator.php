<?php

namespace SWN_Deluxe\Handle_Spin;

use \SWN_Deluxe\Wheels;

defined('ABSPATH') || exit;


class Spin_Validator
{
    public function validate($wheel_id, int $user_id): array
    {

        // Check if wheel_id is provided and valid
        if ($wheel_id === null) {
            return [
                'success' => false,
                'data' => ['message' => __('Missing or invalid wheel ID.', 'swn-deluxe')]
            ];
        }

        // Check if the wheel with the specified id exists
        $wheel = Wheels::get($wheel_id);
        if (! $wheel) {
            return [
                'success' => false,
                'data' => ['message' => __('The requested wheel does not exist.', 'swn-deluxe')]
            ];
        }

        // Check if the user is logged in
        if (! $user_id) {
            return [
                'success' => false,
                'data' => ['message' => __('You must be logged in to spin.', 'swn-deluxe')]
            ];
        }

        // Check if the user has any remaining spin chances for this wheel
        // This logic needs to be implemented

        // All validations passed; the user is allowed to spin
        return ['success' => true];
    }
}
