<?php
function maquina_texto_shortcode($atts) {
    $frases = [
        "Crea con IA",
        "Mejora imagenes",
        "Genera campañas",
    ];

    $frases_json = json_encode($frases);

    $output = <<<HTML
    <span id="maquina-texto-wrapper">
        <span id="maquina-texto"></span>
    </span>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const frases = $frases_json;
        const elemento = document.getElementById("maquina-texto");
        let i = 0, j = 0, borrando = false, texto = "";
        let velocidad = 100;

        function escribir() {
            if (!borrando && j < frases[i].length) {
                texto += frases[i][j];
                elemento.textContent = texto;
                j++;
                setTimeout(escribir, velocidad);
            } else if (!borrando && j === frases[i].length) {
                borrando = true;
                setTimeout(escribir, 1500);
            } else if (borrando && j > 0) {
                texto = texto.slice(0, -1);
                elemento.textContent = texto;
                j--;
                setTimeout(escribir, velocidad / 2);
            } else if (borrando && j === 0) {
                borrando = false;
                i = (i + 1) % frases.length;
                setTimeout(escribir, 500);
            }
        }

        escribir();
    });
    </script>
HTML;

    return $output;
}
add_shortcode('maquina_texto', 'maquina_texto_shortcode');