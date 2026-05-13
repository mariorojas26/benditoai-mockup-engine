document.addEventListener("DOMContentLoaded", () => {
    const wrapper = document.querySelector(".benditoai-wrapper-historia-modelos");
    if (!wrapper) return;

    const escapeHtml = (value) => String(value || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

    const getItem = (node) => node?.closest(".benditoai-historial-item");
    const getList = (item) => item?.querySelector("[data-saved-outfits-list]");
    const getEmpty = (item) => item?.querySelector("[data-saved-outfits-empty]");
    const getSaveButton = (item) => item?.querySelector(".benditoai-save-outfit-btn");
    const getWarning = (item) => item?.querySelector("[data-outfit-warning-message]");

    const getStatsFromItem = (item) => ({
        count: Number(item?.dataset.outfitCount || 0),
        limit: Number(item?.dataset.outfitLimit || wrapper.dataset.outfitLimit || 1),
        warning: item?.dataset.outfitWarning || wrapper.dataset.outfitWarning || "Has alcanzado el límite de outfits para este modelo.",
    });

    const syncStats = (item, stats = null) => {
        if (!item) return;
        const next = stats || getStatsFromItem(item);
        const count = Number(next.count || 0);
        const limit = Number(next.limit || 1);
        const warningText = next.warning || getStatsFromItem(item).warning;
        const hasPendingDecision = item.classList.contains("is-awaiting-decision");
        const reachedLimit = count >= limit;

        item.dataset.outfitCount = String(count);
        item.dataset.outfitLimit = String(limit);
        item.dataset.outfitWarning = warningText;

        item.querySelectorAll("[data-outfit-counter]").forEach((counter) => {
            counter.textContent = counter.closest("[data-outfits-toggle]")
                ? `${count} de ${limit}`
                : `${count} de ${limit} outfits guardados`;
        });

        const warning = getWarning(item);
        if (warning) {
            warning.textContent = warningText;
            warning.hidden = !reachedLimit;
        }

        const button = getSaveButton(item);
        if (button) {
            button.disabled = reachedLimit || hasPendingDecision;
            button.setAttribute("aria-disabled", button.disabled ? "true" : "false");
            button.title = reachedLimit ? warningText : "";
        }
    };

    const syncAllSaveButtons = () => {
        wrapper.querySelectorAll(".benditoai-historial-item").forEach((item) => syncStats(item));
    };

    const renderOutfitCard = (outfit, modelData) => {
        const id = escapeHtml(outfit.id);
        const modeloId = escapeHtml(outfit.modelo_id || modelData.id);
        const modeloNombre = escapeHtml(modelData.name || "Modelo AI");
        const name = escapeHtml(outfit.nombre_outfit || "Outfit");
        const imageUrl = escapeHtml(outfit.image_url || "");

        return `
            <div
                class="benditoai-saved-outfit-card"
                data-outfit-id="${id}"
                data-modelo-id="${modeloId}"
                data-modelo-nombre="${modeloNombre}"
                data-outfit-name="${name}"
                data-outfit-image="${imageUrl}"
                role="button"
                tabindex="0"
                aria-pressed="false"
                aria-label="Usar outfit: ${name}"
            >
                <div class="benditoai-saved-outfit-thumb">
                    <img src="${imageUrl}" alt="${name}" loading="lazy" />
                    <span class="benditoai-saved-outfit-use">Usar</span>
                </div>
                <div class="benditoai-saved-outfit-body">
                    <span class="benditoai-saved-outfit-name" data-outfit-name-label>${name}</span>
                </div>
            </div>
        `;
    };

    const setOutfitsPanelOpen = (item, open = true) => {
        if (!item) return;

        const panel = item.querySelector("[data-saved-outfits-rail]");
        const toggle = item.querySelector("[data-outfits-toggle]");
        if (!panel) return;

        if (open) {
            panel.hidden = false;
            requestAnimationFrame(() => {
                panel.classList.add("is-open");
                item.classList.add("is-outfits-open");
            });
        } else {
            panel.classList.remove("is-open");
            item.classList.remove("is-outfits-open");
            window.setTimeout(() => {
                if (!panel.classList.contains("is-open")) {
                    panel.hidden = true;
                }
            }, 220);
        }

        if (toggle) {
            toggle.setAttribute("aria-expanded", open ? "true" : "false");
        }
    };

    const appendOutfitCard = (item, outfit) => {
        const list = getList(item);
        if (!list) return;

        const empty = getEmpty(item);
        if (empty) empty.remove();

        const modelData = {
            id: item.dataset.id || outfit.modelo_id || "",
            name: item.querySelector(".benditoai-historial-name")?.textContent?.trim() || "Modelo AI",
        };

        list.insertAdjacentHTML("afterbegin", renderOutfitCard(outfit, modelData));
    };

    const setButtonLoading = (button, label) => {
        if (!button) return;
        if (!button.dataset.originalHtml) {
            button.dataset.originalHtml = button.innerHTML;
        }
        button.disabled = true;
        button.innerHTML = label;
    };

    const restoreButton = (button) => {
        if (!button) return;
        button.innerHTML = button.dataset.originalHtml || button.innerHTML;
    };

    const getCleanImageUrl = (url) => String(url || "").split("?")[0];

    const ensureBaseModelSnapshot = (item) => {
        if (!item || item.dataset.baseModeloImage) return;

        const useButton = item.querySelector(".benditoai-use-campaign-btn");
        const editButton = item.querySelector(".benditoai-edit-modelo-btn");
        const mainImage = item.querySelector(".benditoai-historial-img");
        const name = item.querySelector(".benditoai-historial-name")?.textContent?.trim() || "Modelo AI";

        item.dataset.baseModeloImage = getCleanImageUrl(useButton?.dataset.modeloImage || editButton?.dataset.image || mainImage?.src || "");
        item.dataset.baseModeloName = name;
    };

    const clearSelectedOutfit = (item, stats = null) => {
        if (!item) return;
        ensureBaseModelSnapshot(item);

        const baseImage = item.dataset.baseModeloImage || "";
        const baseName = item.dataset.baseModeloName || item.querySelector(".benditoai-historial-name")?.textContent?.trim() || "Modelo AI";

        delete item.dataset.selectedOutfitId;
        delete item.dataset.selectedOutfitImage;
        delete item.dataset.selectedOutfitName;

        item.querySelectorAll(".benditoai-saved-outfit-card").forEach((outfitCard) => {
            outfitCard.classList.remove("is-selected");
            outfitCard.setAttribute("aria-pressed", "false");
        });

        if (baseImage) {
            const noCache = `${baseImage}?t=${Date.now()}`;
            const mainImage = item.querySelector(".benditoai-historial-img");
            if (mainImage) {
                mainImage.src = noCache;
                mainImage.alt = baseName;
            }

            item.querySelectorAll(".benditoai-use-campaign-btn").forEach((button) => {
                button.dataset.modeloImage = baseImage;
                button.dataset.modeloNombre = baseName;
                delete button.dataset.outfitId;
                delete button.dataset.outfitName;
                delete button.dataset.source;
            });

            item.querySelectorAll(".benditoai-edit-modelo-btn").forEach((button) => {
                button.dataset.image = baseImage;
            });

            item.querySelectorAll(".benditoai-btn--download").forEach((link) => {
                link.href = noCache;
            });
        }

        syncStats(item, stats);
    };

    const saveOutfit = async (button) => {
        const item = getItem(button);
        if (!item || button.disabled) return;

        if (item.classList.contains("is-awaiting-decision")) {
            alert("Conserva o deshaz la previsualizacion antes de guardar el outfit.");
            syncStats(item);
            return;
        }

        if (item.dataset.selectedOutfitId) {
            clearSelectedOutfit(item);
        }

        const body = new URLSearchParams();
        body.set("action", "benditoai_save_modelo_outfit");
        body.set("modelo_id", button.dataset.modeloId || item.dataset.id || "");

        setButtonLoading(button, '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i><span>Guardando...</span>');

        try {
            const response = await fetch(benditoai_ajax.ajax_url, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body.toString(),
            });
            const data = await response.json();

            restoreButton(button);
            if (!data.success) {
                alert(data?.data?.message || "No se pudo guardar el outfit");
                if (data?.data?.stats) syncStats(item, data.data.stats);
                else syncStats(item);
                return;
            }

            appendOutfitCard(item, data.data.outfit);
            syncStats(item, data.data.stats);
            setOutfitsPanelOpen(item, true);
        } catch (error) {
            restoreButton(button);
            alert("Error inesperado");
            syncStats(item);
        }
    };

    const selectOutfitAsMain = (card) => {
        const item = getItem(card);
        if (!item) return;

        const payload = {
            id: String(card.dataset.modeloId || item.dataset.id || ""),
            outfit_id: String(card.dataset.outfitId || ""),
            nombre: String(card.dataset.outfitName || card.dataset.modeloNombre || item.querySelector(".benditoai-historial-name")?.textContent?.trim() || "Modelo AI"),
            outfit_name: String(card.dataset.outfitName || "Outfit"),
            image_url: String(card.dataset.outfitImage || ""),
            source: "saved_outfit",
            created_at: new Date().toISOString(),
        };

        if (!payload.id || !payload.image_url) return;

        ensureBaseModelSnapshot(item);

        item.querySelectorAll(".benditoai-saved-outfit-card").forEach((outfitCard) => {
            outfitCard.classList.toggle("is-selected", outfitCard === card);
            outfitCard.setAttribute("aria-pressed", outfitCard === card ? "true" : "false");
        });

        item.dataset.selectedOutfitId = payload.outfit_id;
        item.dataset.selectedOutfitImage = payload.image_url;
        item.dataset.selectedOutfitName = payload.outfit_name;
        syncStats(item);

        const noCache = `${payload.image_url}?t=${Date.now()}`;
        const mainImage = item.querySelector(".benditoai-historial-img");
        if (mainImage) {
            mainImage.src = noCache;
            mainImage.alt = payload.outfit_name;
        }

        item.querySelectorAll(".benditoai-use-campaign-btn").forEach((button) => {
            button.dataset.modeloImage = payload.image_url;
            button.dataset.modeloNombre = payload.outfit_name;
            button.dataset.outfitId = payload.outfit_id;
            button.dataset.outfitName = payload.outfit_name;
            button.dataset.source = "saved_outfit";
        });

        item.querySelectorAll(".benditoai-edit-modelo-btn").forEach((button) => {
            button.dataset.image = payload.image_url;
        });

        item.querySelectorAll(".benditoai-btn--download").forEach((link) => {
            link.href = noCache;
        });

        try {
            localStorage.setItem("benditoai_campaign_model_ref", JSON.stringify(payload));
            localStorage.setItem("benditoai_selected_model", JSON.stringify({
                id: payload.id,
                name: payload.nombre,
                outfit_id: payload.outfit_id,
                outfit_name: payload.outfit_name,
                image_url: payload.image_url,
            }));
        } catch (error) {
            // Continue with current page state.
        }

        const target = item.querySelector(".benditoai-img-wrap") || item;
        target.scrollIntoView({ behavior: "smooth", block: "center" });
    };

    window.benditoaiDeleteSelectedOutfit = async (item, button) => {
        if (!item?.dataset.selectedOutfitId) return false;

        const outfitId = item.dataset.selectedOutfitId;
        if (!window.confirm("Eliminar este outfit guardado?")) return true;

        if (button) button.disabled = true;

        const body = new URLSearchParams();
        body.set("action", "benditoai_delete_modelo_outfit");
        body.set("outfit_id", outfitId);

        try {
            const response = await fetch(benditoai_ajax.ajax_url, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body.toString(),
            });
            const data = await response.json();

            if (!data.success) {
                alert(data?.data?.message || "No se pudo eliminar el outfit");
                if (button) button.disabled = false;
                return true;
            }

            const selectedCard = Array.from(item.querySelectorAll(".benditoai-saved-outfit-card"))
                .find((candidate) => String(candidate.dataset.outfitId || "") === String(outfitId));
            if (selectedCard) selectedCard.remove();

            const list = getList(item);
            if (list && !list.querySelector(".benditoai-saved-outfit-card")) {
                list.insertAdjacentHTML("beforeend", '<p class="benditoai-saved-outfits-empty" data-saved-outfits-empty>Aun no tienes outfits guardados para este modelo.</p>');
            }

            clearSelectedOutfit(item, data.data?.stats || null);
            if (button) button.disabled = false;
            return true;
        } catch (error) {
            alert("Error inesperado");
            if (button) button.disabled = false;
            return true;
        }
    };

    window.benditoaiClearSelectedOutfit = clearSelectedOutfit;

    const updateOutfitImage = (card, imageUrl) => {
        if (!card || !imageUrl) return;
        const noCache = `${imageUrl}?t=${Date.now()}`;
        const img = card.querySelector(".benditoai-saved-outfit-thumb img");
        if (img) img.src = noCache;
        card.dataset.outfitImage = imageUrl;
    };

    document.addEventListener("click", (event) => {
        const saveButton = event.target.closest(".benditoai-save-outfit-btn");
        if (saveButton) {
            saveOutfit(saveButton);
            return;
        }

        const outfitsToggle = event.target.closest("[data-outfits-toggle]");
        if (outfitsToggle) {
            const item = getItem(outfitsToggle);
            const isOpen = outfitsToggle.getAttribute("aria-expanded") === "true";
            setOutfitsPanelOpen(item, !isOpen);
            return;
        }

        const card = event.target.closest(".benditoai-saved-outfit-card");
        if (card && !event.target.closest("button, input, textarea, label")) {
            selectOutfitAsMain(card);
        }
    });

    document.addEventListener("keydown", (event) => {
        const card = event.target.closest(".benditoai-saved-outfit-card");
        if (!card || !["Enter", " "].includes(event.key)) return;
        if (event.target.closest("button, input, textarea")) return;
        event.preventDefault();
        selectOutfitAsMain(card);
    });

    document.addEventListener("benditoai:outfit-updated", (event) => {
        const outfitId = String(event.detail?.outfit_id || "");
        const imageUrl = String(event.detail?.image_url || "");
        if (!outfitId || !imageUrl) return;

        const card = Array.from(wrapper.querySelectorAll(".benditoai-saved-outfit-card"))
            .find((candidate) => String(candidate.dataset.outfitId || "") === outfitId);
        if (!card) return;

        updateOutfitImage(card, imageUrl);
        if (card.classList.contains("is-selected")) {
            selectOutfitAsMain(card);
        }
    });

    const observedItems = new WeakSet();
    const observeItem = (item) => {
        if (!item || observedItems.has(item)) return;
        observedItems.add(item);
        observer.observe(item, { attributes: true, attributeFilter: ["class"] });
    };

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type !== "attributes" || mutation.attributeName !== "class") return;
            const item = mutation.target.closest?.(".benditoai-historial-item");
            if (item) syncStats(item);
        });
    });

    wrapper.querySelectorAll(".benditoai-historial-item").forEach((item) => {
        observeItem(item);
        syncStats(item);
    });

    document.addEventListener("benditoai:historial-updated", () => {
        wrapper.querySelectorAll(".benditoai-historial-item").forEach((item) => {
            observeItem(item);
            syncStats(item);
        });
    });

    syncAllSaveButtons();
});
