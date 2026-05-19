<?php

if (!defined('ABSPATH')) exit;

function benditoai_gemini_campaign_image_part($image) {
    if (is_string($image)) {
        $image = array(
            'mime' => 'image/png',
            'data' => $image,
        );
    }

    if (!is_array($image)) {
        return null;
    }

    $mime = isset($image['mime']) ? (string) $image['mime'] : 'image/png';
    $data = isset($image['data']) ? (string) $image['data'] : '';

    if ($data === '') {
        return null;
    }

    return array(
        'inline_data' => array(
            'mime_type' => $mime !== '' ? $mime : 'image/png',
            'data' => $data,
        ),
    );
}

function benditoai_call_gemini_campaign($primary_image, $extra_images, $prompt, $aspect_ratio = '1:1', $image_size = '1K', $options = array()) {
    $api_key = defined('BENDITOAI_GEMINI_KEY') ? BENDITOAI_GEMINI_KEY : '';
    $model = isset($options['model']) && $options['model'] !== ''
        ? (string) $options['model']
        : 'gemini-3.1-flash-image-preview';

    $parts = array();
    $primary_part = benditoai_gemini_campaign_image_part($primary_image);

    if ($primary_part) {
        $parts[] = $primary_part;
    }

    if (is_array($extra_images)) {
        foreach ($extra_images as $image) {
            $part = benditoai_gemini_campaign_image_part($image);
            if ($part) {
                $parts[] = $part;
            }
        }
    }

    $parts[] = array(
        'text' => $prompt,
    );

    $image_config = array();
    if ($aspect_ratio !== '') {
        $image_config['aspectRatio'] = (string) $aspect_ratio;
    }
    if ($image_size !== '') {
        $image_config['imageSize'] = (string) $image_size;
    }

    $generation_config = array(
        'responseModalities' => array('Image'),
    );

    if (!empty($image_config)) {
        $generation_config['responseFormat'] = array(
            'image' => $image_config,
        );
    }

    $body = array(
        'contents' => array(
            array(
                'parts' => $parts,
            ),
        ),
        'generationConfig' => $generation_config,
    );

    return wp_remote_post(
        "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$api_key",
        array(
            'body' => wp_json_encode($body),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 120,
        )
    );
}
