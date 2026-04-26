<?php
/**
 * Plugin Name: BenditoAI Mockup Engine
 * Plugin URI: https://bendidoai.com
 * Description: Motor de generación de mockups con IA usando Gemini.
 * Version: 1.0.0
 * Author: BendidoTrazo
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit;
}

define('BENDIDOAI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BENDIDOAI_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once BENDIDOAI_PLUGIN_PATH . 'includes/install.php';

/* ACTIVACIÓN TABLAS BASES DE DATOS*/

function bendidoai_activate_plugin() {
    // Crear tabla historial de mockups
    benditoai_create_historial_table();

    // Crear tabla de campañas AI
    benditoai_create_campanas_ai_table();

    // Crear tabla de modelos AI
    benditoai_create_modelos_ai_table();

    error_log('BendidoAI activado 🚀');
}

register_activation_hook(__FILE__, 'bendidoai_activate_plugin');


/* DESACTIVACIÓN */

function bendidoai_deactivate_plugin() {
    error_log('BendidoAI desactivado.');
}

register_deactivation_hook(__FILE__, 'bendidoai_deactivate_plugin');


/* CARGAR MÓDULOS */

require_once BENDIDOAI_PLUGIN_PATH . 'includes/admin-menu.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/admin/admin-users-plans.php';

require_once BENDIDOAI_PLUGIN_PATH . 'includes/ajax-mockup.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/auth-redirect.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/gemini-api.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/gemini-api-text.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/prompts.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/shortcode-historial.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/shortcodes.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/variables.php';

require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/remove-bg/ajax-remove-bg.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/remove-bg/shortcode-remove-bg.php';

require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/tokens/tokens-shortcode.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/tokens/tokens-manager.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/tokens/ajax-get-tokens.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/tokens/tokens-usage.php';

require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/ux/maquinaEscribir.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/ux/CardsSkills.php';

/* PLANES (SISTEMA DE SUSCRIPCIÓN) */

require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/plans/plans-config.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/plans/plans-functions.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/plans/plans-init.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/plans/ajax-update-plan.php';

require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/enhance-image/ajax-enhance-image.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/enhance-image/shortcode-enhance-image.php';

require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/tendencias/trending-shortcode.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/tendencias/trending-ajax.php';

require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/modelos-ai/modelos-ai-shortcode.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/modelos-ai/modelos-ai-historial-shortcode.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/modelos-ai/modelos-ai-ajax.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/modelos-ai/modelos-ai-delete.php';
require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/modelos-ai/modelos-ai-edit.php';

require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/auth/auth-dropdown.php';

require_once BENDIDOAI_PLUGIN_PATH . 'includes/modules/Home/antes-y-despues/before-after-shortcode.php';




/* ESTILOS Y JS */

function benditoai_enqueue_assets() {


    // 🔥 Font Awesome version gratuita solo aceptan 5.0 https://fontawesome.com/v5/search
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
        array(),
        '6.7.2'
    );

    wp_enqueue_style(
        'benditoai-styles',
        BENDIDOAI_PLUGIN_URL . 'assets/css/styles.css',
        array(),
        '1.0'
    );

    /* JS PRINCIPAL (usa imports internos) */

    wp_enqueue_script(
        'benditoai-main',
        BENDIDOAI_PLUGIN_URL . 'assets/js/benditoai-main.js',
        array(),
        '1.0',
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

    wp_enqueue_script(
        'benditoai-modelos-ai',
        BENDIDOAI_PLUGIN_URL . 'includes/modules/modelos-ai/modelos-ai-script.js',
        array('benditoai-main'),
        '1.0',
        true
    );

}

add_action('wp_enqueue_scripts', 'benditoai_modelos_ai_scripts');