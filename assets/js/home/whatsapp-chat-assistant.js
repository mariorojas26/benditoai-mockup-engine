document.addEventListener("DOMContentLoaded", () => {
    const root = document.querySelector("[data-benditoai-wa-chat]");
    if (!root || typeof benditoai_ajax === "undefined") return;

    const fab = root.querySelector("[data-wa-fab]");
    const panel = root.querySelector("[data-wa-panel]");
    const close = root.querySelector("[data-wa-close]");
    const form = root.querySelector("[data-wa-form]");
    const input = root.querySelector("[data-wa-input]");
    const messages = root.querySelector("[data-wa-messages]");
    if (!fab || !panel || !close || !form || !input || !messages) return;

    const addMessage = (text, who = "bot") => {
        const el = document.createElement("div");
        el.className = `benditoai-wa-chat__msg is-${who}`;
        el.textContent = text;
        messages.appendChild(el);
        messages.scrollTop = messages.scrollHeight;
    };

    const addPlansBlock = (ui) => {
        if (!ui || ui.type !== "plans" || !Array.isArray(ui.items) || !ui.items.length) return;
        const card = document.createElement("div");
        card.className = "benditoai-wa-chat__msg is-bot is-structured";

        const title = document.createElement("strong");
        title.className = "benditoai-wa-chat__plans-title";
        title.textContent = ui.title || "Planes";
        card.appendChild(title);

        const list = document.createElement("ul");
        list.className = "benditoai-wa-chat__plans-list";
        ui.items.forEach((item) => {
            const li = document.createElement("li");
            li.className = "benditoai-wa-chat__plans-item";
            const badge = item.badge ? `<em>${item.badge}</em>` : "";
            li.innerHTML = [
                `<div class="benditoai-wa-chat__plan-head">`,
                `<span>${item.name || "Plan"}</span>`,
                badge,
                `</div>`,
                `<div class="benditoai-wa-chat__plan-meta">`,
                `<small>${Number(item.tokens || 0)} tokens</small>`,
                `<small>${Number(item.max_modelos || 0)} modelos</small>`,
                `<small>${Number(item.max_outfits || 0)} outfits por modelo</small>`,
                `</div>`,
            ].join("");
            list.appendChild(li);
        });
        card.appendChild(list);

        if (ui.cta_url) {
            const cta = document.createElement("a");
            cta.className = "benditoai-wa-chat__plans-cta";
            cta.href = ui.cta_url;
            cta.textContent = ui.cta_label || "Ver planes";
            card.appendChild(cta);
        }

        messages.appendChild(card);
        messages.scrollTop = messages.scrollHeight;
    };

    const openPanel = () => {
        panel.hidden = false;
        root.classList.add("is-open");
        setTimeout(() => input.focus(), 40);
    };

    const closePanel = () => {
        root.classList.remove("is-open");
        setTimeout(() => { panel.hidden = true; }, 140);
    };

    fab.addEventListener("click", openPanel);
    close.addEventListener("click", closePanel);

    document.addEventListener("mousemove", (event) => {
        const r = fab.getBoundingClientRect();
        const cx = r.left + (r.width / 2);
        const cy = r.top + (r.height / 2);
        const dx = event.clientX - cx;
        const dy = event.clientY - cy;
        const dist = Math.sqrt((dx * dx) + (dy * dy));
        if (dist <= 130) root.classList.add("is-near");
        else root.classList.remove("is-near");
    });

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        const text = String(input.value || "").trim();
        if (!text) return;

        addMessage(text, "user");
        input.value = "";
        input.disabled = true;
        const submit = form.querySelector("button[type='submit']");
        if (submit) submit.disabled = true;

        try {
            const body = new URLSearchParams();
            body.set("action", "benditoai_home_chat_assistant");
            body.set("message", text);

            const response = await fetch(benditoai_ajax.ajax_url, {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body.toString(),
            });
            const data = await response.json();
            addMessage(data?.data?.reply || "No pude responder en este momento.", "bot");
            if (data?.data?.ui?.type === "plans") {
                addPlansBlock(data.data.ui);
            }
        } catch (error) {
            addMessage("Tuve un error temporal. Intentemos de nuevo.", "bot");
        } finally {
            input.disabled = false;
            if (submit) submit.disabled = false;
            input.focus();
        }
    });
});
