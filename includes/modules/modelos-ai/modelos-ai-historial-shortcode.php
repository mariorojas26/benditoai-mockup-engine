<?php
function benditoai_modelos_ai_historial_shortcode() {

    if (!is_user_logged_in()) {
        return '<p class="benditoai-message">Debes iniciar sesión para ver tus modelos AI.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'benditoai_modelos_ai';

    // 🔥 ORDEN CORRECTO
    $historial = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE user_id = %d 
             ORDER BY created_at DESC, id DESC",
            $user_id
        )
    );

    if (!is_array($historial) || count($historial) === 0) {
        return '<p class="benditoai-message">No has generado ningún modelo todavía.</p>';
    }

    ob_start();
    ?>
    <div class="benditoai-wrapper-historia-modelos">

        <h2 class="benditoai-historial-title">Mis Modelos AI</h2>

        <div class="benditoai-historial-grid" id="benditoai-historial-mockups">

            <?php foreach ($historial as $item): ?>

            <div class="benditoai-historial-item">

                <p class="benditoai-historial-name">
                    <?php echo esc_html($item->nombre_modelo); ?>
                </p>

                <?php if(!empty($item->image_url)): ?>

                    <div class="benditoai-img-wrap">
                        <img 
                            src="<?php echo esc_url($item->image_url); ?>" 
                            alt="<?php echo esc_attr($item->nombre_modelo); ?>" 
                            class="benditoai-historial-img"
                        />
                    </div>

                    <a href="<?php echo esc_url($item->image_url); ?>" download class="benditoai-btn benditoai-btn--download">
                        ⬇️ Descargar
                    </a>

                <?php endif; ?>

                <div class="benditoai-historial-info">

                    <p><strong>Género:</strong> <?php echo esc_html($item->genero); ?></p>
                    <p><strong>Edad:</strong> <?php echo esc_html($item->edad); ?></p>
                    <p><strong>Estilo:</strong> <?php echo esc_html($item->estilo); ?></p>

                    <!-- 🔥 FECHA CORREGIDA -->
                    <p>
                        <strong>Creado:</strong> 
                        <?php 
                        if(!empty($item->created_at) && $item->created_at !== '0000-00-00 00:00:00'){
                            echo esc_html(
                                date('d/m/Y H:i', strtotime($item->created_at))
                            );
                        } else {
                            echo 'Fecha no disponible';
                        }
                        ?>
                    </p>

                    <button 
                        class="benditoai-delete-modelo-btn" 
                        data-id="<?php echo esc_attr($item->id); ?>">
                        🗑 Eliminar
                    </button>

                    <button 
                        class="benditoai-edit-modelo-btn" 
                        data-id="<?php echo esc_attr($item->id); ?>"
                        data-image="<?php echo esc_url($item->image_url); ?>">
                        ✏️ Editar
                    </button>

                    <div class="benditoai-edit-box" style="display:none;">
                        <textarea 
                            class="benditoai-edit-text"
                            placeholder="Ej: cámbiale el pantalón por uno jean oscuro..."
                        ></textarea>

                        <button class="benditoai-save-edit-btn">
                            Guardar cambios
                        </button>
                    </div>

                </div>

            </div>

            <?php endforeach; ?>

        </div>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('benditoai_modelos_ai_historial', 'benditoai_modelos_ai_historial_shortcode');