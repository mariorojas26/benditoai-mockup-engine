# Modelos AI

## Create Model Wizard

- Shortcode: `[benditoai_modelos_ai]`.
- Markup/CSS: `includes/modules/modelos-ai/modelos-ai-shortcode.php`.
- Behavior: `includes/modules/modelos-ai/modelos-ai-script.js`.
- Endpoint: `wp_ajax_benditoai_generar_modelo_ai` in `modelos-ai-ajax.php`.

## Main Flow

1. Step 1 selects creation mode: reference photo or rasgos/from scratch.
2. Step 2 contains reference fields or rasgos miniwizard.
3. Rasgos miniwizard has internal steps: base, origin, face.
4. Step 3 adds final rasgos/details and public/private toggle.
5. Submit posts `FormData` with action `benditoai_generar_modelo_ai`.
6. PHP builds prompt, calls Gemini, stores image in uploads, inserts DB row, returns model data and optional `principal_outfit`.

## Rasgos Miniwizard

- Field names tracked in JS: `genero`, `cuerpo`, `etnia`, `peinado`, `color_ojos`, `color_pelo`, `color_cejas`, `nacionalidad`.
- Auto-advance depends on current mini step being complete and touched.
- If user goes back and confirms, auto-advance becomes manual and `Siguiente` is shown.
- Choice tiles update hidden selects through `data-choice-target` and `data-choice-value`.
- Ranges use `--range-progress` for visual track fill.

## Hair/Peinado System

- Hair image base URL: `assets/images/peinados/`.
- Male/female modes swap the entire peinado set in-place.
- Image files are expected as JPGs named by label, for example `Taper Fade.jpg`, `Lob (Long Bob).jpg`.
- Hidden select remains `name="peinado"` so prompt/DB integration stays stable.

## Generated Data

`modelos-ai-ajax.php` reads form fields, composes prompt data, calls either reference or rasgos prompt builder, normalizes output, inserts into `benditoai_modelos_ai`, and returns:

- `id`, `image_url`, `tokens`, `nombre_modelo`.
- trait fields such as `genero`, `edad`, `cuerpo`, `nacionalidad`, `color_ojos`, `peinado`, `color_pelo`, `color_cejas`.
- `perfil_publico`, `descripcion_modelo`, and `principal_outfit` when available.

## History, Outfits, Campaign Handoff

- History shortcode: `modelos-ai-historial-shortcode.php`.
- Saved outfits backend: `modelos-ai-outfits.php`.
- Saved outfits frontend: `assets/js/modelos/saved-outfits.js`.
- Edit model/outfit frontend: `assets/js/modelos/edit-modelo.js`.
- Campaign bridge: `assets/js/modelos/use-for-campana-bridge.js`.
- Bridge stores `benditoai_campaign_model_ref` and `benditoai_selected_model` in `localStorage`, including `modelo_id`, `image_url`, `outfit_id`, `outfit_tag`, and source.

## Common Edit Targets

- UI layout or wizard fields: shortcode first, script second.
- Auto-advance/back/validation: `modelos-ai-script.js`.
- Prompt/field persistence: `modelos-ai-ajax.php`.
- Model history card/outfit controls: history shortcode plus `saved-outfits.js`.
- Edit preview/confirm: `modelos-ai-edit.php`, `modelos-ai-outfits.php`, and `edit-modelo.js`.
