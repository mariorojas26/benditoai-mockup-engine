<?php

function benditoai_redirect_after_login($redirect_to, $request, $user) {

    if (!is_wp_error($user) && isset($user->roles)) {
        if (!in_array('administrator', $user->roles)) {
            return home_url('/dashboard');
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
    $logo_url = BENDIDOAI_PLUGIN_URL . 'assets/images/icoben.png';
    ?>
    <style>
        :root{
            --bendito-bg: #07030f;
            --bendito-surface: rgba(18, 10, 39, 0.86);
            --bendito-surface-border: rgba(124, 58, 255, 0.40);
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
                radial-gradient(900px 400px at 10% 8%, rgba(124,58,255,.28), transparent 70%),
                radial-gradient(700px 340px at 90% 88%, rgba(94,29,247,.22), transparent 70%),
                var(--bendito-bg);
            color: var(--bendito-text);
            font-family: "Segoe UI", "Inter", "Helvetica Neue", Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: clamp(12px, 2.6dvh, 24px) 14px;
            box-sizing: border-box;
            overflow-x: hidden;
            overflow-y: auto;
        }

        body.login #login {
            width: min(440px, 100%);
            max-width: 440px;
            margin: auto;
            padding: 0;
            display: grid;
            align-content: center;
            justify-items: stretch;
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

        #loginform,
        #registerform,
        #lostpasswordform {
            background: var(--bendito-surface);
            border: 1px solid var(--bendito-surface-border);
            border-radius: 16px;
            box-shadow: 0 18px 60px rgba(0,0,0,.45);
            padding: 26px 24px 22px;
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
            background: rgba(8, 6, 18, .7);
            border: 1px solid rgba(167, 139, 250, .35);
            color: var(--bendito-text);
            border-radius: 12px;
            min-height: 52px;
            box-shadow: none;
            font-size: 17px;
            padding: 0 14px;
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
            background: linear-gradient(135deg, var(--bendito-primary), var(--bendito-primary-2)) !important;
            border: 1px solid rgba(167, 139, 250, .36);
            border-radius: 12px;
            min-height: 46px;
            padding: 0 22px;
            color: #fff !important;
            font-weight: 600;
            text-shadow: none;
            box-shadow: 0 10px 24px rgba(94, 29, 247, .35);
            transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
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
            margin: 12px 2px 0;
            padding: 0;
            text-align: center;
            font-size: 15px;
            line-height: 1.45;
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
                justify-content: center;
                align-items: center;
                padding: 14px 12px 18px;
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

/**
 * UX limpia: ocultar switcher de idioma en wp-login.
 */
add_filter('login_display_language_dropdown', '__return_false');
