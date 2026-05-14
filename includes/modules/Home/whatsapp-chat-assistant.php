<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_benditoai_home_chat_assistant', 'benditoai_home_chat_assistant');
add_action('wp_ajax_nopriv_benditoai_home_chat_assistant', 'benditoai_home_chat_assistant');

function benditoai_home_chat_render_widget() {
    if (is_admin() || !is_front_page()) {
        return;
    }
    ?>
    <div class="benditoai-wa-chat" data-benditoai-wa-chat>
        <button type="button" class="benditoai-wa-chat__fab" data-wa-fab aria-label="Abrir chat inteligente">
            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
        </button>

        <section class="benditoai-wa-chat__panel" data-wa-panel hidden aria-live="polite">
            <header class="benditoai-wa-chat__head">
                <strong>Asistente Bendito AI</strong>
                <button type="button" class="benditoai-wa-chat__close" data-wa-close aria-label="Cerrar chat">
                    <i class="fas fa-xmark" aria-hidden="true"></i>
                </button>
            </header>
            <div class="benditoai-wa-chat__messages" data-wa-messages>
                <div class="benditoai-wa-chat__msg is-bot">Hola, soy tu asistente. Pregúntame lo que necesites.</div>
            </div>
            <form class="benditoai-wa-chat__form" data-wa-form>
                <input type="text" data-wa-input placeholder="Escribe tu mensaje..." maxlength="700" required>
                <button type="submit">Enviar</button>
            </form>
        </section>
    </div>
    <?php
}
add_action('wp_footer', 'benditoai_home_chat_render_widget', 50);

function benditoai_home_chat_assistant() {
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    if ($message === '') {
        wp_send_json_error(array('message' => 'Escribe un mensaje.'));
    }

    $fallback = "Te leo perfecto. Si quieres, te ayudo paso a paso con modelos, outfits, campañas o prompts.";
    $api_key = defined('BENDITOAI_GEMINI_KEY') ? (string) BENDITOAI_GEMINI_KEY : '';
    if ($api_key === '') {
        wp_send_json_success(array('reply' => $fallback));
    }

    $system = "Eres el asistente de Bendito AI. Responde en español claro, breve y útil. Máximo 4 líneas. "
        . "Ayuda en marketing, moda, prompts e interfaz web. Si falta contexto, pregunta solo 1 cosa concreta.";

    $body = array(
        "contents" => array(
            array(
                "parts" => array(
                    array("text" => $system . "\n\nUsuario: " . $message)
                )
            )
        ),
        "generationConfig" => array(
            "responseModalities" => array("TEXT"),
            "temperature" => 0.7,
            "maxOutputTokens" => 220
        )
    );

    $response = wp_remote_post(
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$api_key}",
        array(
            'body'    => wp_json_encode($body),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 35
        )
    );

    if (is_wp_error($response)) {
        wp_send_json_success(array('reply' => $fallback));
    }

    $json = json_decode(wp_remote_retrieve_body($response), true);
    $text = '';
    if (!empty($json['candidates'][0]['content']['parts']) && is_array($json['candidates'][0]['content']['parts'])) {
        foreach ($json['candidates'][0]['content']['parts'] as $part) {
            if (!empty($part['text'])) {
                $text .= (string) $part['text'];
            }
        }
    }

    $text = trim($text);
    if ($text === '') {
        $text = $fallback;
    }

    wp_send_json_success(array('reply' => $text));
}

