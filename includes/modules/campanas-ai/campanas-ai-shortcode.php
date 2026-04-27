<?php
if (!defined('ABSPATH')) exit;

function benditoai_campanas_ai_shortcode() {

    if (!is_user_logged_in()) {
        return '<p>Debes iniciar sesión</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table = $wpdb->prefix . 'benditoai_modelos_ai';

    $modelos = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT id, nombre_modelo, image_url 
             FROM $table 
             WHERE user_id = %d 
             ORDER BY id DESC",
            $user_id
        )
    );

    ob_start();
?>

<div class="benditoai-wizard">

<form id="benditoai-form-campana-ai">

<!-- PASO 1 -->
<div class="benditoai-step active" data-step="1">
    <h3 class="benditoai-step-text">¿Qué quieres vender?</h3>

    <textarea 
        name="producto" 
        maxlength="15"
        placeholder="Ej: camiseta"
        required
    ></textarea>

    <button type="button" class="benditoai-next">Siguiente</button>
</div>

<!-- PASO 2 -->
<div class="benditoai-step" data-step="2">
    <h3>Sube tu producto</h3>

    <input type="file" id="benditoai-product-image" accept="image/*" required>

    <button type="button" class="benditoai-prev">Atrás</button>
    <button type="button" class="benditoai-next">Siguiente</button>
</div>

<!-- PASO 3 -->
<div class="benditoai-step" data-step="3">
    <h3>¿Usar modelo?</h3>

    <label><input type="radio" name="use_model" value="1"> Sí</label>
    <label><input type="radio" name="use_model" value="0" checked> No</label>

    <div id="benditoai-modelos-container" style="display:none; margin-top:15px;">

        <?php if(empty($modelos)): ?>

            <p class="benditoai-message">
                No tienes modelos aún.
            </p>

            <a href="/crea-modelo" class="benditoai-btn">
                Crear mi primer modelo
            </a>

        <?php else: ?>

            <div class="benditoai-historial-grid benditoai-modelos-grid-mini">

                <?php foreach ($modelos as $m): ?>

                    <div 
                        class="benditoai-historial-item benditoai-modelo-card"
                        data-url="<?php echo esc_url($m->image_url); ?>"
                        data-nombre="<?php echo esc_attr($m->nombre_modelo); ?>"
                    >
                        <div class="benditoai-img-wrap">
                            <img 
                                src="<?php echo esc_url($m->image_url); ?>" 
                                class="benditoai-historial-img"
                            >
                        </div>
                        <p class="benditoai-historial-name">
                            <?php echo esc_html($m->nombre_modelo); ?>
                        </p>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

    <p id="benditoai-modelo-seleccionado" style="display:none; margin-top:10px; color:#A78BFA;">
        Has seleccionado: <strong></strong>
    </p>

    <input type="hidden" name="model_url" id="model_url">

    <button type="button" class="benditoai-prev">Atrás</button>
    <button type="button" class="benditoai-next">Siguiente</button>
</div>

<!-- PASO 4 -->
<div class="benditoai-step" data-step="4">
    <h3>Estilo</h3>

    <select name="estilo">
        <option>Streetwear</option>
        <option>Lujo</option>
        <option>Minimalista</option>
        <option>Urbano</option>
    </select>

    <button type="button" class="benditoai-prev">Atrás</button>
    <button type="button" class="benditoai-next">Siguiente</button>
</div>

<!-- PASO 5 -->
<div class="benditoai-step" data-step="5">
    <h3>Tono</h3>

    <select name="tono">
        <option>Elegante</option>
        <option>Agresivo</option>
        <option>Juvenil</option>
    </select>

    <button type="button" class="benditoai-prev">Atrás</button>
    <button type="submit">Generar campaña</button>
</div>

<!-- 🔥 PASO 6 — RESULTADO (nuevo paso, reemplaza el div flotante) -->
<div class="benditoai-step" data-step="6">

    <h2 class="benditoai-resultado-title">Tu campaña</h2>

    <div class="benditoai-resultado-preview">

        <div id="benditoai-loading" style="display:none;">
            Generando campaña...
        </div>

        <img id="benditoai-result-img" src="" style="display:none;" />

        <p id="benditoai-error" style="display:none; color:red;">
            No se pudo generar la imagen
        </p>

    </div>

    <div class="benditoai-acciones">

        <button type="button" id="benditoai-recrear">
            🔁 Recrear campaña
        </button>

        <button type="button" id="benditoai-reset">
            ⚙️ Configurar otra campaña
        </button>

    </div>

</div>

</form>

</div>

<?php
    return ob_get_clean();
}

add_shortcode('benditoai_campanas_ai', 'benditoai_campanas_ai_shortcode');