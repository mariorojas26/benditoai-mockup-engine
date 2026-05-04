document.addEventListener("click", async function (event) {
    const button = event.target.closest(".benditoai-delete-modelo-btn");
    if (!button) return;

    const modeloId = (button.dataset.id || "").trim();
    if (!modeloId) {
        alert("ID de modelo inválido.");
        return;
    }

    if (!confirm("Eliminar este modelo?")) return;

    button.disabled = true;

    const image = button.querySelector("img");
    if (image) image.style.opacity = "0.5";

    try {
        const response = await fetch(benditoai_ajax.ajax_url, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
            },
            body: "action=benditoai_delete_modelo&modelo_id=" + encodeURIComponent(modeloId)
        });

        const rawText = await response.text();
        let data = null;

        const parseCandidates = [];
        if (rawText) {
            parseCandidates.push(rawText.trim());
            const firstBrace = rawText.indexOf("{");
            const lastBrace = rawText.lastIndexOf("}");
            if (firstBrace !== -1 && lastBrace !== -1 && lastBrace > firstBrace) {
                parseCandidates.push(rawText.slice(firstBrace, lastBrace + 1).trim());
            }
        }

        for (const candidate of parseCandidates) {
            if (!candidate) continue;
            try {
                const parsed = JSON.parse(candidate);
                if (parsed && typeof parsed === "object" && "success" in parsed) {
                    data = parsed;
                    break;
                }
            } catch (_ignored) {}
        }

        if (!data) {
            if (response.ok) {
                data = { success: true, data: { message: "Modelo eliminado" } };
            } else {
                throw new Error("Error al eliminar");
            }
        }

        if (data.success !== true) {
            const message = data && data.data && data.data.message
                ? data.data.message
                : "Error al eliminar";
            alert(message);
            button.disabled = false;
            if (image) image.style.opacity = "1";
            return;
        }

        const card = button.closest(".benditoai-historial-item") ||
            document.querySelector('.benditoai-historial-item[data-id="' + modeloId + '"]');

        if (card) {
            card.style.opacity = "0";
            card.style.transform = "scale(0.95)";

            setTimeout(function () {
                card.remove();
                const remaining = document.querySelectorAll(".benditoai-historial-item");
                const emptyMessage = document.getElementById("benditoai-empty-message");
                if (remaining.length === 0 && emptyMessage) {
                    emptyMessage.style.display = "block";
                }
            }, 200);
        }

        const successMessage = data.data && data.data.message
            ? data.data.message
            : "Modelo eliminado";
        alert(successMessage);
    } catch (error) {
        alert(error && error.message ? error.message : "Error al eliminar");
        button.disabled = false;
        if (image) image.style.opacity = "1";
    }
});
