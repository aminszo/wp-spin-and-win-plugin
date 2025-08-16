<?php

if (! defined('ABSPATH')) exit;

use \SWN_Deluxe\Admin;

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Wheels', 'swn-deluxe'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEEL_EDIT_PAGE'])); ?>" class="page-title-action">
        <?php esc_html_e('Add New Wheel', 'swn-deluxe'); ?>
    </a>

    <hr class="wp-header-end">

    <?php if (!empty($wheels)) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'swn-deluxe'); ?></th>
                    <th><?php esc_html_e('Name', 'swn-deluxe'); ?></th>
                    <th><?php esc_html_e('Display Name', 'swn-deluxe'); ?></th>
                    <th><?php esc_html_e('Slug', 'swn-deluxe'); ?></th>
                    <th><?php esc_html_e('Status', 'swn-deluxe'); ?></th>
                    <th><?php esc_html_e('Created At', 'swn-deluxe'); ?></th>
                    <th><?php esc_html_e('Actions', 'swn-deluxe'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($wheels as $wheel) : ?>
                    <tr>
                        <td><?php echo intval($wheel->id); ?></td>
                        <td><?php echo esc_html($wheel->name); ?></td>
                        <td><?php echo esc_html($wheel->display_name); ?></td>
                        <td><?php echo esc_html($wheel->slug); ?></td>
                        <td><?php echo esc_html(ucfirst($wheel->status)); ?></td>
                        <td><?php echo esc_html($wheel->created_at); ?></td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEEL_EDIT_PAGE'] . '&id=' . intval($wheel->id))); ?>"
                                class="button">
                                <?php esc_html_e('Edit', 'swn-deluxe'); ?>
                            </a>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEEL_ITEMS_LIST_PAGE'] . '&wheel_id=' . intval($wheel->id))); ?>"
                                class="button">
                                <?php esc_html_e('Manage Items', 'swn-deluxe'); ?>
                            </a>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p><?php esc_html_e('No wheels found.', 'swn-deluxe'); ?></p>
    <?php endif; ?>
</div>