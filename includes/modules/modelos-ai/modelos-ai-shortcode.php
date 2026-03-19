<?php

if (!defined('ABSPATH')) {
    exit;
}

function benditoai_modelos_ai_shortcode() {

    if (!is_user_logged_in()) {
        return '<div class="benditoai-auth-message">Debes iniciar sesión para usar esta herramienta.</div>';
    }

    ob_start();
?>

<div class="benditoai-wrapper-modelos">

    <div class="benditoai-wrapper">

        <div class="benditoai-modelos-header">
            <h2>Creador de Modelo IA</h2>
            <p>Diseña tu modelo virtual para usarlo luego en el generador de mockups.</p>
        </div>

        <form id="benditoai-form-modelo-ai">

            <div class="benditoai-top-grid">

                <!-- RASGOS DEL MODELO -->

                <div class="benditoai-card">

                    <h3>Rasgos del modelo</h3>

                    <label>Nombre del modelo</label>
                    <input 
                        type="text"
                        name="nombre_modelo"
                        placeholder="Ej: Modelo urbano 01"
                        required
                    >

                    <label>Género</label>
                    <select name="genero">
                        <option value="male">Hombre</option>
                        <option value="female">Mujer</option>
                    </select>

                    <label>Edad</label>
                    <select name="edad">
                        <option value="young adult">18-25</option>
                        <option value="adult">25-35</option>
                        <option value="mature">35-45</option>
                    </select>

                    <label>Tipo de cuerpo</label>
                    <select name="cuerpo">
                        <option value="athletic">Atlético</option>
                        <option value="slim">Delgado</option>
                        <option value="average">Promedio</option>
                    </select>

                    <label>Etnia</label>
                    <select name="etnia">
                        <option value="latin">Latino</option>
                        <option value="european">Europeo</option>
                        <option value="african">Afro</option>
                        <option value="asian">Asiático</option>
                    </select>

                    <label>Estilo general</label>
                    <select name="estilo">
                        <option value="streetwear">Streetwear</option>
                        <option value="fashion">Fashion</option>
                        <option value="fitness">Fitness</option>
                        <option value="minimalist">Minimalista</option>
                    </select>

                </div>

                <!-- VESTIMENTA -->

                <div class="benditoai-card">

                    <h3>Vestimenta</h3>

                    <label>Prenda superior</label>
                    <textarea 
                        name="prenda_superior"
                        placeholder="Ej: hoodie negro oversize"
                    ></textarea>

                    <label>Prenda inferior</label>
                    <textarea 
                        name="prenda_inferior"
                        placeholder="Ej: pantalón cargo gris"
                    ></textarea>

                    <label>Zapatos</label>
                    <textarea 
                        name="zapatos"
                        placeholder="Ej: Nike Air Force blancas"
                    ></textarea>

                    <label>Accesorios</label>
                    <textarea 
                        name="accesorios"
                        placeholder="Ej: gafas negras y reloj minimalista"
                    ></textarea>

                </div>

            </div>

            <!-- PARTE INFERIOR -->

            <div class="benditoai-bottom-grid">

                <!-- GENERADOR -->

                <div class="benditoai-card benditoai-generator">

                    <h3>Generar modelo</h3>

                    <p class="benditoai-info">
                        Define las características del modelo y presiona generar.
                        La IA creará un avatar completo listo para usar en mockups.
                    </p>

                    <button 
                        type="submit"
                        class="benditoai-btn benditoai-btn--primary"
                    >
                        Generar modelo
                    </button>

                    <p class="benditoai-error-message" style="display:none;"></p>

                    <p class="benditoai-loading" id="benditoai-modelo-loading">
                        🤖 Generando modelo...
                    </p>

                </div>

                <!-- RESULTADO -->

                <div class="benditoai-card benditoai-render">

                    <h3>Resultado</h3>

                    <div id="benditoai-modelo-resultado">

                        <p class="benditoai-placeholder">
                            La imagen generada aparecerá aquí.
                        </p>

                    </div>

                    <div class="benditoai-image-wrapper" style="display:none;">

                        <img 
                            class="benditoai-generated-image"
                            src=""
                            alt="Modelo generado"
                        >

                        <a 
                            class="benditoai-download-btn"
                            href=""
                            download="modelo-benditoai.png"
                        >

                            <img 
                                class="benditoai-download-icon"
                                src="<?php echo plugin_dir_url(dirname(__FILE__,3)) . 'assets/images/download-icon.png'; ?>"
                                alt="Descargar"
                            >

                        </a>

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>

<?php

    return ob_get_clean();
}

add_shortcode('benditoai_modelos_ai','benditoai_modelos_ai_shortcode');