(bloquear boton al intenar doble envio de solicitud)
REGLA BENDITOAI - REQUEST MANAGER GLOBAL

Para que el sistema de bloqueo de requests funcione
correctamente en cualquier herramienta de IA, se
deben cumplir estas 2 reglas obligatorias:

1️⃣ El formulario debe tener la clase.:

   .benditoai-ai-form

2️⃣ El botón que ejecuta la IA debe tener la clase:

   .benditoai-ai-button

El archivo global.:
assets/js/core/request-manager.js

detecta automáticamente los formularios con
.benditoai-ai-form y bloquea el botón con
.benditoai-ai-button mientras la IA procesa
la solicitud, evitando múltiples requests
del usuario.

Si alguna de estas clases falta, el bloqueo
NO funcionará.
