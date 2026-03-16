<?php

if (!defined('ABSPATH')) {
    exit;
}

/* =====================================================
   DASHBOARD SHORTCODE
===================================================== */

function benditoai_dashboard_shortcode() {

    if (!is_user_logged_in()) {
        return '<div class="benditoai-auth-message">Debes iniciar sesión para acceder.</div>';
    }

    $current_user = wp_get_current_user();

    ob_start();
    ?>

    <div class="benditoai-wrapper benditoai-dashboard">

        <div class="benditoai-dashboard__container">

            <header class="benditoai-dashboard__header">

                <h2 class="benditoai-dashboard__title">
                    ¿Listo para crear hoy <?php echo esc_html($current_user->display_name); ?>?
                </h2>

                <p class="benditoai-dashboard__welcome">
                   Transforma tus diseños en mockups listos para vender.
                </p>
            

            </header>

            <div class="benditoai-dashboard__actions">
                <a href="/crear-mockup" class="benditoai-btn benditoai-btn--primary">
                    🚀 Crear Mockup Ahora
                </a>
            </div>

        </div>

    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('benditoai_dashboard', 'benditoai_dashboard_shortcode');


/* =====================================================
   CREAR MOCKUP SHORTCODE
===================================================== */

function benditoai_crear_mockup_shortcode() {

    if (!is_user_logged_in()) {
        return '<div class="benditoai-auth-message">Debes iniciar sesión para crear un mockup.</div>';
    }

    ob_start();
    ?>

    <div class="benditoai-wrapper benditoai-mockup">

        <div class="benditoai-mockup__container">

        
            <form id="benditoai-form"
                  class="benditoai-form"
                  enctype="multipart/form-data">

                <div class="benditoai-form__group">

                   <header class="benditoai-mockup__header">
                <h2 class="benditoai-mockup__title">
                    🧠 Generador de Mockups con IA
                </h2>
            </header>

                    <label class="benditoai-form__label">
                        Selecciona el producto para tu mockup
                    </label>

                    <select name="producto"
                            class="benditoai-form__select"
                            required>

                        <option value="">-- Seleccionar --</option>
                        <option value="mug">Taza</option>
                        <option value="camiseta">Camiseta</option>
                        <option value="hoodie">Hoodie</option>

                    </select>

                </div>

                <!-- solo si selecciona camiseta -->
                <div class="benditoai-form__group benditoai-camiseta-estilos" style="display:none;">
                    <label class="benditoai-form__label">Selecciona el estilo de camiseta:</label>
                    <label>
                        <input type="radio" name="estilo_camiseta" value="Normal" checked> Normal
                    </label>
                    <label>
                        <input type="radio" name="estilo_camiseta" value="Oversize"> Oversize
                    </label>
                    <label>
                        <input type="radio" name="estilo_camiseta" value="croptop"> Crop Top
                    </label>
                </div>
                <!-- solo si selecciona camiseta -->

                <div class="benditoai-form__group">
                    <label class="benditoai-form__label">Color del producto:</label>
                    <select name="color" class="benditoai-form__select" required>
                        <option value="blanco">Blanco</option>
                        <option value="negro">Negro</option>
                        <option value="azul">Azul</option>
                        <option value="morado">Morado</option>
                    </select>
                </div>

                <!-- Entorno -->
                 <div class="benditoai-form__group">
                    <label class="benditoai-form__label">Selecciona el entorno:</label>
                    <select name="entorno" class="benditoai-form__select">
                        <option value="urbano">Urbano</option>
                        <option value="minimalista">Minimalista</option>
                        <option value="agresivo">Agresivo</option>
                        <option value="celestial">Celestial</option>
                    </select>
                </div>
                   <!-- Entorno -->

                <div class="benditoai-form__group">

                    <label class="benditoai-form__label">
                        Sube tu diseño
                    </label>

                    <input type="file"
                           name="diseno"
                           class="benditoai-form__input"
                           accept="image/*"
                           required>
                </div>

                <div class="benditoai-form__group">
                    <label class="benditoai-form__label" for="modelo">Usar modelo en el mockup:</label>
                    <select class="benditoai-form__select" name="modelo" id="modelo">
                        <option value="si" selected>Sí, modelo realista</option>
                        <option value="no">No, solo mockup sin humanos</option>
                    </select>
                </div>

                <label class="benditoai-form__label" >Formato de imagen:</label>

                <div class="benditoai-form__group">
                    <select name="formato" class="benditoai-form__select" required>
                        <option value="instagram">Instagram (4:5)</option>
                        <option value="cuadrado">Cuadrado (1:1)</option>
                    </select>
                </div>


                <div class="benditoai-form__actions">

                    <button type="submit"
                            class="benditoai-btn benditoai-btn--primary benditoai-form__submit">

                        🚀 Generar Mockup

                    </button>

                </div>

            </form>

            <div id="resultado-mockup"
                 class="benditoai-mockup__result">
            </div>



        </div>
                            <!-- ========================= HISTORIAL ========================= -->
        <div class="benditoai-dashboard__historial">
            <?php 
            // Llamamos directamente a la función del historial
            if (function_exists('benditoai_historial_shortcode')) {
                echo benditoai_historial_shortcode();
            }
            ?>
        </div>
        <!-- ============================================================ -->

    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('benditoai_crear_mockup', 'benditoai_crear_mockup_shortcode');

