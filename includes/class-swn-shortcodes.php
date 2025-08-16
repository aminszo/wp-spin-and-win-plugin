<?php
class SWN_Shortcodes
{
    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        add_shortcode('spin_and_win_wheel', array($this, 'render_wheel_shortcode'));
    }

    public function render_wheel_shortcode($atts)
    {
        /*
         * TODO: Enable custom wheel configuration by shortcode attributes
         *  $atts = shortcode_atts( array(
         *  'id' => 'default',
         *  ), $atts, 'spin_and_win_wheel' );
         */

        $user_id = get_current_user_id();
        $spin_chances = SWN_User::get_spin_chances($user_id);

        ob_start();

        $template = SWN_DELUXE_PLUGIN_DIR . 'templates/frontend/wheel-display.php';

        if (file_exists($template)) {
            include $template;
        } else {
            echo 'Wheel template not found.';
        }

        return ob_get_clean();
    }
}
