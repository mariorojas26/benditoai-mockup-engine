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

    error_log('BendidoAI activado ðŸš€');
}

register_activation_hook(__FILE__, 'bendidoai_activate_plugin');


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

    'includes/services/gemini/gemini-api.php',
    'includes/services/gemini/gemini-api-text.php',
    'includes/services/gemini/gemini-api-multi-image.php',

    'includes/modules/auth/auth-redirect.php',

    'includes/modules/plans/plans-config.php',
    'includes/modules/plans/plans-functions.php',
    'includes/modules/plans/plans-init.php',
    'includes/modules/plans/ajax-update-plan.php',

    'includes/modules/tokens/tokens-manager.php',
    'includes/modules/tokens/tokens-shortcode.php',
    'includes/modules/tokens/ajax-get-tokens.php',
    'includes/modules/tokens/tokens-usage.php',

    'includes/modules/mockup/ajax-mockup.php',
    'includes/modules/mockup/shortcodes.php',
    'includes/modules/historial/shortcode-historial.php',
    'includes/modules/scroll-video/scroll-video-shortcode.php',

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

    'includes/modules/auth/auth-dropdown.php',
    'includes/modules/ux/maquinaEscribir.php',
    'includes/modules/ux/cardsSkills.php',
    'includes/modules/Home/antes-y-despues/before-after-shortcode.php',
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

    $scroll_video_script_path = BENDIDOAI_PLUGIN_PATH . 'assets/js/scroll-video.js';
    $scroll_video_script_version = file_exists($scroll_video_script_path) ? (string) filemtime($scroll_video_script_path) : '1.0';

    wp_register_script(
        'benditoai-scroll-video',
        BENDIDOAI_PLUGIN_URL . 'assets/js/scroll-video.js',
        array(),
        $scroll_video_script_version,
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
