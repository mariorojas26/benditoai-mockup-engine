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


// 🔥 NUEVO: INSERTAR EN HISTORIAL SIN RECARGAR
const grid = document.querySelector(".benditoai-historial-grid");

if(grid){

const nuevoItem = `
<div class="benditoai-historial-item nuevo">

<img src="${data.data.image_url}" 
class="benditoai-historial-img">

<a href="${data.data.image_url}" 
download="mockup.png"
class="benditoai-btn benditoai-btn--download">
⬇️ Descargar
</a>

<div class="benditoai-historial-info">
<p><strong>Producto:</strong> ${data.data.producto}</p>
<p><strong>Color:</strong> ${data.data.color}</p>
<p><strong>Entorno:</strong> ${data.data.entorno}</p>
<p><strong>Fecha:</strong> ${data.data.fecha}</p>
</div>

</div>
`;

grid.insertAdjacentHTML("afterbegin", nuevoItem);
}


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

    toggleModelo();

    selectModelo.addEventListener("change", toggleModelo);

});


// cards de avatars en generador de mockups

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