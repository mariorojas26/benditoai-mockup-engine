<?php
function maquina_texto_shortcode($atts) {
    $atts = shortcode_atts([
        'titulo' => 'Genera campañas',
    ], $atts);

    $frases = [
        "con Inteligencia Artificial",
        "en segundos",
        "sin ser diseñador",
        "para tu marca",
    ];

    $frases_json = json_encode($frases);
    $titulo      = esc_html($atts['titulo']);

    $output = <<<HTML
    <div class="maquina-hero">

        <div class="maquina-badge">
            ✨ Powered by Gemini Nano Banana Pro
        </div>

        <h1 class="maquina-titulo">
            {$titulo}<br>
            <span id="maquina-texto" style="color:#7C3AFF !important;"></span><span class="maquina-cursor" style="color:transparent !important;">|</span>
        </h1>

        <p class="maquina-subtitulo">
           Crea modelos, vístelos con tu marca y ponlos a vender.
        </p>

        <div class="maquina-btns">
            <a href="/mockup" class="maquina-btn maquina-btn--primary">
                Crear mi primer avatar <i class="fas fa-long-arrow-alt-right"></i>
            </a>
            <a href="/demo" class="maquina-btn maquina-btn--ghost">
                Ver demo   <i class="fas fa-play"></i>
            </a>
        </div>

    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const frases   = {$frases_json};
        const elemento = document.getElementById("maquina-texto");
        let i = 0, j = 0, borrando = false, texto = "";
        const vel = 80;

        function escribir() {
            if (!borrando && j < frases[i].length) {
                texto += frases[i][j++];
                elemento.textContent = texto;
                setTimeout(escribir, vel);
            } else if (!borrando && j === frases[i].length) {
                borrando = true;
                setTimeout(escribir, 1800);
            } else if (borrando && j > 0) {
                texto = texto.slice(0, -1);
                elemento.textContent = texto;
                j--;
                setTimeout(escribir, vel / 2);
            } else {
                borrando = false;
                i = (i + 1) % frases.length;
                setTimeout(escribir, 400);
            }
        }

        escribir();
    });
    </script>
HTML;

    return $output;
}
add_shortcode('maquina_texto', 'maquina_texto_shortcode');