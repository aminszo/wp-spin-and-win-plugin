<?php

/**
 * Wheel Items Management Page
 *
 * @package SWN_Deluxe
 */

use SWN_Deluxe\Admin;
use \SWN_Deluxe\Wheel_Items;

if (! defined('ABSPATH')) exit;

?>

<div class="wrap">
    <h1>
        <?php echo esc_html(sprintf(__('Manage Items for Wheel: %s', 'swn-deluxe'), $wheel->display_name)); ?>
        <a href="<?php echo admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEELS_LIST_PAGE']); ?>" class="page-title-action"><?php _e('Back to Wheels', 'swn-deluxe'); ?></a>
    </h1>

    <hr>

    <!-- Items List Table -->
    <h2>
        <?php _e('Existing Items', 'swn-deluxe'); ?>
        <a href="<?php echo admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEEL_ITEM_EDIT_PAGE'] . '&wheel_id=' . $wheel_id) ?>" class="page-title-action"><?php _e('Add New Item', 'swn-deluxe'); ?></a>
    </h2>
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
                            <a href="<?php echo admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEEL_ITEM_EDIT_PAGE'] . '&wheel_id=' . $wheel_id . '&item_id=' . $item->id); ?>" class="button"><?php _e('Edit', 'swn-deluxe'); ?></a>

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