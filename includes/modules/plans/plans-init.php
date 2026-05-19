<?php
if (!defined('ABSPATH')) exit;

function benditoai_set_default_plan($user_id){

    benditoai_sync_user_plan_limits($user_id, 'starter', true);

}

add_action('user_register', 'benditoai_set_default_plan');

/**
 * One-time migration:
 * Ensure existing Starter users get refreshed limits after plan config updates.
 */
function benditoai_migrate_starter_limits_once() {
    if (!is_admin()) {
        return;
    }

    $flag = get_option('benditoai_migrated_starter_limits_v2', '0');
    if ($flag === '1') {
        return;
    }

    $users = get_users(array(
        'fields' => 'ids',
        'meta_key' => 'benditoai_plan',
        'meta_value' => 'starter',
        'number' => -1,
    ));

    if (!empty($users)) {
        foreach ($users as $user_id) {
            benditoai_sync_user_plan_limits((int) $user_id, 'starter', false);
        }
    }

    update_option('benditoai_migrated_starter_limits_v2', '1', false);
}

add_action('admin_init', 'benditoai_migrate_starter_limits_once');
