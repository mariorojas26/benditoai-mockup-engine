<?php
function benditoai_get_user_plan_data($user_id){

    $plans = benditoai_get_plans();

    // 🔥 FORZAR lectura fresca desde BD
    $plan = get_user_meta($user_id, 'benditoai_plan', true);

    // limpiar cache de meta (IMPORTANTE)
    wp_cache_delete($user_id, 'user_meta');

    // volver a obtener
    $plan = get_user_meta($user_id, 'benditoai_plan', true);

    $plan = strtolower(trim($plan));

    if(!$plan || !isset($plans[$plan])){
        $plan = 'starter';
    }

    return $plans[$plan];
}