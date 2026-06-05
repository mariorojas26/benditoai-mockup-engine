# Overview

## Purpose

`bendidoai-mockup-engine` is a WordPress plugin for AI-assisted commerce visuals: mockups, AI models, model outfits, campaigns, enhance image, remove background, trends, tokens, plans, account/header UI, and UI shortcodes.

## Product Context

BenditoAI targets sellers, brands, and creators that need commercial visuals without a full production team. The plugin lets a user generate a model, dress/edit that model, create mockups or campaign images, improve/remove backgrounds, and manage usage through tokens and plans.

The main promise is practical: create sellable visual content for ecommerce, ads, and social media. UX should prioritize speed, clarity, preview quality, and confidence that each generated image can be reused in a commercial workflow.

## Architecture Shape

This is not a single-page app. It is a WordPress plugin made of PHP modules, shortcodes, vanilla JS files, AJAX actions, shared assets, and Gemini service helpers.

Most user journeys follow this chain:

`shortcode markup -> vanilla JS state/form -> admin-ajax.php action -> validation/tokens/plans -> Gemini/service call -> uploads/DB persistence -> JSON response -> UI/history update`.

## Bootstrap

- `bendidoai-mockup-engine.php` defines `BENDIDOAI_PLUGIN_PATH` and `BENDIDOAI_PLUGIN_URL`.
- It requires core/module PHP files and enqueues global assets.
- `benditoai_enqueue_assets()` loads shared CSS/JS and localizes `benditoai_ajax`.
- `includes/core/performance-assets.php` centralizes local image asset resolution, optional WebP lookup, dimensions, and shortcode presence checks.
- `benditoai_modelos_ai_scripts()` loads Choices vendor assets and `includes/modules/modelos-ai/modelos-ai-script.js` only on singular pages that contain `[benditoai_modelos_ai]` or `[benditoai_modelos_ai_historial]`.
- `includes/core/install.php` creates/upgrades database tables on `plugins_loaded`.

## Core Tables

- `wp_benditoai_historial`: mockup/generation history.
- `wp_benditoai_campanas_ai`: AI campaign records.
- `wp_benditoai_modelos_ai`: AI model records.
- `wp_benditoai_modelos_ai_outfits`: saved outfits per model.

## Common Request Shape

Frontend UI renders through a shortcode, vanilla JS collects form state, `fetch()` posts to `admin-ajax.php`, PHP validates login/nonce/ownership/plan/tokens, then calls Gemini or another processor, stores result if needed, and returns JSON.

## Main Product Areas

- Home/marketing UX: explanatory and animated shortcodes, including GSAP cards.
- Mockup generator: product/reference image inputs and prompt-driven commercial output.
- Modelos AI: model creation, rasgos/from-scratch flow, reference-photo flow, model history, edits, outfits, and campaign handoff.
- Campaigns: generate campaign-ready visuals using selected model/outfit context.
- Utility tools: remove background, enhance image, trends.
- Account/commerce system: tokens, plans, limits, auth dropdown, header account menu, redirects.

## Header And Account Menu

- The WordPress/Astra navigation labels and order are administered from WordPress menus, not from this plugin. Treat code work here as visual behavior only unless the user explicitly asks to change menu content.
- Header glass/sticky visuals are split between generated Custom CSS/JS plugin files in `wp-content/uploads/custom-css-js/` and repo fallback/shared rules in `assets/css/styles.css`.
- Current home behavior: `body.home #masthead` is fixed over the hero so the menu floats without leaving a gray band.
- Current non-home behavior: `body:not(.home) #masthead` is relative and occupies normal space so internal page content is not covered.
- Desktop account UI is rendered by `[benditoai_desktop_user]` from `includes/modules/auth/auth-dropdown.php`. The dropdown contains model link, plan, tokens, and logout; the standalone desktop token counter is hidden inside the header.

## Shortcodes

- `[benditoai_crear_mockup]`, `[benditoai_dashboard]`: mockup module.
- `[benditoai_modelos_ai]`: model creation wizard.
- `[benditoai_modelos_ai_historial]`: model history and saved outfits.
- `[benditoai_campanas_ai]`: campaign creation.
- `[benditoai_tokens]`, `[benditoai_desktop_tokens]`: token display. The desktop header now hides the standalone counter and shows tokens inside `[benditoai_desktop_user]`.
- `[benditoai_user_menu]`, `[benditoai_desktop_user]`: authenticated user dropdown/account button.
- `[benditoai_plan_cards]`: starter/pro/elite subscription cards with customizable copy and media placeholder.
- `[benditoai_remove_bg]`, `[benditoai_enhance_image]`, `[benditoai_trending]`: tools.
- `[benditoai_gsap_cards]`: GSAP/ScrollTrigger pinned 3-card Home component with synchronized image transitions and expanding copy.
- UX/home shortcodes live under `includes/modules/ux`, `includes/modules/Home`, and `includes/modules/scroll-video`.

## Safety Defaults

- Confirm user is logged in before tools that require account state.
- Check nonce on AJAX endpoints when present.
- Check ownership before editing/deleting models/outfits.
- Keep `benditoai_ajax` compatibility unless deliberately changing frontend request plumbing.
- Do not spend tokens until a valid output exists.
- Return updated token counts on successful token-consuming actions.
- Keep frontend-created cards/modals consistent with PHP-rendered initial markup.
