<?php

/**
 * Wheel Items Management Page
 *
 * @package SWN_Deluxe
 */

use SWN_Deluxe\Admin;
use \SWN_Deluxe\Wheel_Items;

if (! defined('ABSPATH')) exit;

$editing_item = isset($_GET['item_id']) ? Wheel_Items::get(intval($_GET['item_id'])) : null;
?>

<div class="wrap">
    <h1>
        <?php echo esc_html(sprintf(__('Manage Items for Wheel: %s', 'swn-deluxe'), $wheel->display_name)); ?>
        <a href="<?php echo admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEELS_LIST_PAGE']); ?>" class="page-title-action"><?php _e('Back to Wheels', 'swn-deluxe'); ?></a>
    </h1>

    <!-- Add/Edit Item Form -->
    <h2><?php echo $editing_item ? __('Edit Item', 'swn-deluxe') : __('Add New Item', 'swn-deluxe'); ?></h2>
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

    <hr>

    <!-- Items List Table -->
    <h2><?php _e('Existing Items', 'swn-deluxe'); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Name', 'swn-deluxe'); ?></th>
                <th><?php _e('Display Name', 'swn-deluxe'); ?></th>
                <th><?php _e('Type', 'swn-deluxe'); ?></th>
                <th><?php _e('Value', 'swn-deluxe'); ?></th>
                <th><?php _e('Probability', 'swn-deluxe'); ?></th>
                <th><?php _e('Color', 'swn-deluxe'); ?></th>
                <th><?php _e('Sort Order', 'swn-deluxe'); ?></th>
                <th><?php _e('Actions', 'swn-deluxe'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item->name); ?></td>
                        <td><?php echo esc_html($item->display_name); ?></td>
                        <td><?php echo esc_html($item->type); ?></td>
                        <td><?php echo esc_html($item->value); ?></td>
                        <td><?php echo esc_html($item->probability); ?></td>
                        <td>
                            <span style="display:inline-block;width:24px;height:24px;background-color:<?php echo esc_attr($item->segment_color); ?>;border:1px solid #000;"></span>
                        </td>
                        <td><?php echo esc_html($item->sort_order); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page='.Admin::MENU_SLUGS['WHEEL_ITEMS_LIST_PAGE'].'&wheel_id=' . $wheel_id . '&item_id=' . $item->id); ?>" class="button"><?php _e('Edit', 'swn-deluxe'); ?></a>

                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('swn_delete_item_action', 'swn_delete_item_nonce'); ?>
                                <input type="hidden" name="item_id" value="<?php echo esc_attr($item->id); ?>">
                                <?php submit_button(__('Delete', 'swn-deluxe'), 'delete', 'swn_delete_item', false); ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8"><?php _e('No items found.', 'swn-deluxe'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>