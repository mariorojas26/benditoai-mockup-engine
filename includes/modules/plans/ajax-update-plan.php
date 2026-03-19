<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_benditoai_update_user_plan','benditoai_update_user_plan');

function benditoai_update_user_plan(){

    if(!current_user_can('manage_options')){
        wp_send_json_error(['message'=>'No autorizado']);
    }

    $user_id = intval($_POST['user_id']);
    $plan = sanitize_text_field($_POST['plan']);

    // guardar plan
    update_user_meta($user_id,'benditoai_plan',$plan);

    // 🔥 obtener configuración del plan
    $plans = benditoai_get_plans();

    if(isset($plans[$plan])){

        $tokens_plan = $plans[$plan]['tokens'];

        // 🔥 RESETEAR TOKENS SEGÚN PLAN
        update_user_meta($user_id,'benditoai_tokens',$tokens_plan);

    }

    wp_send_json_success([
        'message' => 'Plan actualizado correctamente'
    ]);

}