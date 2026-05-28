<?php
if (!defined('ABSPATH')) {
    exit;
}

function benditoai_gsap_cards_asset_url($src, $fallback) {
    $src = trim((string) $src);

    if ($src === '') {
        $src = $fallback;
    }

    if (filter_var($src, FILTER_VALIDATE_URL)) {
        $plugin_url = trailingslashit(BENDIDOAI_PLUGIN_URL);

        if (strpos($src, $plugin_url) === 0) {
            return esc_url($src);
        }

        $src = $fallback;
    }

    $relative_path = ltrim($src, '/');

    if (strpos($relative_path, 'assets/images/') !== 0) {
        $relative_path = ltrim($fallback, '/');
    }

    $absolute_path = BENDIDOAI_PLUGIN_PATH . $relative_path;

    if (!file_exists($absolute_path)) {
        $relative_path = ltrim($fallback, '/');
    }

    return esc_url(BENDIDOAI_PLUGIN_URL . $relative_path);
}

function benditoai_gsap_cards_shortcode($atts) {
    wp_enqueue_style('benditoai-gsap-cards');
    wp_enqueue_script('benditoai-gsap-cards');

    $defaults = array(
       'title_1' => 'Diseña tu modelo ideal',
'title_1' => 'Diseña tu modelo ideal',
'summary_1' => 'Crea personajes digitales que representen tu marca.',
'content_1' => 'Genera modelos únicos con la apariencia, estilo y vibra que quieras para tu contenido y campañas.',
'eyebrow_1' => 'Paso 01',
'image_1' => 'assets/images/1crea.png',
'alt_1' => 'Modelo AI creado para una marca',


'title_2' => 'Vistelo con tu marca',
'summary_2' => 'Ponle tus prendas, accesorios y estilo fácilmente.',
'content_2' => 'Crea outfits reutilizables, cambia ropa cuando quieras y prueba nuevas combinaciones en segundos con IA.',
'eyebrow_2' => 'Paso 02',
'image_2' => 'assets/images/crea2.png',
'alt_2' => 'Modelo AI usando ropa de una marca',


'title_3' => 'Haz que venda por ti',
'summary_3' => 'Crea contenido para redes, anuncios y campañas fácilmente.',
'content_3' => 'Genera imágenes con tu modelo para atraer clientes y hacer crecer tu marca.',
'eyebrow_3' => 'Paso 03',
'image_3' => 'assets/images/crea3.png',
'alt_3' => 'Campaña visual creada con BenditoAI',
        'scroll_vh' => 180,
        'scroll_vh_mobile' => 220,
        'class' => '',
    );

    $atts = shortcode_atts($defaults, $atts, 'benditoai_gsap_cards');

    $extra_classes_raw = trim((string) $atts['class']);
    $extra_classes = '';

    if ($extra_classes_raw !== '') {
        $class_parts = preg_split('/\s+/', $extra_classes_raw);
        $class_parts = array_filter(array_map('sanitize_html_class', $class_parts));
        $extra_classes = implode(' ', $class_parts);
    }

    $cards = array();

    for ($i = 1; $i <= 3; $i += 1) {
        $fallback_image = $defaults['image_' . $i];

        $cards[] = array(
            'title' => (string) $atts['title_' . $i],
            'summary' => (string) $atts['summary_' . $i],
            'content' => (string) $atts['content_' . $i],
            'eyebrow' => (string) $atts['eyebrow_' . $i],
            'image' => benditoai_gsap_cards_asset_url($atts['image_' . $i], $fallback_image),
            'alt' => (string) $atts['alt_' . $i],
        );
    }

    $scroll_vh = max(140, (int) $atts['scroll_vh']);
    $scroll_vh_mobile = max(220, (int) $atts['scroll_vh_mobile']);
    $uid = function_exists('wp_unique_id') ? wp_unique_id('benditoai-gsap-cards-') : uniqid('benditoai-gsap-cards-', true);
    $classes = trim('benditoai-gsap-cards ' . $extra_classes);

    ob_start();
    ?>
    <section
        id="<?php echo esc_attr($uid); ?>"
        class="<?php echo esc_attr($classes); ?>"
        data-scroll-vh="<?php echo esc_attr($scroll_vh); ?>"
        data-scroll-vh-mobile="<?php echo esc_attr($scroll_vh_mobile); ?>"
    >
        <div class="benditoai-gsap-cards__pin">
            <div class="benditoai-gsap-cards__layout">
                <div class="benditoai-gsap-cards__copy" role="list" aria-label="Flujo BenditoAI">
                    <?php foreach ($cards as $index => $card) : ?>
                        <article
                            class="benditoai-gsap-cards__card<?php echo $index === 0 ? ' is-active' : ''; ?>"
                            data-card-index="<?php echo esc_attr($index); ?>"
                            role="listitem"
                        >
                            <h3 class="benditoai-gsap-cards__title"><?php echo esc_html($card['title']); ?></h3>
                            <p class="benditoai-gsap-cards__summary"><?php echo esc_html($card['summary']); ?></p>
                            <div class="benditoai-gsap-cards__detail">
                                <p><?php echo esc_html($card['content']); ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="benditoai-gsap-cards__media" aria-live="polite">
                    <?php foreach ($cards as $index => $card) : ?>
                        <figure
                            class="benditoai-gsap-cards__image-panel<?php echo $index === 0 ? ' is-active' : ''; ?>"
                            data-image-index="<?php echo esc_attr($index); ?>"
                            aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>"
                        >
                            <img
                                src="<?php echo $card['image']; ?>"
                                alt="<?php echo esc_attr($card['alt']); ?>"
                                loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
                            />
                        </figure>
                    <?php endforeach; ?>
                </div>

                <div class="benditoai-gsap-cards__steps bai-gsap-stepper" aria-label="Progreso de 3 pasos" role="list">
                    <?php foreach ($cards as $index => $card) : ?>
                        <span
                            class="benditoai-gsap-cards__steps-tile bai-gsap-stepper__dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
                            role="listitem"
                            aria-label="<?php echo esc_attr('Paso ' . ($index + 1)); ?>"
                            aria-current="<?php echo $index === 0 ? 'step' : 'false'; ?>"
                        ></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php

    return ob_get_clean();
}

add_shortcode('benditoai_gsap_cards', 'benditoai_gsap_cards_shortcode');
