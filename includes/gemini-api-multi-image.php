<?php

if (!defined('ABSPATH')) exit;

/**
 * GEMINI MULTI IMAGE
 * Permite enviar múltiples imágenes + prompt
 */
function benditoai_call_gemini_multi($imagenes_base64, $prompt) {

    // 🔐 API KEY
    $api_key = defined('BENDITOAI_GEMINI_KEY') ? BENDITOAI_GEMINI_KEY : '';

    // 🤖 MODELO
    $model = 'gemini-3.1-flash-image-preview';
    // $model = 'gemini-3-pro-image-preview';

    // 🧠 PARTES DEL REQUEST
    $parts = [];

    /**
     * 🔥 IMPORTANTE:
     * Primero el texto para guiar el modelo
     */
    $parts[] = [
        "text" => $prompt
    ];

    /**
     * 📸 Luego las imágenes
     */
    foreach ($imagenes_base64 as $img) {

        $parts[] = [
            "inline_data" => [
                "mime_type" => "image/png",
                "data" => $img
            ]
        ];
    }

    /**
     * 🧾 BODY FINAL
     */
    $body = [
        "contents" => [
            [
                "parts" => $parts
            ]
        ],
        "generationConfig" => [
            "responseModalities" => ["IMAGE"]
        ]
    ];

    /**
     * 🚀 REQUEST
     */
    $response = wp_remote_post(
        "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$api_key",
        [
            'body'    => json_encode($body),
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 120
        ]
    );

    // 🪵 DEBUG (opcional)
    // error_log('Gemini Multi Response: ' . wp_remote_retrieve_body($response));

    return $response;
}