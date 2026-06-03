(function () {
    const SELECTOR = ".benditoai-gsap-cards";
    const MOBILE_QUERY = window.matchMedia("(max-width: 768px)");
    const REDUCED_MOTION_QUERY = window.matchMedia("(prefers-reduced-motion: reduce)");
    const LAST_HOLD_UNITS = 0.45;

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function smoothstep(value) {
        const x = clamp(value, 0, 1);
        return x * x * (3 - 2 * x);
    }

    function getViewportHeight() {
        if (window.visualViewport && window.visualViewport.height) {
            return window.visualViewport.height;
        }

        return window.innerHeight || document.documentElement.clientHeight || 1;
    }

    function getVisualHeight() {
        return Math.max(1, getViewportHeight());
    }

    function getScrollDistance(section) {
        const desktopVh = parseInt(section.dataset.scrollVh || "260", 10);
        const mobileVh = parseInt(section.dataset.scrollVhMobile || "310", 10);
        const selectedVh = MOBILE_QUERY.matches ? mobileVh : desktopVh;

        return Math.round(getViewportHeight() * (Math.max(selectedVh, 190) / 100));
    }

    function setSectionHeight(section) {
        const visualHeight = getVisualHeight();

        section.style.setProperty("--bai-gsap-visual-height", visualHeight + "px");
        section.style.setProperty("--bai-gsap-scroll-height", (visualHeight + getScrollDistance(section)) + "px");
    }

    function getStage(progress, totalItems) {
        if (totalItems <= 1) {
            return 0;
        }

        const travelUnits = (totalItems - 1) + LAST_HOLD_UNITS;
        return Math.min(totalItems - 1, clamp(progress, 0, 1) * travelUnits);
    }

    function setActive(section, scenes, activeIndex) {
        if (section.dataset.activeIndex === String(activeIndex)) {
            return;
        }

        section.dataset.activeIndex = String(activeIndex);

        scenes.forEach(function (scene, index) {
            const active = index === activeIndex;
            scene.classList.toggle("is-active", active);
            scene.setAttribute("aria-current", active ? "step" : "false");
        });
    }

    function initStatic(section) {
        section.classList.add("is-static");
        section.querySelectorAll(".benditoai-gsap-cards__scene").forEach(function (scene) {
            scene.classList.add("is-active");
            scene.removeAttribute("aria-current");
        });
    }

    function renderScene(section, scenes, progress, gsap) {
        const stage = getStage(progress, scenes.length);
        const activeIndex = clamp(Math.round(stage), 0, scenes.length - 1);
        const viewportHeight = getViewportHeight();
        const travelDistance = MOBILE_QUERY.matches ? viewportHeight * 0.72 : viewportHeight * 0.78;

        setActive(section, scenes, activeIndex);

        scenes.forEach(function (scene, index) {
            const offset = index - stage;
            const distance = Math.abs(offset);
            const visible = distance < 0.82 || index === activeIndex;
            const centered = smoothstep(1 - clamp(distance / 0.48, 0, 1));
            const imageOpacity = visible ? smoothstep(1 - clamp(distance / 0.76, 0, 1)) : 0;
            const textOpacity = smoothstep(centered);
            const imageY = -offset * travelDistance;
            const imageScale = 1 - Math.min(distance, 1) * 0.08;
            const imageBlur = Math.min(distance, 1) * 5;
            const isReversed = scene.classList.contains("is-reversed");
            const copy = scene.querySelector(".benditoai-gsap-cards__copy");
            const figure = scene.querySelector(".benditoai-gsap-cards__figure");

            gsap.set(scene, {
                autoAlpha: visible ? 1 : 0,
                pointerEvents: visible ? "auto" : "none",
                overwrite: true,
            });

            if (figure) {
                gsap.set(figure, {
                    autoAlpha: imageOpacity,
                    y: imageY,
                    scale: imageScale,
                    filter: "blur(" + imageBlur.toFixed(2) + "px)",
                    force3D: true,
                    overwrite: true,
                });
            }

            if (copy) {
                gsap.set(copy, {
                    autoAlpha: textOpacity,
                    x: MOBILE_QUERY.matches ? 0 : (isReversed ? -34 : 34) * (1 - textOpacity),
                    y: MOBILE_QUERY.matches ? 18 - textOpacity * 18 : 0,
                    filter: "blur(" + ((1 - textOpacity) * 5).toFixed(2) + "px)",
                    force3D: true,
                    overwrite: true,
                });
            }
        });
    }

    function initInstance(section) {
        if (section.dataset.gsapCardsInitialized === "true") {
            return;
        }

        section.dataset.gsapCardsInitialized = "true";

        const scenes = Array.from(section.querySelectorAll(".benditoai-gsap-cards__scene"));

        if (!scenes.length) {
            return;
        }

        if (REDUCED_MOTION_QUERY.matches || !window.gsap || !window.ScrollTrigger) {
            initStatic(section);
            return;
        }

        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;
        const pin = section.querySelector(".benditoai-gsap-cards__pin") || section;

        gsap.registerPlugin(ScrollTrigger);
        section.classList.add("has-gsap");
        setSectionHeight(section);
        renderScene(section, scenes, 0, gsap);

        const timeline = gsap.timeline({
            defaults: {
                ease: "none",
            },
            onUpdate: function () {
                renderScene(section, scenes, this.progress(), gsap);
            },
            scrollTrigger: {
                trigger: section,
                start: "top top",
                end: function () {
                    return "+=" + getScrollDistance(section);
                },
                pin: pin,
                pinSpacing: true,
                scrub: 0.62,
                anticipatePin: 1,
                invalidateOnRefresh: true,
                refreshPriority: 1,
                onRefreshInit: function () {
                    setSectionHeight(section);
                },
                onRefresh: function (self) {
                    renderScene(section, scenes, self.progress, gsap);
                },
            },
        });

        timeline.to({}, {
            duration: 1,
        });
    }

    function init() {
        const sections = document.querySelectorAll(SELECTOR);

        if (!sections.length) {
            return;
        }

        sections.forEach(initInstance);

        window.addEventListener("resize", function () {
            sections.forEach(setSectionHeight);

            if (window.ScrollTrigger) {
                window.ScrollTrigger.refresh();
            }
        }, { passive: true });

        if (window.visualViewport) {
            window.visualViewport.addEventListener("resize", function () {
                sections.forEach(setSectionHeight);

                if (window.ScrollTrigger) {
                    window.ScrollTrigger.refresh();
                }
            }, { passive: true });
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
