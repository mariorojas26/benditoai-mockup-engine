<?php

if (!defined('ABSPATH')) {
    exit;
}

function benditoai_print_browser_theme_meta() {
    $theme_color = '#090413';
    $accent_color = '#5E1DF7';
    ?>
    <meta name="theme-color" content="<?php echo esc_attr($theme_color); ?>">
    <meta name="theme-color" media="(prefers-color-scheme: dark)" content="<?php echo esc_attr($theme_color); ?>">
    <meta name="theme-color" media="(prefers-color-scheme: light)" content="<?php echo esc_attr($theme_color); ?>">
    <meta name="msapplication-navbutton-color" content="<?php echo esc_attr($theme_color); ?>">
    <meta name="msapplication-TileColor" content="<?php echo esc_attr($accent_color); ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <?php
}

add_action('wp_head', 'benditoai_print_browser_theme_meta', 1);
