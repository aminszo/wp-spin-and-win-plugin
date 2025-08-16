<?php

namespace SWN_Deluxe;

require_once "class-admin-wheels.php";

class Admin
{
    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public const PARENT_MENU_SLUG = "swn-settings";

    private function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_swn_give_spin_manually', array($this, 'ajax_give_spin_manually'));
        Admin_Wheels::init();
    }

    public function add_admin_menu()
    {
        add_menu_page(
            __('Spin & Win', 'swn-deluxe'),
            __('Spin & Win', 'swn-deluxe'),
            'manage_options', // Capability
            self::PARENT_MENU_SLUG,   // Menu slug
            array($this, 'render_settings_page'),
            'dashicons-awards', // Icon
            30 // Position
        );
        add_submenu_page(
            'swn-settings',
            __('Prize Settings', 'swn-deluxe'),
            __('Prize Settings', 'swn-deluxe'),
            'manage_options',
            'swn-settings', // Same as parent for default
            array($this, 'render_settings_page')
        );
        // add_submenu_page(
        //     'swn-settings',
        //     __('Spin Logs', 'swn-deluxe'),
        //     __('Spin Logs', 'swn-deluxe'),
        //     'manage_options',
        //     'swn-logs',
        //     array($this, 'render_logs_page')
        // );
        // add_submenu_page(
        //     'swn-settings',
        //     __('Manual Spins', 'swn-deluxe'),
        //     __('Manual Spins', 'swn-deluxe'),
        //     'manage_options',
        //     'swn-manual-spins',
        //     array($this, 'render_manual_spins_page')
        // );
        // add_submenu_page(
        //     'swn-settings',
        //     __('Usage Conditions', 'swn-deluxe'),
        //     __('Usage Conditions', 'swn-deluxe'),
        //     'manage_options',
        //     'swn-conditions',
        //     array($this, 'render_conditions_page')
        // );
    }

    public function register_settings()
    {
        register_setting('swn_prizes_group', 'swn_prizes_settings', array($this, 'sanitize_prizes_settings'));
        register_setting('swn_conditions_group', 'swn_purchase_threshold'); // For purchase threshold
        // Add more settings as needed
    }

    public function sanitize_prizes_settings($input)
    {
        $sanitized_input = array();
        if (is_array($input)) {
            foreach ($input as $index => $prize) {
                if (empty($prize['name'])) continue; // Skip if name is empty

                $sanitized_input[$index]['name'] = sanitize_text_field($prize['name']);
                $sanitized_input[$index]['display_name'] = sanitize_text_field($prize['display_name']);
                $sanitized_input[$index]['type'] = sanitize_text_field($prize['type']);
                $sanitized_input[$index]['value'] = sanitize_text_field($prize['value']); // Needs more specific sanitization based on type
                $sanitized_input[$index]['probability'] = max(0, intval($prize['probability'])); // Ensure positive integer
                $sanitized_input[$index]['segment_color'] = sanitize_hex_color($prize['segment_color']);
            }
        }
        // Validate total probability is 100 (optional, but good practice)
        $total_prob = array_sum(array_column($sanitized_input, 'probability'));
        if ($total_prob !== 100 && !empty($sanitized_input)) {
            add_settings_error('swn_prizes_settings', 'prob_error', __('Total probability for all prizes should ideally sum up to 100.', 'swn-deluxe'), 'warning');
        }
        return $sanitized_input;
    }


    public function render_settings_page()
    {
        // include admin settings page template
        include SWN_DELUXE_PLUGIN_DIR . 'admin/templates/settings-page.php';
    }

    public function render_logs_page()
    {
        // include SWN_DELUXE_PLUGIN_DIR . 'templates/admin/logs-page.php';
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $logs_per_page = 20;
        $offset = ($paged - 1) * $logs_per_page;

        $logs = \SWN_DB::get_spin_logs(['number' => $logs_per_page, 'offset' => $offset]);
        $total_logs = \SWN_DB::get_total_spin_logs_count();
        $total_pages = ceil($total_logs / $logs_per_page);
    ?>
        <div class="wrap swn-admin-page">
            <h1><?php _e('Spin & Win - Spin Logs', 'swn-deluxe'); ?></h1>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Log ID', 'swn-deluxe'); ?></th>
                        <th><?php _e('User', 'swn-deluxe'); ?></th>
                        <th><?php _e('Prize Won', 'swn-deluxe'); ?></th>
                        <th><?php _e('Details', 'swn-deluxe'); ?></th>
                        <th><?php _e('Timestamp', 'swn-deluxe'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($logs)) : ?>
                        <?php foreach ($logs as $log) :
                            $user_info = get_userdata($log->user_id);
                            $prize_details = maybe_unserialize($log->prize_details);
                        ?>
                            <tr>
                                <td><?php echo esc_html($log->log_id); ?></td>
                                <td><?php echo $user_info ? esc_html($user_info->user_login . ' (ID: ' . $log->user_id . ')') : __('Unknown User', 'swn-deluxe'); ?></td>
                                <td><?php echo esc_html($log->prize_id); ?></td>
                                <td><?php echo esc_html(is_array($prize_details) ? implode(', ', $prize_details) : $prize_details); ?></td>
                                <td><?php echo esc_html($log->spin_timestamp); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5"><?php _e('No spin logs found.', 'swn-deluxe'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php
            if ($total_pages > 1) {
                echo '<div class="tablenav"><div class="tablenav-pages">' . paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $paged
                )) . '</div></div>';
            }
            ?>
        </div>
    <?php
    }

    public function render_manual_spins_page()
    {
        // include SWN_DELUXE_PLUGIN_DIR . 'templates/admin/manual-spins-page.php';
    ?>
        <div class="wrap swn-admin-page">
            <h1><?php _e('Spin & Win - Manually Give Spins', 'swn-deluxe'); ?></h1>
            <div id="swn-manual-spin-message"></div>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="swn-user-search"><?php _e('User (ID, Email, or Username)', 'swn-deluxe'); ?></label></th>
                    <td><input type="text" id="swn-user-search" name="swn_user_search" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="swn-spins-to-give"><?php _e('Number of Spins to Add/Set', 'swn-deluxe'); ?></label></th>
                    <td><input type="number" id="swn-spins-to-give" name="swn_spins_to_give" class="small-text" value="1" min="0" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="swn-spin-action"><?php _e('Action', 'swn-deluxe'); ?></label></th>
                    <td>
                        <select id="swn-spin-action" name="swn_spin_action">
                            <option value="add"><?php _e('Add to Current Spins', 'swn-deluxe'); ?></option>
                            <option value="set"><?php _e('Set Total Spins', 'swn-deluxe'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="button" id="swn-give-spin-button" class="button button-primary"><?php _e('Update User Spins', 'swn-deluxe'); ?></button>
            </p>
            <p><strong><?php _e('Current User Spins:', 'swn-deluxe'); ?></strong> <span id="swn-current-user-spins-display">N/A</span></p>
        </div>
    <?php
    }

    public function render_conditions_page()
    {
    ?>
        <div class="wrap swn-admin-page">
            <h1><?php _e('Spin & Win - Usage Conditions', 'swn-deluxe'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('swn_conditions_group');
                do_settings_sections('swn_conditions_group');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><label for="swn_purchase_threshold"><?php _e('Purchase Amount for Extra Spin ($)', 'swn-deluxe'); ?></label></th>
                        <td>
                            <input type="number" id="swn_purchase_threshold" name="swn_purchase_threshold" class="small-text" value="<?php echo esc_attr(get_option('swn_purchase_threshold', \SWN_User::PURCHASE_THRESHOLD_FOR_SPIN)); ?>" min="0" step="0.01" />
                            <p class="description"><?php _e('A user gets an additional spin chance if their completed order total is equal to or greater than this amount. Requires WooCommerce.', 'swn-deluxe'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
<?php
    }

    public function ajax_give_spin_manually()
    {
        check_ajax_referer('swn_admin_nonce', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'swn-deluxe')));
        }

        $user_identifier = sanitize_text_field($_POST['user_identifier'] ?? '');
        $spins_to_give = intval($_POST['spins_to_give'] ?? 0);
        $action = sanitize_text_field($_POST['action'] ?? 'add');

        if (empty($user_identifier)) {
            wp_send_json_error(array('message' => __('User identifier cannot be empty.', 'swn-deluxe')));
        }

        $user = null;
        if (is_numeric($user_identifier)) {
            $user = get_user_by('ID', $user_identifier);
        }
        if (! $user && is_email($user_identifier)) {
            $user = get_user_by('email', $user_identifier);
        }
        if (! $user) {
            $user = get_user_by('login', $user_identifier);
        }

        if (! $user) {
            wp_send_json_error(array('message' => sprintf(__('User "%s" not found.', 'swn-deluxe'), esc_html($user_identifier))));
        }

        if ($action === 'add') {
            \SWN_User::increment_spin_chances($user->ID, $spins_to_give);
        } elseif ($action === 'set') {
            \SWN_User::set_spin_chances($user->ID, $spins_to_give);
        } else {
            wp_send_json_error(array('message' => __('Invalid action.', 'swn-deluxe')));
        }

        $new_total_spins = \SWN_User::get_spin_chances($user->ID);
        wp_send_json_success(array(
            'message' => sprintf(__('Successfully updated spins for user %s. New total: %d', 'swn-deluxe'), $user->user_login, $new_total_spins),
            'current_spins' => $new_total_spins
        ));
    }
}
