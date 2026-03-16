<?php

if (!defined('ABSPATH')) {
    exit;
}

function benditoai_get_tokens_ajax() {

    if (!is_user_logged_in()) {
        wp_send_json_error("No autorizado");
    }

    $user_id = get_current_user_id();

    $tokens = benditoai_get_user_tokens($user_id);

    wp_send_json_success([
        'tokens' => $tokens
    ]);
}

add_action('wp_ajax_benditoai_get_tokens', 'benditoai_get_tokens_ajax');