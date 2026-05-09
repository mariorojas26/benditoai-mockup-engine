<?php
/*
  includes/core/install.php
 
  AquÃ­ creamos las tablas de la base de datos para guardar el historial de generaciones
  y los modelos AI.
 
  CADA VEZ QUE SE ACTIVA EL PLUGIN, SE EJECUTAN ESTAS FUNCIONES PARA ASEGURAR QUE LAS TABLAS EXISTAN AUTOMATICAMENTE
  .
 âš ï¸ SOLO DEBE HABER 1 if (!defined('ABSPATH')) exit; Y DEBAJO ANINDAR LAS TABLAS, NO DEBEN HABER 2 IF POR QUE ROMPE ELEMENTOR


 */

if (!defined('ABSPATH')) exit;

/**
 * Crear tabla historial de mockups
 */
function benditoai_create_historial_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'benditoai_historial';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        producto VARCHAR(50) NOT NULL,
        color VARCHAR(50) DEFAULT '',
        estilo_camiseta VARCHAR(50) DEFAULT '',
        modelo VARCHAR(50) DEFAULT '',
        entorno VARCHAR(50) DEFAULT '',
        prompt TEXT,
        image_url TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    error_log("âœ… benditoai_create_historial_table ejecutada: $table_name");
}

/**
 * Crear tabla campaÃ±as AI
 */
function benditoai_create_campanas_ai_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'benditoai_campanas_ai';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,

        producto_url TEXT,
        modelo_id BIGINT UNSIGNED DEFAULT NULL,

        estilo VARCHAR(50) DEFAULT '',
        colores VARCHAR(100) DEFAULT '',
        ambiente VARCHAR(100) DEFAULT '',
        mood VARCHAR(100) DEFAULT '',

        prompt TEXT,
        image_url TEXT,

        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    error_log("âœ… benditoai_create_campanas_ai_table ejecutada: $table_name");
}

/**
 * Crear tabla modelos AI
 */
function benditoai_create_modelos_ai_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'benditoai_modelos_ai';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        nombre_modelo VARCHAR(100) NOT NULL,
        genero VARCHAR(50) DEFAULT '',
        edad VARCHAR(50) DEFAULT '',
        cuerpo VARCHAR(50) DEFAULT '',
        etnia VARCHAR(50) DEFAULT '',
        estilo VARCHAR(50) DEFAULT '',
        perfil_publico TINYINT(1) NOT NULL DEFAULT 0,
        descripcion_modelo TEXT,
        prenda_superior TEXT,
        prenda_inferior TEXT,
        zapatos TEXT,
        accesorios TEXT,
        modo_creacion VARCHAR(30) NOT NULL DEFAULT 'rasgos',
        nacionalidad VARCHAR(80) DEFAULT '',
        rasgos_caracteristicas TEXT,
        campo_adicional TEXT,
        prompt TEXT,
        image_url TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    error_log("âœ… benditoai_create_modelos_ai_table ejecutada: $table_name");
}

