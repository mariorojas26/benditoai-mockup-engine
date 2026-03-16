<?php

if (!defined('ABSPATH')) {
    exit;
}

function benditoai_trending_generate() {

    if (!is_user_logged_in()) {
        wp_send_json_error("Debes iniciar sesión.");
    }

    $user_id = get_current_user_id();

    if (!benditoai_user_has_tokens($user_id,1)) {
        wp_send_json_error("No tienes tokens.");
    }

    if (!isset($_FILES['imagen'])) {
        wp_send_json_error("No se recibió imagen.");
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $uploadedfile = $_FILES['imagen'];

    $movefile = wp_handle_upload($uploadedfile,['test_form'=>false]);

    if (!$movefile) {
        wp_send_json_error("Error subiendo imagen.");
    }

    $image_data = file_get_contents($movefile['file']);
    $base64 = base64_encode($image_data);

    /**
     * 🔥 PROMPT TENDENCIA
     * AQUÍ CAMBIAS CADA MES
     */

    $prompt = "Ultra realistic cinematic selfie in vertical 9:16 format using a wide fisheye smartphone lens. Use the uploaded image strictly as the identity reference for the person’s face. The person must remain clearly recognizable as the same individual from the reference photo. Preserve the exact facial structure, proportions, natural asymmetry, eyes, nose, mouth, skin tone, glasses and any facial accessories visible in the reference image. Do not redesign, beautify, stylize or symmetrize the face. Preserve all natural imperfections and subtle asymmetries exactly as they appear. The face angle must remain the same as in the reference image. Do not rotate the head into a new perspective and do not reinterpret the facial structure from a different angle. Keep the same head orientation and facial viewpoint so the identity remains accurate. However, lighting, color, shadows and reflections should adapt naturally to the environment so the subject blends realistically with the scene. The person is taking a casual cinematic selfie in a dim living room with warm lamp lighting and toys on the floor, creating a playful horror-movie atmosphere. Behind the person, Chucky and Tiffany Valentine lean slightly into the frame with mischievous expressions. High camera angle selfie with fisheye distortion typical of a smartphone lens. Ultra detailed photorealistic face, cinematic color grading, shallow depth of field, dramatic lighting, modern smartphone camera look.";

    $response = benditoai_call_gemini($base64,$prompt);

    if (is_wp_error($response)) {
        wp_send_json_error("Error IA.");
    }

    $body = json_decode(wp_remote_retrieve_body($response),true);

    $image_base64 = $body['candidates'][0]['content']['parts'][0]['inlineData']['data'] ?? null;

    if (!$image_base64) {
        wp_send_json_error("La IA no devolvió imagen.");
    }

    $image = base64_decode($image_base64);

    $upload = wp_upload_dir();

    $filename = 'trend_' . time() . '.png';

    $path = $upload['path'].'/'.$filename;

    file_put_contents($path,$image);

    $url = $upload['url'].'/'.$filename;

    benditoai_decrease_tokens($user_id,1);

    wp_send_json_success([
        'image_url'=>$url
    ]);

}

add_action('wp_ajax_benditoai_trending_generate','benditoai_trending_generate');