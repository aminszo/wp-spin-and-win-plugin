<?php

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
function generate_and_assign_spin_win_coupon($user_id, $amount, $discount_type = 'percent', $description = '', $expiry_days = 30)
{

    // Ensure WooCommerce is active and required classes are available.
    if (! class_exists('WC_Coupon')) {
        return false;
    }

    // Generate a unique coupon code
    // You can customize this generation logic.
    // woocommerce_coupon_code_generator_characters is a filter used by WC.
    $characters = apply_filters('woocommerce_coupon_code_generator_characters', 'ABCDEFGHJKMNPQRSTUVWXYZ23456789');
    $length = apply_filters('woocommerce_coupon_code_generator_character_length', 6);
    $coupon_code = substr(str_shuffle($characters), 0, $length);

    // Add a prefix to make it more identifiable for your plugin
    $coupon_code = "MC".$amount."OFF-" . strtoupper($coupon_code);

    // Check if the coupon code already exists (unlikely with random generation, but good practice)
    if (wc_get_coupon_id_by_code($coupon_code)) {
        // If it exists, try generating a new one (or handle as an error)
        return generate_and_assign_spin_win_coupon($user_id, $amount, $discount_type, $description, $expiry_days);
    }

    $coupon = new WC_Coupon();

    // Set coupon properties
    $coupon->set_code($coupon_code);
    $coupon->set_amount($amount);
    $coupon->set_discount_type($discount_type);
    $coupon->set_description($description ?: 'Coupon won from Spin & Win game.');

    // Set expiry date
    if ($expiry_days > 0) {
        $expiry_date = new DateTime();
        $expiry_date->modify("+{$expiry_days} days");
        $coupon->set_date_expires($expiry_date->format('Y-m-d H:i:s'));
    }

    // Set usage limits
    $coupon->set_usage_limit(1); // Can only be used once in total
    $coupon->set_usage_limit_per_user(1); // Each user can only use it once

    // Restrict to the specific user (by user ID) - this makes it "assigned"
    // Note: This feature is not directly supported by WC_Coupon class itself.
    // Instead, you'd apply this restriction by setting the 'customer_ids' meta.
    // You'll need to get the current customer's ID.
    // $customer_ids = array( $user_id );
    // $coupon->set_customer_ids( $customer_ids );


    // Save the coupon
    $coupon_id = $coupon->save();

    if ($coupon_id) {
        // Store the coupon code in user meta for easy retrieval/display
        // You might want to store an array of coupons if a user can win multiple.
        // $user_coupons = get_user_meta( $user_id, '_spin_win_coupons', true );
        // if ( ! is_array( $user_coupons ) ) {
        //     $user_coupons = array();
        // }
        // $user_coupons[] = array(
        //     'code' => $coupon_code,
        //     'id'   => $coupon_id,
        //     'amount' => $amount,
        //     'discount_type' => $discount_type,
        //     'expires' => $coupon->get_date_expires() ? $coupon->get_date_expires()->format( 'Y-m-d H:i:s' ) : 'Never',
        //     'status' => 'active' // You can update this to 'used' or 'expired' later
        // );
        // update_user_meta( $user_id, '_spin_win_coupons', $user_coupons );

        return $coupon_code;
    }

    return false;
}

function generate_coupon_for_specific_product_category($category_slug, $x_items, $description = '', $expiry_days = 30)
{

    // Ensure WooCommerce is active and required classes are available.
    if (! class_exists('WC_Coupon')) {
        return false;
    }

    // Generate a unique coupon code
    // You can customize this generation logic.
    // woocommerce_coupon_code_generator_characters is a filter used by WC.
    $characters = apply_filters('woocommerce_coupon_code_generator_characters', 'ABCDEFGHJKMNPQRSTUVWXYZ23456789');
    $length = apply_filters('woocommerce_coupon_code_generator_character_length', 6);
    $coupon_code = substr(str_shuffle($characters), 0, $length);

    // Add a prefix to make it more identifiable for your plugin
    $coupon_code = 'MC'. $x_items.'S-' . strtoupper($coupon_code);

    // Check if the coupon code already exists (unlikely with random generation, but good practice)
    if (wc_get_coupon_id_by_code($coupon_code)) {
        // If it exists, try generating a new one (or handle as an error)
        return generate_coupon_for_specific_product_category($category_slug, $x_items, $description = '', $expiry_days = 30);
    }

    $coupon = new WC_Coupon();

    // Set coupon properties
    $coupon->set_code($coupon_code);
    $coupon->set_amount(100);
    $coupon->set_discount_type('percent');
    $coupon->set_description($description ?: 'Coupon won from Spin & Win game.');

    // Set expiry date
    if ($expiry_days > 0) {
        $expiry_date = new DateTime();
        $expiry_date->modify("+{$expiry_days} days");
        $coupon->set_date_expires($expiry_date->format('Y-m-d H:i:s'));
    }

    // Set usage limits
    $coupon->set_usage_limit(1); // Can only be used once in total
    $coupon->set_usage_limit_per_user(1); // Each user can only use it once
    $coupon->set_limit_usage_to_x_items($x_items); // Each user can only use it once
    $coupon->set_product_categories(array(get_term_by('slug', $category_slug, 'product_cat')->term_id));

    // 'individual_use', 'yes'); // Cannot be used with other coupons
    // 'product_categories', array(get_term_by('slug', $category_slug, 'product_cat')->term_id)); // Specific category
    // 'free_shipping', 'no'); // No free shipping
    // 'apply_before_tax', 'yes'); // Apply before tax
    // 'product_ids', ''); // No specific products


    // Save the coupon
    $coupon_id = $coupon->save();

    if ($coupon_id) {
        return $coupon_code;
    }

    return false;
}
