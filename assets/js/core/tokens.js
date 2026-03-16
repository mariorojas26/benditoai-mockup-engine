/* --------------------------------------
BENDITOAI TOKEN MANAGER

/* =========================================================
BENDITOAI TOKEN SYSTEM – REGLAS DE INTEGRACIÓN
==============================================

Este plugin usa un sistema centralizado de tokens para todas
las herramientas de IA (mockup, enhance, remove-bg, trending, etc).

Para que el contador de tokens se actualice correctamente
en tiempo real, se deben cumplir estas reglas:

---

## 1️⃣ CLASE DEL CONTADOR DE TOKENS

El elemento HTML que muestra los tokens DEBE tener la clase:

.benditoai-user-tokens

Ejemplo correcto:

<span class="benditoai-user-tokens">30</span>

Esto permite que el archivo:

assets/js/core/tokens.js

pueda actualizar el contador automáticamente.

---

## 2️⃣ DESCONTAR TOKENS EN EL BACKEND

Todas las herramientas de IA deben descontar tokens usando:

benditoai_use_token(1);

Ejemplo:

benditoai_use_token(1);

Esto garantiza que el sistema de tokens sea consistente
en todo el plugin.

---

## 3️⃣ DEVOLVER TOKENS ACTUALIZADOS EN AJAX

Después de descontar tokens, el endpoint AJAX debe devolver
los tokens actualizados:
$tokens = benditoai_get_user_tokens($user_id);

wp_send_json_success([
'resultado' => $data,
'tokens' => $tokens
]);

---

## 4️⃣ ACTUALIZAR TOKENS EN EL FRONTEND

Cuando el JS recibe la respuesta AJAX, debe ejecutar:

benditoaiActualizarTokensInstantaneo(data.data.tokens);

Ejemplo:

if(typeof benditoaiActualizarTokensInstantaneo === "function"){
benditoaiActualizarTokensInstantaneo(data.data.tokens);
}

Esto actualizará todos los contadores visibles en la página
sin necesidad de recargar.

---

## 5️⃣ ARCHIVO RESPONSABLE DEL CONTADOR

assets/js/core/tokens.js

Este archivo contiene el manager global que refresca los
tokens en todos los elementos con la clase:

.benditoai-user-tokens

---

## RESUMEN

HTML: <span class="benditoai-user-tokens">30</span>

PHP:
benditoai_use_token(1);

AJAX RESPONSE:
'tokens' => $tokens

JS:
benditoaiActualizarTokensInstantaneo(data.data.tokens);

=========================================================
BENDITOAI MOCKUP ENGINE
Token system by BenditoTrazo
============================

*/

window.benditoaiTokensManager = {

    actualizar(tokens){

        /* si el backend envía tokens */
        if(tokens !== undefined){

            const tokenElements = document.querySelectorAll(".benditoai-user-tokens");

            tokenElements.forEach(el=>{
                el.textContent = tokens;
            });

            return;
        }

        /* fallback si no envía tokens */
        this.refrescar();

    },

    refrescar(){

        fetch(benditoai_ajax.ajax_url,{
            method:"POST",
            headers:{
                "Content-Type":"application/x-www-form-urlencoded"
            },
            body:"action=benditoai_get_tokens"
        })
        .then(res=>res.json())
        .then(data=>{

            if(!data.success) return;

            const tokenElements = document.querySelectorAll(".benditoai-user-tokens");

            tokenElements.forEach(el=>{
                el.textContent = data.data.tokens;
            });

        });

    }

};


/* --------------------------------------
FUNCION GLOBAL COMPATIBLE
-------------------------------------- */

window.benditoaiActualizarTokensInstantaneo = function(tokens){

    window.benditoaiTokensManager.actualizar(tokens);

};