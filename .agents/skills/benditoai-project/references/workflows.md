# Workflows

## Product Workflow Philosophy

BenditoAI workflows should feel like guided production pipelines, not isolated demos. A user may create a model, save outfits, send that model to a campaign, generate commercial visuals, edit or download results, and continue using the same account/tokens.

When changing any workflow, trace the full loop:

`input UI -> frontend state -> AJAX payload -> PHP validation -> AI/service call -> persistence -> JSON response -> rendered result/history -> token update`.

If only one side of that loop changes, bugs usually appear later in history cards, saved outfits, campaign handoff, or token display.

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
- Desktop account/header shortcode: `[benditoai_desktop_user]` in `includes/modules/auth/auth-dropdown.php`; it renders the account button, dropdown, plan row, token row, and logout.
- Login/register customization and redirects: `auth-redirect.php`.
- Home widgets and UX cards: `includes/modules/Home`, `includes/modules/ux`, `assets/js/home`.
- GSAP home components should use scoped CSS/JS and not affect regular page scroll outside their section.

## Header Visual Workflow

- WordPress/Astra menu content is configured in WP admin. For visual-only requests, do not change nav labels/order in code.
- For header glass/sticky styling, inspect both generated Custom CSS/JS files in `wp-content/uploads/custom-css-js/` and repo fallback rules in `assets/css/styles.css`.
- For account dropdown markup or row order, edit `includes/modules/auth/auth-dropdown.php`.
- For token count placement in the header, keep standalone desktop token shortcodes hidden and render tokens inside `[benditoai_desktop_user]`.
- Verify at least the home page and one non-home page after header changes: home should have fixed overlay behavior; non-home pages should keep header space and not sit underneath it.

## Cross-Flow Connections

- Model history can hand selected model/outfit data to campaign creation through localStorage.
- Token-consuming tools should update shared token UI immediately after success.
- Generated images often need download affordances and stable URLs for later reuse.
- Plan limits can affect model count, outfit count, and feature availability.
- Auth redirects and login state can change whether a shortcode renders the full tool or a prompt to log in.

## Investigation Pattern

- For a UI bug: read shortcode + matching JS + relevant CSS.
- For a generation bug: read endpoint + service + prompt source.
- For a persistence bug: read endpoint + `install.php` table schema + any helper module.
- For token/limit issues: read endpoint + `tokens-plans.md` files + plan helpers.
- For a cross-flow bug: inspect localStorage keys, response data shape, and both source/destination modules.
