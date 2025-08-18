<?php

namespace SWN_Deluxe\Handle_Spin;

use SWN_Deluxe\Wheel_Items;

class Prize_Selector
{
    public function select_random_prize(int $wheel_id)
    {
        $items = Wheel_Items::get_by_wheel($wheel_id);

        if (empty($items)) {
            return null;
        }

        // Weighted random based on probability
        $pool = [];
        foreach ($items as $item) {
            for ($i = 0; $i < $item->probability; $i++) {
                $pool[] = $item;
            }
        }

        return $pool[array_rand($pool)];
    }
}
