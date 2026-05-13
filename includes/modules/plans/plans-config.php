<?php
if (!defined('ABSPATH')) exit;

/**
 * Fuente unica de planes del plugin.
 */

function benditoai_get_plans(){

    return apply_filters('benditoai_plans_config', [

        'starter' => [
            'name' => 'Starter',
            'tokens' => 200,
            'max_modelos' => 3,
            'max_outfits' => 1
        ],

        'pro' => [
            'name' => 'Pro',
            'tokens' => 1000,
            'max_modelos' => 10,
            'max_outfits' => 4
        ],

        'elite' => [
            'name' => 'Elite',
            'tokens' => 5000,
            'max_modelos' => 50,
            'max_outfits' => 8
        ]

    ]);

}

function benditoai_normalize_plan_key($plan){

    $plan = strtolower(trim((string) $plan));

    if(function_exists('remove_accents')){
        $plan = remove_accents($plan);
    }

    $plan = preg_replace('/[^a-z0-9_-]/', '', $plan);

    $aliases = apply_filters('benditoai_plan_aliases', [
        '' => 'starter',
        'free' => 'starter',
        'gratis' => 'starter',
        'basic' => 'starter',
        'basico' => 'starter',
        'starter' => 'starter',
        'intermedio' => 'pro',
        'intermediate' => 'pro',
        'pro' => 'pro',
        'premium' => 'elite',
        'elite' => 'elite',
    ]);

    if(isset($aliases[$plan])){
        return $aliases[$plan];
    }

    $plans = benditoai_get_plans();
    return isset($plans[$plan]) ? $plan : 'starter';
}

function benditoai_get_user_plan_key($user_id, $repair_meta = true){
    $raw_plan = get_user_meta($user_id, 'benditoai_plan', true);
    $plan = benditoai_normalize_plan_key($raw_plan);

    if($repair_meta && (string) $raw_plan !== $plan){
        update_user_meta($user_id, 'benditoai_plan', $plan);
        clean_user_cache($user_id);
        wp_cache_delete($user_id, 'user_meta');
    }

    return $plan;
}

function benditoai_sync_user_plan_limits($user_id, $plan = null, $reset_tokens = false){
    $plans = benditoai_get_plans();
    $plan = $plan === null ? benditoai_get_user_plan_key($user_id) : benditoai_normalize_plan_key($plan);

    if(!isset($plans[$plan])){
        $plan = 'starter';
    }

    update_user_meta($user_id, 'benditoai_plan', $plan);
    update_user_meta($user_id, 'benditoai_max_modelos', $plans[$plan]['max_modelos']);
    update_user_meta($user_id, 'benditoai_max_outfits', $plans[$plan]['max_outfits']);

    if($reset_tokens){
        update_user_meta($user_id, 'benditoai_tokens', $plans[$plan]['tokens']);
    }

    clean_user_cache($user_id);
    wp_cache_delete($user_id, 'user_meta');

    return array_merge($plans[$plan], ['key' => $plan]);
}

function benditoai_get_plan_label($plan){
    $plans = benditoai_get_plans();
    $plan = benditoai_normalize_plan_key($plan);

    return isset($plans[$plan]['name']) ? $plans[$plan]['name'] : 'Starter';
}
