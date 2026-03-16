<?php



/**
 * Verifica si el admin tiene tokens ilimitados ACTIVOS
 */
function benditoai_admin_unlimited_tokens_enabled($user_id){

    if(!user_can($user_id,'administrator')){
        return false;
    }

    $enabled = get_user_meta($user_id,'benditoai_admin_unlimited_tokens',true);

    return $enabled === 'yes';
}


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verificar si el usuario es administrador
 * Si es admin tendrá tokens ilimitados
 */
/**
 * Determinar si el usuario tiene tokens ilimitados
 */
function benditoai_user_has_unlimited_tokens($user_id){

    if(!user_can($user_id,'administrator')){
        return false;
    }

    return benditoai_admin_unlimited_tokens_enabled($user_id);
}


/**
 * Obtener tokens del usuario
 */
function benditoai_get_user_tokens($user_id) {

    // 👑 Si es admin devolvemos un número muy alto
    // Esto es solo para mostrar en el contador
    if (benditoai_user_has_unlimited_tokens($user_id)) {
        return 999999;
    }

    $tokens = get_user_meta($user_id, 'benditoai_tokens', true);

    // Si el usuario nunca ha tenido tokens
    if ($tokens === '') {
        $tokens = 50; // tokens iniciales
        update_user_meta($user_id, 'benditoai_tokens', $tokens);
    }

    return intval($tokens);
}


/**
 * Descontar tokens
 */
function benditoai_decrease_tokens($user_id, $amount = 1) {

    // 👑 Si es admin NO descontamos tokens
    if (benditoai_user_has_unlimited_tokens($user_id)) {
        return true;
    }

    $tokens = benditoai_get_user_tokens($user_id);

    $tokens = $tokens - $amount;

    if ($tokens < 0) {
        $tokens = 0;
    }

    update_user_meta($user_id, 'benditoai_tokens', $tokens);

    return $tokens;
}


/**
 * Verificar si el usuario tiene tokens
 */
function benditoai_user_has_tokens($user_id, $amount = 1) {

    // 👑 Si es admin siempre tiene tokens
    if (benditoai_user_has_unlimited_tokens($user_id)) {
        return true;
    }

    $tokens = benditoai_get_user_tokens($user_id);

    return $tokens >= $amount;
}

/**
 * AJAX para activar / desactivar tokens ilimitados del admin
 */
function benditoai_toggle_admin_tokens(){

    if(!current_user_can('administrator')){
        wp_send_json_error();
    }

    $user_id = get_current_user_id();

    $enabled = sanitize_text_field($_POST['enabled']);

    update_user_meta($user_id,'benditoai_admin_unlimited_tokens',$enabled);

    wp_send_json_success();

}

add_action('wp_ajax_benditoai_toggle_admin_tokens','benditoai_toggle_admin_tokens');