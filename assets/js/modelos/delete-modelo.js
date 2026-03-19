document.addEventListener("click", async function(e){

    if(!e.target.classList.contains("benditoai-delete-modelo-btn")) return;

    const btn = e.target;
    const modeloId = btn.dataset.id;

    if(!confirm("¿Eliminar este modelo?")) return;

    btn.disabled = true;
    btn.innerText = "Eliminando...";

    try{

        let response = await fetch(benditoai_ajax.ajax_url,{
            method:"POST",
            headers:{
                "Content-Type":"application/x-www-form-urlencoded"
            },
            body:"action=benditoai_delete_modelo&modelo_id="+modeloId
        });

        let data = await response.json();

        if(data.success){

            // eliminar del DOM
            btn.closest(".benditoai-historial-item").remove();

        }else{

            alert(data.data.message || "Error al eliminar");

            btn.disabled = false;
            btn.innerText = "🗑 Eliminar";

        }

    }catch(err){

        alert("Error inesperado");

        btn.disabled = false;
        btn.innerText = "🗑 Eliminar";

    }

});