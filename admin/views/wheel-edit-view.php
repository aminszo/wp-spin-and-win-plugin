<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;

/**
 * Wheel Edit/Create Page
 *
 * @package SWN_Deluxe
 */

// Default values for new wheel
$wheel = $wheel ?? (object) [
    'id' => 0,
    'name' => '',
    'display_name' => '',
    'slug' => '',
    'status' => 'inactive',
    'settings' => '',
];

// Unserialize settings if needed
$wheel_settings = json_decode($wheel->settings, true);
?>

<div class="wrap">
    <h1>
        <?php echo $wheel->id ? esc_html__('Edit Wheel', 'swn-deluxe') : esc_html__('Add New Wheel', 'swn-deluxe'); ?>
        <a href="<?php echo admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEELS_LIST_PAGE']) ?>" class="page-title-action"><?php _e('Back to Wheels List', 'swn-deluxe'); ?></a>

    </h1>

    <form method="post">
        <?php wp_nonce_field('swn_save_wheel_action', 'swn_save_wheel_nonce'); ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="name"><?php esc_html_e('Name', 'swn-deluxe'); ?></label>
                </th>
                <td>
                    <input name="name" type="text" id="name" value="<?php echo esc_attr($wheel->name); ?>" class="regular-text" required>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="display_name"><?php esc_html_e('Display Name', 'swn-deluxe'); ?></label>
                </th>
                <td>
                    <input name="display_name" type="text" id="display_name" value="<?php echo esc_attr($wheel->display_name); ?>" class="regular-text" required>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="slug"><?php esc_html_e('Slug', 'swn-deluxe'); ?></label>
                </th>
                <td>
                    <input name="slug" type="text" id="slug" value="<?php echo esc_attr($wheel->slug); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('Unique identifier for this wheel.', 'swn-deluxe'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="status"><?php esc_html_e('Status', 'swn-deluxe'); ?></label>
                </th>
                <td>
                    <select name="status" id="status">
                        <option value="active" <?php selected($wheel->status, 'active'); ?>><?php esc_html_e('Active', 'swn-deluxe'); ?></option>
                        <option value="inactive" <?php selected($wheel->status, 'inactive'); ?>><?php esc_html_e('Inactive', 'swn-deluxe'); ?></option>
                    </select>
                </td>
            </tr>

            <!-- Optional settings: you can expand this later -->
            <tr>
                <th scope="row">
                    <label for="new_user_chances"><?php _e('Initial Spin Chances For New Users', 'swn-deluxe'); ?></label>
                </th>
                <td>
                    <input type="number"
                        name="settings[new_user_chances]"
                        id="new_user_chances"
                        value="<?php echo esc_attr($wheel_settings['new_user_chances'] ?? 0); ?>"
                        min="0" />
                    <p class="description"><?php _e('How many spin chances a new user gets when they register.', 'swn-deluxe'); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button($wheel->id ? __('Update Wheel', 'swn-deluxe') : __('Create Wheel', 'swn-deluxe'), 'primary', 'swn_save_wheel'); ?>
    </form>

    <?php if ($wheel->id) : ?>
        <hr>
        <div class="swn-danger-zone">
            <h2><?php esc_html_e('Delete', 'swn-deluxe'); ?></h2>
            <p><?php esc_html_e('Deleting this wheel is permanent and cannot be undone.', 'swn-deluxe'); ?></p>
            <form method="post" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to delete this wheel? This cannot be undone.', 'swn-deluxe'); ?>');">
                <?php wp_nonce_field('swn_delete_wheel_action', 'swn_delete_wheel_nonce'); ?>
                <input type="hidden" name="wheel_id" value="<?php echo intval($wheel->id); ?>">
                <?php submit_button(__('Delete Wheel', 'swn-deluxe'), 'delete', 'swn_delete_wheel'); ?>
            </form>
        </div>
    <?php endif; ?>


</div>