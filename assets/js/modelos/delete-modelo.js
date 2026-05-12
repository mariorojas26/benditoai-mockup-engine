document.addEventListener("click", async function(e){

    const btn = e.target.closest(".benditoai-delete-modelo-btn");

    if(!btn) return;

    const modeloId = btn.dataset.id;

    if(!confirm("¿Eliminar este modelo?")) return;

    btn.disabled = true;

    const img = btn.querySelector("img");
    if(img) img.style.opacity = "0.5";

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

            const card = btn.closest(".benditoai-historial-item");

            // animación suave 🔥
            card.style.opacity = "0";
            card.style.transform = "scale(0.95)";

            setTimeout(()=>{
                card.remove();
                const grid = document.getElementById("benditoai-historial-mockups");
                if (grid && grid.querySelectorAll(".benditoai-historial-item").length === 0) {
                    const wrapper = grid.closest(".benditoai-wrapper-historia-modelos");
                    if (wrapper && !wrapper.querySelector("#benditoai-empty-message")) {
                        const empty = document.createElement("p");
                        empty.className = "benditoai-message";
                        empty.id = "benditoai-empty-message";
                        empty.textContent = "No has generado ningun modelo todavia.";
                        grid.insertAdjacentElement("beforebegin", empty);
                    }
                }
                if (grid) {
                    grid.dispatchEvent(new CustomEvent("benditoai:historial-updated"));
                }
                document.dispatchEvent(new CustomEvent("benditoai:historial-updated"));
                if (typeof window.benditoaiRefreshHistorialPagination === "function") {
                    window.benditoaiRefreshHistorialPagination();
                }
            },200);

        }else{

            alert(data.data.message || "Error al eliminar");

            btn.disabled = false;
            if(img) img.style.opacity = "1";

        }

    }catch(err){

        alert("Error inesperado");

        btn.disabled = false;
        if(img) img.style.opacity = "1";

    }

});
