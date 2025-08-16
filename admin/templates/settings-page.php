<div class="wrap swn-admin-page">
    <h1><?php _e('Spin & Win - Prize Settings', 'swn-deluxe'); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('swn_prizes_group');
        do_settings_sections('swn_prizes_group'); // Not strictly needed if not using sections
        $prizes = get_option('swn_prizes_settings', array());
        if (empty($prizes)) { // Provide at least one empty row if no prizes are set
            $prizes = [['name' => '', 'type' => 'coupon', 'value' => '', 'probability' => '', 'segment_color' => '#CCCCCC']];
        }
        ?>
        <div id="swn-prizes-container">
            <?php foreach ($prizes as $index => $prize) : ?>
                <div class="swn-prize-entry" data-index="<?php echo $index; ?>">
                    <hr>
                    <h4><?php printf(__('Prize Segment %d', 'swn-deluxe'), $index + 1); ?></h4>
                    <p>
                        <label><?php _e('Prize Name:', 'swn-deluxe'); ?></label><br>
                        <input type="text" name="swn_prizes_settings[<?php echo $index; ?>][name]" value="<?php echo esc_attr($prize['name'] ?? ''); ?>" required />
                    </p>
                    <p>
                        <label><?php _e('Prize Text on Wheel:', 'swn-deluxe'); ?></label><br>
                        <input type="text" name="swn_prizes_settings[<?php echo $index; ?>][display_name]" value="<?php echo esc_attr($prize['display_name'] ?? ''); ?>" required />
                    </p>
                    <p>
                        <label><?php _e('Prize Type:', 'swn-deluxe'); ?></label><br>
                        <select name="swn_prizes_settings[<?php echo $index; ?>][type]">
                            <option value="coupon" <?php selected(($prize['type'] ?? ''), 'coupon'); ?>><?php _e('Discount Coupon', 'swn-deluxe'); ?></option>
                            <option value="credit" <?php selected(($prize['type'] ?? ''), 'credit'); ?>><?php _e('Credit', 'swn-deluxe'); ?></option>
                            <option value="product" <?php selected(($prize['type'] ?? ''), 'product'); ?>><?php _e('Free Coffee Sample Box', 'swn-deluxe'); ?></option>
                        </select>
                    </p>
                    <p>
                        <label><?php _e('Prize Value:', 'swn-deluxe'); ?></label><br>
                        <input type="text" name="swn_prizes_settings[<?php echo $index; ?>][value]" value="<?php echo esc_attr($prize['value'] ?? ''); ?>" />
                        <small><em><?php _e('E.g., Coupon Code, Credit Amount, Product ID.', 'swn-deluxe'); ?></em></small>
                    </p>
                    <p>
                        <label><?php _e('Probability (%):', 'swn-deluxe'); ?></label><br>
                        <input type="number" name="swn_prizes_settings[<?php echo $index; ?>][probability]" value="<?php echo esc_attr($prize['probability'] ?? ''); ?>" min="0" max="100" step="1" required />
                    </p>
                    <p>
                        <label><?php _e('Segment Color:', 'swn-deluxe'); ?></label><br>
                        <input type="color" name="swn_prizes_settings[<?php echo $index; ?>][segment_color]" value="<?php echo esc_attr($prize['segment_color'] ?? '#CCCCCC'); ?>" />
                    </p>
                    <button type="button" class="button swn-remove-prize"><?php _e('Remove Prize Segment', 'swn-deluxe'); ?></button>
                </div>
            <?php endforeach; ?>
        </div>
        <p><button type="button" id="swn-add-prize" class="button button-secondary"><?php _e('Add New Prize Segment', 'swn-deluxe'); ?></button></p>
        <?php submit_button(); ?>
    </form>
</div>