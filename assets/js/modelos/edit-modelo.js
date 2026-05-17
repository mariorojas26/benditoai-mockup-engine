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
    const getInlineSelectedStyleRemove = (item) => item?.querySelector(".benditoai-inline-edit-style-remove");
    const promptDebugger = document.querySelector("[data-prompt-debugger]");
    const promptDebuggerToggle = promptDebugger?.querySelector("[data-prompt-debugger-toggle]");
    const promptDebuggerMinimize = promptDebugger?.querySelector("[data-prompt-debugger-minimize]");
    const promptDebuggerOutput = promptDebugger?.querySelector("[data-prompt-debug-output]");
    const promptDebuggerModel = promptDebugger?.querySelector("[data-prompt-debug-model]");
    const promptDebuggerOutfit = promptDebugger?.querySelector("[data-prompt-debug-outfit]");
    const promptDebuggerMode = promptDebugger?.querySelector("[data-prompt-debug-mode]");

    const getDecision = (item) => item?.querySelector(".benditoai-edit-decision");
    const getDecisionAdd = (item) => item?.querySelector(".benditoai-edit-add-btn");
    const getDecisionReplace = (item) => item?.querySelector(".benditoai-edit-replace-btn");
    const getAllEditButtons = (item) => item ? Array.from(item.querySelectorAll(".benditoai-edit-modelo-btn")) : [];
    const getPanelCampaignButtons = (item) => item ? Array.from(item.querySelectorAll(".benditoai-use-campaign-btn--panel")) : [];
    let activeDebugItem = null;
    const escapeDebuggerHtml = (value) => String(value || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
    const formatDebuggerText = (value) => escapeDebuggerHtml(value).replace(/\n/g, "<br>");
    const formatFinalWithDynamicHighlights = (finalText, dynamicParts = []) => {
        let escapedFinal = escapeDebuggerHtml(finalText || "");
        const uniqueParts = Array.from(new Set(
            (dynamicParts || [])
                .map((part) => String(part || "").trim())
                .filter((part) => part !== "")
        )).sort((a, b) => b.length - a.length);

        uniqueParts.forEach((part) => {
            const escapedPart = escapeDebuggerHtml(part);
            if (!escapedPart) return;
            escapedFinal = escapedFinal.split(escapedPart).join(
                `<span class="benditoai-prompt-dynamic-inline">${escapedPart}</span>`
            );
        });

        return escapedFinal.replace(/\n/g, "<br>");
    };

    const isVisibleHistoryItem = (item) => {
        if (!item) return false;
        if (item.hidden) return false;
        if (item.getAttribute("aria-hidden") === "true") return false;
        if (item.style.display === "none") return false;
        return document.body.contains(item);
    };

    const getActiveDebugItem = (fallback = null) => {
        if (fallback && isVisibleHistoryItem(fallback)) return fallback;
        if (isVisibleHistoryItem(activeDebugItem)) return activeDebugItem;
        return document.querySelector(".benditoai-historial-item.is-editing") ||
            document.querySelector(".benditoai-historial-item:not([hidden])");
    };

    const getActiveStyleData = (item) => {
        const selectedStyle = getInlineSelectedStyleInput(item)?.value?.trim() || "";
        const selectedStyleId = getInlineSelectedStyleIdInput(item)?.value?.trim() || "";
        const activeOption = item?.querySelector(".benditoai-style-option.is-active");
        return {
            label: selectedStyle || activeOption?.dataset.styleLabel || "",
            id: selectedStyleId || activeOption?.dataset.styleId || "",
            hint: activeOption?.dataset.stylePrompt || "",
        };
    };

    const getDebugRequest = (item) => {
        const manualText = getInlineText(item)?.value?.trim() || "";
        const file = getInlineFile(item);
        const hasReference = Boolean(file && file.files && file.files[0]);
        const style = getActiveStyleData(item);
        let request = manualText;
        let mode = "Texto manual";

        if (!request && hasReference) {
            request = "Cambia exactamente la prenda de la imagen adjunta por la del modelo, sin cambiar su rostro ni su pose. Ajusta la prenda perfectamente, de forma fiel, realista y natural.";
            mode = style.label ? "Prenda + estilo" : "Prenda adjunta";
        } else if (!request && style.label) {
            request = `Viste al modelo con ropa aleatoriamente al estilo ${style.label}, manteniendo el rostro del modelo fiel y su pose.`;
            mode = "Estilo automatico";
        } else if (request && hasReference && style.label) {
            mode = "Texto + prenda + estilo";
        } else if (request && hasReference) {
            mode = "Texto + prenda";
        } else if (request && style.label) {
            mode = "Texto + estilo";
        } else if (!request) {
            mode = "Esperando datos";
        }

        return { request, mode, hasReference, style };
    };

    const buildPromptPreview = (item) => {
        if (!item) {
            return {
                model: "Modelo: sin editor",
                outfit: "Outfit: principal",
                mode: "Modo: esperando",
                base: "Abre el editor para ver la plantilla base usada en este flujo.",
                dynamic: "Escribe texto, sube prenda o selecciona estilo para ver este bloque dinamico.",
                prompt: "Abre el editor, escribe, selecciona un estilo o sube una prenda para ver aqui el prompt en tiempo real.",
            };
        }

        const modelName = item.querySelector(".benditoai-historial-name")?.textContent?.trim() ||
            item.dataset.baseModeloName ||
            "Modelo AI";
        const outfitName = item.dataset.selectedOutfitName ||
            item.dataset.baseModeloName ||
            "Principal";
        const outfitTag = item.dataset.selectedOutfitTag || "principal";
        const debug = getDebugRequest(item);
        const styleHint = debug.style.hint || (debug.style.label ? `Estilo preferido seleccionado por el usuario: ${debug.style.label}. Manten esta direccion de estilo respetando la solicitud exacta de cambio de prenda.` : "");
        const contextRules = styleHint ? `Pista de referencia: ${styleHint}\n` : "";
        const dynamicLines = [];
        dynamicLines.push(`Solicitud de cambio del usuario:\n${debug.request || "(vacio)"}`);
        dynamicLines.push(`Estilo seleccionado (label): ${debug.style.label || "(sin estilo seleccionado)"}`);
        dynamicLines.push(`Estilo seleccionado (id): ${debug.style.id || "(sin id de estilo)"}`);
        dynamicLines.push(`Prenda de referencia adjunta: ${debug.hasReference ? "SI" : "NO"}`);
        if (styleHint) {
            dynamicLines.push(`Pista de estilo:\n${styleHint}`);
        }
        const dynamicBlock = dynamicLines.join("\n\n");

        const baseGeneralHeader = "Edita esta imagen.\n\nMisma persona, rostro, cuerpo, pose, fondo, iluminacion y camara.";
        const basePreciseRules = "Edicion puntual: cambia SOLO la prenda objetivo.\nNo alteres otras prendas, identidad ni escena.\nSi son zapatos, reemplaza solo calzado y conserva pantalon.\nResultado natural, fotorrealista, 4K.";
        const baseRestyleRules = "Reestiliza SOLO el outfit (ropa/accesorios/calzado) segun el estilo.\nLook coherente, natural y comercial.\nNo cambies identidad ni escena.\nCalidad fotorrealista, 4K.";
        const baseGarmentStyleRules = "La prenda de referencia tiene prioridad.\nEl estilo solo armoniza el resto del look.\nMantener identidad y escena sin cambios.\nCalidad fotorrealista, 4K.";

        let baseBlock = "";
        let prompt = "";
        let referenceDynamicLine = "";

        if (!debug.request) {
            baseBlock = "Aun no hay flujo activo.";
            prompt = "Todavia no hay prompt para enviar.\n\nAgrega texto, sube una prenda o selecciona un estilo para ver el prompt armado.";
        } else if (!debug.hasReference && debug.style.label && debug.mode === "Estilo automatico") {
            baseBlock = `${baseGeneralHeader}\n\nNo se adjunto imagen de prenda de referencia.\nCrea vestuario nuevo segun el estilo.\n\n${baseRestyleRules}`;
            prompt = `Edita esta imagen.\n\nMisma persona, rostro, cuerpo, pose, fondo, iluminacion y camara.\n\nSolicitud:\n${debug.request}\n\nNo se adjunto referencia. Crea vestuario nuevo segun el estilo.\n${contextRules}\nReestiliza SOLO el outfit con resultado coherente, natural y comercial.\nNo generes otra persona ni redisenes la escena.\nCalidad fotorrealista, 4K.`;
        } else if (debug.hasReference && debug.style.label) {
            referenceDynamicLine = "Se adjunta PRENDA DE REFERENCIA: aplicala fielmente (tipo, material, color y detalles clave).";
            baseBlock = `${baseGeneralHeader}\n\nSe adjunta PRENDA DE REFERENCIA: aplicala fielmente.\n\n${baseGarmentStyleRules}`;
            prompt = `Edita esta imagen.\n\nMisma persona, rostro, cuerpo, pose, fondo, iluminacion y camara.\n\nSolicitud:\n${debug.request}\n\nSe adjunta PRENDA DE REFERENCIA: aplicala fielmente (tipo, material, color y detalles clave).\n${contextRules}\nLa prenda de referencia tiene prioridad; el estilo solo armoniza el resto del look.\nNo cambies identidad ni escena.\nCalidad fotorrealista, 4K.`;
        } else {
            const referenceRules = debug.hasReference
                ? "Se adjunta PRENDA DE REFERENCIA: reemplaza SOLO la prenda solicitada y manten el resto intacto."
                : "Sin referencia: aplica solo la solicitud del usuario en la prenda objetivo.";
            if (debug.hasReference) {
                referenceDynamicLine = "Se adjunta PRENDA DE REFERENCIA: reemplaza SOLO la prenda solicitada y manten el resto intacto.";
            }
            baseBlock = `${baseGeneralHeader}\n\n${referenceRules}\n\n${basePreciseRules}`;
            prompt = `Edita esta imagen.\n\nMisma persona, rostro, cuerpo, pose, fondo, iluminacion y camara.\n\nSolicitud:\n${debug.request}\n\n${referenceRules}\n${contextRules}Edicion puntual: cambia SOLO la prenda objetivo; no alteres otras prendas ni identidad.\nSi son zapatos, reemplaza solo calzado y conserva pantalon.\nSin redisenar escena. Resultado natural, fotorrealista, 4K.`;
        }

        return {
            model: `Modelo: ${modelName}`,
            outfit: `Outfit: ${outfitTag === "principal" ? "Principal" : outfitName}`,
            mode: `Modo: ${debug.mode}`,
            base: baseBlock,
            dynamic: dynamicBlock,
            prompt,
            dynamic_for_final: [
                debug.request || "",
                referenceDynamicLine,
                styleHint ? `Pista de referencia: ${styleHint}` : "",
            ],
        };
    };

    const updatePromptDebugger = (item = null) => {
        if (!promptDebugger || !promptDebuggerOutput) return;
        const activeItem = getActiveDebugItem(item);
        if (activeItem) activeDebugItem = activeItem;
        const preview = buildPromptPreview(activeItem);
        if (promptDebuggerModel) promptDebuggerModel.textContent = preview.model;
        if (promptDebuggerOutfit) promptDebuggerOutfit.textContent = preview.outfit;
        if (promptDebuggerMode) promptDebuggerMode.textContent = preview.mode;
        promptDebuggerOutput.innerHTML = [
            `<div class="benditoai-prompt-unified">`,
            `<div class="benditoai-prompt-unified-part is-base">`,
            `<span class="benditoai-prompt-unified-tag">BASE</span>`,
            `<div class="benditoai-prompt-unified-text">${formatDebuggerText(preview.base)}</div>`,
            `</div>`,
            `<div class="benditoai-prompt-unified-part is-dynamic">`,
            `<span class="benditoai-prompt-unified-tag">DINAMICO</span>`,
            `<div class="benditoai-prompt-unified-text">${formatDebuggerText(preview.dynamic)}</div>`,
            `</div>`,
            `<div class="benditoai-prompt-unified-part is-final">`,
            `<span class="benditoai-prompt-unified-tag">FINAL</span>`,
            `<div class="benditoai-prompt-unified-text">${formatFinalWithDynamicHighlights(preview.prompt, preview.dynamic_for_final || [])}</div>`,
            `</div>`,
            `</div>`,
        ].join("");
    };

    const schedulePromptDebuggerUpdate = (item = null) => {
        window.requestAnimationFrame(() => updatePromptDebugger(item));
    };

    const setPromptDebuggerOpen = (isOpen) => {
        if (!promptDebugger || !promptDebuggerToggle) return;
        promptDebugger.classList.toggle("is-collapsed", !isOpen);
        promptDebuggerToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
        if (isOpen) schedulePromptDebuggerUpdate();
    };

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
        activeDebugItem = item;
        item.classList.add("is-editing");
        setEditButtonsActiveState(item, true);
        setCampaignButtonsEditingState(item, true);
        edit.hidden = false;
        const text = getInlineText(item);
        if (text) {
            window.setTimeout(() => text.focus(), 40);
        }
        schedulePromptDebuggerUpdate(item);
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
        schedulePromptDebuggerUpdate(item);
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
            triggerText.textContent = "Opcional: sube una foto de tu prenda para vestir al modelo con ella.";
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
            triggerText.textContent = "Opcional: sube una foto de tu prenda para vestir al modelo con ella.";
        };
        previewImg.src = imageUrl;
        triggerText.textContent = labelText || "Referencia lista";
    };

    const setSelectedStyle = (item, styleLabel, styleId) => {
        const selectedStyleInput = getInlineSelectedStyleInput(item);
        const selectedStyleIdInput = getInlineSelectedStyleIdInput(item);
        const selectedStyleBlock = getInlineSelectedStyleBlock(item);
        const selectedStyleValue = getInlineSelectedStyleValue(item);
        const selectedStyleRemove = getInlineSelectedStyleRemove(item);
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
            if (selectedStyleRemove) selectedStyleRemove.hidden = true;
            schedulePromptDebuggerUpdate(item);
            return;
        }

        selectedStyleValue.textContent = normalizedStyle;
        selectedStyleBlock.hidden = false;
        if (selectedStyleRemove) selectedStyleRemove.hidden = false;
        schedulePromptDebuggerUpdate(item);
    };

    const clearSelectedStyle = (item) => {
        getStyleOptions(item).forEach((thumb) => {
            thumb.classList.remove("is-active");
            thumb.setAttribute("aria-pressed", "false");
        });
        setSelectedStyle(item, "", "");
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

    const syncDecisionButtons = (item, stats = null) => {
        const addBtn = getDecisionAdd(item);
        const replaceBtn = getDecisionReplace(item);
        const canAdd = Boolean(stats?.can_add ?? true);
        const warning = stats?.warning || "No tienes mas espacios disponibles para agregar outfits.";

        if (addBtn) {
            addBtn.disabled = !canAdd;
            addBtn.setAttribute("aria-disabled", canAdd ? "false" : "true");
            addBtn.title = canAdd ? "" : warning;
        }

        if (replaceBtn) {
            replaceBtn.disabled = false;
            replaceBtn.setAttribute("aria-disabled", "false");
        }
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
        const downloadBtns = item ? Array.from(item.querySelectorAll("a[download]")) : [];
        const campaignUseBtns = item ? Array.from(item.querySelectorAll(".benditoai-use-campaign-btn")) : [];
        if (!img || !imageUrl) return;

        const noCache = `${imageUrl}?t=${Date.now()}`;
        img.src = noCache;
        item.dataset.liveModeloImage = imageUrl;
        if (item.dataset.selectedOutfitId) {
            item.dataset.selectedOutfitImage = imageUrl;
        }
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

        const modeloId = editBtn.dataset.id || "";
        const currentImageUrl = editBtn.dataset.image || "";
        const selectedOutfitId = item.dataset.selectedOutfitId || "";
        const selectedStyleInput = getInlineSelectedStyleInput(item);
        const selectedStyle = selectedStyleInput?.value?.trim() || "";
        const selectedStyleIdInput = getInlineSelectedStyleIdInput(item);
        const selectedStyleId = selectedStyleIdInput?.value?.trim() || "";
        let texto = text.value.trim();
        const hasReferenceGarment = Boolean(file && file.files && file.files[0]);
        const isAutoGarmentPrompt = !texto && hasReferenceGarment;
        const isAutoStylePrompt = !texto && !hasReferenceGarment && selectedStyle;

        if (isAutoGarmentPrompt) {
            texto = "Cambia exactamente la prenda de la imagen adjunta por la del modelo, sin cambiar su rostro ni su pose. Ajusta la prenda perfectamente, de forma fiel, realista y natural.";
        } else if (isAutoStylePrompt) {
            texto = `Viste al modelo con ropa aleatoriamente al estilo ${selectedStyle}, manteniendo el rostro del modelo fiel y su pose.`;
        }

        if (!texto) {
            alert("Escribe que deseas cambiar, sube una prenda o selecciona un estilo.");
            return;
        }

        const formData = new FormData();
        formData.append("action", selectedOutfitId ? "benditoai_preview_edit_modelo_outfit" : "benditoai_preview_edit_modelo");
        if (selectedOutfitId) {
            formData.append("outfit_id", selectedOutfitId);
        } else {
            formData.append("modelo_id", modeloId);
        }
        formData.append("texto", texto);
        if (isAutoStylePrompt) {
            formData.append("style_only_prompt", "1");
        }
        if (isAutoGarmentPrompt) {
            formData.append("garment_only_prompt", "1");
        }
        if (hasReferenceGarment) {
            formData.append("prenda_referencia", file.files[0]);
        }

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
            syncDecisionButtons(item, data?.data?.stats || null);
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
        const selectedOutfitId = item.dataset.selectedOutfitId || "";
        const addBtn = getDecisionAdd(item);
        const replaceBtn = getDecisionReplace(item);
        if (addBtn) addBtn.disabled = true;
        if (replaceBtn) replaceBtn.disabled = true;

        try {
            const body = new URLSearchParams();
            body.set("action", selectedOutfitId ? "benditoai_confirm_edit_modelo_outfit" : "benditoai_confirm_edit_modelo");
            if (selectedOutfitId) {
                body.set("outfit_id", selectedOutfitId);
            } else {
                body.set("modelo_id", modeloId);
            }
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
                syncDecisionButtons(item, data?.data?.stats || null);
                return;
            }

            const finalImage = data?.data?.image_url || state.previewUrl;
            if (finalImage) {
                updateImageInCard(item, finalImage);
            }

            const outfitItem = data?.data?.outfit || null;
            if (outfitItem && outfitItem.id) {
                document.dispatchEvent(new CustomEvent("benditoai:outfit-committed", {
                    detail: {
                        decision,
                        modelo_id: modeloId,
                        outfit: outfitItem,
                        stats: data?.data?.stats || null,
                        active_outfit_id: data?.data?.active_outfit_id || outfitItem.id,
                        active_outfit_tag: data?.data?.active_outfit_tag || outfitItem.outfit_tag || "outfit",
                    },
                }));
            } else if (selectedOutfitId && finalImage) {
                document.dispatchEvent(new CustomEvent("benditoai:outfit-updated", {
                    detail: {
                        outfit_id: selectedOutfitId,
                        image_url: finalImage,
                    },
                }));
            }

            if ((data?.data?.active_outfit_tag || "") === "principal") {
                item.dataset.baseModeloImage = finalImage;
                item.dataset.baseModeloName = data?.data?.outfit?.nombre_outfit || item.dataset.baseModeloName || "";
            }

            state.previewUrl = "";
            state.previewToken = "";
            state.pendingDecision = false;
            state.originalUrl = finalImage || state.originalUrl;

            hideDecision(item);
            syncDecisionButtons(item, data?.data?.stats || null);
        } catch (error) {
            alert("Error inesperado");
            syncDecisionButtons(item, null);
        }
    };

    document.addEventListener("click", function (e) {
        const debugToggle = e.target.closest("[data-prompt-debugger-toggle]");
        if (debugToggle) {
            const isOpen = promptDebugger?.classList.contains("is-collapsed");
            setPromptDebuggerOpen(Boolean(isOpen));
            return;
        }

        const debugMinimize = e.target.closest("[data-prompt-debugger-minimize]");
        if (debugMinimize) {
            setPromptDebuggerOpen(false);
            return;
        }

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

        const desktopCurtain = e.target.closest(".benditoai-desktop-edit-curtain");
        if (desktopCurtain) {
            const item = desktopCurtain.closest(".benditoai-historial-item");
            if (item?.classList.contains("is-editing")) {
                hideEditor(item);
                schedulePromptDebuggerUpdate(item);
            }
            return;
        }

        const cancelInline = e.target.closest(".benditoai-inline-edit-cancel");
        if (cancelInline) {
            const item = cancelInline.closest(".benditoai-historial-item");
            hideEditor(item);
            schedulePromptDebuggerUpdate(item);
            return;
        }

        const refTrigger = e.target.closest(".benditoai-inline-edit-ref-trigger");
        if (refTrigger) {
            const item = refTrigger.closest(".benditoai-historial-item");
            const refInput = getInlineFile(item);
            refInput?.click();
            return;
        }

        const removeStyle = e.target.closest(".benditoai-inline-edit-style-remove");
        if (removeStyle) {
            const item = removeStyle.closest(".benditoai-historial-item");
            clearSelectedStyle(item);
            schedulePromptDebuggerUpdate(item);
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
                clearSelectedStyle(item);
            } else {
                styleOption.classList.add("is-active");
                styleOption.setAttribute("aria-pressed", "true");
                const styleLabel = styleOption.dataset.styleLabel || "Estilo";
                const styleId = styleOption.dataset.styleId || "";
                setSelectedStyle(item, styleLabel, styleId);
            }
            schedulePromptDebuggerUpdate(item);
            return;
        }

        const outfitCard = e.target.closest(".benditoai-saved-outfit-card");
        if (outfitCard) {
            const item = outfitCard.closest(".benditoai-historial-item");
            if (item) {
                activeDebugItem = item;
                window.setTimeout(() => updatePromptDebugger(item), 80);
            }
        }

        const historyNav = e.target.closest("[data-history-index], [data-history-page='prev'], [data-history-page='next']");
        if (historyNav) {
            window.setTimeout(() => {
                activeDebugItem = null;
                updatePromptDebugger();
            }, 80);
        }

        const submitInline = e.target.closest(".benditoai-inline-edit-submit");
        if (submitInline) {
            const item = submitInline.closest(".benditoai-historial-item");
            handlePreviewEdit(item);
            return;
        }

        const addBtn = e.target.closest(".benditoai-edit-add-btn");
        if (addBtn) {
            const item = addBtn.closest(".benditoai-historial-item");
            sendDecision(item, "add");
            return;
        }

        const replaceBtn = e.target.closest(".benditoai-edit-replace-btn");
        if (replaceBtn) {
            const item = replaceBtn.closest(".benditoai-historial-item");
            sendDecision(item, "replace");
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

    document.addEventListener("input", function (e) {
        const text = e.target.closest(".benditoai-inline-edit-text");
        if (!text) return;
        const item = text.closest(".benditoai-historial-item");
        if (item) {
            activeDebugItem = item;
            schedulePromptDebuggerUpdate(item);
        }
    });

    document.addEventListener("change", function (e) {
        const input = e.target.closest(".benditoai-inline-edit-ref-file");
        if (!input) return;
        const item = input.closest(".benditoai-historial-item");
        if (item) activeDebugItem = item;
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
        schedulePromptDebuggerUpdate(item);
    });

    document.addEventListener("benditoai:outfit-committed", (event) => {
        const modeloId = String(event?.detail?.modelo_id || "");
        const item = modeloId
            ? Array.from(document.querySelectorAll(".benditoai-historial-item"))
                .find((candidate) => String(candidate.dataset.id || "") === modeloId)
            : activeDebugItem;
        schedulePromptDebuggerUpdate(item);
    });

    document.addEventListener("benditoai:outfit-updated", () => {
        schedulePromptDebuggerUpdate(activeDebugItem);
    });

    schedulePromptDebuggerUpdate();
});
