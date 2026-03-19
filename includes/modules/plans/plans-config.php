<?php
if (!defined('ABSPATH')) exit;

/**
 * CONFIGURACIÓN DE PLANES
 */

function benditoai_get_plans(){

    return [

        'starter' => [
            'name' => 'Starter',
            'tokens' => 200,
            'max_modelos' => 3
        ],

        'pro' => [
            'name' => 'Pro',
            'tokens' => 1000,
            'max_modelos' => 10
        ],

        'elite' => [
            'name' => 'Elite',
            'tokens' => 5000,
            'max_modelos' => 50
        ]

    ];

}