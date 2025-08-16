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
        \SWN_Deluxe\Shortcode::init();
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
    }
}
