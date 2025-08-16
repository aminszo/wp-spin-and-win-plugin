<?php

/**
 * Main plugin bootstrap class.
 *
 * Handles initialization, asset loading, and instantiating
 * the other plugin component classes.
 */
class Spin_And_Win_Deluxe
{

    /**
     * Holds the singleton instance of the class.
     *
     * @var Spin_And_Win_Deluxe|null
     */
    private static $_instance = null;


    /**
     * Retrieves the singleton instance of this class.
     *
     * @return Spin_And_Win_Deluxe
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     * Constructor.
     *
     * Initializes hooks and loads required classes.
     * This is private to enforce the singleton pattern.
     */
    private function __construct()
    {
        $this->init_hooks();
        SWN_DB::instance();
        SWN_User::instance();
        SWN_Ajax::instance();
        SWN_Shortcodes::instance();
        if (is_admin()) {
            \SWN_Deluxe\Admin::init();
        }
    }


    /**
     * Registers plugin hooks for localization and assets.
     *
     * @return void
     */
    private function init_hooks()
    {
        load_plugin_textdomain('swn-deluxe', false, basename(dirname(dirname(__FILE__))) . '/languages');
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
    }


    /**
     * Enqueues frontend scripts and styles.
     *
     * Loads only if {@see self::should_load_frontend_assets()} returns true.
     * Passes wheel configuration and UI strings via `wp_localize_script()`.
     *
     * @return void
     */
    public function enqueue_frontend_assets()
    {
        // Only load on pages where the shortcode is present or specific conditions are met
        if (! $this->should_load_frontend_assets()) {
            return;
        }

        wp_enqueue_script('winwheel-js', SWN_DELUXE_PLUGIN_URL . 'assets/js/winwheel.min.js', array(), null, true);
        wp_enqueue_script('tweenmax-js', SWN_DELUXE_PLUGIN_URL . 'assets/js/tweenmax.min.js', array(), null, true);
        wp_enqueue_style('sweetalert-css', SWN_DELUXE_PLUGIN_URL . 'assets/css/sweetalert2.min.css', array(), "11.22.0");
        wp_enqueue_script('sweetalert-js', SWN_DELUXE_PLUGIN_URL . 'assets/js/sweetalert2@11.js', array(), null, true);
        wp_enqueue_style('swn-frontend-css', SWN_DELUXE_PLUGIN_URL . 'assets/css/swn-frontend.css', array(), SWN_DELUXE_VERSION);
        wp_enqueue_script('swn-frontend-js', SWN_DELUXE_PLUGIN_URL . 'assets/js/swn-frontend.js', array('jquery', 'winwheel-js'), SWN_DELUXE_VERSION, true);

        // Get prize configuration from database.
        $prizes_config = get_option('swn_prizes_settings', []);
        $segments = [];
        if (is_array($prizes_config)) {
            foreach ($prizes_config as $index => $prize) {
                $segments[] = array(
                    'fillStyle' => !empty($prize['segment_color']) ? sanitize_hex_color($prize['segment_color']) : '#' . substr(md5(rand()), 0, 6), // Random color if not set
                    'text'      => $prize['display_name'],
                    'id'        => $index // Or a unique prize ID
                );
            }
        }

        // Localize script with AJAX URL, nonce, and wheel configuration.
        wp_localize_script('swn-frontend-js', 'swn_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('swn_spin_nonce'),
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
            'user_spin_chances' => is_user_logged_in() ? SWN_User::get_spin_chances(get_current_user_id()) : 0,
            'not_logged_in_message' => __('Please log in to spin the wheel!', 'swn-deluxe'),
            'remaining_spins_text' => __('Remaining spin chances: %d', 'swn-deluxe'),
            'no_spins_message' => __('You have no spins left.', 'swn-deluxe'),
            'win_message' => __('You won %s', 'swn-deluxe'),
            'spinning_message' => __('...', 'swn-deluxe'),
            'tick_audio_url' => plugin_dir_url(__FILE__) . 'assets/audio/tick.mp3',
            'success_audio_url' => plugin_dir_url(__FILE__) . 'assets/audio/success.mp3',
        ));
    }


    /**
     * Determines whether frontend assets should be loaded.
     *
     * Checks if the current page contains the plugin shortcode.
     *
     * @global WP_Post $post
     * @return bool
     */
    private function should_load_frontend_assets()
    {
        // logic to determine if assets should be loaded
        // check if the current page contains the shortcode
        global $post;
        return (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'spin_and_win_wheel'));
    }
}