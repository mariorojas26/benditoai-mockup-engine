document.addEventListener("DOMContentLoaded", function () {

const mockupForm = document.getElementById("benditoai-form");
if (!mockupForm) return;

const resultado = document.getElementById("resultado-mockup");

mockupForm.addEventListener("submit", function (e) {

e.preventDefault();

let formData = new FormData(mockupForm);
formData.append("action", "benditoai_generar_mockup");

resultado.innerHTML = `
<div class="benditoai-loading">
Generando tu mockup creativo... ⏳
</div>
`;

fetch(benditoai_ajax.ajax_url,{
method:"POST",
body:formData
})
.then(response=>response.json())
.then(data=>{

if(data.success){

const imageUrl = data.data.image_url;

resultado.innerHTML = `
<div class="benditoai-image-wrapper">

<img src="${imageUrl}" 
class="benditoai-generated-image"
alt="Mockup generado por BenditoAI"/>

<a href="${imageUrl}"
download="Mockup-BenditoAI.png"
class="benditoai-download-btn"
title="Descargar imagen">

<img class="benditoai-download-icon"
src="${benditoai_ajax.plugin_url}assets/images/download-icon.png"
alt="Descargar"/>

</a>

</div>
`;

if(typeof benditoaiActualizarTokensInstantaneo === "function"){
benditoaiActualizarTokensInstantaneo();
}

}else{

resultado.innerHTML = `
<div class="benditoai-error">
Ocurrió un error al generar el mockup.
</div>`;

}

})
.catch(()=>{

resultado.innerHTML = `
<div class="benditoai-error">
Error inesperado. Intenta nuevamente.
</div>`;

});

});

});

// Lógica para mostrar/ocultar sección de modelo en el formulario

document.addEventListener("DOMContentLoaded", function () {

    const selectModelo = document.getElementById("modelo");
    const modeloWrap = document.getElementById("modeloWrap");

    function toggleModelo() {

        if (selectModelo.value === "no") {
            modeloWrap.classList.add("benditoai-modelo-wrap--hidden");
        } else {
            modeloWrap.classList.remove("benditoai-modelo-wrap--hidden");
        }
    }

    // Ejecutar al cargar
    toggleModelo();

    // Ejecutar al cambiar
    selectModelo.addEventListener("change", toggleModelo);

});

//cards de avatars en genrador de mockups
document.addEventListener("DOMContentLoaded", function () {

    const cards = document.querySelectorAll(".benditoai-modelo-card");
    const input = document.getElementById("modeloAvatarInput");

    cards.forEach(card => {

        card.addEventListener("click", () => {

            cards.forEach(c => c.classList.remove("active"));

            card.classList.add("active");

            input.value = card.getAttribute("data-id");

        });

    });

});