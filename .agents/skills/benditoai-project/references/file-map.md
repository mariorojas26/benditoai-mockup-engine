# File Map

Use this to pick the smallest source set before reading code.

## Core

- `bendidoai-mockup-engine.php`: bootstrap, requires, enqueues, localized JS data.
- `includes/core/install.php`: DB schema and upgrades.
- `includes/core/prompts.php`: shared mockup prompt templates.
- `includes/core/variables.php`: prompt variable catalogs and product/model metadata.

## Modelos AI

- `includes/modules/modelos-ai/modelos-ai-shortcode.php`: create-model wizard markup, inline CSS, rasgos options, asset URLs, confirmation modal, shortcode.
- `includes/modules/modelos-ai/modelos-ai-script.js`: create-model wizard behavior, miniwizard, auto/manual navigation, live state, history item insertion.
- `includes/modules/modelos-ai/modelos-ai-ajax.php`: create model endpoint, prompt building, Gemini call, DB insert, principal outfit response.
- `includes/modules/modelos-ai/modelos-ai-historial-shortcode.php`: history UI, style catalog, outfit catalog data attributes.
- `includes/modules/modelos-ai/modelos-ai-edit.php`: edit/preview/confirm existing model.
- `includes/modules/modelos-ai/modelos-ai-outfits.php`: save/delete/rename/edit outfits.
- `includes/modules/modelos-ai/modelos-ai-delete.php`: delete model and related outfits.

## Modelos Frontend Assets

- `assets/js/modelos/edit-modelo.js`: model/outfit edit UI behavior.
- `assets/js/modelos/saved-outfits.js`: saved outfit panel, save/delete/rename/select, events.
- `assets/js/modelos/use-for-campana-bridge.js`: stores selected model/outfit for campaign flow.
- `assets/js/modelos/delete-modelo.js`: delete action.

## Other Tools

- `includes/modules/mockup/ajax-mockup.php`, `includes/modules/mockup/shortcodes.php`, `assets/js/mockup/mockup-generator.js`.
- `includes/modules/campanas-ai/campanas-ai-ajax.php`, `includes/modules/campanas-ai/campanas-ai-shortcode.php`, `assets/js/campanas/campanas-ai-script.js`.
- `includes/modules/remove-bg/ajax-remove-bg.php`, `includes/modules/remove-bg/shortcode-remove-bg.php`, `assets/js/remove-bg/remove-bg.js`.
- `includes/modules/enhance-image/ajax-enhance-image.php`, `includes/modules/enhance-image/shortcode-enhance-image.php`, `assets/js/enhance/enhance-image.js`.
- `includes/modules/tendencias/trending-ajax.php`, `includes/modules/tendencias/trending-shortcode.php`, `assets/js/trending/trending-generator.js`, `assets/js/trending/trending-ui.js`.

## Tokens And Plans

- `includes/modules/tokens/tokens-manager.php`: token count, admin unlimited, decrease/check helpers.
- `includes/modules/tokens/tokens-usage.php`: common token-use helper.
- `includes/modules/tokens/ajax-get-tokens.php`: token refresh endpoint.
- `includes/modules/tokens/tokens-shortcode.php`: token UI.
- `includes/modules/plans/plans-config.php`: plan definitions.
- `includes/modules/plans/plans-functions.php`: plan data and limits.
- `includes/modules/plans/plans-init.php`: default plan on registration.
- `includes/modules/plans/ajax-update-plan.php`: admin/user plan updates.

## Services

- `includes/services/gemini/gemini-api.php`: image-to-image Gemini call.
- `includes/services/gemini/gemini-api-text.php`: text prompt to image response.
- `includes/services/gemini/gemini-api-multi-image.php`: multi-image Gemini call.

## Styling And Assets

- `assets/css/styles.css`: global plugin CSS and many shared components.
- `assets/images/rasgosAvatar/`: rasgos/avatar thumbnails.
- `assets/images/peinados/`: hairstyle JPG assets named exactly like hairstyle labels.
- `assets/images/estilosDeModelo/`: model outfit/style references.
- `assets/vendor/choices/`: Choices select UI library.
