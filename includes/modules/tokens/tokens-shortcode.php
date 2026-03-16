<?php

if (!defined('ABSPATH')) {
    exit;
}

function benditoai_tokens_shortcode() {

    if (!is_user_logged_in()) {
        return '';
    }

    $user_id = get_current_user_id();

    // Obtener tokens
    $tokens = benditoai_get_user_tokens($user_id);

    /**
     * 👑 Si el usuario es administrador
     * mostramos infinito en lugar del número
     */
    if (benditoai_user_has_unlimited_tokens($user_id)) {
        $tokens_display = '∞';
    } else {
        $tokens_display = $tokens;
    }

    ob_start();
    ?>

    <!-- CONTADOR DE TOKENS -->
    <div id="benditoai-token-counter" class="benditoai-token-counter-box">
        <span class="benditoai-token-label">Tokens:</span> 
        <span id="benditoai-token-value" class="benditoai-user-tokens"><?php echo esc_html($tokens_display); ?></span>
    </div>

    <?php
    /**
     * 🔧 TOGGLE SOLO PARA ADMIN
     * Permite activar o desactivar tokens ilimitados
     * para poder probar si los módulos descuentan correctamente
     */
    if (current_user_can('administrator')) {

        $enabled = get_user_meta($user_id,'benditoai_admin_unlimited_tokens',true);
        $checked = ($enabled === 'yes') ? 'checked' : '';

        ?>

        <div class="benditoai-admin-toggle-wrapper" style="margin-top:10px;">
            <label class="benditoai-admin-toggle-label">
                <input type="checkbox" id="benditoai-admin-unlimited-toggle" <?php echo $checked; ?>>
                Admin tokens ilimitados
            </label>
        </div>

        <script>

        document.addEventListener("DOMContentLoaded",function(){

            const toggle = document.getElementById("benditoai-admin-unlimited-toggle");

            if(!toggle) return;

            toggle.addEventListener("change",function(){

                fetch("<?php echo admin_url('admin-ajax.php'); ?>",{
                    method:"POST",
                    headers:{
                        "Content-Type":"application/x-www-form-urlencoded"
                    },
                    body:"action=benditoai_toggle_admin_tokens&enabled="+(this.checked ? "yes":"no")
                })
                .then(()=>{

                    // refrescar contador visual
                    if(typeof benditoaiActualizarTokensInstantaneo === "function"){
                        benditoaiActualizarTokensInstantaneo();
                    }

                });

            });

        });

        </script>

        <?php
    }

    return ob_get_clean();
}

add_shortcode('benditoai_tokens', 'benditoai_tokens_shortcode');