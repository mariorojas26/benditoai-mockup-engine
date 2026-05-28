(function () {
    const SELECTOR = ".benditoai-gsap-cards";
    const MOBILE_QUERY = window.matchMedia("(max-width: 768px)");
    const REDUCED_MOTION_QUERY = window.matchMedia("(prefers-reduced-motion: reduce)");
    const SLIDE_SPACING = 116;

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function getViewportHeight() {
        if (window.visualViewport && window.visualViewport.height) {
            return window.visualViewport.height;
        }

        return window.innerHeight || document.documentElement.clientHeight || 1;
    }

    function getScrollDistance(section) {
        const desktopVh = parseInt(section.dataset.scrollVh || "180", 10);
        const mobileVh = parseInt(section.dataset.scrollVhMobile || "220", 10);
        const selectedVh = MOBILE_QUERY.matches ? mobileVh : desktopVh;

        return Math.round(getViewportHeight() * (Math.max(selectedVh, 140) / 100));
    }

    function setSectionHeight(section) {
        section.style.setProperty("--bai-gsap-scroll-height", (getViewportHeight() + getScrollDistance(section)) + "px");
    }

    function getStage(progress, totalItems) {
        if (totalItems <= 1) {
            return 0;
        }

        return clamp(progress, 0, 1) * (totalItems - 1);
    }

    function getSceneMetrics(section) {
        const viewportH = getViewportHeight();
        const rect = section.getBoundingClientRect();
        const travel = Math.max(section.offsetHeight - viewportH, 1);
        const firstCardHold = Math.min(viewportH * 0.18, travel * 0.22);
        const animatedTravel = Math.max(travel - firstCardHold, 1);

        return {
            animatedTravel: animatedTravel,
            firstCardHold: firstCardHold,
            rect: rect,
        };
    }

    function getSceneProgress(section) {
        const metrics = getSceneMetrics(section);

        return clamp((-metrics.rect.top - metrics.firstCardHold) / metrics.animatedTravel, 0, 1);
    }

    function setActive(section, index) {
        const cards = section.querySelectorAll(".benditoai-gsap-cards__card");
        const panels = section.querySelectorAll(".benditoai-gsap-cards__image-panel");
        const tiles = section.querySelectorAll(".benditoai-gsap-cards__steps-tile");

        section.dataset.activeIndex = String(index);

        cards.forEach(function (card, cardIndex) {
            const isActive = cardIndex === index;
            card.classList.toggle("is-active", isActive);
            card.setAttribute("aria-current", isActive ? "step" : "false");
        });

        panels.forEach(function (panel, panelIndex) {
            const isActive = panelIndex === index;
            panel.classList.toggle("is-active", isActive);
            panel.setAttribute("aria-hidden", isActive ? "false" : "true");
        });

        tiles.forEach(function (tile, tileIndex) {
            tile.classList.toggle("is-active", tileIndex === index);
        });
    }

    function initStatic(section) {
        section.classList.add("is-static");
        const cards = section.querySelectorAll(".benditoai-gsap-cards__card");
        const panels = section.querySelectorAll(".benditoai-gsap-cards__image-panel");

        cards.forEach(function (card, index) {
            card.classList.add("is-active");
            card.removeAttribute("aria-current");

            const panel = panels[index];
            if (panel && !card.querySelector(".benditoai-gsap-cards__static-image")) {
                const image = panel.querySelector("img");
                const clone = image ? image.cloneNode(false) : null;

                if (clone) {
                    clone.className = "benditoai-gsap-cards__static-image";
                    clone.loading = "lazy";
                    card.appendChild(clone);
                }
            }
        });
    }

    function renderScene(section, cards, panels, panelImages, progress, gsap) {
        const stage = getStage(progress, panels.length);
        const activeIndex = clamp(Math.round(stage), 0, panels.length - 1);
        const stepProgress = panels.length > 1 ? clamp(stage / (panels.length - 1), 0, 1) : 0;

        section.style.setProperty("--bai-steps-progress", stepProgress.toFixed(4));
        setActive(section, activeIndex);

        panels.forEach(function (panel, index) {
            const offset = index - stage;
            const distance = Math.min(Math.abs(offset), 1);
            const visible = distance < 1.18 || index === activeIndex;
            const direction = clamp(offset, -1, 1);
            const leftAmount = clamp(-offset, 0, 1);
            const opacity = clamp(1 - distance * 0.46, 0, 1);
            const brightness = clamp(1 - distance * 0.34 - leftAmount * 0.26, 0.34, 1);
            const saturate = clamp(1.02 - distance * 0.18 - leftAmount * 0.12, 0.72, 1.02);
            const blur = distance * 6 + leftAmount * 4;
            const glassOpacity = clamp(distance * 0.62 + leftAmount * 0.28, 0, 0.9);

            panel.style.setProperty("--bai-glass-opacity", glassOpacity.toFixed(3));

            gsap.set(panel, {
                autoAlpha: visible ? opacity : 0,
                xPercent: direction * SLIDE_SPACING,
                yPercent: -50,
                scale: 1 - distance * 0.06,
                rotateY: direction * -4,
                rotateZ: -2 + direction * -0.45,
                filter: "brightness(" + brightness.toFixed(3) + ") saturate(" + saturate.toFixed(3) + ") blur(" + blur.toFixed(2) + "px)",
                overwrite: true,
            });
        });

        panelImages.forEach(function (image, index) {
            const offset = index - stage;
            const distance = Math.min(Math.abs(offset), 1);
            const direction = clamp(offset, -1, 1);

            if (!image) {
                return;
            }

            gsap.set(image, {
                xPercent: direction * 3,
                scale: 1.01 + distance * 0.035,
                overwrite: true,
            });
        });

        cards.forEach(function (card, index) {
            const offset = index - stage;
            const distance = Math.min(Math.abs(offset), 1);
            const direction = clamp(offset, -1, 1);
            const opacity = distance < 0.42 ? clamp(1 - distance / 0.42, 0, 1) : 0;

            gsap.set(card, {
                autoAlpha: opacity,
                x: direction * 18 * distance,
                overwrite: true,
            });
        });
    }

    function initInstance(section) {
        if (section.dataset.gsapCardsInitialized === "true") {
            return;
        }

        section.dataset.gsapCardsInitialized = "true";

        const cards = Array.from(section.querySelectorAll(".benditoai-gsap-cards__card"));
        const panels = Array.from(section.querySelectorAll(".benditoai-gsap-cards__image-panel"));

        if (!cards.length || cards.length !== panels.length) {
            return;
        }

        if (REDUCED_MOTION_QUERY.matches || !window.gsap || !window.ScrollTrigger) {
            initStatic(section);
            return;
        }

        const gsap = window.gsap;
        const ScrollTrigger = window.ScrollTrigger;
        const panelImages = panels.map(function (panel) {
            return panel.querySelector("img");
        });

        gsap.registerPlugin(ScrollTrigger);
        section.classList.add("has-gsap");
        setSectionHeight(section);
        renderScene(section, cards, panels, panelImages, 0, gsap);

        ScrollTrigger.create({
            trigger: section,
            start: "top top",
            end: "bottom bottom",
            scrub: 0.9,
            invalidateOnRefresh: true,
            onUpdate: function () {
                renderScene(section, cards, panels, panelImages, getSceneProgress(section), gsap);
            },
            onRefreshInit: function () {
                setSectionHeight(section);
            },
            onRefresh: function () {
                renderScene(section, cards, panels, panelImages, getSceneProgress(section), gsap);
            },
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
