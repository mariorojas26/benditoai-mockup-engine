<?php
// includes/install.php
if (!defined('ABSPATH')) exit;

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

    error_log("✅ benditoai_create_historial_table ejecutada: $table_name");
}
