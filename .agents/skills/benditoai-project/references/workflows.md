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
- Model/outfit selection can arrive from localStorage via model history bridge.
- Campaign table stores user, model reference, prompt, generated image/status.

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
