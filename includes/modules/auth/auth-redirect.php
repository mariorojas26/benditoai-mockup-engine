<?php

function benditoai_redirect_after_login($redirect_to, $request, $user) {

    if (!is_wp_error($user) && isset($user->roles)) {
        if (!in_array('administrator', $user->roles)) {
            return home_url('/');
        }
    }

    return $redirect_to;
}
add_filter('login_redirect', 'benditoai_redirect_after_login', 10, 3);


function benditoai_block_admin_area() {

    if (
        is_admin() &&
        !current_user_can('administrator') &&
        !wp_doing_ajax()
    ) {
        wp_safe_redirect(site_url('/dashboard'));
        exit;
    }
}
add_action('admin_init', 'benditoai_block_admin_area');

/**
 * Personalizar pantalla wp-login.php con el estilo de BenditoAI.
 */
function benditoai_customize_login_screen() {
    $logo_url = BENDIDOAI_PLUGIN_URL . 'assets/images/icobenbla.png';
    ?>
    <style>
        :root{
            --bendito-bg: #06030f;
            --bendito-grid: rgba(124, 58, 255, 0.12);
            --bendito-surface: rgba(13, 9, 30, 0.78);
            --bendito-surface-border: rgba(124, 58, 255, 0.32);
            --bendito-primary: #7c3aff;
            --bendito-primary-2: #5e1df7;
            --bendito-text: #f8f7ff;
            --bendito-muted: #c4b5fd;
            --bendito-focus: #a78bfa;
        }

        html,
        body.login {
            min-height: 100vh;
            min-height: 100dvh;
        }

        body.login {
            margin: 0;
            background:
                radial-gradient(920px 540px at 50% 32%, rgba(124,58,255,.18), transparent 72%),
                radial-gradient(760px 420px at 12% 8%, rgba(124,58,255,.12), transparent 72%),
                radial-gradient(700px 420px at 88% 84%, rgba(94,29,247,.10), transparent 74%),
                linear-gradient(180deg, #070312 0%, #05020d 100%);
            color: var(--bendito-text);
            font-family: "Segoe UI", "Inter", "Helvetica Neue", Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            gap: 14px;
            padding: clamp(18px, 3dvh, 32px) 14px clamp(28px, 4dvh, 42px);
            box-sizing: border-box;
            overflow-x: hidden;
            overflow-y: scroll;
            position: relative;
        }

        body.login::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(to right, var(--bendito-grid) 1px, transparent 1px),
                linear-gradient(to bottom, var(--bendito-grid) 1px, transparent 1px);
            background-size: 64px 64px;
            background-position: 0 0;
            pointer-events: none;
            z-index: 0;
            animation: benditoai-grid-drift 28s linear infinite;
        }

        body.login::after {
            content: "";
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at 48% 38%, rgba(124,58,255,.12) 0%, transparent 66%),
                radial-gradient(ellipse at 70% 76%, rgba(94,29,247,.08) 0%, transparent 62%);
            pointer-events: none;
            z-index: 0;
            animation: benditoai-glow-drift 18s ease-in-out infinite alternate;
        }

        @keyframes benditoai-grid-drift {
            0% {
                transform: translate3d(0, 0, 0);
            }
            100% {
                transform: translate3d(-12px, -10px, 0);
            }
        }

        @keyframes benditoai-glow-drift {
            0% {
                transform: translate3d(0, 0, 0) scale(1);
                opacity: .90;
            }
            100% {
                transform: translate3d(-10px, 8px, 0) scale(1.03);
                opacity: 1;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            body.login::before,
            body.login::after {
                animation: none !important;
            }
        }

        body.login #login {
            width: min(440px, 100%);
            max-width: 440px;
            margin: 0 auto;
            padding: 0 0 8px;
            display: grid;
            align-content: start;
            justify-items: stretch;
            position: relative;
            z-index: 1;
        }

        body.login h1 {
            margin-bottom: 18px;
        }

        body.login h1 a {
            background-image: url("<?php echo esc_url($logo_url); ?>");
            background-size: contain;
            background-position: center;
            width: 150px;
            height: 88px;
            margin: 0 auto 8px;
        }

        .benditoai-login-context {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 0 14px;
            margin: 0 auto 14px;
            border-radius: 999px;
            border: 1px solid rgba(124, 58, 255, 0.48);
            background: rgba(94, 29, 247, 0.14);
            color: var(--bendito-muted);
            line-height: 1.1;
            letter-spacing: 0.2px;
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 600;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.02);
        }

        #loginform,
        #registerform,
        #lostpasswordform {
            background: var(--bendito-surface);
            border: 1px solid var(--bendito-surface-border);
            border-radius: 18px;
            box-shadow:
                0 24px 60px rgba(0,0,0,.5),
                inset 0 1px 0 rgba(255,255,255,.03);
            backdrop-filter: blur(4px);
            padding: 24px 22px 20px;
        }

        #loginform label,
        #registerform label,
        #lostpasswordform label {
            color: var(--bendito-muted);
            font-size: 15px;
        }

        .login form .input,
        .login form input[type="text"],
        .login form input[type="password"],
        .login form input[type="email"] {
            background: rgba(8, 6, 18, .84);
            border: 1px solid rgba(167, 139, 250, .28);
            color: var(--bendito-text);
            border-radius: 12px;
            min-height: 52px;
            box-shadow: none;
            font-size: 17px;
            padding: 0 14px;
        }

        .benditoai-register-password-wrap {
            margin-top: 8px;
        }

        .login form .input:focus,
        .login form input[type="text"]:focus,
        .login form input[type="password"]:focus,
        .login form input[type="email"]:focus {
            border-color: var(--bendito-focus);
            box-shadow: 0 0 0 2px rgba(167, 139, 250, .2);
            outline: none;
        }

        .login .button.wp-hide-pw {
            color: var(--bendito-muted);
            border-color: rgba(167, 139, 250, .35);
            border-radius: 10px;
            min-height: 44px;
            background: rgba(8, 6, 18, .7);
        }

        .login .button.wp-hide-pw:hover {
            color: var(--bendito-text);
            border-color: var(--bendito-focus);
        }

        .login .forgetmenot label {
            color: var(--bendito-muted);
        }

        .login .forgetmenot input[type="checkbox"] {
            border-color: rgba(167, 139, 250, .45);
            background: rgba(8, 6, 18, .7);
            border-radius: 6px;
        }

        .wp-core-ui .button-primary {
            background: #5E1DF7 !important;
            border: 1px solid rgba(167, 139, 250, .36);
            border-radius: 12px !important;
            min-height: 52px;
             padding: 5px 10px !important;
            color: #fff !important;
            font-weight: 600;
            text-shadow: none;
            box-shadow: 0 12px 28px rgba(94, 29, 247, .42);
            transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
            margin-top: 16px !important;
            border-color: transparent !important;
            width: 100% !important;
        }

        .wp-core-ui .button-primary:hover,
        .wp-core-ui .button-primary:focus {
            filter: brightness(1.06);
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(94, 29, 247, .46);
        }

        .login .submit {
            margin: 14px 0 0;
            padding: 0;
        }

        .login #nav,
        .login #backtoblog {
            margin: 14px 2px 0;
            padding: 0;
            text-align: center;
            font-size: 15px;
            line-height: 1.45;
            position: relative;
            z-index: 1;
        }

        .login #nav a,
        .login #backtoblog a {
            color: var(--bendito-muted);
            text-decoration: none;
        }

        .login #nav a:hover,
        .login #backtoblog a:hover {
            color: #ffffff;
        }

        .login .message,
        .login .notice,
        .login .success,
        .login #login_error {
            border-radius: 12px;
            border-left: 3px solid rgba(167, 139, 250, .8);
            border-top: 1px solid rgba(124, 58, 255, .3);
            border-right: 1px solid rgba(124, 58, 255, .3);
            border-bottom: 1px solid rgba(124, 58, 255, .3);
            background: rgba(8, 6, 18, .78) !important;
            color: #f4efff !important;
            box-shadow: none;
            font-size: 14px;
            line-height: 1.45;
            margin: 0 0 14px;
            padding: 12px 14px;
        }

        .login .message a,
        .login .notice a,
        .login .success a,
        .login #login_error a {
            color: #ffffff !important;
            text-decoration: underline;
        }

        .login .message:empty,
        .login .notice:empty,
        .login .success:empty,
        .login #login_error:empty {
            display: none !important;
        }

        /* En pantalla de registro, ocultar mensaje genérico superior (ruido visual). */
        body.login-action-register .message.register {
            display: none !important;
        }

        .login .language-switcher {
            width: min(440px, 100%);
            max-width: 440px;
            margin: 2px auto 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            position: relative;
            z-index: 1;
        }

        .login .language-switcher form {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .login .language-switcher .button,
        .login .language-switcher select {
            border-radius: 10px;
        }

        .login .language-switcher select {
            width: 100%;
            min-height: 46px;
            background: rgba(8, 6, 18, .65);
            border-color: rgba(167, 139, 250, .3);
            color: #fff;
        }

        .login .language-switcher .button {
            min-height: 44px;
            padding: 0 18px;
        }

        .login .language-switcher {
            display: none !important;
        }

        /* Menos ruido en acción register: dejamos solo el link para volver a acceso. */
        body.login-action-register #nav a:last-child {
            display: none;
        }

        @media (max-width: 640px) {
            body.login {
                justify-content: flex-start;
                align-items: center;
                padding: 16px 12px 24px;
                gap: 10px;
            }

            body.login #login {
                width: 100%;
                max-width: 100%;
            }

            #loginform,
            #registerform,
            #lostpasswordform {
                border-radius: 14px;
                padding: 20px 16px 18px;
            }

            .login h1 a {
                width: min(170px, 50vw);
                height: 88px;
                margin-bottom: 4px;
            }

            .benditoai-login-context {
                min-height: 32px;
                padding: 0 12px;
                margin-bottom: 10px;
                font-size: 10px;
            }

            .login form .input,
            .login form input[type="text"],
            .login form input[type="password"],
            .login form input[type="email"] {
                min-height: 48px;
                font-size: 16px;
            }

            .login .forgetmenot {
                float: none;
                margin: 2px 0 8px;
                width: 100%;
            }

            .login .submit {
                float: none;
                width: 100%;
                margin-top: 8px;
            }

            .wp-core-ui .button-primary {
                width: 100%;
                min-height: 48px;
            }

            .login .language-switcher {
                width: 100%;
                max-width: 100%;
                margin-top: 0;
            }

            .login .language-switcher select,
            .login .language-switcher .button {
                width: 100%;
            }

            .login .language-switcher .button {
                max-width: 180px;
            }
        }
        .login #login h1 a {
            outline: none !important;
            border: none !important;
            box-shadow: none !important;
            -webkit-tap-highlight-color: transparent !important;
            -webkit-appearance: none !important;
            appearance: none !important;
        }

        .login #login h1 a:focus,
        .login #login h1 a:focus-visible,
        .login #login h1 a:active,
        .login #login h1 a:focus-within {
            outline: none !important;
            border: none !important;
            box-shadow: none !important;
            -webkit-outline: none !important;
        }

        .login a:focus,
        .login a:focus-visible {
            outline: none !important;
        }

        body.login h1 a {
            outline: 0 none transparent !important;
            border: 0 none !important;
            box-shadow: none !important;
        }

    </style>
    <?php
}
add_action('login_enqueue_scripts', 'benditoai_customize_login_screen');

function benditoai_login_logo_url() {
    return home_url('/');
}
add_filter('login_headerurl', 'benditoai_login_logo_url');

function benditoai_login_logo_title() {
    return get_bloginfo('name');
}
add_filter('login_headertext', 'benditoai_login_logo_title');

function benditoai_register_password_fields() {
    ?>
    <p class="benditoai-register-password-wrap">
        <label for="benditoai_register_password"><?php esc_html_e('contraseña', 'bendidoai-mockup-engine'); ?></label>
        <input
            type="password"
            name="benditoai_register_password"
            id="benditoai_register_password"
            class="input"
            autocomplete="new-password"
            required
            minlength="8"
        >
    </p>
    <p class="benditoai-register-password-wrap">
        <label for="benditoai_register_password_confirm"><?php esc_html_e('Confirmar contraseña', 'bendidoai-mockup-engine'); ?></label>
        <input
            type="password"
            name="benditoai_register_password_confirm"
            id="benditoai_register_password_confirm"
            class="input"
            autocomplete="new-password"
            required
            minlength="8"
        >
    </p>
    <?php
}
add_action('register_form', 'benditoai_register_password_fields');

function benditoai_validate_register_password($errors, $sanitized_user_login, $user_email) {
    $password = isset($_POST['benditoai_register_password']) ? (string) wp_unslash($_POST['benditoai_register_password']) : '';
    $password_confirm = isset($_POST['benditoai_register_password_confirm']) ? (string) wp_unslash($_POST['benditoai_register_password_confirm']) : '';

    if ($password === '' || $password_confirm === '') {
        $errors->add('benditoai_register_password_required', '<strong>Error:</strong> Debes definir una contraseña y confirmarla.');
        return $errors;
    }

    if ($password !== $password_confirm) {
        $errors->add('benditoai_register_password_mismatch', '<strong>Error:</strong> Las contraseñas no coinciden.');
        return $errors;
    }

    if (strlen($password) < 8) {
        $errors->add('benditoai_register_password_length', '<strong>Error:</strong> La contraseña debe tener al menos 8 caracteres.');
    }

    return $errors;
}
add_filter('registration_errors', 'benditoai_validate_register_password', 10, 3);

function benditoai_set_registered_user_password($user_id) {
    if (empty($_POST['benditoai_register_password'])) {
        return;
    }

    $password = (string) wp_unslash($_POST['benditoai_register_password']);

    if ($password !== '') {
        wp_set_password($password, $user_id);
    }
}
add_action('user_register', 'benditoai_set_registered_user_password');

function benditoai_registration_redirect_to_login($redirect_to) {
    return add_query_arg('benditoai_registered', '1', wp_login_url());
}
add_filter('registration_redirect', 'benditoai_registration_redirect_to_login');

function benditoai_login_context_label($message) {
    $action = isset($_REQUEST['action']) ? sanitize_key(wp_unslash($_REQUEST['action'])) : 'login';

    $labels = array(
        'login' => 'Iniciar sesion',
        'register' => 'Registrarse',
        'lostpassword' => 'Recuperar acceso',
        'rp' => 'Nueva contraseña',
        'resetpass' => 'Nueva contraseña',
    );

    $label = isset($labels[$action]) ? $labels[$action] : 'Iniciar sesion';

    if (isset($_GET['benditoai_registered']) && $_GET['benditoai_registered'] === '1') {
        $message = '<p class="message">Registro completado. Inicia sesion con tu nueva contraseña.</p>' . $message;
    }

    return '<div class="benditoai-login-context">' . esc_html($label) . '</div>' . $message;
}
add_filter('login_message', 'benditoai_login_context_label');

/**
 * UX limpia: ocultar switcher de idioma en wp-login.
 */
add_filter('login_display_language_dropdown', '__return_false');
