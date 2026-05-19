<?php
if (!defined('ABSPATH')) exit;

function benditoai_plan_cards_shortcode($atts = array()) {
    $atts = shortcode_atts(
        array(
            'title' => 'Elige el plan que mejor se adapte a ti',
            'subtitle' => 'Desbloquea el poder creativo de BenditoAI con el plan ideal para tu ritmo.',
            'cta_url' => home_url('/planes/'),
            'cta_label' => 'Elegir plan',
            'price_period' => '/mes',
            'starter_price' => 'gratis',
            'pro_price' => '$50.000',
            'elite_price' => '$95.000',
        ),
        $atts,
        'benditoai_plan_cards'
    );

    $plans = function_exists('benditoai_get_plans') ? benditoai_get_plans() : array();
    if (empty($plans) || !is_array($plans)) {
        return '';
    }

    $order = array('starter', 'pro', 'elite');
    $copy_map = array(
        'starter' => array(
            'kicker' => 'Ideal para comenzar',
            'desc' => 'Perfecto para validar ideas y publicar tus primeras campanas.',
            'extra' => array(),
            'badge' => '',
            'icon' => 'fa-bolt',
            'price' => (string) $atts['starter_price'],
        ),
        'pro' => array(
            'kicker' => 'Para creadores exigentes',
            'desc' => 'Flujo profesional para crecer con consistencia y mas velocidad.',
            'extra' => array(),
            'badge' => 'Mas popular',
            'icon' => 'fa-star',
            'price' => (string) $atts['pro_price'],
        ),
        'elite' => array(
            'kicker' => 'Maximo poder creativo',
            'desc' => 'Capacidad avanzada para equipos y marcas con alta demanda.',
            'extra' => array(),
            'badge' => '',
            'icon' => 'fa-gem',
            'price' => (string) $atts['elite_price'],
        ),
    );

    $current_plan = '';
    if (is_user_logged_in() && function_exists('benditoai_get_user_plan_key')) {
        $current_plan = benditoai_get_user_plan_key(get_current_user_id(), false);
    }
    $back_fallback_url = home_url('/');

    ob_start();
    ?>
    <section class="benditoai-plan-showcase" data-benditoai-plan-showcase>
        <a href="<?php echo esc_url($back_fallback_url); ?>" class="benditoai-plan-close-btn" aria-label="Cerrar y volver al inicio">
            <span aria-hidden="true">&times;</span>
        </a>

        <header class="benditoai-plan-showcase__head">
            <div class="benditoai-plan-showcase__intro">
                <a href="<?php echo esc_url($back_fallback_url); ?>" class="benditoai-plan-showcase__intro-icon" aria-label="Volver al inicio">
                    <i class="fas fa-crown" aria-hidden="true"></i>
                </a>
                <div>
                    <h2><?php echo esc_html($atts['title']); ?></h2>
                    <p><?php echo esc_html($atts['subtitle']); ?></p>
                </div>
            </div>
            <a
                href="<?php echo esc_url($back_fallback_url); ?>"
                class="benditoai-plan-showcase__secure-logo"
                aria-label="Volver al inicio"
            >
                <img
                    src="<?php echo esc_url(BENDIDOAI_PLUGIN_URL . 'assets/images/icobenbla.png'); ?>"
                    alt="BenditoAI"
                    loading="lazy"
                />
            </a>
        </header>

        <div class="benditoai-plan-showcase__cards">
            <?php foreach ($order as $plan_key) :
                if (!isset($plans[$plan_key]) || !is_array($plans[$plan_key])) {
                    continue;
                }

                $plan = $plans[$plan_key];
                $name = isset($plan['name']) ? (string) $plan['name'] : ucfirst($plan_key);
                $tokens = isset($plan['tokens']) ? (int) $plan['tokens'] : 0;
                $max_modelos = isset($plan['max_modelos']) ? (int) $plan['max_modelos'] : 0;
                $max_outfits = isset($plan['max_outfits']) ? (int) $plan['max_outfits'] : 0;

                $copy = isset($copy_map[$plan_key]) ? $copy_map[$plan_key] : array();
                $kicker = isset($copy['kicker']) ? (string) $copy['kicker'] : '';
                $desc = isset($copy['desc']) ? (string) $copy['desc'] : '';
                $badge = isset($copy['badge']) ? (string) $copy['badge'] : '';
                $icon = isset($copy['icon']) ? (string) $copy['icon'] : 'fa-bolt';
                $price = isset($copy['price']) ? (string) $copy['price'] : '';
                $is_free_price = preg_match('/^\s*gratis\s*$/i', $price) === 1;
                $is_logged_in = is_user_logged_in();
                $is_current = $is_logged_in && ($current_plan === $plan_key);
                $is_featured = $plan_key === 'pro';
                $cta_href = (string) $atts['cta_url'];
                if (!$is_logged_in && $plan_key === 'starter') {
                    $cta_href = wp_login_url(home_url('/planes/'));
                }
                $cta_text = $is_current ? 'Tu plan actual' : (string) $atts['cta_label'];
                $current_plan_tooltip = 'Este es el plan que tienes activo actualmente con nosotros.';
                $feature_rows = array(
                    array(
                        'label' => sprintf('%s tokens mensuales', number_format_i18n($tokens)),
                        'enabled' => true,
                    ),
                    array(
                        'label' => sprintf('Hasta %s modelos AI', number_format_i18n($max_modelos)),
                        'enabled' => true,
                    ),
                    array(
                        'label' => sprintf('Hasta %s outfits por modelo', number_format_i18n($max_outfits)),
                        'enabled' => true,
                    ),
                    array(
                        'label' => 'Eliminacion de marca de agua',
                        'enabled' => $plan_key !== 'starter',
                    ),
                    array(
                        'label' => 'Soporte por correo',
                        'enabled' => $plan_key === 'elite',
                    ),
                    array(
                        'label' => 'Soporte prioritario 24/7',
                        'enabled' => $plan_key === 'elite',
                    ),
                );
                ?>
                <article class="benditoai-plan-card benditoai-plan-card--<?php echo esc_attr($plan_key); ?><?php echo $is_featured ? ' is-featured' : ''; ?>">
                    <?php if ($badge !== '') : ?>
                        <div class="benditoai-plan-card__popular"><?php echo esc_html($badge); ?></div>
                    <?php endif; ?>
                    <div class="benditoai-plan-card__inner">
                        <div class="benditoai-plan-card__plan-head">
                            <span class="benditoai-plan-card__icon" aria-hidden="true"><i class="fas <?php echo esc_attr($icon); ?>"></i></span>
                            <div>
                                <h3><?php echo esc_html($name); ?></h3>
                                <?php if ($kicker !== '') : ?>
                                    <p class="benditoai-plan-card__kicker"><?php echo esc_html($kicker); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($price !== '') : ?>
                            <p class="benditoai-plan-card__price-wrap">
                                <span class="benditoai-plan-card__price<?php echo $is_free_price ? ' is-free' : ''; ?>"><?php echo esc_html($price); ?></span>
                                <span class="benditoai-plan-card__price-period"><?php echo esc_html((string) $atts['price_period']); ?></span>
                            </p>
                        <?php endif; ?>

                        <?php if ($desc !== '') : ?>
                            <p class="benditoai-plan-card__desc"><?php echo esc_html($desc); ?></p>
                        <?php endif; ?>
                        <ul class="benditoai-plan-card__list">
                            <?php foreach ($feature_rows as $feature_item) : ?>
                                <li class="<?php echo !empty($feature_item['enabled']) ? 'is-available' : 'is-unavailable'; ?>">
                                    <?php echo esc_html((string) $feature_item['label']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <?php if ($is_current) : ?>
                            <span
                                class="benditoai-plan-card__cta is-current-plan"
                                tabindex="0"
                                role="note"
                                data-tooltip="<?php echo esc_attr($current_plan_tooltip); ?>"
                            >
                                <?php echo esc_html($cta_text); ?>
                            </span>
                        <?php else : ?>
                            <a class="benditoai-plan-card__cta" href="<?php echo esc_url($cta_href); ?>">
                                <?php echo esc_html($cta_text); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <section class="benditoai-plan-showcase__benefits" aria-label="Beneficios principales">
            <article class="benditoai-plan-benefit">
                <span class="benditoai-plan-benefit__icon" aria-hidden="true"><i class="fas fa-infinity"></i></span>
                <div>
                    <h4>Flujo creativo continuo</h4>
                    <p>Crea contenido para tus campanas con un proceso mas agil y ordenado.</p>
                </div>
            </article>
            <article class="benditoai-plan-benefit">
                <span class="benditoai-plan-benefit__icon" aria-hidden="true"><i class="fas fa-cloud-download-alt"></i></span>
                <div>
                    <h4>Exportacion en alta calidad</h4>
                    <p>Descarga tus resultados listos para publicar en redes, ecommerce o anuncios.</p>
                </div>
            </article>
            <article class="benditoai-plan-benefit">
                <span class="benditoai-plan-benefit__icon" aria-hidden="true"><i class="fas fa-lock"></i></span>
                <div>
                    <h4>Pago 100% seguro</h4>
                    <p>Tus datos y pagos se procesan con estandares de seguridad modernos.</p>
                </div>
            </article>
        </section>

        <p class="benditoai-plan-showcase__guarantee">
            <i class="fas fa-shield-alt" aria-hidden="true"></i>
            <span>Garantia de satisfaccion de 7 dias o te devolvemos tu dinero.</span>
        </p>
    </section>

    <a href="<?php echo esc_url($back_fallback_url); ?>" class="benditoai-plan-back-btn" data-benditoai-back-btn>
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        <span>Volver</span>
    </a>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        var backBtn = document.querySelector("[data-benditoai-back-btn]");
        if (!backBtn) return;

        backBtn.addEventListener("click", function (event) {
            if (window.history.length > 1) {
                event.preventDefault();
                window.history.back();
            }
        });
    });
    </script>
    <?php

    return ob_get_clean();
}

add_shortcode('benditoai_plan_cards', 'benditoai_plan_cards_shortcode');
