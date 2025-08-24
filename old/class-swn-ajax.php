<?php
class SWN_Ajax
{
    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        // add_action('wp_ajax_swn_spin_wheel', array($this, 'handle_spin_wheel'));
        add_action('wp_ajax_swn_spin_wheel', array($this, 'ajax_test_response'));
        // No wp_ajax_nopriv_ for this action as only logged-in users can spin
    }

    public function ajax_test_response()
    {
        wp_send_json_success(array(
            'message'          => "hi", //sprintf(__('Congratulations! You won: %s', 'swn-deluxe'), $winning_prize['name']),
            'prize_name'       => 'prizeA',
            'prize_details'    => 'A Detail',
            'stop_at_segment'  => 1, // Winwheel segments are 1-indexed for stopAngle
            'remaining_spins'  => 1,
        ));
    }

    public function handle_spin_wheel()
    {
        check_ajax_referer('swn_spin_nonce', 'nonce');

        if (! is_user_logged_in()) {
            wp_send_json_error(array('message' => __('You must be logged in to spin.', 'swn-deluxe')));
        }

        $user_id = get_current_user_id();
        $spin_chances = SWN_User::get_spin_chances($user_id);

        if ($spin_chances <= 0) {
            wp_send_json_error(array('message' => __('You have no spins left.', 'swn-deluxe'), 'remaining_spins' => 0));
        }

        // Decrement spin chance
        SWN_User::decrement_spin_chances($user_id);

        // Determine the winning segment based on probabilities
        $prizes = get_option('swn_prizes_settings', array());
        if (empty($prizes)) {
            // Restore default if admin deleted all prizes for some reason
            $default_prizes = array(
                array('name' => '10% Discount', 'type' => 'coupon', 'value' => 'SPIN10', 'probability' => 30, 'segment_color' => '#FFD700', 'id' => 'prize1'),
                array('name' => '5 Credits', 'type' => 'credit', 'value' => '5', 'probability' => 20, 'segment_color' => '#C0C0C0', 'id' => 'prize2'),
                array('name' => 'Try Again', 'type' => 'nothing', 'value' => '', 'probability' => 50, 'segment_color' => '#808080', 'id' => 'prize3'),
            );
            update_option('swn_prizes_settings', $default_prizes);
            $prizes = $default_prizes;
        }


        $winning_prize = $this->calculate_winning_prize($prizes);
        $winning_segment_index = $winning_prize['determined_index']; // The actual index in the $prizes array

        // Award the prize

        $prize_result = $this->award_prize($user_id, $winning_prize);
        $prize_awarded_details = $prize_result['details'];
        // $prize_sms_text = $prize_result['text'];
        $prize_sms = $prize_result['sms'];
        $sms_patern_code = $prize_sms['pattern_code'];
        $sms_variables = $prize_sms['variables'];

        // Log the spin
        SWN_DB::add_spin_log($user_id, $winning_prize['name'], $prize_awarded_details);

        SWN_SMS::send_pattern_sms($user_id, $sms_patern_code, $sms_variables);

        wp_send_json_success(array(
            'message'          => "hi", //sprintf(__('Congratulations! You won: %s', 'swn-deluxe'), $winning_prize['name']),
            'prize_name'       => $winning_prize['name'],
            'prize_details'    => $prize_awarded_details,
            'stop_at_segment'  => $winning_segment_index + 1, // Winwheel segments are 1-indexed for stopAngle
            'remaining_spins'  => SWN_User::get_spin_chances($user_id),
        ));
    }

    private function calculate_winning_prize($prizes)
    {
        $weighted_list = array();
        $total_probability = 0;
        foreach ($prizes as $index => $prize) {
            $total_probability += (int)$prize['probability'];
        }

        // If total probability is not 100, adjust or handle error
        // For simplicity, we'll proceed, but ideally, it should be validated in admin.

        $rand = mt_rand(1, $total_probability);
        $current_probability_sum = 0;

        foreach ($prizes as $index => $prize) {
            $current_probability_sum += (int)$prize['probability'];
            if ($rand <= $current_probability_sum) {
                $prize['determined_index'] = $index; // Keep track of original index
                return $prize;
            }
        }
        // Fallback (should ideally not be reached if probabilities are set correctly)
        $fallback_index = array_rand($prizes);
        $prizes[$fallback_index]['determined_index'] = $fallback_index;
        return $prizes[$fallback_index];
    }

    private function award_prize($user_id, $prize)
    {
        $result['details'] = '';
        switch ($prize['type']) {
            case 'product':
                // Example: Integrate with WooCommerce to give a coupon
                if (class_exists('WooCommerce')) {
                    $coupon_code_x_items = sanitize_text_field($prize['value']); // 'value' stores the coupon code percent
                    $coupon_code = generate_coupon_for_specific_product_category(
                        'coffee-sample',
                        $coupon_code_x_items,
                        "Congratulations! You won $coupon_code_x_items free sample box!",
                        0 // never expires
                    );

                    if ($coupon_code) {
                        // Successfully generated and "assigned"
                        error_log("Generated coupon for user {$user_id}: {$coupon_code}");
                        // You can now display this coupon code to the user in your plugin's interface.
                        // Or send them an email.
                    } else {
                        error_log("Failed to generate coupon for user {$user_id}.");
                    }
                    $result['details'] = sprintf(__('Coupon Code: %s <br> Use this to order free sample boxes.', 'swn-deluxe'), $coupon_code);


                    $replacements = [
                        '%sample-count%' => $coupon_code_x_items,
                        '%coupon-code%' => $coupon_code,
                    ];

                    $finalText = str_replace(array_keys($replacements), array_values($replacements), $text_1);

                    // $result['text'] = $finalText;

                    $sms_variables = [
                        'sample-count' => $coupon_code_x_items,
                        'coupon-code' => $coupon_code,
                    ];

                    $result['sms'] = [
                        'variables' => $sms_variables,
                        'pattern_code' => 'ku3p0fp9c0s9mtg',
                    ];
                } else {
                    $result['details'] = __('Coupon prize (WooCommerce not active).', 'swn-deluxe');
                }
                break;

            case 'credit':
                $credit_amount = intval($prize['value']);
                // Integrate with your custom credit system
                // Example: update_user_meta($user_id, 'user_credits', get_user_meta($user_id, 'user_credits', true) + $credit_amount);
                // For this example, we'll just log it
                if (true) {
                    // $credit = get_user_meta($user_id, 'unused_credit', true);
                    // $credit = $credit ? $credit : 0;
                    // $total_credit = $credit + $credit_amount;
                    // update_user_meta($user_id, 'unused_credit', $total_credit);

                    \ACBD\Transaction::manualIncrease($user_id, $credit_amount, "added by spin and win plugin");
                    // my_custom_credit_system_add_credits($user_id, $credit_amount);
                    $result['details'] = sprintf(__('%d credits added to your account.', 'swn-deluxe'), $credit_amount);


                    $replacements = [
                        '%credit%' => $credit_amount,
                    ];

                    $finalText = str_replace(array_keys($replacements), array_values($replacements), $text_2);

                    // $result['text'] = $finalText;

                    $sms_variables = [
                        'credit' => $credit_amount,
                    ];

                    $result['sms'] = [
                        'variables' => $sms_variables,
                        'pattern_code' => 'pr1fjoya8qrw8nl',
                    ];
                } else {
                    $result['details'] = sprintf(__('%d credits (manual processing needed).', 'swn-deluxe'), $credit_amount);
                }
                break;

            case 'coupon':
                // Example: Integrate with WooCommerce to give a coupon
                if (class_exists('WooCommerce')) {
                    $coupon_code_percent = sanitize_text_field($prize['value']); // 'value' stores the coupon code percent
                    $coupon_code = generate_and_assign_spin_win_coupon(
                        $user_id,
                        $coupon_code_percent,
                        'percent',
                        "Congratulations! You won $coupon_code_percent off!",
                        0 // never expires
                    );

                    if ($coupon_code) {
                        // Successfully generated and "assigned"
                        error_log("Generated coupon for user {$user_id}: {$coupon_code}");
                        // You can now display this coupon code to the user in your plugin's interface.
                        // Or send them an email.
                    } else {
                        error_log("Failed to generate coupon for user {$user_id}.");
                    }
                    $result['details'] = sprintf(__('Coupon Code: %s', 'swn-deluxe'), $coupon_code);


                    $replacements = [
                        '%percent%' => $coupon_code_percent,
                        '%coupon-code%' => $coupon_code,
                    ];

                    $finalText = str_replace(array_keys($replacements), array_values($replacements), $text_3);

                    // $result['text'] = $finalText;

                    $sms_variables = [
                        'coupon-code' => $coupon_code,
                    ];

                    $result['sms'] = [
                        'variables' => $sms_variables,
                        'pattern_code' => 'ijktl0xeolpxdai',
                    ];
                } else {
                    $result['details'] = __('Coupon prize (WooCommerce not active).', 'swn-deluxe');
                }
                break;


            case 'nothing':
            default:
                $result['details'] = __('Better luck next time!', 'swn-deluxe');
                break;
        }
        return $result;
    }
}
