<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_benditoai_generar_modelo_ai', 'benditoai_crear_modelo_ai');
add_action('wp_ajax_benditoai_crear_modelo_ai', 'benditoai_crear_modelo_ai');
add_action('wp_ajax_benditoai_generar_influencer_referencia', 'benditoai_generar_influencer_referencia');

function benditoai_modelos_ai_get_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'benditoai_modelos_ai';
}

function benditoai_modelos_ai_ensure_extra_columns() {
    static $checked = false;

    if ($checked) {
        return;
    }

    global $wpdb;

    $table = benditoai_modelos_ai_get_table_name();

    $columns = [
        'descripcion_modelo' => "ALTER TABLE {$table} ADD descripcion_modelo TEXT NULL AFTER nombre_modelo",
        'is_public' => "ALTER TABLE {$table} ADD is_public TINYINT(1) NOT NULL DEFAULT 0 AFTER image_url",
        'reference_source' => "ALTER TABLE {$table} ADD reference_source VARCHAR(30) DEFAULT '' AFTER is_public",
        'traits_payload' => "ALTER TABLE {$table} ADD traits_payload LONGTEXT NULL AFTER reference_source",
    ];

    foreach ($columns as $column => $sql) {
        $exists = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM {$table} LIKE %s", $column));
        if (!$exists) {
            $wpdb->query($sql);
        }
    }

    $checked = true;
}

function benditoai_modelos_ai_validate_limit($user_id) {
    global $wpdb;

    $plan_data = benditoai_get_user_plan_data($user_id);
    $max_modelos = isset($plan_data['max_modelos']) ? (int) $plan_data['max_modelos'] : 0;

    $table_name = benditoai_modelos_ai_get_table_name();

    $total_modelos = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE user_id = %d",
            $user_id
        )
    );

    if ($total_modelos >= $max_modelos) {
        wp_send_json_error([
            'message' => 'Has alcanzado el límite de modelos de tu plan.',
            'code' => 'limit_reached'
        ]);
    }
}

function benditoai_modelos_ai_normalize_and_save_image($image_base64, $prefix = 'modelo') {
    $binary = base64_decode($image_base64);

    if (!$binary) {
        return new WP_Error('invalid_image', 'No se pudo decodificar la imagen generada.');
    }

    $src = imagecreatefromstring($binary);

    if (!$src) {
        return new WP_Error('invalid_resource', 'No se pudo abrir la imagen para procesarla.');
    }

    $final_width = 1024;
    $final_height = 1792;

    $dst = imagecreatetruecolor($final_width, $final_height);

    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);

    $width = imagesx($src);
    $height = imagesy($src);

    if ($width <= 0 || $height <= 0) {
        imagedestroy($src);
        imagedestroy($dst);
        return new WP_Error('invalid_size', 'La imagen generada no tiene dimensiones válidas.');
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

    $filename = sanitize_file_name($prefix . '_' . time() . '_' . wp_rand(1000, 9999) . '.jpg');
    $path = trailingslashit($upload['path']) . $filename;

    imagejpeg($dst, $path, 90);

    imagedestroy($src);
    imagedestroy($dst);

    return [
        'path' => $path,
        'url' => trailingslashit($upload['url']) . $filename,
    ];
}

function benditoai_modelos_ai_text($key, $default = '') {
    if (!isset($_POST[$key])) {
        return $default;
    }

    return sanitize_text_field(wp_unslash($_POST[$key]));
}

function benditoai_modelos_ai_textarea($key, $default = '') {
    if (!isset($_POST[$key])) {
        return $default;
    }

    return sanitize_textarea_field(wp_unslash($_POST[$key]));
}

function benditoai_modelos_ai_get_traits_payload() {
    if (!isset($_POST['traits_payload'])) {
        return [
            'raw' => '',
            'parsed' => [],
        ];
    }

    $raw = wp_unslash($_POST['traits_payload']);
    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        return [
            'raw' => '',
            'parsed' => [],
        ];
    }

    $sanitized = [];

    foreach ($decoded as $key => $value) {
        if (is_array($value)) {
            continue;
        }

        $sanitized[sanitize_key($key)] = sanitize_text_field((string) $value);
    }

    return [
        'raw' => wp_json_encode($sanitized),
        'parsed' => $sanitized,
    ];
}

function benditoai_modelos_ai_build_influencer_prompt($traits) {
    $genero = $traits['genero'];
    $edad = $traits['edad'];
    $altura = $traits['altura'];
    $peso = $traits['peso'];
    $pais = $traits['pais'];
    $pais2 = $traits['pais2'];
    $constitucion = $traits['constitucion'];
    $ojos = $traits['ojos'];
    $peinado = $traits['peinado'];
    $color_pelo = $traits['color_pelo'];
    $hoyuelos = $traits['hoyuelos'] === '1' ? 'yes' : 'no';
    $barba = $traits['barba'] === '1' ? 'yes' : 'no';
    $bronceado = $traits['bronceado'] === '1' ? 'yes' : 'no';
    $detalles = $traits['detalles'];

    $mix_paises = $pais2 !== 'Ninguno' ? "{$pais} + {$pais2}" : $pais;

    return "
Ultra realistic influencer portrait.

Single person only.

Character profile:
- Gender: {$genero}
- Age: {$edad}
- Height: {$altura} cm
- Weight: {$peso} kg
- National/cultural style influence: {$mix_paises}
- Body build: {$constitucion}
- Eye color/type: {$ojos}
- Hairstyle: {$peinado}
- Hair color: {$color_pelo}
- Dimples: {$hoyuelos}
- Beard: {$barba}
- Sun tan: {$bronceado}

Additional custom details:
{$detalles}

Image direction:
- Full body, head-to-feet visible
- Subject centered
- Fashion-forward influencer look
- Clean soft studio background
- Natural skin texture, photorealistic detail
- High quality lighting and depth

Critical rules:
- only ONE person
- no group
- no text overlays
- no logos
- no furniture

Output:
- vertical image 9:16
- photorealistic
- 4k quality
";
}

function benditoai_generar_influencer_referencia() {

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Debes iniciar sesión']);
    }

    $user_id = get_current_user_id();

    $traits = [
        'genero' => benditoai_modelos_ai_text('genero', 'Mujer'),
        'edad' => (string) max(18, min(60, (int) benditoai_modelos_ai_text('edad', '28'))),
        'altura' => (string) max(145, min(200, (int) benditoai_modelos_ai_text('altura', '168'))),
        'peso' => (string) max(40, min(130, (int) benditoai_modelos_ai_text('peso', '58'))),
        'pais' => benditoai_modelos_ai_text('pais', 'Colombia'),
        'pais2' => benditoai_modelos_ai_text('pais2', 'Ninguno'),
        'constitucion' => benditoai_modelos_ai_text('constitucion', 'Atlética'),
        'ojos' => benditoai_modelos_ai_text('ojos', 'Ámbar'),
        'peinado' => benditoai_modelos_ai_text('peinado', 'Pixie lateral'),
        'color_pelo' => benditoai_modelos_ai_text('color_pelo', 'Miel'),
        'hoyuelos' => benditoai_modelos_ai_text('hoyuelos', '0') === '1' ? '1' : '0',
        'barba' => benditoai_modelos_ai_text('barba', '0') === '1' ? '1' : '0',
        'bronceado' => benditoai_modelos_ai_text('bronceado', '1') === '1' ? '1' : '0',
        'detalles' => benditoai_modelos_ai_textarea('detalles', 'Natural editorial style, confident expression, fashion campaign aesthetics.'),
    ];

    $prompt = benditoai_modelos_ai_build_influencer_prompt($traits);

    $response = benditoai_call_gemini_text($prompt);

    if (is_wp_error($response)) {
        wp_send_json_error([
            'message' => 'Error llamando IA para generar la referencia: ' . $response->get_error_message()
        ]);
    }

    $status_code = (int) wp_remote_retrieve_response_code($response);
    $raw_body = wp_remote_retrieve_body($response);
    $body = json_decode($raw_body, true);

    if ($status_code < 200 || $status_code >= 300) {
        $api_message = '';

        if (is_array($body) && isset($body['error']['message'])) {
            $api_message = sanitize_text_field($body['error']['message']);
        }

        if (empty($api_message)) {
            $api_message = 'HTTP ' . $status_code;
        }

        wp_send_json_error([
            'message' => 'Gemini devolvió un error: ' . $api_message
        ]);
    }

    $image_base64 = null;

    if (isset($body['candidates'][0]['content']['parts'])) {
        foreach ($body['candidates'][0]['content']['parts'] as $part) {
            if (isset($part['inlineData']['data'])) {
                $image_base64 = $part['inlineData']['data'];
                break;
            }
        }
    }

    if (!$image_base64) {
        $api_message = '';
        if (is_array($body) && isset($body['error']['message'])) {
            $api_message = sanitize_text_field($body['error']['message']);
        }

        wp_send_json_error([
            'message' => !empty($api_message)
                ? 'Gemini no pudo generar imagen: ' . $api_message
                : 'La IA no devolvió imagen de referencia.'
        ]);
    }

    $saved_image = benditoai_modelos_ai_normalize_and_save_image($image_base64, 'influencer_ref');

    if (is_wp_error($saved_image)) {
        wp_send_json_error(['message' => $saved_image->get_error_message()]);
    }

    benditoai_use_token(1);
    $tokens_restantes = benditoai_get_user_tokens($user_id);

    wp_send_json_success([
        'image_url' => $saved_image['url'],
        'prompt' => $prompt,
        'traits' => $traits,
        'tokens' => $tokens_restantes,
    ]);
}

function benditoai_modelos_ai_extract_image_base64($body) {
    if (!is_array($body) || empty($body['candidates'][0]['content']['parts'])) {
        return '';
    }

    foreach ($body['candidates'][0]['content']['parts'] as $part) {
        if (!empty($part['inlineData']['data'])) {
            return (string) $part['inlineData']['data'];
        }

        if (!empty($part['inline_data']['data'])) {
            return (string) $part['inline_data']['data'];
        }
    }

    return '';
}

function benditoai_modelos_ai_extract_gemini_error($status_code, $body) {
    if (is_array($body) && !empty($body['error']['message'])) {
        return sanitize_text_field($body['error']['message']);
    }

    if (is_array($body) && !empty($body['promptFeedback']['blockReason'])) {
        return sanitize_text_field($body['promptFeedback']['blockReason']);
    }

    return 'HTTP ' . (int) $status_code;
}

function benditoai_modelos_ai_get_reference_base64($reference_source, $reference_image_url) {
    $has_uploaded_file = isset($_FILES['reference_image'])
        && isset($_FILES['reference_image']['tmp_name'])
        && !empty($_FILES['reference_image']['tmp_name']);

    if ($reference_source === 'upload' || ($has_uploaded_file && empty($reference_source))) {
        if (!$has_uploaded_file) {
            return new WP_Error('missing_upload', 'Debes subir una imagen de referencia para crear el modelo.');
        }

        if (!empty($_FILES['reference_image']['error']) && (int) $_FILES['reference_image']['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', 'No se pudo leer la imagen subida (error de carga).');
        }

        $tmp_path = $_FILES['reference_image']['tmp_name'];
        $binary = @file_get_contents($tmp_path);

        if ($binary === false || empty($binary)) {
            return new WP_Error('upload_read_error', 'No se pudo leer la imagen de referencia subida.');
        }

        return base64_encode($binary);
    }

    if ($reference_source !== 'ai') {
        return new WP_Error('missing_reference', 'Selecciona una imagen de referencia (subida o generada).');
    }

    if (empty($reference_image_url)) {
        return new WP_Error('missing_ai_reference', 'Primero genera y selecciona una imagen de referencia.');
    }

    $upload_data = wp_upload_dir();
    $base_upload_url = trailingslashit($upload_data['baseurl']);
    $base_upload_dir = trailingslashit($upload_data['basedir']);

    if (strpos($reference_image_url, $base_upload_url) !== 0) {
        return new WP_Error('invalid_reference_url', 'La imagen de referencia no pertenece a una fuente permitida.');
    }

    $binary = false;

    $local_path = str_replace($base_upload_url, $base_upload_dir, $reference_image_url);
    if (!empty($local_path) && file_exists($local_path)) {
        $binary = @file_get_contents($local_path);
    }

    if ($binary === false || empty($binary)) {
        $remote = wp_remote_get($reference_image_url, ['timeout' => 45]);
        if (is_wp_error($remote)) {
            return new WP_Error('reference_download_error', 'No se pudo descargar la imagen de referencia: ' . $remote->get_error_message());
        }

        $http = (int) wp_remote_retrieve_response_code($remote);
        if ($http < 200 || $http >= 300) {
            return new WP_Error('reference_http_error', 'No se pudo descargar la imagen de referencia (HTTP ' . $http . ').');
        }

        $binary = wp_remote_retrieve_body($remote);
    }

    if (empty($binary)) {
        return new WP_Error('empty_reference', 'La imagen de referencia está vacía o no se pudo leer.');
    }

    return base64_encode($binary);
}

function benditoai_modelos_ai_build_final_prompt($nombre_modelo, $descripcion_modelo, $traits_payload, $generated_prompt) {
    $traits_lines = [];

    $map = [
        'genero' => 'Gender',
        'edad' => 'Age',
        'altura' => 'Height (cm)',
        'peso' => 'Weight (kg)',
        'pais' => 'Country',
        'pais2' => 'Country 2',
        'constitucion' => 'Body build',
        'ojos' => 'Eyes',
        'peinado' => 'Hairstyle',
        'color_pelo' => 'Hair color',
    ];

    foreach ($map as $key => $label) {
        if (!empty($traits_payload[$key]) && $traits_payload[$key] !== 'Ninguno') {
            $traits_lines[] = "- {$label}: " . $traits_payload[$key];
        }
    }

    if (!empty($traits_payload['hoyuelos']) && $traits_payload['hoyuelos'] === '1') {
        $traits_lines[] = "- Dimples: yes";
    }
    if (!empty($traits_payload['barba']) && $traits_payload['barba'] === '1') {
        $traits_lines[] = "- Beard: yes";
    }
    if (!empty($traits_payload['bronceado']) && $traits_payload['bronceado'] === '1') {
        $traits_lines[] = "- Tan: yes";
    }
    if (!empty($traits_payload['detalles'])) {
        $traits_lines[] = "- Custom details: " . $traits_payload['detalles'];
    }

    $traits_block = !empty($traits_lines)
        ? implode("\n", $traits_lines)
        : "- Keep same identity from reference image.";

    $base_generated_prompt = trim((string) $generated_prompt);
    $base_generated_prompt = !empty($base_generated_prompt)
        ? "\nBase style from prior influencer generation:\n{$base_generated_prompt}\n"
        : '';

    return "
Create a high-end photorealistic human avatar using the provided reference image.

Primary objective:
- Keep the same core identity and facial consistency from the reference.
- Produce a clean model image ready for mockup generation.

Model name:
{$nombre_modelo}

User description:
{$descripcion_modelo}

Requested traits:
{$traits_block}
{$base_generated_prompt}
Composition and quality rules:
- single person only
- full body visible (head to feet)
- centered subject
- soft studio lighting
- clean neutral background
- no text, no logos, no watermarks
- photorealistic skin and fabric detail
- vertical 9:16 composition
- high detail 4k quality
";
}

function benditoai_crear_modelo_ai() {

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Debes iniciar sesión']);
    }

    $user_id = get_current_user_id();

    benditoai_modelos_ai_ensure_extra_columns();
    benditoai_modelos_ai_validate_limit($user_id);

    $nombre_modelo = benditoai_modelos_ai_text('nombre_modelo');

    if (empty($nombre_modelo)) {
        wp_send_json_error(['message' => 'El nombre del modelo es obligatorio.']);
    }

    $descripcion_modelo = benditoai_modelos_ai_textarea('descripcion_modelo');
    $is_public = benditoai_modelos_ai_text('is_public', '0') === '1' ? 1 : 0;

    $reference_source = benditoai_modelos_ai_text('reference_source');
    $reference_image_url = esc_url_raw(benditoai_modelos_ai_text('reference_image_url'));
    $generated_prompt = benditoai_modelos_ai_textarea('generated_prompt');

    $traits_payload_data = benditoai_modelos_ai_get_traits_payload();
    $traits_payload_raw = $traits_payload_data['raw'];
    $traits_payload = $traits_payload_data['parsed'];

    if (empty($reference_source)) {
        $reference_source = (!empty($_FILES['reference_image']['tmp_name'])) ? 'upload' : 'ai';
    }

    if (empty($generated_prompt)) {
        $generated_prompt = benditoai_modelos_ai_textarea('influencer_prompt');
    }

    if (empty($traits_payload) && $reference_source === 'ai') {
        $fallback_traits = [
            'genero' => benditoai_modelos_ai_text('genero', ''),
            'edad' => benditoai_modelos_ai_text('edad', ''),
            'altura' => benditoai_modelos_ai_text('altura', ''),
            'peso' => benditoai_modelos_ai_text('peso', ''),
            'pais' => benditoai_modelos_ai_text('pais', ''),
            'pais2' => benditoai_modelos_ai_text('pais2', ''),
            'constitucion' => benditoai_modelos_ai_text('constitucion', ''),
            'ojos' => benditoai_modelos_ai_text('ojos', ''),
            'peinado' => benditoai_modelos_ai_text('peinado', ''),
            'color_pelo' => benditoai_modelos_ai_text('color_pelo', ''),
            'hoyuelos' => benditoai_modelos_ai_text('hoyuelos', '0') === '1' ? '1' : '0',
            'barba' => benditoai_modelos_ai_text('barba', '0') === '1' ? '1' : '0',
            'bronceado' => benditoai_modelos_ai_text('bronceado', '0') === '1' ? '1' : '0',
            'detalles' => benditoai_modelos_ai_textarea('detalles', ''),
        ];

        $has_any = false;
        foreach ($fallback_traits as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);

            if ($value === '') {
                continue;
            }

            if (in_array($key, ['hoyuelos', 'barba', 'bronceado'], true) && $value === '0') {
                continue;
            }

            if ($key === 'pais2' && $value === 'Ninguno') {
                continue;
            }

            $has_any = true;
            break;
        }

        if ($has_any) {
            $traits_payload = $fallback_traits;
            $traits_payload_raw = wp_json_encode($fallback_traits);
        }
    }

    if (!benditoai_user_has_tokens($user_id, 1)) {
        wp_send_json_error(['message' => 'No tienes tokens suficientes.']);
    }

    $reference_base64 = benditoai_modelos_ai_get_reference_base64($reference_source, $reference_image_url);

    if (is_wp_error($reference_base64)) {
        wp_send_json_error(['message' => $reference_base64->get_error_message()]);
    }

    $genero = isset($traits_payload['genero']) ? $traits_payload['genero'] : benditoai_modelos_ai_text('genero');
    $edad = isset($traits_payload['edad']) ? $traits_payload['edad'] : benditoai_modelos_ai_text('edad');
    $cuerpo = isset($traits_payload['constitucion']) ? $traits_payload['constitucion'] : benditoai_modelos_ai_text('cuerpo');
    $etnia = isset($traits_payload['pais']) ? $traits_payload['pais'] : benditoai_modelos_ai_text('etnia');

    if (!empty($traits_payload['pais2']) && $traits_payload['pais2'] !== 'Ninguno') {
        $etnia = !empty($etnia)
            ? $etnia . ' + ' . $traits_payload['pais2']
            : $traits_payload['pais2'];
    }

    $estilo = !empty($traits_payload) ? 'influencer-ai' : benditoai_modelos_ai_text('estilo', 'referencia-personal');

    $prenda_superior = !empty($traits_payload['peinado']) ? 'Peinado: ' . $traits_payload['peinado'] : benditoai_modelos_ai_textarea('prenda_superior');
    $prenda_inferior = !empty($traits_payload['color_pelo']) ? 'Color de pelo: ' . $traits_payload['color_pelo'] : benditoai_modelos_ai_textarea('prenda_inferior');
    $zapatos = !empty($traits_payload['ojos']) ? 'Ojos: ' . $traits_payload['ojos'] : benditoai_modelos_ai_textarea('zapatos');

    $accesorios_parts = [];

    if (!empty($traits_payload['hoyuelos']) && $traits_payload['hoyuelos'] === '1') {
        $accesorios_parts[] = 'Hoyuelos';
    }

    if (!empty($traits_payload['barba']) && $traits_payload['barba'] === '1') {
        $accesorios_parts[] = 'Barba';
    }

    if (!empty($traits_payload['bronceado']) && $traits_payload['bronceado'] === '1') {
        $accesorios_parts[] = 'Bronceado';
    }

    if (!empty($traits_payload['detalles'])) {
        $accesorios_parts[] = 'Detalles: ' . $traits_payload['detalles'];
    }

    $accesorios = !empty($accesorios_parts)
        ? implode(' | ', $accesorios_parts)
        : benditoai_modelos_ai_textarea('accesorios');

    $prompt = benditoai_modelos_ai_build_final_prompt(
        $nombre_modelo,
        $descripcion_modelo,
        $traits_payload,
        $generated_prompt
    );

    $response = benditoai_call_gemini($reference_base64, $prompt);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'Error llamando Gemini: ' . $response->get_error_message()]);
    }

    $status_code = (int) wp_remote_retrieve_response_code($response);
    $raw_body = wp_remote_retrieve_body($response);
    $body = json_decode($raw_body, true);

    if ($status_code < 200 || $status_code >= 300) {
        $api_error = benditoai_modelos_ai_extract_gemini_error($status_code, $body);
        wp_send_json_error(['message' => 'Gemini devolvió un error: ' . $api_error]);
    }

    $generated_base64 = benditoai_modelos_ai_extract_image_base64($body);

    if (empty($generated_base64)) {
        $api_error = benditoai_modelos_ai_extract_gemini_error($status_code, $body);
        wp_send_json_error(['message' => 'Gemini no devolvió imagen del modelo. Detalle: ' . $api_error]);
    }

    $saved_image = benditoai_modelos_ai_normalize_and_save_image($generated_base64, 'modelo_ai');
    if (is_wp_error($saved_image)) {
        wp_send_json_error(['message' => $saved_image->get_error_message()]);
    }

    $image_url = $saved_image['url'];

    global $wpdb;
    $table_name = benditoai_modelos_ai_get_table_name();
    $created_at = current_time('mysql');

    $inserted = $wpdb->insert(
        $table_name,
        [
            'user_id' => $user_id,
            'nombre_modelo' => $nombre_modelo,
            'descripcion_modelo' => $descripcion_modelo,
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
            'image_url' => $image_url,
            'is_public' => $is_public,
            'reference_source' => $reference_source,
            'traits_payload' => $traits_payload_raw,
            'created_at' => $created_at,
        ],
        [
            '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s'
        ]
    );

    if (!$inserted) {
        wp_send_json_error(['message' => 'No se pudo guardar el modelo en la base de datos.']);
    }

    $modelo_id = $wpdb->insert_id;
    benditoai_decrease_tokens($user_id, 1);
    $tokens_restantes = benditoai_get_user_tokens($user_id);

    wp_send_json_success([
        'id' => $modelo_id,
        'image_url' => $image_url,
        'tokens' => $tokens_restantes,
        'nombre_modelo' => $nombre_modelo,
        'genero' => $genero,
        'edad' => $edad,
        'estilo' => $estilo,
        'descripcion_modelo' => $descripcion_modelo,
        'is_public' => $is_public,
        'prompt' => $prompt,
        'generated_prompt' => $generated_prompt,
        'traits' => $traits_payload,
        'traits_payload' => $traits_payload_raw,
        'reference_source' => $reference_source,
        'reference_image_url' => $reference_image_url,
        'fecha' => date('d/m/Y H:i', strtotime($created_at)),
    ]);
}
