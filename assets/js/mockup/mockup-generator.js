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

function ensureMockupStage(resultNode) {
    if (!resultNode) return null;

    let stage = resultNode.querySelector("[data-ai-preview-stage]");
    if (!stage) {
        stage = document.createElement("div");
        stage.className = "benditoai-ai-preview-stage";
        stage.setAttribute("data-ai-preview-stage", "1");
        stage.innerHTML = `
<div class="benditoai-ai-preview-placeholder" data-ai-preview-placeholder>
    <i class="fa-regular fa-image"></i>
    <span>Tu mockup aparecerá aquí</span>
</div>
<div class="benditoai-image-wrapper" style="display:none;">
    <img class="benditoai-generated-image" src="" alt="Mockup generado por BenditoAI" />
    <a href="" download="Mockup-BenditoAI.png" class="benditoai-download-btn" title="Descargar imagen">
        <img class="benditoai-download-icon"
             src="${benditoai_ajax.plugin_url}assets/images/download-icon.png"
             alt="Descargar" />
    </a>
</div>`;
        resultNode.innerHTML = "";
        resultNode.appendChild(stage);
    }

    return stage;
}

document.addEventListener("DOMContentLoaded", function () {
    const mockupForm = document.getElementById("benditoai-form");
    if (!mockupForm) return;

    const resultNode = document.getElementById("resultado-mockup");
    if (!resultNode) return;

    const stage = ensureMockupStage(resultNode);
    const image = stage?.querySelector(".benditoai-generated-image");
    const download = stage?.querySelector(".benditoai-download-btn");

    mockupForm.addEventListener("submit", async function (event) {
        event.preventDefault();

        const formData = new FormData(mockupForm);
        formData.append("action", "benditoai_generar_mockup");

        window.BenditoAIUX?.preview?.loading(stage, { label: "Generando tu mockup..." });

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

                const grid = document.querySelector(".benditoai-historial-grid");
                if (grid) {
                    const producto = payload?.data?.producto || "-";
                    const color = payload?.data?.color || "-";
                    const entorno = payload?.data?.entorno || "-";
                    const fecha = payload?.data?.fecha || "-";

                    const nuevoItem = `
<div class="benditoai-historial-item nuevo">
    <img src="${imageUrl}" class="benditoai-historial-img" alt="Mockup generado">
    <a href="${imageUrl}" download="mockup.png" class="benditoai-btn benditoai-btn--download">⬇️ Descargar</a>
    <div class="benditoai-historial-info">
        <p><strong>Producto:</strong> ${window.BenditoAIUX.escapeHtml(producto)}</p>
        <p><strong>Color:</strong> ${window.BenditoAIUX.escapeHtml(color)}</p>
        <p><strong>Entorno:</strong> ${window.BenditoAIUX.escapeHtml(entorno)}</p>
        <p><strong>Fecha:</strong> ${window.BenditoAIUX.escapeHtml(fecha)}</p>
    </div>
</div>`;

                    grid.insertAdjacentHTML("afterbegin", nuevoItem);
                }

                if (typeof benditoaiActualizarTokensInstantaneo === "function") {
                    benditoaiActualizarTokensInstantaneo(payload?.data?.tokens);
                }

                return;
            }

            const message = window.BenditoAIUX?.getErrorMessage(payload, "Ocurrió un error al generar el mockup.");
            window.BenditoAIUX?.preview?.error(stage, {
                title: "No se pudo generar el mockup",
                message
            });
        } catch (_error) {
            window.BenditoAIUX?.preview?.error(stage, {
                title: "Error inesperado",
                message: "Intenta nuevamente."
            });
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const selectModelo = document.getElementById("modelo");
    const modeloWrap = document.getElementById("modeloWrap");
    if (!selectModelo || !modeloWrap) return;

    function toggleModelo() {
        if (selectModelo.value === "no") {
            modeloWrap.classList.add("benditoai-modelo-wrap--hidden");
        } else {
            modeloWrap.classList.remove("benditoai-modelo-wrap--hidden");
        }
    }

    toggleModelo();
    selectModelo.addEventListener("change", toggleModelo);
});

document.addEventListener("DOMContentLoaded", function () {
    const cards = document.querySelectorAll(".benditoai-modelo-card");
    const input = document.getElementById("modeloAvatarInput");
    if (!cards.length || !input) return;

    cards.forEach((card) => {
        card.addEventListener("click", () => {
            cards.forEach((item) => item.classList.remove("active"));
            card.classList.add("active");
            input.value = card.getAttribute("data-id") || "";
        });
    });
});
