document.addEventListener("DOMContentLoaded", function () {
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
    const edadRange = document.getElementById("benditoai_edad_range");
    const edadValue = document.getElementById("benditoai_edad_value");
    const edadHidden = document.getElementById("benditoai_edad");
    const alturaRange = document.getElementById("benditoai_altura");
    const alturaValue = document.getElementById("benditoai_altura_value");
    const pesoRange = document.getElementById("benditoai_peso");
    const pesoValue = document.getElementById("benditoai_peso_value");

    let currentStep = 1;
    let isSubmitting = false;
    let lastSuccess = null;
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
        return modeInputs.find((input) => input.checked)?.value || "referencia";
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

    const syncModePanels = () => {
        const mode = getActiveMode();
        const modePanels = Array.from(form.querySelectorAll("[data-mode-panel]"));

        modePanels.forEach((panel) => {
            const isActive = panel.dataset.modePanel === mode;
            panel.hidden = !isActive;
            panel.setAttribute("aria-hidden", isActive ? "false" : "true");

            const fields = panel.querySelectorAll("input, select, textarea, button");
            fields.forEach((field) => {
                field.disabled = !isActive;
            });
        });

        if (referenceImageInput) {
            referenceImageInput.required = mode === "referencia";
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

        if (prevBtn) prevBtn.style.display = currentStep === 1 ? "none" : "inline-flex";
        if (nextBtn) nextBtn.style.display = currentStep === totalSteps ? "none" : "inline-flex";
        if (submitBtn) submitBtn.style.display = currentStep === totalSteps ? "inline-flex" : "none";

        root.classList.toggle("is-step-compact", currentStep > 1);

        clearInlineError();
    };

    const validateStep = (stepNumber) => {
        const step = steps[stepNumber - 1];
        if (!step) return true;

        const activeMode = getActiveMode();
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

        if (stepNumber === 2 && activeMode === "referencia" && referenceImageInput) {
            if (!referenceImageInput.files || referenceImageInput.files.length === 0) {
                showInlineError("Sube la imagen de referencia para avanzar.");
                referenceImageInput.focus();
                return false;
            }
        }

        return true;
    };

    const goToStep = (targetStep) => {
        const step = Math.max(1, Math.min(totalSteps, targetStep));
        currentStep = step;
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

        const noCacheUrl = `${d.image_url}?t=${Date.now()}`;
        const genero = escapeHtml(d.genero || "-");
        const edad = escapeHtml(d.edad || "-");
        const estilo = escapeHtml(d.estilo || d.modo_label || "-");
        const modo = escapeHtml(d.modo_label || "-");
        const nacionalidad = escapeHtml(d.nacionalidad || "-");
        const nombreModelo = escapeHtml(d.nombre_modelo || "Modelo AI");
        const fecha = escapeHtml(d.fecha || "-");
        const publico = Number(d.perfil_publico) === 1 ? "Publico" : "Privado";

        const nuevoItem = `
            <div class="benditoai-historial-item" data-id="${d.id}">
                <p class="benditoai-historial-name">${nombreModelo}</p>
                <div class="benditoai-img-wrap">
                    <img src="${noCacheUrl}" alt="${nombreModelo}" class="benditoai-historial-img" />
                    <div class="benditoai-action-buttons">
                        <div class="hoverselect">
                            <a href="${noCacheUrl}" download class="benditoai-btn benditoai-btn--download">
                                <img src="${benditoai_ajax.plugin_url}assets/images/icon-download.png" alt="Descargar" class="benditoai-btn-icon">
                            </a>
                        </div>
                        <div class="hoverselect">
                            <button class="benditoai-edit-modelo-btn" data-id="${d.id}" data-image="${d.image_url}">
                                <img src="${benditoai_ajax.plugin_url}assets/images/icon-edit.png" alt="Editar" class="benditoai-btn-icon">
                            </button>
                        </div>
                        <div class="hoverselect">
                            <button class="benditoai-delete-modelo-btn benditoai-action-btn" data-id="${d.id}">
                                <img src="${benditoai_ajax.plugin_url}assets/images/icon-delete.png" alt="Eliminar" class="benditoai-btn-icon">
                            </button>
                        </div>
                    </div>
                </div>
                <div class="benditoai-edit-box" style="display:none;">
                    <textarea class="benditoai-edit-text" placeholder="Ej: cambia vestuario y expresion."></textarea>
                    <button class="benditoai-save-edit-btn">Guardar cambios</button>
                </div>
                <button class="benditoai-toggle-info">Ver detalles</button>
                <div class="benditoai-historial-info" style="display:none;">
                    <p><strong>Flujo:</strong> ${modo}</p>
                    <p><strong>Genero:</strong> ${genero}</p>
                    <p><strong>Edad:</strong> ${edad}</p>
                    <p><strong>Estilo:</strong> ${estilo}</p>
                    <p><strong>Nacionalidad:</strong> ${nacionalidad}</p>
                    <p><strong>Visibilidad:</strong> ${publico}</p>
                    <p><strong>Creado:</strong> ${fecha}</p>
                </div>
            </div>
        `;

        grid.insertAdjacentHTML("afterbegin", nuevoItem);
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
                    localStorage.setItem("benditoai_selected_model", JSON.stringify({
                        id: d.id,
                        name: d.nombre_modelo,
                        image_url: d.image_url,
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
        updateStepUi();
        clearInlineError();
        showConfigStage();
        resetResult();
        window.scrollTo({ top: root.offsetTop - 30, behavior: "smooth" });
    };

    prevBtn?.addEventListener("click", () => {
        if (isSubmitting) return;
        goToStep(currentStep - 1);
    });

    nextBtn?.addEventListener("click", () => {
        if (isSubmitting) return;
        if (!validateStep(currentStep)) return;
        goToStep(currentStep + 1);
    });

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        if (isSubmitting) return;
        if (!validateStep(currentStep)) return;
        submitRequest();
    });

    modeInputs.forEach((input) => {
        input.addEventListener("change", () => {
            syncModePanels();
            clearInlineError();
        });
    });

    [edadRange, alturaRange, pesoRange].forEach((range) => {
        range?.addEventListener("input", syncRangos);
    });

    publicToggle?.addEventListener("change", syncVisibilityToggle);

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
    syncRangos();
    updateStepUi();
    resetResult();
    showConfigStage();
});
