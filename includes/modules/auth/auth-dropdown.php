<?php

if (!function_exists('benditoai_render_nav_items')) {
    function benditoai_render_nav_items($nav_items) {
        foreach ($nav_items as $item) {
            $has_children = !empty($item['children']) && is_array($item['children']);

            if ($has_children) {
                $submenu_id = 'benditoai-submenu-' . wp_unique_id();
                ?>
                <div class="benditoai-dropdown-group">
                    <button
                        type="button"
                        class="benditoai-dropdown-item benditoai-dropdown-item--has-children"
                        aria-expanded="false"
                        aria-controls="<?php echo esc_attr($submenu_id); ?>"
                    >
                        <?php echo $item['icon']; ?>
                        <span><?php echo wp_kses_post($item['label']); ?></span>
                        <svg class="benditoai-dropdown-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div class="benditoai-dropdown-submenu" id="<?php echo esc_attr($submenu_id); ?>">
                        <?php foreach ($item['children'] as $child) : ?>
                            <a href="<?php echo esc_url($child['url']); ?>" class="benditoai-dropdown-subitem">
                                <span><?php echo esc_html($child['label']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
                continue;
            }
            ?>
            <a href="<?php echo esc_url($item['url']); ?>" class="benditoai-dropdown-item">
                <?php echo $item['icon']; ?>
                <span><?php echo wp_kses_post($item['label']); ?></span>
                <?php if (!empty($item['has_children'])) : ?>
                    <svg class="benditoai-dropdown-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                <?php endif; ?>
            </a>
            <?php
        }
    }
}

function benditoai_user_dropdown() {

    $login_url = wp_login_url(get_permalink());
    $nav_items = array(
        array(
            'label' => 'Home',
            'url' => home_url('/'),
            'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/></svg>',
        ),
        array(
            'label' => 'Herramientas IAS',
            'url' => home_url('/herramientas-ia/'),
            'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 1.7 5.1L19 10l-5.3 1.9L12 17l-1.7-5.1L5 10l5.3-1.9Z"/><path d="m5 15 .8 2.4L8 18l-2.2.6L5 21l-.8-2.4L2 18l2.2-.6Z"/></svg>',
            'has_children' => true,
            'children' => array(
                array(
                    'label' => 'Crear modelo',
                    'url' => home_url('/crea-modelo/'),
                ),
            ),
        ),
        array(
            'label' => 'Campa&ntilde;as',
            'url' => home_url('/campanas/'),
            'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11v4a2 2 0 0 0 2 2h2l4 4v-5l8 2V8l-8 2H5a2 2 0 0 0-2 2Z"/><path d="M19 8a4 4 0 0 0 0 10"/></svg>',
        ),
        array(
            'label' => 'Projects',
            'url' => home_url('/projects/'),
            'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></svg>',
        ),
        array(
            'label' => 'Contact',
            'url' => home_url('/contact/'),
            'icon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v12H4Z"/><path d="m4 7 8 6 8-6"/></svg>',
        ),
    );

    if (!is_user_logged_in()) {
        $guest_menu_id = 'benditoai-user-dropdown-' . wp_unique_id();

        ob_start();
        ?>
        <div class="benditoai-user-menu">
            <button
                type="button"
                class="benditoai-user-trigger"
                aria-label="Abrir menu de navegación"
                aria-expanded="false"
                aria-controls="<?php echo esc_attr($guest_menu_id); ?>"
            >
                <span class="benditoai-menu-line" aria-hidden="true"></span>
                <span class="benditoai-menu-line" aria-hidden="true"></span>
                <span class="benditoai-menu-line" aria-hidden="true"></span>
            </button>

            <div class="benditoai-user-dropdown" id="<?php echo esc_attr($guest_menu_id); ?>">
                <nav class="benditoai-dropdown-section benditoai-dropdown-nav" aria-labelledby="<?php echo esc_attr($guest_menu_id); ?>-nav">
                    <h3 class="benditoai-dropdown-heading" id="<?php echo esc_attr($guest_menu_id); ?>-nav">Navegación</h3>

                    <?php benditoai_render_nav_items($nav_items); ?>
                </nav>

                <div class="benditoai-dropdown-section benditoai-guest-login-section">
                    <a href="<?php echo esc_url($login_url); ?>" class="benditoai-dropdown-item benditoai-dropdown-login">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17 15 12l-5-5"/><path d="M15 12H3"/><path d="M14 5V4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2v-1"/></svg>
                        <span>Iniciar sesión</span>
                    </a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    $current_user = wp_get_current_user();
    $logout_url = wp_logout_url(home_url());
    $user_id = get_current_user_id();
    $tokens = function_exists('benditoai_get_user_tokens') ? benditoai_get_user_tokens($user_id) : 0;
    $is_admin = current_user_can('administrator');
    $enabled = get_user_meta($user_id, 'benditoai_admin_unlimited_tokens', true);
    $is_unlimited = $is_admin && ($enabled === 'yes');
    $tokens_display = $is_unlimited ? html_entity_decode('&infin;', ENT_QUOTES, 'UTF-8') : number_format((float) $tokens, 0, ',', '.');
    $plan_data = function_exists('benditoai_get_user_plan_data') ? benditoai_get_user_plan_data($user_id) : array('name' => 'Starter');
    $plan_label = isset($plan_data['name']) ? (string) $plan_data['name'] : 'Starter';
    $checked = ($enabled === 'yes') ? 'checked' : '';
    $display_name = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
    $menu_id = 'benditoai-user-dropdown-' . wp_unique_id();

    ob_start();
    ?>

    <div class="benditoai-user-menu">

        <button
            type="button"
            class="benditoai-user-trigger"
            aria-label="Abrir menu de usuario"
            aria-expanded="false"
            aria-controls="<?php echo esc_attr($menu_id); ?>"
        >
            <span class="benditoai-menu-line" aria-hidden="true"></span>
            <span class="benditoai-menu-line" aria-hidden="true"></span>
            <span class="benditoai-menu-line" aria-hidden="true"></span>
        </button>

        <div class="benditoai-user-dropdown" id="<?php echo esc_attr($menu_id); ?>">
            <section class="benditoai-dropdown-section benditoai-account-section" aria-labelledby="<?php echo esc_attr($menu_id); ?>-account">
                <h3 class="benditoai-dropdown-heading" id="<?php echo esc_attr($menu_id); ?>-account">Cuenta</h3>

                <div class="benditoai-account-card">
                    <span class="benditoai-account-name"><?php echo esc_html($display_name); ?></span>

                    <div class="benditoai-account-summary">
                        <div class="benditoai-account-summary-item">
                            <span class="benditoai-account-label">Tus tokens</span>
                            <span class="benditoai-user-tokens benditoai-account-tokens"><?php echo esc_html($tokens_display); ?></span>
                        </div>
                        <div class="benditoai-account-summary-item">
                            <span class="benditoai-account-label">Plan</span>
                            <span class="benditoai-account-plan"><?php echo esc_html($plan_label); ?></span>
                        </div>
                    </div>

                    <a href="<?php echo esc_url(home_url('/mis-modelos/')); ?>" class="benditoai-account-models">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m12 12 8-4.5"/><path d="M12 12v9"/><path d="M12 12 4 7.5"/></svg>
                        <span>Mis modelos</span>
                    </a>
                </div>
            </section>

            <?php if ($is_admin) : ?>
                <div class="benditoai-dropdown-item benditoai-dropdown-item--admin-toggle">
                    <span>Tokens ilimitados</span>
                    <label class="benditoai-admin-check-inline" title="Admin tokens ilimitados">
                        <input type="checkbox" class="benditoai-admin-unlimited-toggle" <?php echo $checked; ?> aria-label="Admin tokens ilimitados">
                        <span class="benditoai-admin-check-ui" aria-hidden="true"></span>
                    </label>
                </div>
            <?php endif; ?>

            <nav class="benditoai-dropdown-section benditoai-dropdown-nav" aria-labelledby="<?php echo esc_attr($menu_id); ?>-nav">
                <h3 class="benditoai-dropdown-heading" id="<?php echo esc_attr($menu_id); ?>-nav">Navegaci&oacute;n</h3>

                <?php benditoai_render_nav_items($nav_items); ?>
            </nav>

            <a href="<?php echo esc_url($logout_url); ?>" class="benditoai-dropdown-item benditoai-dropdown-logout">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17 15 12l-5-5"/><path d="M15 12H3"/><path d="M14 5V4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2v-1"/></svg>
                <span>Cerrar sesi&oacute;n</span>
            </a>
        </div>

    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('benditoai_user_menu', 'benditoai_user_dropdown');

function benditoai_desktop_user_shortcode() {

    if (!is_user_logged_in()) {

        $login_url = wp_login_url(get_permalink());

        return '<a href="' . esc_url($login_url) . '" class="benditoai-btn-login benditoai-desktop-user-login">Iniciar sesi&oacute;n</a>';
    }

    $current_user = wp_get_current_user();
    $display_name = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
    $logout_url = wp_logout_url(home_url());
    $user_id = get_current_user_id();
    $is_admin = current_user_can('administrator');
    $enabled = get_user_meta($user_id, 'benditoai_admin_unlimited_tokens', true);
    $checked = ($enabled === 'yes') ? 'checked' : '';
    $menu_id = 'benditoai-desktop-user-menu-' . wp_unique_id();

    ob_start();
    ?>

    <div class="benditoai-desktop-user">
        <button
            type="button"
            class="benditoai-desktop-user__button"
            aria-label="Abrir menu de cuenta"
            aria-expanded="false"
            aria-controls="<?php echo esc_attr($menu_id); ?>"
        >
            <svg class="benditoai-desktop-user__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2Z"/></svg>
            <span class="benditoai-desktop-user__label"></span>
            <span class="benditoai-desktop-user__name"><?php echo esc_html($display_name); ?></span>
        </button>

        <div class="benditoai-desktop-user__dropdown" id="<?php echo esc_attr($menu_id); ?>">
            <?php if ($is_admin) : ?>
                <div class="benditoai-desktop-user__item benditoai-desktop-user__item--admin-toggle">
                    <label class="benditoai-admin-check-inline" title="Admin tokens ilimitados">
                        <input type="checkbox" class="benditoai-admin-unlimited-toggle" <?php echo $checked; ?> aria-label="Admin tokens ilimitados">
                        <span class="benditoai-admin-check-ui" aria-hidden="true"></span>
                    </label>
                    <span>Tokens ilimitados</span>
                </div>
            <?php endif; ?>
            <a href="<?php echo esc_url(home_url('/mis-modelos/')); ?>" class="benditoai-desktop-user__item">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9Z"/><path d="m12 12 8-4.5"/><path d="M12 12v9"/><path d="M12 12 4 7.5"/></svg>
                <span>Mis modelos</span>
            </a>
            <a href="<?php echo esc_url($logout_url); ?>" class="benditoai-desktop-user__item benditoai-desktop-user__item--logout">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17 15 12l-5-5"/><path d="M15 12H3"/><path d="M14 5V4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2v-1"/></svg>
                <span>Cerrar sesi&oacute;n</span>
            </a>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('benditoai_desktop_user', 'benditoai_desktop_user_shortcode');
