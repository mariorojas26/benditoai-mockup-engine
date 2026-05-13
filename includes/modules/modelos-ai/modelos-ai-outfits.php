<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_benditoai_save_modelo_outfit', 'benditoai_save_modelo_outfit');
add_action('wp_ajax_benditoai_delete_modelo_outfit', 'benditoai_delete_modelo_outfit');
add_action('wp_ajax_benditoai_rename_modelo_outfit', 'benditoai_rename_modelo_outfit');
add_action('wp_ajax_benditoai_preview_edit_modelo_outfit', 'benditoai_preview_edit_modelo_outfit');
add_action('wp_ajax_benditoai_confirm_edit_modelo_outfit', 'benditoai_confirm_edit_modelo_outfit');

function benditoai_modelo_outfits_table() {
    global $wpdb;
    return $wpdb->prefix . 'benditoai_modelos_ai_outfits';
}

function benditoai_modelo_outfit_plan_key($user_id) {
    if (function_exists('benditoai_get_user_plan_key')) {
        return benditoai_get_user_plan_key($user_id);
    }

    return 'starter';
}

function benditoai_modelo_outfit_limit($user_id) {
    if (function_exists('benditoai_get_user_plan_data')) {
        $plan_data = benditoai_get_user_plan_data($user_id);
        return max(0, (int) ($plan_data['max_outfits'] ?? 1));
    }

    return 1;
}

function benditoai_modelo_outfit_warning() {
    return 'Has alcanzado el límite de outfits para este modelo.';
}

function benditoai_modelo_outfit_get_owned_model($modelo_id, $user_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'benditoai_modelos_ai';

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND user_id = %d",
        $modelo_id,
        $user_id
    ));
}

function benditoai_modelo_outfit_get_owned_outfit($outfit_id, $user_id) {
    global $wpdb;
    $table = benditoai_modelo_outfits_table();

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND user_id = %d",
        $outfit_id,
        $user_id
    ));
}

function benditoai_modelo_outfit_count($modelo_id, $user_id) {
    global $wpdb;
    $table = benditoai_modelo_outfits_table();

    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE modelo_id = %d AND user_id = %d",
        $modelo_id,
        $user_id
    ));
}

function benditoai_modelo_outfit_stats($modelo_id, $user_id) {
    $count = benditoai_modelo_outfit_count($modelo_id, $user_id);
    $limit = benditoai_modelo_outfit_limit($user_id);

    return array(
        'count' => $count,
        'limit' => $limit,
        'can_save' => $count < $limit,
        'warning' => $count >= $limit ? benditoai_modelo_outfit_warning() : '',
    );
}

function benditoai_modelo_outfit_response_item($outfit) {
    return array(
        'id' => (int) $outfit->id,
        'modelo_id' => (int) $outfit->modelo_id,
        'nombre_outfit' => (string) $outfit->nombre_outfit,
        'image_url' => (string) $outfit->image_url,
        'created_at' => (string) ($outfit->created_at ?? ''),
        'updated_at' => (string) ($outfit->updated_at ?? ''),
    );
}

function benditoai_modelos_ai_get_saved_outfits_grouped($user_id, $modelo_ids = array()) {
    global $wpdb;
    $table = benditoai_modelo_outfits_table();
    $modelo_ids = array_values(array_filter(array_map('intval', (array) $modelo_ids)));

    if (empty($modelo_ids)) {
        return array();
    }

    $placeholders = implode(',', array_fill(0, count($modelo_ids), '%d'));
    $params = array_merge(array($user_id), $modelo_ids);
    $query = "SELECT * FROM $table WHERE user_id = %d AND modelo_id IN ($placeholders) ORDER BY created_at DESC, id DESC";
    array_unshift($params, $query);
    $sql = call_user_func_array(array($wpdb, 'prepare'), $params);

    $rows = $wpdb->get_results($sql);
    $grouped = array();

    foreach ((array) $rows as $row) {
        $modelo_id = (int) $row->modelo_id;
        if (!isset($grouped[$modelo_id])) {
            $grouped[$modelo_id] = array();
        }
        $grouped[$modelo_id][] = $row;
    }

    return $grouped;
}

function benditoai_save_modelo_outfit() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'No autorizado'));
    }

    $user_id = get_current_user_id();
    $modelo_id = isset($_POST['modelo_id']) ? (int) $_POST['modelo_id'] : 0;

    if ($modelo_id <= 0) {
        wp_send_json_error(array('message' => 'Modelo invalido'));
    }

    $modelo = benditoai_modelo_outfit_get_owned_model($modelo_id, $user_id);
    if (!$modelo || empty($modelo->image_url)) {
        wp_send_json_error(array('message' => 'Modelo no valido'));
    }

    $stats = benditoai_modelo_outfit_stats($modelo_id, $user_id);
    if (!$stats['can_save']) {
        wp_send_json_error(array(
            'message' => benditoai_modelo_outfit_warning(),
            'stats' => $stats,
        ));
    }

    global $wpdb;
    $table = benditoai_modelo_outfits_table();
    $next_number = $stats['count'] + 1;
    $model_name = trim((string) ($modelo->nombre_modelo ?? 'Modelo AI'));
    $name = sprintf('%s Outfit(%d)', $model_name !== '' ? $model_name : 'Modelo AI', $next_number);
    $now = current_time('mysql');

    $inserted = $wpdb->insert(
        $table,
        array(
            'user_id' => $user_id,
            'modelo_id' => $modelo_id,
            'nombre_outfit' => $name,
            'image_url' => (string) $modelo->image_url,
            'prompt' => isset($modelo->prompt) ? (string) $modelo->prompt : '',
            'created_at' => $now,
            'updated_at' => $now,
        ),
        array('%d', '%d', '%s', '%s', '%s', '%s', '%s')
    );

    if (!$inserted) {
        wp_send_json_error(array('message' => 'No se pudo guardar el outfit'));
    }

    $outfit = benditoai_modelo_outfit_get_owned_outfit((int) $wpdb->insert_id, $user_id);
    if (!$outfit) {
        wp_send_json_error(array('message' => 'No se pudo cargar el outfit guardado'));
    }

    wp_send_json_success(array(
        'outfit' => benditoai_modelo_outfit_response_item($outfit),
        'stats' => benditoai_modelo_outfit_stats($modelo_id, $user_id),
    ));
}

function benditoai_delete_modelo_outfit() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'No autorizado'));
    }

    $user_id = get_current_user_id();
    $outfit_id = isset($_POST['outfit_id']) ? (int) $_POST['outfit_id'] : 0;

    if ($outfit_id <= 0) {
        wp_send_json_error(array('message' => 'Outfit invalido'));
    }

    $outfit = benditoai_modelo_outfit_get_owned_outfit($outfit_id, $user_id);
    if (!$outfit) {
        wp_send_json_error(array('message' => 'Outfit no valido'));
    }

    global $wpdb;
    $deleted = $wpdb->delete(
        benditoai_modelo_outfits_table(),
        array(
            'id' => $outfit_id,
            'user_id' => $user_id,
        ),
        array('%d', '%d')
    );

    if (!$deleted) {
        wp_send_json_error(array('message' => 'No se pudo eliminar el outfit'));
    }

    wp_send_json_success(array(
        'stats' => benditoai_modelo_outfit_stats((int) $outfit->modelo_id, $user_id),
    ));
}

function benditoai_rename_modelo_outfit() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'No autorizado'));
    }

    $user_id = get_current_user_id();
    $outfit_id = isset($_POST['outfit_id']) ? (int) $_POST['outfit_id'] : 0;
    $name = isset($_POST['nombre_outfit']) ? sanitize_text_field(wp_unslash($_POST['nombre_outfit'])) : '';
    $name = trim($name);

    if ($outfit_id <= 0 || $name === '') {
        wp_send_json_error(array('message' => 'Nombre de outfit invalido'));
    }

    $outfit = benditoai_modelo_outfit_get_owned_outfit($outfit_id, $user_id);
    if (!$outfit) {
        wp_send_json_error(array('message' => 'Outfit no valido'));
    }

    $name = substr($name, 0, 100);

    global $wpdb;
    $updated = $wpdb->update(
        benditoai_modelo_outfits_table(),
        array(
            'nombre_outfit' => $name,
            'updated_at' => current_time('mysql'),
        ),
        array(
            'id' => $outfit_id,
            'user_id' => $user_id,
        ),
        array('%s', '%s'),
        array('%d', '%d')
    );

    if ($updated === false) {
        wp_send_json_error(array('message' => 'No se pudo renombrar el outfit'));
    }

    wp_send_json_success(array(
        'id' => $outfit_id,
        'nombre_outfit' => $name,
    ));
}

function benditoai_preview_edit_modelo_outfit() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'No autorizado'));
    }

    $user_id = get_current_user_id();
    $outfit_id = isset($_POST['outfit_id']) ? (int) $_POST['outfit_id'] : 0;
    $texto = isset($_POST['texto']) ? sanitize_textarea_field(wp_unslash($_POST['texto'])) : '';

    if ($outfit_id <= 0) {
        wp_send_json_error(array('message' => 'Outfit invalido'));
    }
    if ($texto === '') {
        wp_send_json_error(array('message' => 'Debes indicar que deseas editar'));
    }

    $outfit = benditoai_modelo_outfit_get_owned_outfit($outfit_id, $user_id);
    if (!$outfit || empty($outfit->image_url)) {
        wp_send_json_error(array('message' => 'Outfit no valido'));
    }

    $main_base64 = benditoai_modelo_read_remote_image_base64((string) $outfit->image_url);
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

    $prompt = benditoai_modelo_build_edit_prompt($texto, !empty($extra_images), $reference_context);

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
    $filename = 'modelo_outfit_preview_' . time() . '_' . wp_generate_password(6, false, false) . '.png';
    $path = trailingslashit($upload['path']) . $filename;
    $saved = file_put_contents($path, $image_binary);
    if ($saved === false) {
        wp_send_json_error(array('message' => 'No se pudo guardar preview'));
    }

    $preview_url = trailingslashit($upload['url']) . $filename;
    $preview_token = wp_generate_password(18, false, false);
    $transient_key = 'benditoai_outfit_preview_' . $user_id . '_' . $outfit_id . '_' . $preview_token;

    set_transient($transient_key, array(
        'preview_url' => $preview_url,
        'prompt' => $prompt,
        'base_image_url' => (string) $outfit->image_url,
        'created_at' => time(),
    ), HOUR_IN_SECONDS);

    benditoai_use_token(1);

    wp_send_json_success(array(
        'preview_url' => $preview_url,
        'preview_token' => $preview_token,
        'base_image_url' => (string) $outfit->image_url,
    ));
}

function benditoai_confirm_edit_modelo_outfit() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'No autorizado'));
    }

    $user_id = get_current_user_id();
    $outfit_id = isset($_POST['outfit_id']) ? (int) $_POST['outfit_id'] : 0;
    $preview_token = isset($_POST['preview_token']) ? sanitize_text_field(wp_unslash($_POST['preview_token'])) : '';
    $decision = isset($_POST['decision']) ? sanitize_text_field(wp_unslash($_POST['decision'])) : '';

    if ($outfit_id <= 0 || $preview_token === '') {
        wp_send_json_error(array('message' => 'Solicitud invalida'));
    }

    $outfit = benditoai_modelo_outfit_get_owned_outfit($outfit_id, $user_id);
    if (!$outfit) {
        wp_send_json_error(array('message' => 'Outfit no valido'));
    }

    $transient_key = 'benditoai_outfit_preview_' . $user_id . '_' . $outfit_id . '_' . $preview_token;
    $preview_data = get_transient($transient_key);
    if (!is_array($preview_data) || empty($preview_data['preview_url'])) {
        wp_send_json_error(array('message' => 'La previsualizacion expiro. Intenta de nuevo.'));
    }

    if ($decision === 'apply') {
        global $wpdb;
        $wpdb->update(
            benditoai_modelo_outfits_table(),
            array(
                'image_url' => (string) $preview_data['preview_url'],
                'prompt' => isset($preview_data['prompt']) ? (string) $preview_data['prompt'] : '',
                'updated_at' => current_time('mysql'),
            ),
            array(
                'id' => $outfit_id,
                'user_id' => $user_id,
            ),
            array('%s', '%s', '%s'),
            array('%d', '%d')
        );
    }

    delete_transient($transient_key);

    wp_send_json_success(array(
        'decision' => $decision,
        'image_url' => $decision === 'apply' ? (string) $preview_data['preview_url'] : (string) $outfit->image_url,
    ));
}
