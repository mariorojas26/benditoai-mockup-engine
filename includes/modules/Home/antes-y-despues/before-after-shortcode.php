<?php
if (!defined('ABSPATH')) exit;

function benditoai_before_after_shortcode($atts) {

    $atts = shortcode_atts(array(
        'before' => '',
        'after'  => '',
        'before_mobile' => '',
        'after_mobile'  => ''
    ), $atts);

    // 🔥 ID único por instancia
    $unique_id = 'benditoai_ba_' . uniqid();

    // rutas
    $before_source = (strpos($atts['before'], 'http') === 0) ? $atts['before'] : 'assets/images/' . ltrim($atts['before'], '/');
    $after_source = (strpos($atts['after'], 'http') === 0) ? $atts['after'] : 'assets/images/' . ltrim($atts['after'], '/');
    $before_mobile_source = !empty($atts['before_mobile']) ? 'assets/images/' . ltrim($atts['before_mobile'], '/') : '';
    $after_mobile_source = !empty($atts['after_mobile']) ? 'assets/images/' . ltrim($atts['after_mobile'], '/') : '';

    $before = function_exists('benditoai_get_image_asset')
        ? benditoai_get_image_asset($before_source, 'assets/images/antesba.png')
        : array('url' => BENDIDOAI_PLUGIN_URL . 'assets/images/' . $atts['before'], 'webp_url' => '', 'width' => '', 'height' => '');
    $after = function_exists('benditoai_get_image_asset')
        ? benditoai_get_image_asset($after_source, 'assets/images/despuesba.png')
        : array('url' => BENDIDOAI_PLUGIN_URL . 'assets/images/' . $atts['after'], 'webp_url' => '', 'width' => '', 'height' => '');
    $before_mobile = $before_mobile_source && function_exists('benditoai_get_image_asset')
        ? benditoai_get_image_asset($before_mobile_source, $before_source)
        : null;
    $after_mobile = $after_mobile_source && function_exists('benditoai_get_image_asset')
        ? benditoai_get_image_asset($after_mobile_source, $after_source)
        : null;

    ob_start();
?>

<div id="<?php echo $unique_id; ?>" class="benditoai-ba-wrapper">

    <!-- BEFORE -->
    <picture>
        <?php if (!empty($before_mobile['webp_url'])): ?>
            <source media="(max-width: 768px)" srcset="<?php echo esc_url($before_mobile['webp_url']); ?>" type="image/webp">
        <?php endif; ?>
        <?php if (!empty($before['webp_url'])): ?>
            <source srcset="<?php echo esc_url($before['webp_url']); ?>" type="image/webp">
        <?php endif; ?>
        <?php if (!empty($before_mobile['url'])): ?>
            <source media="(max-width: 768px)" srcset="<?php echo esc_url($before_mobile['url']); ?>">
        <?php endif; ?>
        <img src="<?php echo esc_url($before['url']); ?>" class="benditoai-ba-img" alt="" loading="lazy" decoding="async"<?php echo function_exists('benditoai_image_dimension_attrs') ? benditoai_image_dimension_attrs($before) : ''; ?>>
    </picture>

    <!-- AFTER -->
    <div class="benditoai-ba-overlay">
        <picture>
            <?php if (!empty($after_mobile['webp_url'])): ?>
                <source media="(max-width: 768px)" srcset="<?php echo esc_url($after_mobile['webp_url']); ?>" type="image/webp">
            <?php endif; ?>
            <?php if (!empty($after['webp_url'])): ?>
                <source srcset="<?php echo esc_url($after['webp_url']); ?>" type="image/webp">
            <?php endif; ?>
            <?php if (!empty($after_mobile['url'])): ?>
                <source media="(max-width: 768px)" srcset="<?php echo esc_url($after_mobile['url']); ?>">
            <?php endif; ?>
            <img src="<?php echo esc_url($after['url']); ?>" class="benditoai-ba-img-after" alt="" loading="lazy" decoding="async"<?php echo function_exists('benditoai_image_dimension_attrs') ? benditoai_image_dimension_attrs($after) : ''; ?>>
        </picture>
    </div>

    <div class="benditoai-ba-slider"></div>
    

</div>

<script>
(function() {

    const wrapper = document.getElementById('<?php echo $unique_id; ?>');
    if (!wrapper) return;

    const slider = wrapper.querySelector('.benditoai-ba-slider');
    const overlayImg = wrapper.querySelector('.benditoai-ba-img-after');

    let isDown = false;
    let autoPlayed_<?php echo $unique_id; ?> = false;

    const move = (x) => {
        const rect = wrapper.getBoundingClientRect();
        let pos = x - rect.left;

        pos = Math.max(0, Math.min(pos, rect.width));

        const percent = (pos / rect.width) * 100;

        overlayImg.style.clipPath = `inset(0 0 0 ${percent}%)`;
        slider.style.left = percent + '%';
    };

    wrapper.addEventListener('dragstart', e => e.preventDefault());
    wrapper.style.userSelect = 'none';

    // mouse
    slider.addEventListener('mousedown', () => isDown = true);
    window.addEventListener('mouseup', () => isDown = false);
    window.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        move(e.clientX);
    });

    // touch
    slider.addEventListener('touchstart', () => isDown = true);
    window.addEventListener('touchend', () => isDown = false);
    window.addEventListener('touchmove', (e) => {
        if (!isDown) return;
        move(e.touches[0].clientX);
    });

    // 🔥 observer único por instancia
    const observer_<?php echo $unique_id; ?> = new IntersectionObserver(entries => {
        entries.forEach(entry => {

            if (entry.isIntersecting && !autoPlayed_<?php echo $unique_id; ?>) {

                autoPlayed_<?php echo $unique_id; ?> = true;

                let start = 40;
                let mid = 60;
                let duration = 1200;

                const ease = (t) => {
                    return t < 0.5
                        ? 2 * t * t
                        : 1 - Math.pow(-2 * t + 2, 2) / 2;
                };

                let startTime = null;

                const go = (time) => {
                    if (!startTime) startTime = time;

                    let p = (time - startTime) / duration;
                    p = Math.min(p, 1);

                    let val = start + (mid - start) * ease(p);

                    const rect = wrapper.getBoundingClientRect();
                    move(rect.left + (val / 100) * rect.width);

                    if (p < 1) {
                        requestAnimationFrame(go);
                    } else {
                        back();
                    }
                };

                let backTime = null;

                const back = () => {
                    const animateBack = (time) => {
                        if (!backTime) backTime = time;

                        let p = (time - backTime) / duration;
                        p = Math.min(p, 1);

                        let val = mid - (mid - start) * ease(p);

                        const rect = wrapper.getBoundingClientRect();
                        move(rect.left + (val / 100) * rect.width);

                        if (p < 1) {
                            requestAnimationFrame(animateBack);
                        }
                    };

                    requestAnimationFrame(animateBack);
                };

                requestAnimationFrame(go);
            }

        });
    }, { threshold: 0.5 });

    observer_<?php echo $unique_id; ?>.observe(wrapper);

})();
</script>

<?php
    return ob_get_clean();
}

add_shortcode('benditoai_before_after', 'benditoai_before_after_shortcode');
