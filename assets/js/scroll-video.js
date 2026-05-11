(function () {
    const SELECTOR = ".benditoai-scroll-video";

    const instances = [];
    let rafId = 0;
    let resizeTimer = 0;
    let trackingUntil = 0;

    const mobileQuery = window.matchMedia("(max-width: 768px)");

    function clamp(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function mapRange(value, inMin, inMax, outMin, outMax) {
        if (inMax <= inMin) {
            return outMin;
        }

        const t = clamp((value - inMin) / (inMax - inMin), 0, 1);
        return outMin + (outMax - outMin) * t;
    }

    function getViewportHeight() {
        if (window.visualViewport && window.visualViewport.height) {
            return window.visualViewport.height;
        }

        return window.innerHeight || document.documentElement.clientHeight || 1;
    }

    function getScrollHeightVh(section) {
        const desktop = parseInt(section.dataset.scrollVh || "360", 10);
        const mobile = parseInt(section.dataset.scrollVhMobile || "420", 10);

        return mobileQuery.matches ? mobile : desktop;
    }

    function setSectionHeight(instance) {
        const viewportH = getViewportHeight();
        const scrollVh = getScrollHeightVh(instance.section);

        const targetHeight = Math.max(
            Math.round(viewportH * (scrollVh / 100)),
            Math.round(viewportH + 2)
        );

        instance.viewportH = viewportH;
        instance.section.style.height = targetHeight + "px";
        instance.section.style.setProperty("--bsv-vh", viewportH + "px");
    }

    function getProgress(instance) {
        const rect = instance.section.getBoundingClientRect();
        const viewportH = instance.viewportH || getViewportHeight();
        const travel = Math.max(instance.section.offsetHeight - viewportH, 1);

        return clamp((-rect.top) / travel, 0, 1);
    }

    function isVisible(instance) {
        const rect = instance.section.getBoundingClientRect();
        const viewportH = instance.viewportH || getViewportHeight();

        return rect.bottom > 0 && rect.top < viewportH;
    }

    function seekVideo(instance, now) {
        if (!instance.ready || !instance.visible || instance.duration <= 0) {
            return;
        }

        const video = instance.video;
        const playableDuration = Math.max(instance.duration - 0.08, 0);
        const targetTime = clamp(instance.progress * playableDuration, 0, playableDuration);

        const delta = Math.abs(video.currentTime - targetTime);
        const isMobile = mobileQuery.matches;

        const epsilon = isMobile ? 1 / 20 : 1 / 36;
        const minDelay = isMobile ? 22 : 16;

        if (delta < epsilon) {
            return;
        }

        if (video.seeking && now - instance.lastSeekAt < 45) {
            return;
        }

        if (now - instance.lastSeekAt < minDelay && delta < 0.09) {
            return;
        }

        try {
            if (typeof video.fastSeek === "function" && delta > 0.35) {
                video.fastSeek(targetTime);
            } else {
                video.currentTime = targetTime;
            }

            instance.lastSeekAt = now;
        } catch (error) {
            // Evita romper la página si el navegador bloquea un seek puntual.
        }
    }

    function updateProgress(instance, now) {
        instance.visible = isVisible(instance);
        instance.progress = getProgress(instance);

        const progress = instance.progress;

        // Cortina inicial más corta para que el video aparezca antes.
        const enterOpacity = mapRange(progress, 0, 0.06, 1, 0);

        // Cortina final más corta para no frenar la salida.
        const exitOpacity = mapRange(progress, 0.96, 1, 0, 1);

        instance.section.style.setProperty("--bsv-enter-opacity", enterOpacity.toFixed(4));
        instance.section.style.setProperty("--bsv-exit-opacity", exitOpacity.toFixed(4));

        seekVideo(instance, now);
    }

    function updateAll(now) {
        rafId = 0;
        const tickNow = now || performance.now();

        for (let i = 0; i < instances.length; i += 1) {
            updateProgress(instances[i], tickNow);
        }

        if (tickNow < trackingUntil) {
            requestUpdate();
        }
    }

    function requestUpdate() {
        if (!rafId) {
            rafId = requestAnimationFrame(updateAll);
        }
    }

    function bumpTracking(ms) {
        const now = performance.now();
        trackingUntil = Math.max(trackingUntil, now + ms);
        requestUpdate();
    }

    function refreshLayout() {
        for (let i = 0; i < instances.length; i += 1) {
            setSectionHeight(instances[i]);
        }

        bumpTracking(320);
    }

    function requestRefreshLayout() {
        clearTimeout(resizeTimer);

        resizeTimer = setTimeout(function () {
            refreshLayout();
        }, 120);
    }

    function initInstance(section) {
        if (section.dataset.bsvInitialized === "true") {
            return null;
        }

        const video = section.querySelector(".benditoai-scroll-video__media");

        if (!video) {
            return null;
        }

        section.dataset.bsvInitialized = "true";

        const instance = {
            section: section,
            video: video,
            ready: false,
            visible: false,
            duration: 0,
            progress: 0,
            viewportH: getViewportHeight(),
            lastSeekAt: 0,
        };

        video.muted = true;
        video.defaultMuted = true;
        video.playsInline = true;
        video.preload = "auto";
        video.pause();

        const onReady = function () {
            const duration = Number.isFinite(video.duration) ? video.duration : 0;

            if (duration > 0) {
                instance.duration = duration;
                instance.ready = true;

                video.pause();
                section.classList.add("is-ready");

                requestUpdate();
            }
        };

        video.addEventListener("loadedmetadata", onReady, { passive: true });
        video.addEventListener("loadeddata", onReady, { passive: true });
        video.addEventListener("canplay", onReady, { passive: true });

        video.load();

        setSectionHeight(instance);

        return instance;
    }

    function init() {
        const sections = document.querySelectorAll(SELECTOR);

        if (!sections.length) {
            return;
        }

        sections.forEach(function (section) {
            const instance = initInstance(section);

            if (instance) {
                instances.push(instance);
            }
        });

        if (!instances.length) {
            return;
        }

        window.addEventListener("scroll", function () {
            bumpTracking(260);
        }, { passive: true });

        window.addEventListener("touchstart", function () {
            bumpTracking(520);
        }, { passive: true });
        window.addEventListener("touchmove", function () {
            bumpTracking(300);
        }, { passive: true });
        window.addEventListener("touchend", function () {
            bumpTracking(420);
        }, { passive: true });

        window.addEventListener("wheel", function () {
            bumpTracking(240);
        }, { passive: true });

        window.addEventListener("resize", requestRefreshLayout, { passive: true });
        window.addEventListener("orientationchange", requestRefreshLayout, { passive: true });

        if (window.visualViewport) {
            window.visualViewport.addEventListener("resize", requestRefreshLayout, { passive: true });
            window.visualViewport.addEventListener("scroll", function () {
                bumpTracking(220);
            }, { passive: true });
        }

        refreshLayout();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
