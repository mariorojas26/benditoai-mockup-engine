<?php
if (!defined('ABSPATH')) exit;

function benditoai_get_user_plan_data($user_id){

    $plans = benditoai_get_plans();

    $plan = get_user_meta($user_id, 'benditoai_plan', true);

    if(!$plan || !isset($plans[$plan])){
        $plan = 'starter';
    }

    return $plans[$plan];
}