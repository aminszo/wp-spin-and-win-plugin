<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


class Prize_Fulfillment
{
    public static function award($prize, int $wheel_id, int $user_id)
    {
        $result['details'] = '';
        switch ($prize->type) {
            case 'free-product':
                $result = self::give_free_product($prize, $user_id);
                break;

            case 'credit':
                $result = self::give_credit($prize, $user_id);
                break;

            case 'coupon':
                $result = self::give_discount_coupon($prize,  $user_id);
                break;


            case 'nothing':
            default:
                $result['details'] = __('Better luck next time!', 'swn-deluxe');
                break;
        }
        return $result;
    }

    public static function give_free_product($prize, $user_id)
    {
        $result = [];

        $options = json_decode($prize->options, true) ?: [];
        // Example: Integrate with WooCommerce to give a coupon
        if (class_exists('WooCommerce')) {
            $coupon_code_x_items = sanitize_text_field($options['count']); // 'value' stores the coupon code free products  number
            $coupon_code = Coupon_Code::generate_coupon_for_specific_product_category(
                $options['product_category'],
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

        return $result;
    }

    public static function give_credit($prize,  $user_id)
    {
        $result = [];
        $options = json_decode($prize->options, true) ?: [];
        $credit_amount = intval($options['credit_amount']);
        // Integrate with your custom credit system

        if (true) {

            \ACBD\Transaction::manualIncrease($user_id, $credit_amount, "added by spin and win plugin");

            $result['details'] = sprintf(__('%d credits added to your account.', 'swn-deluxe'), $credit_amount);

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

        return $result;
    }

    public static function give_discount_coupon($prize,  $user_id)
    {
        $result = [];
        $options = json_decode($prize->options, true) ?: [];

        if (class_exists('WooCommerce')) {
            $coupon_code_percent = $options['percent'];
            $coupon_code_maximum_amount = $options['maximum_amount'];
            $coupon_code_expiry_days = $options['expiry_days'];
            $product_category = $options['product_category'];
            $coupon_code_x_items = sanitize_text_field($options['count']);
            $first_order_only = $options['first_order'];

            $coupon_code = Coupon_Code::generate_coupon(
                $coupon_code_percent,
                'percent',
                "Congratulations! You won $coupon_code_percent off!",
                $coupon_code_expiry_days,
                $coupon_code_maximum_amount,
                $product_category,
                $coupon_code_x_items,
                $first_order_only
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

            $sms_variables = [
                'coupon-code' => $coupon_code,
                'expiry-days' => $options['expiry_days'],
            ];

            $result['sms'] = [
                'variables' => $sms_variables,
                'pattern_code' => 'ijktl0xeolpxdai',
            ];
        } else {
            $result['details'] = __('Coupon prize (WooCommerce not active).', 'swn-deluxe');
        }

        return $result;
    }
}
