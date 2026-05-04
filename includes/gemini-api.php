<?php

if (!defined('ABSPATH')) exit;

function benditoai_call_gemini($base64_image, $prompt) {

    $api_key = defined('BENDITOAI_GEMINI_KEY') ? BENDITOAI_GEMINI_KEY : ''; //clave mario (ingenieromarior)
    //$api_key = defined('BENDITOAI_GEMINI_KEY_NATA') ? BENDITOAI_GEMINI_KEY_NATA : ''; //clave natalia (hrojas26)
     $model = 'gemini-3.1-flash-image-preview'; //modelo barato nano banana 2
    //$model = 'gemini-3-pro-image-preview'; //modelo caro nano banana pro
    

    $body = array(
        "contents" => array(
            array(
                "parts" => array(
                    array(
                        "inline_data" => array(
                            "mime_type" => "image/png",
                            "data" => $base64_image
                        )
                    ),
                    array(
                        "text" => $prompt
                    )
                )
            )
        ),
        "generationConfig" => array(
            "responseModalities" => array("IMAGE")
        )
    );

    return wp_remote_post(
        "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$api_key",
        array(
            'body'    => json_encode($body),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 120
        )
    );
}

if (!function_exists('benditoai_extract_gemini_error_message')) {
    function benditoai_extract_gemini_error_message($body) {
        if (!is_array($body)) {
            return '';
        }

        $api_message = trim((string) ($body['error']['message'] ?? ''));
        if ($api_message !== '') {
            return $api_message;
        }

        $block_reason = trim((string) ($body['promptFeedback']['blockReasonMessage'] ?? ''));
        if ($block_reason !== '') {
            return $block_reason;
        }

        $finish_reason = trim((string) ($body['candidates'][0]['finishReason'] ?? ''));
        if ($finish_reason !== '' && strtoupper($finish_reason) !== 'STOP') {
            return 'Gemini finalizÃ³ sin imagen. RazÃ³n: ' . $finish_reason;
        }

        return '';
    }
}

