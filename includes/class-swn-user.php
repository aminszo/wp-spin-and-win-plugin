<?php
class SWN_User
{
    private static $_instance = null;

    const SPIN_CHANCE_META_KEY = '_swn_spin_chances';
    const DEFAULT_SPINS_ON_REGISTER = 1; // Default spins for new users
    const PURCHASE_THRESHOLD_FOR_SPIN = 2000000; // Default purchase threshold: Spend 2000000 to get a spin

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        add_action('user_register', array($this, 'give_default_spin_on_register'));

        // Hook for WooCommerce order completion
        // add_action( 'woocommerce_order_status_completed', array( $this, 'give_spin_on_purchase' ) );
    }

    public static function get_spin_chances($user_id)
    {
        if (! $user_id) return 0;
        return (int) get_user_meta($user_id, self::SPIN_CHANCE_META_KEY, true);
    }

    public static function set_spin_chances($user_id, $chances)
    {
        if (! $user_id) return false;
        return update_user_meta($user_id, self::SPIN_CHANCE_META_KEY, max(0, (int) $chances)); // Ensure chances aren't negative
    }

    public static function increment_spin_chances($user_id, $amount = 1)
    {
        if (! $user_id) return false;
        $current_chances = self::get_spin_chances($user_id);
        return self::set_spin_chances($user_id, $current_chances + (int) $amount);
    }

    public static function decrement_spin_chances($user_id, $amount = 1)
    {
        if (! $user_id) return false;
        $current_chances = self::get_spin_chances($user_id);
        if ($current_chances <= 0) return false; // Cannot decrement if already zero or less
        return self::set_spin_chances($user_id, $current_chances - (int) $amount);
    }

    public function give_default_spin_on_register($user_id)
    {
        self::set_spin_chances($user_id, self::DEFAULT_SPINS_ON_REGISTER);
    }

    public function give_spin_on_purchase($order_id)
    {
        $order = wc_get_order($order_id);
        if (! $order) return;

        $user_id = $order->get_user_id();
        if (! $user_id) return; // Only for registered users

        $order_total = $order->get_total();

        // Check if custom threshold is set, otherwise use default
        $threshold = get_option('swn_purchase_threshold', self::PURCHASE_THRESHOLD_FOR_SPIN);

        if ($order_total >= (float) $threshold) {
            self::increment_spin_chances($user_id, 1);
            // Optional: Add an order note
            // $order->add_order_note( __( 'User awarded 1 spin chance for this purchase.', 'swn-deluxe' ) );
        }
    }

    /**
     * Assigns default spin chance to all existing users if they don't already have any.
     * This function is designed to be run once.
     */
    public function assign_default_spin_chances_to_existing_users()
    {
        // Use an option to mark that this function has been run
        $option_name = 'swn_default_spin_chances_assigned';

        // Check if the function has already been run
        if (get_option($option_name)) {
            return 'Default spin chances have already been assigned to existing users.';
        }

        // Get all user IDs
        $users = get_users(array('fields' => 'ID'));
        $users_updated = 0;

        if ($users) {
            foreach ($users as $user_id) {
                // Get current spin chances for the user
                $current_spin_chances = self::get_spin_chances($user_id);

                // If the user doesn't have any spin chances (or meta doesn't exist), give them 1
                if ($current_spin_chances === 0) {
                    self::set_spin_chances($user_id, 1);
                    $users_updated++;
                }
            }
        }

        // Mark that the function has been run
        update_option($option_name, true);

        return sprintf('Successfully assigned 1 default spin chance to %d existing users.', $users_updated);
    }
}
