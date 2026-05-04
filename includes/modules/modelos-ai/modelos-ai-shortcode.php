<?php

if (!defined('ABSPATH')) {
    exit;
}

function benditoai_modelos_ai_shortcode() {

    if (!is_user_logged_in()) {
        return '<div class="benditoai-auth-message">Debes iniciar sesión para usar esta herramienta.</div>';
    }

    $traits_img_base = plugin_dir_url(__FILE__) . '../../../assets/images/traits/';
    ob_start();
?>

<div class="benditoai-wrapper-modelos benditoai-v2">

    <div class="benditoai-modelos-hero">
        <h2>Herramienta: Creador de modelos IA</h2>
        <p>Sube una referencia o usa el wizard para generar tu modelo.</p>
    </div>

    <div class="benditoai-modelo-shell">

        <div class="benditoai-modelo-stage" id="benditoai-modelo-stage-main">
            <img id="benditoai-modelo-stage-image" src="" alt="Imagen de referencia del modelo" style="display:none;">

            <div class="benditoai-modelo-stage-placeholder" id="benditoai-modelo-stage-placeholder">
                <i class="fa-regular fa-image"></i>
                <p>Tu imagen de referencia aparecerá aquí al subir foto o al generar un retrato con IA.</p>
            </div>
        </div>

        <form id="benditoai-form-modelo-ai" enctype="multipart/form-data">

            <input type="hidden" name="reference_source" id="benditoai-reference-source" value="">
            <input type="hidden" name="reference_image_url" id="benditoai-reference-image-url" value="">
            <input type="hidden" name="generated_prompt" id="benditoai-generated-prompt" value="">
            <input type="hidden" name="traits_payload" id="benditoai-traits-payload" value="">
            <input type="hidden" name="prompt_preview" id="benditoai-prompt-preview-hidden" value="">

            <input type="file" id="benditoai-upload-image-input" name="reference_image" accept="image/*" style="display:none;">

            <div class="benditoai-form-card">
                <p class="benditoai-form-card-title">Paso 1: Elige tu referencia</p>
                <div class="benditoai-entry-actions">
                    <button type="button" class="benditoai-entry-action" id="benditoai-upload-trigger">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Subir imagen</span>
                        <small>Usa una foto propia como base del modelo</small>
                    </button>

                    <button type="button" class="benditoai-entry-action" id="benditoai-open-generator">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>Generar influencer IA</span>
                        <small>Abre el wizard para crear la referencia ideal</small>
                    </button>
                </div>
            </div>

            <div class="benditoai-form-card">
                <p class="benditoai-form-card-title">Paso 2: Define el modelo</p>

                <label for="benditoai-nombre-modelo">Nombre del modelo</label>
                <div class="benditoai-input-wrap">
                    <input id="benditoai-nombre-modelo" type="text" name="nombre_modelo" maxlength="32" required placeholder="Ej: Camila UGC 01">
                    <span class="benditoai-counter" id="benditoai-nombre-counter">0 / 32</span>
                </div>

                <label for="benditoai-descripcion-modelo">Descripción</label>
                <div class="benditoai-input-wrap">
                    <textarea id="benditoai-descripcion-modelo" name="descripcion_modelo" maxlength="512" rows="4" placeholder="Describe estilo, tono, nicho o detalles de marca"></textarea>
                    <span class="benditoai-counter" id="benditoai-descripcion-counter">0 / 512</span>
                </div>

                <div class="benditoai-public-row">
                    <label for="benditoai-modelo-publico">Modelo público</label>
                    <label class="benditoai-switch">
                        <input type="checkbox" id="benditoai-modelo-publico" name="is_public" value="1" checked>
                        <span class="benditoai-slider"></span>
                    </label>
                </div>
            </div>

            <button type="submit" class="benditoai-create-btn" id="benditoai-create-model-btn" disabled>
                Crear tu modelo de imagen
            </button>

            <p class="benditoai-error-message" id="benditoai-modelo-error" style="display:none;"></p>
            <p class="benditoai-loading" id="benditoai-modelo-loading">Generando y guardando modelo...</p>

            <div class="benditoai-prompt-debug" id="benditoai-prompt-debug" style="display:none;">
                <label for="benditoai-prompt-preview">Prompt enviado a Gemini</label>
                <textarea id="benditoai-prompt-preview" rows="8" readonly></textarea>
            </div>

        </form>

    </div>

    <div class="benditoai-influencer-modal" id="benditoai-influencer-modal" aria-hidden="true">
        <div class="benditoai-influencer-backdrop" data-close-modal="1"></div>

        <div class="benditoai-influencer-dialog" role="dialog" aria-modal="true" aria-labelledby="benditoai-influencer-title">

            <div class="benditoai-influencer-header">
                <div>
                    <h3 id="benditoai-influencer-title">Generador de influencer con IA</h3>
                    <p class="benditoai-influencer-subtitle">Completa el wizard y genera una referencia para tu modelo.</p>
                </div>
                <button type="button" class="benditoai-close-modal" id="benditoai-close-generator" aria-label="Cerrar">×</button>
            </div>

            <div class="benditoai-influencer-body">

                <div class="benditoai-modal-preview-col">
                    <p class="benditoai-modal-label">Vista previa generada</p>

                    <div class="benditoai-modal-stage" id="benditoai-modal-stage">
                        <img id="benditoai-modal-image" src="" alt="Imagen generada" style="display:none;">
                        <div class="benditoai-modal-stage-placeholder" id="benditoai-modal-placeholder">
                            <i class="fa-regular fa-image"></i>
                            <span>Tu generación aparecerá aquí</span>
                        </div>
                    </div>

                    <button type="button" class="benditoai-select-ref-btn" id="benditoai-select-generated-image" disabled>
                        Seleccionar esta imagen como referencia
                    </button>
                </div>

                <div class="benditoai-modal-controls-col">

                    <button type="button" class="benditoai-random-btn" id="benditoai-randomize-traits">
                        <i class="fa-solid fa-shuffle"></i>
                        Aleatorio
                    </button>

                    <div class="benditoai-field-block">
                        <span class="benditoai-field-title">Género</span>
                        <div class="benditoai-gender-row" id="benditoai-gender-row">
                            <label class="benditoai-gender-pill">
                                <input type="radio" name="influencer_genero" value="Hombre">
                                <span><i class="fa-solid fa-mars"></i> Hombre</span>
                            </label>
                            <label class="benditoai-gender-pill">
                                <input type="radio" name="influencer_genero" value="Mujer" checked>
                                <span><i class="fa-solid fa-venus"></i> Mujer</span>
                            </label>
                            <label class="benditoai-gender-pill">
                                <input type="radio" name="influencer_genero" value="Secreto">
                                <span><i class="fa-regular fa-circle-dot"></i> Secreto</span>
                            </label>
                        </div>
                    </div>

                    <div class="benditoai-field-block">
                        <div class="benditoai-range-head">
                            <span>Edad</span>
                            <strong id="benditoai-age-value">28</strong>
                        </div>
                        <input type="range" id="benditoai-age-range" min="18" max="60" value="28">
                    </div>

                    <div class="benditoai-field-block">
                        <div class="benditoai-range-head">
                            <span>Altura</span>
                            <strong id="benditoai-height-value">168 cm</strong>
                        </div>
                        <input type="range" id="benditoai-height-range" min="145" max="200" value="168">
                    </div>

                    <div class="benditoai-field-block">
                        <div class="benditoai-range-head">
                            <span>Peso</span>
                            <strong id="benditoai-weight-value">58 kg</strong>
                        </div>
                        <input type="range" id="benditoai-weight-range" min="40" max="130" value="58">
                    </div>

                    <div class="benditoai-two-cols">
                        <div class="benditoai-field-block">
                            <label for="benditoai-country-main">País</label>
                            <select id="benditoai-country-main">
                                <option value="Colombia">Colombia</option>
                                <option value="México">México</option>
                                <option value="Argentina">Argentina</option>
                                <option value="España">España</option>
                                <option value="Brasil">Brasil</option>
                                <option value="Estados Unidos">Estados Unidos</option>
                            </select>
                        </div>

                        <div class="benditoai-field-block">
                            <label for="benditoai-country-secondary">País 2</label>
                            <select id="benditoai-country-secondary">
                                <option value="Ninguno">Ninguno</option>
                                <option value="Colombia">Colombia</option>
                                <option value="México">México</option>
                                <option value="Argentina">Argentina</option>
                                <option value="España">España</option>
                                <option value="Brasil">Brasil</option>
                                <option value="Estados Unidos">Estados Unidos</option>
                            </select>
                        </div>
                    </div>

                    <div class="benditoai-two-cols">
                        <div class="benditoai-field-block">
                            <span class="benditoai-field-title">Constitución</span>
                            <div class="benditoai-trait-select" data-trait-select="constitucion">
                                <button type="button" class="benditoai-trait-select-trigger" data-trait-trigger aria-expanded="false">
                                    <span class="benditoai-trait-select-current">
                                        <img src="<?php echo esc_url($traits_img_base . 'body-athletic.svg'); ?>" alt="Atlética" data-trait-current-image>
                                        <span data-trait-current-label>Atlética</span>
                                    </span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                                <div class="benditoai-trait-select-menu" data-trait-menu>
                                    <button type="button" class="benditoai-trait-option is-active" data-value="Atlética" data-image="<?php echo esc_url($traits_img_base . 'body-athletic.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'body-athletic.svg'); ?>" alt="Atlética"><span>Atlética</span>
                                    </button>
                                    <button type="button" class="benditoai-trait-option" data-value="Delgada" data-image="<?php echo esc_url($traits_img_base . 'body-slim.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'body-slim.svg'); ?>" alt="Delgada"><span>Delgada</span>
                                    </button>
                                    <button type="button" class="benditoai-trait-option" data-value="Curvy" data-image="<?php echo esc_url($traits_img_base . 'body-curvy.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'body-curvy.svg'); ?>" alt="Curvy"><span>Curvy</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="benditoai-field-block">
                            <span class="benditoai-field-title">Ojos</span>
                            <div class="benditoai-trait-select" data-trait-select="ojos">
                                <button type="button" class="benditoai-trait-select-trigger" data-trait-trigger aria-expanded="false">
                                    <span class="benditoai-trait-select-current">
                                        <img src="<?php echo esc_url($traits_img_base . 'eyes-amber.svg'); ?>" alt="Ámbar" data-trait-current-image>
                                        <span data-trait-current-label>Ámbar</span>
                                    </span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                                <div class="benditoai-trait-select-menu" data-trait-menu>
                                    <button type="button" class="benditoai-trait-option is-active" data-value="Ámbar" data-image="<?php echo esc_url($traits_img_base . 'eyes-amber.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'eyes-amber.svg'); ?>" alt="Ámbar"><span>Ámbar</span>
                                    </button>
                                    <button type="button" class="benditoai-trait-option" data-value="Marrón" data-image="<?php echo esc_url($traits_img_base . 'eyes-brown.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'eyes-brown.svg'); ?>" alt="Marrón"><span>Marrón</span>
                                    </button>
                                    <button type="button" class="benditoai-trait-option" data-value="Verde" data-image="<?php echo esc_url($traits_img_base . 'eyes-green.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'eyes-green.svg'); ?>" alt="Verde"><span>Verde</span>
                                    </button>
                                    <button type="button" class="benditoai-trait-option" data-value="Azul" data-image="<?php echo esc_url($traits_img_base . 'eyes-blue.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'eyes-blue.svg'); ?>" alt="Azul"><span>Azul</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="benditoai-two-cols">
                        <div class="benditoai-field-block">
                            <span class="benditoai-field-title">Peinado</span>
                            <div class="benditoai-trait-select" data-trait-select="peinado">
                                <button type="button" class="benditoai-trait-select-trigger" data-trait-trigger aria-expanded="false">
                                    <span class="benditoai-trait-select-current">
                                        <img src="<?php echo esc_url($traits_img_base . 'hair-pixie.svg'); ?>" alt="Pixie lateral" data-trait-current-image>
                                        <span data-trait-current-label>Pixie lateral</span>
                                    </span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                                <div class="benditoai-trait-select-menu" data-trait-menu>
                                    <button type="button" class="benditoai-trait-option is-active" data-value="Pixie lateral" data-image="<?php echo esc_url($traits_img_base . 'hair-pixie.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'hair-pixie.svg'); ?>" alt="Pixie lateral"><span>Pixie lateral</span>
                                    </button>
                                    <button type="button" class="benditoai-trait-option" data-value="Largo ondulado" data-image="<?php echo esc_url($traits_img_base . 'hair-long.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'hair-long.svg'); ?>" alt="Largo ondulado"><span>Largo ondulado</span>
                                    </button>
                                    <button type="button" class="benditoai-trait-option" data-value="Bob liso" data-image="<?php echo esc_url($traits_img_base . 'hair-bob.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'hair-bob.svg'); ?>" alt="Bob liso"><span>Bob liso</span>
                                    </button>
                                    <button type="button" class="benditoai-trait-option" data-value="Rizos naturales" data-image="<?php echo esc_url($traits_img_base . 'hair-curly.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'hair-curly.svg'); ?>" alt="Rizos naturales"><span>Rizos naturales</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="benditoai-field-block">
                            <span class="benditoai-field-title">Color de pelo</span>
                            <div class="benditoai-trait-select" data-trait-select="color_pelo">
                                <button type="button" class="benditoai-trait-select-trigger" data-trait-trigger aria-expanded="false">
                                    <span class="benditoai-trait-select-current">
                                        <img src="<?php echo esc_url($traits_img_base . 'color-honey.svg'); ?>" alt="Miel" data-trait-current-image>
                                        <span data-trait-current-label>Miel</span>
                                    </span>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                                <div class="benditoai-trait-select-menu" data-trait-menu>
                                    <button type="button" class="benditoai-trait-option is-active" data-value="Miel" data-image="<?php echo esc_url($traits_img_base . 'color-honey.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'color-honey.svg'); ?>" alt="Miel"><span>Miel</span>
                                    </button>
                                    <button type="button" class="benditoai-trait-option" data-value="Negro" data-image="<?php echo esc_url($traits_img_base . 'color-black.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'color-black.svg'); ?>" alt="Negro"><span>Negro</span>
                                    </button>
                                    <button type="button" class="benditoai-trait-option" data-value="Castaño" data-image="<?php echo esc_url($traits_img_base . 'color-brown.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'color-brown.svg'); ?>" alt="Castaño"><span>Castaño</span>
                                    </button>
                                    <button type="button" class="benditoai-trait-option" data-value="Rubio" data-image="<?php echo esc_url($traits_img_base . 'color-blonde.svg'); ?>">
                                        <img src="<?php echo esc_url($traits_img_base . 'color-blonde.svg'); ?>" alt="Rubio"><span>Rubio</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="benditoai-check-row">
                        <label><input type="checkbox" id="benditoai-hoyuelos"> Hoyuelos</label>
                        <label><input type="checkbox" id="benditoai-barba"> Barba</label>
                        <label><input type="checkbox" id="benditoai-bronceado" checked> Bronceado</label>
                    </div>

                    <div class="benditoai-field-block">
                        <label for="benditoai-custom-details">Detalles a medida</label>
                        <textarea id="benditoai-custom-details" rows="2" placeholder="Ej: sonrisa cálida, estilo editorial, maquillaje natural"></textarea>
                    </div>

                    <div class="benditoai-modal-actions">
                        <button type="button" class="benditoai-reset-btn" id="benditoai-reset-traits">Restablecer</button>
                        <button type="button" class="benditoai-generate-btn" id="benditoai-generate-influencer">
                            Generar
                            <small>-1 crédito</small>
                        </button>
                    </div>

                    <p class="benditoai-error-message" id="benditoai-generator-error" style="display:none;"></p>
                    <p class="benditoai-loading" id="benditoai-generator-loading">Generando referencia...</p>

                </div>

            </div>

        </div>

    </div>

</div>

<?php

    return ob_get_clean();
}

add_shortcode('benditoai_modelos_ai','benditoai_modelos_ai_shortcode');




