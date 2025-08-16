<?php

namespace SWN_Deluxe;

/**
 * Main plugin bootstrap handler.
 *
 * Loads required files, initializes core classes, and hooks into WordPress.
 */
class Init
{
    /**
     * Entry point for plugin initialization.
     *
     * @return void
     */
    public static function init(): void
    {
        self::include_required_files();
        add_action('plugins_loaded', [self::class, 'initialize_plugin']);
    }

    /**
     * Include all the core files required by the plugin.
     *
     * @return void
     */
    private static function include_required_files(): void
    {
        require_once SWN_DELUXE_PLUGIN_DIR . 'db/class-db.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-wheels.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-wheel-items.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-swn-db.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-swn-user.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-swn-ajax.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-shortcode.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-swn-sms.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-swn-coupon-code.php';

        if (is_admin()) {
            require_once SWN_DELUXE_PLUGIN_DIR . 'admin/class-admin.php';
        }
    }

    /**
     * Runs after plugins are loaded.
     *
     * Loads translation and bootstraps core functionality.
     *
     * @return void
     */
    public static function initialize_plugin(): void
    {
        // Load translations
        load_plugin_textdomain(
            'swn-deluxe',
            false,
            dirname(plugin_basename(SWN_DELUXE_PLUGIN_FILE)) . '/languages'
        );

        // Core instances
        \SWN_DB::instance();
        \SWN_User::instance();
        \SWN_Ajax::instance();

        // Frontend shortcode
        Shortcode::init();

        // Admin area
        if (is_admin()) {
            Admin::init();
        }
    }
}
