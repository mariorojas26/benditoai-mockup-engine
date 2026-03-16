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