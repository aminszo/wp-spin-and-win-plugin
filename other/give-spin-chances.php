<?php
// these codes are not used in the plugin and they are just for test


//on wheel creation
$settings = json_decode($wheel->settings, true);
$initial_chances = $settings['initial_chances'] ?? 0;

if ($initial_chances > 0) {
    $users = get_users(['fields' => 'ID']);
    foreach ($users as $user_id) {
        Spin_Chance::grant($wheel->id, $user_id, null, $initial_chances);
    }
}



// on user registration
add_action('user_register', function($user_id) {
    $wheels = Wheels::get_all(); // you should implement a method returning all wheels

    foreach ($wheels as $wheel) {
        $settings = json_decode($wheel->settings, true);
        $chances  = $settings['chances_on_register'] ?? 0;

        if ($chances > 0) {
            Spin_Chance::grant($wheel->id, $user_id, null, $chances);
        }
    }
});

