<?php
if (!defined('ABSPATH')) {
    exit;
}

function benditoai_cards_skills_shortcode($atts) {
    wp_enqueue_script('benditoai-gsap-scrolltrigger');

    $uid = function_exists('wp_unique_id') ? wp_unique_id('cards-skills-scroll-') : uniqid('cards-skills-scroll-', true);

    $scenes = array(
        array(
            'title' => 'Crea tu modelo',
            'image' => 'assets/images/monster.jpeg',
            'accent' => '#9b5cff',
        ),
        array(
            'title' => 'Vistelo con tu marca',
            'image' => 'assets/images/monster2.jpeg',
            'accent' => '#16d9ff',
        ),
        array(
            'title' => 'Lanzalo a una campana',
            'image' => 'assets/images/monster3.jpeg',
            'accent' => '#50ff91',
        ),
    );

    foreach ($scenes as $scene_index => $scene) {
        $asset = function_exists('benditoai_get_image_asset')
            ? benditoai_get_image_asset($scene['image'], 'assets/images/monster.jpeg', array('allow_external' => false))
            : array(
                'url' => BENDIDOAI_PLUGIN_URL . 'assets/images/monster.jpeg',
                'webp_url' => '',
            );

        $scenes[$scene_index]['asset'] = $asset;
        $scenes[$scene_index]['background_url'] = !empty($asset['webp_url']) ? $asset['webp_url'] : $asset['url'];
    }

    ob_start();
    ?>
    <section
        id="<?php echo esc_attr($uid); ?>"
        class="cards-skills-scroll-wrapper"
        data-scroll-vh="300"
        data-scroll-vh-mobile="330"
        aria-label="Herramientas BenditoAI"
    >
        <div class="cards-skills-scroll-pin">
            <div class="cards-skills-scroll-visual">
                <div class="cards-skills-scroll-bg-stack" aria-hidden="true">
                    <?php foreach ($scenes as $index => $scene) : ?>
                        <span
                            class="cards-skills-scroll-bg<?php echo $index === 0 ? ' is-active' : ''; ?>"
                            data-bg-index="<?php echo esc_attr($index); ?>"
                            style="--cards-skills-bg-image: url('<?php echo esc_url($scene['background_url']); ?>');"
                        ></span>
                    <?php endforeach; ?>
                </div>

                <div class="cards-skills-scroll-inner">
                    <div class="cards-skills-scroll-stage" role="list" aria-live="polite">
                        <?php foreach ($scenes as $index => $scene) : ?>
                            <article
                                class="cards-skills-scroll-phrase<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                data-phrase-index="<?php echo esc_attr($index); ?>"
                                data-accent="<?php echo esc_attr($scene['accent']); ?>"
                                role="listitem"
                                aria-current="<?php echo $index === 0 ? 'step' : 'false'; ?>"
                            >
                                <h2 class="cards-skills-scroll-title"><?php echo esc_html($scene['title']); ?></h2>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="cards-skills-scroll-steps bai-gsap-stepper" aria-label="Progreso" role="list">
                        <?php foreach ($scenes as $index => $scene) : ?>
                            <button
                                type="button"
                                class="cards-skills-scroll-step bai-gsap-stepper__dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                data-step-index="<?php echo esc_attr($index); ?>"
                                aria-label="<?php echo esc_attr('Escena ' . ($index + 1)); ?>"
                                aria-current="<?php echo $index === 0 ? 'step' : 'false'; ?>"
                            ></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const root = document.getElementById(<?php echo wp_json_encode($uid); ?>);
        if (!root || root.dataset.cardsSkillsScrollInitialized === "true") return;

        root.dataset.cardsSkillsScrollInitialized = "true";

        const phrases = Array.from(root.querySelectorAll(".cards-skills-scroll-phrase"));
        const steps = Array.from(root.querySelectorAll(".cards-skills-scroll-step"));
        const backgrounds = Array.from(root.querySelectorAll(".cards-skills-scroll-bg"));
        const visual = root.querySelector(".cards-skills-scroll-visual");
        const pin = root.querySelector(".cards-skills-scroll-pin") || root;
        const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        const mobileQuery = window.matchMedia("(max-width: 768px)");

        if (!phrases.length) return;

        const clamp = function (value, min, max) {
            return Math.min(max, Math.max(min, value));
        };

        const smoothstep = function (value) {
            const x = clamp(value, 0, 1);
            return x * x * (3 - 2 * x);
        };

        const buildLiquidClip = function (phase, progress) {
            const points = [];
            const steps = 32;
            const baseY = 12 + ((1 - progress) * 2);
            const amplitude = 2.6 + (progress * 1.2);

            for (let index = 0; index <= steps; index += 1) {
                const x = (index / steps) * 100;
                const waveA = Math.sin((index / steps) * Math.PI * 2 + phase);
                const waveB = Math.sin((index / steps) * Math.PI * 4 + phase * 0.62) * 0.28;
                const y = baseY + ((waveA + waveB) * amplitude);

                points.push(x.toFixed(2) + "% " + clamp(y, 8, 18).toFixed(2) + "%");
            }

            points.push("100% 100%", "0% 100%");

            return "polygon(" + points.join(", ") + ")";
        };

        const INTRO_PROGRESS = 0.18;
        const OUTRO_PROGRESS = 0.18;

        const getViewportHeight = function () {
            if (window.visualViewport && window.visualViewport.height) {
                return window.visualViewport.height;
            }

            return window.innerHeight || document.documentElement.clientHeight || 1;
        };

        const getOuterSpace = function () {
            const rawValue = window.getComputedStyle(root).getPropertyValue("--cards-skills-outer-space");
            const parsedValue = parseFloat(rawValue);

            return Number.isFinite(parsedValue) ? parsedValue : 30;
        };

        const getHeaderClearance = function () {
            if (!document.body.classList.contains("home")) {
                return 0;
            }

            const header = document.getElementById("masthead");
            const fallback = mobileQuery.matches ? 96 : 132;

            if (!header) {
                return fallback;
            }

            const rect = header.getBoundingClientRect();
            const bottom = Math.ceil(rect.bottom || 0);
            const breathingRoom = mobileQuery.matches ? 8 : 10;

            return Math.max(0, bottom + breathingRoom, fallback);
        };

        const getPinStartOffset = function () {
            return Math.max(getOuterSpace(), getHeaderClearance());
        };

        const getVisualHeight = function () {
            return Math.max(1, getViewportHeight() - getPinStartOffset() - getOuterSpace());
        };

        const getScrollDistance = function () {
            const desktopVh = parseInt(root.dataset.scrollVh || "300", 10);
            const mobileVh = parseInt(root.dataset.scrollVhMobile || "330", 10);
            const selectedVh = mobileQuery.matches ? mobileVh : desktopVh;

            return Math.round(getViewportHeight() * (Math.max(selectedVh, 220) / 100));
        };

        const getFullScale = function () {
            const rect = root.getBoundingClientRect();
            const viewportWidth = window.innerWidth || document.documentElement.clientWidth || rect.width || 1;
            const viewportHeight = getViewportHeight();
            const shellWidth = Math.max(1, rect.width || viewportWidth);
            const visualHeight = Math.max(1, getVisualHeight());
            const edgeBleed = mobileQuery.matches ? 1.02 : 1.015;
            const widthScale = (viewportWidth / shellWidth) * edgeBleed;
            const heightScale = (viewportHeight / visualHeight) * edgeBleed;

            return Math.max(1.16, widthScale, heightScale);
        };

        const getFullYOffset = function () {
            const topBleed = getPinStartOffset();
            const yOffset = mobileQuery.matches ? topBleed * 0.28 : topBleed * 0.35;

            return -Math.max(mobileQuery.matches ? 16 : 24, yOffset);
        };

        const setSectionHeight = function () {
            const headerClearance = getHeaderClearance();
            const visualHeight = getVisualHeight();

            root.style.setProperty("--cards-skills-header-clearance", headerClearance + "px");
            root.style.setProperty("--cards-skills-visual-height", visualHeight + "px");
            root.style.setProperty("--cards-skills-scroll-height", (visualHeight + getScrollDistance()) + "px");
        };

        const getCssPixelValue = function (propertyName) {
            const parsedValue = parseFloat(window.getComputedStyle(root).getPropertyValue(propertyName));

            return Number.isFinite(parsedValue) ? parsedValue : 0;
        };

        const setAccent = function (activeIndex) {
            const phrase = phrases[activeIndex];
            const accent = phrase ? phrase.dataset.accent || "#9b5cff" : "#9b5cff";

            root.style.setProperty("--cards-skills-active-accent", accent);
            document.body.style.setProperty("--cards-skills-active-accent", accent);
        };

        const setMonsterMode = function (enabled, progress, phase) {
            const menuProgress = enabled ? clamp(progress || 0, 0, 1) : 0;
            const menuInverseProgress = 1 - menuProgress;
            const wavePhase = Number.isFinite(phase) ? phase : menuProgress * Math.PI * 4;
            const liquidX = Math.sin(wavePhase) * (5 + (menuProgress * 6));
            const liquidRotate = Math.sin(wavePhase + 1.15) * (1.35 + (menuProgress * 1.2));
            const liquidScaleX = 1.04 + (Math.cos(wavePhase * 0.72) * 0.035);
            const liquidScaleY = 1 + (Math.sin(wavePhase * 0.82) * 0.018);
            const liquidGlowAX = 24 + (Math.sin(wavePhase * 0.9) * 16);
            const liquidGlowBX = 74 + (Math.cos(wavePhase * 0.8) * 14);
            const liquidGlowY = 9 + (Math.sin(wavePhase * 0.65) * 3);
            const liquidClip = buildLiquidClip(wavePhase, menuProgress);

            if (enabled) {
                document.body.style.setProperty("--cards-skills-menu-progress", menuProgress.toFixed(4));
                document.body.style.setProperty("--cards-skills-menu-progress-pct", (menuProgress * 100).toFixed(2) + "%");
                document.body.style.setProperty("--cards-skills-menu-progress-inverse-pct", (menuInverseProgress * 100).toFixed(2) + "%");
                document.body.style.setProperty("--cards-skills-menu-liquid-y", (102 - (menuProgress * 112)).toFixed(2) + "%");
                document.body.style.setProperty("--cards-skills-menu-liquid-x", liquidX.toFixed(2) + "%");
                document.body.style.setProperty("--cards-skills-menu-liquid-rotate", liquidRotate.toFixed(2) + "deg");
                document.body.style.setProperty("--cards-skills-menu-liquid-scale-x", liquidScaleX.toFixed(3));
                document.body.style.setProperty("--cards-skills-menu-liquid-scale-y", liquidScaleY.toFixed(3));
                document.body.style.setProperty("--cards-skills-menu-liquid-glow-a-x", liquidGlowAX.toFixed(2) + "%");
                document.body.style.setProperty("--cards-skills-menu-liquid-glow-b-x", liquidGlowBX.toFixed(2) + "%");
                document.body.style.setProperty("--cards-skills-menu-liquid-glow-y", liquidGlowY.toFixed(2) + "%");
                document.body.style.setProperty("--cards-skills-menu-liquid-clip", liquidClip);
                document.body.style.setProperty("--cards-skills-menu-shadow-alpha", (menuProgress * 0.32).toFixed(3));
                document.body.style.setProperty("--cards-skills-menu-hover-shadow-alpha", (menuProgress * 0.42).toFixed(3));
                document.body.style.setProperty("--cards-skills-menu-inset-alpha", (menuProgress * 0.18).toFixed(3));
                document.body.style.setProperty("--cards-skills-menu-hover-inset-alpha", (menuProgress * 0.2).toFixed(3));
                document.body.style.setProperty("--cards-skills-menu-underline-shadow-alpha", (menuProgress * 0.72).toFixed(3));
                document.body.style.setProperty("--cards-skills-menu-accent", "#50ff91");
            }

            document.body.classList.toggle("benditoai-monster-pin-active", enabled);

            if (!enabled) {
                document.body.style.removeProperty("--cards-skills-menu-progress");
                document.body.style.removeProperty("--cards-skills-menu-progress-pct");
                document.body.style.removeProperty("--cards-skills-menu-progress-inverse-pct");
                document.body.style.removeProperty("--cards-skills-menu-liquid-y");
                document.body.style.removeProperty("--cards-skills-menu-liquid-x");
                document.body.style.removeProperty("--cards-skills-menu-liquid-rotate");
                document.body.style.removeProperty("--cards-skills-menu-liquid-scale-x");
                document.body.style.removeProperty("--cards-skills-menu-liquid-scale-y");
                document.body.style.removeProperty("--cards-skills-menu-liquid-glow-a-x");
                document.body.style.removeProperty("--cards-skills-menu-liquid-glow-b-x");
                document.body.style.removeProperty("--cards-skills-menu-liquid-glow-y");
                document.body.style.removeProperty("--cards-skills-menu-liquid-clip");
                document.body.style.removeProperty("--cards-skills-menu-shadow-alpha");
                document.body.style.removeProperty("--cards-skills-menu-hover-shadow-alpha");
                document.body.style.removeProperty("--cards-skills-menu-inset-alpha");
                document.body.style.removeProperty("--cards-skills-menu-hover-inset-alpha");
                document.body.style.removeProperty("--cards-skills-menu-underline-shadow-alpha");
                document.body.style.removeProperty("--cards-skills-menu-accent");
                document.body.style.removeProperty("--cards-skills-active-accent");
            }
        };

        const setActive = function (activeIndex) {
            setAccent(activeIndex);

            phrases.forEach(function (phrase, index) {
                const active = index === activeIndex;
                phrase.classList.toggle("is-active", active);
                phrase.setAttribute("aria-current", active ? "step" : "false");
            });

            steps.forEach(function (step, index) {
                const active = index === activeIndex;
                step.classList.toggle("is-active", active);
                step.setAttribute("aria-current", active ? "step" : "false");
            });
        };

        const getSceneProgress = function (progress) {
            return clamp((progress - INTRO_PROGRESS) / (1 - INTRO_PROGRESS - OUTRO_PROGRESS), 0, 1);
        };

        const getProgressForStep = function (index) {
            if (phrases.length <= 1) return INTRO_PROGRESS;

            return clamp(
                INTRO_PROGRESS + ((1 - INTRO_PROGRESS - OUTRO_PROGRESS) * (index / (phrases.length - 1))),
                0,
                1
            );
        };

        const renderScene = function (progress) {
            const clampedProgress = clamp(progress, 0, 1);
            const sceneProgress = getSceneProgress(clampedProgress);
            const scenePosition = phrases.length <= 1 ? 0 : sceneProgress * (phrases.length - 1);
            const activeIndex = clamp(Math.round(scenePosition), 0, phrases.length - 1);
            const growProgress = smoothstep(clampedProgress / INTRO_PROGRESS);
            const shrinkGuard = smoothstep((1 - clampedProgress) / OUTRO_PROGRESS);
            const visualProgress = Math.min(growProgress, shrinkGuard);
            const startScale = mobileQuery.matches ? 0.82 : 0.78;
            const fullScale = getFullScale();
            const visualScale = startScale + ((fullScale - startScale) * visualProgress);
            const visualY = getFullYOffset() * visualProgress;
            const visualRadius = 18 - (visualProgress * 18);
            const textVisibility = smoothstep((visualProgress - 0.68) / 0.26);
            const frameProgress = smoothstep((visualProgress - 0.03) / 0.28);
            const frameTop = getCssPixelValue("--cards-skills-frame-top");
            const frameBottom = getCssPixelValue("--cards-skills-frame-bottom");
            const liveFrameTop = visualProgress > 0.4 ? 0 : frameTop * (1 - frameProgress);
            const liveFrameBottom = visualProgress > 0.4 ? 0 : frameBottom * (1 - frameProgress);
            const visualHeight = getVisualHeight();
            const initialStageTop = Math.max(1, visualHeight - frameBottom);
            const fullStageTop = visualHeight * 0.56;
            const stageTop = initialStageTop + ((fullStageTop - initialStageTop) * frameProgress);
            const menuProgress = clampedProgress < 0.96 ? smoothstep((visualProgress - 0.32) / 0.5) : 0;
            const menuWavePhase = (clampedProgress * Math.PI * 7) + (scenePosition * 0.85);

            root.style.setProperty("--cards-skills-stage-progress", clampedProgress.toFixed(4));
            root.style.setProperty("--cards-skills-visual-progress", visualProgress.toFixed(4));
            root.style.setProperty("--cards-skills-text-visibility", textVisibility.toFixed(4));
            root.style.setProperty("--cards-skills-frame-live-top", liveFrameTop.toFixed(2) + "px");
            root.style.setProperty("--cards-skills-frame-live-bottom", liveFrameBottom.toFixed(2) + "px");
            root.style.setProperty("--cards-skills-frame-progress", frameProgress.toFixed(4));
            root.style.setProperty("--cards-skills-stage-top", stageTop.toFixed(2) + "px");
            setActive(activeIndex);
            setMonsterMode(menuProgress > 0.001, menuProgress, menuWavePhase);

            if (visual && window.gsap) {
                window.gsap.set(visual, {
                    y: visualY,
                    scale: visualScale,
                    borderRadius: visualRadius + "px",
                    force3D: true,
                    overwrite: true,
                });
            } else if (visual) {
                visual.style.transform = "translate3d(0, " + visualY.toFixed(2) + "px, 0) scale(" + visualScale.toFixed(4) + ")";
                visual.style.borderRadius = visualRadius.toFixed(2) + "px";
            }

            steps.forEach(function (step, index) {
                const stepProgress = getProgressForStep(index);
                const dotProgress = clamp(1 - Math.abs(stepProgress - clampedProgress) * phrases.length, 0, 1);
                step.style.setProperty("--bai-gsap-dot-progress", dotProgress.toFixed(4));
            });

            phrases.forEach(function (phrase, index) {
                const distance = Math.abs(index - scenePosition);
                const sceneOpacity = smoothstep(1 - (distance / 0.68));
                const opacity = sceneOpacity * textVisibility;
                const y = clamp((index - scenePosition) * 34, -42, 42) + ((1 - textVisibility) * 18);
                const scale = 0.94 + (opacity * 0.06);
                const blur = (1 - opacity) * 7;

                if (window.gsap) {
                    window.gsap.set(phrase, {
                        autoAlpha: opacity,
                        y: y,
                        scale: scale,
                        filter: "blur(" + blur.toFixed(2) + "px)",
                        force3D: true,
                        overwrite: true,
                    });
                } else {
                    phrase.style.opacity = opacity.toFixed(3);
                    phrase.style.visibility = opacity > 0 ? "visible" : "hidden";
                    phrase.style.transform = "translate3d(0, " + y.toFixed(2) + "px, 0) scale(" + scale.toFixed(4) + ")";
                    phrase.style.filter = "blur(" + blur.toFixed(2) + "px)";
                }
            });

            backgrounds.forEach(function (background, index) {
                const distance = Math.abs(index - scenePosition);
                const opacity = smoothstep(1 - (distance / 0.74));

                background.classList.toggle("is-active", index === activeIndex);

                if (window.gsap) {
                    window.gsap.set(background, {
                        autoAlpha: opacity,
                        scale: 1,
                        force3D: true,
                        overwrite: true,
                    });
                } else {
                    background.style.opacity = opacity.toFixed(3);
                    background.style.visibility = opacity > 0 ? "visible" : "hidden";
                    background.style.transform = "translateZ(0) scale(1)";
                }
            });
        };

        const initStatic = function () {
            root.classList.add("is-static");
            setMonsterMode(false);
            phrases.forEach(function (phrase, index) {
                phrase.classList.toggle("is-active", index === 0);
                phrase.removeAttribute("aria-current");
            });
        };

        if (reduceMotion || !window.gsap || !window.ScrollTrigger) {
            initStatic();
            return;
        }

        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;

        gsap.registerPlugin(ScrollTrigger);
        root.classList.add("has-gsap");
        setSectionHeight();
        renderScene(0);

        let isStepScrolling = false;
        let stepScrollTween = null;

        const timeline = gsap.timeline({
            defaults: {
                ease: "none",
            },
            onUpdate: function () {
                if (isStepScrolling) return;
                renderScene(this.progress());
            },
            scrollTrigger: {
                trigger: root,
                start: function () {
                    return "top " + getPinStartOffset() + "px";
                },
                end: function () {
                    return "+=" + getScrollDistance();
                },
                pin: pin,
                pinSpacing: true,
                scrub: 0.34,
                anticipatePin: 1,
                invalidateOnRefresh: true,
                onRefreshInit: setSectionHeight,
                onRefresh: function (self) {
                    renderScene(self.progress);
                },
                onEnter: function () {
                    setMonsterMode(true, 0, 0);
                },
                onEnterBack: function () {
                    setMonsterMode(true, 0, 0);
                },
                onLeave: function () {
                    setMonsterMode(false);
                    renderScene(1);
                },
                onLeaveBack: function () {
                    setMonsterMode(false);
                    renderScene(0);
                },
            },
        });

        timeline.to({}, {
            duration: 1,
        });

        steps.forEach(function (step, index) {
            step.addEventListener("click", function () {
                if (!timeline.scrollTrigger || phrases.length <= 1) return;

                const targetProgress = getProgressForStep(index);
                const scroller = document.scrollingElement || document.documentElement;
                const scrollTrigger = timeline.scrollTrigger;
                const currentScroll = scroller.scrollTop || window.pageYOffset || 0;
                const scrollRange = Math.max(1, scrollTrigger.end - scrollTrigger.start);
                const currentProgress = clamp((currentScroll - scrollTrigger.start) / scrollRange, 0, 1);
                const targetScroll = scrollTrigger.start + scrollRange * targetProgress;
                const tweenState = {
                    progress: currentProgress,
                    scroll: currentScroll,
                };
                const tweenDuration = clamp(Math.abs(targetProgress - currentProgress) * 0.34, 0.16, 0.32);

                if (stepScrollTween) {
                    stepScrollTween.kill();
                }

                isStepScrolling = true;
                setActive(index);

                stepScrollTween = gsap.to(tweenState, {
                    progress: targetProgress,
                    scroll: targetScroll,
                    duration: tweenDuration,
                    ease: "power2.out",
                    overwrite: true,
                    onUpdate: function () {
                        scroller.scrollTop = tweenState.scroll;
                        timeline.progress(tweenState.progress, true);
                        renderScene(tweenState.progress);
                        ScrollTrigger.update();
                    },
                    onComplete: function () {
                        scroller.scrollTop = targetScroll;
                        timeline.progress(targetProgress, true);
                        renderScene(targetProgress);
                        ScrollTrigger.update();
                        isStepScrolling = false;
                        stepScrollTween = null;
                    },
                });
            });
        });

        window.addEventListener("resize", function () {
            setSectionHeight();
            ScrollTrigger.refresh();
        }, { passive: true });

        if (window.visualViewport) {
            window.visualViewport.addEventListener("resize", function () {
                setSectionHeight();
                ScrollTrigger.refresh();
            }, { passive: true });
        }
    });
    </script>
    <?php

    return ob_get_clean();
}

add_shortcode('benditoai_cards_skills', 'benditoai_cards_skills_shortcode');
