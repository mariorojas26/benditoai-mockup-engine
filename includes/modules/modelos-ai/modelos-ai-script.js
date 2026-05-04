document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("benditoai-form-modelo-ai");
    if (!form) return;

    const uploadInput = document.getElementById("benditoai-upload-image-input");
    const uploadTrigger = document.getElementById("benditoai-upload-trigger");
    const openGeneratorBtn = document.getElementById("benditoai-open-generator");

    const mainStageImage = document.getElementById("benditoai-modelo-stage-image");
    const mainStagePlaceholder = document.getElementById("benditoai-modelo-stage-placeholder");

    const referenceSourceInput = document.getElementById("benditoai-reference-source");
    const referenceImageUrlInput = document.getElementById("benditoai-reference-image-url");
    const generatedPromptInput = document.getElementById("benditoai-generated-prompt");
    const traitsPayloadInput = document.getElementById("benditoai-traits-payload");
    const promptPreviewHiddenInput = document.getElementById("benditoai-prompt-preview-hidden");

    const modelNameInput = document.getElementById("benditoai-nombre-modelo");
    const modelDescInput = document.getElementById("benditoai-descripcion-modelo");
    const nameCounter = document.getElementById("benditoai-nombre-counter");
    const descCounter = document.getElementById("benditoai-descripcion-counter");

    const createBtn = document.getElementById("benditoai-create-model-btn");
    const loading = document.getElementById("benditoai-modelo-loading");
    const errorBox = document.getElementById("benditoai-modelo-error");
    const promptDebugBox = document.getElementById("benditoai-prompt-debug");
    const promptPreview = document.getElementById("benditoai-prompt-preview");

    const modal = document.getElementById("benditoai-influencer-modal");
    const closeGeneratorBtn = document.getElementById("benditoai-close-generator");
    const modalBackdrops = document.querySelectorAll("[data-close-modal='1']");

    const modalImage = document.getElementById("benditoai-modal-image");
    const modalPlaceholder = document.getElementById("benditoai-modal-placeholder");
    const selectGeneratedBtn = document.getElementById("benditoai-select-generated-image");

    const randomizeBtn = document.getElementById("benditoai-randomize-traits");
    const resetTraitsBtn = document.getElementById("benditoai-reset-traits");
    const generateInfluencerBtn = document.getElementById("benditoai-generate-influencer");

    const ageRange = document.getElementById("benditoai-age-range");
    const heightRange = document.getElementById("benditoai-height-range");
    const weightRange = document.getElementById("benditoai-weight-range");
    const ageValue = document.getElementById("benditoai-age-value");
    const heightValue = document.getElementById("benditoai-height-value");
    const weightValue = document.getElementById("benditoai-weight-value");

    const countryMain = document.getElementById("benditoai-country-main");
    const countrySecondary = document.getElementById("benditoai-country-secondary");
    const traitConstitucion = document.getElementById("benditoai-trait-constitucion");
    const traitOjos = document.getElementById("benditoai-trait-ojos");
    const traitPeinado = document.getElementById("benditoai-trait-peinado");
    const traitColorPelo = document.getElementById("benditoai-trait-color-pelo");

    const hoyuelosInput = document.getElementById("benditoai-hoyuelos");
    const barbaInput = document.getElementById("benditoai-barba");
    const bronceadoInput = document.getElementById("benditoai-bronceado");
    const customDetailsInput = document.getElementById("benditoai-custom-details");

    const generatorError = document.getElementById("benditoai-generator-error");
    const generatorLoading = document.getElementById("benditoai-generator-loading");
    const modalControlsCol = document.querySelector(".benditoai-modal-controls-col");

    let localPreviewObjectUrl = "";
    let currentGeneratorStep = 1;
    let generatorWizardReady = false;
    let wizardNudgeTimer = null;
    let activeFloatingTraitSelect = null;

    const influencerState = {
        imageUrl: "",
        prompt: "",
        traits: {}
    };

    const defaultModalPlaceholderMarkup = modalPlaceholder ? modalPlaceholder.innerHTML : "";

    function updateCounters() {
        if (nameCounter) nameCounter.textContent = `${modelNameInput.value.length} / 32`;
        if (descCounter) descCounter.textContent = `${modelDescInput.value.length} / 512`;
    }

    function hideError() {
        if (!errorBox) return;
        errorBox.style.display = "none";
        errorBox.style.color = "";
        errorBox.textContent = "";
    }

    function showError(message) {
        if (!errorBox) return;
        errorBox.style.color = "";
        errorBox.textContent = `⚠ ${message}`;
        errorBox.style.display = "block";
    }

    function showSuccess(message) {
        if (!errorBox) return;
        errorBox.style.color = "#0f7a44";
        errorBox.textContent = message;
        errorBox.style.display = "block";
    }

    function showPromptPreview(promptText) {
        if (!promptDebugBox || !promptPreview) return;
        if (!promptText || !String(promptText).trim()) return;

        promptPreview.value = String(promptText).trim();
        promptDebugBox.style.display = "block";
    }

    function getTraitsFromPayload() {
        if (!traitsPayloadInput || !traitsPayloadInput.value) return {};

        try {
            const parsed = JSON.parse(traitsPayloadInput.value);
            return parsed && typeof parsed === "object" ? parsed : {};
        } catch (e) {
            return {};
        }
    }

    function buildPromptPreviewForPayload() {
        const nombreModelo = (modelNameInput.value || "").trim();
        const descripcionModelo = (modelDescInput.value || "").trim();
        const generatedPrompt = (generatedPromptInput.value || "").trim();
        const traitsPayload = getTraitsFromPayload();

        const traitMap = [
            ["genero", "Gender"],
            ["edad", "Age"],
            ["altura", "Height (cm)"],
            ["peso", "Weight (kg)"],
            ["pais", "Country"],
            ["pais2", "Country 2"],
            ["constitucion", "Body build"],
            ["ojos", "Eyes"],
            ["peinado", "Hairstyle"],
            ["color_pelo", "Hair color"]
        ];

        const traitLines = [];

        traitMap.forEach(([key, label]) => {
            const value = (traitsPayload[key] || "").toString().trim();
            if (value && value !== "Ninguno") {
                traitLines.push(`- ${label}: ${value}`);
            }
        });

        if (traitsPayload.hoyuelos === "1") traitLines.push("- Dimples: yes");
        if (traitsPayload.barba === "1") traitLines.push("- Beard: yes");
        if (traitsPayload.bronceado === "1") traitLines.push("- Tan: yes");

        const detalles = (traitsPayload.detalles || "").toString().trim();
        if (detalles) traitLines.push(`- Custom details: ${detalles}`);

        const traitsBlock = traitLines.length > 0
            ? traitLines.join("\n")
            : "- Keep same identity from reference image.";

        const baseGeneratedBlock = generatedPrompt
            ? `\nBase style from prior influencer generation:\n${generatedPrompt}\n`
            : "";

        return `
Create a high-end photorealistic human avatar using the provided reference image.

Primary objective:
- Keep the same core identity and facial consistency from the reference.
- Produce a clean model image ready for mockup generation.

Model name:
${nombreModelo}

User description:
${descripcionModelo}

Requested traits:
${traitsBlock}
${baseGeneratedBlock}
Composition and quality rules:
- single person only
- full body visible (head to feet)
- centered subject
- soft studio lighting
- clean neutral background
- no text, no logos, no watermarks
- photorealistic skin and fabric detail
- vertical 9:16 composition
- high detail 4k quality
`.trim();
    }

    function buildInfluencerPromptPreviewForPayload(traits) {
        const genero = (traits?.genero || "").toString().trim();
        const edad = (traits?.edad || "").toString().trim();
        const altura = (traits?.altura || "").toString().trim();
        const peso = (traits?.peso || "").toString().trim();
        const pais = (traits?.pais || "").toString().trim();
        const pais2 = (traits?.pais2 || "").toString().trim();
        const constitucion = (traits?.constitucion || "").toString().trim();
        const ojos = (traits?.ojos || "").toString().trim();
        const peinado = (traits?.peinado || "").toString().trim();
        const colorPelo = (traits?.color_pelo || "").toString().trim();
        const hoyuelos = traits?.hoyuelos === "1" ? "yes" : "no";
        const barba = traits?.barba === "1" ? "yes" : "no";
        const bronceado = traits?.bronceado === "1" ? "yes" : "no";
        const detalles = (traits?.detalles || "").toString().trim();

        const mixPaises = pais2 && pais2 !== "Ninguno" ? `${pais} + ${pais2}` : pais;

        return `
Ultra realistic influencer portrait.

Single person only.

Character profile:
- Gender: ${genero}
- Age: ${edad}
- Height: ${altura} cm
- Weight: ${peso} kg
- National/cultural style influence: ${mixPaises}
- Body build: ${constitucion}
- Eye color/type: ${ojos}
- Hairstyle: ${peinado}
- Hair color: ${colorPelo}
- Dimples: ${hoyuelos}
- Beard: ${barba}
- Sun tan: ${bronceado}

Additional custom details:
${detalles}

Image direction:
- Full body, head-to-feet visible
- Subject centered
- Fashion-forward influencer look
- Clean soft studio background
- Natural skin texture, photorealistic detail
- High quality lighting and depth

Critical rules:
- only ONE person
- no group
- no text overlays
- no logos
- no furniture

Output:
- vertical image 9:16
- photorealistic
- 4k quality
`.trim();
    }

    function hideGeneratorError() {
        if (generatorError) {
            generatorError.style.display = "none";
            generatorError.textContent = "";
        }

        if (modalPlaceholder && modalImage && modalImage.style.display !== "block") {
            modalPlaceholder.classList.remove("is-error", "is-loading");
            modalPlaceholder.innerHTML = defaultModalPlaceholderMarkup;
            modalPlaceholder.style.display = "flex";
        }
    }

    function showGeneratorError(message) {
        const safeMessage = (message || "Error inesperado al generar la referencia.").toString().trim();

        if (generatorError) {
            generatorError.style.display = "none";
            generatorError.textContent = "";
        }

        if (!modalPlaceholder || !modalImage) return;

        modalImage.src = "";
        modalImage.style.display = "none";
        modalPlaceholder.classList.remove("is-loading");
        modalPlaceholder.classList.add("is-error");
        modalPlaceholder.style.display = "flex";
        modalPlaceholder.innerHTML = `
<div class="benditoai-ai-state benditoai-ai-state--error" aria-live="assertive">
    <i class="fa-solid fa-circle-exclamation"></i>
    <p class="benditoai-ai-state__title">No se pudo generar la imagen</p>
    <p class="benditoai-ai-state__message">${escapeHtml(safeMessage)}</p>
</div>`;
        selectGeneratedBtn.disabled = true;
    }

    function showGeneratorSkeleton() {
        if (!modalPlaceholder || !modalImage) return;

        modalImage.src = "";
        modalImage.style.display = "none";
        modalPlaceholder.classList.remove("is-error");
        modalPlaceholder.classList.add("is-loading");
        modalPlaceholder.style.display = "flex";
        window.BenditoAIUX.skeleton.render(modalPlaceholder, {
            label: "Generando tu referencia...",
            lines: 2
        });
        selectGeneratedBtn.disabled = true;
    }

    function setMainPreview(url) {
        if (!mainStageImage || !mainStagePlaceholder) return;

        mainStageImage.src = url;
        mainStageImage.style.display = "block";
        mainStagePlaceholder.style.display = "none";
    }

    function clearMainPreview() {
        if (!mainStageImage || !mainStagePlaceholder) return;

        mainStageImage.src = "";
        mainStageImage.style.display = "none";
        mainStagePlaceholder.style.display = "flex";
    }

    function hasReferenceReady() {
        if (referenceSourceInput.value === "ai" && referenceImageUrlInput.value) return true;
        if (referenceSourceInput.value === "upload" && uploadInput.files && uploadInput.files.length > 0) return true;
        return false;
    }

    function updateCreateState() {
        const canCreate = modelNameInput.value.trim().length > 0 && hasReferenceReady();
        createBtn.disabled = !canCreate;
    }

    function setGeneratorStep(step) {
        if (!modalControlsCol || !generatorWizardReady) return;

        const safeStep = Math.min(3, Math.max(1, parseInt(step, 10) || 1));
        currentGeneratorStep = safeStep;

        modalControlsCol.querySelectorAll(".benditoai-generator-step").forEach((btn) => {
            const isActive = Number(btn.dataset.stepTarget) === safeStep;
            btn.classList.toggle("is-active", isActive);
            btn.setAttribute("aria-current", isActive ? "step" : "false");
        });

        modalControlsCol.querySelectorAll(".benditoai-generator-panel").forEach((panel) => {
            const isActive = Number(panel.dataset.stepPanel) === safeStep;
            panel.classList.toggle("is-active", isActive);
            panel.style.display = isActive ? "block" : "none";
        });

        if (generateInfluencerBtn) {
            const canGenerate = safeStep === 3;
            generateInfluencerBtn.disabled = false;
            generateInfluencerBtn.classList.toggle("is-locked", !canGenerate);
            generateInfluencerBtn.title = canGenerate ? "" : "Llega al paso 3 para generar";
        }
    }

    function scrollWizardTopSmooth() {
        if (window.innerWidth > 900) return;

        const modalBody = modal?.querySelector(".benditoai-influencer-body");
        if (!modalBody) return;

        modalBody.scrollTo({ top: 0, behavior: "smooth" });
    }

    function refreshTraitFocusOverlay() {
        if (!modalControlsCol) return;

        const hasOpenSelect = !!modalControlsCol.querySelector(".benditoai-trait-select.is-open");
        modalControlsCol.classList.toggle("has-open-select", hasOpenSelect);
    }

    function centerTraitSelectOnMobile(selectEl) {
        if (!selectEl || window.innerWidth > 900) return;

        const modalBody = modal?.querySelector(".benditoai-influencer-body");
        if (!modalBody) return;

        setTimeout(() => {
            const bodyRect = modalBody.getBoundingClientRect();
            const selectRect = selectEl.getBoundingClientRect();
            const currentTop = modalBody.scrollTop;
            const selectTopInBody = selectRect.top - bodyRect.top + currentTop;
            const targetScrollTop = Math.max(0, selectTopInBody - (modalBody.clientHeight * 0.28));

            modalBody.scrollTo({
                top: targetScrollTop,
                behavior: "smooth"
            });
        }, 40);
    }

    function detachTraitMenu(selectEl) {
        if (!selectEl) return;
        const menu = selectEl.querySelector(".benditoai-trait-select-menu");
        if (!menu) return;

        menu.classList.remove("is-floating");
        menu.style.position = "";
        menu.style.top = "";
        menu.style.left = "";
        menu.style.width = "";
        menu.style.maxHeight = "";
        menu.style.zIndex = "";
    }

    function positionTraitMenuFloating(selectEl) {
        if (!selectEl) return;
        const menu = selectEl.querySelector(".benditoai-trait-select-menu");
        const trigger = selectEl.querySelector("[data-trait-trigger]");
        if (!menu || !trigger) return;

        const rect = trigger.getBoundingClientRect();
        const viewportPadding = 8;
        const maxWidth = window.innerWidth - (viewportPadding * 2);
        const width = Math.min(rect.width, maxWidth);
        const left = Math.min(Math.max(viewportPadding, rect.left), window.innerWidth - width - viewportPadding);
        const top = rect.bottom + 6;
        const availableHeight = Math.max(140, window.innerHeight - top - viewportPadding);

        menu.classList.add("is-floating");
        menu.style.position = "fixed";
        menu.style.top = `${top}px`;
        menu.style.left = `${left}px`;
        menu.style.width = `${width}px`;
        menu.style.maxHeight = `${Math.min(availableHeight, 280)}px`;
        menu.style.zIndex = "20000";
    }

    function nudgeWizardToNext() {
        if (!modalControlsCol) return;

        const nextBtn = modalControlsCol.querySelector(`[data-step-next="${currentGeneratorStep + 1}"]`);
        if (!nextBtn) return;

        nextBtn.classList.add("is-nudge");
        if (wizardNudgeTimer) clearTimeout(wizardNudgeTimer);
        wizardNudgeTimer = setTimeout(() => nextBtn.classList.remove("is-nudge"), 1300);
    }

    function setupGeneratorWizard() {
        if (!modalControlsCol || modalControlsCol.querySelector(".benditoai-generator-steps")) return;

        const genderBlock = document.getElementById("benditoai-gender-row")?.closest(".benditoai-field-block");
        const ageBlock = ageRange?.closest(".benditoai-field-block");
        const heightBlock = heightRange?.closest(".benditoai-field-block");
        const weightBlock = weightRange?.closest(".benditoai-field-block");
        const countryBlock = countryMain?.closest(".benditoai-two-cols");
        const constitucionBlock = (
            traitConstitucion?.closest(".benditoai-two-cols")
            || modalControlsCol.querySelector('[data-trait-select="constitucion"]')?.closest(".benditoai-two-cols")
        );
        const peinadoBlock = (
            traitPeinado?.closest(".benditoai-two-cols")
            || modalControlsCol.querySelector('[data-trait-select="peinado"]')?.closest(".benditoai-two-cols")
        );
        const checksBlock = modalControlsCol.querySelector(".benditoai-check-row");
        const detailsBlock = customDetailsInput?.closest(".benditoai-field-block");

        if (!genderBlock || !ageBlock || !heightBlock || !weightBlock || !countryBlock || !constitucionBlock || !peinadoBlock || !checksBlock || !detailsBlock || !randomizeBtn) {
            return;
        }

        const steps = document.createElement("div");
        steps.className = "benditoai-generator-steps";
        steps.innerHTML = `
            <button type="button" class="benditoai-generator-step is-active" data-step-target="1"><span>1</span> Base</button>
            <button type="button" class="benditoai-generator-step" data-step-target="2"><span>2</span> Rasgos</button>
            <button type="button" class="benditoai-generator-step" data-step-target="3"><span>3</span> Final</button>
        `;

        const panel1 = document.createElement("section");
        panel1.className = "benditoai-generator-panel is-active";
        panel1.dataset.stepPanel = "1";

        const panel2 = document.createElement("section");
        panel2.className = "benditoai-generator-panel";
        panel2.dataset.stepPanel = "2";

        const panel3 = document.createElement("section");
        panel3.className = "benditoai-generator-panel";
        panel3.dataset.stepPanel = "3";

        panel1.append(randomizeBtn, genderBlock, ageBlock, heightBlock, weightBlock, countryBlock);
        panel2.append(constitucionBlock, peinadoBlock);
        panel3.append(checksBlock, detailsBlock);

        panel1.insertAdjacentHTML("beforeend", '<div class="benditoai-generator-step-actions"><button type="button" class="benditoai-step-nav-btn benditoai-step-next" data-step-next="2">Siguiente</button></div>');
        panel2.insertAdjacentHTML("beforeend", '<div class="benditoai-generator-step-actions"><button type="button" class="benditoai-step-nav-btn benditoai-step-prev" data-step-prev="1">Atrás</button><button type="button" class="benditoai-step-nav-btn benditoai-step-next" data-step-next="3">Siguiente</button></div>');
        panel3.insertAdjacentHTML("beforeend", '<div class="benditoai-generator-step-actions"><button type="button" class="benditoai-step-nav-btn benditoai-step-prev" data-step-prev="2">Atrás</button></div>');

        modalControlsCol.prepend(panel3);
        modalControlsCol.prepend(panel2);
        modalControlsCol.prepend(panel1);
        modalControlsCol.prepend(steps);

        modalControlsCol.addEventListener("click", function (event) {
            const target = event.target.closest("[data-step-target], [data-step-next], [data-step-prev]");
            if (!target) return;

            if (target.dataset.stepTarget) setGeneratorStep(target.dataset.stepTarget);
            if (target.dataset.stepNext) {
                setGeneratorStep(target.dataset.stepNext);
                scrollWizardTopSmooth();
            }
            if (target.dataset.stepPrev) setGeneratorStep(target.dataset.stepPrev);
        });

        generatorWizardReady = true;
        setGeneratorStep(1);
    }

    function openModal() {
        modal.classList.add("is-open");
        modal.setAttribute("aria-hidden", "false");
        document.body.classList.add("benditoai-modal-open");
        setGeneratorStep(1);
    }

    function closeModal() {
        modal.classList.remove("is-open");
        modal.setAttribute("aria-hidden", "true");
        document.body.classList.remove("benditoai-modal-open");
    }

    function cmToFeetInches(cm) {
        const totalInches = cm / 2.54;
        const feet = Math.floor(totalInches / 12);
        const inches = Math.round(totalInches % 12);
        return `${feet} ft ${inches} in`;
    }

    function updateRangeLabels() {
        const age = parseInt(ageRange.value, 10);
        const height = parseInt(heightRange.value, 10);
        const weight = parseInt(weightRange.value, 10);
        const lbs = Math.round(weight * 2.20462);

        ageValue.textContent = String(age);
        heightValue.textContent = `${height} cm | ${cmToFeetInches(height)}`;
        weightValue.textContent = `${weight} kg | ${lbs} lbs`;
    }

    function setTraitSelectCurrentFromOption(selectEl, optionEl) {
        if (!selectEl || !optionEl) return;

        const currentLabel = selectEl.querySelector("[data-trait-current-label]");
        const currentImage = selectEl.querySelector("[data-trait-current-image]");
        const optionImage = optionEl.querySelector("img");

        if (currentLabel) {
            currentLabel.textContent = optionEl.dataset.value || "";
        }

        if (currentImage && optionImage) {
            currentImage.src = optionImage.src;
            currentImage.alt = optionImage.alt || optionEl.dataset.value || "";
        }
    }

    function closeAllTraitSelects(exceptSelect = null) {
        document.querySelectorAll(".benditoai-trait-select").forEach((selectEl) => {
            if (exceptSelect && selectEl === exceptSelect) return;
            selectEl.classList.remove("is-open");
            selectEl.style.zIndex = "";
            const parentRow = selectEl.closest(".benditoai-two-cols, .benditoai-field-block");
            if (parentRow) parentRow.style.zIndex = "";
            detachTraitMenu(selectEl);
            const trigger = selectEl.querySelector("[data-trait-trigger]");
            if (trigger) {
                trigger.setAttribute("aria-expanded", "false");
            }
        });
        if (!exceptSelect) {
            activeFloatingTraitSelect = null;
        }
        refreshTraitFocusOverlay();
    }

    function setTraitSelection(target, value) {
        const nativeMap = {
            constitucion: traitConstitucion,
            ojos: traitOjos,
            peinado: traitPeinado,
            color_pelo: traitColorPelo
        };

        if (typeof target === "string" && nativeMap[target]) {
            nativeMap[target].value = value;
            return;
        }

        const traitSelect = typeof target === "string"
            ? document.querySelector(`.benditoai-trait-select[data-trait-select='${target}']`)
            : (target && target.matches && target.matches(".benditoai-trait-select") ? target : null);

        if (traitSelect) {
            const options = traitSelect.querySelectorAll(".benditoai-trait-option");
            let activeOption = null;

            options.forEach((option) => {
                const isActive = option.dataset.value === value;
                option.classList.toggle("is-active", isActive);
                if (isActive) {
                    activeOption = option;
                }
            });

            if (activeOption) {
                setTraitSelectCurrentFromOption(traitSelect, activeOption);
            }

            return;
        }

        const traitGrid = typeof target === "string"
            ? document.querySelector(`.benditoai-trait-grid[data-trait='${target}']`)
            : target;

        if (!traitGrid) return;

        const buttons = traitGrid.querySelectorAll(".benditoai-trait-btn");
        buttons.forEach((btn) => {
            btn.classList.toggle("is-active", btn.dataset.value === value);
        });
    }

    function getActiveTraitValue(traitName) {
        const nativeMap = {
            constitucion: traitConstitucion,
            ojos: traitOjos,
            peinado: traitPeinado,
            color_pelo: traitColorPelo
        };

        if (nativeMap[traitName]) {
            return nativeMap[traitName].value || "";
        }

        const activeSelectOption = document.querySelector(`.benditoai-trait-select[data-trait-select='${traitName}'] .benditoai-trait-option.is-active`);
        if (activeSelectOption) {
            return activeSelectOption.dataset.value || "";
        }

        const activeGridOption = document.querySelector(`.benditoai-trait-grid[data-trait='${traitName}'] .benditoai-trait-btn.is-active`);
        return activeGridOption ? activeGridOption.dataset.value : "";
    }

    function getSelectedGender() {
        const checked = document.querySelector("input[name='influencer_genero']:checked");
        return checked ? checked.value : "Mujer";
    }

    function collectTraits() {
        return {
            genero: getSelectedGender(),
            edad: String(parseInt(ageRange.value, 10)),
            altura: String(parseInt(heightRange.value, 10)),
            peso: String(parseInt(weightRange.value, 10)),
            pais: countryMain.value,
            pais2: countrySecondary.value,
            constitucion: getActiveTraitValue("constitucion"),
            ojos: getActiveTraitValue("ojos"),
            peinado: getActiveTraitValue("peinado"),
            color_pelo: getActiveTraitValue("color_pelo"),
            hoyuelos: hoyuelosInput.checked ? "1" : "0",
            barba: barbaInput.checked ? "1" : "0",
            bronceado: bronceadoInput.checked ? "1" : "0",
            detalles: customDetailsInput.value.trim()
        };
    }

    function applyTraitPreset(preset) {
        document.querySelectorAll("input[name='influencer_genero']").forEach((input) => {
            input.checked = input.value === preset.genero;
        });

        ageRange.value = preset.edad;
        heightRange.value = preset.altura;
        weightRange.value = preset.peso;

        countryMain.value = preset.pais;
        countrySecondary.value = preset.pais2;

        ["constitucion", "ojos", "peinado", "color_pelo"].forEach((key) => {
            if (preset[key]) {
                setTraitSelection(key, preset[key]);
            }
        });

        hoyuelosInput.checked = preset.hoyuelos === "1";
        barbaInput.checked = preset.barba === "1";
        bronceadoInput.checked = preset.bronceado === "1";
        customDetailsInput.value = preset.detalles;

        updateRangeLabels();
    }

    function getDefaultPreset() {
        return {
            genero: "Mujer",
            edad: "28",
            altura: "168",
            peso: "58",
            pais: "Colombia",
            pais2: "Ninguno",
            constitucion: "Atlética",
            ojos: "Ámbar",
            peinado: "Pixie lateral",
            color_pelo: "Miel",
            hoyuelos: "0",
            barba: "0",
            bronceado: "1",
            detalles: ""
        };
    }

    function randomFrom(arr) {
        return arr[Math.floor(Math.random() * arr.length)];
    }

    function buildRandomPreset() {
        return {
            genero: randomFrom(["Hombre", "Mujer", "Secreto"]),
            edad: String(Math.floor(Math.random() * (55 - 18 + 1)) + 18),
            altura: String(Math.floor(Math.random() * (195 - 150 + 1)) + 150),
            peso: String(Math.floor(Math.random() * (110 - 45 + 1)) + 45),
            pais: randomFrom(["Colombia", "México", "Argentina", "España", "Brasil", "Estados Unidos"]),
            pais2: randomFrom(["Ninguno", "Colombia", "México", "Argentina", "España", "Brasil", "Estados Unidos"]),
            constitucion: randomFrom(["Atlética", "Delgada", "Curvy"]),
            ojos: randomFrom(["Ámbar", "Marrón", "Verde", "Azul"]),
            peinado: randomFrom(["Pixie lateral", "Largo ondulado", "Bob liso", "Rizos naturales"]),
            color_pelo: randomFrom(["Miel", "Negro", "Castaño", "Rubio"]),
            hoyuelos: Math.random() > 0.55 ? "1" : "0",
            barba: Math.random() > 0.75 ? "1" : "0",
            bronceado: Math.random() > 0.35 ? "1" : "0",
            detalles: randomFrom([
                "look editorial, piel natural, sonrisa leve",
                "estilo fitness premium, mirada segura",
                "estética urbana de lujo con maquillaje suave",
                "vibe fashion campaign minimal"
            ])
        };
    }

    function setModalPreview(url) {
        if (!modalPlaceholder || !modalImage) return;

        modalImage.src = url;
        modalImage.style.display = "block";
        modalPlaceholder.classList.remove("is-error", "is-loading");
        modalPlaceholder.style.display = "none";
        selectGeneratedBtn.disabled = false;
    }

    function resetModalPreview() {
        if (!modalPlaceholder || !modalImage) return;

        modalImage.src = "";
        modalImage.style.display = "none";
        modalPlaceholder.classList.remove("is-error", "is-loading");
        modalPlaceholder.innerHTML = defaultModalPlaceholderMarkup;
        modalPlaceholder.style.display = "flex";
        selectGeneratedBtn.disabled = true;

        influencerState.imageUrl = "";
        influencerState.prompt = "";
        influencerState.traits = {};
    }

    function renderHistoryItem(data) {
        const grid = document.querySelector("#benditoai-historial-mockups");
        if (!grid) return;

        const empty = document.getElementById("benditoai-empty-message");
        if (empty) empty.remove();

        const safeName = escapeHtml(data.nombre_modelo || "Modelo AI");
        const safeGenero = escapeHtml(data.genero || "-");
        const safeEdad = escapeHtml(data.edad || "-");
        const safeEstilo = escapeHtml(data.estilo || "-");
        const safeFecha = escapeHtml(data.fecha || "-");
        const safeImageUrl = escapeAttribute(data.image_url || "");
        const noCacheUrl = `${safeImageUrl}?t=${Date.now()}`;
        const pluginUrl = benditoai_ajax.plugin_url;

        const item = `
<div class="benditoai-historial-item" data-id="${data.id}">
    <p class="benditoai-historial-name">${safeName}</p>

    <div class="benditoai-img-wrap">
        <img src="${noCacheUrl}" alt="${safeName}" class="benditoai-historial-img" />

        <div class="benditoai-action-buttons">
            <div class="hoverselect">
                <a href="${noCacheUrl}" download class="benditoai-btn benditoai-btn--download">
                    <img src="${pluginUrl}assets/images/icon-download.png" alt="Descargar" class="benditoai-btn-icon">
                </a>
            </div>

            <div class="hoverselect">
                <button class="benditoai-edit-modelo-btn" data-id="${data.id}" data-image="${safeImageUrl}">
                    <img src="${pluginUrl}assets/images/icon-edit.png" alt="Editar" class="benditoai-btn-icon">
                </button>
            </div>

            <div class="hoverselect">
                <button class="benditoai-delete-modelo-btn benditoai-action-btn" data-id="${data.id}">
                    <img src="${pluginUrl}assets/images/icon-delete.png" alt="Eliminar" class="benditoai-btn-icon">
                </button>
            </div>
        </div>
    </div>

    <div class="benditoai-edit-box" style="display:none;">
        <textarea class="benditoai-edit-text" placeholder="Ej: cámbiale el pantalón por uno jean oscuro..."></textarea>
        <button class="benditoai-save-edit-btn">Guardar cambios</button>
    </div>

    <button class="benditoai-toggle-info">Ver detalles</button>

    <div class="benditoai-historial-info" style="display:none;">
        <p><strong>Género:</strong> ${safeGenero}</p>
        <p><strong>Edad:</strong> ${safeEdad}</p>
        <p><strong>Estilo:</strong> ${safeEstilo}</p>
        <p><strong>Creado:</strong> ${safeFecha}</p>
    </div>
</div>`;

        grid.insertAdjacentHTML("afterbegin", item);
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll("\"", "&quot;")
            .replaceAll("'", "&#39;");
    }

    function escapeAttribute(value) {
        return escapeHtml(value);
    }

    async function parseAjaxResponse(response) {
        const raw = await response.text();
        let json = null;

        if (raw) {
            try {
                json = JSON.parse(raw);
            } catch (e) {
                json = tryExtractJson(raw);
            }
        }

        return {
            ok: response.ok,
            status: response.status,
            raw,
            json
        };
    }

    function tryExtractJson(raw) {
        if (!raw || typeof raw !== "string") return null;

        const first = raw.indexOf("{");
        const last = raw.lastIndexOf("}");

        if (first === -1 || last === -1 || last <= first) {
            return null;
        }

        const candidate = raw.slice(first, last + 1).trim();

        try {
            return JSON.parse(candidate);
        } catch (e) {
            return null;
        }
    }

    function extractAjaxErrorMessage(parsed, fallbackMessage) {
        const payload = parsed?.json;
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

        if (typeof parsed?.raw === "string" && parsed.raw.trim()) {
            const clean = parsed.raw
                .replace(/<[^>]*>/g, " ")
                .replace(/\s+/g, " ")
                .trim();

            if (clean) {
                return clean.slice(0, 240);
            }
        }

        if (!parsed?.ok) {
            return `${fallbackMessage} (HTTP ${parsed?.status || "?"})`;
        }

        return fallbackMessage;
    }

    uploadTrigger.addEventListener("click", function () {
        uploadInput.click();
    });

    uploadInput.addEventListener("change", function () {
        hideError();

        if (!uploadInput.files || uploadInput.files.length === 0) {
            if (referenceSourceInput.value === "upload") {
                clearMainPreview();
                referenceSourceInput.value = "";
            }
            updateCreateState();
            return;
        }

        const file = uploadInput.files[0];

        if (localPreviewObjectUrl) {
            URL.revokeObjectURL(localPreviewObjectUrl);
        }

        localPreviewObjectUrl = URL.createObjectURL(file);
        setMainPreview(localPreviewObjectUrl);

        referenceSourceInput.value = "upload";
        referenceImageUrlInput.value = "";
        generatedPromptInput.value = "";
        traitsPayloadInput.value = "";

        updateCreateState();
    });

    openGeneratorBtn.addEventListener("click", function () {
        hideGeneratorError();
        openModal();
    });

    closeGeneratorBtn.addEventListener("click", closeModal);

    modalBackdrops.forEach((backdrop) => {
        backdrop.addEventListener("click", closeModal);
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && modal.classList.contains("is-open")) {
            closeModal();
        }
    });

    document.querySelectorAll(".benditoai-trait-grid .benditoai-trait-btn").forEach((btn) => {
        btn.addEventListener("click", function () {
            const grid = this.closest(".benditoai-trait-grid");
            if (!grid) return;
            grid.querySelectorAll(".benditoai-trait-btn").forEach((item) => item.classList.remove("is-active"));
            this.classList.add("is-active");
        });
    });

    document.querySelectorAll(".benditoai-trait-select [data-trait-trigger]").forEach((trigger) => {
        trigger.addEventListener("click", function (event) {
            event.preventDefault();
            const selectEl = this.closest(".benditoai-trait-select");
            if (!selectEl) return;

            const willOpen = !selectEl.classList.contains("is-open");
            closeAllTraitSelects(selectEl);
            selectEl.classList.toggle("is-open", willOpen);
            this.setAttribute("aria-expanded", willOpen ? "true" : "false");
            if (willOpen) {
                selectEl.style.zIndex = "9999";
                const parentRow = selectEl.closest(".benditoai-two-cols, .benditoai-field-block");
                if (parentRow) parentRow.style.zIndex = "9998";
                activeFloatingTraitSelect = selectEl;
                positionTraitMenuFloating(selectEl);
                centerTraitSelectOnMobile(selectEl);
            } else {
                selectEl.style.zIndex = "";
                const parentRow = selectEl.closest(".benditoai-two-cols, .benditoai-field-block");
                if (parentRow) parentRow.style.zIndex = "";
                detachTraitMenu(selectEl);
                activeFloatingTraitSelect = null;
            }
            refreshTraitFocusOverlay();
        });
    });

    document.querySelectorAll(".benditoai-trait-select .benditoai-trait-option").forEach((option) => {
        option.addEventListener("click", function (event) {
            event.preventDefault();
            const selectEl = this.closest(".benditoai-trait-select");
            if (!selectEl) return;

            setTraitSelection(selectEl, this.dataset.value || "");
            closeAllTraitSelects();
        });
    });

    document.addEventListener("click", function (event) {
        if (!event.target.closest(".benditoai-trait-select")) {
            closeAllTraitSelects();
        }
    });

    const modalBodyForFloating = modal?.querySelector(".benditoai-influencer-body");
    if (modalBodyForFloating) {
        modalBodyForFloating.addEventListener("scroll", function () {
            if (activeFloatingTraitSelect && activeFloatingTraitSelect.classList.contains("is-open")) {
                positionTraitMenuFloating(activeFloatingTraitSelect);
            }
        }, { passive: true });
    }

    window.addEventListener("resize", function () {
        if (activeFloatingTraitSelect && activeFloatingTraitSelect.classList.contains("is-open")) {
            positionTraitMenuFloating(activeFloatingTraitSelect);
        }
    });

    [ageRange, heightRange, weightRange].forEach((rangeEl) => {
        rangeEl.addEventListener("input", updateRangeLabels);
    });

    randomizeBtn.addEventListener("click", function () {
        applyTraitPreset(buildRandomPreset());
    });

    resetTraitsBtn.addEventListener("click", function () {
        applyTraitPreset(getDefaultPreset());
        hideGeneratorError();
    });

    generateInfluencerBtn.addEventListener("click", async function () {
        if (currentGeneratorStep !== 3) {
            showGeneratorError("Llega al paso 3 para generar.");
            nudgeWizardToNext();
            return;
        }

        hideGeneratorError();

        const traits = collectTraits();

        generateInfluencerBtn.disabled = true;
        if (generatorLoading) {
            generatorLoading.style.display = "none";
        }
        showGeneratorSkeleton();

        const data = new FormData();
        data.append("action", "benditoai_generar_influencer_referencia");
        data.append("prompt_preview", buildInfluencerPromptPreviewForPayload(traits));

        Object.keys(traits).forEach((key) => {
            data.append(key, traits[key]);
        });

        try {
            const response = await fetch(benditoai_ajax.ajax_url, {
                method: "POST",
                body: data
            });

            const parsed = await parseAjaxResponse(response);
            const result = parsed.json;

            if (!result || result.success !== true) {
                showGeneratorError(extractAjaxErrorMessage(parsed, "No se pudo generar la referencia."));
                return;
            }

            const payload = result.data;

            influencerState.imageUrl = payload.image_url;
            influencerState.prompt = payload.prompt || "";
            influencerState.traits = payload.traits || traits;

            setModalPreview(payload.image_url);

            if (payload.tokens !== undefined) {
                benditoaiActualizarTokensInstantaneo(payload.tokens);
            }
        } catch (error) {
            showGeneratorError(error?.message || "Error inesperado al generar la referencia.");
        } finally {
            generateInfluencerBtn.disabled = false;
            if (generatorLoading) {
                generatorLoading.style.display = "none";
            }
        }
    });

    selectGeneratedBtn.addEventListener("click", function () {
        if (!influencerState.imageUrl) {
            showGeneratorError("Genera una imagen primero.");
            return;
        }

        if (localPreviewObjectUrl) {
            URL.revokeObjectURL(localPreviewObjectUrl);
            localPreviewObjectUrl = "";
        }

        uploadInput.value = "";

        setMainPreview(influencerState.imageUrl);

        referenceSourceInput.value = "ai";
        referenceImageUrlInput.value = influencerState.imageUrl;
        generatedPromptInput.value = influencerState.prompt || "";
        traitsPayloadInput.value = JSON.stringify(influencerState.traits || {});

        closeModal();
        updateCreateState();
    });

    modelNameInput.addEventListener("input", function () {
        updateCounters();
        updateCreateState();
    });

    modelDescInput.addEventListener("input", updateCounters);

    form.addEventListener("submit", async function (e) {
        e.preventDefault();
        hideError();

        if (!hasReferenceReady()) {
            showError("Selecciona una imagen de referencia antes de crear el modelo.");
            return;
        }

        createBtn.disabled = true;
        createBtn.classList.add("benditoai-btn--loading");
        loading.style.display = "block";

        const promptPreviewText = buildPromptPreviewForPayload();
        if (promptPreviewHiddenInput) {
            promptPreviewHiddenInput.value = promptPreviewText;
        }
        showPromptPreview(promptPreviewText);

        const data = new FormData(form);
        data.append("action", "benditoai_crear_modelo_ai");
        data.set("prompt_preview", promptPreviewText);

        try {
            const response = await fetch(benditoai_ajax.ajax_url, {
                method: "POST",
                body: data
            });

            const parsed = await parseAjaxResponse(response);
            const result = parsed.json;

            if (!result || result.success !== true) {
                showError(extractAjaxErrorMessage(parsed, "No se pudo crear el modelo."));
                return;
            }

            const payload = result.data;

            setMainPreview(payload.image_url);

            if (payload.tokens !== undefined) {
                benditoaiActualizarTokensInstantaneo(payload.tokens);
            }

            renderHistoryItem(payload);
            showPromptPreview(payload.prompt);

            showSuccess("Modelo creado correctamente.");

            if (referenceSourceInput.value === "upload") {
                uploadInput.value = "";
            }

            referenceSourceInput.value = "ai";
            referenceImageUrlInput.value = payload.image_url;
        } catch (error) {
            showError(error?.message || "Error inesperado al crear el modelo.");
        } finally {
            loading.style.display = "none";
            createBtn.classList.remove("benditoai-btn--loading");
            updateCreateState();
        }
    });

    applyTraitPreset(getDefaultPreset());
    updateRangeLabels();
    updateCounters();
    updateCreateState();
    resetModalPreview();
    setupGeneratorWizard();
});
