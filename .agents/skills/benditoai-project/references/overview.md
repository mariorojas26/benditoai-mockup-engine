# Overview

## Purpose

`bendidoai-mockup-engine` is a WordPress plugin for AI-assisted commerce visuals: mockups, AI models, model outfits, campaigns, enhance image, remove background, trends, tokens, plans, and UI shortcodes.

## Bootstrap

- `bendidoai-mockup-engine.php` defines `BENDIDOAI_PLUGIN_PATH` and `BENDIDOAI_PLUGIN_URL`.
- It requires core/module PHP files and enqueues global assets.
- `benditoai_enqueue_assets()` loads shared CSS/JS and localizes `benditoai_ajax`.
- `benditoai_modelos_ai_scripts()` loads Choices vendor assets and `includes/modules/modelos-ai/modelos-ai-script.js`.
- `includes/core/install.php` creates/upgrades database tables on `plugins_loaded`.

## Core Tables

- `wp_benditoai_historial`: mockup/generation history.
- `wp_benditoai_campanas_ai`: AI campaign records.
- `wp_benditoai_modelos_ai`: AI model records.
- `wp_benditoai_modelos_ai_outfits`: saved outfits per model.

## Common Request Shape

Frontend UI renders through a shortcode, vanilla JS collects form state, `fetch()` posts to `admin-ajax.php`, PHP validates login/nonce/ownership/plan/tokens, then calls Gemini or another processor, stores result if needed, and returns JSON.

## Shortcodes

- `[benditoai_crear_mockup]`, `[benditoai_dashboard]`: mockup module.
- `[benditoai_modelos_ai]`: model creation wizard.
- `[benditoai_modelos_ai_historial]`: model history and saved outfits.
- `[benditoai_campanas_ai]`: campaign creation.
- `[benditoai_tokens]`, `[benditoai_desktop_tokens]`: token display.
- `[benditoai_remove_bg]`, `[benditoai_enhance_image]`, `[benditoai_trending]`: tools.
- UX/home shortcodes live under `includes/modules/ux`, `includes/modules/Home`, and `includes/modules/scroll-video`.

## Safety Defaults

- Confirm user is logged in before tools that require account state.
- Check nonce on AJAX endpoints when present.
- Check ownership before editing/deleting models/outfits.
- Keep `benditoai_ajax` compatibility unless deliberately changing frontend request plumbing.
