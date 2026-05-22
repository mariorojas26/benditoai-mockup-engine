# Workflows

## Mockup

- Shortcodes: `includes/modules/mockup/shortcodes.php`.
- Endpoint: `wp_ajax_benditoai_generar_mockup` in `ajax-mockup.php`.
- Frontend: `assets/js/mockup/mockup-generator.js`.
- Prompt source: `includes/core/prompts.php` and `includes/core/variables.php`.
- Gemini calls: single image and multi-image service files.

## Campaigns

- Shortcode: `[benditoai_campanas_ai]` in `campanas-ai-shortcode.php`.
- Endpoint: `wp_ajax_benditoai_generar_campana`.
- Frontend: `assets/js/campanas/campanas-ai-script.js`.
- Campaign UI is a 7-screen dark BenditoAI wizard: focus/model route, product route, optional model confirmation, visual presets, marketing copy, output formats, and final export.
- Product route supports two paths: upload product references for the model/no-model campaign, or (when using a saved model) mark the selected model/outfit as the product so the campaign is generated around the clothing already worn by the model.
- Screen 0 can route to `/crea-modelo/`, use an existing model/outfit, or continue without model. Model/outfit selection can arrive from URL/localStorage via model history bridge.
- Campaign model selector mirrors model history data: it queries the user's models, groups saved outfits through `benditoai_modelos_ai_get_saved_outfits_grouped()`, and displays the principal outfit/model thumbnails for selection.
- Endpoint accepts product image references, `product_mode`, palette/tone/style/background/copy settings, selected formats, and optional model/outfit references. It uses `benditoai_call_gemini_campaign()` for campaign-specific aspect ratio/image size and builds different prompts for uploaded-product, model-as-product, and no-model campaigns.
- The campaign generator returns `images[]` and can generate one final image per selected format. Campaign table stores user, model reference, prompt, generated image/status.

## Remove Background

- Shortcode: `[benditoai_remove_bg]`.
- Endpoint: `wp_ajax_benditoai_remove_background`.
- Frontend: `assets/js/remove-bg/remove-bg.js`.
- Production file is `ajax-remove-bg.php`; there is also an older/production-named variant with parentheses.

## Enhance Image

- Shortcode: `[benditoai_enhance_image]`.
- Endpoint: `wp_ajax_benditoai_enhance_image`.
- Frontend: `assets/js/enhance/enhance-image.js`.
- Uses Gemini service after upload handling.

## Trends

- Shortcode: `[benditoai_trending]`.
- Endpoint: `wp_ajax_benditoai_trending_generate`.
- Frontend split: `trending-generator.js` and `trending-ui.js`.

## Auth And Home UX

- Auth dropdown/user menu: `includes/modules/auth`.
- Login/register customization and redirects: `auth-redirect.php`.
- Home widgets and UX cards: `includes/modules/Home`, `includes/modules/ux`, `assets/js/home`.

## Investigation Pattern

- For a UI bug: read shortcode + matching JS + relevant CSS.
- For a generation bug: read endpoint + service + prompt source.
- For a persistence bug: read endpoint + `install.php` table schema + any helper module.
- For token/limit issues: read endpoint + `tokens-plans.md` files + plan helpers.
