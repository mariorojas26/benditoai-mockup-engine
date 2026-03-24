<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_benditoai_edit_modelo','benditoai_edit_modelo');

function benditoai_edit_modelo(){

    if(!is_user_logged_in()){
        wp_send_json_error(['message'=>'No autorizado']);
    }

    $user_id = get_current_user_id();
    $modelo_id = intval($_POST['modelo_id']);
    $texto = sanitize_text_field($_POST['texto']);
    $image_url = esc_url_raw($_POST['image_url']);

    global $wpdb;
    $table = $wpdb->prefix . 'benditoai_modelos_ai';

    // 🔒 validar que el modelo es del usuario
    $modelo = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND user_id = %d",
        $modelo_id,
        $user_id
    ));

    if(!$modelo){
        wp_send_json_error(['message'=>'Modelo no válido']);
    }

    // 🔥 construir prompt inteligente
    $prompt = "
    Edit this image.

    KEEP EXACT SAME PERSON.
    KEEP same face, body, identity, proportions.

    Only modify:
    $texto

    Do not change identity.
    Do not create new person.
    Maintain realism and lighting.

    High quality, photorealistic, 4k.
    ";

    // convertir imagen a base64
    $image_data = file_get_contents($image_url);
    $base64 = base64_encode($image_data);

    require_once plugin_dir_path(dirname(__FILE__,2)) . "gemini-api.php";

    $response = benditoai_call_gemini($base64, $prompt);

    if(is_wp_error($response)){
        wp_send_json_error(['message'=>'Error IA']);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
        wp_send_json_error(['message'=>'IA no devolvió imagen']);
    }

    $new_base64 = $body['candidates'][0]['content']['parts'][0]['inlineData']['data'];

    $image = base64_decode($new_base64);

    $upload = wp_upload_dir();

    $filename = 'modelo_edit_' . time() . '.png';
    $path = $upload['path'].'/'.$filename;

    file_put_contents($path, $image);

    $new_url = $upload['url'].'/'.$filename;

    // 🔥 REEMPLAZAR MODELO (NO crear nuevo)
    $wpdb->update(
        $table,
        [
            'image_url' => $new_url,
            'prompt' => $prompt
        ],
        [
            'id' => $modelo_id,
            'user_id' => $user_id
        ]
    );

    // tokens
    benditoai_use_token(1);

    wp_send_json_success([
        'image_url' => $new_url
    ]);

}