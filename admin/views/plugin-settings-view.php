<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;

?>

<div class="wrap">
    <h1><?php _e('SWN Deluxe Settings', 'swn-deluxe'); ?></h1>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('swn_deluxe_save_settings'); ?>
        <input type="hidden" name="action" value="swn_deluxe_save_settings">

        <div class="nav-tab-wrapper">
            <a href="#tab-sms" class="nav-tab nav-tab-active"><?php _e('SMS Settings', 'swn-deluxe'); ?></a>
            <a href="#tab-other" class="nav-tab"><?php _e('Other Settings', 'swn-deluxe'); ?></a>
        </div>

        <div id="tab-sms" class="swn-tab-content" style="display:block;">
            <table class="form-table">
                <tr>
                    <th><label for="sms_api_username"><?php _e('API Username', 'swn-deluxe'); ?></label></th>
                    <td><input type="text" id="sms_api_username" name="<?php echo $option_key; ?>[sms_api_username]" value="<?php echo esc_attr($settings['sms_api_username']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="sms_api_password"><?php _e('API Password', 'swn-deluxe'); ?></label></th>
                    <td><input type="password" id="sms_api_password" name="<?php echo $option_key; ?>[sms_api_password]" value="<?php echo esc_attr($settings['sms_api_password']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="pattern_sender_number"><?php _e('Pattern Sender Number', 'swn-deluxe'); ?></label></th>
                    <td><input type="text" id="pattern_sender_number" name="<?php echo $option_key; ?>[pattern_sender_number]" value="<?php echo esc_attr($settings['pattern_sender_number']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="text_sender_number"><?php _e('Text Sender Number', 'swn-deluxe'); ?></label></th>
                    <td><input type="text" id="text_sender_number" name="<?php echo $option_key; ?>[text_sender_number]" value="<?php echo esc_attr($settings['text_sender_number']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="user_phone_meta_key"><?php _e('User Phone Meta Key', 'swn-deluxe'); ?></label></th>
                    <td><input type="text" id="user_phone_meta_key" name="<?php echo $option_key; ?>[user_phone_meta_key]" value="<?php echo esc_attr($settings['user_phone_meta_key']); ?>" class="regular-text"></td>
                </tr>
            </table>
        </div>

        <div id="tab-other" class="swn-tab-content" style="display:none;">
            <table class="form-table">
                <tr>
                    <th><?php _e('Remove Data on Uninstall', 'swn-deluxe'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo $option_key; ?>[remove_data_on_uninstall]" value="1" <?php checked($settings['remove_data_on_uninstall'], 1); ?>>
                            <?php _e('Yes, remove all plugin data when uninstalling.', 'swn-deluxe'); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(); ?>
    </form>
</div>