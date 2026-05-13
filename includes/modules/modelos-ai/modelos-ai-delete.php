<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_benditoai_delete_modelo','benditoai_delete_modelo');

function benditoai_delete_modelo(){

    if(!is_user_logged_in()){
        wp_send_json_error("No autorizado");
    }

    $user_id = get_current_user_id();
    $modelo_id = intval($_POST['modelo_id']);

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

    if($deleted){
        $outfits_table = $wpdb->prefix . 'benditoai_modelos_ai_outfits';
        $wpdb->delete(
            $outfits_table,
            [
                'modelo_id' => $modelo_id,
                'user_id' => $user_id
            ],
            ['%d','%d']
        );
        wp_send_json_success();
    } else {
        wp_send_json_error("No se pudo eliminar");
    }

}
