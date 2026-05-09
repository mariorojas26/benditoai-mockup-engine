function benditoaiGetAdminToggles() {
    return Array.from(document.querySelectorAll(".benditoai-admin-unlimited-toggle"));
}

function benditoaiSyncAdminToggles(checked, sourceToggle) {
    const toggles = benditoaiGetAdminToggles();

    toggles.forEach((toggle) => {
        if (toggle !== sourceToggle) {
            toggle.checked = checked;
        }
    });
}

function benditoaiPersistAdminUnlimited(checked) {
    if (!window.benditoai_ajax || !benditoai_ajax.ajax_url) {
        return Promise.resolve();
    }

    return fetch(benditoai_ajax.ajax_url, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "action=benditoai_toggle_admin_tokens&enabled=" + (checked ? "yes" : "no")
    }).catch(() => {});
}

window.benditoaiTokensManager = {

    esIlimitadoActivo() {
        return benditoaiGetAdminToggles().some((toggle) => toggle.checked);
    },

    normalizarDisplay(tokens) {
        if (this.esIlimitadoActivo()) {
            return "\u221E";
        }

        const numericTokens = Number(tokens);

        if (Number.isFinite(numericTokens)) {
            return String(Math.trunc(numericTokens)).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        return tokens;
    },

    actualizar(tokens) {

        // Si el backend envia tokens
        if (tokens !== undefined) {
            const tokenElements = document.querySelectorAll(".benditoai-user-tokens");
            const display = this.normalizarDisplay(tokens);

            tokenElements.forEach((el) => {
                el.textContent = display;
            });

            return;
        }

        // Fallback si no envia tokens
        this.refrescar();
    },

    refrescar() {

        fetch(benditoai_ajax.ajax_url, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=benditoai_get_tokens"
        })
        .then((res) => res.json())
        .then((data) => {

            if (!data.success) return;

            const tokenElements = document.querySelectorAll(".benditoai-user-tokens");
            const display = this.normalizarDisplay(data.data.tokens);

            tokenElements.forEach((el) => {
                el.textContent = display;
            });

        });
    }

};

window.benditoaiActualizarTokensInstantaneo = function(tokens) {
    window.benditoaiTokensManager.actualizar(tokens);
};

function benditoaiInitAdminUnlimitedToggles() {
    const toggles = benditoaiGetAdminToggles();
    if (!toggles.length) {
        return;
    }

    toggles.forEach((toggle) => {
        if (toggle.dataset.benditoaiBound === "1") {
            return;
        }

        toggle.dataset.benditoaiBound = "1";

        toggle.addEventListener("pointerdown", function(event) {
            event.stopPropagation();
        });

        toggle.addEventListener("click", function(event) {
            event.stopPropagation();
        });

        toggle.addEventListener("change", function(event) {
            event.stopPropagation();

            benditoaiSyncAdminToggles(this.checked, this);
            benditoaiPersistAdminUnlimited(this.checked).then(() => {
                window.benditoaiActualizarTokensInstantaneo();
            });
        });
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", benditoaiInitAdminUnlimitedToggles);
} else {
    benditoaiInitAdminUnlimitedToggles();
}
