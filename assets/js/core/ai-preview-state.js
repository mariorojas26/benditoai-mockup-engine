(() => {
    if (!window.BenditoAIUX) {
        window.BenditoAIUX = {};
    }

    const namespace = window.BenditoAIUX;

    if (!namespace.escapeHtml) {
        namespace.escapeHtml = function escapeHtml(value) {
            return String(value)
                .replaceAll("&", "&amp;")
                .replaceAll("<", "&lt;")
                .replaceAll(">", "&gt;")
                .replaceAll("\"", "&quot;")
                .replaceAll("'", "&#39;");
        };
    }

    if (!namespace.getErrorMessage) {
        namespace.getErrorMessage = function getErrorMessage(payload, fallback = "Error inesperado.") {
            const data = payload?.data;

            if (typeof data === "string" && data.trim()) {
                return data.trim();
            }

            if (data && typeof data.message === "string" && data.message.trim()) {
                return data.message.trim();
            }

            if (typeof payload?.message === "string" && payload.message.trim()) {
                return payload.message.trim();
            }

            if (data && typeof data.error?.message === "string" && data.error.message.trim()) {
                return data.error.message.trim();
            }

            if (typeof payload?.error?.message === "string" && payload.error.message.trim()) {
                return payload.error.message.trim();
            }

            return fallback;
        };
    }

    if (!namespace.skeleton) {
        namespace.skeleton = {
            render(target, options = {}) {
                if (!target) return;

                const label = options.label || "Generando con IA...";
                const lines = Math.max(1, parseInt(options.lines, 10) || 2);
                const lineMarkup = Array.from({ length: lines }).map((_, index) => {
                    const cls = index === lines - 1 ? " benditoai-skeleton--short" : "";
                    return `<span class="benditoai-skeleton${cls}"></span>`;
                }).join("");

                target.innerHTML = `
<div class="benditoai-ai-state" aria-live="polite">
    <div class="benditoai-skeleton benditoai-skeleton--media"></div>
    <div class="benditoai-ai-state__lines">
        ${lineMarkup}
    </div>
    <p class="benditoai-ai-state__label">${label}</p>
</div>`;
            },
            clear(target) {
                if (!target) return;
                target.innerHTML = "";
            }
        };
    }

    function resolveStage(stage) {
        if (!stage) return null;
        if (typeof stage === "string") return document.querySelector(stage);
        return stage;
    }

    function ensurePlaceholder(stageEl) {
        let placeholder = stageEl.querySelector("[data-ai-preview-placeholder]");

        if (!placeholder) {
            placeholder = document.createElement("div");
            placeholder.className = "benditoai-ai-preview-placeholder";
            placeholder.setAttribute("data-ai-preview-placeholder", "1");
            placeholder.innerHTML = `
<i class="fa-regular fa-image"></i>
<span>Tu generación aparecerá aquí</span>`;
            stageEl.prepend(placeholder);
        }

        if (!placeholder.dataset.defaultHtml) {
            placeholder.dataset.defaultHtml = placeholder.innerHTML;
        }

        return placeholder;
    }

    function getRefs(stage) {
        const stageEl = resolveStage(stage);
        if (!stageEl) return null;

        const placeholder = ensurePlaceholder(stageEl);
        const wrapper = stageEl.querySelector(".benditoai-image-wrapper");
        const image = wrapper
            ? wrapper.querySelector(".benditoai-generated-image")
            : stageEl.querySelector(".benditoai-generated-image");

        return { stageEl, placeholder, wrapper, image };
    }

    namespace.preview = {
        reset(stage) {
            const refs = getRefs(stage);
            if (!refs) return;

            const { placeholder, wrapper, image } = refs;

            placeholder.classList.remove("is-loading", "is-error");
            placeholder.innerHTML = placeholder.dataset.defaultHtml || `
<i class="fa-regular fa-image"></i>
<span>Tu generación aparecerá aquí</span>`;
            placeholder.style.display = "flex";

            if (wrapper) wrapper.style.display = "none";
            if (image) image.src = "";
        },
        loading(stage, options = {}) {
            const refs = getRefs(stage);
            if (!refs) return;

            const { placeholder, wrapper, image } = refs;

            if (wrapper) wrapper.style.display = "none";
            if (image) image.src = "";

            placeholder.classList.remove("is-error");
            placeholder.classList.add("is-loading");
            placeholder.style.display = "flex";

            namespace.skeleton.render(placeholder, {
                label: options.label || "Generando con IA...",
                lines: options.lines || 2
            });
        },
        error(stage, options = {}) {
            const refs = getRefs(stage);
            if (!refs) return;

            const { placeholder, wrapper, image } = refs;

            const title = options.title || "No se pudo generar la imagen";
            const message = options.message || "Intenta nuevamente en unos segundos.";

            if (wrapper) wrapper.style.display = "none";
            if (image) image.src = "";

            placeholder.classList.remove("is-loading");
            placeholder.classList.add("is-error");
            placeholder.style.display = "flex";
            placeholder.innerHTML = `
<div class="benditoai-ai-state benditoai-ai-state--error" aria-live="assertive">
    <i class="fa-solid fa-circle-exclamation"></i>
    <p class="benditoai-ai-state__title">${namespace.escapeHtml(title)}</p>
    <p class="benditoai-ai-state__message">${namespace.escapeHtml(message)}</p>
</div>`;
        },
        image(stage, options = {}) {
            const refs = getRefs(stage);
            if (!refs) return;

            const { placeholder, wrapper, image } = refs;
            const url = options.imageUrl || "";
            if (!url) return;

            if (image) {
                image.src = url;
            }

            placeholder.classList.remove("is-loading", "is-error");
            placeholder.style.display = "none";

            if (wrapper) {
                wrapper.style.display = "block";
            }
        }
    };
})();
