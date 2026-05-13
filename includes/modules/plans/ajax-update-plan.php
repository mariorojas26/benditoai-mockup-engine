<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_benditoai_update_user_plan','benditoai_update_user_plan');

function benditoai_update_user_plan(){

    if(!current_user_can('manage_options')){
        wp_send_json_error(['message'=>'No autorizado']);
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $raw_plan = isset($_POST['plan']) ? sanitize_text_field(wp_unslash($_POST['plan'])) : '';
    $plan = benditoai_normalize_plan_key($raw_plan);
    $plans = benditoai_get_plans();

    if($user_id <= 0 || !get_user_by('id', $user_id)){
        wp_send_json_error(['message'=>'Usuario invalido']);
    }

    if(!isset($plans[$plan])){
        wp_send_json_error(['message'=>'Plan invalido']);
    }

    $plan_data = benditoai_sync_user_plan_limits($user_id, $plan, true);

    wp_send_json_success([
        'message' => 'Plan actualizado correctamente',
        'plan' => $plan,
        'limits' => [
            'tokens' => $plan_data['tokens'],
            'max_modelos' => $plan_data['max_modelos'],
            'max_outfits' => $plan_data['max_outfits'],
        ],
    ]);

}
