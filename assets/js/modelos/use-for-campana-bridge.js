document.addEventListener("DOMContentLoaded", () => {
    const getCampaignBaseUrl = (button) => {
        const wrapper = button.closest(".benditoai-wrapper-historia-modelos");
        return (
            button.dataset.campaignUrl ||
            wrapper?.dataset.campaignUrl ||
            window.benditoaiCampaignUrl ||
            "/crea-campana/"
        );
    };

    const resolveCampaignUrl = (button, payload) => {
        const baseUrl = getCampaignBaseUrl(button);
        const url = new URL(baseUrl, window.location.origin);
        url.searchParams.set("modelo_id", String(payload.id || ""));
        url.searchParams.set("model_url", String(payload.image_url || ""));
        url.searchParams.set("modelo_nombre", String(payload.nombre || ""));
        if (payload.outfit_id) {
            url.searchParams.set("outfit_id", String(payload.outfit_id));
        }
        if (payload.outfit_tag) {
            url.searchParams.set("outfit_tag", String(payload.outfit_tag));
        }
        url.searchParams.set("source", String(payload.source || "historial"));
        return url.toString();
    };

    const getCardData = (button) => {
        const card = button.closest(".benditoai-historial-item");
        if (!card) return null;

        const id = button.dataset.modeloId || card.dataset.id || "";
        const nombre = button.dataset.modeloNombre || card.querySelector(".benditoai-historial-name")?.textContent?.trim() || "";
        const imageUrl = button.dataset.modeloImage || card.querySelector(".benditoai-historial-img")?.src || "";

        if (!id || !imageUrl) return null;

        return {
            id: String(id),
            nombre: String(nombre || "Modelo AI"),
            outfit_id: String(button.dataset.outfitId || ""),
            outfit_tag: String(button.dataset.outfitTag || ""),
            outfit_name: String(button.dataset.outfitName || ""),
            image_url: String(imageUrl),
            source: String(button.dataset.source || (button.dataset.outfitTag === "principal" ? "principal_outfit" : "modelos_historial")),
            created_at: new Date().toISOString(),
        };
    };

    const setButtonRoutingState = (button, label) => {
        button.textContent = label;
        button.classList.add("is-routing");
        button.disabled = true;
    };

    document.addEventListener("click", (event) => {
        const btn = event.target.closest(".benditoai-use-campaign-btn");
        if (!btn) return;

        const payload = getCardData(btn);
        if (!payload) return;

        try {
            localStorage.setItem("benditoai_campaign_model_ref", JSON.stringify(payload));
        } catch (error) {
            // localStorage may be blocked; continue with in-memory bridge.
        }

        document.dispatchEvent(
            new CustomEvent("benditoai:modelo:use-for-campana", {
                detail: payload,
            })
        );

        if (typeof window.benditoaiOnUseModelForCampana === "function") {
            window.benditoaiOnUseModelForCampana(payload);
        }

        setButtonRoutingState(btn, "Llevando a campana...");

        const nextUrl = resolveCampaignUrl(btn, payload);
        window.setTimeout(() => {
            window.location.href = nextUrl;
        }, 120);
    });
});
