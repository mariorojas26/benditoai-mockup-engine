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

    $sin_modelos = (!is_array($historial) || count($historial) === 0);

    ob_start();
    ?>
    <div class="benditoai-wrapper-historia-modelos">

        <h2 class="benditoai-historial-title">Mis Modelos AI</h2>

        <?php if($sin_modelos): ?>
            <p class="benditoai-message" id="benditoai-empty-message">
                No has generado ningún modelo todavía.
            </p>
        <?php endif; ?>

        <div class="benditoai-historial-grid" id="benditoai-historial-mockups">

            <?php if(!$sin_modelos): ?>
                <?php foreach ($historial as $item): ?>

                <div class="benditoai-historial-item" data-id="<?php echo esc_attr($item->id); ?>">

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

        <div class="benditoai-action-buttons">
            <!-- boton descargar -->
                                        <a 
                href="<?php echo esc_url($item->image_url); ?>" 
                download
                class="benditoai-btn benditoai-btn--download"
            >
                <img 
                    src="<?php echo plugin_dir_url(__FILE__) . '../../../assets/images/icon-download.png'; ?>" 
                    alt="Descargar"
                    class="benditoai-btn-icon"
                >
            </a>

                        <!-- boton editar -->


            <button 
                class="benditoai-edit-modelo-btn" 
                data-id="<?php echo esc_attr($item->id); ?>"
                data-image="<?php echo esc_url($item->image_url); ?>">
                <img 
                    src="<?php echo plugin_dir_url(__FILE__) . '../../../assets/images/icon-edit.png'; ?>" 
                    alt="Editar"
                >

            </button>
            
            <!-- boton eliminar -->

            <button 
                class="benditoai-delete-modelo-btn benditoai-action-btn" 
                data-id="<?php echo esc_attr($item->id); ?>"
            >
                <img 
                    src="<?php echo plugin_dir_url(__FILE__) . '../../../assets/images/icon-delete.png'; ?>" 
                    alt="Eliminar"
                >
            </button>


        </div>


                        </div>



                    <?php endif; ?>

<button class="benditoai-toggle-info">
    Ver detalles
</button>

<div class="benditoai-historial-info" style="display:none;">

    <p><strong>Género:</strong> <?php echo esc_html($item->genero); ?></p>
    <p><strong>Edad:</strong> <?php echo esc_html($item->edad); ?></p>
    <p><strong>Estilo:</strong> <?php echo esc_html($item->estilo); ?></p>

    <p>
        <strong>Creado:</strong> 
        <?php 
        echo (!empty($item->created_at) && $item->created_at !== '0000-00-00 00:00:00')
            ? esc_html(date('d/m/Y H:i', strtotime($item->created_at)))
            : 'Fecha no disponible';
        ?>
    </p>

</div>

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

                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('benditoai_modelos_ai_historial', 'benditoai_modelos_ai_historial_shortcode');