<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_benditoai_generar_modelo_ai','benditoai_generar_modelo_ai');

function benditoai_generar_modelo_ai(){

    if(!is_user_logged_in()){
        wp_send_json_error("Debes iniciar sesión");
    }

    $user_id = get_current_user_id();

    // -------------------------------------------------
    // VALIDAR LÍMITE
    // -------------------------------------------------

    $plan_data = benditoai_get_user_plan_data($user_id);
    $max_modelos = $plan_data['max_modelos'];

    global $wpdb;
    $table_name = $wpdb->prefix . 'benditoai_modelos_ai';

    $total_modelos = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d",
            $user_id
        )
    );

    if($total_modelos >= $max_modelos){
        wp_send_json_error([
            'message' => 'Has alcanzado el límite de modelos de tu plan.',
            'code' => 'limit_reached'
        ]);
    }

    // -------------------------------------------------
    // DATOS
    // -------------------------------------------------

    $genero = sanitize_text_field($_POST['genero']);
    $edad = sanitize_text_field($_POST['edad']);
    $cuerpo = sanitize_text_field($_POST['cuerpo']);
    $etnia = sanitize_text_field($_POST['etnia']);
    $estilo = sanitize_text_field($_POST['estilo']);

    $prenda_superior = sanitize_textarea_field($_POST['prenda_superior']);
    $prenda_inferior = sanitize_textarea_field($_POST['prenda_inferior']);
    $zapatos = sanitize_textarea_field($_POST['zapatos']);
    $accesorios = sanitize_textarea_field($_POST['accesorios']);

    $nombre_modelo = sanitize_text_field($_POST['nombre_modelo']);

    // -------------------------------------------------
    // PROMPT (FORZADO 9:16)
    // -------------------------------------------------

    $prompt = "

Ultra realistic human avatar.

Single person only.

Gender: $genero
Age: $edad
Body type: $cuerpo
Ethnicity: $etnia
Style: $estilo

OUTFIT DESCRIPTION

Upper clothing:
$prenda_superior

Lower clothing:
$prenda_inferior

Shoes:
$zapatos

Accessories:
$accesorios

POSE

full body standing pose
person centered
head to feet visible

ENVIRONMENT

clean studio background
soft studio lighting
white background

IMPORTANT RULES

only ONE person
no group
no multiple people
no objects
no furniture

portrait composition
high detail
photorealistic
4k quality

CRITICAL:
vertical image (9:16 aspect ratio)
full body centered
no cropping
subject fully visible head to feet
";

    // -------------------------------------------------
    // IA
    // -------------------------------------------------

    $response = benditoai_call_gemini_text($prompt);

    if(is_wp_error($response)){
        wp_send_json_error("Error llamando IA");
    }

    $body = json_decode(wp_remote_retrieve_body($response),true);

    $image_base64 = null;

    if(isset($body['candidates'][0]['content']['parts'])){
        foreach($body['candidates'][0]['content']['parts'] as $part){
            if(isset($part['inlineData']['data'])){
                $image_base64 = $part['inlineData']['data'];
                break;
            }
        }
    }

    if(!$image_base64){
        wp_send_json_error("La IA no devolvió imagen");
    }

    // -------------------------------------------------
    // 🔥 NORMALIZAR IMAGEN A 9:16 (CLAVE)
    // -------------------------------------------------

    $image = base64_decode($image_base64);

    $src = imagecreatefromstring($image);

    if(!$src){
        wp_send_json_error("Error procesando imagen");
    }

    // tamaño final 9:16
    $final_width = 1024;
    $final_height = 1792;

    $dst = imagecreatetruecolor($final_width, $final_height);

    // fondo blanco
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);

    $width = imagesx($src);
    $height = imagesy($src);

    // escala proporcional
    $scale = min($final_width / $width, $final_height / $height);

    $new_width = $width * $scale;
    $new_height = $height * $scale;

    // centrar
    $x = ($final_width - $new_width) / 2;
    $y = ($final_height - $new_height) / 2;

    imagecopyresampled(
        $dst, $src,
        $x, $y, 0, 0,
        $new_width, $new_height,
        $width, $height
    );

    // guardar
    $upload = wp_upload_dir();

    $filename = 'modelo_' . time() . '.jpg';
    $path = $upload['path'] . '/' . $filename;

    imagejpeg($dst, $path, 90);

    imagedestroy($src);
    imagedestroy($dst);

    $url = $upload['url'] . '/' . $filename;

    // -------------------------------------------------
    // FECHA
    // -------------------------------------------------

    $created_at = current_time('mysql');

    // -------------------------------------------------
    // INSERT
    // -------------------------------------------------

    $wpdb->insert(
        $table_name,
        [
            'user_id' => $user_id,
            'nombre_modelo' => $nombre_modelo,
            'genero' => $genero,
            'edad' => $edad,
            'cuerpo' => $cuerpo,
            'etnia' => $etnia,
            'estilo' => $estilo,
            'prenda_superior' => $prenda_superior,
            'prenda_inferior' => $prenda_inferior,
            'zapatos' => $zapatos,
            'accesorios' => $accesorios,
            'prompt' => $prompt,
            'image_url' => $url,
            'created_at' => $created_at
        ],
        [
            '%d','%s','%s','%s','%s','%s','%s',
            '%s','%s','%s','%s','%s','%s','%s'
        ]
    );

    $modelo_id = $wpdb->insert_id;

    // -------------------------------------------------
    // TOKENS
    // -------------------------------------------------

    benditoai_use_token(1);
    $tokens_restantes = benditoai_get_user_tokens($user_id);

    // -------------------------------------------------
    // RESPUESTA FINAL
    // -------------------------------------------------

    wp_send_json_success([
        'id' => $modelo_id,
        'image_url' => $url,
        'tokens' => $tokens_restantes,
        'nombre_modelo' => $nombre_modelo,
        'genero' => $genero,
        'edad' => $edad,
        'estilo' => $estilo,
        'fecha' => date('d/m/Y H:i', strtotime($created_at))
    ]);
}