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
    // 🔥 VALIDAR LÍMITE DE MODELOS SEGÚN PLAN
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
            'message' => 'Has alcanzado el límite de modelos de tu plan. Elimina un avatar o mejora tu plan para crear más.',
            'code' => 'limit_reached'
        ]);
    }

    // -------------------------------------------------
    // SANITIZAR DATOS
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

    // -------------------------------------------------
    // PROMPT
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

";

    // -------------------------------------------------
    // LLAMAR GEMINI
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
    // GUARDAR IMAGEN
    // -------------------------------------------------

    $image = base64_decode($image_base64);

    $upload = wp_upload_dir();

    $filename = 'modelo_' . time() . '.png';

    $path = $upload['path'] . '/' . $filename;

    file_put_contents($path,$image);

    $url = $upload['url'] . '/' . $filename;

    // -------------------------------------------------
    // 🔥 FECHA (FIX REAL)
    // -------------------------------------------------

    $created_at = date('Y-m-d H:i:s');

    // -------------------------------------------------
    // GUARDAR DATOS EN BD
    // -------------------------------------------------

    $wpdb->insert(
        $table_name,
        [
            'user_id' => $user_id,
            'nombre_modelo' => sanitize_text_field($_POST['nombre_modelo']),
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
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s' // 🔥 ESTE FALTABA (created_at)
        ]
    );

    // -------------------------------------------------
    // TOKENS
    // -------------------------------------------------

    benditoai_use_token(1);

    $tokens_restantes = benditoai_get_user_tokens($user_id);

    // -------------------------------------------------
    // RESPUESTA
    // -------------------------------------------------

    wp_send_json_success([
        'image_url' => $url,
        'tokens' => $tokens_restantes
    ]);

}