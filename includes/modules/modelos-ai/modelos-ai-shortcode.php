<?php

if (!defined('ABSPATH')) {
    exit;
}

function benditoai_modelos_ai_shortcode() {

    if (!is_user_logged_in()) {
        return '<div class="benditoai-auth-message">Debes iniciar sesion para usar esta herramienta.</div>';
    }

    $campaign_url = apply_filters('benditoai_modelos_ai_campaign_url', home_url('/crea-campana/'));

    ob_start();
?>

<div class="benditoai-modelos-wizard" data-campaign-url="<?php echo esc_url($campaign_url); ?>">
    <div class="baiw-shell">

        <section class="baiw-config-stage" id="benditoai-config-stage">
            <header class="baiw-header">
                <div>
                                       <h2 class="baiw-title-main">Crea tu modelo AI en 3 pasos</h2>
                    <p>Flujo claro, rapido y listo para reutilizar en campanas.</p>
                </div>
                <div class="baiw-credits">
                    <span>Consumo estimado</span>
                    <strong>1 credito</strong>
                </div>
            </header>

            <ol class="baiw-stepper" aria-label="Progreso del wizard">
                <li class="is-active" data-step-indicator="1">
                    <span class="baiw-step-badge">1</span>
                </li>
                <li data-step-indicator="2">
                    <span class="baiw-step-badge">2</span>
                </li>
                <li data-step-indicator="3">
                    <span class="baiw-step-badge">3</span>
                </li>
            </ol>

            <div class="baiw-progress-track" aria-hidden="true">
                <div class="baiw-progress-fill" id="benditoai-wizard-progress"></div>
            </div>

            <form id="benditoai-form-modelo-ai" class="baiw-form" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="benditoai_modelos_nonce" value="<?php echo esc_attr(wp_create_nonce('benditoai_modelos_ai_nonce')); ?>">
                <input type="hidden" name="perfil_publico" id="benditoai_perfil_publico" value="0">

                <section class="baiw-step is-active" data-step="1" aria-hidden="false">
                    <div class="baiw-card">
                        <h3>Informacion principal</h3>
                       
                        <div class="baiw-field">
                            <label for="benditoai_nombre_modelo">Nombre del modelo</label>
                            <input id="benditoai_nombre_modelo" type="text" name="nombre_modelo" placeholder="Natalia Pastor" maxlength="100" required>
                        </div>

                        <p class="baiw-field-label">Tipo de creacion</p>
                        <div class="baiw-mode-grid" role="radiogroup" aria-label="Tipo de creacion">
                            <label class="baiw-mode-option">
                                <input type="radio" name="modo_creacion" value="referencia" checked>
                                <span>
                                    <span class="baiw-tip baiw-tip--corner" tabindex="0" aria-label="Ayuda modo referencia">
                                        ?
                                        <span class="baiw-tip-bubble">Usa una foto base y la IA la toma como guia principal.</span>
                                    </span>
                                    <small>Sube una foto y la IA la usa como base.</small>
                                </span>
                            </label>

                            <label class="baiw-mode-option">
                                <input type="radio" name="modo_creacion" value="rasgos">
                                <span>
                                    <span class="baiw-tip baiw-tip--corner" tabindex="0" aria-label="Ayuda modo rasgos">
                                        ?
                                        <span class="baiw-tip-bubble">Describe rasgos y estilo para generar el avatar desde cero.</span>
                                    </span>
                                    <small>Construye el avatar desde cero.</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </section>

                <section class="baiw-step" data-step="2" aria-hidden="true" hidden>
                    <div class="baiw-card" data-mode-panel="referencia">

                        <div class="baiw-field">
                            <label class="baiw-label-tip" for="benditoai_imagen_referencia">
                                <span>Imagen de referencia</span>
                                <span class="baiw-tip" tabindex="0" aria-label="Ayuda imagen de referencia">
                                    ?
                                    <span class="baiw-tip-bubble">Sube una imagen nitida.</span>
                                </span>
                            </label>
                            <label class="baiw-file-smart" for="benditoai_imagen_referencia" id="benditoai-file-smart">
                                <input id="benditoai_imagen_referencia" type="file" name="imagen_referencia" accept="image/png,image/jpeg,image/webp">
                                <span class="baiw-file-smart-text" id="benditoai-file-text">Subir imagen</span>
                                <div class="baiw-file-smart-meta" id="benditoai-file-meta" hidden>
                                    <img class="baiw-file-preview" id="benditoai-file-preview" src="" alt="Preview referencia" hidden>
                                    <p class="baiw-file-name" id="benditoai-file-name"></p>
                                </div>
                            </label>
                        </div>

                        <div class="baiw-field baiw-grid-2">
                            <div>
                                <label for="benditoai_estilo_ref">Estilo visual</label>
                                <select id="benditoai_estilo_ref" name="estilo">
                                    <option value="editorial">Editorial</option>
                                    <option value="streetwear">Streetwear</option>
                                    <option value="minimalista">Minimalista</option>
                                    <option value="fitness">Fitness</option>
                                    <option value="luxury">Lujo</option>
                                </select>
                            </div>
                            <div>
                                <label for="benditoai_nacionalidad_ref">Nacionalidad</label>
                                <input id="benditoai_nacionalidad_ref" type="text" name="nacionalidad" placeholder="Ej: Colombiana" maxlength="80">
                            </div>
                        </div>
                    </div>

                    <div class="baiw-card" data-mode-panel="rasgos" hidden>
                        <h3>Rasgos base del avatar</h3>
                        <p class="baiw-hint">Completa la base visual antes de pasar a los detalles.</p>

                        <div class="baiw-rasgos-layout">
                            <div class="baiw-slider-row">
                                <div class="baiw-field baiw-slider-group">
                                    <label for="benditoai_edad_range">Edad <strong id="benditoai_edad_value">25</strong></label>
                                    <input id="benditoai_edad_range" class="baiw-range" type="range" min="18" max="60" step="1" value="25" name="edad_valor">
                                    <input type="hidden" id="benditoai_edad" name="edad" value="adult">
                                </div>

                                <div class="baiw-field baiw-slider-group">
                                    <label for="benditoai_altura">Altura <strong id="benditoai_altura_value">175 cm</strong></label>
                                    <input id="benditoai_altura" class="baiw-range" type="range" min="145" max="210" step="1" value="175" name="altura_cm">
                                </div>

                                <div class="baiw-field baiw-slider-group">
                                    <label for="benditoai_peso">Peso <strong id="benditoai_peso_value">55 kg</strong></label>
                                    <input id="benditoai_peso" class="baiw-range" type="range" min="40" max="140" step="1" value="55" name="peso_kg">
                                </div>
                            </div>

                            <div class="baiw-fields-grid">
                                <div class="baiw-field">
                                    <label for="benditoai_genero">Genero</label>
                                    <select id="benditoai_genero" name="genero">
                                        <option value="male">Hombre</option>
                                        <option value="female">Mujer</option>
                                        <option value="non-binary">No binario</option>
                                    </select>
                                </div>

                                <div class="baiw-field">
                                    <label for="benditoai_cuerpo">Constitucion</label>
                                    <select id="benditoai_cuerpo" name="cuerpo" class="baiw-enhanced-select">
                                        <option value="athletic">Atletico</option>
                                        <option value="slim">Delgado</option>
                                        <option value="average">Promedio</option>
                                        <option value="plus size">Plus size</option>
                                    </select>
                                </div>

                                <div class="baiw-field">
                                    <label for="benditoai_etnia">Etnia</label>
                                    <select id="benditoai_etnia" name="etnia">
                                        <option value="latin">Latino</option>
                                        <option value="european">Europeo</option>
                                        <option value="african">Afro</option>
                                        <option value="asian">Asiatico</option>
                                        <option value="middle eastern">Medio oriente</option>
                                    </select>
                                </div>

                                <div class="baiw-field">
                                    <label for="benditoai_color_ojos">Color de ojos</label>
                                    <select id="benditoai_color_ojos" name="color_ojos" class="baiw-enhanced-select">
                                        <option value="brown">Cafe</option>
                                        <option value="black">Negro</option>
                                        <option value="hazel">Avellana</option>
                                        <option value="green">Verde</option>
                                        <option value="blue">Azul</option>
                                        <option value="gray">Gris</option>
                                    </select>
                                </div>

                                <div class="baiw-field">
                                    <label for="benditoai_peinado">Peinado</label>
                                    <select id="benditoai_peinado" name="peinado" class="baiw-enhanced-select">
                                        <option value="short">Corto</option>
                                        <option value="long">Largo</option>
                                        <option value="curly">Rizado</option>
                                        <option value="afro">Afro</option>
                                        <option value="wavy">Ondulado</option>
                                        <option value="buzz cut">Rapado</option>
                                    </select>
                                </div>

                                <div class="baiw-field">
                                    <label for="benditoai_color_pelo">Color de pelo</label>
                                    <select id="benditoai_color_pelo" name="color_pelo" class="baiw-enhanced-select">
                                        <option value="black">Negro</option>
                                        <option value="brown">Castano</option>
                                        <option value="blonde">Rubio</option>
                                        <option value="red">Pelirrojo</option>
                                        <option value="gray">Gris</option>
                                    </select>
                                </div>

                                <div class="baiw-field">
                                    <label for="benditoai_estilo_rasgos">Estilo general</label>
                                    <select id="benditoai_estilo_rasgos" name="estilo">
                                        <option value="streetwear">Streetwear</option>
                                        <option value="fashion">Fashion</option>
                                        <option value="fitness">Fitness</option>
                                        <option value="minimalist">Minimalista</option>
                                        <option value="luxury">Lujo</option>
                                    </select>
                                </div>

                                <div class="baiw-field baiw-field--full">
                                    <label for="benditoai_nacionalidad">Nacionalidad</label>
                                    <input id="benditoai_nacionalidad" type="text" name="nacionalidad" placeholder="Ej: Colombiana" maxlength="80">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="baiw-step" data-step="3" aria-hidden="true" hidden>
                    <div class="baiw-card" data-mode-panel="referencia">
                        <h3>Ultimos ajustes</h3>
                        <p class="baiw-hint">Agrega contexto final para la generacion.</p>

                        <div class="baiw-field">
                            <label for="benditoai_campo_adicional_ref">Detalle adicional</label>
                            <textarea id="benditoai_campo_adicional_ref" name="campo_adicional" placeholder="Ej: look limpio, fondo claro, pose frontal."></textarea>
                        </div>
                    </div>

                    <div class="baiw-card" data-mode-panel="rasgos" hidden>
                        <h3>Detalles a medida</h3>
               

                        <div class="baiw-step3-layout">
                            <div class="baiw-field">
                                <label for="benditoai_rasgos">Rasgos y caracteristicas</label>
                                <textarea id="benditoai_rasgos" name="rasgos_caracteristicas" placeholder="Ej: rostro ovalado, mirada segura, sonrisa suave."></textarea>
                            </div>

                            <div class="baiw-field">
                                <label for="benditoai_campo_adicional">Campo adicional</label>
                                <textarea id="benditoai_campo_adicional" name="campo_adicional" placeholder="Indicaciones extra para la IA."></textarea>
                            </div>

                            <div class="baiw-field baiw-step3-flags">
                                <p class="baiw-field-label">Detalles rapidos</p>
                                <div class="baiw-step3-flags-list">
                                    <label class="baiw-step3-flag">
                                        <input type="checkbox" name="detalle_barba" value="1">
                                        <span>Barba</span>
                                    </label>
                                    <label class="baiw-step3-flag">
                                        <input type="checkbox" name="detalle_hoyuelos" value="1">
                                        <span>Hoyuelos</span>
                                    </label>
                                    <label class="baiw-step3-flag">
                                        <input type="checkbox" name="detalle_bronceado" value="1">
                                        <span>Bronceado</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="baiw-consent-row">
                        <label class="baiw-consent-check" for="benditoai_perfil_publico_toggle">
                            <input type="checkbox" id="benditoai_perfil_publico_toggle" role="switch" aria-checked="false">
                            <span class="baiw-consent-box" aria-hidden="true"></span>
                            <span class="baiw-consent-copy">
                                <small>Publico</small>
                            </span>
                            <span class="baiw-tip baiw-tip--consent" tabindex="0" aria-label="Ayuda visibilidad">
                                <span aria-hidden="true">?</span>
                                <span class="baiw-tip-bubble">Permites usar tus imagenes generadas como ejemplos reales en el sitio.</span>
                            </span>
                        </label>
                    </div>

                </section>

                <div class="baiw-error-inline" id="benditoai-modelo-inline-error" role="alert" aria-live="polite"></div>

                <div class="baiw-nav">
                    <button type="button" class="baiw-btn baiw-btn--ghost" id="benditoai-modelo-prev">Anterior</button>
                    <button type="button" class="baiw-btn baiw-btn--primary" id="benditoai-modelo-next">Siguiente</button>
                    <button type="submit" class="baiw-btn baiw-btn--accent" id="benditoai-modelo-submit">Generar modelo</button>
                </div>
            </form>
        </section>

        <section class="baiw-result-stage" id="benditoai-result-stage" hidden aria-live="polite">
            <div class="baiw-result-stage-inner">
                <p class="baiw-kicker">Resultado final</p>
                <h3 class="baiw-result-title">Tu modelo AI</h3>

                <p class="benditoai-loading" id="benditoai-modelo-loading">Generando modelo con IA...</p>
                <div class="baiw-result-skeleton benditoai-img-wrap benditoai-image-loading" id="benditoai-modelo-skeleton" hidden></div>

                <div class="baiw-final-error" id="benditoai-modelo-error-panel" hidden>
                    <p class="benditoai-error-message" id="benditoai-modelo-error"></p>
                    <button type="button" class="baiw-btn baiw-btn--ghost" id="benditoai-modelo-retry">Intentar de nuevo</button>
                </div>

                <div class="benditoai-image-wrapper baiw-result-image" id="benditoai-modelo-image-wrapper" style="display:none;">
                    <img class="benditoai-generated-image" id="benditoai-modelo-image" src="" alt="Modelo generado">
                </div>

                <div class="baiw-action-row baiw-action-row--main" id="benditoai-modelo-success-actions" style="display:none;">
                    <a class="baiw-btn baiw-btn--primary" id="benditoai-modelo-campaign" href="<?php echo esc_url($campaign_url); ?>">Empezar campana</a>
                    <button type="button" class="baiw-btn baiw-btn--ghost" id="benditoai-modelo-create-another">Crear otro modelo</button>
                </div>

                <div class="baiw-action-row baiw-action-row--secondary" id="benditoai-modelo-secondary-actions" style="display:none;">
                    <a class="baiw-btn baiw-btn--ghost" id="benditoai-modelo-download" href="" download="modelo-benditoai.png">Descargar</a>
                    <button type="button" class="baiw-btn baiw-btn--ghost" id="benditoai-modelo-share">Compartir</button>
                    <button type="button" class="baiw-btn baiw-btn--ghost" id="benditoai-modelo-edit">Editar</button>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
.benditoai-modelos-wizard {
    --baiw-bg: #070312;
    --baiw-surface: rgba(20, 12, 42, 0.84);
    --baiw-surface-soft: rgba(32, 20, 68, 0.62);
    --baiw-primary: #7c3aff;
    --baiw-primary-strong: #8f56ff;
    --baiw-accent: #a071ff;
    --baiw-ink: #f8f7ff;
    --baiw-muted: #b6adcf;
    --baiw-border: rgba(137, 111, 214, 0.33);
    --baiw-success: #22c55e;
    --baiw-danger: #fb7185;

    max-width: 1160px;
    margin: 48px auto;
    padding: 0 16px;
    color: var(--baiw-ink);
    line-height: 1.45;
}

.benditoai-modelos-wizard [hidden] {
    display: none !important;
}

.benditoai-modelos-wizard .baiw-shell {
    position: relative;
    overflow: visible !important;
    display: grid;
    gap: 10px;
    border-radius: 22px;
    border: 1px solid var(--baiw-border);
    background:
        radial-gradient(circle at 16% 2%, rgba(124, 58, 255, 0.3), transparent 40%),
        radial-gradient(circle at 92% 22%, rgba(124, 58, 255, 0.24), transparent 45%),
        var(--baiw-bg);
    padding: clamp(14px, 1.8vw, 22px);
    box-shadow: 0 28px 64px rgba(2, 0, 12, 0.52);
    transition: padding-bottom 0.25s ease;
}

.benditoai-modelos-wizard.is-dropdown-open .baiw-shell {
    padding-bottom: clamp(170px, 22vw, 260px) !important;
}

.benditoai-modelos-wizard .baiw-shell::before {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.045) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.045) 1px, transparent 1px);
    background-size: 52px 52px;
    opacity: 0.35;
}

.benditoai-modelos-wizard .baiw-shell > * {
    position: relative;
    z-index: 1;
}

.benditoai-modelos-wizard .baiw-config-stage {
    display: grid;
    gap: 10px;
}

.benditoai-modelos-wizard .baiw-header {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 2px;
    max-height: 196px;
    opacity: 1;
    transform: translateY(0);
    overflow: hidden;
    transition: max-height 0.34s ease, opacity 0.22s ease, transform 0.22s ease, margin 0.3s ease;
}

.benditoai-modelos-wizard.is-step-compact .baiw-header {
    max-height: 0;
    opacity: 0;
    margin: 0;
    transform: translateY(-8px);
    pointer-events: none;
}

.benditoai-modelos-wizard .baiw-kicker {
    margin: 0 0 8px;
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    border: 1px solid rgba(128, 90, 213, 0.5);
    background: rgba(74, 30, 150, 0.35);
    padding: 8px 14px;
    letter-spacing: 0.02em;
    font-size: 0.82rem;
    font-weight: 600;
    color: #d8c8ff;
}

.benditoai-modelos-wizard .baiw-header h2 {
    margin: 6px 0;
    font-size: clamp(1.45rem, 1.05rem + 1.4vw, 2.15rem);
    line-height: 1.2;
    letter-spacing: -0.02em;
}

.benditoai-modelos-wizard .baiw-title-main {
    color: #ffffff !important;
}

.benditoai-modelos-wizard .baiw-header p {
    margin: 0;
    color: var(--baiw-muted);
    font-size: 0.95rem;
}

.benditoai-modelos-wizard .baiw-credits {
    min-width: 160px;
    padding: 10px 14px;
    border-radius: 14px;
    border: 1px solid rgba(133, 102, 211, 0.4);
    background: rgba(14, 8, 31, 0.62);
}

.benditoai-modelos-wizard .baiw-credits span {
    display: block;
    color: var(--baiw-muted);
    font-size: 0.78rem;
}

.benditoai-modelos-wizard .baiw-credits strong {
    display: block;
    margin-top: 2px;
    font-size: 1rem;
    color: #e8dcff;
}

.benditoai-modelos-wizard .baiw-stepper {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
}

.benditoai-modelos-wizard .baiw-stepper li {
    display: grid;
    place-items: center;
    padding: 8px 10px;
    border-radius: 12px;
    border: 1px solid rgba(124, 92, 201, 0.33);
    background: rgba(20, 12, 42, 0.68);
    color: #b8afd2;
    transition: 0.2s ease;
}

.benditoai-modelos-wizard .baiw-stepper li.is-active {
    border-color: rgba(124, 58, 255, 0.66);
    color: #f5f1ff;
    background: rgba(124, 58, 255, 0.34);
    box-shadow: 0 0 0 1px rgba(124, 58, 255, 0.24);
}

.benditoai-modelos-wizard .baiw-stepper li.is-complete {
    border-color: rgba(120, 95, 193, 0.55);
    background: rgba(72, 35, 150, 0.38);
    color: #ddd1ff;
}

.benditoai-modelos-wizard .baiw-step-badge {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    font-weight: 700;
    color: #dcd2f8;
    background: rgba(255, 255, 255, 0.08);
}

.benditoai-modelos-wizard .baiw-stepper li.is-active .baiw-step-badge,
.benditoai-modelos-wizard .baiw-stepper li.is-complete .baiw-step-badge {
    color: #ffffff;
    background: linear-gradient(140deg, #7c3aff, #8f56ff);
}

.benditoai-modelos-wizard .baiw-progress-track {
    width: 100%;
    height: 4px;
    border-radius: 999px;
    background: rgba(120, 106, 165, 0.28);
    overflow: hidden;
}

.benditoai-modelos-wizard .baiw-progress-fill {
    width: 0%;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, #7c3aff, #9d6dff);
    transition: width 0.2s ease;
}

.benditoai-modelos-wizard .baiw-form {
    display: grid;
    gap: 10px;
}

.benditoai-modelos-wizard .baiw-step {
    display: none;
}

.benditoai-modelos-wizard .baiw-step.is-active {
    display: grid;
    gap: 10px;
}

.benditoai-modelos-wizard .baiw-card {
    border: 1px solid rgba(124, 92, 201, 0.33);
    border-radius: 16px;
    background: var(--baiw-surface);
    backdrop-filter: blur(8px);
    padding: 14px;
}

.benditoai-modelos-wizard .baiw-card h3 {
    margin: 0;
    font-size: 1.04rem;
    letter-spacing: 0.01em;
    font-weight: 600;
}

.benditoai-modelos-wizard .baiw-hint {
    margin: 6px 0 0;
    color: var(--baiw-muted);
    font-size: 0.85rem;
}

.benditoai-modelos-wizard .baiw-field,
.benditoai-modelos-wizard .baiw-toggle-row {
    margin-top: 9px;
}

.benditoai-modelos-wizard .baiw-field label,
.benditoai-modelos-wizard .baiw-field-label {
    display: block;
    margin: 0 0 6px;
    font-size: 0.84rem;
    color: #f2ecff;
    font-weight: 600;
}

.benditoai-modelos-wizard .baiw-label-tip {
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
}

.benditoai-modelos-wizard .baiw-tip {
    position: relative;
    width: 19px;
    height: 19px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid rgba(94, 29, 247, 0.35);
    background: rgba(94, 29, 247, 0.12);
    color: #A78BFA;
    font-size: 0.68rem;
    font-weight: 700;
    line-height: 1;
    cursor: help;
    user-select: none;
}

.benditoai-modelos-wizard .baiw-tip-bubble {
    position: absolute;
    right: 0;
    bottom: calc(100% + 8px);
    min-width: 220px;
    max-width: 280px;
    padding: 8px 10px;
    border-radius: 10px;
    border: 1px solid rgba(124, 92, 201, 0.45);
    background: #140c2f;
    color: #e7defe;
    font-size: 0.76rem;
    font-weight: 500;
    line-height: 1.35;
    box-shadow: 0 10px 24px rgba(4, 2, 13, 0.45);
    opacity: 0;
    pointer-events: none;
    transform: translateY(4px);
    transition: opacity 0.18s ease, transform 0.18s ease;
    z-index: 95;
}

.benditoai-modelos-wizard .baiw-tip:hover .baiw-tip-bubble,
.benditoai-modelos-wizard .baiw-tip:focus .baiw-tip-bubble,
.benditoai-modelos-wizard .baiw-tip:focus-visible .baiw-tip-bubble {
    opacity: 1;
    transform: translateY(0);
}

.benditoai-modelos-wizard input,
.benditoai-modelos-wizard select,
.benditoai-modelos-wizard textarea {
    width: 100%;
    border-radius: 12px;
    border: 1px solid rgba(120, 96, 187, 0.42);
    background: rgba(9, 5, 24, 0.72);
    color: #f7f3ff !important;
    padding: 11px 12px;
    font-size: 0.92rem;
    appearance: none;
}

.benditoai-modelos-wizard input::placeholder,
.benditoai-modelos-wizard textarea::placeholder {
    color: rgba(214, 205, 240, 0.88);
}

.benditoai-modelos-wizard textarea {
    min-height: 112px;
    resize: vertical;
    line-height: 1.45;
}

.benditoai-modelos-wizard input:focus,
.benditoai-modelos-wizard select:focus,
.benditoai-modelos-wizard textarea:focus {
    outline: none;
    background: rgba(9, 5, 24, 0.82);
    border-color: rgba(124, 58, 255, 0.88);
    box-shadow: 0 0 0 3px rgba(124, 58, 255, 0.24);
}

.benditoai-modelos-wizard select option {
    background: #140d2e;
    color: #f7f3ff;
}

.benditoai-modelos-wizard .baiw-file-smart {
    position: relative;
    width: 100%;
    display: grid;
    place-items: center;
    margin: 0;
    border-radius: 12px;
    border: 1.5px solid rgba(94, 29, 247, 0.30) !important;
    background: rgba(94, 29, 247, 0.12) !important;
    min-height: 54px;
    padding: 8px 12px;
    font-size: 0.84rem;
    font-weight: 600;
    color: #A78BFA !important;
    cursor: pointer;
    overflow: hidden;
    transition: border-color 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
}

.benditoai-modelos-wizard .baiw-file-smart input[type="file"] {
    position: absolute;
    opacity: 0;
    inset: 0;
    cursor: pointer;
}

.benditoai-modelos-wizard .baiw-file-smart-text {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #A78BFA !important;
    font-size: 0.98rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    text-align: center;
}

.benditoai-modelos-wizard .baiw-file-smart-meta {
    width: 100%;
    display: grid;
    grid-template-columns: 44px minmax(0, 1fr);
    align-items: center;
    gap: 10px;
}

.benditoai-modelos-wizard .baiw-file-name {
    margin: 0;
    color: #ffffff;
    font-size: 0.82rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.benditoai-modelos-wizard .baiw-file-preview {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    object-fit: cover;
    display: block;
    border: 1px solid rgba(124, 92, 201, 0.45);
    background: rgba(20, 12, 42, 0.92);
}

.benditoai-modelos-wizard .baiw-file-preview[hidden] {
    display: none;
}

.benditoai-modelos-wizard .baiw-file-smart.is-has-file {
    place-items: center;
    background: rgba(94, 29, 247, 0.16) !important;
}

.benditoai-modelos-wizard .baiw-file-smart:hover {
    border-color: rgba(94, 29, 247, 0.55) !important;
    background: rgba(94, 29, 247, 0.20) !important;
    box-shadow: none !important;
}

.benditoai-modelos-wizard .baiw-mode-grid {
    margin-top: 8px;
    display: grid;
    gap: 10px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.benditoai-modelos-wizard .baiw-mode-option {
    position: relative;
}

.benditoai-modelos-wizard .baiw-mode-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.benditoai-modelos-wizard .baiw-mode-option > span {
    display: block;
    border: 1px solid rgba(124, 92, 201, 0.36);
    border-radius: 12px;
    background: var(--baiw-surface-soft);
    padding: 12px;
    cursor: pointer;
    transition: 0.2s ease;
    position: relative;
}

.benditoai-modelos-wizard .baiw-mode-option > span > strong {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
}

.benditoai-modelos-wizard .baiw-mode-option > span > small {
    display: block;
    margin-top: 4px;
    font-size: 0.77rem;
    color: #ac9fcb;
    line-height: 1.4;
}

.benditoai-modelos-wizard .baiw-mode-option input:checked + span {
    border-color: rgba(124, 58, 255, 0.74);
    background: rgba(99, 56, 207, 0.34);
    box-shadow: 0 12px 24px rgba(9, 4, 25, 0.35);
}

/* Paso 1 con mejor respiracion tras quitar descripcion */
.benditoai-modelos-wizard .baiw-step[data-step="1"] .baiw-card {
    padding: 18px;
}

.benditoai-modelos-wizard .baiw-step[data-step="1"] .baiw-field-label {
    margin-top: 14px;
}

.benditoai-modelos-wizard .baiw-step[data-step="1"] .baiw-mode-grid {
    gap: 12px;
}

.benditoai-modelos-wizard .baiw-step[data-step="1"] .baiw-mode-option > span {
    min-height: 74px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 14px 18px;
}

.benditoai-modelos-wizard .baiw-tip--corner {
    position: absolute;
    top: 10px;
    right: 10px;
}

/* Paso 2 referencia: mas respiracion entre contenedores */
.benditoai-modelos-wizard .baiw-step[data-step="2"] .baiw-card[data-mode-panel="referencia"] {
    padding: 20px 20px 18px;
}

.benditoai-modelos-wizard .baiw-step[data-step="2"] .baiw-card[data-mode-panel="referencia"] .baiw-field {
    margin-top: 14px;
}

.benditoai-modelos-wizard .baiw-step[data-step="2"] .baiw-card[data-mode-panel="referencia"] .baiw-field + .baiw-field {
    margin-top: 18px;
}

.benditoai-modelos-wizard .baiw-step[data-step="2"] .baiw-card[data-mode-panel="referencia"] .baiw-grid-2 {
    margin-top: 14px;
    gap: 14px;
}

/* Paso 3 rasgos: distribucion 3 columnas (rasgos / campo / flags) */
.benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-card[data-mode-panel="rasgos"] .baiw-step3-layout {
    margin-top: 12px;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    align-items: start;
}

.benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-card[data-mode-panel="rasgos"] .baiw-step3-layout .baiw-field {
    margin-top: 0;
}

.benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-step3-flags-list {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
    width: 100%;
}

.benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-step3-flags {
    grid-column: 1 / -1;
}

.benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-step3-flag {
    position: relative;
    display: flex;
    align-items: center;
    gap: 0;
    border: 1px solid rgba(124, 92, 201, 0.3);
    border-radius: 999px;
    padding: 6px 12px 6px 40px;
    background: rgba(33, 20, 67, 0.34);
    cursor: pointer;
    color: #ded1ff;
    font-weight: 500;
    overflow: hidden;
    min-height: 38px;
    min-width: 0;
    width: 100%;
    transition: border-color .2s ease, background-color .2s ease;
}

.benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-step3-flag::before {
    content: "";
    position: absolute;
    left: 10px;
    top: 50%;
    width: 20px;
    height: 20px;
    border-radius: 999px;
    transform: translateY(-50%);
    border: 1px solid rgba(126, 78, 255, 0.48);
    box-shadow: none;
    background: rgba(18, 9, 40, 0.9);
}

.benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-step3-flag::after {
    content: "";
    position: absolute;
    left: 14px;
    top: 50%;
    width: 12px;
    height: 12px;
    border-radius: 999px;
    transform: translateY(-50%);
    background: #0a041d;
    box-shadow: inset 0 0 0 1px rgba(167, 139, 250, 0.08);
}

.benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-step3-flag input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    margin: 0;
    cursor: pointer;
    z-index: 2;
}

.benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-step3-flag span {
    position: relative;
    z-index: 1;
    font-size: 0.74rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    text-transform: uppercase;
}

.benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-step3-flag:has(input:checked) {
    border-color: rgba(140, 102, 255, 0.52);
    background: rgba(50, 30, 95, 0.42);
}

.benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-step3-flag:has(input:checked)::before {
    border-color: rgba(155, 121, 255, 0.68);
    box-shadow: 0 0 0 1px rgba(124, 58, 255, 0.14);
}

.benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-step3-flag:has(input:checked)::after {
    background: #8f63ff;
}

/* Desktop: compactar paso 3 (rasgos) moviendo visibilidad al espacio libre */
@media (min-width: 901px) {
    .benditoai-modelos-wizard .baiw-step[data-step="3"][data-active-mode="rasgos"] {
        gap: 12px;
    }

    .benditoai-modelos-wizard .baiw-step[data-step="3"][data-active-mode="rasgos"] .baiw-consent-row {
        width: auto !important;
        max-width: 100% !important;
        margin-top: 8px !important;
        margin-left: 0 !important;
        position: static !important;
        z-index: auto !important;
        border-radius: 20px;
        padding: 0;
    }
}

.benditoai-modelos-wizard .baiw-toggle-row {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: center;
    border: 1px solid rgba(123, 91, 200, 0.35);
    border-radius: 12px;
    padding: 10px 12px;
    background: rgba(28, 16, 57, 0.55);
}

.benditoai-modelos-wizard .baiw-consent-row {
    display: inline-flex;
    width: auto;
    max-width: 100%;
    align-items: stretch;
    justify-content: flex-start;
    border: 1px solid rgba(123, 91, 200, 0.34);
    border-radius: 14px;
    padding: 0;
    background: rgba(23, 13, 49, 0.62);
    box-shadow: none;
}

.benditoai-modelos-wizard .baiw-consent-check {
    position: relative;
    display: inline-flex;
    width: auto;
    align-items: center;
    gap: 9px;
    padding: 8px 30px 8px 12px;
    min-height: 0px;
    cursor: pointer;
}

.benditoai-modelos-wizard .baiw-tip--consent {
    position: absolute;
    top: 50%;
    right: 6px;
    transform: translateY(-50%);
    z-index: 6;
    width: 15px;
    height: 15px;
    font-size: 0.58rem;
    border-width: 1px;
}

.benditoai-modelos-wizard .baiw-tip--consent .baiw-tip-bubble {
    right: 0;
    min-width: 250px;
}

.benditoai-modelos-wizard .baiw-consent-check input {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
}

.benditoai-modelos-wizard .baiw-consent-box {
    width: 14px;
    height: 14px;
    border-radius: 5px;
    border: 1px solid rgba(141, 111, 221, 0.68);
    background: rgba(10, 5, 31, 0.95);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    transition: 0.2s ease;
}

.benditoai-modelos-wizard .baiw-consent-box::after {
    content: "";
    width: 7px;
    height: 4px;
    border-left: 1.5px solid #ffffff;
    border-bottom: 1.5px solid #ffffff;
    transform: rotate(-45deg) translate(1px, -1px);
    opacity: 0;
    transition: 0.18s ease;
}

.benditoai-modelos-wizard .baiw-consent-check input:checked + .baiw-consent-box {
    background: #7c3aff;
    border-color: #9f77ff;
    box-shadow: 0 0 0 2px rgba(124, 58, 255, 0.12);
}

.benditoai-modelos-wizard .baiw-consent-check input:checked + .baiw-consent-box::after {
    opacity: 1;
}

.benditoai-modelos-wizard .baiw-consent-copy {
    display: block;
}

.benditoai-modelos-wizard .baiw-consent-copy small {
    display: block;
    margin: 0;
    font-size: 0.75rem;
    color: #a99ccb;
    line-height: 1.1;
    letter-spacing: 0.005em;
    text-transform: uppercase;
}

.benditoai-modelos-wizard .baiw-consent-state {
    display: none;
}

.benditoai-modelos-wizard .baiw-switch {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--baiw-ink);
    cursor: pointer;
}

.benditoai-modelos-wizard .baiw-switch input {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
}

.benditoai-modelos-wizard .baiw-switch-ui {
    width: 42px;
    height: 24px;
    border-radius: 999px;
    border: 1px solid rgba(135, 106, 209, 0.52);
    background: rgba(26, 15, 52, 0.85);
    display: inline-flex;
    align-items: center;
    padding: 2px;
    transition: 0.2s ease;
}

.benditoai-modelos-wizard .baiw-switch-knob {
    width: 18px;
    height: 18px;
    border-radius: 999px;
    background: #d9cbff;
    box-shadow: 0 1px 8px rgba(17, 5, 43, 0.4);
    transition: 0.2s ease;
}

.benditoai-modelos-wizard .baiw-switch input:checked + .baiw-switch-ui {
    background: rgba(124, 58, 255, 0.66);
    border-color: rgba(167, 139, 250, 0.68);
}

.benditoai-modelos-wizard .baiw-switch input:checked + .baiw-switch-ui .baiw-switch-knob {
    transform: translateX(17px);
}

.benditoai-modelos-wizard .baiw-grid-2 {
    margin-top: 8px;
    display: grid;
    gap: 8px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.benditoai-modelos-wizard .baiw-chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.benditoai-modelos-wizard .baiw-chip {
    border: 1px solid rgba(128, 98, 200, 0.45);
    border-radius: 999px;
    padding: 6px 10px;
    font-size: 0.82rem;
    background: rgba(40, 24, 79, 0.55);
    color: #ddd2fa;
    cursor: pointer;
}

.benditoai-modelos-wizard .baiw-chip input {
    width: auto;
    margin-right: 6px;
}

.benditoai-modelos-wizard .baiw-slider-group label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.benditoai-modelos-wizard .baiw-slider-group label strong {
    color: #ffffff;
    font-weight: 700;
}

.benditoai-modelos-wizard .baiw-rasgos-layout {
    margin-top: 8px;
    display: grid;
    gap: 12px;
}

.benditoai-modelos-wizard .baiw-slider-row {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    padding: 0;
    border: none;
    background: transparent;
}

.benditoai-modelos-wizard .baiw-slider-row .baiw-slider-group {
    margin-top: 0;
    padding: 12px 12px 10px;
    border-radius: 12px;
    border: 1px solid rgba(124, 92, 201, 0.36);
    background: linear-gradient(180deg, rgba(20, 11, 44, 0.84), rgba(14, 8, 32, 0.88));
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
}

.benditoai-modelos-wizard .baiw-fields-grid {
    position: relative;
    isolation: isolate;
    overflow: visible;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

.benditoai-modelos-wizard .baiw-fields-grid .baiw-field {
    position: relative;
    z-index: 1;
    overflow: visible;
}

.benditoai-modelos-wizard .baiw-fields-grid .baiw-field.baiw-field--open {
    z-index: 900;
}

.benditoai-modelos-wizard .baiw-field--full {
    grid-column: 1 / -1;
}

.benditoai-modelos-wizard .baiw-range {
    -webkit-appearance: none;
    appearance: none;
    height: 8px;
    border-radius: 999px;
    border: none;
    outline: none;
    background: linear-gradient(90deg, #7c3aff 0%, #7c3aff var(--range-progress, 50%), #23153d var(--range-progress, 50%), #23153d 100%);
    box-shadow: inset 0 0 0 1px rgba(146, 122, 214, 0.45);
    padding: 0;
}

.benditoai-modelos-wizard .baiw-range::-webkit-slider-runnable-track {
    height: 8px;
    border-radius: 999px;
    background: transparent;
    border: none;
}

.benditoai-modelos-wizard .baiw-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 19px;
    height: 19px;
    border-radius: 50%;
    border: 2px solid #1a1031;
    background: #7c3aff;
    box-shadow: 0 0 0 1px rgba(210, 196, 255, 0.18), 0 3px 9px rgba(6, 2, 19, 0.58);
    cursor: pointer;
    margin-top: -5px;
}

.benditoai-modelos-wizard .baiw-range::-moz-range-track {
    height: 8px;
    border-radius: 999px;
    background: #23153d;
    border: 1px solid rgba(146, 122, 214, 0.45);
}

.benditoai-modelos-wizard .baiw-range::-moz-range-progress {
    height: 8px;
    border-radius: 999px;
    background: linear-gradient(90deg, #6f33e8, #7c3aff);
}

.benditoai-modelos-wizard .baiw-range::-moz-range-thumb {
    width: 19px;
    height: 19px;
    border-radius: 50%;
    border: 2px solid #1a1031;
    background: #7c3aff;
    box-shadow: 0 0 0 1px rgba(210, 196, 255, 0.18), 0 3px 9px rgba(6, 2, 19, 0.58);
    cursor: pointer;
}

.benditoai-modelos-wizard .choices {
    margin: 0;
    z-index: 20;
}

.benditoai-modelos-wizard .baiw-field .choices.is-open {
    position: relative;
    z-index: 901;
}

.benditoai-modelos-wizard .choices__inner {
    min-height: 50px;
    border-radius: 18px;
    border: 1px solid #5d2cce;
    background: #0c0722;
    color: #f7f3ff;
    padding: 9px 14px;
    font-size: 0.92rem;
    box-shadow: inset 0 0 0 2px rgba(124, 58, 255, 0.15);
}

.benditoai-modelos-wizard .is-open .choices__inner {
    border-color: #7c3aff;
    box-shadow: inset 0 0 0 2px rgba(124, 58, 255, 0.26);
}

.benditoai-modelos-wizard .choices__list--single {
    padding: 4px 20px 4px 2px;
    color: #ffffff;
    font-weight: 600;
}

.benditoai-modelos-wizard .choices__item--selectable {
    opacity: 1 !important;
}

.benditoai-modelos-wizard .choices[data-type*="select-one"]::after {
    border-color: #bda6ff transparent transparent;
    right: 12px;
    margin-top: -4px;
}

.benditoai-modelos-wizard .choices[data-type*="select-one"].is-open::after {
    border-color: transparent transparent #bda6ff;
    margin-top: -9px;
}

.benditoai-modelos-wizard .choices__list--dropdown,
.benditoai-modelos-wizard .choices__list[aria-expanded] {
    z-index: 120;
    margin-top: 0;
    border-radius: 0;
    border: 1px solid rgba(228, 221, 250, 0.65);
    background: #140c2f !important;
    box-shadow: 0 16px 36px rgba(2, 1, 9, 0.56);
    overflow: hidden;
}

.benditoai-modelos-wizard .choices__list--dropdown .choices__list,
.benditoai-modelos-wizard .choices__list[aria-expanded] .choices__list {
    background: #140c2f !important;
}

.benditoai-modelos-wizard .choices__list--dropdown .choices__item,
.benditoai-modelos-wizard .choices__list[aria-expanded] .choices__item {
    position: relative;
    display: flex;
    align-items: center;
    color: #ffffff;
    font-weight: 600;
    min-height: 82px;
    padding: 18px 98px 18px 16px;
    border-bottom: 1px solid rgba(235, 228, 255, 0.86);
    background: #1a0f40 !important;
}

.benditoai-modelos-wizard .choices__list--dropdown .choices__item::before,
.benditoai-modelos-wizard .choices__list[aria-expanded] .choices__item::before {
    content: "";
    position: absolute;
    right: 80px;
    top: 0;
    bottom: 0;
    width: 1px;
    background: rgba(235, 228, 255, 0.86);
}

.benditoai-modelos-wizard .choices__list--dropdown .choices__item::after,
.benditoai-modelos-wizard .choices__list[aria-expanded] .choices__item::after {
    content: "";
    position: absolute;
    right: 16px;
    top: 50%;
    width: 52px;
    height: 52px;
    transform: translateY(-50%);
    border-radius: 11px;
    border: 1px solid rgba(242, 236, 255, 0.78);
    background:
        radial-gradient(circle at 50% 36%, #f5dccb 12px, transparent 13px),
        radial-gradient(circle at 50% 22%, #11101a 15px, transparent 16px),
        linear-gradient(180deg, #f7f4ee 0 62%, #dac9c0 100%);
}

.benditoai-modelos-wizard .choices__list--dropdown .choices__item--selectable.is-selected::after,
.benditoai-modelos-wizard .choices__list[aria-expanded] .choices__item--selectable.is-selected::after {
    content: "" !important;
    display: block !important;
}

.benditoai-modelos-wizard .choices__list--dropdown .choices__item--selectable.is-highlighted,
.benditoai-modelos-wizard .choices__list[aria-expanded] .choices__item--selectable.is-highlighted {
    background: #2f0b8a;
}

.benditoai-modelos-wizard .choices__list--dropdown .choices__item--selectable.is-highlighted {
    box-shadow: inset 4px 0 0 #35a0ff;
}

.benditoai-modelos-wizard .choices__list[aria-expanded] .choices__item--selectable.is-highlighted {
    box-shadow: inset 4px 0 0 #35a0ff;
}

.benditoai-modelos-wizard .choices__list--dropdown .choices__item--selectable.is-highlighted,
.benditoai-modelos-wizard .choices__list[aria-expanded] .choices__item--selectable.is-highlighted {
    color: #ffffff;
}

.benditoai-modelos-wizard .baiw-error-inline {
    display: none;
    border-radius: 12px;
    border: 1px solid rgba(251, 113, 133, 0.45);
    background: rgba(89, 19, 38, 0.55);
    color: #ffc2cf;
    padding: 10px 12px;
    font-size: 0.84rem;
}

.benditoai-modelos-wizard .baiw-nav {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 8px;
}

.benditoai-modelos-wizard .baiw-btn {
    border-radius: 12px;
    border: 1.5px solid rgba(94, 29, 247, 0.30) !important;
    background: rgba(94, 29, 247, 0.12) !important;
    padding: 10px 14px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #A78BFA !important;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: 0.18s ease;
    box-shadow: none !important;
}

.benditoai-modelos-wizard .baiw-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.benditoai-modelos-wizard .baiw-btn--ghost {
    color: #A78BFA !important;
    border-color: rgba(94, 29, 247, 0.30) !important;
    background: rgba(94, 29, 247, 0.12) !important;
}

.benditoai-modelos-wizard .baiw-btn--ghost:hover {
    border-color: rgba(94, 29, 247, 0.55) !important;
    background: rgba(94, 29, 247, 0.20) !important;
}

.benditoai-modelos-wizard .baiw-btn--primary {
    color: #A78BFA !important;
    border-color: rgba(94, 29, 247, 0.30) !important;
    background: rgba(94, 29, 247, 0.12) !important;
}

.benditoai-modelos-wizard .baiw-btn--primary:hover {
    border-color: rgba(94, 29, 247, 0.55) !important;
    background: rgba(94, 29, 247, 0.20) !important;
    filter: none;
}

.benditoai-modelos-wizard .baiw-btn--accent {
    margin-left: auto;
    color: #A78BFA !important;
    border-color: rgba(94, 29, 247, 0.30) !important;
    background: rgba(94, 29, 247, 0.12) !important;
    box-shadow: none !important;
}

.benditoai-modelos-wizard .baiw-btn--accent:hover {
    transform: none;
    border-color: rgba(94, 29, 247, 0.55) !important;
    background: rgba(94, 29, 247, 0.20) !important;
    filter: none;
}

.benditoai-modelos-wizard .baiw-btn--loading {
    opacity: 0.9;
    cursor: wait;
}

.benditoai-modelos-wizard .baiw-result-stage {
    min-height: auto;
    display: grid;
    place-items: center;
    text-align: center;
    opacity: 1;
    transform: translateY(0);
    transition: opacity 0.28s ease, transform 0.32s ease;
}

.benditoai-modelos-wizard .baiw-result-stage-inner {
    width: min(860px, 100%);
    margin: 0 auto;
    border: 1px solid rgba(124, 92, 201, 0.33);
    border-radius: 18px;
    background: rgba(20, 12, 42, 0.74);
    backdrop-filter: blur(8px);
    padding: clamp(16px, 3vw, 28px);
}

.benditoai-modelos-wizard.is-loading-result .baiw-result-stage {
    margin-top: 2px;
    animation: baiwResultReveal 0.42s cubic-bezier(0.2, 0.78, 0.24, 1) both;
}

.benditoai-modelos-wizard.is-loading-result .baiw-result-stage-inner {
    border-color: rgba(124, 92, 201, 0.42);
    box-shadow: 0 14px 32px rgba(8, 4, 26, 0.38);
}

.benditoai-modelos-wizard .baiw-result-title {
    margin: 2px 0 14px;
    font-size: clamp(1.35rem, 1.1rem + 1vw, 1.85rem);
}

.benditoai-modelos-wizard .benditoai-loading {
    display: none;
    margin: 0 auto 12px;
    border-radius: 10px;
    border: 1px solid rgba(124, 58, 255, 0.5);
    background: rgba(81, 45, 170, 0.38);
    color: #ede6ff;
    padding: 10px 12px;
    font-size: 0.84rem;
    max-width: 430px;
}

.benditoai-modelos-wizard .baiw-final-error {
    margin: 0 auto 12px;
    max-width: 560px;
}

.benditoai-modelos-wizard .benditoai-error-message {
    margin: 0 0 10px;
    border-radius: 10px;
    border: 1px solid rgba(251, 113, 133, 0.5);
    background: rgba(95, 24, 43, 0.58);
    color: #ffd2dc;
    padding: 10px 12px;
    font-size: 0.84rem;
}

.benditoai-modelos-wizard .baiw-result-image {
    margin: 0 auto;
    width: min(520px, 100%);
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid rgba(130, 102, 202, 0.5);
    box-shadow: 0 22px 36px rgba(4, 2, 13, 0.38);
}

.benditoai-modelos-wizard .baiw-result-skeleton {
    width: min(520px, 100%);
    margin: 0 auto;
    height: 320px;
    border-radius: 14px;
    border: 1px solid rgba(130, 102, 202, 0.5);
}

.benditoai-modelos-wizard .benditoai-generated-image {
    width: 100%;
    height: auto;
    display: block;
}

.benditoai-modelos-wizard .baiw-action-row {
    margin-top: 14px;
    display: grid;
    gap: 8px;
}

.benditoai-modelos-wizard .baiw-action-row--main {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.benditoai-modelos-wizard .baiw-action-row--secondary {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

@media (max-width: 900px) {
    .benditoai-modelos-wizard .baiw-header {
        flex-direction: column;
    }

    .benditoai-modelos-wizard .baiw-credits {
        width: 100%;
    }
}

@media (min-width: 901px) {
    .benditoai-modelos-wizard .baiw-result-stage .baiw-kicker,
    .benditoai-modelos-wizard .baiw-result-stage .baiw-result-title {
        display: none;
    }
}

@media (max-width: 720px) {
    .benditoai-modelos-wizard .baiw-mode-grid,
    .benditoai-modelos-wizard .baiw-grid-2,
    .benditoai-modelos-wizard .baiw-fields-grid,
    .benditoai-modelos-wizard .baiw-slider-row,
    .benditoai-modelos-wizard .baiw-action-row--main,
    .benditoai-modelos-wizard .baiw-action-row--secondary {
        grid-template-columns: 1fr;
    }

    .benditoai-modelos-wizard .baiw-stepper li {
        padding: 9px 8px;
    }

    .benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-card[data-mode-panel="rasgos"] .baiw-step3-layout {
        grid-template-columns: 1fr;
    }

    .benditoai-modelos-wizard .baiw-step[data-step="3"] .baiw-step3-flags-list {
        grid-template-columns: 1fr;
    }

    .benditoai-modelos-wizard .baiw-consent-row {
        display: inline-flex;
        width: auto;
        max-width: 100%;
    }

    .benditoai-modelos-wizard .baiw-consent-check {
        min-height: 0;
        padding: 8px 10px;
    }

    .benditoai-modelos-wizard .baiw-nav {
        flex-direction: column;
    }

    .benditoai-modelos-wizard .baiw-btn--accent {
        margin-left: 0;
    }

    .benditoai-modelos-wizard .baiw-result-stage {
        min-height: auto;
    }
}

@media (prefers-reduced-motion: reduce) {
    .benditoai-modelos-wizard * {
        transition: none !important;
        animation: none !important;
    }
}

@keyframes baiwResultReveal {
    from {
        opacity: 0;
        transform: translateY(24px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<?php

    return ob_get_clean();
}

add_shortcode('benditoai_modelos_ai', 'benditoai_modelos_ai_shortcode');
