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

document.addEventListener("DOMContentLoaded", function () {
    let step = 1;
    const steps = document.querySelectorAll(".benditoai-step");

    function showStep(nextStep) {
        steps.forEach((stepEl) => stepEl.classList.remove("active"));
        const target = document.querySelector(`.benditoai-step[data-step="${nextStep}"]`);
        if (target) target.classList.add("active");
    }

    document.addEventListener("click", (event) => {
        if (event.target.classList.contains("benditoai-next")) {
            step += 1;
            showStep(step);
        }
    });

    document.addEventListener("click", (event) => {
        if (event.target.classList.contains("benditoai-prev")) {
            step -= 1;
            showStep(step);
        }
    });

    let productBase64 = null;
    const productInput = document.getElementById("benditoai-product-image");

    if (productInput) {
        productInput.addEventListener("change", function () {
            const file = this.files?.[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                productBase64 = event.target?.result || null;
            };
            reader.readAsDataURL(file);
        });
    }

    let useModel = "0";
    let modelUrl = null;

    document.querySelectorAll("input[name='use_model']").forEach((radio) => {
        radio.addEventListener("change", () => {
            useModel = radio.value;

            const container = document.getElementById("benditoai-modelos-container");
            if (container) {
                container.style.display = useModel === "1" ? "block" : "none";
            }

            if (useModel === "0") {
                modelUrl = null;
                const modelUrlInput = document.getElementById("model_url");
                if (modelUrlInput) modelUrlInput.value = "";

                document.querySelectorAll(".benditoai-modelo-card").forEach((card) => {
                    card.classList.remove("active");
                });

                const label = document.getElementById("benditoai-modelo-seleccionado");
                if (label) label.style.display = "none";
            }
        });
    });

    document.addEventListener("click", function (event) {
        const card = event.target.closest(".benditoai-modelo-card");
        if (!card) return;

        const url = card.dataset.url || "";
        const nombre = card.dataset.nombre || "";

        document.querySelectorAll(".benditoai-modelo-card").forEach((item) => {
            item.classList.remove("active");
        });

        card.classList.add("active");

        modelUrl = url;
        const modelUrlInput = document.getElementById("model_url");
        if (modelUrlInput) modelUrlInput.value = url;

        const box = document.getElementById("benditoai-modelo-seleccionado");
        if (box) {
            box.style.display = "block";
            const strong = box.querySelector("strong");
            if (strong) strong.innerText = nombre;
        }
    });

    const previewStage = document.getElementById("benditoai-campana-preview-stage");
    const resultImage = document.getElementById("benditoai-result-img");

    let lastPayload = null;

    async function generarCampana(payload) {
        step = 6;
        showStep(6);

        window.BenditoAIUX?.preview?.loading(previewStage, { label: "Generando campaña..." });

        try {
            const requestData = new FormData();
            requestData.append("action", "benditoai_generar_campana");

            Object.keys(payload).forEach((key) => {
                requestData.append(key, payload[key]);
            });

            const response = await fetch(benditoai_ajax.ajax_url, {
                method: "POST",
                body: requestData
            });

            const raw = await response.text();
            const parsed = benditoaiParseJsonResponse(raw);

            if (parsed?.success === true && parsed?.data?.image_url) {
                const url = `${parsed.data.image_url}?t=${Date.now()}`;

                if (resultImage) resultImage.src = url;
                window.BenditoAIUX?.preview?.image(previewStage, { imageUrl: url });

                if (typeof benditoaiActualizarTokensInstantaneo === "function") {
                    benditoaiActualizarTokensInstantaneo(parsed?.data?.tokens);
                }

                return;
            }

            const message = window.BenditoAIUX?.getErrorMessage(parsed, "Error generando campaña.");
            window.BenditoAIUX?.preview?.error(previewStage, {
                title: "No se pudo generar la campaña",
                message
            });
        } catch (_error) {
            window.BenditoAIUX?.preview?.error(previewStage, {
                title: "Error generando campaña",
                message: "Error inesperado, intenta de nuevo."
            });
        }
    }

    const form = document.getElementById("benditoai-form-campana-ai");

    if (form) {
        form.addEventListener("submit", async function (event) {
            event.preventDefault();

            const producto = form.querySelector("textarea[name='producto']")?.value?.trim() || "";
            const estilo = form.querySelector("select[name='estilo']")?.value || "";
            const tono = form.querySelector("select[name='tono']")?.value || "";

            if (!producto) {
                alert("Escribe el nombre del producto");
                return;
            }

            if (!productBase64) {
                alert("Sube una imagen del producto");
                return;
            }

            if (useModel === "1" && !modelUrl) {
                alert("Selecciona un modelo");
                return;
            }

            lastPayload = {
                producto,
                product_image: productBase64,
                use_model: useModel,
                model_url: modelUrl || "",
                estilo,
                tono
            };

            await generarCampana(lastPayload);
        });
    }

    document.getElementById("benditoai-recrear")?.addEventListener("click", () => {
        if (!lastPayload) {
            alert("No hay campaña previa");
            return;
        }
        generarCampana(lastPayload);
    });

    document.getElementById("benditoai-reset")?.addEventListener("click", () => {
        step = 1;
        showStep(1);

        lastPayload = null;
        modelUrl = null;
        productBase64 = null;

        const modelUrlInput = document.getElementById("model_url");
        if (modelUrlInput) modelUrlInput.value = "";

        document.querySelectorAll(".benditoai-modelo-card").forEach((card) => {
            card.classList.remove("active");
        });

        const label = document.getElementById("benditoai-modelo-seleccionado");
        if (label) label.style.display = "none";

        const radioNo = document.querySelector("input[name='use_model'][value='0']");
        if (radioNo) radioNo.checked = true;

        const container = document.getElementById("benditoai-modelos-container");
        if (container) container.style.display = "none";

        useModel = "0";
        window.BenditoAIUX?.preview?.reset(previewStage);
    });
});
