document.addEventListener("DOMContentLoaded", function(){

const form = document.getElementById("benditoai-form-modelo-ai")

if(!form) return

const loading = document.getElementById("benditoai-modelo-loading")
const placeholder = document.querySelector(".benditoai-placeholder")
const imageWrapper = document.querySelector(".benditoai-image-wrapper")
const image = document.querySelector(".benditoai-generated-image")
const downloadBtn = document.querySelector(".benditoai-download-btn")
const errorBox = form.querySelector(".benditoai-error-message")

form.addEventListener("submit", function(e){

e.preventDefault()

const data = new FormData(form)
data.append("action","benditoai_generar_modelo_ai")

if(errorBox){
    errorBox.style.display = "none"
    errorBox.innerText = ""
}

if(imageWrapper){
    imageWrapper.style.display = "none"
    imageWrapper.classList.remove(
        "benditoai-image-loading",
        "benditoai-image-enter",
        "benditoai-image-loaded"
    )
}

if(image){ image.src = "" }

if(downloadBtn){
    downloadBtn.href = ""
    downloadBtn.style.display = "none"
}

if(placeholder){ placeholder.style.display = "block" }

loading.style.display = "block"
placeholder.style.display = "none"

fetch(benditoai_ajax.ajax_url,{
method:"POST",
body:data
})
.then(res=>res.json())
.then(res=>{

loading.style.display="none"

if(res.success){

const d = res.data
const imageUrl = d.image_url

imageWrapper.style.display = "block"
imageWrapper.classList.add("benditoai-image-enter")

image.src = imageUrl

image.onload = () => {
    imageWrapper.classList.remove("benditoai-image-enter")
    imageWrapper.classList.add("benditoai-image-loaded")
}

downloadBtn.href = imageUrl
downloadBtn.style.display = "inline-block"

if(d.tokens !== undefined){
    benditoaiActualizarTokensInstantaneo(d.tokens)
}

// 🔥 ELIMINAR MENSAJE DE VACÍO
const emptyMsg = document.getElementById("benditoai-empty-message")
if(emptyMsg){
    emptyMsg.remove()
}

// 🔥 INSERTAR EN HISTORIAL
const grid = document.querySelector("#benditoai-historial-mockups")

if(grid){

const nuevoItem = `
<div class="benditoai-historial-item" data-id="${d.id}">

<p class="benditoai-historial-name">${d.nombre_modelo}</p>

<div class="benditoai-img-wrap">
<img src="${d.image_url}" class="benditoai-historial-img"/>
</div>

<a href="${d.image_url}" download class="benditoai-btn benditoai-btn--download">
⬇️ Descargar
</a>

<div class="benditoai-historial-info">

<p><strong>Género:</strong> ${d.genero}</p>
<p><strong>Edad:</strong> ${d.edad}</p>
<p><strong>Estilo:</strong> ${d.estilo}</p>
<p><strong>Creado:</strong> ${d.fecha}</p>

<button 
class="benditoai-delete-modelo-btn" 
data-id="${d.id}">
🗑 Eliminar
</button>

<button 
class="benditoai-edit-modelo-btn" 
data-id="${d.id}"
data-image="${d.image_url}">
✏️ Editar
</button>

<div class="benditoai-edit-box" style="display:none;">
<textarea 
class="benditoai-edit-text"
placeholder="Ej: cámbiale el pantalón por uno jean oscuro..."
></textarea>

<button class="benditoai-save-edit-btn">
Guardar cambios
</button>
</div>

</div>

</div>
`;

grid.insertAdjacentHTML("afterbegin", nuevoItem);

}

}else{

const errorMsg = res?.data?.message || "Error inesperado"

if(errorBox){
    errorBox.innerHTML = "⚠️ " + errorMsg
    errorBox.style.display = "block"
}

}

})
.catch(err=>{

console.error(err)

if(errorBox){
    errorBox.innerHTML = "⚠️ Error inesperado."
    errorBox.style.display = "block"
}

})

})

})