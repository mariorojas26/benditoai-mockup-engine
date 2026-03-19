<?php
if (!defined('ABSPATH')) exit;

function benditoai_set_default_plan($user_id){

    $plans = benditoai_get_plans();

    $default_plan = 'starter';

    update_user_meta($user_id, 'benditoai_plan', $default_plan);
    update_user_meta($user_id, 'benditoai_tokens', $plans[$default_plan]['tokens']);
    update_user_meta($user_id, 'benditoai_max_modelos', $plans[$default_plan]['max_modelos']);

}

add_action('user_register', 'benditoai_set_default_plan');