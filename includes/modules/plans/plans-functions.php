<?php
if (!defined('ABSPATH')) exit;

function benditoai_get_user_plan_data($user_id){

    // Leer fresco y normalizar contra la configuracion central.
    wp_cache_delete($user_id, 'user_meta');
    return benditoai_sync_user_plan_limits($user_id);
}
