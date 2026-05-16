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
            <img src="https://tottoco.vteximg.com.br/arquivos/whatsapp-verde-icon-redimension50.svg" alt="WhatsApp" class="benditoai-wa-chat__fab-icon">
        </button>

        <section class="benditoai-wa-chat__panel" data-wa-panel hidden aria-live="polite">
            <header class="benditoai-wa-chat__head">
                <strong>Asistente Bendito AI</strong>
                <button type="button" class="benditoai-wa-chat__close" data-wa-close aria-label="Cerrar chat">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </header>
            <div class="benditoai-wa-chat__messages" data-wa-messages>
                <div class="benditoai-wa-chat__msg is-bot">Hola, soy tu asistente. Preguntame lo que necesites.</div>
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

    // Respuesta local para preguntas sobre planes (sin depender de API externa).
    $normalized = strtolower(remove_accents($message));
    $normalized = preg_replace('/[^a-z0-9\\s]/', ' ', $normalized);
    $normalized = preg_replace('/\\s+/', ' ', trim((string) $normalized));

    $is_plan_intent = false;
    $plan_keywords = array(
        'plan',
        'planes',
        'precio',
        'precios',
        'membresia',
        'membresias',
        'suscripcion',
        'suscripciones',
        'plan op',
        'planes op',
    );

    foreach ($plan_keywords as $keyword) {
        if ($normalized !== '' && strpos($normalized, $keyword) !== false) {
            $is_plan_intent = true;
            break;
        }
    }

    if ($is_plan_intent) {
        $plans = function_exists('benditoai_get_plans') ? benditoai_get_plans() : array();
        $plan_items = array();

        foreach (array('starter', 'pro', 'elite') as $plan_key) {
            if (!isset($plans[$plan_key]) || !is_array($plans[$plan_key])) {
                continue;
            }
            $plan = $plans[$plan_key];
            $plan_items[] = array(
                'key' => $plan_key,
                'name' => isset($plan['name']) ? (string) $plan['name'] : ucfirst($plan_key),
                'tokens' => (int) ($plan['tokens'] ?? 0),
                'max_modelos' => (int) ($plan['max_modelos'] ?? 0),
                'max_outfits' => (int) ($plan['max_outfits'] ?? 0),
                'badge' => $plan_key === 'pro' ? 'Recomendado' : '',
            );
        }

        wp_send_json_success(array(
            'reply' => 'Estos son los planes activos y sus limites:',
            'ui' => array(
                'type' => 'plans',
                'title' => 'Planes disponibles',
                'items' => $plan_items,
                'cta_label' => 'Adquirir plan',
                'cta_url' => home_url('/planes/'),
            ),
        ));
    }

    $fallback = "Te leo perfecto. Si quieres, te ayudo paso a paso con modelos, outfits, campanas o prompts.";
    $api_key = defined('BENDITOAI_GEMINI_KEY') ? (string) BENDITOAI_GEMINI_KEY : '';
    if ($api_key === '') {
        wp_send_json_success(array('reply' => $fallback));
    }

    $system = "Eres el asistente de Bendito AI. Responde en espanol claro, breve y util. Maximo 4 lineas. "
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
