document.addEventListener("DOMContentLoaded", function () {
    const root = document.querySelector(".benditoai-campaign-wizard");
    const form = document.getElementById("benditoai-form-campana-ai");

    if (!root || !form) return;

    const steps = Array.from(root.querySelectorAll(".baiw-step"));
    const indicators = Array.from(root.querySelectorAll("[data-step-indicator]"));
    const progress = document.getElementById("benditoai-campaign-progress");
    const createModelUrl = root.dataset.createModelUrl || "/crea-modelo/";
    const editModelUrl = root.dataset.editModelUrl || "/mis-modelos/";
    const flowInput = document.getElementById("benditoai-campaign-flow");
    const modelIdInput = document.getElementById("benditoai-campaign-model-id");
    const modelUrlInput = document.getElementById("benditoai-campaign-model-url");
    const outfitIdInput = document.getElementById("benditoai-campaign-outfit-id");
    const outfitTagInput = document.getElementById("benditoai-campaign-outfit-tag");

    let step = 0;
    let productImages = [];
    let selectedModel = null;
    let lastPayload = null;
    let lastResults = [];

    const formatMeta = {
        instagram: { id: "instagram", label: "Instagram", ratio: "1:1", size: "1080x1080", imageSize: "1K" },
        story: { id: "story", label: "Story", ratio: "9:16", size: "1080x1920", imageSize: "1K" },
        tiktok: { id: "tiktok", label: "TikTok", ratio: "9:16", size: "1080x1920", imageSize: "1K" },
        banner: { id: "banner", label: "Banner web", ratio: "16:9", size: "1920x1080", imageSize: "1K" },
        pinterest: { id: "pinterest", label: "Pinterest", ratio: "2:3", size: "1000x1500", imageSize: "1K" },
    };

    const $ = (selector) => root.querySelector(selector);
    const $$ = (selector) => Array.from(root.querySelectorAll(selector));

    const setHidden = (el, hidden) => {
        if (!el) return;
        el.hidden = hidden;
        el.setAttribute("aria-hidden", hidden ? "true" : "false");
    };

    const toast = (message) => {
        window.alert(message);
    };

    const escapeHtml = (value) => String(value || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

    const ratioToCss = (ratio) => {
        const parts = String(ratio || "1:1").split(":");
        if (parts.length !== 2) return "1 / 1";
        return `${Number(parts[0]) || 1} / ${Number(parts[1]) || 1}`;
    };

    const visibleStepTotal = () => flowInput.value === "use_model" ? 7 : 6;

    const visibleStepPosition = (targetStep) => {
        if (flowInput.value === "use_model") return targetStep;
        if (targetStep <= 1) return targetStep;
        if (targetStep >= 3) return targetStep - 1;
        return targetStep;
    };

    const showStep = (targetStep) => {
        step = targetStep;

        steps.forEach((item) => {
            const isActive = Number(item.dataset.step) === targetStep;
            item.classList.toggle("is-active", isActive);
            setHidden(item, !isActive);
        });

        indicators.forEach((item) => {
            const itemStep = Number(item.dataset.stepIndicator);
            const isSkipped = itemStep === 2 && flowInput.value !== "use_model";
            item.classList.toggle("is-active", itemStep === targetStep);
            item.classList.toggle("is-complete", !isSkipped && itemStep < targetStep);
            item.style.display = isSkipped ? "none" : "";
        });

        if (progress) {
            const total = Math.max(1, visibleStepTotal() - 1);
            const position = Math.min(total, visibleStepPosition(targetStep));
            progress.style.width = `${Math.round((position / total) * 100)}%`;
        }
    };

    const getNextStep = () => {
        if (step === 0) return 1;
        if (step === 1 && flowInput.value !== "use_model") return 3;
        return Math.min(6, step + 1);
    };

    const getPrevStep = () => {
        if (step === 3 && flowInput.value !== "use_model") return 1;
        return Math.max(0, step - 1);
    };

    const setFocusFlow = (flow) => {
        if (flow === "create_model") {
            window.location.href = createModelUrl;
            return;
        }

        flowInput.value = flow;

        $$(".bai-campaign-focus-card").forEach((card) => {
            card.classList.toggle("is-active", card.dataset.campaignFlow === flow);
        });

        const picker = $("#benditoai-campaign-model-picker");
        setHidden(picker, flow !== "use_model");
        showStep(step);

        if (flow !== "use_model") {
            selectedModel = null;
            modelIdInput.value = "";
            modelUrlInput.value = "";
            outfitIdInput.value = "";
            outfitTagInput.value = "";
            $$(".bai-campaign-model-card").forEach((card) => card.classList.remove("is-active"));
            updateSelectedModelPreview();
        }
    };

    const applySelectedModel = (card, outfitButton = null) => {
        if (!card) return;

        const activeOutfit = outfitButton || card.querySelector(".bai-campaign-outfit-chip.is-active") || card.querySelector(".bai-campaign-outfit-chip");

        $$(".bai-campaign-model-card").forEach((item) => item.classList.remove("is-active"));
        card.classList.add("is-active");

        card.querySelectorAll(".bai-campaign-outfit-chip").forEach((chip) => {
            chip.classList.toggle("is-active", chip === activeOutfit);
        });

        selectedModel = {
            id: card.dataset.modelId || "",
            name: card.dataset.modelName || "Modelo AI",
            modelUrl: card.dataset.modelUrl || "",
            outfitId: activeOutfit?.dataset.outfitId || card.dataset.outfitId || "",
            outfitTag: activeOutfit?.dataset.outfitTag || card.dataset.outfitTag || "principal",
            outfitName: activeOutfit?.dataset.outfitName || card.dataset.outfitName || "Principal",
            imageUrl: activeOutfit?.dataset.outfitUrl || card.dataset.outfitUrl || card.dataset.modelUrl || "",
        };

        modelIdInput.value = selectedModel.id;
        modelUrlInput.value = selectedModel.imageUrl;
        outfitIdInput.value = selectedModel.outfitId;
        outfitTagInput.value = selectedModel.outfitTag;

        const modelImage = card.querySelector(".bai-campaign-model-image img");
        if (modelImage && selectedModel.imageUrl) {
            modelImage.src = selectedModel.imageUrl;
            modelImage.alt = selectedModel.outfitName || selectedModel.name;
        }

        const selectedOutfitLabel = card.querySelector("[data-selected-outfit-label]");
        if (selectedOutfitLabel) {
            selectedOutfitLabel.textContent = `Outfit: ${selectedModel.outfitName || "Principal"}`;
        }

        try {
            localStorage.setItem("benditoai_campaign_model_ref", JSON.stringify({
                id: selectedModel.id,
                nombre: selectedModel.name,
                image_url: selectedModel.imageUrl,
                outfit_id: selectedModel.outfitId,
                outfit_tag: selectedModel.outfitTag,
                outfit_name: selectedModel.outfitName,
                source: "campana_wizard",
            }));
        } catch (error) {
            // Storage can be blocked by the browser.
        }

        updateSelectedModelPreview();
    };

    const updateSelectedModelPreview = () => {
        const media = $("#benditoai-selected-model-preview");
        const name = $("#benditoai-selected-model-name");
        const outfit = $("#benditoai-selected-outfit-name");
        const editLink = $("#benditoai-edit-model-link");

        if (name) name.textContent = selectedModel?.name || "Sin seleccionar";
        if (outfit) outfit.textContent = selectedModel ? `Outfit: ${selectedModel.outfitName || "Principal"}` : "Selecciona modelo y outfit en la pantalla inicial.";

        if (media) {
            media.innerHTML = selectedModel?.imageUrl
                ? `<img src="${escapeHtml(selectedModel.imageUrl)}" alt="${escapeHtml(selectedModel.name)}">`
                : `<i class="fas fa-user" aria-hidden="true"></i>`;
        }

        if (editLink && selectedModel?.id) {
            const url = new URL(editModelUrl, window.location.origin);
            url.searchParams.set("modelo_id", selectedModel.id);
            if (selectedModel.outfitId) url.searchParams.set("outfit_id", selectedModel.outfitId);
            editLink.href = url.toString();
        }
    };

    const updateMainProductPreview = () => {
        const preview = $("#benditoai-product-main-preview");
        const first = productImages[0];

        if (preview) {
            preview.innerHTML = first
                ? `<img src="${first.data}" alt="${escapeHtml(first.name)}">`
                : `<i class="fas fa-image" aria-hidden="true"></i><p>La imagen principal aparecera al subir referencias.</p>`;
        }

    };

    const renderProductThumbs = () => {
        const thumbs = $("#benditoai-product-thumbs");
        if (!thumbs) return;

        if (!productImages.length) {
            thumbs.innerHTML = "";
            updateMainProductPreview();
            return;
        }

        thumbs.innerHTML = productImages.map((item, index) => `
            <figure class="bai-campaign-thumb" data-index="${index}">
                <img src="${item.data}" alt="${escapeHtml(item.name)}">
                <figcaption>
                    <span>${index === 0 ? "Principal" : `Ref ${index + 1}`}</span>
                    <span class="bai-campaign-thumb-actions">
                        <button type="button" class="bai-campaign-mini-btn" data-image-action="up" aria-label="Mover arriba" ${index === 0 ? "disabled" : ""}><i class="fas fa-arrow-left" aria-hidden="true"></i></button>
                        <button type="button" class="bai-campaign-mini-btn" data-image-action="down" aria-label="Mover abajo" ${index === productImages.length - 1 ? "disabled" : ""}><i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                        <button type="button" class="bai-campaign-mini-btn" data-image-action="remove" aria-label="Eliminar"><i class="fas fa-trash" aria-hidden="true"></i></button>
                    </span>
                </figcaption>
            </figure>
        `).join("");

        updateMainProductPreview();
    };

    const readFiles = async (files) => {
        const selected = Array.from(files || []).filter((file) => file.type && file.type.startsWith("image/"));
        const slots = Math.max(0, 3 - productImages.length);

        if (!selected.length) return;
        if (slots <= 0) {
            toast("Puedes subir maximo 3 imagenes del producto.");
            return;
        }

        const limited = selected.slice(0, slots);

        const readers = limited.map((file) => new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = () => resolve({ name: file.name, data: reader.result });
            reader.onerror = () => resolve(null);
            reader.readAsDataURL(file);
        }));

        const loaded = (await Promise.all(readers)).filter(Boolean);
        productImages = productImages.concat(loaded).slice(0, 3);

        if (selected.length > slots) {
            toast("Solo se agregaron 3 imagenes como maximo.");
        }

        renderProductThumbs();
    };

    const validateStep = () => {
        if (step === 0) {
            if (!flowInput.value) {
                toast("Elige como quieres iniciar la campana.");
                return false;
            }
            if (flowInput.value === "use_model" && !selectedModel) {
                toast("Selecciona un modelo y outfit para continuar.");
                return false;
            }
        }

        if (step === 1) {
            const product = $("#benditoai-campaign-product")?.value.trim();
            const category = $("#benditoai-campaign-category")?.value;
            if (!product) {
                toast("Escribe el nombre del producto.");
                return false;
            }
            if (!category) {
                toast("Selecciona una categoria.");
                return false;
            }
            if (!productImages.length) {
                toast("Sube al menos una imagen del producto.");
                return false;
            }
        }

        if (step === 2 && flowInput.value === "use_model" && !selectedModel) {
            toast("Selecciona el modelo que se usara en la campana.");
            return false;
        }

        if (step === 5 && !getSelectedFormats().length) {
            toast("Selecciona al menos un formato.");
            return false;
        }

        return true;
    };

    const getSelectedFormats = () => {
        return $$("input[name='formatos[]']:checked").map((input) => {
            const card = input.closest(".bai-campaign-format-option");
            const id = input.value;
            return {
                ...(formatMeta[id] || {}),
                id,
                label: card?.querySelector("strong")?.textContent?.trim() || formatMeta[id]?.label || id,
                ratio: card?.dataset.ratio || formatMeta[id]?.ratio || "1:1",
                size: card?.dataset.size || formatMeta[id]?.size || "",
                imageSize: card?.dataset.imageSize || formatMeta[id]?.imageSize || "1K",
            };
        });
    };

    const updateFormatPreview = (card = null) => {
        const selectedCard = card || $(".bai-campaign-format-option.is-active") || $(".bai-campaign-format-option input:checked")?.closest(".bai-campaign-format-option") || $(".bai-campaign-format-option");
        if (!selectedCard) return;

        const input = selectedCard.querySelector("input");
        const id = input?.value || "instagram";
        const meta = formatMeta[id] || {};
        const ratio = selectedCard.dataset.ratio || meta.ratio || "1:1";
        const size = selectedCard.dataset.size || meta.size || "";

        $$(".bai-campaign-format-option").forEach((item) => {
            const itemInput = item.querySelector("input");
            item.classList.toggle("is-active", itemInput?.checked || item === selectedCard);
        });

        const name = $("#benditoai-format-preview-name");
        const title = $("#benditoai-format-preview-title");
        const sizeLabel = $("#benditoai-format-preview-size");
        const box = $("#benditoai-format-ratio-box");

        if (name) name.textContent = meta.label || selectedCard.querySelector("strong")?.textContent?.trim() || "Formato";
        if (title) title.textContent = $("#benditoai-campaign-slogan")?.value.trim() || "Tu campana";
        if (sizeLabel) sizeLabel.textContent = size;
        if (box) box.style.setProperty("--campaign-ratio", ratioToCss(ratio));
    };

    const updateCopyPreview = () => {
        const product = $("#benditoai-campaign-product")?.value.trim() || "Producto destacado";
        const slogan = $("#benditoai-campaign-slogan")?.value.trim() || "Tu eslogan aqui";
        const cta = $("#benditoai-campaign-cta")?.value.trim() || "Descubre mas";

        const title = $("#benditoai-copy-preview-title");
        const productLabel = $("#benditoai-copy-preview-product");
        const ctaLabel = $("#benditoai-copy-preview-cta");

        if (title) title.textContent = slogan;
        if (productLabel) productLabel.textContent = product;
        if (ctaLabel) ctaLabel.textContent = cta;

        updateFormatPreview();
    };

    const selectDefaultPalette = () => {
        const first = $(".bai-campaign-palette");
        if (first) first.click();
    };

    const resetVisual = () => {
        $("#benditoai-custom-colors").value = "";
        $("#benditoai-custom-background").value = "";
        $("#benditoai-vary-background").checked = false;
        $("#benditoai-campaign-tone").value = "Elegante";
        $("#benditoai-campaign-style").value = "Minimalista";
        $("#benditoai-campaign-background").selectedIndex = 0;
        selectDefaultPalette();
        updateStyleHint();
    };

    const updateStyleHint = () => {
        const select = $("#benditoai-campaign-style");
        const hint = $("#benditoai-style-hint");
        if (select && hint) {
            hint.textContent = select.selectedOptions[0]?.dataset.hint || "";
        }
    };

    const buildPayload = () => {
        const formData = new FormData(form);
        const formats = getSelectedFormats();

        return {
            campaign_flow: flowInput.value,
            producto: ($("#benditoai-campaign-product")?.value || "").trim(),
            categoria: formData.get("categoria") || "",
            product_images: JSON.stringify(productImages.map((item) => item.data)),
            use_model: flowInput.value === "use_model" ? "1" : "0",
            model_id: modelIdInput.value || "",
            model_url: modelUrlInput.value || "",
            outfit_id: outfitIdInput.value || "",
            outfit_tag: outfitTagInput.value || "",
            modelo_nombre: selectedModel?.name || "",
            outfit_nombre: selectedModel?.outfitName || "",
            paleta: $("#benditoai-campaign-palette")?.value || "",
            paleta_colores: $("#benditoai-campaign-palette-colors")?.value || "",
            colores_custom: ($("#benditoai-custom-colors")?.value || "").trim(),
            tono: formData.get("tono") || "",
            estilo: formData.get("estilo") || "",
            fondo_preset: formData.get("fondo_preset") || "",
            fondo_custom: ($("#benditoai-custom-background")?.value || "").trim(),
            generar_angulos_fondo: $("#benditoai-vary-background")?.checked ? "1" : "0",
            slogan: ($("#benditoai-campaign-slogan")?.value || "").trim(),
            cta: ($("#benditoai-campaign-cta")?.value || "").trim(),
            copy_direction: ($("#benditoai-copy-direction")?.value || "").trim(),
            formatos: JSON.stringify(formats),
            export_png: formData.get("export_png") ? "1" : "0",
        };
    };

    const setGenerating = (isGenerating) => {
        const status = $("#benditoai-generation-status");
        const button = $("#benditoai-generate-campaign");
        setHidden(status, !isGenerating);
        if (button) {
            button.disabled = isGenerating;
            button.textContent = isGenerating ? "Generando..." : "Generar campana";
        }
    };

    const renderResults = (results) => {
        const main = $("#benditoai-result-main");
        const grid = $("#benditoai-result-grid");
        const downloadAll = $("#benditoai-download-all");
        const share = $("#benditoai-share-campaign");

        lastResults = Array.isArray(results) ? results : [];

        if (!lastResults.length) {
            if (main) {
                main.innerHTML = `<div class="bai-campaign-result-empty"><i class="fas fa-image" aria-hidden="true"></i><p>Tus imagenes generadas apareceran aqui.</p></div>`;
            }
            if (grid) grid.innerHTML = "";
            if (downloadAll) downloadAll.disabled = true;
            if (share) share.disabled = true;
            return;
        }

        const first = lastResults[0];

        if (main) {
            main.innerHTML = `<img src="${escapeHtml(first.image_url)}?t=${Date.now()}" alt="${escapeHtml(first.label || "Campana")}">`;
        }

        if (grid) {
            grid.innerHTML = lastResults.map((item, index) => `
                <button type="button" class="bai-campaign-result-card${index === 0 ? " is-active" : ""}" data-result-index="${index}">
                    <img src="${escapeHtml(item.image_url)}?t=${Date.now()}" alt="${escapeHtml(item.label || "Campana")}">
                    <span><strong>${escapeHtml(item.label || "Formato")}</strong><small>${escapeHtml(item.size || "")}</small></span>
                </button>
            `).join("");
        }

        if (downloadAll) downloadAll.disabled = false;
        if (share) share.disabled = false;
    };

    const generarCampana = async (payload) => {
        const errorBox = $("#benditoai-error");
        if (errorBox) setHidden(errorBox, true);

        setGenerating(true);

        try {
            const data = new FormData();
            data.append("action", "benditoai_generar_campana");

            Object.keys(payload).forEach((key) => {
                data.append(key, payload[key]);
            });

            const detail = $("#benditoai-generation-detail");
            if (detail) detail.textContent = "Enviando producto, modelo y presets a la IA.";

            const response = await fetch(benditoai_ajax.ajax_url, {
                method: "POST",
                body: data,
            });

            const res = await response.json();

            setGenerating(false);

            if (res.success) {
                const images = Array.isArray(res.data?.images)
                    ? res.data.images
                    : (res.data?.image_url ? [{ image_url: res.data.image_url, label: "Campana", size: "" }] : []);
                renderResults(images);
                showStep(6);
                return;
            }

            const message = typeof res.data === "string" ? res.data : (res.data?.message || "Error generando campana");
            if (errorBox) {
                errorBox.textContent = message;
                setHidden(errorBox, false);
            }
            showStep(6);
        } catch (error) {
            console.error(error);
            setGenerating(false);
            if (errorBox) {
                errorBox.textContent = "Error inesperado, intenta de nuevo.";
                setHidden(errorBox, false);
            }
            showStep(6);
        }
    };

    const hydrateModelFromContext = () => {
        let payload = null;

        try {
            const params = new URLSearchParams(window.location.search);
            const modelId = params.get("modelo_id") || "";
            const imageUrl = params.get("model_url") || "";
            if (modelId || imageUrl) {
                payload = {
                    id: modelId,
                    image_url: imageUrl,
                    nombre: params.get("modelo_nombre") || "Modelo AI",
                    outfit_id: params.get("outfit_id") || "",
                    outfit_tag: params.get("outfit_tag") || "",
                };
            }
        } catch (error) {
            payload = null;
        }

        if (!payload) {
            try {
                payload = JSON.parse(localStorage.getItem("benditoai_campaign_model_ref") || "null");
            } catch (error) {
                payload = null;
            }
        }

        if (!payload) {
            try {
                payload = JSON.parse(localStorage.getItem("benditoai_selected_model") || "null");
            } catch (error) {
                payload = null;
            }
        }

        if (!payload || (!payload.id && !payload.image_url)) return;

        const cards = $$(".bai-campaign-model-card");
        const card = cards.find((item) => {
            return (payload.id && String(item.dataset.modelId) === String(payload.id)) ||
                (payload.image_url && item.dataset.modelUrl === payload.image_url);
        });

        if (!card) {
            if (!payload.image_url) return;

            setFocusFlow("use_model");
            selectedModel = {
                id: String(payload.id || ""),
                name: String(payload.nombre || payload.modelo_nombre || "Modelo AI"),
                modelUrl: String(payload.image_url || ""),
                outfitId: String(payload.outfit_id || ""),
                outfitTag: String(payload.outfit_tag || "principal"),
                outfitName: String(payload.outfit_name || "Principal"),
                imageUrl: String(payload.image_url || ""),
            };
            modelIdInput.value = selectedModel.id;
            modelUrlInput.value = selectedModel.imageUrl;
            outfitIdInput.value = selectedModel.outfitId;
            outfitTagInput.value = selectedModel.outfitTag;
            updateSelectedModelPreview();
            return;
        }

        let outfit = null;
        if (payload.outfit_id) {
            outfit = Array.from(card.querySelectorAll(".bai-campaign-outfit-chip"))
                .find((chip) => String(chip.dataset.outfitId) === String(payload.outfit_id));
        }

        setFocusFlow("use_model");
        applySelectedModel(card, outfit);
    };

    root.addEventListener("click", (event) => {
        const focusCard = event.target.closest(".bai-campaign-focus-card");
        if (focusCard && root.contains(focusCard)) {
            if (focusCard.disabled || focusCard.classList.contains("is-disabled")) return;
            setFocusFlow(focusCard.dataset.campaignFlow);
        }

        const modelMain = event.target.closest(".bai-campaign-model-main");
        if (modelMain && root.contains(modelMain)) {
            applySelectedModel(modelMain.closest(".bai-campaign-model-card"));
        }

        const outfitChip = event.target.closest(".bai-campaign-outfit-chip");
        if (outfitChip && root.contains(outfitChip)) {
            event.stopPropagation();
            applySelectedModel(outfitChip.closest(".bai-campaign-model-card"), outfitChip);
        }

        const next = event.target.closest(".benditoai-next");
        if (next && root.contains(next)) {
            if (validateStep()) showStep(getNextStep());
        }

        const prev = event.target.closest(".benditoai-prev");
        if (prev && root.contains(prev)) {
            showStep(getPrevStep());
        }

        const thumbButton = event.target.closest("[data-image-action]");
        if (thumbButton && root.contains(thumbButton)) {
            const figure = thumbButton.closest(".bai-campaign-thumb");
            const index = Number(figure?.dataset.index || 0);
            const action = thumbButton.dataset.imageAction;

            if (action === "remove") {
                productImages.splice(index, 1);
            }

            if (action === "up" && index > 0) {
                [productImages[index - 1], productImages[index]] = [productImages[index], productImages[index - 1]];
            }

            if (action === "down" && index < productImages.length - 1) {
                [productImages[index + 1], productImages[index]] = [productImages[index], productImages[index + 1]];
            }

            renderProductThumbs();
        }

        const palette = event.target.closest(".bai-campaign-palette");
        if (palette && root.contains(palette)) {
            $$(".bai-campaign-palette").forEach((item) => item.classList.remove("is-active"));
            palette.classList.add("is-active");
            $("#benditoai-campaign-palette").value = palette.dataset.paletteName || "";
            $("#benditoai-campaign-palette-colors").value = palette.dataset.paletteColors || "";
            const caption = $("#benditoai-visual-caption");
            if (caption) caption.textContent = palette.dataset.paletteHint || caption.textContent;
        }

        const formatCard = event.target.closest(".bai-campaign-format-option");
        if (formatCard && root.contains(formatCard)) {
            window.setTimeout(() => updateFormatPreview(formatCard), 0);
        }

        const resultCard = event.target.closest(".bai-campaign-result-card");
        if (resultCard && root.contains(resultCard)) {
            const index = Number(resultCard.dataset.resultIndex || 0);
            const item = lastResults[index];
            const main = $("#benditoai-result-main");
            if (item && main) {
                main.innerHTML = `<img src="${escapeHtml(item.image_url)}?t=${Date.now()}" alt="${escapeHtml(item.label || "Campana")}">`;
                $$(".bai-campaign-result-card").forEach((card) => card.classList.toggle("is-active", card === resultCard));
            }
        }
    });

    $("#benditoai-product-images")?.addEventListener("change", function () {
        readFiles(this.files);
        this.value = "";
    });

    const dropzone = $("#benditoai-product-dropzone");
    if (dropzone) {
        ["dragenter", "dragover"].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.add("is-dragging");
            });
        });

        ["dragleave", "drop"].forEach((eventName) => {
            dropzone.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.remove("is-dragging");
            });
        });

        dropzone.addEventListener("drop", (event) => {
            readFiles(event.dataTransfer?.files || []);
        });
    }

    ["input", "change"].forEach((eventName) => {
        $("#benditoai-campaign-product")?.addEventListener(eventName, updateCopyPreview);
        $("#benditoai-campaign-slogan")?.addEventListener(eventName, updateCopyPreview);
        $("#benditoai-campaign-cta")?.addEventListener(eventName, updateCopyPreview);
    });

    $("#benditoai-campaign-style")?.addEventListener("change", () => {
        updateStyleHint();
    });

    $("#benditoai-reset-visual")?.addEventListener("click", resetVisual);

    $("#benditoai-edit-model-link")?.addEventListener("click", (event) => {
        const ok = window.confirm("Vas a salir del modulo de campanas para editar el modelo. ¿Quieres continuar?");
        if (!ok) event.preventDefault();
    });

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (!validateStep()) return;

        lastPayload = buildPayload();
        await generarCampana(lastPayload);
    });

    $("#benditoai-recrear")?.addEventListener("click", () => {
        if (!lastPayload) {
            if (!validateStep()) return;
            lastPayload = buildPayload();
        }
        showStep(5);
        generarCampana(lastPayload);
    });

    $("#benditoai-reset")?.addEventListener("click", () => {
        form.reset();
        productImages = [];
        selectedModel = null;
        lastPayload = null;
        lastResults = [];
        flowInput.value = "";
        modelIdInput.value = "";
        modelUrlInput.value = "";
        outfitIdInput.value = "";
        outfitTagInput.value = "";
        $$(".is-active").forEach((item) => {
            if (!item.classList.contains("baiw-step")) item.classList.remove("is-active");
        });
        setHidden($("#benditoai-campaign-model-picker"), true);
        renderProductThumbs();
        renderResults([]);
        updateSelectedModelPreview();
        updateCopyPreview();
        resetVisual();
        showStep(0);
    });

    $("#benditoai-download-all")?.addEventListener("click", () => {
        lastResults.forEach((item, index) => {
            const link = document.createElement("a");
            link.href = item.image_url;
            link.download = `benditoai-campana-${item.id || index + 1}.png`;
            document.body.appendChild(link);
            link.click();
            link.remove();
        });
    });

    $("#benditoai-share-campaign")?.addEventListener("click", async () => {
        const first = lastResults[0];
        if (!first) return;

        if (navigator.share) {
            try {
                await navigator.share({
                    title: "Campana BenditoAI",
                    text: "Imagen generada con BenditoAI",
                    url: first.image_url,
                });
                return;
            } catch (error) {
                // User cancelled or browser rejected the share dialog.
            }
        }

        try {
            await navigator.clipboard.writeText(first.image_url);
            toast("Link copiado al portapapeles.");
        } catch (error) {
            toast("No se pudo compartir automaticamente.");
        }
    });

    hydrateModelFromContext();
    selectDefaultPalette();
    updateStyleHint();
    updateCopyPreview();
    updateFormatPreview();
    showStep(0);
});
