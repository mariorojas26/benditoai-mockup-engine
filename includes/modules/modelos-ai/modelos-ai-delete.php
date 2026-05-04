<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_benditoai_delete_modelo','benditoai_delete_modelo');

function benditoai_delete_modelo(){

    if(!is_user_logged_in()){
        wp_send_json_error(['message' => 'No autorizado']);
    }

    $user_id = get_current_user_id();
    $modelo_id = isset($_POST['modelo_id']) ? (int) $_POST['modelo_id'] : 0;

    if ($modelo_id <= 0) {
        wp_send_json_error(['message' => 'ID de modelo inválido.']);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'benditoai_modelos_ai';

    // SOLO borrar si es del usuario
    $deleted = $wpdb->delete(
        $table,
        [
            'id' => $modelo_id,
            'user_id' => $user_id
        ],
        ['%d','%d']
    );

    if ($deleted === false) {
        wp_send_json_error(['message' => 'No se pudo eliminar']);
    }

    wp_send_json_success([
        'message' => 'Modelo eliminado',
        'modelo_id' => $modelo_id,
    ]);

}
