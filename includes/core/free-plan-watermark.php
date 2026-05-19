<?php
if (!defined('ABSPATH')) {
    exit;
}

function benditoai_is_free_plan_user($user_id) {
    $user_id = (int) $user_id;
    if ($user_id <= 0) {
        return false;
    }

    $plan_key = 'starter';
    if (function_exists('benditoai_get_user_plan_key')) {
        $plan_key = (string) benditoai_get_user_plan_key($user_id, false);
    } else {
        $plan_key = (string) get_user_meta($user_id, 'benditoai_plan', true);
    }

    if (function_exists('benditoai_normalize_plan_key')) {
        $plan_key = (string) benditoai_normalize_plan_key($plan_key);
    } else {
        $plan_key = strtolower(trim($plan_key));
    }

    return $plan_key === 'starter';
}

function benditoai_apply_free_plan_watermark($file_path, $user_id) {
    $file_path = (string) $file_path;
    if ($file_path === '' || !file_exists($file_path)) {
        return false;
    }

    if (!benditoai_is_free_plan_user($user_id)) {
        return false;
    }

    if (!function_exists('imagecreatefrompng') || !function_exists('imagecopyresampled')) {
        return false;
    }

    $logo_path = BENDIDOAI_PLUGIN_PATH . 'assets/images/icobenbla.png';
    if (!file_exists($logo_path)) {
        return false;
    }

    $image_binary = file_get_contents($file_path);
    if ($image_binary === false || $image_binary === '') {
        return false;
    }

    $base = @imagecreatefromstring($image_binary);
    if (!$base) {
        return false;
    }

    $logo = @imagecreatefrompng($logo_path);
    if (!$logo) {
        imagedestroy($base);
        return false;
    }

    $base_w = imagesx($base);
    $base_h = imagesy($base);
    $logo_w = imagesx($logo);
    $logo_h = imagesy($logo);

    if ($base_w <= 0 || $base_h <= 0 || $logo_w <= 0 || $logo_h <= 0) {
        imagedestroy($base);
        imagedestroy($logo);
        return false;
    }

    $target_w = max(36, (int) round($base_w * 0.14));
    $target_w = min($target_w, 220);
    $scale = $target_w / $logo_w;
    $target_h = max(16, (int) round($logo_h * $scale));

    $pad = max(10, (int) round(min($base_w, $base_h) * 0.018));
    $dst_x = max(0, $base_w - $target_w - $pad);
    $dst_y = max(0, $base_h - $target_h - $pad);

    $logo_resized = imagecreatetruecolor($target_w, $target_h);
    imagealphablending($logo_resized, false);
    imagesavealpha($logo_resized, true);
    $transparent = imagecolorallocatealpha($logo_resized, 0, 0, 0, 127);
    imagefill($logo_resized, 0, 0, $transparent);
    imagecopyresampled($logo_resized, $logo, 0, 0, 0, 0, $target_w, $target_h, $logo_w, $logo_h);

    imagealphablending($base, true);
    imagesavealpha($base, true);
    imagecopy($base, $logo_resized, $dst_x, $dst_y, 0, 0, $target_w, $target_h);

    $ext = strtolower((string) pathinfo($file_path, PATHINFO_EXTENSION));
    $saved = false;
    if ($ext === 'jpg' || $ext === 'jpeg') {
        $saved = imagejpeg($base, $file_path, 92);
    } elseif ($ext === 'webp' && function_exists('imagewebp')) {
        $saved = imagewebp($base, $file_path, 92);
    } else {
        $saved = imagepng($base, $file_path, 6);
    }

    imagedestroy($logo_resized);
    imagedestroy($logo);
    imagedestroy($base);

    return (bool) $saved;
}
