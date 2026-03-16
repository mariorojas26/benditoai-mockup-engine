<?php
if (!defined('ABSPATH')) exit;

/**
 * AJAX: Mejorar imagen con Gemini
 */

function benditoai_enhance_image() {

    if (!is_user_logged_in()) {
        wp_send_json_error("No autorizado");
    }

    if (!isset($_FILES['imagen'])) {
        wp_send_json_error("No se recibió imagen.");
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $uploadedfile = $_FILES['imagen'];

    $movefile = wp_handle_upload($uploadedfile, ['test_form' => false]);

    if (!$movefile || isset($movefile['error'])) {
        wp_send_json_error("Error subiendo imagen.");
    }

    $upload_dir = wp_upload_dir();
    $input_path  = $movefile['file'];

    /**
     * Convertir imagen a base64
     */

    $image_data = file_get_contents($input_path);
    $base64_image = base64_encode($image_data);

    /**
     * Prompt para mejorar imagen
     */

    $prompt = "Professional photo enhancement. Using the uploaded image as reference, create an improved version of the same photo while preserving the exact subject and composition. Enhance sharpness, texture details, lighting, contrast, and color balance. Correct exposure and white balance. Improve dynamic range and shadows while keeping natural highlights. Reduce noise, blur, and compression artifacts. Make the image look like it was edited by a professional photographer. Maintain realistic skin tones, natural lighting, and photographic realism. Do not modify the subject, pose, objects, or environment. Output a high-resolution photorealistic image with superior clarity and color quality. Ultra detailed, 4k quality, professional photography, natural lighting, high dynamic range.";

    /**
     * Consumir API Gemini
     */

    require_once plugin_dir_path(dirname(__FILE__,2)) . "gemini-api.php";

    $response = benditoai_call_gemini($base64_image, $prompt);

    if (is_wp_error($response)) {
        wp_send_json_error("Error llamando a Gemini.");
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
        wp_send_json_error("Gemini no devolvió imagen.");
    }

    /**
     * Guardar imagen generada
     */

    $generated_base64 = $body['candidates'][0]['content']['parts'][0]['inlineData']['data'];

    $image_data = base64_decode($generated_base64);

    $output_filename = 'enhanced_' . time() . '.png';

    $output_path = $upload_dir['path'] . '/' . $output_filename;

    file_put_contents($output_path, $image_data);

    $url = $upload_dir['url'] . '/' . $output_filename;

    /**
     * Descontar token SOLO si todo salió bien
     */

    benditoai_use_token(1);

    wp_send_json_success([
        'image_url' => $url
    ]);

}

add_action('wp_ajax_benditoai_enhance_image', 'benditoai_enhance_image');