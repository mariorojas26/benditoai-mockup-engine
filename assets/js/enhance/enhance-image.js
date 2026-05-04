function benditoaiParseJsonResponse(raw) {
    if (!raw) return null;

    try {
        return JSON.parse(raw);
    } catch (_e) {}

    const first = raw.indexOf("{");
    const last = raw.lastIndexOf("}");

    if (first === -1 || last === -1 || last <= first) return null;

    try {
        return JSON.parse(raw.slice(first, last + 1).trim());
    } catch (_e) {
        return null;
    }
}

document.addEventListener("submit", async function (event) {
    if (event.target.id !== "benditoai-enhance-image-form") return;
    event.preventDefault();

    const formData = new FormData(event.target);
    formData.append("action", "benditoai_enhance_image");

    const result = document.getElementById("benditoai-enhance-result");
    if (!result) return;

    const stage = result.querySelector("[data-ai-preview-stage]");
    const image = result.querySelector(".benditoai-generated-image");
    const download = result.querySelector(".benditoai-download-btn");

    window.BenditoAIUX?.preview?.loading(stage, { label: "Mejorando imagen..." });

    try {
        const response = await fetch(benditoai_ajax.ajax_url, {
            method: "POST",
            body: formData
        });

        const raw = await response.text();
        const payload = benditoaiParseJsonResponse(raw);

        if (payload?.success === true && payload?.data?.image_url) {
            const imageUrl = payload.data.image_url;

            if (image) image.src = imageUrl;
            if (download) download.href = imageUrl;

            window.BenditoAIUX?.preview?.image(stage, { imageUrl });

            if (typeof benditoaiActualizarTokensInstantaneo === "function") {
                benditoaiActualizarTokensInstantaneo(payload?.data?.tokens);
            }

            return;
        }

        const message = window.BenditoAIUX?.getErrorMessage(payload, "No se pudo mejorar la imagen.");
        window.BenditoAIUX?.preview?.error(stage, {
            title: "No se pudo mejorar la imagen",
            message
        });
    } catch (_error) {
        window.BenditoAIUX?.preview?.error(stage, {
            title: "Error al mejorar imagen",
            message: "Error inesperado. Intenta nuevamente."
        });
    }
});
