<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;

$editing_item = isset($_GET['item_id']) ? Wheel_Items::get(intval($_GET['item_id'])) : null;
$options = $editing_item ? json_decode($editing_item->options, true) : [];
// var_dump($options);
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $editing_item ? __('Edit Item', 'swn-deluxe') : __('Add New Item', 'swn-deluxe'); ?>
        <a href="<?php echo admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEEL_ITEMS_LIST_PAGE']) . '&wheel_id=' . $wheel_id ?>" class="page-title-action"><?php _e('Back to Items List', 'swn-deluxe'); ?></a>
    </h1>

    <h2>
        <?php
        if ($editing_item) {
            printf(
                __('Editing Item "%s" on Wheel "%s"', 'swn-deluxe'),
                esc_html($editing_item->name),
                esc_html($wheel->name)
            );
        } else {
            printf(
                __('Add New Item to Wheel "%s"', 'swn-deluxe'),
                esc_html($wheel->name)
            );
        }
        ?>
    </h2>

    <!-- Add/Edit Item Form -->
    <form method="post">
        <?php wp_nonce_field('swn_save_item_action', 'swn_save_item_nonce'); ?>
        <input type="hidden" name="item_id" value="<?php echo $editing_item ? esc_attr($editing_item->id) : ''; ?>">

        <table class="form-table">
            <tr>
                <th><label for="name"><?php _e('Name', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="text" name="name" id="name" value="<?php echo $editing_item ? esc_attr($editing_item->name) : ''; ?>" class="regular-text" required>
                    <p class="description"><?php _e('Internal identifier for the wheel item. Not shown to users, used only for management.', 'swn-deluxe'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="display_name"><?php _e('Display Name', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="text" name="display_name" id="display_name" value="<?php echo $editing_item ? esc_attr($editing_item->display_name) : ''; ?>" class="regular-text" required>
                    <p class="description"><?php _e('The label displayed on the wheel segment when users spin.', 'swn-deluxe'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="type"><?php _e('Type', 'swn-deluxe'); ?></label></th>
                <td>
                    <select name="type" id="type" required>
                        <option value=""><?php esc_html_e('Select a type', 'swn-deluxe'); ?></option>
                        <option value="coupon" <?php selected($editing_item ? $editing_item->type : '', 'coupon'); ?>>
                            <?php esc_html_e('Coupon', 'swn-deluxe'); ?>
                        </option>
                        <option value="credit" <?php selected($editing_item ? $editing_item->type : '', 'credit'); ?>>
                            <?php esc_html_e('Credit', 'swn-deluxe'); ?>
                        </option>
                        <option value="nothing" <?php selected($editing_item ? $editing_item->type : '', 'nothing'); ?>>
                            <?php esc_html_e('nothing', 'swn-deluxe'); ?>
                        </option>
                    </select>
                    <p class="description"><?php _e('The prize type (e.g., coupon, credit, free product). Different types have different options.', 'swn-deluxe'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="probability"><?php _e('Probability', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="number" name="probability" id="probability" min="0" value="<?php echo $editing_item ? esc_attr($editing_item->probability) : 0; ?>" class="small-text">
                    <p class="description"><?php _e('Relative weight for this prize. Higher numbers make the item more likely to be selected compared to others, but it is not a percentage.', 'swn-deluxe'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="segment_color"><?php _e('Segment Color', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="color" name="segment_color" id="segment_color" value="<?php echo $editing_item ? esc_attr($editing_item->segment_color) : '#ffffff'; ?>">
                    <p class="description"><?php _e('The color used to display this item’s segment on the wheel.', 'swn-deluxe'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="sort_order"><?php _e('Sort Order', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="number" name="sort_order" id="sort_order" min="0" value="<?php echo $editing_item ? esc_attr($editing_item->sort_order) : 0; ?>" class="small-text">
                    <p class="description"><?php _e('Determines the order of appearance for segments. Lower numbers appear earlier in rendering.', 'swn-deluxe'); ?></p>
                </td>
            </tr>

            <!-- Type Specific Options -->
            <tr class="field-coupon type-field">
                <th scope="row"><label for="percent"><?php _e('Discount Percent', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="number" min='0' max='100' name="percent" id="percent" value="<?php echo esc_attr($options['percent'] ?? ''); ?>" />
                    <p class="description"><?php _e('The discount percentage to apply when this item is selected as a prize.', 'swn-deluxe'); ?></p>
                </td>
            </tr>

            <tr class="field-coupon type-field">
                <th scope="row"><label for="maximum_amount"><?php _e('Maximum Amount', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="number" min='0' name="maximum_amount" id="maximum_amount" value="<?php echo esc_attr($options['maximum_amount'] ?? ''); ?>" />
                    <p class="description"><?php _e('The maximum amount allowed in cart with this coupon code to apply. 0 means unlimited', 'swn-deluxe'); ?></p>
                </td>
            </tr>

            <tr class="field-coupon type-field">
                <th scope="row"><label for="expiry_days"><?php _e('expiry days', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="number" min='0' name="expiry_days" id="expiry_days" value="<?php echo esc_attr($options['expiry_days'] ?? ''); ?>" />
                    <p class="description"><?php _e('The number of days the discount code will expire. A value of 0 means no expiration date.', 'swn-deluxe'); ?></p>
                </td>
            </tr>

            <tr class="field-coupon type-field">
                <th scope="row"><label for="first_order"><?php _e('first_order', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="checkbox" name="first_order" id="first_order" value="true" <?php checked(true, $options['first_order']) ?> />
                    <p class="description"><?php _e('Is this only limited to the user\'s first order?', 'swn-deluxe'); ?></p>
                </td>
            </tr>

            <tr class="field-coupon type-field">
                <th scope="row"><label for="individual_use"><?php _e('individual_use', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="checkbox" name="individual_use" id="individual_use" value="true" <?php checked(true, $options['individual_use']) ?> />
                    <p class="description"><?php _e('Is this only limited to individual_use?', 'swn-deluxe'); ?></p>
                </td>
            </tr>

            <tr class="field-free-product field-coupon type-field">
                <th scope="row"><label for="count"><?php _e('Product Count', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="number" name="count" id="count" value="<?php echo esc_attr($options['count'] ?? ''); ?>" />
                    <p class="description"><?php _e('Number of free products awarded when this prize is won.', 'swn-deluxe'); ?></p>
                </td>
            </tr>

            <tr class="field-free-product field-coupon type-field">
                <th scope="row"><label for="product_category"><?php _e('Product Category Slug', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="text" name="product_category" id="product_category" value="<?php echo esc_attr($options['product_category'] ?? ''); ?>" />
                    <p class="description"><?php _e('The Slug of WooCommerce product category from which the free product will be selected.', 'swn-deluxe'); ?></p>
                </td>
            </tr>

            <tr class="field-credit type-field">
                <th scope="row"><label for="credit_amount"><?php _e('Credit Amount', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="number" name="credit_amount" id="credit_amount" value="<?php echo esc_attr($options['credit_amount'] ?? ''); ?>" />
                    <p class="description"><?php _e('The amount of credit awarded to the user when this item is selected.', 'swn-deluxe'); ?></p>
                </td>
            </tr>


        </table>

        <hr>

        <table class="form-table">
            <h2><?php _e('display results settings') ?></h2>
            <tr>
                <th><label for="popup-title"><?php _e('popup-title', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="text" name="popup-title" id="popup-title" value="<?php echo $options['popup-title'] ?? ''; ?>" class="regular-text" required>
                    <p class="description"><?php _e('Internal identifier for the wheel item. Not shown to users, used only for management.', 'swn-deluxe'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="popup-sub-heading"><?php _e('popup-sub-heading', 'swn-deluxe'); ?></label></th>
                <td>
                    <input type="text" name="popup-sub-heading" id="popup-sub-heading" value="<?php echo $options['popup-sub-heading'] ?? ''; ?>" class="regular-text">
                    <p class="description"><?php _e('Internal identifier for the wheel item. Not shown to users, used only for management.', 'swn-deluxe'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="popup-description"><?php _e('popup-description', 'swn-deluxe'); ?></label></th>
                <td>
                    <textarea name="popup-description" id="popup-description" class="large-text" required><?php echo $options['popup-description'] ?? ''; ?></textarea>
                    <p class="description"><?php _e('Internal identifier for the wheel item. Not shown to users, used only for management.', 'swn-deluxe'); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button($editing_item ? __('Update Item', 'swn-deluxe') : __('Add Item', 'swn-deluxe'), 'primary', 'swn_save_item'); ?>
    </form>
</div>