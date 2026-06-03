<?php
if (!defined('ABSPATH')) {
    exit;
}

function benditoai_cards_skills_shortcode($atts) {
    wp_enqueue_script('benditoai-gsap-scrolltrigger');

    $uid = function_exists('wp_unique_id') ? wp_unique_id('cards-skills-scroll-') : uniqid('cards-skills-scroll-', true);

    $frases = array(
        '1. Crea tu modelo, ',
        '2. Vistelo con tu marca',
        '3. Vende con el',
        '¿Que esperas para crear?',
    );

    ob_start();
    ?>
    <section
        id="<?php echo esc_attr($uid); ?>"
        class="cards-skills-scroll-wrapper"
        data-scroll-vh="150"
        data-scroll-vh-mobile="190"
        aria-label="Herramientas BenditoAI"
    >
        <div class="cards-skills-scroll-pin">
            <div class="cards-skills-scroll-inner">

                <div class="cards-skills-scroll-stage" role="list" aria-live="polite">
                    <?php foreach ($frases as $index => $frase) : ?>
                        <article
                            class="cards-skills-scroll-phrase<?php echo $index === 0 ? ' is-active' : ''; ?>"
                            data-phrase-index="<?php echo esc_attr($index); ?>"
                            role="listitem"
                            aria-current="<?php echo $index === 0 ? 'step' : 'false'; ?>"
                        >
                            <h2 class="cards-skills-scroll-title"><?php echo esc_html($frase); ?></h2>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="cards-skills-scroll-steps bai-gsap-stepper" aria-label="Progreso" role="list">
                    <?php foreach ($frases as $index => $frase) : ?>
                        <button
                            type="button"
                            class="cards-skills-scroll-step bai-gsap-stepper__dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
                            data-step-index="<?php echo esc_attr($index); ?>"
                            aria-label="<?php echo esc_attr('Frase ' . ($index + 1)); ?>"
                            aria-current="<?php echo $index === 0 ? 'step' : 'false'; ?>"
                        ></button>
                    <?php endforeach; ?>
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
        const stageEl = root.querySelector(".cards-skills-scroll-stage");
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

        const HOLD_UNITS = 0.24;
        const TRANSITION_UNITS = 0.58;

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

        const getVisualHeight = function () {
            return Math.max(1, getViewportHeight() - (getOuterSpace() * 2));
        };

        const getScrollDistance = function () {
            const desktopVh = parseInt(root.dataset.scrollVh || "220", 10);
            const mobileVh = parseInt(root.dataset.scrollVhMobile || "290", 10);
            const selectedVh = mobileQuery.matches ? mobileVh : desktopVh;

            return Math.round(getViewportHeight() * (Math.max(selectedVh, 130) / 100));
        };

        const setSectionHeight = function () {
            const visualHeight = getVisualHeight();

            root.style.setProperty("--cards-skills-visual-height", visualHeight + "px");
            root.style.setProperty("--cards-skills-scroll-height", (visualHeight + getScrollDistance()) + "px");
        };

        const setActive = function (activeIndex) {
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

        const getTotalUnits = function () {
            return (phrases.length * HOLD_UNITS) + ((phrases.length - 1) * TRANSITION_UNITS);
        };

        const getSceneState = function (progress) {
            const totalUnits = getTotalUnits();
            const virtualProgress = clamp(progress, 0, 1) * totalUnits;
            const phraseBlock = HOLD_UNITS + TRANSITION_UNITS;

            for (let index = 0; index < phrases.length; index += 1) {
                const holdStart = index * phraseBlock;
                const transitionStart = holdStart + HOLD_UNITS;
                const transitionEnd = transitionStart + TRANSITION_UNITS;
                const isLast = index === phrases.length - 1;

                if (isLast || virtualProgress < transitionStart) {
                    return {
                        currentIndex: index,
                        nextIndex: Math.min(index + 1, phrases.length - 1),
                        transitionProgress: 0,
                        rawTransitionProgress: 0,
                        totalUnits: totalUnits,
                    };
                }

                if (virtualProgress < transitionEnd) {
                    const rawTransitionProgress = (virtualProgress - transitionStart) / TRANSITION_UNITS;

                    return {
                        currentIndex: index,
                        nextIndex: Math.min(index + 1, phrases.length - 1),
                        transitionProgress: smoothstep(rawTransitionProgress),
                        rawTransitionProgress: rawTransitionProgress,
                        totalUnits: totalUnits,
                    };
                }
            }

            return {
                currentIndex: phrases.length - 1,
                nextIndex: phrases.length - 1,
                transitionProgress: 0,
                rawTransitionProgress: 0,
                totalUnits: totalUnits,
            };
        };

        const getProgressForStep = function (index) {
            const totalUnits = getTotalUnits();
            const phraseBlock = HOLD_UNITS + TRANSITION_UNITS;
            const targetUnits = Math.min(index * phraseBlock, totalUnits);

            return clamp(targetUnits / totalUnits, 0, 1);
        };

        const renderScene = function (progress) {
            const scene = getSceneState(progress);
            const currentIndex = scene.currentIndex;
            const nextIndex = scene.nextIndex;
            const transitionProgress = scene.transitionProgress;
            const activeIndex = transitionProgress > 0.58 ? nextIndex : currentIndex;
            const currentTitle = phrases[currentIndex] ? phrases[currentIndex].querySelector(".cards-skills-scroll-title") : null;
            const nextTitle = phrases[nextIndex] ? phrases[nextIndex].querySelector(".cards-skills-scroll-title") : null;
            const currentHeight = currentTitle ? currentTitle.getBoundingClientRect().height : 96;
            const nextHeight = nextTitle ? nextTitle.getBoundingClientRect().height : currentHeight;
            const stageHeight = (stageEl && stageEl.clientHeight) || 250;
            const travelDistance = Math.min(
                stageHeight * 0.42,
                Math.max(96, ((currentHeight + nextHeight) / 2) * 0.62)
            );

            root.style.setProperty("--cards-skills-stage-progress", clamp(progress, 0, 1).toFixed(4));
            setActive(activeIndex);

            steps.forEach(function (step, index) {
                const stepProgress = getProgressForStep(index);
                const dotProgress = clamp(1 - Math.abs(stepProgress - progress) * phrases.length, 0, 1);
                step.style.setProperty("--bai-gsap-dot-progress", dotProgress.toFixed(4));
            });

            phrases.forEach(function (phrase, index) {
                let opacity = 0;
                let y = travelDistance;
                let scale = 0.62;
                let blur = 8;

                if (index === currentIndex) {
                    opacity = 1 - smoothstep((transitionProgress - 0.5) / 0.5) * 0.88;
                    y = -transitionProgress * travelDistance;
                    scale = 1 - transitionProgress * 0.38;
                    blur = transitionProgress * 9;
                } else if (index === nextIndex && nextIndex !== currentIndex) {
                    opacity = smoothstep((transitionProgress - 0.08) / 0.68);
                    y = (1 - transitionProgress) * travelDistance;
                    scale = 0.62 + transitionProgress * 0.38;
                    blur = (1 - transitionProgress) * 9;
                }

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
        };

        const initStatic = function () {
            root.classList.add("is-static");
            phrases.forEach(function (phrase) {
                phrase.classList.add("is-active");
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
                    return "top " + getOuterSpace() + "px";
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
