<?php

function benditoai_user_dropdown() {

    if (!is_user_logged_in()) {

        $login_url = wp_login_url(get_permalink());

        return '<a href="'.$login_url.'" class="benditoai-btn-login">Iniciar sesión</a>';
    }

    $current_user = wp_get_current_user();
    $logout_url = wp_logout_url(home_url());

    ob_start();
    ?>

    <div class="benditoai-user-menu">

        <div class="benditoai-user-trigger">
            <?php echo esc_html($current_user->display_name); ?>
        </div>

        <div class="benditoai-user-dropdown">
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