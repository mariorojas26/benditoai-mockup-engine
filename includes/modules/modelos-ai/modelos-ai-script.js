document.addEventListener("DOMContentLoaded", function () {
    const historyWrapper = document.querySelector(".benditoai-wrapper-historia-modelos");
    const getOutfitCatalog = () => {
        if (!historyWrapper) return [];
        const raw = historyWrapper.dataset.outfitCatalog || "[]";
        try {
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    };

    const renderOutfitRailMarkup = () => {
        const outfits = getOutfitCatalog();
        if (!outfits.length) return "";

        const cards = outfits
            .filter((outfit) => outfit && outfit.id && outfit.name)
            .map((outfit) => {
                const id = escapeHtml(outfit.id || "");
                const name = escapeHtml(outfit.name || "Outfit");
                const prompt = escapeHtml(outfit.prompt_hint || "");
                const thumb = escapeHtml(outfit.thumb_url || outfit.reference_url || "");
                const reference = escapeHtml(outfit.reference_url || outfit.thumb_url || "");
                return `
                    <button
                        type="button"
                        class="benditoai-style-option"
                        data-style-id="${id}"
                        data-style-label="${name}"
                        data-style-prompt="${prompt}"
                        data-style-reference="${reference}"
                        aria-label="Usar estilo: ${name}"
                        aria-pressed="false"
                    >
                        <img src="${thumb}" alt="${name}" loading="lazy" />
                        <span>${name}</span>
                    </button>
                `;
            })
            .join("");

        if (!cards) return "";

        return `
            <div class="benditoai-desktop-style-pills" data-style-catalog-rail>
                <div class="benditoai-desktop-style-pills-head">
                    <span>Estilos sugerido para editar tus modelos</span>
                    <div class="benditoai-style-rail-nav" aria-label="Navegar estilos">
                        <button type="button" class="benditoai-style-rail-btn is-prev" data-style-nav="prev" aria-label="Ver estilos anteriores">
                            <span aria-hidden="true">&lsaquo;</span>
                        </button>
                        <button type="button" class="benditoai-style-rail-btn is-next" data-style-nav="next" aria-label="Ver mas estilos">
                            <span aria-hidden="true">&rsaquo;</span>
                        </button>
                    </div>
                </div>
                <div class="benditoai-desktop-style-pills-list">
                    ${cards}
                </div>
            </div>
        `;
    };

    const getOutfitLimit = () => {
        const parsed = Number(historyWrapper?.dataset.outfitLimit || 1);
        return Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
    };

    const getOutfitWarning = () => {
        return historyWrapper?.dataset.outfitWarning || "Has alcanzado el límite de outfits para este modelo.";
    };

    const renderSavedOutfitsRailMarkup = (modelData = null) => {
        const limit = getOutfitLimit();
        const warning = escapeHtml(getOutfitWarning());
        const principal = modelData && modelData.principal_outfit ? modelData.principal_outfit : null;
        const principalName = principal?.nombre_outfit || `${modelData?.nombre_modelo || "Modelo AI"} Outfit(1)`;
        const principalImage = principal?.image_url || modelData?.image_url || "";
        const principalId = principal?.id || "";
        const hasPrincipal = Boolean(principalImage);
        const initialCount = hasPrincipal ? 1 : 0;
        const canAdd = initialCount < limit;
        const initialWarning = !canAdd ? warning : "";
        const principalCard = hasPrincipal
            ? `
                <div
                    class="benditoai-saved-outfit-card"
                    data-outfit-id="${escapeHtml(principalId)}"
                    data-outfit-tag="principal"
                    data-modelo-id="${escapeHtml(modelData?.id || "")}"
                    data-modelo-nombre="${escapeHtml(modelData?.nombre_modelo || "Modelo AI")}"
                    data-outfit-name="${escapeHtml(principalName)}"
                    data-outfit-image="${escapeHtml(principalImage)}"
                    role="button"
                    tabindex="0"
                    aria-pressed="false"
                    aria-label="Usar outfit: ${escapeHtml(principalName)}"
                >
                    <div class="benditoai-saved-outfit-thumb">
                        <img src="${escapeHtml(principalImage)}" alt="${escapeHtml(principalName)}" loading="lazy" />
                        <span class="benditoai-saved-outfit-use">Usar</span>
                    </div>
                    <div class="benditoai-saved-outfit-body">
                        <span class="benditoai-saved-outfit-tag">Principal</span>
                        <span class="benditoai-saved-outfit-name" data-outfit-name-label>${escapeHtml(principalName)}</span>
                    </div>
                </div>
            `
            : "";

        return `
            <button
                type="button"
                class="benditoai-outfits-toggle"
                data-outfits-toggle
                aria-expanded="false"
            >
                <i class="fas fa-shirt" aria-hidden="true"></i>
                <span>Outfits del modelo</span>
                <strong class="benditoai-outfit-counter" data-outfit-counter>${initialCount} de ${limit}</strong>
            </button>
            <div class="benditoai-saved-outfits benditoai-saved-outfits-panel" data-saved-outfits-rail hidden>
                <div class="benditoai-saved-outfits-panel-head">
                    <span>Mis outfits guardados</span>
                    <strong class="benditoai-outfit-counter" data-outfit-counter>${initialCount} de ${limit} outfits guardados</strong>
                </div>
                <p class="benditoai-outfit-limit-warning" data-outfit-warning-message ${canAdd ? "hidden" : ""}>${initialWarning}</p>
                <div class="benditoai-saved-outfits-list" data-saved-outfits-list>
                    ${principalCard || `
                    <p class="benditoai-saved-outfits-empty" data-saved-outfits-empty>
                        Aun no tienes outfits guardados para este modelo.
                    </p>`}
                </div>
            </div>
        `;
    };

    const initStyleRailNavigation = () => {
        document.addEventListener("click", (event) => {
            const navButton = event.target.closest(".benditoai-style-rail-btn");
            if (!navButton) return;
            const rail = navButton.closest(".benditoai-desktop-style-pills");
            const list = rail?.querySelector(".benditoai-desktop-style-pills-list");
            if (!list) return;

            const direction = navButton.dataset.styleNav === "prev" ? -1 : 1;
            const firstCard = list.querySelector(".benditoai-style-option");
            const cardWidth = firstCard ? firstCard.getBoundingClientRect().width : 140;
            const gap = 7;
            const delta = (cardWidth + gap) * 2 * direction;
            list.scrollBy({ left: delta, behavior: "smooth" });
        });
    };

    const initHistorialPaginator = () => {
        const grid = document.getElementById("benditoai-historial-mockups");
        const pagination = document.getElementById("benditoai-historial-pagination");
        const scrollHint = document.getElementById("benditoai-historial-scroll-hint");
        if (!grid || !pagination) return;

        const prevBtn = pagination.querySelector("[data-history-page='prev']");
        const nextBtn = pagination.querySelector("[data-history-page='next']");
        const status = pagination.querySelector("[data-history-page='status']");
        if (!prevBtn || !nextBtn || !status) return;

        let currentPage = 1;
        const isMobileView = () => window.matchMedia("(max-width: 768px)").matches;
        const thumbsClass = "benditoai-mobile-history-thumbs";
        const desktopThumbsClass = "benditoai-desktop-history-thumbs";
        const escapeAttr = (value) => String(value || "").replace(/"/g, "&quot;");
        const getCreateModelUrl = () => historyWrapper?.dataset.createModelUrl || "/crea-modelo/";
        const getModelLimit = () => {
            const parsed = Number(historyWrapper?.dataset.modelLimit || 0);
            return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
        };
        const getModelWarning = () => historyWrapper?.dataset.modelWarning || "Maximo de modelos alcanzado.";
        const canAddModel = (count) => {
            const limit = getModelLimit();
            return limit > 0 && count < limit;
        };

        const getItems = () => Array.from(grid.querySelectorAll(".benditoai-historial-item"));
        const getPerPage = () => 1;
        const getTotalPages = () => Math.max(1, Math.ceil(getItems().length / getPerPage()));
        const smoothScrollToHistoryTop = () => {
            const wrapper = grid.closest(".benditoai-wrapper-historia-modelos");
            if (!wrapper) return;
            const top = Math.max(0, wrapper.getBoundingClientRect().top + window.scrollY - 24);
            window.requestAnimationFrame(() => {
                window.scrollTo({ top, behavior: "smooth" });
            });
        };

        const getItemImage = (item) => item?.querySelector(".benditoai-historial-img")?.src || "";

        const clearThumbs = () => {
            grid.querySelectorAll(`.${thumbsClass}`).forEach((node) => node.remove());
            grid.querySelectorAll(`.${desktopThumbsClass}`).forEach((node) => node.remove());
        };

        const buildThumbsMarkup = (items, activeIndex, thumbClass) => {
            const count = items.length;
            const limit = getModelLimit();
            const canAdd = canAddModel(count);
            const remaining = Math.max(0, limit - count);
            const addLabel = canAdd ? "Añadir modelo" : "Máximo alcanzado";
            const addTitle = canAdd
                ? `Puedes crear ${remaining} modelo${remaining === 1 ? "" : "s"} más`
                : getModelWarning();
            const modelThumbs = items.map((item, index) => {
                const src = getItemImage(item);
                const label = item.querySelector(".benditoai-historial-name")?.textContent?.trim() || `Modelo ${index + 1}`;
                const selected = index === activeIndex;
                return `
                    <button
                        type="button"
                        class="${thumbClass}${selected ? " is-active" : ""}"
                        data-history-index="${index}"
                        aria-label="Ver ${escapeAttr(label)}"
                        aria-pressed="${selected ? "true" : "false"}"
                    >
                        <img src="${src}" alt="" loading="lazy" />
                    </button>
                `;
            }).join("");

            const addThumb = `
                <button
                    type="button"
                    class="${thumbClass} benditoai-history-add-model${canAdd ? "" : " is-disabled"}"
                    data-history-add-model
                    aria-label="${escapeAttr(addTitle)}"
                    aria-disabled="${canAdd ? "false" : "true"}"
                    title="${escapeAttr(addTitle)}"
                    ${canAdd ? "" : "disabled"}
                >
                    <span class="benditoai-history-add-model-icon" aria-hidden="true">${canAdd ? "+" : "!"}</span>
                    <span class="benditoai-history-add-model-text">${addLabel}</span>
                    <small>${count}/${limit}</small>
                </button>
            `;

            return modelThumbs + addThumb;
        };

        const renderThumbs = (items, activeIndex) => {
            clearThumbs();
            const activeItem = items[activeIndex];
            if (!activeItem) return;

            if (isMobileView()) {
                const toggleBtn = activeItem.querySelector(".benditoai-toggle-info");
                if (!toggleBtn) return;
                const rail = document.createElement("div");
                rail.className = thumbsClass;
                rail.innerHTML = buildThumbsMarkup(items, activeIndex, "benditoai-mobile-history-thumb");
                activeItem.insertBefore(rail, toggleBtn);
                return;
            }

            const imageWrap = activeItem.querySelector(".benditoai-img-wrap");
            if (!imageWrap) return;

            const desktopRail = document.createElement("div");
            desktopRail.className = desktopThumbsClass;
            desktopRail.innerHTML = `
                <div class="benditoai-desktop-history-thumbs-head">
                    <span>Mis modelos</span>
                    <small>${activeIndex + 1} / ${items.length}</small>
                </div>
                <div class="benditoai-desktop-history-thumbs-list">
                    ${buildThumbsMarkup(items, activeIndex, "benditoai-desktop-history-thumb")}
                </div>
            `;
            imageWrap.insertAdjacentElement("afterend", desktopRail);
        };

        const render = () => {
            const items = getItems();
            const perPage = getPerPage();
            const totalPages = getTotalPages();

            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const start = (currentPage - 1) * perPage;
            const end = start + perPage;

            items.forEach((item, index) => {
                const visible = index >= start && index < end;
                item.hidden = !visible;
                item.setAttribute("aria-hidden", visible ? "false" : "true");
                if (visible) {
                    item.style.removeProperty("display");
                } else {
                    item.style.setProperty("display", "none", "important");
                }
            });

            pagination.hidden = isMobileView() || items.length <= perPage;
            status.textContent = `${currentPage} / ${totalPages}`;
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = currentPage >= totalPages;
            renderThumbs(items, start);
            if (scrollHint) scrollHint.hidden = true;
        };

        prevBtn.addEventListener("click", () => {
            if (currentPage <= 1) return;
            currentPage -= 1;
            render();
            smoothScrollToHistoryTop();
        });

        nextBtn.addEventListener("click", () => {
            const totalPages = getTotalPages();
            if (currentPage >= totalPages) return;
            currentPage += 1;
            render();
            smoothScrollToHistoryTop();
        });

        grid.addEventListener("click", (event) => {
            const addModel = event.target.closest("[data-history-add-model]");
            if (addModel) {
                const items = getItems();
                if (!canAddModel(items.length)) {
                    alert(getModelWarning());
                    return;
                }
                window.location.href = getCreateModelUrl();
                return;
            }

            const thumb = event.target.closest("[data-history-index]");
            if (!thumb) return;
            const targetIndex = Number(thumb.dataset.historyIndex || -1);
            if (targetIndex < 0) return;
            currentPage = targetIndex + 1;
            render();
            smoothScrollToHistoryTop();
        });

        window.addEventListener("resize", render);
        grid.addEventListener("benditoai:historial-updated", render);
        document.addEventListener("benditoai:historial-updated", render);

        window.benditoaiRefreshHistorialPagination = () => {
            const totalPages = getTotalPages();
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;
            render();
        };

        render();
    };

    initHistorialPaginator();
    initStyleRailNavigation();

    const root = document.querySelector(".benditoai-modelos-wizard");
    const form = document.getElementById("benditoai-form-modelo-ai");
    if (!root || !form) return;

    const configStage = document.getElementById("benditoai-config-stage");
    const resultStage = document.getElementById("benditoai-result-stage");

    const steps = Array.from(form.querySelectorAll(".baiw-step"));
    const stepIndicators = Array.from(root.querySelectorAll("[data-step-indicator]"));
    const progressBar = document.getElementById("benditoai-wizard-progress");

    const prevBtn = document.getElementById("benditoai-modelo-prev");
    const nextBtn = document.getElementById("benditoai-modelo-next");
    const submitBtn = document.getElementById("benditoai-modelo-submit");
    const createAnotherBtn = document.getElementById("benditoai-modelo-create-another");
    const rasgosConfirmModal = document.getElementById("benditoai-rasgos-confirm");
    const rasgosConfirmCopy = document.getElementById("benditoai-rasgos-confirm-copy");
    const rasgosConfirmOk = form.querySelector("[data-rasgos-confirm-ok]");
    const rasgosConfirmCancelButtons = Array.from(form.querySelectorAll("[data-rasgos-confirm-cancel]"));

    const inlineError = document.getElementById("benditoai-modelo-inline-error");

    const loading = document.getElementById("benditoai-modelo-loading");
    const resultSkeleton = document.getElementById("benditoai-modelo-skeleton");
    const errorPanel = document.getElementById("benditoai-modelo-error-panel");
    const errorBox = document.getElementById("benditoai-modelo-error");
    const retryBtn = document.getElementById("benditoai-modelo-retry");
    const imageWrapper = document.getElementById("benditoai-modelo-image-wrapper");
    const image = document.getElementById("benditoai-modelo-image");
    const downloadBtn = document.getElementById("benditoai-modelo-download");
    const successActions = document.getElementById("benditoai-modelo-success-actions");
    const secondaryActions = document.getElementById("benditoai-modelo-secondary-actions");
    const shareBtn = document.getElementById("benditoai-modelo-share");
    const editBtn = document.getElementById("benditoai-modelo-edit");
    const campaignBtn = document.getElementById("benditoai-modelo-campaign");

    const modeInputs = Array.from(form.querySelectorAll("input[name='modo_creacion']"));
    const publicToggle = document.getElementById("benditoai_perfil_publico_toggle");
    const publicField = document.getElementById("benditoai_perfil_publico");
    const publicLabel = document.getElementById("benditoai_perfil_publico_label");

    const referenceImageInput = form.querySelector("input[name='imagen_referencia']");
    const referenceSmart = document.getElementById("benditoai-file-smart");
    const referenceFileText = document.getElementById("benditoai-file-text");
    const referenceFileMeta = document.getElementById("benditoai-file-meta");
    const referenceFileName = document.getElementById("benditoai-file-name");
    const referenceFilePreview = document.getElementById("benditoai-file-preview");
    const campaignBaseUrl = root.dataset.campaignUrl || "";
    const cancelUrl = root.dataset.cancelUrl || "/";
    const nombreModeloInput = document.getElementById("benditoai_nombre_modelo");
    const nombreModeloCount = document.getElementById("benditoai-modelo-name-count");
    const edadRange = document.getElementById("benditoai_edad_range");
    const edadValue = document.getElementById("benditoai_edad_value");
    const edadHidden = document.getElementById("benditoai_edad");
    const alturaRange = document.getElementById("benditoai_altura");
    const alturaValue = document.getElementById("benditoai_altura_value");
    const pesoRange = document.getElementById("benditoai_peso");
    const pesoValue = document.getElementById("benditoai_peso_value");
    const rasgosAvatarBaseUrl = root.dataset.rasgosAvatarBaseUrl || "";
    const rasgosMiniWizard = form.querySelector("[data-rasgos-miniwizard]");
    const rasgosMiniSteps = Array.from(form.querySelectorAll("[data-rasgos-mini-step]"));
    const rasgosMiniIndicators = Array.from(form.querySelectorAll("[data-rasgos-mini-step-indicator]"));
    const rasgosMiniHint = form.querySelector("[data-rasgos-mini-hint]");
    const rasgosMiniHintBadge = form.querySelector("[data-rasgos-mini-hint-badge]");
    const rasgosMiniHintTitle = form.querySelector("[data-rasgos-mini-hint-title]");
    const rasgosMiniHintCopy = form.querySelector("[data-rasgos-mini-hint-copy]");
    const rasgosChoiceTargets = Array.from(form.querySelectorAll("[data-choice-target]"));
    const rasgosFieldNames = ["genero", "cuerpo", "etnia", "peinado", "color_ojos", "color_pelo", "color_cejas", "nacionalidad"];
    const rasgosMiniStepFields = {
        1: ["genero", "cuerpo"],
        2: ["etnia", "peinado", "nacionalidad"],
        3: ["color_ojos", "color_pelo", "color_cejas"],
    };
    const rasgosFieldIdMap = {
        genero: "benditoai_genero",
        cuerpo: "benditoai_cuerpo",
        etnia: "benditoai_etnia",
        peinado: "benditoai_peinado",
        color_ojos: "benditoai_color_ojos",
        color_pelo: "benditoai_color_pelo",
        color_cejas: "benditoai_color_cejas",
        nacionalidad: "benditoai_nacionalidad",
    };
    if (rasgosAvatarBaseUrl) {
        root.style.setProperty("--rasgos-avatar-base-url", rasgosAvatarBaseUrl);
    }

    let currentStep = 1;
    let currentMiniStep = 1;
    let isSubmitting = false;
    let lastSuccess = null;
    let rasgosAutoAdvanceEnabled = true;
    let rasgosAutoAdvanceLockedManual = false;
    let miniAdvanceTimer = null;
    let rasgosConfirmResolver = null;
    let miniStepTouched = { 1: false, 2: false, 3: false };
    let miniFieldTouched = {
        genero: false,
        cuerpo: false,
        etnia: false,
        peinado: false,
        color_ojos: false,
        color_pelo: false,
        color_cejas: false,
        nacionalidad: false,
    };
    const totalSteps = steps.length;
    const desktopQuery = window.matchMedia("(min-width: 901px)");
    const isDesktop = () => desktopQuery.matches;

    const scrollToTopWithOffset = (element, offset = 48) => {
        if (!element) return;
        const top = Math.max(0, element.getBoundingClientRect().top + window.scrollY - offset);
        const delta = Math.abs(window.scrollY - top);
        if (delta < 28) return;

        window.requestAnimationFrame(() => {
            window.scrollTo({ top, behavior: "smooth" });
        });
    };

    const scrollWizardTopIfNeeded = () => {
        const top = Math.max(0, root.getBoundingClientRect().top + window.scrollY - 48);
        const isMobile = window.matchMedia("(max-width: 900px)").matches;
        const isScrolledPast = window.scrollY > top + 20;

        if (isMobile || isScrolledPast) {
            scrollToTopWithOffset(root, 48);
        }
    };

    const escapeHtml = (value) => {
        return String(value || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/\"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    const getActiveMode = () => {
        return modeInputs.find((input) => input.checked)?.value || "";
    };

    const setRangeProgress = (range) => {
        if (!range) return;
        const min = Number(range.min || 0);
        const max = Number(range.max || 100);
        const val = Number(range.value || min);
        const pct = ((val - min) * 100) / Math.max(max - min, 1);
        range.style.setProperty("--range-progress", `${pct}%`);
    };

    const syncRangos = () => {
        if (edadRange && edadValue && edadHidden) {
            const edad = Number(edadRange.value);
            edadValue.textContent = String(edad);
            edadHidden.value = edad <= 25 ? "young adult" : edad <= 35 ? "adult" : edad <= 45 ? "mature" : "senior";
            setRangeProgress(edadRange);
        }
        if (alturaRange && alturaValue) {
            alturaValue.textContent = `${alturaRange.value} cm`;
            setRangeProgress(alturaRange);
        }
        if (pesoRange && pesoValue) {
            pesoValue.textContent = `${pesoRange.value} kg`;
            setRangeProgress(pesoRange);
        }
    };

    const syncNombreModeloCount = () => {
        if (!nombreModeloInput || !nombreModeloCount) return;
        const current = String(nombreModeloInput.value || "").length;
        const max = Number(nombreModeloInput.maxLength || 60);
        nombreModeloCount.textContent = `${current}/${max > 0 ? max : 60}`;
    };

    const showInlineError = (message) => {
        if (!inlineError) return;
        inlineError.textContent = message;
        inlineError.style.display = "block";
    };

    const clearInlineError = () => {
        if (!inlineError) return;
        inlineError.textContent = "";
        inlineError.style.display = "none";
    };

    const setButtonsDisabled = (disabled) => {
        [prevBtn, nextBtn, submitBtn].forEach((btn) => {
            if (btn) btn.disabled = disabled;
        });
    };

    const setSubmitLoadingState = (loadingState) => {
        if (!submitBtn) return;
        if (loadingState) {
            submitBtn.dataset.originalText = submitBtn.textContent;
            submitBtn.textContent = "Generando...";
            submitBtn.classList.add("baiw-btn--loading");
            return;
        }

        submitBtn.textContent = submitBtn.dataset.originalText || "Generar modelo";
        submitBtn.classList.remove("baiw-btn--loading");
    };

    const syncVisibilityToggle = () => {
        if (!publicToggle || !publicField) return;
        const isPublic = publicToggle.checked;
        publicField.value = isPublic ? "1" : "0";
        publicToggle.setAttribute("aria-checked", isPublic ? "true" : "false");

        if (publicLabel) {
            publicLabel.textContent = isPublic ? "Publico" : "Privado";
        }

    };

    const isRasgosMiniwizardActive = () => currentStep === 2 && getActiveMode() === "rasgos";

    const clearMiniAdvanceTimer = () => {
        if (miniAdvanceTimer) {
            window.clearTimeout(miniAdvanceTimer);
            miniAdvanceTimer = null;
        }
    };

    const resetRasgosTouchState = () => {
        miniStepTouched = { 1: false, 2: false, 3: false };
        miniFieldTouched = {
            genero: false,
            cuerpo: false,
            etnia: false,
            peinado: false,
            color_ojos: false,
            color_pelo: false,
            color_cejas: false,
            nacionalidad: false,
        };
    };

    const getMiniStep = (stepNumber) => form.querySelector(`[data-rasgos-mini-step="${stepNumber}"]`);

    const getFieldByName = (fieldName) => form.querySelector(`[name="${fieldName}"]`);

    const refreshRasgosChoiceTiles = () => {
        rasgosChoiceTargets.forEach((tile) => {
            const targetName = tile.dataset.choiceTarget || "";
            const targetField = getFieldByName(targetName);
            const targetValue = String(targetField?.value || "");
            const tileValue = String(tile.dataset.choiceValue || "");
            const isSelected = targetValue !== "" && targetValue === tileValue;
            tile.classList.toggle("is-selected", isSelected);
            tile.setAttribute("aria-pressed", isSelected ? "true" : "false");
        });
    };

    const syncRasgosMiniHint = () => {
        if (!rasgosMiniHint) return;

        const isManual = !rasgosAutoAdvanceEnabled;
        rasgosMiniHint.classList.toggle("is-manual", isManual);
        if (rasgosMiniHintBadge) rasgosMiniHintBadge.textContent = isManual ? "OFF" : "ON";
        if (rasgosMiniHintTitle) {
            rasgosMiniHintTitle.textContent = isManual
                ? "Desactivado"
                : "Auto activo";
        }
        if (rasgosMiniHintCopy) {
            rasgosMiniHintCopy.textContent = isManual
                ? "Pulsa Siguiente para continuar."
                : "Avanza solo al completar.";
        }
    };

    const confirmDisableRasgosAutoAdvance = () => {
        if (!rasgosAutoAdvanceEnabled) return true;

        if (!rasgosConfirmModal) {
            return window.confirm("Si te devuelves, se desactivara el avance automatico y tendras que usar Siguiente en cada pantalla. ¿Continuar?");
        }

        if (rasgosConfirmCopy) {
            rasgosConfirmCopy.textContent = "Si te devuelves, el paso quedara manual y tendras que usar Siguiente en cada pantalla.";
        }

        return new Promise((resolve) => {
            rasgosConfirmResolver = resolve;
            rasgosConfirmModal.hidden = false;
            rasgosConfirmModal.setAttribute("aria-hidden", "false");
            window.requestAnimationFrame(() => {
                rasgosConfirmOk?.focus();
            });
        });
    };

    const closeRasgosConfirm = (confirmed = false) => {
        if (!rasgosConfirmModal) return;
        rasgosConfirmModal.hidden = true;
        rasgosConfirmModal.setAttribute("aria-hidden", "true");
        if (confirmed && rasgosAutoAdvanceEnabled) {
            rasgosAutoAdvanceEnabled = false;
            rasgosAutoAdvanceLockedManual = true;
            clearMiniAdvanceTimer();
            syncRasgosMiniHint();
        }
        if (rasgosConfirmResolver) {
            const resolve = rasgosConfirmResolver;
            rasgosConfirmResolver = null;
            resolve(confirmed);
        }
    };

    const syncRasgosMiniUi = () => {
        if (!rasgosMiniWizard) return;

        const active = isRasgosMiniwizardActive();
        root.classList.toggle("is-rasgos-miniwizard", active);
        rasgosMiniWizard.hidden = !active;

        rasgosMiniSteps.forEach((step) => {
            const stepNumber = Number(step.dataset.rasgosMiniStep || "1");
            const isActive = active && stepNumber === currentMiniStep;
            step.hidden = !isActive;
            step.classList.toggle("is-active", isActive);
            step.setAttribute("aria-hidden", isActive ? "false" : "true");
        });

        rasgosMiniIndicators.forEach((indicator) => {
            const indicatorStep = Number(indicator.dataset.rasgosMiniStepIndicator || "1");
            indicator.classList.toggle("is-active", active && indicatorStep === currentMiniStep);
            indicator.classList.toggle("is-complete", active && indicatorStep < currentMiniStep);
            if (active && indicatorStep === currentMiniStep) {
                indicator.setAttribute("aria-current", "step");
            } else {
                indicator.removeAttribute("aria-current");
            }
        });

        refreshRasgosChoiceTiles();
        syncRasgosMiniHint();
    };

    const resetRasgosMiniwizard = () => {
        currentMiniStep = 1;
        rasgosAutoAdvanceEnabled = true;
        rasgosAutoAdvanceLockedManual = false;
        resetRasgosTouchState();
        clearMiniAdvanceTimer();
        syncRasgosMiniUi();
    };

    const goToRasgosMiniStep = (targetStep) => {
        const nextStep = Math.max(1, Math.min(3, targetStep));
        currentMiniStep = nextStep;
        clearMiniAdvanceTimer();
        updateStepUi();
        if (nextStep === 3) {
            scheduleRasgosAutoAdvance();
        }
        window.setTimeout(scrollWizardTopIfNeeded, 100);
    };

    const isRasgosMiniStepComplete = (stepNumber) => {
        const step = getMiniStep(stepNumber);
        if (!step) return true;

        const fields = Array.from(step.querySelectorAll("input, select, textarea")).filter((field) => {
            if (field.disabled || field.type === "hidden") return false;
            return field.required;
        });

        return fields.every((field) => String(field.value || "").trim() !== "");
    };

    const isRasgosMiniStepTouched = (stepNumber) => {
        const fields = rasgosMiniStepFields[stepNumber] || [];
        if (!fields.length) return false;
        return fields.every((fieldName) => Boolean(miniFieldTouched[fieldName]));
    };

    const scheduleRasgosAutoAdvance = () => {
        if (!isRasgosMiniwizardActive()) return;
        if (!rasgosAutoAdvanceEnabled) return;
        clearMiniAdvanceTimer();

        miniAdvanceTimer = window.setTimeout(() => {
            if (!isRasgosMiniwizardActive()) return;
            if (!miniStepTouched[currentMiniStep]) return;
            if (!isRasgosMiniStepTouched(currentMiniStep)) return;
            if (!isRasgosMiniStepComplete(currentMiniStep)) return;

            if (currentMiniStep < 3) {
                goToRasgosMiniStep(currentMiniStep + 1);
                return;
            }

            goToStep(3);
        }, 650);
    };

    const handleRasgosFieldActivity = (fieldName = "") => {
        if (!isRasgosMiniwizardActive()) return;
        if (fieldName && Object.prototype.hasOwnProperty.call(miniFieldTouched, fieldName)) {
            miniFieldTouched[fieldName] = true;
        }
        miniStepTouched[currentMiniStep] = true;
        syncRasgosMiniUi();
        if (!rasgosAutoAdvanceEnabled) return;
        scheduleRasgosAutoAdvance();
    };

    const setRasgosChoiceValue = (targetName, targetValue) => {
        const field = getFieldByName(targetName);
        if (!field || field.disabled) return;

        field.value = targetValue;
        field.dispatchEvent(new Event("change", { bubbles: true }));
    };

    const syncModePanels = () => {
        const mode = getActiveMode();
        const modePanels = Array.from(form.querySelectorAll("[data-mode-panel]"));
        const stepThree = form.querySelector(".baiw-step[data-step='3']");

        modePanels.forEach((panel) => {
            const isActive = !!mode && panel.dataset.modePanel === mode;
            panel.hidden = !isActive;
            panel.setAttribute("aria-hidden", isActive ? "false" : "true");

            const fields = panel.querySelectorAll("input, select, textarea, button");
            fields.forEach((field) => {
                field.disabled = !isActive;
            });
        });

        if (referenceImageInput) {
            referenceImageInput.required = false;
        }

        if (stepThree) {
            stepThree.setAttribute("data-active-mode", mode || "none");
        }

        if (mode !== "rasgos") {
            resetRasgosMiniwizard();
        } else if (currentStep !== 2) {
            currentMiniStep = 1;
            resetRasgosTouchState();
            clearMiniAdvanceTimer();
            syncRasgosMiniUi();
        } else {
            syncRasgosMiniUi();
        }

        initChoicesSelects();
    };

    const initChoicesSelects = () => {
        if (typeof window.Choices !== "function") return;
        const selects = Array.from(form.querySelectorAll("select.baiw-enhanced-select"));
        selects.forEach((select) => {
            if (select._baiChoices) {
                if (select.disabled) {
                    select._baiChoices.disable();
                } else {
                    select._baiChoices.enable();
                }
                return;
            }

            if (select.disabled) return;

            try {
                const choices = new window.Choices(select, {
                    searchEnabled: false,
                    itemSelectText: "",
                    shouldSort: false,
                    allowHTML: false,
                    position: "bottom",
                });
                select._baiChoices = choices;

                const field = select.closest(".baiw-field");
                select.addEventListener("showDropdown", () => {
                    field?.classList.add("baiw-field--open");
                    root.classList.add("is-dropdown-open");
                });
                select.addEventListener("hideDropdown", () => {
                    field?.classList.remove("baiw-field--open");
                    const stillOpen = form.querySelector(".choices.is-open");
                    if (!stillOpen) {
                        root.classList.remove("is-dropdown-open");
                    }
                });
            } catch (error) {
                // Keep native select as fallback if library init fails.
            }
        });
    };

    const updateStepUi = () => {
        steps.forEach((step, index) => {
            const stepNumber = index + 1;
            const isActive = stepNumber === currentStep;
            step.hidden = !isActive;
            step.classList.toggle("is-active", isActive);
            step.setAttribute("aria-hidden", isActive ? "false" : "true");
        });

        stepIndicators.forEach((item) => {
            const indicatorStep = Number(item.dataset.stepIndicator || "1");
            item.classList.remove("is-active", "is-complete");
            item.removeAttribute("aria-current");

            if (indicatorStep < currentStep) {
                item.classList.add("is-complete");
            } else if (indicatorStep === currentStep) {
                item.classList.add("is-active");
                item.setAttribute("aria-current", "step");
            }
        });

        if (progressBar) {
            const denominator = Math.max(totalSteps - 1, 1);
            const progress = ((currentStep - 1) / denominator) * 100;
            progressBar.style.width = `${progress}%`;
        }

        if (prevBtn) {
            prevBtn.style.display = "inline-flex";
            const isMiniwizard = currentStep === 2 && getActiveMode() === "rasgos";
            prevBtn.textContent = currentStep === 1 ? "Cancelar" : (isMiniwizard ? "Volver" : "Anterior");
            prevBtn.classList.toggle("is-cancel", currentStep === 1);
        }
        if (nextBtn) {
            const hasMode = getActiveMode() !== "";
            const isRasgosManualMode = currentStep === 2 && getActiveMode() === "rasgos" && !rasgosAutoAdvanceEnabled;
            const shouldShow = currentStep !== totalSteps
                && (currentStep !== 1 || hasMode)
                && (currentStep !== 2 || getActiveMode() !== "rasgos" || isRasgosManualMode);
            nextBtn.style.display = shouldShow ? "inline-flex" : "none";
        }
        if (submitBtn) submitBtn.style.display = currentStep === totalSteps ? "inline-flex" : "none";

        root.classList.toggle("is-step-compact", currentStep > 1);
        syncRasgosMiniUi();

        clearInlineError();
    };

    const validateStep = (stepNumber) => {
        const step = steps[stepNumber - 1];
        if (!step) return true;

        const activeMode = getActiveMode();
        if (stepNumber === 2 && activeMode === "rasgos") {
            const miniStep = getMiniStep(currentMiniStep);
            if (!miniStep) return true;

            const fields = Array.from(miniStep.querySelectorAll("input, select, textarea"));
            for (const field of fields) {
                if (field.disabled || field.type === "hidden") continue;
                if (!field.required) continue;

                if (!String(field.value || "").trim()) {
                    const label = miniStep.querySelector(`label[for='${field.id}']`)?.textContent || "Este campo";
                    showInlineError(`${label} es obligatorio.`);
                    field.focus();
                    return false;
                }
            }

            return true;
        }

        if (stepNumber === 1 && !activeMode) {
            showInlineError("Selecciona un tipo de creacion para continuar.");
            return false;
        }
        const fields = Array.from(step.querySelectorAll("input, select, textarea"));

        for (const field of fields) {
            if (field.disabled || field.type === "hidden") continue;

            const modePanel = field.closest("[data-mode-panel]");
            if (modePanel && modePanel.dataset.modePanel !== activeMode) continue;

            if (!field.required) continue;

            if (field.type === "file") {
                if (!field.files || field.files.length === 0) {
                    showInlineError("Debes subir una imagen de referencia para continuar.");
                    field.focus();
                    return false;
                }
                continue;
            }

            if (!String(field.value || "").trim()) {
                const label = step.querySelector(`label[for='${field.id}']`)?.textContent || "Este campo";
                showInlineError(`${label} es obligatorio.`);
                field.focus();
                return false;
            }
        }

        return true;
    };

    const goToStep = (targetStep) => {
        const step = Math.max(1, Math.min(totalSteps, targetStep));
        const previousStep = currentStep;
        currentStep = step;
        if (currentStep === 2 && getActiveMode() === "rasgos" && previousStep !== 3) {
            currentMiniStep = 1;
            resetRasgosTouchState();
            clearMiniAdvanceTimer();
        } else if (currentStep === 2 && getActiveMode() === "rasgos" && previousStep === 3) {
            clearMiniAdvanceTimer();
            if (!rasgosAutoAdvanceLockedManual) {
                rasgosAutoAdvanceEnabled = true;
                scheduleRasgosAutoAdvance();
            } else {
                rasgosAutoAdvanceEnabled = false;
            }
        }
        updateStepUi();
        syncModePanels();
        window.setTimeout(scrollWizardTopIfNeeded, 120);
    };

    const showConfigStage = () => {
        if (configStage) configStage.hidden = false;
        if (resultStage) resultStage.hidden = true;
        root.classList.remove("is-loading-result", "is-result-ready");
    };

    const showResultStage = ({ keepConfig = false, isLoading = false } = {}) => {
        if (configStage) configStage.hidden = !keepConfig;
        if (resultStage) resultStage.hidden = false;
        root.classList.toggle("is-loading-result", Boolean(keepConfig && isLoading));
        root.classList.toggle("is-result-ready", !keepConfig && !isLoading);
    };

    const resetResult = () => {
        if (loading) loading.style.display = "none";
        if (resultSkeleton) resultSkeleton.hidden = true;
        if (errorPanel) errorPanel.hidden = true;
        if (errorBox) errorBox.textContent = "";
        if (imageWrapper) imageWrapper.style.display = "none";
        if (image) image.src = "";
        if (successActions) successActions.style.display = "none";
        if (secondaryActions) secondaryActions.style.display = "none";
        if (downloadBtn) downloadBtn.href = "";
    };

    const setLoadingResult = () => {
        resetResult();
        showResultStage({ keepConfig: true, isLoading: true });
        if (loading) loading.style.display = "block";
        if (resultSkeleton) resultSkeleton.hidden = false;
        if (errorPanel) errorPanel.hidden = true;
        window.setTimeout(() => {
            scrollToTopWithOffset(resultStage, 42);
        }, 140);
    };

    const setErrorResult = (message) => {
        if (isDesktop()) {
            showConfigStage();
            showInlineError(message || "Error inesperado");
            scrollWizardTopIfNeeded();
            return;
        }

        if (loading) loading.style.display = "none";
        if (resultSkeleton) resultSkeleton.hidden = true;
        if (errorBox) errorBox.textContent = message || "Error inesperado";
        if (errorPanel) errorPanel.hidden = false;
    };

    const setSuccessResult = (data) => {
        if (!data || !data.image_url) return;

        showResultStage();
        if (loading) loading.style.display = "none";
        if (resultSkeleton) resultSkeleton.hidden = true;
        if (errorPanel) errorPanel.hidden = true;
        if (imageWrapper) imageWrapper.style.display = "block";
        if (image) image.src = data.image_url;
        if (downloadBtn) downloadBtn.href = data.image_url;
        if (successActions) successActions.style.display = "grid";
        if (secondaryActions) secondaryActions.style.display = "grid";

        if (campaignBtn) {
            let finalUrl = campaignBaseUrl || campaignBtn.getAttribute("href") || "";
            if (finalUrl) {
                try {
                    const url = new URL(finalUrl, window.location.origin);
                    url.searchParams.set("modelo_id", String(data.id || ""));
                    url.searchParams.set("model_url", data.image_url);
                    finalUrl = url.toString();
                } catch (error) {
                    // Keep fallback URL.
                }
            }
            campaignBtn.setAttribute("href", finalUrl || "#");
        }

        window.setTimeout(() => {
            scrollToTopWithOffset(resultStage, 48);
        }, 100);
    };

    const insertHistorialItem = (d) => {
        const grid = document.getElementById("benditoai-historial-mockups");
        if (!grid || !d || !d.id) return;
        if (grid.querySelector(`.benditoai-historial-item[data-id='${d.id}']`)) return;

        const emptyMsg = document.getElementById("benditoai-empty-message");
        if (emptyMsg) emptyMsg.remove();

        const principalOutfit = d.principal_outfit || null;
        const iconDownload = `${benditoai_ajax.plugin_url}assets/images/icon-download.png`;
        const iconEdit = `${benditoai_ajax.plugin_url}assets/images/icon-edit.png`;
        const iconDelete = `${benditoai_ajax.plugin_url}assets/images/icon-delete.png`;
        const genero = escapeHtml(d.genero || "-");
        const edad = escapeHtml(d.edad || "-");
        const cuerpo = escapeHtml(d.cuerpo || "Personalizable");
        const estilo = escapeHtml(d.estilo || d.modo_label || "-");
        const modo = escapeHtml(d.modo_label || "-");
        const nacionalidad = escapeHtml(d.nacionalidad || "-");
        const colorOjos = escapeHtml(d.color_ojos || "-");
        const peinado = escapeHtml(d.peinado || "-");
        const colorPelo = escapeHtml(d.color_pelo || "-");
        const colorCejas = escapeHtml(d.color_cejas || "-");
        const nombreModelo = escapeHtml(d.nombre_modelo || "Modelo AI");
        const descripcionModelo = escapeHtml(d.descripcion_modelo || "Modelo AI listo para campanas de moda, redes y lookbooks.");
        const fecha = escapeHtml(d.fecha || "-");
        const publico = Number(d.perfil_publico) === 1 ? "Publico" : "Privado";
        const rawDisplayImage = (principalOutfit && principalOutfit.image_url) ? principalOutfit.image_url : d.image_url;
        const displayImage = escapeHtml(rawDisplayImage || "");
        const displayName = escapeHtml((principalOutfit && principalOutfit.nombre_outfit) ? principalOutfit.nombre_outfit : (d.nombre_modelo || "Modelo AI"));
        const noCacheUrl = `${displayImage}?t=${Date.now()}`;

        const outfitLimit = getOutfitLimit();
        const outfitWarning = escapeHtml(getOutfitWarning());
        const initialOutfitCount = principalOutfit && principalOutfit.image_url ? 1 : 0;
        const canSaveOutfit = initialOutfitCount < outfitLimit;

        const nuevoItem = `
            <div class="benditoai-historial-item" data-id="${d.id}" data-outfit-count="${initialOutfitCount}" data-outfit-limit="${outfitLimit}" data-outfit-warning="${outfitWarning}" data-principal-outfit-id="${escapeHtml(principalOutfit?.id || "")}" data-principal-outfit-image="${escapeHtml(principalOutfit?.image_url || d.image_url)}">
                <p class="benditoai-historial-name">${nombreModelo}</p>
                <div class="benditoai-img-wrap">
                    <img src="${noCacheUrl}" alt="${displayName}" class="benditoai-historial-img" />
                    <button
                        type="button"
                        class="benditoai-save-outfit-btn"
                        data-modelo-id="${d.id}"
                        ${canSaveOutfit ? "" : "disabled"}
                        aria-disabled="${canSaveOutfit ? "false" : "true"}"
                    >
                        <i class="far fa-bookmark" aria-hidden="true"></i>
                        <span>Guardar outfit</span>
                    </button>
                    <div class="benditoai-inline-edit" hidden>
                        <div class="benditoai-inline-edit-surface">
                            <label class="benditoai-inline-edit-label">Que deseas cambiar</label>
                            <textarea class="benditoai-inline-edit-text" placeholder="Ej: cambia solo la chaqueta por una bomber negra."></textarea>
                            <div class="benditoai-inline-edit-style" hidden>
                                <span class="benditoai-inline-edit-style-label">Estilo</span>
                                <span class="benditoai-inline-edit-style-value"></span>
                            </div>
                            <input type="hidden" class="benditoai-inline-edit-selected-style" value="">
                            <input type="hidden" class="benditoai-inline-edit-selected-style-id" value="">
                            <div class="benditoai-inline-edit-ref-block">
                                <p class="benditoai-inline-edit-ref-help">
                                    Opcional: sube una imagen de prenda para usarla como referencia. El estilo seleccionado se aplicara por separado.
                                </p>
                                <input type="file" class="benditoai-inline-edit-ref-file" accept="image/png,image/jpeg,image/webp" hidden>
                                <button type="button" class="benditoai-inline-edit-ref-trigger">
                                    <i class="fas fa-plus" aria-hidden="true"></i>
                                    <span class="benditoai-inline-edit-ref-trigger-preview" hidden>
                                        <img src="" alt="" class="benditoai-inline-edit-ref-trigger-preview-img" />
                                    </span>
                                    <span class="benditoai-inline-edit-ref-trigger-text">Una prenda de vestir (opcional)</span>
                                </button>
                                <p class="benditoai-inline-edit-ref-name"></p>
                            </div>
                            <div class="benditoai-inline-edit-submit-block">
                                <div class="benditoai-inline-edit-actions">
                                    <button type="button" class="benditoai-inline-edit-submit">Enviar</button>
                                    <button type="button" class="benditoai-inline-edit-cancel">Volver</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="benditoai-action-buttons">
                        <div class="hoverselect">
                            <a href="${noCacheUrl}" download class="benditoai-btn benditoai-btn--download benditoai-icon-btn" aria-label="Descargar modelo">
                                <img src="${iconDownload}" class="benditoai-btn-icon" alt="" aria-hidden="true" />
                            </a>
                        </div>
                        <div class="hoverselect">
                            <button class="benditoai-edit-modelo-btn benditoai-icon-btn" data-id="${d.id}" data-image="${displayImage}" aria-label="Editar modelo">
                                <img src="${iconEdit}" class="benditoai-btn-icon" alt="" aria-hidden="true" />
                            </button>
                        </div>
                        <div class="hoverselect">
                            <button class="benditoai-delete-modelo-btn benditoai-action-btn benditoai-icon-btn" data-id="${d.id}" aria-label="Eliminar modelo">
                                <img src="${iconDelete}" class="benditoai-btn-icon" alt="" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="benditoai-use-campaign-btn"
                        data-modelo-id="${d.id}"
                        data-modelo-nombre="${displayName}"
                        data-modelo-image="${displayImage}"
                        data-outfit-id="${escapeHtml(principalOutfit?.id || "")}"
                        data-outfit-tag="principal"
                        data-source="principal_outfit"
                    >
                        Usar para campana
                    </button>
                    ${renderSavedOutfitsRailMarkup(d)}
                </div>
                <div class="benditoai-desktop-model-panel">
                    <div class="benditoai-desktop-model-head">
                        <div class="benditoai-desktop-model-head-row">
                            <h3>${nombreModelo}</h3>
                            <button
                                type="button"
                                class="benditoai-panel-title-edit benditoai-edit-modelo-btn"
                                data-id="${d.id}"
                                data-image="${displayImage}"
                                aria-label="Editar modelo"
                            >
                                <i class="fas fa-pen" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="benditoai-desktop-model-badges">
                            <span class="benditoai-model-badge benditoai-model-badge--status"><i class="fas fa-lock" aria-hidden="true"></i>${Number(d.perfil_publico) === 1 ? "Activo" : "Privado"}</span>
                            <span class="benditoai-model-badge"><i class="fas fa-tag" aria-hidden="true"></i>${estilo}</span>
                            <span class="benditoai-model-badge benditoai-model-badge--outfit-state" data-active-outfit-badge><i class="fas fa-shirt" aria-hidden="true"></i><span data-active-outfit-label>Principal</span></span>
                            <span class="benditoai-model-badge"><i class="far fa-check-circle" aria-hidden="true"></i>Listo para campana</span>
                        </div>
                        <div class="benditoai-desktop-model-divider"></div>
                        <div class="benditoai-desktop-model-intro">
                            <h4>Viste este modelo con tu marca</h4>
                            <p>Personalizalo con tu ropa, accesorios y estilo para crear imagenes unicas que promocionen tus productos.</p>
                        </div>
                    </div>
                    <div class="benditoai-desktop-model-actions">
                        <div class="benditoai-desktop-campaign-spotlight">
                            <div class="benditoai-desktop-campaign-spotlight-main">
                                <span class="benditoai-desktop-campaign-spotlight-icon" aria-hidden="true"><i class="fas fa-rocket"></i></span>
                                <div class="benditoai-desktop-campaign-spotlight-copy">
                                    <h5>¿Listo para promocionar tus productos?</h5>
                                    <p>Lanza una campana con este modelo</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="benditoai-use-campaign-btn benditoai-use-campaign-btn--panel cards-skills-panel-cta"
                                data-modelo-id="${d.id}"
                                data-modelo-nombre="${displayName}"
                                data-modelo-image="${displayImage}"
                                data-outfit-id="${escapeHtml(principalOutfit?.id || "")}"
                                data-outfit-tag="principal"
                                data-source="principal_outfit"
                            >
                                Lanzar campana <span aria-hidden="true">&rarr;</span>
                            </button>
                        </div>
                        <h4 class="benditoai-desktop-manage-title">Gestiona tu modelo</h4>
                        <div class="benditoai-desktop-model-secondary">
                            <button class="benditoai-edit-modelo-btn benditoai-desktop-tool-card" data-id="${d.id}" data-image="${displayImage}">
                                <span class="benditoai-desktop-tool-title"><i class="fas fa-pen" aria-hidden="true"></i><span>Editar modelo</span></span>
                                <span class="benditoai-desktop-tool-desc">Cambia la apariencia, ropa, accesorios o detalles del modelo.</span>
                                <span class="benditoai-desktop-tool-arrow" aria-hidden="true">&rarr;</span>
                            </button>
                            <a href="${noCacheUrl}" download class="benditoai-desktop-tool-card">
                                <span class="benditoai-desktop-tool-title"><i class="fas fa-download" aria-hidden="true"></i><span>Descargar modelo</span></span>
                                <span class="benditoai-desktop-tool-desc">Descarga las imagenes del modelo para usar en tus proyectos.</span>
                                <span class="benditoai-desktop-tool-arrow" aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                        ${renderOutfitRailMarkup()}
                    </div>
                    <div class="benditoai-desktop-model-meta">
                        <div class="benditoai-desktop-model-box">
                            <h4>Atributos</h4>
                            <p><span>Complexion</span><strong>${cuerpo}</strong></p>
                            <p><span>Edad aparente</span><strong>${edad}</strong></p>
                            <p><span>Origen</span><strong>${nacionalidad}</strong></p>
                        </div>
                        <div class="benditoai-desktop-model-box">
                            <h4>Campanas</h4>
                            <p><span>Estado</span><strong>Disponible</strong></p>
                            <p><span>Uso sugerido</span><strong>Moda y redes</strong></p>
                            <p><span>Flujo</span><strong>${modo}</strong></p>
                        </div>
                    </div>
                </div>
                <div class="benditoai-edit-decision" hidden>
                    <button type="button" class="benditoai-edit-add-btn">Agregar</button>
                    <button type="button" class="benditoai-edit-replace-btn">Reemplazar</button>
                </div>
                <button class="benditoai-toggle-info">Ver detalles</button>
                <div class="benditoai-historial-info" style="display:none;">
                    <p><strong>Flujo:</strong> ${modo}</p>
                    <p><strong>Genero:</strong> ${genero}</p>
                    <p><strong>Edad:</strong> ${edad}</p>
                    <p><strong>Estilo:</strong> ${estilo}</p>
                    <p><strong>Nacionalidad:</strong> ${nacionalidad}</p>
                    <p><strong>Ojos:</strong> ${colorOjos}</p>
                    <p><strong>Pelo:</strong> ${colorPelo}</p>
                    <p><strong>Cejas:</strong> ${colorCejas}</p>
                    <p><strong>Visibilidad:</strong> ${publico}</p>
                    <p><strong>Creado:</strong> ${fecha}</p>
                </div>
            </div>
        `;

        grid.insertAdjacentHTML("afterbegin", nuevoItem);
        grid.dispatchEvent(new CustomEvent("benditoai:historial-updated"));
        document.dispatchEvent(new CustomEvent("benditoai:historial-updated"));
        if (typeof window.benditoaiRefreshHistorialPagination === "function") {
            window.benditoaiRefreshHistorialPagination();
        }
    };

    const submitRequest = () => {
        const data = new FormData(form);
        data.append("action", "benditoai_generar_modelo_ai");

        clearInlineError();
        setButtonsDisabled(true);
        setSubmitLoadingState(true);
        isSubmitting = true;
        setLoadingResult();

        fetch(benditoai_ajax.ajax_url, {
            method: "POST",
            body: data,
        })
            .then((res) => res.json())
            .then((res) => {
                setButtonsDisabled(false);
                setSubmitLoadingState(false);
                isSubmitting = false;

                if (!res.success) {
                    const errorMsg = typeof res.data === "string"
                        ? res.data
                        : res?.data?.message || "Error inesperado";
                    setErrorResult(errorMsg);
                    return;
                }

                const d = res.data || {};
                lastSuccess = d;

                setSuccessResult(d);
                insertHistorialItem(d);

                try {
                    const principalOutfit = d.principal_outfit || null;
                    const selectedImage = (principalOutfit && principalOutfit.image_url) ? principalOutfit.image_url : d.image_url;
                    localStorage.setItem("benditoai_selected_model", JSON.stringify({
                        id: d.id,
                        name: d.nombre_modelo,
                        image_url: selectedImage || "",
                    }));
                } catch (storageError) {
                    // Ignore storage errors.
                }

                if (d.tokens !== undefined && typeof benditoaiActualizarTokensInstantaneo === "function") {
                    benditoaiActualizarTokensInstantaneo(d.tokens);
                }
            })
            .catch(() => {
                setButtonsDisabled(false);
                setSubmitLoadingState(false);
                isSubmitting = false;
                setErrorResult("Error inesperado. Intenta de nuevo.");
            });
    };

    const resetWizardToStart = () => {
        form.reset();
        lastSuccess = null;
        currentStep = 1;
        currentMiniStep = 1;
        rasgosAutoAdvanceEnabled = true;
        rasgosAutoAdvanceLockedManual = false;
        resetRasgosTouchState();
        clearMiniAdvanceTimer();

        if (referenceSmart) referenceSmart.classList.remove("is-has-file");
        if (referenceFileText) {
            referenceFileText.hidden = false;
            referenceFileText.textContent = "Subir imagen";
        }
        if (referenceFileMeta) referenceFileMeta.hidden = true;
        if (referenceFileName) referenceFileName.textContent = "";
        if (referenceFilePreview) {
            referenceFilePreview.hidden = true;
            referenceFilePreview.src = "";
        }

        syncVisibilityToggle();
        syncModePanels();
        syncNombreModeloCount();
        updateStepUi();
        clearInlineError();
        showConfigStage();
    resetResult();
    window.scrollTo({ top: root.offsetTop - 30, behavior: "smooth" });
};

    rasgosConfirmOk?.addEventListener("click", () => {
        closeRasgosConfirm(true);
    });

    rasgosConfirmCancelButtons.forEach((button) => {
        button.addEventListener("click", () => {
            closeRasgosConfirm(false);
        });
    });

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") return;
        if (!rasgosConfirmModal || rasgosConfirmModal.hidden) return;
        closeRasgosConfirm(false);
    });

    prevBtn?.addEventListener("click", async () => {
        if (isSubmitting) return;
        if (currentStep === 1) {
            window.location.href = cancelUrl;
            return;
        }
        if (currentStep === 3 && getActiveMode() === "rasgos") {
            if (!(await confirmDisableRasgosAutoAdvance())) return;
            goToStep(2);
            return;
        }
        if (currentStep === 2 && getActiveMode() === "rasgos") {
            if (currentMiniStep > 1) {
                if (!(await confirmDisableRasgosAutoAdvance())) return;
                goToRasgosMiniStep(currentMiniStep - 1);
                return;
            }
            if (!(await confirmDisableRasgosAutoAdvance())) return;
            goToStep(1);
            return;
        }
        goToStep(currentStep - 1);
    });

    nextBtn?.addEventListener("click", () => {
        if (isSubmitting) return;
        if (!validateStep(currentStep)) return;
        if (currentStep === 2 && getActiveMode() === "rasgos") {
            if (currentMiniStep < 3) {
                goToRasgosMiniStep(currentMiniStep + 1);
                return;
            }
            goToStep(3);
            return;
        }
        goToStep(currentStep + 1);
    });

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        if (isSubmitting) return;
        if (!validateStep(currentStep)) return;
        if (currentStep < totalSteps) {
            if (currentStep === 2 && getActiveMode() === "rasgos") {
                if (currentMiniStep < 3) {
                    goToRasgosMiniStep(currentMiniStep + 1);
                    return;
                }
                goToStep(3);
                return;
            }

            goToStep(currentStep + 1);
            return;
        }

        submitRequest();
    });

    modeInputs.forEach((input) => {
        input.addEventListener("change", () => {
            syncModePanels();
            updateStepUi();
            clearInlineError();
        });
    });

    [edadRange, alturaRange, pesoRange].forEach((range) => {
        range?.addEventListener("input", () => {
            syncRangos();
            handleRasgosFieldActivity();
        });
    });

    rasgosFieldNames.forEach((fieldName) => {
        const field = document.getElementById(rasgosFieldIdMap[fieldName] || "");
        if (!field) return;

        field.addEventListener(field.tagName === "INPUT" ? "input" : "change", () => {
            syncRasgosMiniUi();
            handleRasgosFieldActivity(fieldName);
        });
    });

    form.addEventListener("click", (event) => {
        const choice = event.target.closest("[data-choice-target]");
        if (!choice || !isRasgosMiniwizardActive()) return;
        event.preventDefault();
        setRasgosChoiceValue(choice.dataset.choiceTarget || "", choice.dataset.choiceValue || "");
    });

    publicToggle?.addEventListener("change", syncVisibilityToggle);
    nombreModeloInput?.addEventListener("input", syncNombreModeloCount);

    referenceImageInput?.addEventListener("change", () => {
        const file = referenceImageInput.files && referenceImageInput.files[0];

        if (!file) {
            if (referenceSmart) referenceSmart.classList.remove("is-has-file");
            if (referenceFileText) {
                referenceFileText.hidden = false;
                referenceFileText.textContent = "Subir imagen";
            }
            if (referenceFileMeta) referenceFileMeta.hidden = true;
            if (referenceFileName) referenceFileName.textContent = "";
            if (referenceFilePreview) {
                referenceFilePreview.hidden = true;
                referenceFilePreview.src = "";
            }
            return;
        }

        if (referenceSmart) referenceSmart.classList.add("is-has-file");
        if (referenceFileText) referenceFileText.hidden = true;
        if (referenceFileMeta) referenceFileMeta.hidden = false;
        if (referenceFileName) referenceFileName.textContent = file.name;
        if (!referenceFilePreview) return;
        referenceFilePreview.hidden = true;

        const reader = new FileReader();
        reader.onload = (event) => {
            referenceFilePreview.src = event.target?.result || "";
            referenceFilePreview.hidden = false;
        };
        reader.onerror = () => {
            referenceFilePreview.hidden = true;
            referenceFilePreview.src = "";
        };
        reader.readAsDataURL(file);
    });

    retryBtn?.addEventListener("click", () => {
        if (isSubmitting) return;
        submitRequest();
    });

    createAnotherBtn?.addEventListener("click", () => {
        if (isSubmitting) return;
        resetWizardToStart();
    });

    shareBtn?.addEventListener("click", async () => {
        if (!lastSuccess?.image_url) return;

        const shareUrl = lastSuccess.image_url;
        if (navigator.share) {
            try {
                await navigator.share({
                    title: lastSuccess.nombre_modelo || "Modelo AI",
                    text: "Mira este modelo que genere con BenditoAI",
                    url: shareUrl,
                });
            } catch (error) {
                // Ignore user-cancel share.
            }
            return;
        }

        if (navigator.clipboard?.writeText) {
            try {
                await navigator.clipboard.writeText(shareUrl);
            } catch (error) {
                // Ignore clipboard failures.
            }
        }
    });

    editBtn?.addEventListener("click", () => {
        if (!lastSuccess?.id) return;
        const item = document.querySelector(`.benditoai-historial-item[data-id='${lastSuccess.id}']`);
        if (!item) return;

        item.scrollIntoView({ behavior: "smooth", block: "center" });
        const targetEditButton = item.querySelector(".benditoai-edit-modelo-btn");
        if (targetEditButton) {
            targetEditButton.click();
        }
    });

    syncVisibilityToggle();
    syncModePanels();
    initChoicesSelects();
    syncNombreModeloCount();
    syncRangos();
    updateStepUi();
    resetResult();
    showConfigStage();
});


