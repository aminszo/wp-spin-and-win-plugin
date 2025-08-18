<?php

namespace SWN_Deluxe;

class Prize_Selector {
    public function select_random_prize( int $wheel_id ) {
        $items = Wheel_Items::get_active_items( $wheel_id );

        if ( empty( $items ) ) {
            return null;
        }

        // Weighted random based on probability
        $pool = [];
        foreach ( $items as $item ) {
            for ( $i = 0; $i < $item->weight; $i++ ) {
                $pool[] = $item;
            }
        }

        return $pool[ array_rand( $pool ) ];
    }
}
