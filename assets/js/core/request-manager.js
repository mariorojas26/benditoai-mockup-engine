/* =====================================================

BENDITOAI GLOBAL REQUEST MANAGER

Bloquea automáticamente botones de IA
para evitar múltiples solicitudes.

REGLA GLOBAL:

FORMULARIO IA
.benditoai-ai-form

BOTON IA
.benditoai-ai-button

Cualquier formulario con esa clase
se bloqueará automáticamente al enviar.

===================================================== */

window.benditoaiRequestManager = {

    bloquear(form){

        const btn = form.querySelector(".benditoai-ai-button");

        if(!btn) return;

        btn.disabled = true;
        btn.classList.add("benditoai-btn--loading");

        btn.dataset.originalText = btn.innerText;
        btn.innerText = "Procesando...";

    },

    desbloquear(form){

        const btn = form.querySelector(".benditoai-ai-button");

        if(!btn) return;

        btn.disabled = false;
        btn.classList.remove("benditoai-btn--loading");

        if(btn.dataset.originalText){
            btn.innerText = btn.dataset.originalText;
        }

    }

};


/* =====================================================

AUTO BLOQUEO GLOBAL

Intercepta todos los formularios IA

===================================================== */

document.addEventListener("submit", function(e){

    const form = e.target;

    if(!form.classList.contains("benditoai-ai-form")) return;

    if(window.benditoaiRequestManager){
        benditoaiRequestManager.bloquear(form);
    }

});


/* =====================================================

AUTO DESBLOQUEO GLOBAL

Cuando termina cualquier fetch

===================================================== */

(function(){

const originalFetch = window.fetch;

window.fetch = async function(){

    const response = await originalFetch.apply(this, arguments);

    document.querySelectorAll(".benditoai-ai-form").forEach(form=>{
        benditoaiRequestManager.desbloquear(form);
    });

    return response;

};

})();