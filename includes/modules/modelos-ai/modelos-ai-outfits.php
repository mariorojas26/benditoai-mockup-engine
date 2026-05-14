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
    return 'Has alcanzado el limite de outfits para este modelo. Actualiza tu plan para guardar mas.';
}

function benditoai_modelo_outfit_ensure_schema() {
    static $done = false;
    if ($done) {
        return;
    }

    global $wpdb;
    $table = benditoai_modelo_outfits_table();
    $columns = $wpdb->get_col("SHOW COLUMNS FROM `$table`");
    if (!is_array($columns)) {
        $done = true;
        return;
    }

    if (!in_array('outfit_tag', $columns, true)) {
        $wpdb->query("ALTER TABLE `$table` ADD COLUMN `outfit_tag` VARCHAR(20) NOT NULL DEFAULT 'outfit' AFTER `nombre_outfit`");
    }

    if (!in_array('sort_order', $columns, true)) {
        $wpdb->query("ALTER TABLE `$table` ADD COLUMN `sort_order` INT NOT NULL DEFAULT 1000 AFTER `outfit_tag`");
    }

    $done = true;
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
    benditoai_modelo_outfit_ensure_schema();

    global $wpdb;
    $table = benditoai_modelo_outfits_table();

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND user_id = %d",
        $outfit_id,
        $user_id
    ));
}

function benditoai_modelo_outfit_default_name($modelo, $number = 1) {
    $model_name = trim((string) ($modelo->nombre_modelo ?? 'Modelo AI'));
    if ($model_name === '') {
        $model_name = 'Modelo AI';
    }
    return sprintf('%s Outfit(%d)', $model_name, max(1, (int) $number));
}

function benditoai_modelo_outfit_insert_principal($modelo, $user_id) {
    if (!$modelo || empty($modelo->image_url)) {
        return null;
    }

    benditoai_modelo_outfit_ensure_schema();

    global $wpdb;
    $table = benditoai_modelo_outfits_table();
    $now = current_time('mysql');
    $created = !empty($modelo->created_at) ? (string) $modelo->created_at : $now;

    $wpdb->insert(
        $table,
        array(
            'user_id' => (int) $user_id,
            'modelo_id' => (int) $modelo->id,
            'nombre_outfit' => benditoai_modelo_outfit_default_name($modelo, 1),
            'outfit_tag' => 'principal',
            'sort_order' => 1,
            'image_url' => (string) $modelo->image_url,
            'prompt' => isset($modelo->prompt) ? (string) $modelo->prompt : '',
            'created_at' => $created,
            'updated_at' => $now,
        ),
        array('%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
    );

    if (empty($wpdb->insert_id)) {
        return null;
    }

    return benditoai_modelo_outfit_get_owned_outfit((int) $wpdb->insert_id, $user_id);
}

function benditoai_modelo_outfit_get_principal($modelo_id, $user_id, $modelo = null) {
    benditoai_modelo_outfit_ensure_schema();

    global $wpdb;
    $table = benditoai_modelo_outfits_table();

    $principal = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE modelo_id = %d AND user_id = %d AND outfit_tag = 'principal' ORDER BY sort_order ASC, id ASC LIMIT 1",
        $modelo_id,
        $user_id
    ));

    if (!$principal) {
        if (!$modelo) {
            $modelo = benditoai_modelo_outfit_get_owned_model($modelo_id, $user_id);
        }
        if (!$modelo) {
            return null;
        }

        $match = null;
        if (!empty($modelo->image_url)) {
            $match = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE modelo_id = %d AND user_id = %d AND image_url = %s ORDER BY created_at ASC, id ASC LIMIT 1",
                $modelo_id,
                $user_id,
                (string) $modelo->image_url
            ));
        }

        if ($match) {
            $wpdb->update(
                $table,
                array(
                    'outfit_tag' => 'principal',
                    'sort_order' => 1,
                    'updated_at' => current_time('mysql'),
                ),
                array('id' => (int) $match->id, 'user_id' => (int) $user_id),
                array('%s', '%d', '%s'),
                array('%d', '%d')
            );
            $principal = benditoai_modelo_outfit_get_owned_outfit((int) $match->id, $user_id);
        } else {
            $principal = benditoai_modelo_outfit_insert_principal($modelo, $user_id);
        }
    }

    if (!$principal) {
        return null;
    }

    $wpdb->query($wpdb->prepare(
        "UPDATE $table SET outfit_tag = 'outfit', updated_at = %s WHERE modelo_id = %d AND user_id = %d AND outfit_tag = 'principal' AND id <> %d",
        current_time('mysql'),
        $modelo_id,
        $user_id,
        (int) $principal->id
    ));

    $wpdb->update(
        $table,
        array('sort_order' => 1, 'outfit_tag' => 'principal'),
        array('id' => (int) $principal->id, 'user_id' => (int) $user_id),
        array('%d', '%s'),
        array('%d', '%d')
    );

    return benditoai_modelo_outfit_get_owned_outfit((int) $principal->id, $user_id);
}

function benditoai_modelo_outfit_reindex($modelo_id, $user_id, $principal_id = 0) {
    benditoai_modelo_outfit_ensure_schema();

    global $wpdb;
    $table = benditoai_modelo_outfits_table();
    $principal_id = (int) $principal_id;

    if ($principal_id <= 0) {
        $principal = benditoai_modelo_outfit_get_principal($modelo_id, $user_id);
        $principal_id = (int) ($principal->id ?? 0);
    }

    if ($principal_id <= 0) {
        return;
    }

    $secondary = $wpdb->get_results($wpdb->prepare(
        "SELECT id FROM $table WHERE modelo_id = %d AND user_id = %d AND id <> %d ORDER BY created_at ASC, id ASC",
        $modelo_id,
        $user_id,
        $principal_id
    ));

    $order = 2;
    foreach ((array) $secondary as $row) {
        $wpdb->update(
            $table,
            array('sort_order' => $order, 'outfit_tag' => 'outfit'),
            array('id' => (int) $row->id, 'user_id' => (int) $user_id),
            array('%d', '%s'),
            array('%d', '%d')
        );
        $order++;
    }
}

function benditoai_modelo_outfit_count($modelo_id, $user_id) {
    benditoai_modelo_outfit_ensure_schema();

    global $wpdb;
    $table = benditoai_modelo_outfits_table();

    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE modelo_id = %d AND user_id = %d",
        $modelo_id,
        $user_id
    ));
}

function benditoai_modelo_outfit_stats($modelo_id, $user_id) {
    benditoai_modelo_outfit_get_principal($modelo_id, $user_id);
    $count = benditoai_modelo_outfit_count($modelo_id, $user_id);
    $limit = benditoai_modelo_outfit_limit($user_id);
    $can_add = $count < $limit;

    return array(
        'count' => $count,
        'limit' => $limit,
        'can_add' => $can_add,
        'limit_reached' => !$can_add,
        'warning' => !$can_add ? benditoai_modelo_outfit_warning() : '',
    );
}

function benditoai_modelo_outfit_response_item($outfit) {
    $tag = isset($outfit->outfit_tag) ? (string) $outfit->outfit_tag : 'outfit';
    return array(
        'id' => (int) $outfit->id,
        'modelo_id' => (int) $outfit->modelo_id,
        'nombre_outfit' => (string) $outfit->nombre_outfit,
        'outfit_tag' => $tag,
        'is_principal' => $tag === 'principal',
        'sort_order' => (int) ($outfit->sort_order ?? 0),
        'image_url' => (string) $outfit->image_url,
        'created_at' => (string) ($outfit->created_at ?? ''),
        'updated_at' => (string) ($outfit->updated_at ?? ''),
    );
}

function benditoai_modelos_ai_get_saved_outfits_grouped($user_id, $modelo_ids = array()) {
    benditoai_modelo_outfit_ensure_schema();

    global $wpdb;
    $table = benditoai_modelo_outfits_table();
    $modelo_ids = array_values(array_filter(array_map('intval', (array) $modelo_ids)));

    if (empty($modelo_ids)) {
        return array();
    }

    foreach ($modelo_ids as $modelo_id) {
        benditoai_modelo_outfit_get_principal($modelo_id, $user_id);
        benditoai_modelo_outfit_reindex($modelo_id, $user_id);
    }

    $placeholders = implode(',', array_fill(0, count($modelo_ids), '%d'));
    $params = array_merge(array($user_id), $modelo_ids);
    $query = "SELECT * FROM $table WHERE user_id = %d AND modelo_id IN ($placeholders) ORDER BY modelo_id ASC, CASE WHEN outfit_tag = 'principal' THEN 0 ELSE 1 END ASC, sort_order ASC, created_at ASC, id ASC";
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

function benditoai_modelo_outfit_create_secondary_from_image($modelo_id, $user_id, $image_url, $prompt = '') {
    $modelo = benditoai_modelo_outfit_get_owned_model($modelo_id, $user_id);
    if (!$modelo || !$image_url) {
        return null;
    }

    $stats = benditoai_modelo_outfit_stats($modelo_id, $user_id);
    if (!$stats['can_add']) {
        return new WP_Error('limit_reached', benditoai_modelo_outfit_warning());
    }

    global $wpdb;
    $table = benditoai_modelo_outfits_table();
    $now = current_time('mysql');
    $next_number = max(1, (int) $stats['count'] + 1);

    $inserted = $wpdb->insert(
        $table,
        array(
            'user_id' => $user_id,
            'modelo_id' => $modelo_id,
            'nombre_outfit' => benditoai_modelo_outfit_default_name($modelo, $next_number),
            'outfit_tag' => 'outfit',
            'sort_order' => $next_number,
            'image_url' => (string) $image_url,
            'prompt' => (string) $prompt,
            'created_at' => $now,
            'updated_at' => $now,
        ),
        array('%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
    );

    if (!$inserted) {
        return null;
    }

    $outfit = benditoai_modelo_outfit_get_owned_outfit((int) $wpdb->insert_id, $user_id);
    benditoai_modelo_outfit_reindex($modelo_id, $user_id);
    return $outfit;
}

function benditoai_modelo_outfit_backfill_principals() {
    benditoai_modelo_outfit_ensure_schema();

    global $wpdb;
    $modelos_table = $wpdb->prefix . 'benditoai_modelos_ai';
    $outfits_table = benditoai_modelo_outfits_table();

    $rows = $wpdb->get_results(
        "SELECT id, user_id FROM $modelos_table"
    );

    foreach ((array) $rows as $row) {
        $modelo_id = (int) ($row->id ?? 0);
        $user_id = (int) ($row->user_id ?? 0);
        if ($modelo_id <= 0 || $user_id <= 0) {
            continue;
        }

        benditoai_modelo_outfit_get_principal($modelo_id, $user_id);
        benditoai_modelo_outfit_reindex($modelo_id, $user_id);
    }

    // Best effort: set missing tags/orders for legacy rows.
    $wpdb->query("UPDATE $outfits_table SET outfit_tag = 'outfit' WHERE outfit_tag IS NULL OR outfit_tag = ''");
    $wpdb->query("UPDATE $outfits_table SET sort_order = 1000 WHERE sort_order IS NULL OR sort_order <= 0");
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
    if (!$modelo) {
        wp_send_json_error(array('message' => 'Modelo no valido'));
    }

    $principal = benditoai_modelo_outfit_get_principal($modelo_id, $user_id, $modelo);
    if (!$principal || empty($principal->image_url)) {
        wp_send_json_error(array('message' => 'No se encontro outfit principal para guardar'));
    }

    $outfit = benditoai_modelo_outfit_create_secondary_from_image(
        $modelo_id,
        $user_id,
        (string) $principal->image_url,
        (string) ($principal->prompt ?? '')
    );

    if (is_wp_error($outfit)) {
        wp_send_json_error(array(
            'message' => $outfit->get_error_message(),
            'stats' => benditoai_modelo_outfit_stats($modelo_id, $user_id),
        ));
    }

    if (!$outfit) {
        wp_send_json_error(array('message' => 'No se pudo guardar el outfit'));
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

    if ((string) ($outfit->outfit_tag ?? '') === 'principal') {
        wp_send_json_error(array('message' => 'El outfit principal no se puede eliminar. Solo se puede reemplazar.'));
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

    benditoai_modelo_outfit_reindex((int) $outfit->modelo_id, $user_id);

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
    $selected_style_context = benditoai_modelo_get_selected_style_context();
    $has_reference_upload = !empty($_FILES['prenda_referencia']['tmp_name']);
    $is_style_only_prompt = !empty($_POST['style_only_prompt']) && is_array($selected_style_context) && !$has_reference_upload;
    if ($texto === '' && $has_reference_upload) {
        $texto = 'Cambia exactamente la prenda de la imagen adjunta por la del modelo, sin cambiar su rostro ni su pose. Ajusta la prenda perfectamente, de forma fiel, realista y natural.';
        $is_style_only_prompt = false;
    } elseif ($texto === '' && is_array($selected_style_context)) {
        $style_label = trim((string) ($selected_style_context['label'] ?? ''));
        if ($style_label !== '') {
            $texto = 'Viste al modelo con ropa aleatoriamente al estilo ' . $style_label . ', manteniendo el rostro del modelo fiel y su pose.';
            $is_style_only_prompt = true;
        }
    }

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
            $reference_context = 'Estilo preferido seleccionado por el usuario: ' . $style_label . '. Manten esta direccion de estilo respetando la solicitud exacta de cambio de prenda.';
        }
    }

    $prompt = benditoai_modelo_build_edit_prompt($texto, !empty($extra_images), $reference_context, $is_style_only_prompt);

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
        'stats' => benditoai_modelo_outfit_stats((int) $outfit->modelo_id, $user_id),
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

    if (!in_array($decision, array('add', 'replace'), true)) {
        wp_send_json_error(array('message' => 'Decision invalida'));
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

    $modelo_id = (int) $outfit->modelo_id;
    $preview_url = (string) $preview_data['preview_url'];
    $prompt = isset($preview_data['prompt']) ? (string) $preview_data['prompt'] : '';
    $active_outfit = null;

    if ($decision === 'add') {
        $added = benditoai_modelo_outfit_create_secondary_from_image($modelo_id, $user_id, $preview_url, $prompt);
        if (is_wp_error($added)) {
            wp_send_json_error(array(
                'message' => $added->get_error_message(),
                'stats' => benditoai_modelo_outfit_stats($modelo_id, $user_id),
            ));
        }

        if (!$added) {
            wp_send_json_error(array('message' => 'No se pudo agregar el nuevo outfit'));
        }

        $active_outfit = $added;
    } else {
        global $wpdb;
        $updated = $wpdb->update(
            benditoai_modelo_outfits_table(),
            array(
                'image_url' => $preview_url,
                'prompt' => $prompt,
                'updated_at' => current_time('mysql'),
            ),
            array(
                'id' => $outfit_id,
                'user_id' => $user_id,
            ),
            array('%s', '%s', '%s'),
            array('%d', '%d')
        );

        if ($updated === false) {
            wp_send_json_error(array('message' => 'No se pudo reemplazar el outfit'));
        }

        $active_outfit = benditoai_modelo_outfit_get_owned_outfit($outfit_id, $user_id);
    }

    delete_transient($transient_key);
    benditoai_modelo_outfit_reindex($modelo_id, $user_id);

    $stats = benditoai_modelo_outfit_stats($modelo_id, $user_id);
    $item = $active_outfit ? benditoai_modelo_outfit_response_item($active_outfit) : null;

    wp_send_json_success(array(
        'decision' => $decision,
        'image_url' => isset($item['image_url']) ? (string) $item['image_url'] : $preview_url,
        'outfit' => $item,
        'active_outfit_id' => $item ? (int) $item['id'] : 0,
        'active_outfit_tag' => $item ? (string) $item['outfit_tag'] : 'outfit',
        'can_add' => (bool) $stats['can_add'],
        'limit_reached' => (bool) $stats['limit_reached'],
        'stats' => $stats,
    ));
}
