<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_benditoai_generar_campana', 'benditoai_generar_campana');

function benditoai_campana_text($key, $default = '') {
    return isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
}

function benditoai_campana_textarea($key, $default = '') {
    return isset($_POST[$key]) ? sanitize_textarea_field(wp_unslash($_POST[$key])) : $default;
}

function benditoai_campana_json_array($key) {
    if (empty($_POST[$key])) {
        return array();
    }

    $raw = wp_unslash($_POST[$key]);
    $decoded = json_decode($raw, true);

    return is_array($decoded) ? $decoded : array();
}

function benditoai_campana_normalize_base64_image($value) {
    $value = is_string($value) ? trim($value) : '';

    if ($value === '') {
        return null;
    }

    $mime = 'image/png';

    if (preg_match('#^data:(image/(?:png|jpe?g|webp));base64,#i', $value, $matches)) {
        $mime = strtolower($matches[1]);
        if ($mime === 'image/jpg') {
            $mime = 'image/jpeg';
        }
        $value = preg_replace('#^data:image/(?:png|jpe?g|webp);base64,#i', '', $value);
    } else {
        $value = preg_replace('#^data:image/\w+;base64,#i', '', $value);
    }

    $value = preg_replace('/\s+/', '', $value);

    if ($value === '' || base64_decode($value, true) === false) {
        return null;
    }

    return array(
        'mime' => $mime,
        'data' => $value,
    );
}

function benditoai_campana_read_remote_image($url) {
    $url = esc_url_raw($url);

    if ($url === '') {
        return null;
    }

    $response = wp_remote_get($url, array('timeout' => 30));

    if (is_wp_error($response)) {
        return null;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        return null;
    }

    $body = wp_remote_retrieve_body($response);
    if ($body === '') {
        return null;
    }

    $mime = wp_remote_retrieve_header($response, 'content-type');
    $mime = is_string($mime) && preg_match('#^image/(png|jpe?g|webp)#i', $mime) ? strtolower(strtok($mime, ';')) : 'image/png';
    if ($mime === 'image/jpg') {
        $mime = 'image/jpeg';
    }

    return array(
        'mime' => $mime,
        'data' => base64_encode($body),
    );
}

function benditoai_campana_extract_image_base64($body) {
    if (isset($body['candidates'][0]['content']['parts']) && is_array($body['candidates'][0]['content']['parts'])) {
        foreach ($body['candidates'][0]['content']['parts'] as $part) {
            if (!empty($part['inlineData']['data'])) {
                return (string) $part['inlineData']['data'];
            }

            if (!empty($part['inline_data']['data'])) {
                return (string) $part['inline_data']['data'];
            }
        }
    }

    return '';
}

function benditoai_campana_debug_summary($value) {
    if (is_string($value)) {
        return substr($value, 0, 900);
    }

    return substr(wp_json_encode($value), 0, 900);
}

function benditoai_campana_log_error($debug_code, $format_label = '', $context = array()) {
    $payload = array_merge(
        array(
            'debug_code' => $debug_code,
            'format' => $format_label,
        ),
        is_array($context) ? $context : array()
    );

    error_log('[BenditoAI Campaign] ' . wp_json_encode($payload));
}

function benditoai_campana_format_image_size($format_id, $format = array()) {
    if (isset($format['imageSize']) && $format['imageSize'] !== '') {
        return sanitize_text_field($format['imageSize']);
    }

    if (isset($format['image_size']) && $format['image_size'] !== '') {
        return sanitize_text_field($format['image_size']);
    }

    return '1K';
}

function benditoai_campana_save_image($image_base64, $user_id) {
    $image = base64_decode($image_base64);

    if ($image === false) {
        return null;
    }

    $upload = wp_upload_dir();
    if (!empty($upload['error'])) {
        return null;
    }

    $filename = 'campana_' . time() . '_' . wp_generate_password(6, false, false) . '.png';
    $path = trailingslashit($upload['path']) . $filename;

    $saved = file_put_contents($path, $image);
    if (!$saved) {
        return null;
    }

    if (function_exists('benditoai_apply_free_plan_watermark')) {
        benditoai_apply_free_plan_watermark($path, $user_id);
    }

    return trailingslashit($upload['url']) . $filename;
}

function benditoai_campana_get_owned_model_image($user_id, $model_id, $outfit_id, $fallback_url = '') {
    global $wpdb;

    $model_id = (int) $model_id;
    $outfit_id = (int) $outfit_id;

    if ($outfit_id > 0) {
        $outfits_table = $wpdb->prefix . 'benditoai_modelos_ai_outfits';
        $outfit = $wpdb->get_row($wpdb->prepare(
            "SELECT image_url, modelo_id, nombre_outfit, outfit_tag FROM $outfits_table WHERE id = %d AND user_id = %d",
            $outfit_id,
            $user_id
        ));

        if ($outfit && !empty($outfit->image_url)) {
            return array(
                'url' => (string) $outfit->image_url,
                'modelo_id' => (int) $outfit->modelo_id,
                'outfit_name' => (string) $outfit->nombre_outfit,
                'outfit_tag' => (string) $outfit->outfit_tag,
            );
        }
    }

    if ($model_id > 0) {
        $modelos_table = $wpdb->prefix . 'benditoai_modelos_ai';
        $modelo = $wpdb->get_row($wpdb->prepare(
            "SELECT id, image_url, nombre_modelo FROM $modelos_table WHERE id = %d AND user_id = %d",
            $model_id,
            $user_id
        ));

        if ($modelo && !empty($modelo->image_url)) {
            return array(
                'url' => (string) $modelo->image_url,
                'modelo_id' => (int) $modelo->id,
                'outfit_name' => 'Principal',
                'outfit_tag' => 'principal',
            );
        }
    }

    if ($fallback_url !== '') {
        return array(
            'url' => esc_url_raw($fallback_url),
            'modelo_id' => $model_id > 0 ? $model_id : null,
            'outfit_name' => '',
            'outfit_tag' => '',
        );
    }

    return null;
}

function benditoai_campana_build_prompt($data) {
    $flow = $data['use_model'] ? 'with_model' : 'without_model';
    $model_is_product = !empty($data['model_is_product']);
    $angle_instruction = '';

    if (!empty($data['vary_background'])) {
        $angle_instruction = "\n- Background angle variation: create angle " . (int) $data['variation_index'] . " of the same scenario. Keep the same location, materials, color palette and campaign identity, but vary camera angle, crop, depth and product placement.";
    }

    $copy_instruction = '';
    if ($data['slogan'] !== '' || $data['cta'] !== '' || $data['copy_direction'] !== '') {
        $copy_instruction = "\nMarketing text and layout guidance:
- Main phrase: {$data['slogan']}
- CTA: {$data['cta']}
- Creative copy direction: {$data['copy_direction']}
- Integrate the words as part of the final advertising design, not as a separate overlay.
- Place text where it has clean negative space and strong contrast, adapted to the selected format.
- Build a clear hierarchy: main phrase first, product second, CTA last.
- Use premium, legible typography with professional spacing, no distorted letters, no random extra text.";
    }

    $background = $data['custom_background'] !== '' ? $data['custom_background'] : $data['background'];
    $colors = trim($data['custom_colors'] !== '' ? $data['custom_colors'] : $data['palette_colors']);
    if ($colors === '') {
        $colors = $data['palette'];
    }

    $product_route = $model_is_product
        ? "The selected model/outfit is the product. Treat the visible clothing, styling, logos, colors and materials on the model as the commercial item to advertise."
        : "The uploaded product reference images define the commercial product to advertise.";

    $prompt = "Create a professional AI marketing campaign image.

Product:
- Name: {$data['product']}
- Category: {$data['category']}
- Product route: {$product_route}

Visual direction:
- Campaign tone: {$data['tone']}
- Image style: {$data['style']}
- Color palette: {$data['palette']} ({$colors})
- Background/environment: {$background}
- Output format: {$data['format_label']} {$data['format_ratio']} {$data['format_size']} ({$data['image_size']})
{$copy_instruction}

Core rules:
- The product must remain the main commercial focus.
- Preserve the product design, logo, colors, shape and materials.
- Use premium advertising composition, realistic lighting and polished post-production.
- Make the result feel ready for paid ads, social media and ecommerce.
- Compose specifically for {$data['format_label']} with safe margins, balanced negative space and platform-ready framing.
- Ultra realistic, sharp, high detail, professional 4K quality.
- Avoid distorted text, extra logos, watermark-like artifacts and malformed objects.
- If API sizing is not followed exactly, still visually compose in {$data['format_ratio']} aspect ratio.
{$angle_instruction}
";

    if ($flow === 'with_model') {
        if ($model_is_product) {
            $prompt .= "
Model-as-product campaign route:
- Use the provided model/outfit reference as the primary product image and identity reference.
- Keep the same person, pose credibility, clothing, logos, textures, colors and outfit styling consistent.
- Make the campaign showcase the outfit/clothing already worn by the model; do not invent a separate product.
- The model and outfit are the hero. Build a fashion/brand campaign around that look.
";
        } else {
            $prompt .= "
Model integration:
- Use the provided model/outfit reference as the same person and styling reference.
- Keep identity, face, proportions and outfit consistent with the reference.
- Integrate the model naturally with the product.
- The model supports the product; the product still leads the campaign.
";
        }
    } else {
        $prompt .= "
No-model campaign route:
- Do not include people, faces, hands, silhouettes or human body parts.
- Build the campaign around product, set design, lighting, props, brand atmosphere and composition.
- Make the scene feel intentional and premium without needing a model.
";
    }

    return $prompt;
}

function benditoai_generar_campana() {

    if (!is_user_logged_in()) {
        wp_send_json_error('No auth');
    }

    $user_id = get_current_user_id();

    $producto = benditoai_campana_text('producto');
    $categoria = benditoai_campana_text('categoria');
    $use_model = benditoai_campana_text('use_model', '0') === '1';
    $product_mode = sanitize_key(benditoai_campana_text('product_mode', 'upload_product'));
    $model_is_product = $use_model && $product_mode === 'model_product';
    $model_id = (int) benditoai_campana_text('model_id', '0');
    $outfit_id = (int) benditoai_campana_text('outfit_id', '0');
    $model_url = esc_url_raw(benditoai_campana_text('model_url'));
    $tono = benditoai_campana_text('tono');
    $estilo = benditoai_campana_text('estilo');
    $paleta = benditoai_campana_text('paleta');
    $paleta_colores = benditoai_campana_text('paleta_colores');
    $colores_custom = benditoai_campana_text('colores_custom');
    $fondo_preset = benditoai_campana_text('fondo_preset');
    $fondo_custom = benditoai_campana_textarea('fondo_custom');
    $slogan = benditoai_campana_text('slogan');
    $cta = benditoai_campana_text('cta');
    $copy_direction = benditoai_campana_textarea('copy_direction');
    $vary_background = benditoai_campana_text('generar_angulos_fondo', '0') === '1';

    $product_images_raw = benditoai_campana_json_array('product_images');
    if (empty($product_images_raw) && !empty($_POST['product_image'])) {
        $product_images_raw = array(wp_unslash($_POST['product_image']));
    }

    $formats = benditoai_campana_json_array('formatos');

    if (!$model_is_product && ($producto === '' || empty($product_images_raw))) {
        wp_send_json_error('Faltan datos del producto.');
    }

    if (empty($formats)) {
        $formats = array(array(
            'id' => 'instagram',
            'label' => 'Instagram',
            'ratio' => '1:1',
            'size' => '1080x1080',
        ));
    }

    $formats = array_slice($formats, 0, 5);

    if (function_exists('benditoai_user_has_tokens') && !benditoai_user_has_tokens($user_id, count($formats))) {
        wp_send_json_error(array('message' => 'No tienes tokens suficientes para generar todos los formatos seleccionados.'));
    }

    $product_images = array();
    foreach (array_slice($product_images_raw, 0, 3) as $raw_image) {
        $normalized = benditoai_campana_normalize_base64_image($raw_image);
        if ($normalized) {
            $product_images[] = $normalized;
        }
    }

    if (empty($product_images) && !$model_is_product) {
        wp_send_json_error('Imagen de producto invalida.');
    }

    $primary_product = !$model_is_product ? array_shift($product_images) : null;
    $extra_images = array_values($product_images);
    $model_reference = null;
    $owned_model = null;

    if ($use_model) {
        $owned_model = benditoai_campana_get_owned_model_image($user_id, $model_id, $outfit_id, $model_url);

        if (!$owned_model || empty($owned_model['url'])) {
            benditoai_campana_log_error('invalid_model_reference', '', array(
                'model_id' => $model_id,
                'outfit_id' => $outfit_id,
            ));
            wp_send_json_error('Selecciona un modelo valido.');
        }

        $model_reference = benditoai_campana_read_remote_image($owned_model['url']);

        if (!$model_reference) {
            benditoai_campana_log_error('model_image_load_failed', '', array(
                'model_id' => $model_id,
                'outfit_id' => $outfit_id,
                'model_url' => $owned_model['url'],
            ));
            wp_send_json_error('No se pudo cargar la imagen del modelo.');
        }

        if ($model_is_product) {
            $primary_product = $model_reference;
        } else {
            $extra_images[] = $model_reference;
        }
    }

    if (!$primary_product) {
        wp_send_json_error('No se pudo preparar la referencia principal de la campana.');
    }

    if ($model_is_product) {
        $producto = $producto !== '' ? $producto : (string) ($owned_model['outfit_name'] ?? 'Outfit del modelo');
        $categoria = $categoria !== '' ? $categoria : 'Moda / outfit del modelo';
    }

    $generated = array();
    $errors = array();

    foreach ($formats as $index => $format) {
        $format_id = isset($format['id']) ? sanitize_key($format['id']) : 'format';
        $format_label = isset($format['label']) ? sanitize_text_field($format['label']) : ucfirst($format_id);
        $format_ratio = isset($format['ratio']) ? sanitize_text_field($format['ratio']) : '1:1';
        $format_size = isset($format['size']) ? sanitize_text_field($format['size']) : '';
        $format_image_size = benditoai_campana_format_image_size($format_id, $format);

        $prompt = benditoai_campana_build_prompt(array(
            'use_model' => $use_model,
            'model_is_product' => $model_is_product,
            'product' => $producto,
            'category' => $categoria,
            'tone' => $tono,
            'style' => $estilo,
            'palette' => $paleta,
            'palette_colors' => $paleta_colores,
            'custom_colors' => $colores_custom,
            'background' => $fondo_preset,
            'custom_background' => $fondo_custom,
            'slogan' => $slogan,
            'cta' => $cta,
            'copy_direction' => $copy_direction,
            'format_label' => $format_label,
            'format_ratio' => $format_ratio,
            'format_size' => $format_size,
            'image_size' => $format_image_size,
            'vary_background' => $vary_background,
            'variation_index' => $index + 1,
        ));

        if (!function_exists('benditoai_call_gemini_campaign')) {
            benditoai_campana_log_error('campaign_service_missing', $format_label);
            $errors[] = $format_label;
            continue;
        }

        $response = benditoai_call_gemini_campaign(
            $primary_product,
            $extra_images,
            $prompt,
            $format_ratio,
            $format_image_size,
            array(
                'format_id' => $format_id,
                'format_size' => $format_size,
            )
        );

        if (is_wp_error($response)) {
            benditoai_campana_log_error('gemini_wp_error', $format_label, array(
                'message' => $response->get_error_message(),
            ));
            $errors[] = $format_label;
            continue;
        }

        $http_code = (int) wp_remote_retrieve_response_code($response);
        $raw_body = wp_remote_retrieve_body($response);

        if ($http_code < 200 || $http_code >= 300) {
            benditoai_campana_log_error('gemini_http_error', $format_label, array(
                'http_code' => $http_code,
                'body' => benditoai_campana_debug_summary($raw_body),
            ));
            $errors[] = $format_label;
            continue;
        }

        $body = json_decode($raw_body, true);

        if (!is_array($body)) {
            benditoai_campana_log_error('gemini_invalid_json', $format_label, array(
                'http_code' => $http_code,
                'body' => benditoai_campana_debug_summary($raw_body),
            ));
            $errors[] = $format_label;
            continue;
        }

        $image_base64 = benditoai_campana_extract_image_base64($body);

        if ($image_base64 === '') {
            benditoai_campana_log_error('gemini_no_image', $format_label, array(
                'http_code' => $http_code,
                'body' => benditoai_campana_debug_summary($body),
            ));
            $errors[] = $format_label;
            continue;
        }

        $url = benditoai_campana_save_image($image_base64, $user_id);

        if (!$url) {
            benditoai_campana_log_error('image_save_failed', $format_label);
            $errors[] = $format_label;
            continue;
        }

        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'benditoai_campanas_ai',
            array(
                'user_id' => $user_id,
                'producto_url' => '',
                'modelo_id' => $owned_model && !empty($owned_model['modelo_id']) ? (int) $owned_model['modelo_id'] : null,
                'estilo' => $estilo,
                'colores' => trim($paleta . ' ' . $paleta_colores . ' ' . $colores_custom),
                'ambiente' => $fondo_custom !== '' ? $fondo_custom : $fondo_preset,
                'mood' => $tono,
                'prompt' => $prompt,
                'image_url' => $url,
            ),
            array('%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        $generated[] = array(
            'id' => $format_id,
            'label' => $format_label,
            'ratio' => $format_ratio,
            'size' => $format_size,
            'image_size' => $format_image_size,
            'image_url' => $url,
        );
    }

    if (empty($generated)) {
        wp_send_json_error(array(
            'message' => 'La IA no devolvio imagenes para los formatos seleccionados.',
            'debug_code' => 'campaign_generation_failed',
        ));
    }

    if (function_exists('benditoai_use_token')) {
        benditoai_use_token(count($generated));
    }

    $tokens = function_exists('benditoai_get_user_tokens') ? benditoai_get_user_tokens($user_id) : null;

    wp_send_json_success(array(
        'images' => $generated,
        'image_url' => $generated[0]['image_url'],
        'tokens' => $tokens,
        'partial_errors' => $errors,
    ));
}
