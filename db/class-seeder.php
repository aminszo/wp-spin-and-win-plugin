<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


class Seeder
{
    public static function seed_sample_data()
    {
        $wheel_id = Wheels::insert([
            'name'         => 'demo_wheel',
            'display_name' => 'Demo Prize Wheel',
            'slug'         => 'demo-wheel',
            'status'       => 'active',
            'settings'     => json_encode([
                'new_user_chances' => 1
            ]),
        ]);

        $items = [
            [
                'wheel_id'      => $wheel_id,
                'name'          => 'Discount 10',
                'display_name'  => '10% Discount',
                'type'          => 'coupon',
                'probability'   => 30,
                'segment_color' => '#7a33ff',
                'sort_order'    => 1,
                'options'       => json_encode([
                    'percent' => 10
                ]),
            ],
            [
                'wheel_id'      => $wheel_id,
                'name'          => 'Discount 20',
                'display_name'  => '20% Discount',
                'type'          => 'coupon',
                'probability'   => 20,
                'segment_color' => '#33ffad',
                'sort_order'    => 2,
                'options'       => json_encode([
                    'percent' => 20
                ]),
            ],
            [
                'wheel_id'      => $wheel_id,
                'name'          => 'Free sample box',
                'display_name'  => '3 Free coffe sample',
                'type'          => 'free-product',
                'probability'   => 10,
                'segment_color' => '#FF5733',
                'sort_order'    => 3,
                'options'       => json_encode([
                    'count' => 3,
                    'product_category' => 'sample-box'
                ]),
            ],
            [
                'wheel_id'      => $wheel_id,
                'name'          => 'Credit 200',
                'display_name'  => '200000 credit',
                'type'          => 'credit',
                'probability'   => 15,
                'segment_color' => '#3374ff',
                'sort_order'    => 4,
                'options'       => json_encode([
                    'credit_amount' => 200000
                ]),
            ],
            // add others here...
        ];

        foreach ($items as $item) {
            Wheel_Items::insert($item);
        }
    }
}
