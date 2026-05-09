<?php

if (!defined('ABSPATH')) {
    exit;
}

function benditoai_tokens_shortcode() {

    if (!is_user_logged_in()) {
        return '';
    }

    $user_id = get_current_user_id();
    $is_admin = current_user_can('administrator');

    $tokens = benditoai_get_user_tokens($user_id);
    $enabled = get_user_meta($user_id, 'benditoai_admin_unlimited_tokens', true);
    $is_unlimited = $is_admin && ($enabled === 'yes');
    $tokens_display = $is_unlimited ? html_entity_decode('&infin;', ENT_QUOTES, 'UTF-8') : $tokens;

    $checked = '';
    if ($is_admin) {
        $checked = ($enabled === 'yes') ? 'checked' : '';
    }

    ob_start();
    ?>

    <!-- CONTADOR DE TOKENS -->
    <div id="benditoai-token-counter" class="benditoai-token-counter-box">
        <?php if ($is_admin) : ?>
            <label class="benditoai-admin-check-inline" title="Admin tokens ilimitados">
                <input type="checkbox" class="benditoai-admin-unlimited-toggle" <?php echo $checked; ?> aria-label="Admin tokens ilimitados">
                <span class="benditoai-admin-check-ui" aria-hidden="true"></span>
            </label>
        <?php endif; ?>

        <span class="benditoai-token-label">Tokens:</span>
        <span id="benditoai-token-value" class="benditoai-user-tokens"><?php echo esc_html($tokens_display); ?></span>
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('benditoai_tokens', 'benditoai_tokens_shortcode');

