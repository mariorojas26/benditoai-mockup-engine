<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_benditoai_generar_modelo_ai', 'benditoai_generar_modelo_ai');

function benditoai_modelos_ai_text_field($key, $default = '') {
    if (!isset($_POST[$key])) {
        return $default;
    }

    $value = wp_unslash($_POST[$key]);
    if (is_array($value)) {
        $value = end($value);
    }

    return sanitize_text_field($value);
}

function benditoai_modelos_ai_textarea_field($key, $default = '') {
    if (!isset($_POST[$key])) {
        return $default;
    }

    $value = wp_unslash($_POST[$key]);
    if (is_array($value)) {
        $value = end($value);
    }

    return sanitize_textarea_field($value);
}

function benditoai_modelos_ai_bool_field($key, $default = 0) {
    if (!isset($_POST[$key])) {
        return (int) ((bool) $default);
    }

    $raw = wp_unslash($_POST[$key]);

    if (is_array($raw)) {
        $raw = end($raw);
    }

    $normalized = strtolower(trim((string) $raw));
    if (in_array($normalized, array('1', 'true', 'yes', 'on'), true)) {
        return 1;
    }

    return 0;
}

function benditoai_modelos_ai_compose_traits($base_traits, $extra_pairs = array()) {
    $lines = array();

    $base_traits = trim((string) $base_traits);
    if ($base_traits !== '') {
        $lines[] = $base_traits;
    }

    foreach ($extra_pairs as $label => $value) {
        $value = trim((string) $value);
        if ($value === '') {
            continue;
        }

        $lines[] = $label . ': ' . $value;
    }

    return implode("\n", $lines);
}

function benditoai_modelos_ai_extract_image_base64($body) {
    if (!isset($body['candidates'][0]['content']['parts']) || !is_array($body['candidates'][0]['content']['parts'])) {
        return null;
    }

    foreach ($body['candidates'][0]['content']['parts'] as $part) {
        if (isset($part['inlineData']['data'])) {
            return $part['inlineData']['data'];
        }

        if (isset($part['inline_data']['data'])) {
            return $part['inline_data']['data'];
        }
    }

    return null;
}

function benditoai_modelos_ai_build_prompt_rasgos($data) {
    return "
Ultra realistic human avatar.

Single person only.

CREATIVE MODE
Generated from profile traits.

MODEL SUMMARY
{$data['descripcion_modelo']}

PROFILE
Gender: {$data['genero']}
Age: {$data['edad']}
Body type: {$data['cuerpo']}
Ethnicity: {$data['etnia']}
Nationality: {$data['nacionalidad']}
Style: {$data['estilo']}

FACIAL AND PERSONAL TRAITS
{$data['rasgos_caracteristicas']}

OUTFIT DESCRIPTION
Upper clothing: {$data['prenda_superior']}
Lower clothing: {$data['prenda_inferior']}
Shoes: {$data['zapatos']}
Accessories: {$data['accesorios']}

EXTRA REQUIREMENTS
{$data['campo_adicional']}

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
no text
no logos
no objects
no furniture

portrait composition
high detail
photorealistic
4k quality

CRITICAL
vertical image (9:16 aspect ratio)
full body centered
no cropping
subject fully visible head to feet
";
}

function benditoai_modelos_ai_build_prompt_referencia($data, $has_reference = true) {
    $reference_instruction = $has_reference
        ? "Use the uploaded image as the main visual reference.\nPreserve identity cues from reference: face structure, skin tone and natural proportions."
        : "No reference image was uploaded.\nBuild the avatar from text fields while preserving realism and full body composition.";

    return "
Ultra realistic human avatar.

Single person only.

CREATIVE MODE
{$reference_instruction}
Do not copy watermarks, text or logos.

MODEL SUMMARY
{$data['descripcion_modelo']}

OPTIONAL STYLE HINT
{$data['estilo']}

OPTIONAL OUTFIT HINTS
Upper clothing: {$data['prenda_superior']}
Lower clothing: {$data['prenda_inferior']}
Shoes: {$data['zapatos']}
Accessories: {$data['accesorios']}

EXTRA REQUIREMENTS
{$data['campo_adicional']}

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
no text
no logos
no objects
no furniture

portrait composition
high detail
photorealistic
4k quality

CRITICAL
vertical image (9:16 aspect ratio)
full body centered
no cropping
subject fully visible head to feet
";
}

function benditoai_modelos_ai_normalize_output($image_base64) {
    $image = base64_decode($image_base64);

    if (!$image) {
        return new WP_Error('invalid_image', 'Imagen base64 invalida');
    }

    $src = imagecreatefromstring($image);

    if (!$src) {
        return new WP_Error('decode_error', 'Error procesando imagen');
    }

    $final_width = 1024;
    $final_height = 1792;

    $dst = imagecreatetruecolor($final_width, $final_height);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);

    $width = imagesx($src);
    $height = imagesy($src);

    if (!$width || !$height) {
        imagedestroy($src);
        imagedestroy($dst);
        return new WP_Error('invalid_dimensions', 'Dimensiones invalidas');
    }

    $scale = min($final_width / $width, $final_height / $height);
    $new_width = (int) round($width * $scale);
    $new_height = (int) round($height * $scale);

    $x = (int) round(($final_width - $new_width) / 2);
    $y = (int) round(($final_height - $new_height) / 2);

    imagecopyresampled(
        $dst,
        $src,
        $x,
        $y,
        0,
        0,
        $new_width,
        $new_height,
        $width,
        $height
    );

    $upload = wp_upload_dir();

    if (!empty($upload['error'])) {
        imagedestroy($src);
        imagedestroy($dst);
        return new WP_Error('upload_dir_error', $upload['error']);
    }

    $filename = 'modelo_' . time() . '_' . wp_generate_password(6, false, false) . '.jpg';
    $path = trailingslashit($upload['path']) . $filename;

    if (!imagejpeg($dst, $path, 90)) {
        imagedestroy($src);
        imagedestroy($dst);
        return new WP_Error('save_error', 'No se pudo guardar la imagen generada');
    }

    imagedestroy($src);
    imagedestroy($dst);

    return array(
        'path' => $path,
        'url' => trailingslashit($upload['url']) . $filename,
    );
}

function benditoai_modelos_ai_column_exists($table_name, $column_name) {
    static $cache = array();

    $cache_key = $table_name . '::' . $column_name;
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    global $wpdb;
    $result = $wpdb->get_var(
        $wpdb->prepare("SHOW COLUMNS FROM `$table_name` LIKE %s", $column_name)
    );

    $cache[$cache_key] = !empty($result);
    return $cache[$cache_key];
}

function benditoai_modelos_ai_ensure_optional_columns($table_name) {
    global $wpdb;

    $columns = array(
        'modo_creacion' => "VARCHAR(30) NOT NULL DEFAULT 'rasgos'",
        'perfil_publico' => "TINYINT(1) NOT NULL DEFAULT 0",
        'descripcion_modelo' => "TEXT",
        'nacionalidad' => "VARCHAR(80) DEFAULT ''",
        'rasgos_caracteristicas' => "TEXT",
        'campo_adicional' => "TEXT",
    );

    foreach ($columns as $column_name => $definition) {
        if (benditoai_modelos_ai_column_exists($table_name, $column_name)) {
            continue;
        }

        // Best effort: keep production compatible even if schema has not been migrated.
        $wpdb->query("ALTER TABLE `$table_name` ADD COLUMN `$column_name` $definition");
    }
}

function benditoai_modelos_ai_cleanup_deprecated_columns($table_name) {
    global $wpdb;

    // Deprecated after wizard refactor: no longer used in UI or prompts.
    $deprecated_columns = array('descripcion_referencia');

    foreach ($deprecated_columns as $column_name) {
        if (!benditoai_modelos_ai_column_exists($table_name, $column_name)) {
            continue;
        }

        // Best effort cleanup: if ALTER fails, generation flow continues.
        $wpdb->query("ALTER TABLE `$table_name` DROP COLUMN `$column_name`");
    }
}

function benditoai_modelos_ai_get_reference_base64() {
    if (!isset($_FILES['imagen_referencia'])) {
        return null;
    }

    $file = $_FILES['imagen_referencia'];

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return new WP_Error('upload_error', 'No se pudo leer la imagen de referencia');
    }

    if (!isset($file['size']) || (int) $file['size'] > 8 * 1024 * 1024) {
        return new WP_Error('size_error', 'La imagen debe pesar maximo 8MB');
    }

    if (empty($file['tmp_name'])) {
        return new WP_Error('tmp_error', 'Archivo temporal invalido');
    }

    $mime = wp_get_image_mime($file['tmp_name']);
    $allowed = array('image/jpeg', 'image/png', 'image/webp');

    if (!$mime || !in_array($mime, $allowed, true)) {
        return new WP_Error('mime_error', 'Formato no valido. Usa PNG, JPG o WEBP');
    }

    $raw = file_get_contents($file['tmp_name']);

    if (!$raw) {
        return new WP_Error('read_error', 'No se pudo procesar la imagen de referencia');
    }

    $src = imagecreatefromstring($raw);

    if (!$src) {
        return new WP_Error('decode_error', 'No se pudo decodificar la imagen de referencia');
    }

    ob_start();
    imagepng($src);
    $png_binary = ob_get_clean();
    imagedestroy($src);

    if (!$png_binary) {
        return new WP_Error('encode_error', 'No se pudo preparar la imagen para IA');
    }

    return base64_encode($png_binary);
}

function benditoai_generar_modelo_ai() {

    if (!is_user_logged_in()) {
        wp_send_json_error('Debes iniciar sesion');
    }

    $nonce = benditoai_modelos_ai_text_field('benditoai_modelos_nonce');
    if (!$nonce || !wp_verify_nonce($nonce, 'benditoai_modelos_ai_nonce')) {
        wp_send_json_error(array(
            'message' => 'Solicitud invalida. Recarga la pagina e intenta otra vez.',
            'code' => 'invalid_nonce',
        ));
    }

    $user_id = get_current_user_id();

    $modo_creacion = benditoai_modelos_ai_text_field('modo_creacion', 'referencia');
    if (!in_array($modo_creacion, array('referencia', 'rasgos'), true)) {
        $modo_creacion = 'referencia';
    }

    $nombre_modelo = benditoai_modelos_ai_text_field('nombre_modelo');
    if (!$nombre_modelo) {
        wp_send_json_error(array('message' => 'El nombre del modelo es obligatorio.'));
    }

    $descripcion_modelo = benditoai_modelos_ai_textarea_field('descripcion_modelo', $nombre_modelo);
    if (trim((string) $descripcion_modelo) === '') {
        $descripcion_modelo = $nombre_modelo;
    }
    $perfil_publico = benditoai_modelos_ai_bool_field('perfil_publico');

    // -------------------------------------------------
    // VALIDAR LIMITE
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

    if ((int) $total_modelos >= (int) $max_modelos) {
        wp_send_json_error(array(
            'message' => 'Has alcanzado el limite de modelos de tu plan.',
            'code' => 'limit_reached',
        ));
    }

    $genero = benditoai_modelos_ai_text_field('genero');
    $edad = benditoai_modelos_ai_text_field('edad');
    $cuerpo = benditoai_modelos_ai_text_field('cuerpo');
    $etnia = benditoai_modelos_ai_text_field('etnia');
    $estilo = benditoai_modelos_ai_text_field('estilo');

    $nacionalidad = benditoai_modelos_ai_text_field('nacionalidad');
    $rasgos_caracteristicas = benditoai_modelos_ai_textarea_field('rasgos_caracteristicas');
    $campo_adicional = benditoai_modelos_ai_textarea_field('campo_adicional');

    $prenda_superior = benditoai_modelos_ai_textarea_field('prenda_superior', 'plain white t-shirt');
    $prenda_inferior = benditoai_modelos_ai_textarea_field('prenda_inferior', 'white shorts');
    $zapatos = benditoai_modelos_ai_textarea_field('zapatos', 'barefoot');
    $accesorios = benditoai_modelos_ai_textarea_field('accesorios', 'none');

    if (trim((string) $prenda_superior) === '') {
        $prenda_superior = 'plain white t-shirt';
    }
    if (trim((string) $prenda_inferior) === '') {
        $prenda_inferior = 'white shorts';
    }
    if (trim((string) $zapatos) === '') {
        $zapatos = 'barefoot';
    }
    if (trim((string) $accesorios) === '') {
        $accesorios = 'none';
    }

    $color_ojos = benditoai_modelos_ai_text_field('color_ojos');
    $peinado = benditoai_modelos_ai_text_field('peinado');
    $color_pelo = benditoai_modelos_ai_text_field('color_pelo');

    $detalle_hoyuelos = benditoai_modelos_ai_bool_field('detalle_hoyuelos');
    $detalle_barba = benditoai_modelos_ai_bool_field('detalle_barba');
    $detalle_bronceado = benditoai_modelos_ai_bool_field('detalle_bronceado');

    $detalle_visuales = array(
        'Eye color' => $color_ojos,
        'Hairstyle' => $peinado,
        'Hair color' => $color_pelo,
    );

    $rasgos_flags = array();
    if ($detalle_hoyuelos) {
        $rasgos_flags[] = 'dimples';
    }
    if ($detalle_barba) {
        $rasgos_flags[] = 'beard';
    }
    if ($detalle_bronceado) {
        $rasgos_flags[] = 'tanned skin';
    }
    if (!empty($rasgos_flags)) {
        $detalle_visuales['Extra marks'] = implode(', ', $rasgos_flags);
    }

    $rasgos_compuestos = benditoai_modelos_ai_compose_traits($rasgos_caracteristicas, $detalle_visuales);

    if ($modo_creacion === 'referencia' && !$estilo) {
        $estilo = 'reference';
    }

    $prompt_data = array(
        'descripcion_modelo' => $descripcion_modelo,
        'genero' => $genero,
        'edad' => $edad,
        'cuerpo' => $cuerpo,
        'etnia' => $etnia,
        'estilo' => $estilo,
        'nacionalidad' => $nacionalidad,
        'rasgos_caracteristicas' => $rasgos_compuestos,
        'prenda_superior' => $prenda_superior,
        'prenda_inferior' => $prenda_inferior,
        'zapatos' => $zapatos,
        'accesorios' => $accesorios,
        'campo_adicional' => $campo_adicional,
    );

    if ($modo_creacion === 'referencia') {
        $reference_base64 = benditoai_modelos_ai_get_reference_base64();
        $has_reference = !is_wp_error($reference_base64) && !empty($reference_base64);
        $prompt = benditoai_modelos_ai_build_prompt_referencia($prompt_data, $has_reference);

        if (is_wp_error($reference_base64)) {
            wp_send_json_error(array('message' => $reference_base64->get_error_message()));
        }

        if ($has_reference) {
            $response = benditoai_call_gemini($reference_base64, $prompt);
        } else {
            $response = benditoai_call_gemini_text($prompt);
        }
    } else {
        $prompt = benditoai_modelos_ai_build_prompt_rasgos($prompt_data);
        $response = benditoai_call_gemini_text($prompt);
    }

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Error llamando IA'));
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $image_base64 = benditoai_modelos_ai_extract_image_base64($body);

    if (!$image_base64) {
        wp_send_json_error(array('message' => 'La IA no devolvio imagen'));
    }

    $normalized = benditoai_modelos_ai_normalize_output($image_base64);
    if (is_wp_error($normalized)) {
        wp_send_json_error(array('message' => $normalized->get_error_message()));
    }

    $created_at = current_time('mysql');

    benditoai_modelos_ai_ensure_optional_columns($table_name);
    benditoai_modelos_ai_cleanup_deprecated_columns($table_name);

    $insert_data = array(
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
        'image_url' => $normalized['url'],
        'created_at' => $created_at,
    );

    $optional_data = array(
        'modo_creacion' => $modo_creacion,
        'perfil_publico' => $perfil_publico,
        'descripcion_modelo' => $descripcion_modelo,
        'nacionalidad' => $nacionalidad,
        'rasgos_caracteristicas' => $rasgos_compuestos,
        'campo_adicional' => $campo_adicional,
    );

    foreach ($optional_data as $column_name => $value) {
        if (benditoai_modelos_ai_column_exists($table_name, $column_name)) {
            $insert_data[$column_name] = $value;
        }
    }

    $insert_format = array();
    $numeric_columns = array('user_id', 'perfil_publico');
    foreach ($insert_data as $column_name => $value) {
        $insert_format[] = in_array($column_name, $numeric_columns, true) ? '%d' : '%s';
    }

    $inserted = $wpdb->insert($table_name, $insert_data, $insert_format);

    if ($inserted === false) {
        wp_send_json_error(array(
            'message' => 'No se pudo guardar el modelo en la base de datos.',
        ));
    }

    $modelo_id = (int) $wpdb->insert_id;

    benditoai_use_token(1);
    $tokens_restantes = benditoai_get_user_tokens($user_id);

    $modo_label = ($modo_creacion === 'referencia') ? 'Imagen de referencia' : 'Rasgos';

    wp_send_json_success(array(
        'id' => $modelo_id,
        'image_url' => $normalized['url'],
        'tokens' => $tokens_restantes,
        'nombre_modelo' => $nombre_modelo,
        'modo_creacion' => $modo_creacion,
        'modo_label' => $modo_label,
        'genero' => $genero,
        'edad' => $edad,
        'cuerpo' => $cuerpo,
        'etnia' => $etnia,
        'estilo' => $estilo,
        'descripcion_modelo' => $descripcion_modelo,
        'nacionalidad' => $nacionalidad,
        'prenda_superior' => $prenda_superior,
        'prenda_inferior' => $prenda_inferior,
        'zapatos' => $zapatos,
        'accesorios' => $accesorios,
        'perfil_publico' => $perfil_publico,
        'fecha' => date('d/m/Y H:i', strtotime($created_at)),
    ));
}

