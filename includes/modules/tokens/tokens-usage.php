<?php

if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| BenditoAI Token Usage
|--------------------------------------------------------------------------
| Esta función se llama desde cualquier herramienta.
| Verifica tokens y los descuenta automáticamente.
|
| Uso:
| benditoai_use_token();
|
*/

function benditoai_use_token($tokens = 1) {

    if (!is_user_logged_in()) {
        wp_send_json_error([
            'message' => 'Debes iniciar sesión para usar esta herramienta.'
        ]);
    }

    $user_id = get_current_user_id();

    if (!benditoai_user_has_tokens($user_id, $tokens)) {
        wp_send_json_error([
            'message' => 'No tienes tokens suficientes.'
        ]);
    }

    benditoai_decrease_tokens($user_id, $tokens);

}