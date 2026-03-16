<?php
// includes/shortcode-historial.php
if (!defined('ABSPATH')) exit;

function benditoai_historial_shortcode() {
    if (!is_user_logged_in()) {
        return '<p class="benditoai-historial-message">Debes iniciar sesión para ver tu historial.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'benditoai_historial';

    $historial = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        )
    );

    // Evitamos crash si no hay resultados
    if (!is_array($historial) || count($historial) === 0) {
        return '<p class="benditoai-historial-message">No has generado ningún mockup todavía.</p>';
    }

    ob_start();
    ?>
    <div class="benditoai-historial-wrapper">
        <h2 class="benditoai-historial-title">Mis creaciones</h2>
        <div class="benditoai-historial-grid">
            <?php foreach ($historial as $item): ?>
<div class="benditoai-historial-item">
    <?php if (!empty($item->image_url)) : ?>
        <img src="<?php echo esc_url($item->image_url); ?>" 
             alt="Mockup <?php echo esc_attr($item->producto); ?>" 
             class="benditoai-historial-img">

        <!-- Botón de descarga -->
        <a href="<?php echo esc_url($item->image_url); ?>" 
           download="mockup-<?php echo esc_attr($item->producto); ?>-<?php echo esc_attr($item->id); ?>.png"
           class="benditoai-btn benditoai-btn--download">
            ⬇️ Descargar
        </a>
    <?php endif; ?>
    
    <div class="benditoai-historial-info">
        <p><strong>Producto:</strong> <?php echo esc_html($item->producto); ?></p>
        <p><strong>Color:</strong> <?php echo esc_html($item->color); ?></p>
        <?php if (!empty($item->estilo_camiseta)) : ?>
            <p><strong>Estilo:</strong> <?php echo esc_html($item->estilo_camiseta); ?></p>
        <?php endif; ?>
        <p><strong>Entorno:</strong> <?php echo esc_html($item->entorno); ?></p>
        <p><strong>Fecha:</strong> <?php echo esc_html($item->created_at); ?></p>

        <!-- <?php if (!empty($item->prompt)) : ?>
            <details class="benditoai-historial-prompt">
                <summary>Ver Prompt completo</summary>
                <pre><?php echo esc_html($item->prompt); ?></pre>
            </details>
        <?php endif; ?> -->
    </div>
</div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('benditoai_historial', 'benditoai_historial_shortcode');