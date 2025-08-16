<?php

/**
 * Plugin Name:       Spin and Win Deluxe
 * Plugin URI:        https://aminlog.ir/wp-plugins/spin-and-win
 * Description:       Engage users with a spin and win game for prizes.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Amin Salehizade
 * Author URI:        https://aminlog.ir
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       swn-deluxe
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}


// Define plugin constants
define('SWN_DELUXE_VERSION', '1.0.0');
define('SWN_DELUXE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SWN_DELUXE_PLUGIN_URL', plugin_dir_url(__FILE__));


// Include the core files required by the plugin.
require_once SWN_DELUXE_PLUGIN_DIR . 'admin/class-admin.php';
require_once SWN_DELUXE_PLUGIN_DIR . 'db/class-db.php';
require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-swn-db.php';
require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-swn-user.php';
require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-swn-ajax.php';
require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-swn-shortcodes.php';
require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-swn-sms.php';
require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-swn-coupon-code.php';
require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-core.php';


/**
 * Initializes the plugin on the `plugins_loaded` hook.
 *
 * @return void
 */
add_action('plugins_loaded', function () {
    Spin_And_Win_Deluxe::instance();
});


// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('\SWN_Deluxe\DB', 'create_tables'));
register_deactivation_hook(__FILE__, array('\SWN_Deluxe\DB', 'delete_tables'));
