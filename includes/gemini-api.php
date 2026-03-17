<?php

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