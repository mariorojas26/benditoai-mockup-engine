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
    $before = (strpos($atts['before'], 'http') === 0)
        ? $atts['before']
        : BENDIDOAI_PLUGIN_URL . 'assets/images/' . $atts['before'];

    $after = (strpos($atts['after'], 'http') === 0)
        ? $atts['after']
        : BENDIDOAI_PLUGIN_URL . 'assets/images/' . $atts['after'];

    $before_mobile = !empty($atts['before_mobile'])
        ? BENDIDOAI_PLUGIN_URL . 'assets/images/' . $atts['before_mobile']
        : '';

    $after_mobile = !empty($atts['after_mobile'])
        ? BENDIDOAI_PLUGIN_URL . 'assets/images/' . $atts['after_mobile']
        : '';

    ob_start();
?>

<div id="<?php echo $unique_id; ?>" class="benditoai-ba-wrapper">

    <!-- BEFORE -->
    <picture>
        <?php if ($before_mobile): ?>
            <source media="(max-width: 768px)" srcset="<?php echo esc_url($before_mobile); ?>">
        <?php endif; ?>
        <img src="<?php echo esc_url($before); ?>" class="benditoai-ba-img">
    </picture>

    <!-- AFTER -->
    <div class="benditoai-ba-overlay">
        <picture>
            <?php if ($after_mobile): ?>
                <source media="(max-width: 768px)" srcset="<?php echo esc_url($after_mobile); ?>">
            <?php endif; ?>
            <img src="<?php echo esc_url($after); ?>" class="benditoai-ba-img-after">
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