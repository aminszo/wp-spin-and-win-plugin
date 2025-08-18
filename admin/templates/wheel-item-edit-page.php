<?php

namespace SWN_Deluxe;

$editing_item = isset($_GET['item_id']) ? Wheel_Items::get(intval($_GET['item_id'])) : null;

?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $editing_item ? __('Edit Item', 'swn-deluxe') : __('Add New Item', 'swn-deluxe'); ?>
        <a href="<?php echo admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEEL_ITEMS_LIST_PAGE']) . '&wheel_id=' . $wheel_id ?>" class="page-title-action"><?php _e('Back to Items List', 'swn-deluxe'); ?></a>

    </h1>
    <!-- Add/Edit Item Form -->
    <form method="post">
        <?php wp_nonce_field('swn_save_item_action', 'swn_save_item_nonce'); ?>
        <input type="hidden" name="item_id" value="<?php echo $editing_item ? esc_attr($editing_item->id) : ''; ?>">

        <table class="form-table">
            <tr>
                <th><label for="name"><?php _e('Name', 'swn-deluxe'); ?></label></th>
                <td><input type="text" name="name" id="name" value="<?php echo $editing_item ? esc_attr($editing_item->name) : ''; ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="display_name"><?php _e('Display Name', 'swn-deluxe'); ?></label></th>
                <td><input type="text" name="display_name" id="display_name" value="<?php echo $editing_item ? esc_attr($editing_item->display_name) : ''; ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="type"><?php _e('Type', 'swn-deluxe'); ?></label></th>
                <td><input type="text" name="type" id="type" value="<?php echo $editing_item ? esc_attr($editing_item->type) : 'coupon'; ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="value"><?php _e('Value', 'swn-deluxe'); ?></label></th>
                <td><input type="text" name="value" id="value" value="<?php echo $editing_item ? esc_attr($editing_item->value) : ''; ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="probability"><?php _e('Probability', 'swn-deluxe'); ?></label></th>
                <td><input type="number" name="probability" id="probability" step="0.01" value="<?php echo $editing_item ? esc_attr($editing_item->probability) : 0; ?>" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="segment_color"><?php _e('Segment Color', 'swn-deluxe'); ?></label></th>
                <td><input type="color" name="segment_color" id="segment_color" value="<?php echo $editing_item ? esc_attr($editing_item->segment_color) : '#ffffff'; ?>"></td>
            </tr>
            <tr>
                <th><label for="sort_order"><?php _e('Sort Order', 'swn-deluxe'); ?></label></th>
                <td><input type="number" name="sort_order" id="sort_order" value="<?php echo $editing_item ? esc_attr($editing_item->sort_order) : 0; ?>" class="small-text"></td>
            </tr>
        </table>

        <?php submit_button($editing_item ? __('Update Item', 'swn-deluxe') : __('Add Item', 'swn-deluxe'), 'primary', 'swn_save_item'); ?>
    </form>
</div>