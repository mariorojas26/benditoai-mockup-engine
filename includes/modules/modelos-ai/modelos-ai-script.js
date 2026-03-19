/*
REGLA BENDITOAI - RESPUESTAS AJAX

Siempre acceder a errores así:
res.data.message

Nunca usar:
res.data

Porque el backend devuelve objetos.

Esto evita errores tipo:
[object Object]

--------------------------------------

REGLA BENDITOAI UI:

Todo formulario de IA debe tener:

.benditoai-error-message

Este contenedor se usa para mostrar errores
sin usar alert().

Siempre va debajo del botón principal.
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

/* limpiar errores */
if(errorBox){
    errorBox.style.display = "none"
    errorBox.innerText = ""
}

/* UI loading */
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

const imageUrl = res.data.image_url

image.src = imageUrl
imageWrapper.style.display = "block"
downloadBtn.href = imageUrl

/* ACTUALIZAR TOKENS EN VIVO */
if(res.data.tokens !== undefined){
    benditoaiActualizarTokensInstantaneo(res.data.tokens)
}

/* nombre del modelo */
let nombreModelo = form.querySelector('input[name="nombre_modelo"]').value

/* limpiar nombre */
nombreModelo = nombreModelo
.toLowerCase()
.replace(/[^a-z0-9]/gi,"-")
.replace(/-+/g,"-")

/* nombre final descarga */
downloadBtn.download = `benditoAI-${nombreModelo}.png`

}else{

/* 🔥 MENSAJE DE ERROR PRO (SIN ALERT) */
const errorMsg = res?.data?.message || "Ocurrió un error. Intenta nuevamente."

if(errorBox){
    errorBox.innerHTML = "⚠️ " + errorMsg
    errorBox.style.display = "block"
}

}

})
.catch(err=>{

console.error(err)

/* error inesperado real */
if(errorBox){
    errorBox.innerHTML = "⚠️ Error inesperado. Intenta nuevamente."
    errorBox.style.display = "block"
}

})

})

})