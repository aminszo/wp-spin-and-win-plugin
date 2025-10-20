<?php

namespace SWN_Deluxe\Handle_Spin;

use SWN_Deluxe\Spin_Chance;
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
        $remaining_spin_chances = Spin_Chance::remaining($wheel_id, $user_id, null);
        if ($remaining_spin_chances <= 0) {
            return [
                'success' => false,
                'data' => ['message' => __('You have no spins remaining for this wheel.', 'swn-deluxe')]
            ];
        }

        // All validations passed; the user is allowed to spin
        return ['success' => true];
    }
}
