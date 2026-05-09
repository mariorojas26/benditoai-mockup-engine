<?php

function benditoai_user_dropdown() {

    if (!is_user_logged_in()) {

        $login_url = wp_login_url(get_permalink());

        return '<a href="'.$login_url.'" class="benditoai-btn-login">Iniciar sesión</a>';
    }

    $current_user = wp_get_current_user();
    $logout_url = wp_logout_url(home_url());
    $user_id = get_current_user_id();
    $tokens = function_exists('benditoai_get_user_tokens') ? benditoai_get_user_tokens($user_id) : 0;
    $is_admin = current_user_can('administrator');
    $enabled = get_user_meta($user_id, 'benditoai_admin_unlimited_tokens', true);
    $is_unlimited = $is_admin && ($enabled === 'yes');
    $tokens_display = $is_unlimited ? html_entity_decode('&infin;', ENT_QUOTES, 'UTF-8') : $tokens;
    $checked = ($enabled === 'yes') ? 'checked' : '';

    ob_start();
    ?>

    <div class="benditoai-user-menu">

        <div class="benditoai-user-trigger">
            <span class="benditoai-user-name"><?php echo esc_html($current_user->display_name); ?></span>
            <span aria-hidden="true">-</span>
            <span class="benditoai-token-label">Tokens:</span>
            <span class="benditoai-user-tokens"><?php echo esc_html($tokens_display); ?></span>
        </div>

        <div class="benditoai-user-dropdown">
            <?php if ($is_admin) : ?>
                <div class="benditoai-dropdown-item benditoai-dropdown-item--admin-toggle">
                    <span>Tokens ilimitados</span>
                    <label class="benditoai-admin-check-inline" title="Admin tokens ilimitados">
                        <input type="checkbox" class="benditoai-admin-unlimited-toggle" <?php echo $checked; ?> aria-label="Admin tokens ilimitados">
                        <span class="benditoai-admin-check-ui" aria-hidden="true"></span>
                    </label>
                </div>
            <?php endif; ?>
            <a href="/mis-modelos" class="benditoai-dropdown-item">Mis modelos</a>
            <a href="<?php echo esc_url($logout_url); ?>" class="benditoai-dropdown-item logout">
                Cerrar sesión
            </a>
        </div>

    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('benditoai_user_menu', 'benditoai_user_dropdown');
