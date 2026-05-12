document.addEventListener("DOMContentLoaded", function () {
    const itemStates = new WeakMap();

    const getState = (item) => {
        if (!itemStates.has(item)) {
            itemStates.set(item, {
                originalUrl: "",
                previewUrl: "",
                previewToken: "",
                pendingDecision: false,
            });
        }
        return itemStates.get(item);
    };

    const getCardImage = (item) => item?.querySelector(".benditoai-historial-img");
    const getEditBtn = (item) => item?.querySelector(".benditoai-edit-modelo-btn");
    const getDownloadBtn = (item) => item?.querySelector(".benditoai-btn--download");

    const getInlineEdit = (item) => item?.querySelector(".benditoai-inline-edit");
    const getInlineText = (item) => item?.querySelector(".benditoai-inline-edit-text");
    const getInlineFile = (item) => item?.querySelector(".benditoai-inline-edit-ref-file");
    const getInlineFileName = (item) => item?.querySelector(".benditoai-inline-edit-ref-name");
    const getInlineSubmit = (item) => item?.querySelector(".benditoai-inline-edit-submit");
    const getInlineRefTriggerText = (item) => item?.querySelector(".benditoai-inline-edit-ref-trigger-text");
    const getInlineRefTriggerPreview = (item) => item?.querySelector(".benditoai-inline-edit-ref-trigger-preview");
    const getInlineRefTriggerPreviewImg = (item) => item?.querySelector(".benditoai-inline-edit-ref-trigger-preview-img");
    const getStyleOptions = (item) => item ? Array.from(item.querySelectorAll(".benditoai-style-option")) : [];
    const getInlineSelectedStyleInput = (item) => item?.querySelector(".benditoai-inline-edit-selected-style");
    const getInlineSelectedStyleIdInput = (item) => item?.querySelector(".benditoai-inline-edit-selected-style-id");
    const getInlineSelectedStyleBlock = (item) => item?.querySelector(".benditoai-inline-edit-style");
    const getInlineSelectedStyleValue = (item) => item?.querySelector(".benditoai-inline-edit-style-value");

    const getDecision = (item) => item?.querySelector(".benditoai-edit-decision");
    const getDecisionApply = (item) => item?.querySelector(".benditoai-edit-apply-btn");
    const getDecisionDiscard = (item) => item?.querySelector(".benditoai-edit-discard-btn");
    const getAllEditButtons = (item) => item ? Array.from(item.querySelectorAll(".benditoai-edit-modelo-btn")) : [];
    const getPanelCampaignButtons = (item) => item ? Array.from(item.querySelectorAll(".benditoai-use-campaign-btn--panel")) : [];

    const setEditButtonsActiveState = (item, isActive) => {
        getAllEditButtons(item).forEach((button) => {
            button.classList.toggle("is-active", Boolean(isActive));
            button.setAttribute("aria-pressed", isActive ? "true" : "false");
        });
    };

    const setCampaignButtonsEditingState = (item, isEditing) => {
        getPanelCampaignButtons(item).forEach((button) => {
            if (!button.dataset.originalHtml) {
                button.dataset.originalHtml = button.innerHTML;
            }
            if (isEditing) {
                button.classList.add("is-editing-state");
                button.disabled = true;
                button.setAttribute("aria-disabled", "true");
                button.innerHTML = 'Editando modelo <span aria-hidden="true">&bull;</span>';
                return;
            }

            button.classList.remove("is-editing-state");
            button.disabled = false;
            button.setAttribute("aria-disabled", "false");
            button.innerHTML = button.dataset.originalHtml || 'Lanzar campana <span aria-hidden="true">&rarr;</span>';
        });
    };

    const showEditor = (item) => {
        const edit = getInlineEdit(item);
        if (!edit) return;
        item.classList.add("is-editing");
        setEditButtonsActiveState(item, true);
        setCampaignButtonsEditingState(item, true);
        edit.hidden = false;
        const text = getInlineText(item);
        if (text) {
            window.setTimeout(() => text.focus(), 40);
        }
    };

    const hideEditor = (item) => {
        const edit = getInlineEdit(item);
        if (!edit) return;
        edit.hidden = true;
        item.classList.remove("is-editing");
        setEditButtonsActiveState(item, false);
        setCampaignButtonsEditingState(item, false);
        const text = getInlineText(item);
        const file = getInlineFile(item);
        const fileName = getInlineFileName(item);
        const styleOptions = getStyleOptions(item);
        const selectedStyleInput = getInlineSelectedStyleInput(item);
        const selectedStyleIdInput = getInlineSelectedStyleIdInput(item);
        const selectedStyleBlock = getInlineSelectedStyleBlock(item);
        const selectedStyleValue = getInlineSelectedStyleValue(item);
        if (text) text.value = "";
        if (file) file.value = "";
        if (fileName) fileName.textContent = "";
        if (selectedStyleInput) selectedStyleInput.value = "";
        if (selectedStyleIdInput) selectedStyleIdInput.value = "";
        if (selectedStyleValue) selectedStyleValue.textContent = "";
        if (selectedStyleBlock) selectedStyleBlock.hidden = true;
        styleOptions.forEach((thumb) => {
            thumb.classList.remove("is-active");
            thumb.setAttribute("aria-pressed", "false");
        });
        syncRefTriggerPreview(item, null, "");
    };

    const syncRefTriggerPreview = (item, imageUrl, labelText) => {
        const previewWrap = getInlineRefTriggerPreview(item);
        const previewImg = getInlineRefTriggerPreviewImg(item);
        const triggerText = getInlineRefTriggerText(item);
        if (!previewWrap || !previewImg || !triggerText) return;

        if (!imageUrl) {
            previewWrap.hidden = true;
            previewWrap.classList.remove("is-ready");
            previewImg.hidden = true;
            previewImg.src = "";
            triggerText.textContent = "Una prenda de vestir (opcional)";
            return;
        }

        previewWrap.hidden = false;
        previewWrap.classList.remove("is-ready");
        previewImg.hidden = true;
        previewImg.onload = () => {
            previewImg.hidden = false;
            previewWrap.classList.add("is-ready");
        };
        previewImg.onerror = () => {
            previewWrap.hidden = true;
            previewWrap.classList.remove("is-ready");
            previewImg.hidden = true;
            previewImg.src = "";
            triggerText.textContent = "Una prenda de vestir (opcional)";
        };
        previewImg.src = imageUrl;
        triggerText.textContent = labelText || "Referencia lista";
    };

    const setSelectedStyle = (item, styleLabel, styleId, styleImage) => {
        const selectedStyleInput = getInlineSelectedStyleInput(item);
        const selectedStyleIdInput = getInlineSelectedStyleIdInput(item);
        const selectedStyleBlock = getInlineSelectedStyleBlock(item);
        const selectedStyleValue = getInlineSelectedStyleValue(item);
        const normalizedStyle = String(styleLabel || "").trim();
        const normalizedId = String(styleId || "").trim();

        if (selectedStyleInput) {
            selectedStyleInput.value = normalizedStyle;
        }
        if (selectedStyleIdInput) {
            selectedStyleIdInput.value = normalizedId;
        }

        if (!selectedStyleBlock || !selectedStyleValue) return;

        if (!normalizedStyle) {
            selectedStyleValue.textContent = "";
            selectedStyleBlock.hidden = true;
            syncRefTriggerPreview(item, "", "");
            return;
        }

        selectedStyleValue.textContent = normalizedStyle;
        selectedStyleBlock.hidden = false;
        syncRefTriggerPreview(item, styleImage || "", `Estilo: ${normalizedStyle}`);
    };

    const showDecision = (item) => {
        const decision = getDecision(item);
        if (!decision) return;
        decision.hidden = false;
        item.classList.add("is-awaiting-decision");
    };

    const hideDecision = (item) => {
        const decision = getDecision(item);
        if (!decision) return;
        decision.hidden = true;
        item.classList.remove("is-awaiting-decision");
    };

    const setLoading = (item, isLoading) => {
        const wrap = item.querySelector(".benditoai-img-wrap");
        const submit = getInlineSubmit(item);
        item.classList.toggle("is-edit-loading", isLoading);
        if (wrap) {
            wrap.classList.toggle("benditoai-image-loading", isLoading);
        }
        if (submit) {
            submit.disabled = isLoading;
            submit.textContent = isLoading ? "Editando..." : "Enviar";
        }
    };

    const updateImageInCard = (item, imageUrl) => {
        const img = getCardImage(item);
        const editBtns = item ? Array.from(item.querySelectorAll(".benditoai-edit-modelo-btn")) : [];
        const downloadBtns = item ? Array.from(item.querySelectorAll(".benditoai-btn--download")) : [];
        const campaignUseBtns = item ? Array.from(item.querySelectorAll(".benditoai-use-campaign-btn")) : [];
        if (!img || !imageUrl) return;

        const noCache = `${imageUrl}?t=${Date.now()}`;
        img.src = noCache;
        editBtns.forEach((button) => {
            button.dataset.image = imageUrl;
        });
        downloadBtns.forEach((link) => {
            link.href = noCache;
        });
        campaignUseBtns.forEach((button) => {
            button.dataset.modeloImage = imageUrl;
        });
    };

    const closeAllEditors = () => {
        document.querySelectorAll(".benditoai-historial-item").forEach((item) => {
            hideEditor(item);
        });
    };

    const centerEditorInView = (item) => {
        if (!item) return;
        const surface = item.querySelector(".benditoai-inline-edit-surface");
        const fallbackTarget = item.querySelector(".benditoai-img-wrap") || item;
        const target = surface || fallbackTarget;

        window.requestAnimationFrame(() => {
            const rect = target.getBoundingClientRect();
            const viewportCenter = window.innerHeight / 2;
            const targetCenter = rect.top + (rect.height / 2);
            const delta = targetCenter - viewportCenter;

            if (Math.abs(delta) < 12) return;

            window.scrollTo({
                top: Math.max(0, window.scrollY + delta),
                behavior: "smooth",
            });
        });
    };

    const openInlineEditor = (item) => {
        if (!item) return;
        closeAllEditors();
        const info = item.querySelector(".benditoai-historial-info");
        if (info) info.style.display = "none";
        showEditor(item);
        window.setTimeout(() => centerEditorInView(item), 70);
    };

    const handlePreviewEdit = async (item) => {
        const editBtn = getEditBtn(item);
        const text = getInlineText(item);
        const file = getInlineFile(item);

        if (!editBtn || !text) return;

        const texto = text.value.trim();
        if (!texto) {
            alert("Escribe que deseas cambiar.");
            return;
        }

        const modeloId = editBtn.dataset.id || "";
        const currentImageUrl = editBtn.dataset.image || "";

        const formData = new FormData();
        formData.append("action", "benditoai_preview_edit_modelo");
        formData.append("modelo_id", modeloId);
        formData.append("texto", texto);
        if (file && file.files && file.files[0]) {
            formData.append("prenda_referencia", file.files[0]);
        }

        const selectedStyleInput = getInlineSelectedStyleInput(item);
        const selectedStyle = selectedStyleInput?.value?.trim() || "";
        const selectedStyleIdInput = getInlineSelectedStyleIdInput(item);
        const selectedStyleId = selectedStyleIdInput?.value?.trim() || "";
        if (selectedStyle) {
            formData.append("selected_style", selectedStyle);
        }
        if (selectedStyleId) {
            formData.append("selected_style_id", selectedStyleId);
        }

        setLoading(item, true);
        item.classList.add("is-edit-submitted");

        try {
            const response = await fetch(benditoai_ajax.ajax_url, {
                method: "POST",
                body: formData,
            });
            const data = await response.json();

            if (!data.success) {
                alert(data?.data?.message || "Error al editar");
                setLoading(item, false);
                item.classList.remove("is-edit-submitted");
                return;
            }

            const previewUrl = data?.data?.preview_url || "";
            const previewToken = data?.data?.preview_token || "";
            if (!previewUrl || !previewToken) {
                alert("No se pudo generar previsualizacion.");
                setLoading(item, false);
                item.classList.remove("is-edit-submitted");
                return;
            }

            const state = getState(item);
            state.originalUrl = currentImageUrl;
            state.previewUrl = previewUrl;
            state.previewToken = previewToken;
            state.pendingDecision = true;

            updateImageInCard(item, previewUrl);
            hideEditor(item);
            showDecision(item);
            setLoading(item, false);
            item.classList.remove("is-edit-submitted");
        } catch (error) {
            alert("Error inesperado");
            setLoading(item, false);
            item.classList.remove("is-edit-submitted");
        }
    };

    const sendDecision = async (item, decision) => {
        const state = getState(item);
        const editBtn = getEditBtn(item);
        if (!state.pendingDecision || !editBtn) return;

        const modeloId = editBtn.dataset.id || "";
        const applyBtn = getDecisionApply(item);
        const discardBtn = getDecisionDiscard(item);
        if (applyBtn) applyBtn.disabled = true;
        if (discardBtn) discardBtn.disabled = true;

        try {
            const body = new URLSearchParams();
            body.set("action", "benditoai_confirm_edit_modelo");
            body.set("modelo_id", modeloId);
            body.set("preview_token", state.previewToken);
            body.set("decision", decision);

            const response = await fetch(benditoai_ajax.ajax_url, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body.toString(),
            });
            const data = await response.json();

            if (!data.success) {
                alert(data?.data?.message || "No se pudo confirmar la edicion");
                if (applyBtn) applyBtn.disabled = false;
                if (discardBtn) discardBtn.disabled = false;
                return;
            }

            if (decision === "discard" && state.originalUrl) {
                updateImageInCard(item, state.originalUrl);
            }

            if (decision === "apply") {
                state.originalUrl = state.previewUrl;
            }

            state.previewUrl = "";
            state.previewToken = "";
            state.pendingDecision = false;

            hideDecision(item);
            if (applyBtn) applyBtn.disabled = false;
            if (discardBtn) discardBtn.disabled = false;
        } catch (error) {
            alert("Error inesperado");
            if (applyBtn) applyBtn.disabled = false;
            if (discardBtn) discardBtn.disabled = false;
        }
    };

    document.addEventListener("click", function (e) {
        const editTrigger = e.target.closest(".benditoai-edit-modelo-btn");
        if (editTrigger) {
            const item = editTrigger.closest(".benditoai-historial-item");
            if (item?.classList.contains("is-editing")) {
                hideEditor(item);
            } else {
                openInlineEditor(item);
            }
            return;
        }

        const cancelInline = e.target.closest(".benditoai-inline-edit-cancel");
        if (cancelInline) {
            const item = cancelInline.closest(".benditoai-historial-item");
            hideEditor(item);
            return;
        }

        const refTrigger = e.target.closest(".benditoai-inline-edit-ref-trigger");
        if (refTrigger) {
            const item = refTrigger.closest(".benditoai-historial-item");
            const refInput = getInlineFile(item);
            refInput?.click();
            return;
        }

        const styleOption = e.target.closest(".benditoai-style-option");
        if (styleOption) {
            const item = styleOption.closest(".benditoai-historial-item");
            if (!item) return;
            if (!item.classList.contains("is-editing")) {
                openInlineEditor(item);
            }
            const styleOptions = getStyleOptions(item);
            const isActive = styleOption.classList.contains("is-active");
            styleOptions.forEach((thumb) => {
                thumb.classList.remove("is-active");
                thumb.setAttribute("aria-pressed", "false");
            });
            if (isActive) {
                setSelectedStyle(item, "", "", "");
            } else {
                styleOption.classList.add("is-active");
                styleOption.setAttribute("aria-pressed", "true");
                const styleLabel = styleOption.dataset.styleLabel || "Estilo";
                const styleId = styleOption.dataset.styleId || "";
                const styleImage = styleOption.dataset.styleReference || "";
                setSelectedStyle(item, styleLabel, styleId, styleImage);
            }
            return;
        }

        const submitInline = e.target.closest(".benditoai-inline-edit-submit");
        if (submitInline) {
            const item = submitInline.closest(".benditoai-historial-item");
            handlePreviewEdit(item);
            return;
        }

        const applyBtn = e.target.closest(".benditoai-edit-apply-btn");
        if (applyBtn) {
            const item = applyBtn.closest(".benditoai-historial-item");
            sendDecision(item, "apply");
            return;
        }

        const discardBtn = e.target.closest(".benditoai-edit-discard-btn");
        if (discardBtn) {
            const item = discardBtn.closest(".benditoai-historial-item");
            sendDecision(item, "discard");
            return;
        }

        const toggleBtn = e.target.closest(".benditoai-toggle-info");
        if (toggleBtn) {
            const item = toggleBtn.closest(".benditoai-historial-item");
            if (item && item.classList.contains("is-editing")) return;
            const box = toggleBtn.nextElementSibling;
            if (!box) return;
            if (box.style.display === "none" || box.style.display === "") {
                box.style.display = "block";
                toggleBtn.textContent = "Ocultar detalles";
            } else {
                box.style.display = "none";
                toggleBtn.textContent = "Ver detalles";
            }
        }
    });

    document.addEventListener("change", function (e) {
        const input = e.target.closest(".benditoai-inline-edit-ref-file");
        if (!input) return;
        const item = input.closest(".benditoai-historial-item");
        const name = getInlineFileName(item);
        if (!name) return;
        const file = input.files && input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = () => {
                const src = typeof reader.result === "string" ? reader.result : "";
                syncRefTriggerPreview(item, src, "Imagen adjunta");
            };
            reader.onerror = () => {
                syncRefTriggerPreview(item, "", "");
            };
            reader.readAsDataURL(file);
        } else {
            syncRefTriggerPreview(item, "", "");
        }
        name.textContent = file ? file.name : "";
    });
});
