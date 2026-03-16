<?php

if (!defined('ABSPATH')) {
    exit;
}

function benditoai_call_gemini_text($prompt) {

    $api_key = 'AIzaSyDr2DWui6PQpUMtOF4TkuEcmB2-LtCjlf8';

    $model = 'gemini-3.1-flash-image-preview';

    $body = array(
        "contents" => array(
            array(
                "parts" => array(
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
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'timeout' => 120
        )
    );
}