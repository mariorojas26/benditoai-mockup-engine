<?php
if (!defined('ABSPATH')) {
    exit;
}

function benditoai_get_image_asset($src, $fallback = '', $args = array()) {
    $allow_external = array_key_exists('allow_external', $args) ? (bool) $args['allow_external'] : true;
    $src = trim((string) $src);
    $fallback = ltrim((string) $fallback, '/');

    if ($src === '') {
        $src = $fallback;
    }

    if (filter_var($src, FILTER_VALIDATE_URL)) {
        $plugin_url = trailingslashit(BENDIDOAI_PLUGIN_URL);

        if (strpos($src, $plugin_url) === 0) {
            $src = ltrim(substr($src, strlen($plugin_url)), '/');
        } elseif ($allow_external) {
            return array(
                'url' => esc_url_raw($src),
                'webp_url' => '',
                'width' => '',
                'height' => '',
            );
        } else {
            $src = $fallback;
        }
    }

    $relative_path = ltrim((string) $src, '/');

    if (strpos($relative_path, 'assets/images/') !== 0) {
        $relative_path = $fallback;
    }

    $absolute_path = BENDIDOAI_PLUGIN_PATH . $relative_path;

    if ($relative_path === '' || !file_exists($absolute_path)) {
        $relative_path = $fallback;
        $absolute_path = BENDIDOAI_PLUGIN_PATH . $relative_path;
    }

    $url = BENDIDOAI_PLUGIN_URL . $relative_path;
    $webp_url = '';
    $webp_path = preg_replace('/\.(jpe?g|png)$/i', '.webp', $absolute_path);

    if ($webp_path && $webp_path !== $absolute_path && file_exists($webp_path)) {
        $webp_url = BENDIDOAI_PLUGIN_URL . preg_replace('/\.(jpe?g|png)$/i', '.webp', $relative_path);
    }

    $width = '';
    $height = '';
    $image_size = file_exists($absolute_path) ? @getimagesize($absolute_path) : false;

    if (is_array($image_size)) {
        $width = isset($image_size[0]) ? (int) $image_size[0] : '';
        $height = isset($image_size[1]) ? (int) $image_size[1] : '';
    }

    return array(
        'url' => esc_url_raw($url),
        'webp_url' => esc_url_raw($webp_url),
        'width' => $width,
        'height' => $height,
    );
}

function benditoai_image_dimension_attrs($asset) {
    if (empty($asset['width']) || empty($asset['height'])) {
        return '';
    }

    return ' width="' . esc_attr($asset['width']) . '" height="' . esc_attr($asset['height']) . '"';
}

function benditoai_page_has_shortcode($shortcodes) {
    if (is_admin() || !is_singular()) {
        return false;
    }

    $post = get_post();

    if (!$post || empty($post->post_content)) {
        return false;
    }

    foreach ((array) $shortcodes as $shortcode) {
        if (has_shortcode($post->post_content, $shortcode)) {
            return true;
        }
    }

    return false;
}
