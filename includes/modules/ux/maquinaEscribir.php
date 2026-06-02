<?php
if (!function_exists('maquina_texto_asset_url')) {
    function maquina_texto_asset_url($src, $fallback = 'assets/images/1crea.png') {
        $src = trim((string) $src);

        if ($src === '') {
            $src = $fallback;
        }

        if (filter_var($src, FILTER_VALIDATE_URL)) {
            return esc_url($src);
        }

        $relative_path = ltrim($src, '/');

        if (strpos($relative_path, 'assets/images/') !== 0) {
            $relative_path = ltrim($fallback, '/');
        }

        $plugin_root = trailingslashit(dirname(__FILE__, 4));
        $plugin_file = $plugin_root . 'bendidoai-mockup-engine.php';

        if (!file_exists($plugin_root . $relative_path)) {
            $relative_path = ltrim($fallback, '/');
        }

        return esc_url(plugins_url($relative_path, $plugin_file));
    }
}

function maquina_texto_shortcode($atts) {
    if (!wp_script_is('benditoai-gsap', 'registered')) {
        wp_register_script(
            'benditoai-gsap',
            'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js',
            array(),
            '3.13.0',
            true
        );
    }

    if (!wp_script_is('benditoai-gsap-scrolltrigger', 'registered')) {
        wp_register_script(
            'benditoai-gsap-scrolltrigger',
            'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js',
            array('benditoai-gsap'),
            '3.13.0',
            true
        );
    }

    $script_path = trailingslashit(dirname(__FILE__, 4)) . 'assets/js/home/maquina-hero.js';
    $script_url = plugins_url('assets/js/home/maquina-hero.js', trailingslashit(dirname(__FILE__, 4)) . 'bendidoai-mockup-engine.php');
    $script_version = file_exists($script_path) ? (string) filemtime($script_path) : '1.0';

    wp_enqueue_script(
        'benditoai-maquina-hero',
        $script_url,
        array('benditoai-gsap', 'benditoai-gsap-scrolltrigger'),
        $script_version,
        true
    );

    $atts = shortcode_atts(array(
        'titulo' => 'Genera campañas',
        'card_1_image' => 'assets/images/Home/cardshero1.jpg',
        'card_1_label' => 'Modelo',
        'card_2_image' => 'assets/images/Home/cardshero2.jpg',
        'card_2_label' => 'Outfit',
        'card_3_image' => 'assets/images/Home/cardshero3.jpg',
        'card_3_label' => 'Campaña',
        'card_4_image' => 'assets/images/Home/cardshero4.jpg',
        'card_4_label' => 'Estilo',
    ), $atts, 'maquina_texto');

    $frases = array(
        'con IA',
        'en segundos',
        'sin ser diseñador',
        'para tu marca',
    );

    $cards = array();

    for ($i = 1; $i <= 4; $i++) {
        $cards[] = array(
            'image' => maquina_texto_asset_url($atts['card_' . $i . '_image']),
            'label' => $atts['card_' . $i . '_label'],
            'index' => $i,
        );
    }

    $frases_json = wp_json_encode($frases);
    $titulo = esc_html($atts['titulo']);

    ob_start();
    ?>
    <div class="maquina-hero">
        <div class="maquina-float-cards" aria-hidden="true">
            <?php foreach ($cards as $card) : ?>
                <figure class="maquina-float-card maquina-float-card--<?php echo esc_attr($card['index']); ?>" data-maquina-float-card>
                    <span class="maquina-float-card__hover">
                        <span class="maquina-float-card__body">
                            <span class="maquina-float-card__media">
                                <img src="<?php echo esc_url($card['image']); ?>" alt="" loading="lazy" decoding="async">
                            </span>
                        </span>
                    </span>
                </figure>
            <?php endforeach; ?>
        </div>

        <div class="maquina-hero-inner">

            <h1 class="maquina-titulo">
                <?php echo $titulo; ?><br>
                <span id="maquina-texto" style="color:#7C3AFF !important;"></span>
            </h1>

            <p class="maquina-subtitulo">
                Crea modelos - vístelos con tu marca - ponlos a vender.
            </p>

            <div class="maquina-btns">
                <a href="/mockup" class="maquina-btn maquina-btn--primary">
                    Crear mi primer avatar <i class="fas fa-long-arrow-alt-right"></i>
                </a>
                <a href="/demo" class="maquina-btn maquina-btn--ghost">
                    Ver demo <i class="fas fa-play"></i>
                </a>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const frases = <?php echo $frases_json; ?>;
        const elemento = document.getElementById("maquina-texto");

        if (!elemento || !frases.length) {
            return;
        }

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
    <?php
    return ob_get_clean();
}
add_shortcode('maquina_texto', 'maquina_texto_shortcode');
