<?php

namespace SWN_Deluxe;

class Seeder {
    public static function seed_sample_data() {
        $wheel_id = Wheels::insert([
            'name'         => 'demo_wheel',
            'display_name' => 'Demo Prize Wheel',
            'slug'         => 'demo-wheel',
            'status'       => 'active',
            'settings'     => maybe_serialize([
                // 'max_spins_per_user' => 3,
                // 'cooldown_hours'     => 24,
            ]),
        ]);

        $items = [
            [
                'wheel_id'      => $wheel_id,
                'name'          => 'discount_10',
                'display_name'  => '10% Off Coupon',
                'type'          => 'coupon',
                'value'         => '10',
                'probability'   => 0.25,
                'segment_color' => '#7a33ff',
                'sort_order'    => 1,
            ],
                        [
                'wheel_id'      => $wheel_id,
                'name'          => 'discount_20',
                'display_name'  => '20% Off Coupon',
                'type'          => 'coupon',
                'value'         => '20',
                'probability'   => 0.25,
                'segment_color' => '#33ffad',
                'sort_order'    => 2,
            ],
                        [
                'wheel_id'      => $wheel_id,
                'name'          => 'discount_10',
                'display_name'  => '30% Off Coupon',
                'type'          => 'coupon',
                'value'         => '30',
                'probability'   => 0.25,
                'segment_color' => '#FF5733',
                'sort_order'    => 3,
            ],
                        [
                'wheel_id'      => $wheel_id,
                'name'          => 'discount_10',
                'display_name'  => '40% Off Coupon',
                'type'          => 'coupon',
                'value'         => '40',
                'probability'   => 0.25,
                'segment_color' => '#3374ff',
                'sort_order'    => 4,
            ],
            // add others here...
        ];

        foreach ($items as $item) {
            Wheel_Items::insert($item);
        }
    }
}
