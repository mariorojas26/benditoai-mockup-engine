<?php
if (!defined('ABSPATH')) exit;

function benditoai_set_default_plan($user_id){

    benditoai_sync_user_plan_limits($user_id, 'starter', true);

}

add_action('user_register', 'benditoai_set_default_plan');
