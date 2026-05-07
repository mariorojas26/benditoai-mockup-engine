document.addEventListener("DOMContentLoaded", function(){

/* =========================
   🧭 WIZARD
========================= */

let step = 1
const steps = document.querySelectorAll(".benditoai-step")

function showStep(n){
    steps.forEach(s => s.classList.remove("active"))
    const target = document.querySelector(`.benditoai-step[data-step="${n}"]`)
    if(target) target.classList.add("active")
}

/* NEXT */
document.addEventListener("click", e => {
    if(e.target.classList.contains("benditoai-next")){
        step++
        showStep(step)
    }
})

/* PREV */
document.addEventListener("click", e => {
    if(e.target.classList.contains("benditoai-prev")){
        step--
        showStep(step)
    }
})

/* =========================
   🖼️ PRODUCTO (BASE64)
========================= */

let productBase64 = null

const productInput = document.getElementById("benditoai-product-image")

if(productInput){
    productInput.addEventListener("change", function(){
        const file = this.files[0]
        if(!file) return
        const reader = new FileReader()
        reader.onload = e => {
            productBase64 = e.target.result
        }
        reader.readAsDataURL(file)
    })
}

/* =========================
   👤 USAR MODELO
========================= */

let useModel = "0"
let modelUrl = null

document.querySelectorAll("input[name='use_model']").forEach(r => {
    r.addEventListener("change", () => {

        useModel = r.value

        const container = document.getElementById("benditoai-modelos-container")
        if(container){
            container.style.display = (useModel === "1") ? "block" : "none"
        }

        if(useModel === "0"){
            modelUrl = null
            document.getElementById("model_url").value = ""
            document.querySelectorAll(".benditoai-modelo-card")
                .forEach(c => c.classList.remove("active"))
            const label = document.getElementById("benditoai-modelo-seleccionado")
            if(label) label.style.display = "none"
        }

    })
})

function preselectModelFromContext(){

    const radioYes = document.querySelector("input[name='use_model'][value='1']")
    const container = document.getElementById("benditoai-modelos-container")
    const hiddenInput = document.getElementById("model_url")
    const label = document.getElementById("benditoai-modelo-seleccionado")

    if(!radioYes || !container || !hiddenInput) return

    let selectedModelUrl = ""

    try{
        const params = new URLSearchParams(window.location.search)
        selectedModelUrl = params.get("model_url") || ""
    }catch(err){
        selectedModelUrl = ""
    }

    if(!selectedModelUrl){
        try{
            const cached = JSON.parse(localStorage.getItem("benditoai_selected_model") || "null")
            if(cached && cached.image_url){
                selectedModelUrl = cached.image_url
            }
        }catch(err){
            selectedModelUrl = ""
        }
    }

    if(!selectedModelUrl) return

    radioYes.checked = true
    useModel = "1"
    container.style.display = "block"

    document.querySelectorAll(".benditoai-modelo-card")
        .forEach(c => c.classList.remove("active"))

    const card = Array.from(document.querySelectorAll(".benditoai-modelo-card"))
        .find(c => c.dataset.url === selectedModelUrl)

    modelUrl = selectedModelUrl
    hiddenInput.value = selectedModelUrl

    if(card){
        card.classList.add("active")
        if(label){
            label.style.display = "block"
            label.querySelector("strong").innerText = card.dataset.nombre || "Modelo seleccionado"
        }
        return
    }

    if(label){
        label.style.display = "block"
        label.querySelector("strong").innerText = "Modelo seleccionado desde generacion"
    }
}

/* =========================
   🎯 SELECCIONAR MODELO
========================= */

document.addEventListener("click", function(e){

    const card = e.target.closest(".benditoai-modelo-card")
    if(!card) return

    const url = card.dataset.url
    const nombre = card.dataset.nombre

    document.querySelectorAll(".benditoai-modelo-card")
        .forEach(c => c.classList.remove("active"))

    card.classList.add("active")

    modelUrl = url
    document.getElementById("model_url").value = url

    const box = document.getElementById("benditoai-modelo-seleccionado")
    if(box){
        box.style.display = "block"
        box.querySelector("strong").innerText = nombre
    }

})

/* =========================
   🧠 STORAGE
========================= */

let lastPayload = null

/* =========================
   🚀 GENERAR
========================= */

async function generarCampana(payload){

    // 🔥 ir al paso 6 (resultado) — esto oculta el paso 5 automáticamente
    step = 6
    showStep(6)

    const resultImg = document.getElementById("benditoai-result-img")
    const loading = document.getElementById("benditoai-loading")
    const errorBox = document.getElementById("benditoai-error")

    loading.style.display = "block"
    resultImg.style.display = "none"
    errorBox.style.display = "none"

    try{

        const data = new FormData()
        data.append("action", "benditoai_generar_campana")

        Object.keys(payload).forEach(key => {
            data.append(key, payload[key])
        })

        const response = await fetch(benditoai_ajax.ajax_url, {
            method: "POST",
            body: data
        })

        const res = await response.json()

        loading.style.display = "none"

        if(res.success){
            const url = res.data.image_url + "?t=" + new Date().getTime()
            resultImg.src = url
            resultImg.style.display = "block"
        } else {
            errorBox.style.display = "block"
            errorBox.innerText = res.data || "Error generando campaña"
        }

    } catch(err){
        console.error(err)
        loading.style.display = "none"
        errorBox.style.display = "block"
        errorBox.innerText = "Error inesperado, intenta de nuevo"
    }

}

/* =========================
   📤 SUBMIT
========================= */

const form = document.getElementById("benditoai-form-campana-ai")

if(form){

    form.addEventListener("submit", async function(e){

        e.preventDefault()

        // 🔥 leer valores ANTES de cambiar de paso
        const producto = form.querySelector("textarea[name='producto']").value.trim()
        const estilo = form.querySelector("select[name='estilo']").value
        const tono = form.querySelector("select[name='tono']").value

        if(!producto){
            alert("Escribe el nombre del producto")
            return
        }

        if(!productBase64){
            alert("Sube una imagen del producto")
            return
        }

        if(useModel === "1" && !modelUrl){
            alert("Selecciona un modelo")
            return
        }

        lastPayload = {
            producto,
            product_image: productBase64,
            use_model: useModel,
            model_url: modelUrl || "",
            estilo,
            tono
        }

        await generarCampana(lastPayload)

    })

}

/* =========================
   🔁 RECREAR
========================= */

document.getElementById("benditoai-recrear")?.addEventListener("click", () => {

    if(!lastPayload){
        alert("No hay campaña previa")
        return
    }

    generarCampana(lastPayload)

})

/* =========================
   ⚙️ RESET
========================= */

document.getElementById("benditoai-reset")?.addEventListener("click", () => {

    step = 1
    showStep(1)

    lastPayload = null
    modelUrl = null
    productBase64 = null

    document.getElementById("model_url").value = ""

    document.querySelectorAll(".benditoai-modelo-card")
        .forEach(c => c.classList.remove("active"))

    const label = document.getElementById("benditoai-modelo-seleccionado")
    if(label) label.style.display = "none"

    const radioNo = document.querySelector("input[name='use_model'][value='0']")
    if(radioNo) radioNo.checked = true

    const container = document.getElementById("benditoai-modelos-container")
    if(container) container.style.display = "none"

    useModel = "0"

})

preselectModelFromContext()

})
