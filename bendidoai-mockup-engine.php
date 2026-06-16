<?php
/**
 * Plugin Name: BenditoAI Mockup Engine
 * Plugin URI: https://bendidoai.com
 * Description: Motor de generaciÃ³n de mockups con IA usando Gemini.
 * Version: 1.0.0
 * Author: BendidoTrazo
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit;
}

define('BENDIDOAI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BENDIDOAI_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once BENDIDOAI_PLUGIN_PATH . 'includes/core/install.php';

/* ACTIVACIÃ“N TABLAS BASES DE DATOS*/

function bendidoai_activate_plugin() {
    // Crear tabla historial de mockups
    benditoai_create_historial_table();

    // Crear tabla de campaÃ±as AI
    benditoai_create_campanas_ai_table();

    // Crear tabla de modelos AI
    benditoai_create_modelos_ai_table();
    benditoai_create_modelos_ai_outfits_table();

    error_log('BendidoAI activado ðŸš€');
}

register_activation_hook(__FILE__, 'bendidoai_activate_plugin');
add_action('plugins_loaded', 'benditoai_maybe_upgrade_database');


/* DESACTIVACIÃ“N */

function bendidoai_deactivate_plugin() {
    error_log('BendidoAI desactivado.');
}

register_deactivation_hook(__FILE__, 'bendidoai_deactivate_plugin');


/* CARGAR COMPONENTES */

function benditoai_require_files($files) {
    foreach ($files as $file) {
        require_once BENDIDOAI_PLUGIN_PATH . $file;
    }
}

benditoai_require_files(array(
    'includes/admin/admin-menu.php',
    'includes/admin/admin-users-plans.php',

    'includes/core/variables.php',
    'includes/core/prompts.php',
    'includes/core/browser-theme.php',
    'includes/core/free-plan-watermark.php',
    'includes/core/performance-assets.php',

    'includes/services/gemini/gemini-api.php',
    'includes/services/gemini/gemini-api-text.php',
    'includes/services/gemini/gemini-api-multi-image.php',

    'includes/modules/auth/auth-redirect.php',

    'includes/modules/plans/plans-config.php',
    'includes/modules/plans/plans-functions.php',
    'includes/modules/plans/plans-init.php',
    'includes/modules/plans/ajax-update-plan.php',
    'includes/modules/plans/plans-shortcode.php',

    'includes/modules/tokens/tokens-manager.php',
    'includes/modules/tokens/tokens-shortcode.php',
    'includes/modules/tokens/ajax-get-tokens.php',
    'includes/modules/tokens/tokens-usage.php',

    'includes/modules/mockup/ajax-mockup.php',
    'includes/modules/mockup/shortcodes.php',
    'includes/modules/historial/shortcode-historial.php',
    'includes/modules/scroll-video/scroll-video-shortcode.php',
    'includes/modules/Home/gsap-cards/gsap-cards-shortcode.php',

    'includes/modules/campanas-ai/campanas-ai-shortcode.php',
    'includes/modules/campanas-ai/campanas-ai-ajax.php',

    'includes/modules/remove-bg/ajax-remove-bg.php',
    'includes/modules/remove-bg/shortcode-remove-bg.php',

    'includes/modules/enhance-image/ajax-enhance-image.php',
    'includes/modules/enhance-image/shortcode-enhance-image.php',

    'includes/modules/tendencias/trending-shortcode.php',
    'includes/modules/tendencias/trending-ajax.php',

    'includes/modules/modelos-ai/modelos-ai-shortcode.php',
    'includes/modules/modelos-ai/modelos-ai-historial-shortcode.php',
    'includes/modules/modelos-ai/modelos-ai-ajax.php',
    'includes/modules/modelos-ai/modelos-ai-delete.php',
    'includes/modules/modelos-ai/modelos-ai-edit.php',
    'includes/modules/modelos-ai/modelos-ai-outfits.php',

    'includes/modules/auth/auth-dropdown.php',
    'includes/modules/ux/maquinaEscribir.php',
    'includes/modules/ux/cardsSkills.php',
    'includes/modules/Home/antes-y-despues/before-after-shortcode.php',
    'includes/modules/Home/whatsapp-chat-assistant.php',
));


/* ESTILOS Y JS */

function benditoai_enqueue_assets() {


    // ðŸ”¥ Font Awesome version gratuita solo aceptan 5.0 https://fontawesome.com/v5/search
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
        array(),
        '6.7.2'
    );

    $styles_path = BENDIDOAI_PLUGIN_PATH . 'assets/css/styles.css';
    $styles_version = file_exists($styles_path) ? (string) filemtime($styles_path) : '1.0';

    wp_enqueue_style(
        'benditoai-styles',
        BENDIDOAI_PLUGIN_URL . 'assets/css/styles.css',
        array(),
        $styles_version
    );

    /* JS PRINCIPAL (usa imports internos) */

    $main_script_path = BENDIDOAI_PLUGIN_PATH . 'assets/js/benditoai-main.js';
    $main_script_version = file_exists($main_script_path) ? (string) filemtime($main_script_path) : '1.0';

    wp_enqueue_script(
        'benditoai-main',
        BENDIDOAI_PLUGIN_URL . 'assets/js/benditoai-main.js',
        array(),
        $main_script_version,
        true
    );

    wp_localize_script(
        'benditoai-main',
        'benditoai_ajax',
        array(
            'ajax_url'   => admin_url('admin-ajax.php'),
            'plugin_url' => BENDIDOAI_PLUGIN_URL
        )
    );

    $conditional_scripts = array(
        'benditoai-mockup-generator' => 'assets/js/mockup/mockup-generator.js',
        'benditoai-camiseta-toggle' => 'assets/js/mockup/camiseta-toggle.js',
        'benditoai-remove-bg' => 'assets/js/remove-bg/remove-bg.js',
        'benditoai-enhance-image' => 'assets/js/enhance/enhance-image.js',
        'benditoai-trending-generator' => 'assets/js/trending/trending-generator.js',
        'benditoai-trending-ui' => 'assets/js/trending/trending-ui.js',
        'benditoai-modelo-delete' => 'assets/js/modelos/delete-modelo.js',
        'benditoai-modelo-edit' => 'assets/js/modelos/edit-modelo.js',
        'benditoai-saved-outfits' => 'assets/js/modelos/saved-outfits.js',
        'benditoai-use-for-campana-bridge' => 'assets/js/modelos/use-for-campana-bridge.js',
        'benditoai-campanas-ai' => 'assets/js/campanas/campanas-ai-script.js',
        'benditoai-whatsapp-chat-assistant' => 'assets/js/home/whatsapp-chat-assistant.js',
    );

    foreach ($conditional_scripts as $handle => $relative_path) {
        $script_path = BENDIDOAI_PLUGIN_PATH . $relative_path;
        $script_version = file_exists($script_path) ? (string) filemtime($script_path) : '1.0';

        wp_register_script(
            $handle,
            BENDIDOAI_PLUGIN_URL . $relative_path,
            array('benditoai-main'),
            $script_version,
            true
        );
    }

    if (benditoai_page_has_shortcode(array('benditoai_crear_mockup'))) {
        wp_enqueue_script('benditoai-mockup-generator');
        wp_enqueue_script('benditoai-camiseta-toggle');
    }

    if (benditoai_page_has_shortcode(array('benditoai_remove_bg'))) {
        wp_enqueue_script('benditoai-remove-bg');
    }

    if (benditoai_page_has_shortcode(array('benditoai_enhance_image'))) {
        wp_enqueue_script('benditoai-enhance-image');
    }

    if (benditoai_page_has_shortcode(array('benditoai_trending'))) {
        wp_enqueue_script('benditoai-trending-generator');
        wp_enqueue_script('benditoai-trending-ui');
    }

    if (benditoai_page_has_shortcode(array('benditoai_modelos_ai_historial'))) {
        wp_enqueue_script('benditoai-modelo-delete');
        wp_enqueue_script('benditoai-modelo-edit');
        wp_enqueue_script('benditoai-saved-outfits');
        wp_enqueue_script('benditoai-use-for-campana-bridge');
    }

    if (benditoai_page_has_shortcode(array('benditoai_campanas_ai'))) {
        wp_enqueue_script('benditoai-campanas-ai');
    }

    if (is_front_page()) {
        wp_enqueue_script('benditoai-whatsapp-chat-assistant');
    }

    $scroll_video_script_path = BENDIDOAI_PLUGIN_PATH . 'assets/js/scroll-video.js';
    $scroll_video_script_version = file_exists($scroll_video_script_path) ? (string) filemtime($scroll_video_script_path) : '1.0';

    wp_register_script(
        'benditoai-scroll-video',
        BENDIDOAI_PLUGIN_URL . 'assets/js/scroll-video.js',
        array(),
        $scroll_video_script_version,
        true 
    );

    $gsap_cards_style_path = BENDIDOAI_PLUGIN_PATH . 'assets/css/gsap-cards.css';
    $gsap_cards_style_version = file_exists($gsap_cards_style_path) ? (string) filemtime($gsap_cards_style_path) : '1.0';

    wp_register_style(
        'benditoai-gsap-cards',
        BENDIDOAI_PLUGIN_URL . 'assets/css/gsap-cards.css',
        array('benditoai-styles'),
        $gsap_cards_style_version
    );

    wp_register_script(
        'benditoai-gsap',
        'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js',
        array(),
        '3.13.0',
        true
    );

    wp_register_script(
        'benditoai-gsap-scrolltrigger',
        'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js',
        array('benditoai-gsap'),
        '3.13.0',
        true
    );

    $gsap_cards_script_path = BENDIDOAI_PLUGIN_PATH . 'assets/js/home/gsap-cards.js';
    $gsap_cards_script_version = file_exists($gsap_cards_script_path) ? (string) filemtime($gsap_cards_script_path) : '1.0';

    wp_register_script(
        'benditoai-gsap-cards',
        BENDIDOAI_PLUGIN_URL . 'assets/js/home/gsap-cards.js',
        array('benditoai-gsap', 'benditoai-gsap-scrolltrigger'),
        $gsap_cards_script_version,
        true
    );

}

add_action('wp_enqueue_scripts', 'benditoai_enqueue_assets');


/* HACER QUE EL SCRIPT SEA MODULE PARA QUE FUNCIONEN LOS IMPORT */

add_filter('script_loader_tag', function($tag, $handle) {

    if ($handle === 'benditoai-main') {
        $tag = str_replace('<script ', '<script type="module" ', $tag);
    }

    return $tag;

}, 10, 2);



/* SCRIPT DE MODELOS AI */

function benditoai_modelos_ai_scripts() {
    if (!benditoai_page_has_shortcode(array(
        'benditoai_modelos_ai',
        'benditoai_modelos_ai_historial',
    ))) {
        return;
    }

    $choices_css_path = BENDIDOAI_PLUGIN_PATH . 'assets/vendor/choices/choices.min.css';
    $choices_js_path = BENDIDOAI_PLUGIN_PATH . 'assets/vendor/choices/choices.min.js';
    $script_path = BENDIDOAI_PLUGIN_PATH . 'includes/modules/modelos-ai/modelos-ai-script.js';
    $choices_css_version = file_exists($choices_css_path) ? (string) filemtime($choices_css_path) : '11.1.0';
    $choices_js_version = file_exists($choices_js_path) ? (string) filemtime($choices_js_path) : '11.1.0';
    $script_version = file_exists($script_path) ? (string) filemtime($script_path) : '1.1';

    wp_enqueue_style(
        'benditoai-choices',
        BENDIDOAI_PLUGIN_URL . 'assets/vendor/choices/choices.min.css',
        array(),
        $choices_css_version
    );

    wp_enqueue_script(
        'benditoai-choices-js',
        BENDIDOAI_PLUGIN_URL . 'assets/vendor/choices/choices.min.js',
        array(),
        $choices_js_version,
        true
    );

    wp_enqueue_script(
        'benditoai-modelos-ai',
        BENDIDOAI_PLUGIN_URL . 'includes/modules/modelos-ai/modelos-ai-script.js',
        array('benditoai-main', 'benditoai-choices-js'),
        $script_version,
        true
    );

}

add_action('wp_enqueue_scripts', 'benditoai_modelos_ai_scripts');
