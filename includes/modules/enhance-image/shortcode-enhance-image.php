<?php

if (!defined('ABSPATH')) exit;

function benditoai_enhance_image_shortcode() {

    if (!is_user_logged_in()) {
        return '<div class="benditoai-auth-message">Debes iniciar sesión.</div>';
    }

    ob_start();
    ?>

    <div class="benditoai-wrapper">

        <h3>✨ Subir imagen</h3>

        <form class="benditoai-form__group benditoai-ai-form"
              id="benditoai-enhance-image-form"
              enctype="multipart/form-data">

            <div class="benditoai-form__group">
                <input class="benditoai-form__input"
                       type="file"
                       name="imagen"
                       accept="image/*"
                       required>
            </div>

            <div class="benditoai-form__actions benditoai-form__group">
                <button type="submit"
                        class="benditoai-btn benditoai-btn--primary benditoai-ai-button">
                    Mejorar imagen
                </button>
            </div>

        </form>

        <div id="benditoai-enhance-result">
            <div class="benditoai-ai-preview-stage" data-ai-preview-stage>
                <div class="benditoai-ai-preview-placeholder" data-ai-preview-placeholder>
                    <i class="fa-regular fa-image"></i>
                    <span>Tu imagen mejorada aparecerá aquí</span>
                </div>

                <div class="benditoai-image-wrapper" style="display:none;">
                    <img class="benditoai-generated-image"
                         src=""
                         alt="Imagen mejorada">

                    <a class="benditoai-download-btn"
                       href=""
                       download="BenditoAI-enhanced.png">
                        <img class="benditoai-download-icon"
                             src="<?php echo plugin_dir_url(dirname(__FILE__, 3)) . 'assets/images/download-icon.png'; ?>"
                             alt="Descargar">
                    </a>
                </div>
            </div>
        </div>

    </div>

    <?php

    return ob_get_clean();
}

add_shortcode('benditoai_enhance_image', 'benditoai_enhance_image_shortcode');
