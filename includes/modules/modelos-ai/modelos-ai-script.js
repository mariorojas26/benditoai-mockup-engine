document.addEventListener("DOMContentLoaded", function(){

const form = document.getElementById("benditoai-form-modelo-ai")

if(!form) return

const loading = document.getElementById("benditoai-modelo-loading")
const placeholder = document.querySelector(".benditoai-placeholder")
const imageWrapper = document.querySelector(".benditoai-image-wrapper")
const image = document.querySelector(".benditoai-generated-image")
const downloadBtn = document.querySelector(".benditoai-download-btn")

form.addEventListener("submit", function(e){

e.preventDefault()

const data = new FormData(form)

data.append("action","benditoai_generar_modelo_ai")

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

alert(res.data)

}

})
.catch(err=>{
console.error(err)
})

})

})