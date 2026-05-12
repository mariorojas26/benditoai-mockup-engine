<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_benditoai_preview_edit_modelo', 'benditoai_preview_edit_modelo');
add_action('wp_ajax_benditoai_confirm_edit_modelo', 'benditoai_confirm_edit_modelo');

if (!function_exists('benditoai_modelos_ai_get_outfit_catalog')) {
    function benditoai_modelos_ai_get_outfit_catalog() {
        return array();
    }
}

function benditoai_modelo_get_owned_row($modelo_id, $user_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'benditoai_modelos_ai';
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND user_id = %d",
        $modelo_id,
        $user_id
    ));
}

function benditoai_modelo_read_remote_image_base64($image_url) {
    if (!$image_url || !wp_http_validate_url($image_url)) {
        return new WP_Error('invalid_image_url', 'URL de imagen invalida');
    }

    $response = wp_remote_get($image_url, array('timeout' => 35));
    if (is_wp_error($response)) {
        return new WP_Error('image_fetch_failed', 'No se pudo leer la imagen del modelo');
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    if ($code < 200 || $code >= 300 || empty($body)) {
        return new WP_Error('image_fetch_failed', 'No se pudo leer la imagen del modelo');
    }

    return base64_encode($body);
}

function benditoai_modelo_read_reference_file() {
    if (!isset($_FILES['prenda_referencia'])) {
        return null;
    }

    $file = $_FILES['prenda_referencia'];
    if (!is_array($file) || empty($file['tmp_name'])) {
        return null;
    }

    if (!empty($file['error']) && (int) $file['error'] !== UPLOAD_ERR_OK) {
        return new WP_Error('upload_error', 'No se pudo subir la prenda de referencia');
    }

    $tmp_name = (string) $file['tmp_name'];
    if (!is_uploaded_file($tmp_name)) {
        return new WP_Error('upload_error', 'Archivo de referencia invalido');
    }

    $allowed_mimes = array('image/jpeg', 'image/png', 'image/webp');
    $check = wp_check_filetype_and_ext($tmp_name, $file['name']);
    $mime = isset($check['type']) ? (string) $check['type'] : '';

    if ($mime === '' || !in_array($mime, $allowed_mimes, true)) {
        return new WP_Error('invalid_type', 'La prenda de referencia debe ser JPG, PNG o WEBP');
    }

    $raw = file_get_contents($tmp_name);
    if ($raw === false || $raw === '') {
        return new WP_Error('read_error', 'No se pudo procesar la prenda de referencia');
    }

    return array(
        'mime' => $mime,
        'data' => base64_encode($raw),
    );
}

function benditoai_modelo_get_catalog_outfit_reference() {
    $catalog_id = isset($_POST['outfit_catalog_id']) ? sanitize_key(wp_unslash($_POST['outfit_catalog_id'])) : '';
    if ($catalog_id === '') {
        return null;
    }

    $catalog = benditoai_modelos_ai_get_outfit_catalog();
    if (!is_array($catalog) || empty($catalog)) {
        return null;
    }

    foreach ($catalog as $item) {
        if (!is_array($item)) {
            continue;
        }
        $id = isset($item['id']) ? sanitize_key((string) $item['id']) : '';
        if ($id === '' || $id !== $catalog_id) {
            continue;
        }

        $reference_url = isset($item['reference_url']) ? esc_url_raw((string) $item['reference_url']) : '';
        $label = isset($item['name']) ? sanitize_text_field((string) $item['name']) : 'Outfit';
        $hint = isset($item['prompt_hint']) ? sanitize_text_field((string) $item['prompt_hint']) : '';
        if ($reference_url === '') {
            return null;
        }

        return array(
            'id' => $id,
            'name' => $label,
            'reference_url' => $reference_url,
            'prompt_hint' => $hint,
        );
    }

    return null;
}

function benditoai_modelo_read_catalog_image_base64($image_url) {
    if (!$image_url || !wp_http_validate_url($image_url)) {
        return new WP_Error('invalid_catalog_url', 'Outfit de referencia invalido');
    }

    $response = wp_remote_get($image_url, array('timeout' => 35));
    if (is_wp_error($response)) {
        return new WP_Error('catalog_fetch_failed', 'No se pudo cargar el outfit de referencia');
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $mime = (string) wp_remote_retrieve_header($response, 'content-type');
    if ($code < 200 || $code >= 300 || empty($body)) {
        return new WP_Error('catalog_fetch_failed', 'No se pudo cargar el outfit de referencia');
    }

    if ($mime === '') {
        $mime = 'image/png';
    }
    if (strpos($mime, ';') !== false) {
        $mime = trim(explode(';', $mime)[0]);
    }

    $allowed_mimes = array('image/jpeg', 'image/png', 'image/webp');
    if (!in_array($mime, $allowed_mimes, true)) {
        $mime = 'image/png';
    }

    return array(
        'mime' => $mime,
        'data' => base64_encode($body),
    );
}

function benditoai_modelo_get_selected_style_context() {
    $selected_style_id = isset($_POST['selected_style_id']) ? sanitize_key(wp_unslash($_POST['selected_style_id'])) : '';
    $selected_style = isset($_POST['selected_style']) ? sanitize_text_field(wp_unslash($_POST['selected_style'])) : '';
    $selected_style = trim($selected_style);

    $catalog = benditoai_modelos_ai_get_outfit_catalog();
    if (!is_array($catalog) || empty($catalog)) {
        return $selected_style !== '' ? array('label' => $selected_style, 'hint' => '') : null;
    }

    foreach ($catalog as $item) {
        if (!is_array($item)) {
            continue;
        }
        $id = isset($item['id']) ? sanitize_key((string) $item['id']) : '';
        if ($selected_style_id === '' || $id !== $selected_style_id) {
            continue;
        }

        return array(
            'label' => isset($item['name']) ? sanitize_text_field((string) $item['name']) : $selected_style,
            'hint' => isset($item['prompt_hint']) ? sanitize_text_field((string) $item['prompt_hint']) : '',
        );
    }

    return $selected_style !== '' ? array('label' => $selected_style, 'hint' => '') : null;
}

function benditoai_modelo_build_edit_prompt($texto, $has_reference, $reference_context = '') {
    $reference_rules = $has_reference
        ? "A second image is attached as REFERENCE GARMENT.\nUse that exact reference garment to replace ONLY the garment requested.\nCopy the reference garment faithfully: type, structure, material, color and key details.\nDo NOT transfer reference attributes to any other garment."
        : "No reference garment image is attached.\nApply only the user text instruction to the requested garment.";

    $context_rules = $reference_context !== ''
        ? "Reference hint: {$reference_context}\n"
        : '';

    return "Edit this image.\n\nKEEP EXACT SAME PERSON.\nKEEP same face, body, identity, proportions.\nKEEP same pose, background, lighting, framing, and camera angle.\n\nLOCK all clothing and elements in the image.\n\nUser change request:\n$texto\n\n$reference_rules\n$context_rules\nStrict editing rules:\n- Do NOT change any other clothing items\n- Do NOT remove, replace, or restyle any existing garments outside the requested change\n- Do NOT modify colors, textures, or fit of other clothes\n- Do NOT change hairstyle, expression, or skin tone\n- Do NOT alter body shape or proportions\n- Do NOT reinterpret the outfit\n\nThe change must be isolated ONLY to the requested garment.\n\nIf the request refers to shoes:\n- Replace ONLY the footwear\n- Keep pants exactly the same (no length change, no overlap issues)\n- Ensure natural contact with the ground\n- Match perspective and lighting perfectly\n\nPreserve full realism:\n- Maintain shadows, lighting direction, and reflections\n- Match textures and materials accurately\n- Keep the edit seamless and natural\n\nDo not generate a new image from scratch.\nDo not redesign the scene.\nOnly perform a precise in-place edit.\n\nHigh quality, photorealistic, 4K.";
}

function benditoai_preview_edit_modelo() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'No autorizado'));
    }

    $user_id = get_current_user_id();
    $modelo_id = isset($_POST['modelo_id']) ? (int) $_POST['modelo_id'] : 0;
    $texto = isset($_POST['texto']) ? sanitize_textarea_field(wp_unslash($_POST['texto'])) : '';

    if ($modelo_id <= 0) {
        wp_send_json_error(array('message' => 'Modelo invalido'));
    }
    if ($texto === '') {
        wp_send_json_error(array('message' => 'Debes indicar que deseas editar'));
    }

    $modelo = benditoai_modelo_get_owned_row($modelo_id, $user_id);
    if (!$modelo) {
        wp_send_json_error(array('message' => 'Modelo no valido'));
    }

    $base_image_url = isset($modelo->image_url) ? (string) $modelo->image_url : '';
    if ($base_image_url === '') {
        wp_send_json_error(array('message' => 'Modelo sin imagen'));
    }

    $main_base64 = benditoai_modelo_read_remote_image_base64($base_image_url);
    if (is_wp_error($main_base64)) {
        wp_send_json_error(array('message' => $main_base64->get_error_message()));
    }

    $reference_image = benditoai_modelo_read_reference_file();
    if (is_wp_error($reference_image)) {
        wp_send_json_error(array('message' => $reference_image->get_error_message()));
    }

    $selected_style_context = benditoai_modelo_get_selected_style_context();

    $extra_images = array();
    $reference_context = '';

    if (is_array($reference_image) && !empty($reference_image['data']) && !empty($reference_image['mime'])) {
        $extra_images[] = $reference_image;
    }

    if (is_array($selected_style_context)) {
        $style_label = trim((string) ($selected_style_context['label'] ?? ''));
        $style_hint = trim((string) ($selected_style_context['hint'] ?? ''));
        if ($style_hint !== '') {
            $reference_context = $style_hint;
        } elseif ($style_label !== '') {
            $reference_context = 'Preferred style selected by user: ' . $style_label . '. Keep this style direction while respecting the exact garment change request.';
        }
    }

    $has_reference = !empty($extra_images);
    $prompt = benditoai_modelo_build_edit_prompt($texto, $has_reference, $reference_context);

    require_once BENDIDOAI_PLUGIN_PATH . 'includes/services/gemini/gemini-api.php';
    $response = benditoai_call_gemini($main_base64, $prompt, $extra_images);
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Error IA'));
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $new_base64 = $body['candidates'][0]['content']['parts'][0]['inlineData']['data'] ?? '';
    if ($new_base64 === '') {
        wp_send_json_error(array('message' => 'IA no devolvio imagen'));
    }

    $image_binary = base64_decode($new_base64);
    if ($image_binary === false || $image_binary === '') {
        wp_send_json_error(array('message' => 'No se pudo procesar imagen editada'));
    }

    $upload = wp_upload_dir();
    $filename = 'modelo_edit_preview_' . time() . '_' . wp_generate_password(6, false, false) . '.png';
    $path = trailingslashit($upload['path']) . $filename;
    $saved = file_put_contents($path, $image_binary);
    if ($saved === false) {
        wp_send_json_error(array('message' => 'No se pudo guardar preview'));
    }

    $preview_url = trailingslashit($upload['url']) . $filename;
    $preview_token = wp_generate_password(18, false, false);
    $transient_key = 'benditoai_edit_preview_' . $user_id . '_' . $modelo_id . '_' . $preview_token;

    set_transient($transient_key, array(
        'preview_url' => $preview_url,
        'prompt' => $prompt,
        'base_image_url' => $base_image_url,
        'created_at' => time(),
    ), HOUR_IN_SECONDS);

    // Cobro inmediato: si ya se genero una imagen nueva, el token se consume
    // aunque luego el usuario decida conservarla o deshacerla.
    benditoai_use_token(1);

    wp_send_json_success(array(
        'preview_url' => $preview_url,
        'preview_token' => $preview_token,
        'base_image_url' => $base_image_url,
    ));
}

function benditoai_confirm_edit_modelo() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'No autorizado'));
    }

    $user_id = get_current_user_id();
    $modelo_id = isset($_POST['modelo_id']) ? (int) $_POST['modelo_id'] : 0;
    $preview_token = isset($_POST['preview_token']) ? sanitize_text_field(wp_unslash($_POST['preview_token'])) : '';
    $decision = isset($_POST['decision']) ? sanitize_text_field(wp_unslash($_POST['decision'])) : '';

    if ($modelo_id <= 0 || $preview_token === '') {
        wp_send_json_error(array('message' => 'Solicitud invalida'));
    }

    $modelo = benditoai_modelo_get_owned_row($modelo_id, $user_id);
    if (!$modelo) {
        wp_send_json_error(array('message' => 'Modelo no valido'));
    }

    $transient_key = 'benditoai_edit_preview_' . $user_id . '_' . $modelo_id . '_' . $preview_token;
    $preview_data = get_transient($transient_key);
    if (!is_array($preview_data) || empty($preview_data['preview_url'])) {
        wp_send_json_error(array('message' => 'La previsualizacion expiro. Intenta de nuevo.'));
    }

    if ($decision === 'apply') {
        global $wpdb;
        $table = $wpdb->prefix . 'benditoai_modelos_ai';
        $wpdb->update(
            $table,
            array(
                'image_url' => (string) $preview_data['preview_url'],
                'prompt' => isset($preview_data['prompt']) ? (string) $preview_data['prompt'] : '',
            ),
            array(
                'id' => $modelo_id,
                'user_id' => $user_id,
            )
        );
    }

    delete_transient($transient_key);

    wp_send_json_success(array(
        'decision' => $decision,
    ));
}
