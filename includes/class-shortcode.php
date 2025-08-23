<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


class Shortcode
{

    public const SHORTCODE_NAME = 'spin_and_win_wheel';
    public static function init()
    {
        add_shortcode(self::SHORTCODE_NAME, [self::class, 'render_wheel_shortcode']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_frontend_assets']);
    }


    /**
     * Enqueue frontend assets (scripts & styles).
     * Assets are always enqueued when a shortcode exists in the page content, segments are localized per wheel.
     */
    public static function enqueue_frontend_assets()
    {
        // Only load on pages where the shortcode is present.
        if (! self::should_load_frontend_assets()) {
            return;
        }

        wp_enqueue_script('winwheel-js', SWN_DELUXE_PLUGIN_URL . 'assets/js/winwheel.min.js', [], null, true);
        wp_enqueue_script('tweenmax-js', SWN_DELUXE_PLUGIN_URL . 'assets/js/tweenmax.min.js', [], null, true);
        wp_enqueue_style('sweetalert-css', SWN_DELUXE_PLUGIN_URL . 'assets/css/sweetalert2.min.css', [], '11.22.0');
        wp_enqueue_script('sweetalert-js', SWN_DELUXE_PLUGIN_URL . 'assets/js/sweetalert2@11.js', [], null, true);
        wp_enqueue_style('swn-frontend-css', SWN_DELUXE_PLUGIN_URL . 'assets/css/swn-frontend.css', [], SWN_DELUXE_VERSION);
        wp_enqueue_script('swn-frontend-js', SWN_DELUXE_PLUGIN_URL . 'assets/js/swn-frontend.js', ['jquery', 'winwheel-js'], SWN_DELUXE_VERSION, true);
    }

    /**
     * Determines whether frontend assets should be loaded.
     *
     * Checks if the current page contains the plugin shortcode.
     *
     * @global WP_Post $post
     * @return bool
     */
    private static function should_load_frontend_assets()
    {
        // logic to determine if assets should be loaded
        // check if the current page contains the shortcode
        global $post;
        return (is_a($post, 'WP_Post') && has_shortcode($post->post_content, self::SHORTCODE_NAME));
    }


    /**
     * Render a wheel by shortcode.
     *
     * Attributes:
     *  - slug: string, required wheel slug
     */
    public static function render_wheel_shortcode($atts)
    {
        $atts = shortcode_atts([
            'slug' => 'default',
        ], $atts, self::SHORTCODE_NAME);

        $wheel = Wheels::get_by_slug($atts['slug']);
        if (!$wheel) {
            return __('Wheel not found.', 'swn-deluxe');
        }

        $wheel_settings = [];

        $items = Wheel_Items::get_by_wheel($wheel['id']);

        $user_id = get_current_user_id();

        // Prepare segments
        $segments = [];

        foreach ($items as $index => $item) {
            $segments[] = array(
                'fillStyle' => !empty($item->segment_color) ? sanitize_hex_color($item->segment_color) : '#' . substr(md5(rand()), 0, 6), // Random color if not set
                'text'      => $item->display_name,
                'id'        => $index // Or a unique prize ID
            );
        }

        // var_dump($segments);

        // Localize per wheel
        $params = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('swn_spin_nonce'),
            'wheel_id' => $wheel['id'],
            'user_logged_in' => is_user_logged_in(),
            'segments' => $segments,
            'numSegments' => count($segments),
            'text_size' => 16,
            'wheel_stroke_color' => '#FFFFFF',
            'wheel_line_width' => 3,
            'wheel_pointer_color' => '#000000',
            'wheel_text_color' => '#FFFFFF',
            'outer_radius' => 200,
            'inner_radius' => 10,
            'pin_image_url' => null, //SWN_DELUXE_PLUGIN_URL . 'assets/image/pin.png',
            'user_spin_chances' => 2,//is_user_logged_in() ? \SWN_User::get_spin_chances(get_current_user_id()) : 0,
            'not_logged_in_message' => __('Please log in to spin the wheel!', 'swn-deluxe'),
            'remaining_spins_text' => __('Remaining spin chances: %d', 'swn-deluxe'),
            'no_spins_message' => __('You have no spins left.', 'swn-deluxe'),
            'win_message' => __('You won %s', 'swn-deluxe'),
            'spinning_message' => __('...', 'swn-deluxe'),
            'tick_audio_url' => SWN_DELUXE_PLUGIN_URL . 'assets/audio/tick.mp3',
            'success_audio_url' => SWN_DELUXE_PLUGIN_URL . 'assets/audio/success.mp3',
        ];

        wp_localize_script('swn-frontend-js', 'swn_params', $params);

        // Render template
        ob_start();
        $template = SWN_DELUXE_PLUGIN_DIR . 'templates/frontend/wheel-display.php';
        if (file_exists($template)) {
            include $template;
        } else {
            echo __('Wheel template not found.', 'swn-deluxe');
        }
        return ob_get_clean();
    }
}
