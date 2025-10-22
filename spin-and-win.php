<?php

/**
 * Plugin Name:       Spin and Win Deluxe
 * Plugin URI:        https://aminlog.ir/wp-plugins/spin-and-win
 * Description:       Adds customizable spin-the-wheel games. Engage users with interactive prize wheels.
 * Version:           2.0.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Amin Salehizade
 * Author URI:        https://aminlog.ir
 * Tags:              Spin-to-Win Wheel, spin the wheel, spin wheel, fortune wheel, Prize Wheel, Lucky Wheel
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       swn-deluxe
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Plugin constants
 */
define('SWN_DELUXE_VERSION', '1.0.0');
define('SWN_DELUXE_PLUGIN_FILE', __FILE__);
define('SWN_DELUXE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SWN_DELUXE_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Include bootstrap
 */
require_once SWN_DELUXE_PLUGIN_DIR . 'includes/class-init.php';

/**
 * Initialize plugin
 */
\SWN_Deluxe\Init::init();

/**
 * Activation & deactivation hooks
 */
register_activation_hook(SWN_DELUXE_PLUGIN_FILE, ['\SWN_Deluxe\DB', 'create_tables']);
// register_deactivation_hook(SWN_DELUXE_PLUGIN_FILE, ['\SWN_Deluxe\DB', 'delete_tables']);
