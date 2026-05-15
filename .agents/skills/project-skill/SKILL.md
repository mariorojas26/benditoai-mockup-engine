Nombre: Skill resumen — BenditoAI Mockup Engine
Descripción: Resumen compacto y accionable de todo el proyecto para que un LLM pueda leerlo rápidamente y ahorrar tokens.

Alcance
- Contiene arquitectura, carpetas y archivos clave, flujos de datos y servicios externos.
- Usar como primera fuente: cuando hagas consultas al LLM, primero alimenta esta skill para que no tenga que leer todo el repo.

Resumen del proyecto
- Plugin WordPress llamado "bendidoai-mockup-engine" que genera modelos/mockups mediante IA y herramientas auxiliares.
- Frontend JS en `assets/js/` (módulos: mockup, modelos, enhance, remove-bg, trending, auth, etc.).
- Includes PHP en `includes/` con módulos separados por funcionalidad (modelos-ai, enhance-image, remove-bg, campanas-ai, tokens, plans, ux, etc.).
- Assets está en `assets/` (images, css, js, vendor).
- Servicio AI: carpeta `services/gemini/` (integración externa para generación/modelos).

Estructura y archivos clave
- [bendidoai-mockup-engine.php] Archivo principal del plugin (bootstrap).
- [assets/js/] Código cliente: `mockup-generator.js`, `modelos-ai-script.js`, `request-manager.js`, `tokens.js`.
- [assets/images/rasgosAvatar/] Imágenes usadas como thumbs para `baiw-choice-tile`.
- [includes/core/variables.php] y [includes/core/prompts.php] Variables y prompts reutilizables.
- [includes/modules/modelos-ai/modelos-ai-shortcode.php] Render del wizard de creación de modelos (contenedor y helpers para thumbnails).
- [includes/modules/remove-bg/python/] Integraciones con scripts Python para eliminación de fondo.
- [includes/modules/tokens/tokens-manager.php] Lógica de gestión de tokens de usuario.
- [includes/modules/plans/] Archivos que definen planes y límites de uso.

Entradas y puntos de ejecución
- Shortcodes: módulos en `includes/modules/*` exponen shortcodes que renderizan UIs (ej.: `modelos-ai-shortcode.php`).
- AJAX endpoints: `ajax-*.php` en módulos (ej.: `ajax-mockup.php`, `ajax-remove-bg.php`, `ajax-get-tokens.php`).
- Hooks/filters: revisar `bendidoai-mockup-engine.php` y `includes/core/install.php` para instalaciones y hooks.

Flujo de datos (alto nivel)
- Usuario interactúa en frontend JS → acciones POST/AJAX a endpoints PHP → PHP procesa (tokens, permisos) → llama a servicios externos (Gemini, Python, APIs) → devuelve URL de imagen / estado.
- Valores importantes pasados vía `data-` attributes (ej.: `data-rasgos-avatar-base-url`) para construir URLs de thumbs.

Servicios externos y dependencias
- Gemini (carpeta `services/gemini/`): integración para generación de imágenes/texto.
- Python script para remove-bg (si está en producción, revisar `includes/modules/remove-bg/python`).
- Vendor: `choices.js` para selects estilizados (assets/vendor/choices).

Cómo usar esta skill con un LLM (recomendación)
1. Primer prompt: "Resume este proyecto usando la skill interna. Si necesito detalles de un archivo específico, te lo indicaré." Adjunta el contenido de este SKILL.md.
2. Para preguntas de alto nivel: pasar solo SKILL.md y la pregunta.
3. Para cambios de código: pasar SKILL.md + uno o dos archivos relevantes (no todo el repo).

Prompts recomendados para ahorrar tokens
- "Lee la skill del proyecto y resume los puntos relevantes para implementar X." 
- "Usando la skill, busca dónde se valida el token de usuario para endpoints AJAX." 
- "Con base en la skill, lista los archivos que afectan la generación de mockups." 

Mantenimiento
- Actualiza esta skill cuando cambies arquitectura, rutas de assets o nombres de endpoints.
- Para sincronizar automáticamente, generar un script que actualice la lista de archivos clave (opcional).

Notas internas y conexiones clave
- `rasgos_avatar_base_url` se construye con `BENDIDOAI_PLUGIN_URL` + `assets/images/rasgosAvatar/`.
- Thumbs usan la variable CSS `--baiw-choice-image` (ver `modelos-ai-shortcode.php` para render del tile).
- Muchos módulos usan AJAX con nombres `ajax-*` y shortcodes en `shortcodes.php`.

Siguientes pasos sugeridos
- Añadir un índice JSON compacto (files.json) con rutas y breve descripción para búsquedas rápidas.
- (Opcional) Crear un script que convierta este SKILL.md en un prompt compacto preformateado para LLM.

Fin de SKILL

Artefactos adicionales
- `files.json`: índice compacto con rutas y descripciones para lectura rápida por la IA. Ubicado en `.agents/skills/project-skill/files.json`.
- `SKILL_PROMPT.txt`: prompt compacto listo para inyectar al LLM. Ubicado en `.agents/skills/project-skill/SKILL_PROMPT.txt`.

Cómo usar rápido
1. Cargar `SKILL.md` y `files.json` al LLM como contexto.
2. Enviar `SKILL_PROMPT.txt` como primer mensaje instructivo para ahorrar tokens.
3. Si el LLM necesita código, pedir solo las rutas listadas en `files.json`.

Fin de SKILL
