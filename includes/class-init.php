<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


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
        require_once SWN_DELUXE_PLUGIN_DIR . 'db/class-seeder.php';

        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-wheels.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-wheel-items.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-spin-chance.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-spin-log.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-shortcode.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-coupon-code.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-sms.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-settings.php';

        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/handle-spin/class-ajax.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/handle-spin/class-spin-handler.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/handle-spin/class-spin-validator.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/handle-spin/class-prize-selector.php';
        require_once SWN_DELUXE_PLUGIN_DIR . 'includes/handle-spin/class-prize-fulfillment.php';

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

        // Frontend shortcode
        Shortcode::init();

        // Initialize AJAX hooks
        \SWN_Deluxe\Handle_Spin\AJAX::init();

        // Admin area
        if (is_admin()) {
            Admin::init();
        }

        // $res = Spin_Chance::remaining(1,18,null);
        // var_dump($res);
        // die();

        // DB::refresh_with_seed();

        // $users = get_users(['fields' => 'ID']);
        // foreach ($users as $user_id) {
        //     Spin_Chance::set(1, $user_id, null, 2);
        // }

    }
}
