<?php
class SWN_DB
{
    private static $_instance = null;

    const TABLE_SPIN_LOGS = 'swn_spin_logs';

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function activate()
    {
        self::create_spin_logs_table();
        self::set_default_prize_options();
    }

    public static function deactivate()
    {
        // Code to run on deactivation.

        // Important: remove tables and options only if the user has chosen to clean up plugin's data.

        // Remove logs table
        // global $wpdb;
        // $table_name_logs = $wpdb->prefix . self::TABLE_SPIN_LOGS;
        // $wpdb->query( "DROP TABLE IF EXISTS $table_name_logs" );

        // remove prize options
        // delete_option('swn_prizes_settings');


        // delete_option('swn_purchase_threshold');
        // delete_option('swn_default_spin_chances_assigned');
    }

    public static function create_spin_logs_table()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Create spin logs table
        $spin_logs_table_name = $wpdb->prefix . self::TABLE_SPIN_LOGS;
        $sql_logs = "CREATE TABLE $spin_logs_table_name (
            log_id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            prize_id varchar(255) NOT NULL,
            prize_details text DEFAULT NULL,
            spin_timestamp datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (log_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_logs); // create the table if already not exist. or update it.
    }

    public static function create_sms_logs_table() {}

    public static function set_default_prize_options()
    {
        // Prize configurations will be stored in wp_options for simplicity,
        // but a custom table could be used for more complex prize structures.
        // Example: get_option('swn_prizes_settings', []);
        // Set default prize options if not exist
        if (false === get_option('swn_prizes_settings')) {
            $default_prizes = array(
                array('name' => '10% Discount', 'type' => 'coupon', 'value' => 'SPIN10', 'probability' => 30, 'segment_color' => '#FFD700'),
                array('name' => '5 Credits', 'type' => 'credit', 'value' => '5', 'probability' => 20, 'segment_color' => '#C0C0C0'),
                array('name' => 'Try Again', 'type' => 'nothing', 'value' => '', 'probability' => 50, 'segment_color' => '#808080'),
            );
            update_option('swn_prizes_settings', $default_prizes);
        }
    }

    public static function add_spin_log($user_id, $prize_id, $prize_details = '')
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_SPIN_LOGS;

        $wpdb->insert(
            $table_name,
            array(
                'user_id'        => $user_id,
                'prize_id'       => $prize_id,
                'prize_details'  => maybe_serialize($prize_details),
                'spin_timestamp' => current_time('mysql'),
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
            )
        );
        return $wpdb->insert_id;
    }

    public static function get_spin_logs($args = array())
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_SPIN_LOGS;

        $defaults = array(
            'number'  => 20,
            'offset'  => 0,
            'orderby' => 'log_id',
            'order'   => 'DESC',
            'user_id' => null,
        );
        $args = wp_parse_args($args, $defaults);

        $sql = "SELECT * FROM $table_name";
        if (!is_null($args['user_id'])) {
            $sql .= $wpdb->prepare(" WHERE user_id = %d", $args['user_id']);
        }
        $sql .= $wpdb->prepare(" ORDER BY %s %s LIMIT %d OFFSET %d", $args['orderby'], $args['order'], $args['number'], $args['offset']);

        return $wpdb->get_results($sql);
    }

    public static function get_total_spin_logs_count()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_SPIN_LOGS;
        return (int) $wpdb->get_var("SELECT COUNT(log_id) FROM $table_name");
    }
}
