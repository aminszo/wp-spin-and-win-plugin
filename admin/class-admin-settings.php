<?php

namespace SWN_Deluxe;

defined('ABSPATH') || exit;


class Admin_Settings
{

    /**
     * Initialize hooks.
     * Hooks the menu registration to the WordPress admin_menu action.
     * 
     * @return void
     */
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'register_menu']);
    }


    public static function register_menu()
    {


    }


    public static function render_settings_page()
    {
        echo "settings here.";
    }
}
