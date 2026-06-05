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
        'eyebrow_1' => 'Paso 01',
        'title_1' => 'Disena tu modelo ideal',
        'summary_1' => 'Crea una identidad visual para tu marca.',
        'content_1' => 'Define el estilo, la actitud y la presencia de tu modelo para empezar a crear contenido con una base clara.',
        'image_1' => 'assets/images/modetest1.jpeg',
        'image_mobile_1' => 'assets/images/modetest1.jpeg',
        'alt_1' => 'Modelo AI creado para una marca',

        'eyebrow_2' => 'Paso 02',
        'title_2' => 'Vistelo con tu marca',
        'summary_2' => 'Convierte tus prendas en escenas listas para vender.',
        'content_2' => 'Prueba outfits, estilos y combinaciones sin sesiones largas ni producciones costosas.',
        'image_2' => 'assets/images/modeltest2.jpeg',
        'image_mobile_2' => 'assets/images/modeltest2.jpeg',
        'alt_2' => 'Modelo AI usando ropa de una marca',

        'eyebrow_3' => 'Paso 03',
        'title_3' => 'Lanzalo a una campaña',
        'summary_3' => 'Convierte tu modelo en piezas listas para vender.',
        'content_3' => '',
        'image_3' => 'assets/images/modeltestfull.jpeg',
        'image_mobile_3' => 'assets/images/modeltest3.jpeg',
        'alt_3' => 'Campana visual creada con BenditoAI',

        'scroll_vh' => 220,
        'scroll_vh_mobile' => 250,
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
        $fallback_mobile_image = $defaults['image_mobile_' . $i];

        $cards[] = array(
            'eyebrow' => (string) $atts['eyebrow_' . $i],
            'title' => (string) $atts['title_' . $i],
            'summary' => (string) $atts['summary_' . $i],
            'content' => (string) $atts['content_' . $i],
            'image' => function_exists('benditoai_get_image_asset')
                ? benditoai_get_image_asset($atts['image_' . $i], $fallback_image, array('allow_external' => false))
                : array('url' => benditoai_gsap_cards_asset_url($atts['image_' . $i], $fallback_image), 'webp_url' => '', 'width' => '', 'height' => ''),
            'image_mobile' => function_exists('benditoai_get_image_asset')
                ? benditoai_get_image_asset($atts['image_mobile_' . $i], $fallback_mobile_image, array('allow_external' => false))
                : array('url' => benditoai_gsap_cards_asset_url($atts['image_mobile_' . $i], $fallback_mobile_image), 'webp_url' => '', 'width' => '', 'height' => ''),
            'alt' => (string) $atts['alt_' . $i],
        );
    }

    $scroll_vh = max(190, (int) $atts['scroll_vh']);
    $scroll_vh_mobile = max(230, (int) $atts['scroll_vh_mobile']);
    $uid = function_exists('wp_unique_id') ? wp_unique_id('benditoai-gsap-cards-') : uniqid('benditoai-gsap-cards-', true);
    $classes = trim('benditoai-gsap-cards ' . $extra_classes);

    ob_start();
    ?>
    <section
        id="<?php echo esc_attr($uid); ?>"
        class="<?php echo esc_attr($classes); ?>"
        data-scroll-vh="<?php echo esc_attr($scroll_vh); ?>"
        data-scroll-vh-mobile="<?php echo esc_attr($scroll_vh_mobile); ?>"
        aria-label="Como funciona BenditoAI"
    >
        <div class="benditoai-gsap-cards__pin">
            <div class="benditoai-gsap-cards__stage" role="list" aria-label="Pasos principales">
                <?php foreach ($cards as $index => $card) : ?>
                    <article
                        class="benditoai-gsap-cards__scene<?php echo $index % 2 === 1 ? ' is-reversed' : ''; ?><?php echo $index === 0 ? ' is-active' : ''; ?>"
                        data-scene-index="<?php echo esc_attr($index); ?>"
                        role="listitem"
                        aria-current="<?php echo $index === 0 ? 'step' : 'false'; ?>"
                    >
                        <figure class="benditoai-gsap-cards__figure">
                            <picture>
                                <?php if (!empty($card['image_mobile']['webp_url'])) : ?>
                                    <source media="(max-width: 768px)" srcset="<?php echo esc_url($card['image_mobile']['webp_url']); ?>" type="image/webp" />
                                <?php endif; ?>
                                <?php if (!empty($card['image']['webp_url'])) : ?>
                                    <source srcset="<?php echo esc_url($card['image']['webp_url']); ?>" type="image/webp" />
                                <?php endif; ?>
                                <source media="(max-width: 768px)" srcset="<?php echo esc_url($card['image_mobile']['url']); ?>" />
                                <img
                                    src="<?php echo esc_url($card['image']['url']); ?>"
                                    alt="<?php echo esc_attr($card['alt']); ?>"
                                    loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
                                    decoding="async"
                                    <?php echo $index === 0 ? 'fetchpriority="high"' : ''; ?><?php echo function_exists('benditoai_image_dimension_attrs') ? benditoai_image_dimension_attrs($card['image']) : ''; ?>
                                />
                            </picture>
                        </figure>

                        <div class="benditoai-gsap-cards__copy">
                            <p class="benditoai-gsap-cards__eyebrow"><?php echo esc_html($card['eyebrow']); ?></p>
                            <h3 class="benditoai-gsap-cards__title"><?php echo esc_html($card['title']); ?></h3>
                            <?php if (trim($card['summary']) !== '') : ?>
                                <p class="benditoai-gsap-cards__summary"><?php echo esc_html($card['summary']); ?></p>
                            <?php endif; ?>
                            <?php if (trim($card['content']) !== '') : ?>
                                <p class="benditoai-gsap-cards__content"><?php echo esc_html($card['content']); ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php

    return ob_get_clean();
}

add_shortcode('benditoai_gsap_cards', 'benditoai_gsap_cards_shortcode');
