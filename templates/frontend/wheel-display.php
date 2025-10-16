<div id="swn-wheel-container" class="swn-wheel-container">
    <div class="swn-wheel-wrapper">
        <!-- <img id="swn-pin-image"src="<?php echo SWN_DELUXE_PLUGIN_URL . 'assets/image/pin.png'; ?>," alt=""> -->
        <canvas id="swn-canvas" width="450" height="450"
            data-responsiveMinWidth="180"
            data-responsiveScaleHeight="true"
            data-responsiveMargin="5">
            <p><?php _e('Sorry, your browser doesn\'t support canvas. Please try another.', 'swn-deluxe'); ?></p>
        </canvas>
        <button id="swn-spin-trigger" alt="<?php _e('Spin the Wheel', 'swn-deluxe'); ?>"><?php _e('Spin', 'swn-deluxe') ?></button>
    </div>

    <?php if (! is_user_logged_in()) : ?>
        <div class="swn-not-logged-in" style="text-align: center;">
            <p>
                <?= __('Please log in to spin the wheel!', 'swn-deluxe') ?>
            </p>
            <a href="?login=true&type=login&back=swn" onclick="jQuery('this').digits_login_modal(jQuery(this));return false;" attr-disclick="1" class="digits-login-modal" type="1">
                <?= __('Login or Signup', 'swn-deluxe') ?>
            </a>
        </div>
    <?php else : ?>
        <div id="swn-message-area" class="swn-message-area"></div>
        <p class="swn-spin-chances"><?php printf(__('Remaining spin chances: %d', 'swn-deluxe'), $spin_chances); ?></p>
    <?php endif; ?>
</div>