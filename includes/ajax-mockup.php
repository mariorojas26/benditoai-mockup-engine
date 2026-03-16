<?php

if (!defined('ABSPATH')) exit;

require_once BENDIDOAI_PLUGIN_PATH . 'includes/gemini-api.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/prompts.php';

function benditoai_generar_mockup() {

    if (!is_user_logged_in()) {
        wp_send_json_error("No autorizado");
    }

    $user_id = get_current_user_id();

    /**
     * VERIFICAR CRÉDITOS
     */
    if (!benditoai_user_has_tokens($user_id, 1)) {
        wp_send_json_error("No tienes créditos disponibles.");
    }

    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    // 1️⃣ Recibir variables desde el formulario
    $producto = isset($_POST['producto']) ? sanitize_text_field($_POST['producto']) : 'mug';

    $formato  = isset($_POST['formato']) ? sanitize_text_field($_POST['formato']) : 'instagram';

    $color    = isset($_POST['color']) ? sanitize_text_field($_POST['color']) : 'blanco';

   // Solo para camisetas
    $estilo_camiseta = '';
    if ($producto === 'camiseta' && isset($_POST['estilo_camiseta'])) {
        $estilo_camiseta_input = sanitize_text_field($_POST['estilo_camiseta']);
        global $benditoai_estilos_camiseta;
        // Si existe en el array, usamos la descripción; si no, usamos literal
        $estilo_camiseta = isset($benditoai_estilos_camiseta[$estilo_camiseta_input]) 
                            ? $benditoai_estilos_camiseta[$estilo_camiseta_input] 
                            : $estilo_camiseta_input;
    }

    // entorno
    global $benditoai_entornos;
    $entorno = isset($_POST['entorno']) ? sanitize_text_field($_POST['entorno']) : 'urbano';
    // Si existe en el array, usamos la descripción; si no, usamos literal
    $entorno_texto = isset($benditoai_entornos[$entorno]) ? $benditoai_entornos[$entorno] : $entorno;

    // Solo para mockup con modelo
        $modelo_texto = '';
        if (isset($_POST['modelo'])) {
            $modelo_input = sanitize_text_field($_POST['modelo']);

            global $benditoai_modelo;

            // Ajuste para que siempre sea claro si se usa modelo o no
            $modelo_texto = ($modelo_input === 'no') 
                ? "No incluir personas; solo el mockup del producto." 
                : "Incluir un modelo humano realista mostrando la camiseta.";
        }

    // 2️⃣ Subir imagen
    if (!isset($_FILES['diseno'])) {
        wp_send_json_error("No se recibió ninguna imagen.");
    }

    $uploadedfile = $_FILES['diseno'];
    $movefile = wp_handle_upload($uploadedfile, array('test_form' => false));

    if (!$movefile || isset($movefile['error'])) {
        wp_send_json_error("Error subiendo imagen");
    }

    $image_data = file_get_contents($movefile['file']);
    $base64_image = base64_encode($image_data);

    // 3️⃣ Obtener prompt dinámico desde includes/prompts.php
    $prompt = benditoai_get_prompt($producto, $formato, $color, $estilo_camiseta, $entorno_texto, $modelo_texto);

    // 4️⃣ Llamar a Gemini con prompt dinámico
    $response = benditoai_call_gemini($base64_image, $prompt);

    if (is_wp_error($response)) {
        wp_send_json_error("Error llamando a Gemini");
    }

    $body_response = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body_response['candidates'][0]['content']['parts'][0]['inlineData']['data'])) {
        wp_send_json_error("No se encontró imagen en respuesta.");
    }

    // 5️⃣ Decodificar y guardar imagen en uploads
    $generated_base64 = $body_response['candidates'][0]['content']['parts'][0]['inlineData']['data'];
    $generated_image_data = base64_decode($generated_base64);

    $upload_dir = wp_upload_dir();
    $filename   = 'mockup_' . time() . '.png';
    $path       = $upload_dir['path'] . '/' . $filename;

    file_put_contents($path, $generated_image_data);

    $url = $upload_dir['url'] . '/' . $filename;

    // 5.1️⃣ Guardar historial en la DB
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'benditoai_historial',
        array(
            'user_id' => get_current_user_id(),
            'producto' => $producto,
            'color' => $color,
            'estilo_camiseta' => $estilo_camiseta,
            'modelo' => isset($_POST['modelo']) ? sanitize_text_field($_POST['modelo']) : '',
            'entorno' => $entorno,
            'prompt' => $prompt,
            'image_url' => $url,
            'created_at' => current_time('mysql')
        ),
        array('%d','%s','%s','%s','%s','%s','%s','%s')
    );

    /**
     * DESCONTAR CRÉDITO
     */
    benditoai_decrease_tokens($user_id, 1);

    // 6️⃣ Retornar URL de la imagen generada
    wp_send_json_success(array(
        'image_url' => $url
    ));
}

// Registrar AJAX para usuarios logueados
add_action('wp_ajax_benditoai_generar_mockup', 'benditoai_generar_mockup');