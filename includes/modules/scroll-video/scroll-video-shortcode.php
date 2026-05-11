<?php
if (!defined('ABSPATH')) {
    exit;
}

function benditoai_scroll_video_shortcode($atts) {
    wp_enqueue_script('benditoai-scroll-video');

    $atts = shortcode_atts(
        array(
            'src' => 'assets/images/senora2.mp4',

            // Menor recorrido = experiencia más fluida.
            // Si quieres más lento, sube estos valores.
            'scroll_vh' => 360,
            'scroll_vh_mobile' => 420,

            'class' => '',
        ),
        $atts,
        'benditoai_scroll_video'
    );

    $video_src = trim((string) $atts['src']);

    $extra_classes_raw = trim((string) $atts['class']);
    $extra_classes = '';

    if ($extra_classes_raw !== '') {
        $class_parts = preg_split('/\s+/', $extra_classes_raw);
        $class_parts = array_filter(array_map('sanitize_html_class', $class_parts));
        $extra_classes = implode(' ', $class_parts);
    }

    $scroll_vh = max(240, (int) $atts['scroll_vh']);
    $scroll_vh_mobile = max(280, (int) $atts['scroll_vh_mobile']);

    if (filter_var($video_src, FILTER_VALIDATE_URL)) {
        $video_src_url = esc_url($video_src);
    } else {
        $video_path = ltrim($video_src, '/');
        $absolute_path = BENDIDOAI_PLUGIN_PATH . $video_path;

        if (file_exists($absolute_path)) {
            $video_src_url = esc_url(BENDIDOAI_PLUGIN_URL . $video_path);
        } else {
            $video_src_url = esc_url(BENDIDOAI_PLUGIN_URL . 'assets/images/senora.mp4');
        }
    }

    $wrapper_id = 'benditoai-scroll-video-' . wp_unique_id();
    $classes = trim('benditoai-scroll-video ' . $extra_classes);

    ob_start();
    ?>
    <section
        id="<?php echo esc_attr($wrapper_id); ?>"
        class="<?php echo esc_attr($classes); ?>"
        data-scroll-vh="<?php echo esc_attr($scroll_vh); ?>"
        data-scroll-vh-mobile="<?php echo esc_attr($scroll_vh_mobile); ?>"
    >
        <div class="benditoai-scroll-video__sticky">
            <div class="benditoai-scroll-video__viewport">
                <video
                    class="benditoai-scroll-video__media"
                    muted
                    playsinline
                    preload="auto"
                    webkit-playsinline
                    disablepictureinpicture
                    aria-hidden="true"
                >
                    <source src="<?php echo $video_src_url; ?>" type="video/mp4">
                </video>

                <div class="benditoai-scroll-video__curtain-enter" aria-hidden="true"></div>
                <div class="benditoai-scroll-video__curtain-exit" aria-hidden="true"></div>
                <div class="benditoai-scroll-video__vignette" aria-hidden="true"></div>
            </div>
        </div>
    </section>
    <?php

    return ob_get_clean();
}

add_shortcode('benditoai_scroll_video', 'benditoai_scroll_video_shortcode');