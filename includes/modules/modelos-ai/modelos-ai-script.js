/*
REGLA BENDITOAI - RESPUESTAS AJAX

Siempre acceder a errores así:
res.data.message

Nunca usar:
res.data

Porque el backend devuelve objetos.
--------------------------------------
*/

document.addEventListener("DOMContentLoaded", function(){

const form = document.getElementById("benditoai-form-modelo-ai")

if(!form) return

const loading = document.getElementById("benditoai-modelo-loading")
const placeholder = document.querySelector(".benditoai-placeholder")
const imageWrapper = document.querySelector(".benditoai-image-wrapper")
const image = document.querySelector(".benditoai-generated-image")
const downloadBtn = document.querySelector(".benditoai-download-btn")

/* 🔥 CONTENEDOR DE ERROR */
const errorBox = form.querySelector(".benditoai-error-message")

form.addEventListener("submit", function(e){

e.preventDefault()

const data = new FormData(form)
data.append("action","benditoai_generar_modelo_ai")

/* =========================================
🔥 RESET UI LIMPIO (SIN MOSTRAR IMAGEN)
========================================= */

/* limpiar errores */
if(errorBox){
    errorBox.style.display = "none"
    errorBox.innerText = ""
}

/* ocultar TODO */
if(imageWrapper){
    imageWrapper.style.display = "none"
    imageWrapper.classList.remove(
        "benditoai-image-loading",
        "benditoai-image-enter",
        "benditoai-image-loaded"
    )
}

if(image){
    image.src = ""
}

/* ocultar descarga */
if(downloadBtn){
    downloadBtn.href = ""
    downloadBtn.style.display = "none"
}

/* mostrar placeholder */
if(placeholder){
    placeholder.style.display = "block"
}

/* =========================================
🔥 LOADING
========================================= */

loading.style.display = "block"
placeholder.style.display = "none"

/* =========================================
🔥 PETICIÓN
========================================= */

fetch(benditoai_ajax.ajax_url,{
method:"POST",
body:data
})
.then(res=>res.json())
.then(res=>{

loading.style.display="none"

if(res.success){

const imageUrl = res.data.image_url

/* 🔥 mostrar wrapper SOLO cuando ya hay imagen */
imageWrapper.style.display = "block"

/* 🔥 animación entrada elegante */
imageWrapper.classList.add("benditoai-image-enter")

/* cargar imagen */
image.src = imageUrl

/* cuando carga realmente */
image.onload = () => {

    imageWrapper.classList.remove("benditoai-image-enter")
    imageWrapper.classList.add("benditoai-image-loaded")

}

/* 🔥 mostrar descarga SOLO aquí */
downloadBtn.href = imageUrl
downloadBtn.style.display = "inline-block"

/* actualizar tokens */
if(res.data.tokens !== undefined){
    benditoaiActualizarTokensInstantaneo(res.data.tokens)
}

/* nombre archivo */
let nombreModelo = form.querySelector('input[name="nombre_modelo"]').value

nombreModelo = nombreModelo
.toLowerCase()
.replace(/[^a-z0-9]/gi,"-")
.replace(/-+/g,"-")

downloadBtn.download = `benditoAI-${nombreModelo}.png`

}else{

const errorMsg = res?.data?.message || "Ocurrió un error. Intenta nuevamente."

if(errorBox){
    errorBox.innerHTML = "⚠️ " + errorMsg
    errorBox.style.display = "block"
}

}

})
.catch(err=>{

console.error(err)

if(errorBox){
    errorBox.innerHTML = "⚠️ Error inesperado. Intenta nuevamente."
    errorBox.style.display = "block"
}

})

})

})