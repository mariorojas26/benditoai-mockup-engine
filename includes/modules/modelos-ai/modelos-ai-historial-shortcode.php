<?php
function benditoai_modelos_ai_historial_shortcode() {
    if (!is_user_logged_in()) {
        return '<p class="benditoai-message">Debes iniciar sesión para ver tus modelos AI.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'benditoai_modelos_ai';
    $historial = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC", $user_id));

    if (!is_array($historial) || count($historial) === 0) {
        return '<p class="benditoai-message">No has generado ningún modelo todavía.</p>';
    }

    ob_start();
    ?>
    <div class="benditoai-wrapper-historia-modelos"> <!-- clase padre para especificidad -->
        <h2 class="benditoai-historial-title">Mis Modelos AI</h2>
        <div class="benditoai-historial-grid">
            <?php foreach ($historial as $item): ?>
            <div class="benditoai-historial-item">
                   <p class="benditoai-historial-name"> <?php echo esc_html($item->nombre_modelo); ?></p>
                <?php if(!empty($item->image_url)): ?>
                    <img src="<?php echo esc_url($item->image_url); ?>" alt="<?php echo esc_attr($item->nombre_modelo); ?>" class="benditoai-historial-img"/>
                    <a href="<?php echo esc_url($item->image_url); ?>" download class="benditoai-btn benditoai-btn--download">⬇️ Descargar</a>
                <?php endif; ?>
                <div class="benditoai-historial-info">
                    <p><strong>Género:</strong> <?php echo esc_html($item->genero); ?></p>
                    <p><strong>Edad:</strong> <?php echo esc_html($item->edad); ?></p>
                    <p><strong>Estilo:</strong> <?php echo esc_html($item->estilo); ?></p>
                    <p><strong>Fecha:</strong> <?php echo esc_html($item->created_at); ?></p>
<button 
    class="benditoai-delete-modelo-btn" 
    data-id="<?php echo esc_attr($item->id); ?>">
    🗑 Eliminar
</button>
                    
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('benditoai_modelos_ai_historial', 'benditoai_modelos_ai_historial_shortcode');