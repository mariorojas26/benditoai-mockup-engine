<?php

if (!defined('ABSPATH')) exit;

function benditoai_call_gemini_multi($base64_image_1, $base64_image_2, $prompt) {

    $api_key = defined('BENDITOAI_GEMINI_KEY') ? BENDITOAI_GEMINI_KEY : '';

    $model = 'gemini-3.1-flash-image-preview';

    $body = array(
        "contents" => array(
            array(
                "parts" => array(

                    array(
                        "inline_data" => array(
                            "mime_type" => "image/png",
                            "data" => $base64_image_1
                        )
                    ),

                    array(
                        "inline_data" => array(
                            "mime_type" => "image/png",
                            "data" => $base64_image_2
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