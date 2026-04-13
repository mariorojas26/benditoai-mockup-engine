document.addEventListener("click", async function(e){

    if(e.target.classList.contains("benditoai-edit-modelo-btn")){

        const item = e.target.closest(".benditoai-historial-item");
        const box = item.querySelector(".benditoai-edit-box");

        box.style.display = box.style.display === "none" ? "block" : "none";
    }

    if(e.target.classList.contains("benditoai-save-edit-btn")){

        const item = e.target.closest(".benditoai-historial-item");

        const modeloId = item.querySelector(".benditoai-edit-modelo-btn").dataset.id;
        const imageUrl = item.querySelector(".benditoai-edit-modelo-btn").dataset.image;
        const texto = item.querySelector(".benditoai-edit-text").value;

        if(!texto){
            alert("Escribe qué deseas cambiar");
            return;
        }

        if(!confirm("⚠️ El modelo será reemplazado. ¿Continuar?")) return;

        const imgWrap = item.querySelector(".benditoai-img-wrap");
        const img = item.querySelector("img");

        if(imgWrap){
            imgWrap.classList.add("benditoai-image-loading");
        }

        e.target.disabled = true;
        e.target.innerText = "Editando...";

        try{

            let response = await fetch(benditoai_ajax.ajax_url,{
                method:"POST",
                headers:{
                    "Content-Type":"application/x-www-form-urlencoded"
                },
                body:`action=benditoai_edit_modelo&modelo_id=${modeloId}&image_url=${encodeURIComponent(imageUrl)}&texto=${encodeURIComponent(texto)}`
            });

            let data = await response.json();

            if(data.success){

                const newUrl = data.data.image_url + "?t=" + new Date().getTime();

                if(imgWrap){
                    imgWrap.classList.remove("benditoai-image-loading");
                    imgWrap.classList.add("benditoai-image-enter");
                }

                // 🔥 cambiar imagen
                img.src = newUrl;

                img.onload = () => {

                    if(imgWrap){
                        imgWrap.classList.remove("benditoai-image-enter");
                        imgWrap.classList.add("benditoai-image-loaded");
                    }

                    setTimeout(()=>{
                        if(imgWrap){
                            imgWrap.classList.remove("benditoai-image-loaded");
                        }
                    }, 400);
                };

                // 🔥 actualizar dataset
                const editBtn = item.querySelector(".benditoai-edit-modelo-btn");
                if(editBtn){
                    editBtn.dataset.image = data.data.image_url;
                }

                // 🔥🔥🔥 FIX CLAVE: actualizar botón de descarga
                const downloadBtn = item.querySelector(".benditoai-btn--download");
                if(downloadBtn){
                   downloadBtn.href = data.data.image_url + "?t=" + new Date().getTime();
                }

                // reset UI
                item.querySelector(".benditoai-edit-box").style.display = "none";
                e.target.innerText = "Guardar cambios";
                e.target.disabled = false;

            }else{

                if(imgWrap){
                    imgWrap.classList.remove("benditoai-image-loading");
                }

                alert(data?.data?.message || "Error al editar");

                e.target.innerText = "Guardar cambios";
                e.target.disabled = false;
            }

        }catch(err){

            if(imgWrap){
                imgWrap.classList.remove("benditoai-image-loading");
            }

            alert("Error inesperado");

            e.target.innerText = "Guardar cambios";
            e.target.disabled = false;
        }

    }

});

// DROPDOWN INFO
document.addEventListener("click", function(e){

const btn = e.target.closest(".benditoai-toggle-info")
if(!btn) return

const box = btn.nextElementSibling

if(!box) return

if(box.style.display === "none" || box.style.display === ""){
    box.style.display = "block"
    btn.innerHTML = "Ocultar detalles ⌃"
}else{
    box.style.display = "none"
    btn.innerHTML = "Ver detalles ⌄"
}

})
// DROPDOWN INFO