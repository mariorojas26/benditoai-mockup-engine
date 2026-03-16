<?php

if (!defined('ABSPATH')) exit;

function benditoai_trending_shortcode() {

    if (!is_user_logged_in()) {
        return '<div class="benditoai-auth-message">Debes iniciar sesión para usar esta herramienta.</div>';
    }

    ob_start();
?>

<div class="benditoai-wrapper benditoai-wrapper-tendencia">

    <form class="benditoai-form__group"
          id="benditoai-trending-form"
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
                    class="benditoai-btn benditoai-btn--primary">

                Generar tendencia

            </button>

        </div>

           <div class="benditoai-tips"> 
                <p class="benditoai-tips-title">
                    Nota: La vista previa puede verse recortada. Después de generar la imagen, descárgala para verla completa.
                </p>
            </div>

    </form>

    <div id="benditoai-trending-result">
        

        <p class="benditoai-loading" style="display:none;">
            🔥 Generando tendencia...
        </p>

        <div class="benditoai-image-wrapper" style="display:none;">

            <img class="benditoai-generated-image"
                 src=""
                 alt="Imagen generada con tendencia">

            <a class="benditoai-download-btn"
               href=""
               download="BenditoAI-trend.png">

                <img class="benditoai-download-icon"
                     src="<?php echo plugin_dir_url(dirname(__FILE__,3)) . 'assets/images/download-icon.png'; ?>"
                     alt="Descargar">

            </a>

        </div>

    </div>

</div>

<?php

    return ob_get_clean();
}

add_shortcode('benditoai_trending', 'benditoai_trending_shortcode');


