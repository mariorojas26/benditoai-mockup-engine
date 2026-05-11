<?php
if (!defined('ABSPATH')) {
    exit;
}

function benditoai_cards_skills_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'ver_mas_url' => '/herramientas',
        ),
        $atts
    );

    $url = esc_url($atts['ver_mas_url']);
    $uid = function_exists('wp_unique_id') ? wp_unique_id('cards-skills-') : uniqid('cards-skills-', true);

    $skills_images_dir = BENDIDOAI_PLUGIN_PATH . 'assets/images/carrouselSkills/';
    $skills_images_url = BENDIDOAI_PLUGIN_URL . 'assets/images/carrouselSkills/';
    $skills_gallery = array();

    if (is_dir($skills_images_dir)) {
        $files = glob($skills_images_dir . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $skills_gallery[] = esc_url($skills_images_url . basename($file));
                }
            }
        }
    }

    shuffle($skills_gallery);

    $fallback_img = esc_url(BENDIDOAI_PLUGIN_URL . 'assets/images/crea3.png');
    if (empty($skills_gallery)) {
        $skills_gallery = array($fallback_img);
    }

    $herramientas = array(
        array(
            'slug' => 'mockups',
            'icono' => '<i class="fas fa-tshirt"></i>',
            'titulo' => 'Generador de Mockups',
            'descripcion' => 'Genera mockups de moda con IA usando prompts inteligentes. Resultado en segundos.',
            'imagen' => esc_url(BENDIDOAI_PLUGIN_URL . 'assets/images/crea3.png'),
        ),
        array(
            'slug' => 'modelos',
            'icono' => '<i class="fas fa-robot"></i>',
            'titulo' => 'Modelos IA',
            'descripcion' => 'Crea modelos únicos, cambia su estilo y genera imágenes listas para vender tus productos.',
            'imagen' => $skills_gallery[0],
        ),
        array(
            'slug' => 'remove-bg',
            'icono' => '<i class="fas fa-cut"></i>',
            'titulo' => 'Eliminar Fondo',
            'descripcion' => 'Elimina el fondo de cualquier imagen automaticamente con inteligencia artificial.',
            'imagen' => esc_url(BENDIDOAI_PLUGIN_URL . 'assets/images/vendemodelo.png'),
        ),
        array(
            'slug' => 'enhance',
            'icono' => '<i class="fas fa-magic"></i>',
            'titulo' => 'Mejorar Imagen',
            'descripcion' => 'Aumenta la calidad y resolucion de tus imagenes generadas con IA.',
            'imagen' => esc_url(BENDIDOAI_PLUGIN_URL . 'assets/images/vistemodelo.png'),
        ),
        array(
            'slug' => 'tendencias',
            'icono' => '<i class="fas fa-fire"></i>',
            'titulo' => 'Tendencias',
            'descripcion' => 'Descubre los estilos y prompts mas populares del momento en moda AI.',
            'imagen' => esc_url(BENDIDOAI_PLUGIN_URL . 'assets/images/creamodelo.png'),
        ),
        array(
            'slug' => 'historial',
            'icono' => '<i class="fas fa-history"></i>',
            'titulo' => 'Historial',
            'descripcion' => 'Accede a todos tus mockups y modelos generados anteriormente.',
            'imagen' => esc_url(BENDIDOAI_PLUGIN_URL . 'assets/images/antesba.png'),
        ),
    );

    ob_start();
    ?>
    <section id="<?php echo esc_attr($uid); ?>" class="cards-skills-tabs-wrapper" data-modelos-gallery='<?php echo esc_attr(wp_json_encode($skills_gallery)); ?>'>
        <div class="cards-skills-tabs-layout">
            <nav class="cards-skills-tabs-nav" role="tablist" aria-label="Herramientas BenditoAI">
                <?php foreach ($herramientas as $index => $h) : ?>
                    <?php $tab_id = $uid . '-tab-' . $h['slug']; ?>
                    <?php $panel_id = $uid . '-panel-' . $h['slug']; ?>
                    <button
                        type="button"
                        id="<?php echo esc_attr($tab_id); ?>"
                        class="cards-skills-tab<?php echo $index === 1 ? ' is-active' : ''; ?>"
                        role="tab"
                        aria-selected="<?php echo $index === 1 ? 'true' : 'false'; ?>"
                        aria-controls="<?php echo esc_attr($panel_id); ?>"
                        data-target="<?php echo esc_attr($panel_id); ?>"
                    >
                        <span class="cards-skills-tab-icon"><?php echo $h['icono']; ?></span>
                        <span class="cards-skills-tab-label"><?php echo esc_html($h['titulo']); ?></span>
                    </button>
                <?php endforeach; ?>
            </nav>

            <div class="cards-skills-panels">
                <?php foreach ($herramientas as $index => $h) : ?>
                    <?php $tab_id = $uid . '-tab-' . $h['slug']; ?>
                    <?php $panel_id = $uid . '-panel-' . $h['slug']; ?>
                    <article
                        id="<?php echo esc_attr($panel_id); ?>"
                        class="cards-skills-panel<?php echo $index === 1 ? ' is-active' : ''; ?>"
                        role="tabpanel"
                        aria-labelledby="<?php echo esc_attr($tab_id); ?>"
                        <?php echo $index === 1 ? '' : 'hidden'; ?>
                    >
                        <div class="cards-skills-panel-icon"><?php echo $h['icono']; ?></div>
                        <h3 class="cards-skills-panel-title"><?php echo esc_html($h['titulo']); ?></h3>
                        <p class="cards-skills-panel-description"><?php echo esc_html($h['descripcion']); ?></p>
                        <div class="cards-skills-panel-media">
                            <img
                                class="cards-skills-panel-image<?php echo $h['slug'] === 'modelos' ? ' is-modelos-image' : ''; ?>"
                                src="<?php echo esc_url($h['imagen']); ?>"
                                alt="<?php echo esc_attr($h['titulo']); ?>"
                                loading="lazy"
                            />
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="cards-skills-footer">
            <a href="<?php echo $url; ?>" class="cards-skills-cta">
                Ver todas las herramientas
                <i class="fas fa-long-arrow-alt-right"></i>
            </a>
        </div>
    </section>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const root = document.getElementById(<?php echo wp_json_encode($uid); ?>);
        if (!root) return;

        const tabs = Array.from(root.querySelectorAll(".cards-skills-tab"));
        const panels = Array.from(root.querySelectorAll(".cards-skills-panel"));
        const modelosImg = root.querySelector(".is-modelos-image");
        const modelosPanel = modelosImg ? modelosImg.closest(".cards-skills-panel") : null;
        const panelsWrap = root.querySelector(".cards-skills-panels");
        const isMobile = function () {
            return window.matchMedia("(max-width: 768px)").matches;
        };

        const centerMobilePanel = function (panel) {
            if (!panel || !isMobile()) return;

            const scrollPanel = function () {
                const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
                const rect = panel.getBoundingClientRect();
                const startY = window.pageYOffset || document.documentElement.scrollTop;
                const maxY = Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
                const targetY = Math.min(
                    maxY,
                    Math.max(0, startY + rect.top - ((window.innerHeight - rect.height) / 2))
                );

                if (reduceMotion) {
                    window.scrollTo(0, targetY);
                    return;
                }

                const duration = 150;
                const startedAt = window.performance.now();

                const tick = function (now) {
                    const progress = Math.min(1, (now - startedAt) / duration);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    window.scrollTo(0, startY + ((targetY - startY) * eased));

                    if (progress < 1) {
                        window.requestAnimationFrame(tick);
                    }
                };

                window.requestAnimationFrame(tick);
            };

            window.requestAnimationFrame(scrollPanel);

            const panelImage = panel.querySelector(".cards-skills-panel-image");
            if (panelImage && !panelImage.complete) {
                panelImage.addEventListener("load", function () {
                    window.requestAnimationFrame(scrollPanel);
                }, { once: true });
            }
        };

        const movePanelsForViewport = function () {
            if (!panelsWrap) return;

            if (isMobile()) {
                tabs.forEach(function (tab) {
                    const panelId = tab.getAttribute("data-target");
                    const panel = root.querySelector("#" + panelId);
                    if (panel && tab.nextElementSibling !== panel) {
                        tab.insertAdjacentElement("afterend", panel);
                    }
                });
            } else {
                panels.forEach(function (panel) {
                    if (panel.parentElement !== panelsWrap) {
                        panelsWrap.appendChild(panel);
                    }
                });
            }
        };

        const closeAllTabs = function () {
            tabs.forEach(function (btn) {
                btn.classList.remove("is-active");
                btn.setAttribute("aria-selected", "false");
            });

            panels.forEach(function (panel) {
                panel.classList.remove("is-active");
                panel.setAttribute("hidden", "hidden");
            });
        };

        let restartModelosGallery = function () {};

        const activateTab = function (tab) {
            const panelId = tab.getAttribute("data-target");
            const isAlreadyActive = tab.classList.contains("is-active");
            let activePanel = null;

            if (isMobile() && isAlreadyActive) {
                closeAllTabs();
                return;
            }

            tabs.forEach(function (btn) {
                const active = btn === tab;
                btn.classList.toggle("is-active", active);
                btn.setAttribute("aria-selected", active ? "true" : "false");
            });

            panels.forEach(function (panel) {
                const active = panel.id === panelId;
                panel.classList.toggle("is-active", active);
                if (active) {
                    panel.removeAttribute("hidden");
                    activePanel = panel;
                } else {
                    panel.setAttribute("hidden", "hidden");
                }
            });

            centerMobilePanel(activePanel);
            if (activePanel && modelosPanel && activePanel === modelosPanel) {
                restartModelosGallery();
            }
        };

        tabs.forEach(function (tab) {
            tab.addEventListener("click", function () {
                activateTab(tab);
            });
        });

        panels.forEach(function (panel) {
            panel.addEventListener("click", function (event) {
                if (!isMobile() || !panel.classList.contains("is-active")) return;
                if (event.target.closest("a, button, input, select, textarea")) return;

                closeAllTabs();
            });
        });

        movePanelsForViewport();
        window.addEventListener("resize", function () {
            movePanelsForViewport();
        }, { passive: true });

        const galleryRaw = root.getAttribute("data-modelos-gallery");
        let gallery = [];

        try {
            gallery = JSON.parse(galleryRaw || "[]");
        } catch (error) {
            gallery = [];
        }

        if (modelosImg && Array.isArray(gallery) && gallery.length > 1) {
            let currentSrc = modelosImg.getAttribute("src");
            let timerId = 0;

            const pickNext = function () {
                const filtered = gallery.filter(function (img) {
                    return img && img !== currentSrc;
                });
                const pool = filtered.length ? filtered : gallery;
                const index = Math.floor(Math.random() * pool.length);
                return pool[index];
            };

            const swapImage = function () {
                const nextSrc = pickNext();
                if (!nextSrc) return;

                modelosImg.classList.add("is-fading");
                window.setTimeout(function () {
                    modelosImg.setAttribute("src", nextSrc);
                    currentSrc = nextSrc;
                    modelosImg.classList.remove("is-fading");
                }, 260);
            };

            const startGallery = function () {
                if (!timerId) {
                    timerId = window.setInterval(swapImage, 3200);
                }
            };

            restartModelosGallery = startGallery;
            startGallery();
        }
    });
    </script>
    <?php

    return ob_get_clean();
}

add_shortcode('benditoai_cards_skills', 'benditoai_cards_skills_shortcode');
