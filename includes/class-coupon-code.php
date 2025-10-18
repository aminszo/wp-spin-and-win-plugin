<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


class Coupon_Code
{

    /**
     * Generates a WooCommerce coupon and "assigns" it to a specific user.
     *
     * @param int    $user_id       The ID of the user to assign the coupon to.
     * @param float  $amount        The discount amount (e.g., 10 for 10% or $10).
     * @param string $discount_type The type of discount ('percent', 'fixed_cart', 'fixed_product').
     * @param string $description   (Optional) A description for the coupon.
     * @param int    $expiry_days   (Optional) Days until the coupon expires from now.
     *
     * @return string|bool The generated coupon code on success, or false on failure.
     */
    public static function generate_coupon($amount, $discount_type = 'percent', $description = '', $expiry_days = 7, $maximum_amount = 0, $category_slug = null, $limit_usage_to_x_items = 0, $for_first_order_only = false)
    {

        // Ensure WooCommerce is active and required classes are available.
        if (! class_exists('\WC_Coupon')) {
            return false;
            // log an error 
        }

        // Generate a unique coupon code
        // You can customize this generation logic.
        // woocommerce_coupon_code_generator_characters is a filter used by WC.
        $characters = apply_filters('woocommerce_coupon_code_generator_characters', 'ABCDEFGHJKMNPQRSTUVWXYZ23456789');
        $length = apply_filters('woocommerce_coupon_code_generator_character_length', 6);
        $coupon_code = substr(str_shuffle($characters), 0, $length);

        // Add a prefix to make it more identifiable for your plugin
        $coupon_code = "MC" . $amount . "OFF-" . strtoupper($coupon_code);

        // Check if the coupon code already exists (unlikely with random generation, but good practice)
        if (wc_get_coupon_id_by_code($coupon_code)) {
            // If it exists, try generating a new one (or handle as an error)
            return self::generate_coupon($amount, $discount_type, $description, $expiry_days);
        }

        $coupon = new \WC_Coupon();

        // Set coupon properties
        $coupon->set_code($coupon_code);
        $coupon->set_amount($amount);
        $coupon->set_discount_type($discount_type);
        $coupon->set_description($description ?: 'Coupon won from Spin & Win game.');

        // if ($maximum_amount > 0) {
        //     $coupon->set_maximum_amount($maximum_amount);
        // }
        // Set expiry date
        if ($expiry_days > 0) {
            $expiry_date = new \DateTime();
            $expiry_date->modify("+{$expiry_days} days");
            $coupon->set_date_expires($expiry_date->format('Y-m-d H:i:s'));
        }

        // Set usage limits
        $coupon->set_usage_limit(1); // Can only be used once in total
        $coupon->set_usage_limit_per_user(1); // Each user can only use it once

        if ($limit_usage_to_x_items > 0) {
            $coupon->set_limit_usage_to_x_items($limit_usage_to_x_items);
        }

        if ($category_slug && !empty(trim($category_slug))) {
            $coupon->set_product_categories(array(get_term_by('slug', $category_slug, 'product_cat')->term_id));
        }


        // Save the coupon
        $coupon_id = $coupon->save();

        if ($coupon_id && $maximum_amount > 0) {
            update_post_meta($coupon_id, 'max_discount_amount', $maximum_amount);
        }

        if ($coupon_id && true === $for_first_order_only) {
            update_post_meta($coupon_id, 'is_first_order_only', 'yes');
        }

        if ($coupon_id) {
            return $coupon_code;
        }

        return false;
    }

    public static function generate_coupon_for_specific_product_category($category_slug, $x_items, $description = '', $expiry_days = 0)
    {

        // Ensure WooCommerce is active and required classes are available.
        if (! class_exists('\WC_Coupon')) {
            return false;
            // log an error
        }

        // Generate a unique coupon code
        // You can customize this generation logic.
        // woocommerce_coupon_code_generator_characters is a filter used by WC.
        $characters = apply_filters('woocommerce_coupon_code_generator_characters', 'ABCDEFGHJKMNPQRSTUVWXYZ23456789');
        $length = apply_filters('woocommerce_coupon_code_generator_character_length', 6);
        $coupon_code = substr(str_shuffle($characters), 0, $length);

        // Add a prefix to make it more identifiable for your plugin
        $coupon_code = 'MC' . $x_items . 'S-' . strtoupper($coupon_code);

        // Check if the coupon code already exists (unlikely with random generation, but good practice)
        if (wc_get_coupon_id_by_code($coupon_code)) {
            // If it exists, try generating a new one (or handle as an error)
            return generate_coupon_for_specific_product_category($category_slug, $x_items, $description, $expiry_days);
        }

        $coupon = new \WC_Coupon();

        // Set coupon properties
        $coupon->set_code($coupon_code);
        $coupon->set_amount(100);
        $coupon->set_discount_type('percent');
        $coupon->set_description($description ?: 'Coupon won from Spin & Win game.');

        // Set expiry date
        if ($expiry_days > 0) {
            $expiry_date = new \DateTime();
            $expiry_date->modify("+{$expiry_days} days");
            $coupon->set_date_expires($expiry_date->format('Y-m-d H:i:s'));
        }

        // Set usage limits
        $coupon->set_usage_limit(1); // Can only be used once in total
        $coupon->set_usage_limit_per_user(1); // Each user can only use it once
        $coupon->set_limit_usage_to_x_items($x_items);
        $coupon->set_product_categories(array(get_term_by('slug', $category_slug, 'product_cat')->term_id));


        // Save the coupon
        $coupon_id = $coupon->save();

        if ($coupon_id) {
            return $coupon_code;
        }

        return false;
    }
}
