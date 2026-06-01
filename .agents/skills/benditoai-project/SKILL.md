---
name: benditoai-project
description: Compact project map for the BenditoAI WordPress plugin. Use when a request touches this repository's architecture, WordPress hooks, shortcodes, AJAX endpoints, AI generation flows, tokens, plans, model creation wizard, mockups, campaigns, saved outfits, frontend UI, assets, or Gemini service integrations, before reading source files.
---

# BenditoAI Project

Use this skill as the first stop for work in `bendidoai-mockup-engine`. It is a compact operating manual for another AI or developer entering the project, not a full code dump.

## What This Project Is

BenditoAI is a WordPress plugin for AI-assisted commerce visuals. It helps users create product mockups, AI models, model outfits, campaign images, enhanced images, background removal, trend images, token/plan management, and marketing/home UI shortcodes.

The product feeling is: dark premium AI tool, direct-to-commerce, focused on helping brands produce sellable visuals faster. Most UI is PHP-rendered shortcode markup plus vanilla JavaScript and AJAX endpoints that call Gemini services, store generated media, and update token state.

## Working Model

- Frontend entry points are usually shortcodes.
- Interactions are mostly vanilla JS modules under `assets/js/...`.
- Server actions are WordPress AJAX endpoints registered in PHP modules.
- AI generation generally flows through Gemini service files and persists images into WordPress uploads.
- User limits are enforced with plans and tokens.
- Visual consistency matters: read the look and feel guide before changing UI.

## Token-Saving Workflow

1. Classify the request by domain.
2. Read only the matching reference file(s) below.
3. Open source files only after naming the smallest likely file set.
4. Prefer `rg` targeted searches over broad file reads.
5. If changing UI, read `references/ui-assets.md` plus `assets/docs/guia-look-and-feel-card-skills.md`.
6. Update the relevant reference after architectural changes, new AJAX actions, new shortcodes, new assets paths, new external service behavior, or important UI flow changes.

## Reference Router

- Project boot, folders, hooks, and global loading: read `references/overview.md`.
- File ownership and likely edit targets: read `references/file-map.md`.
- Model creation, rasgos miniwizard, model history, editing, outfits, and campaign handoff: read `references/modelos-ai.md`.
- Tokens, plans, limits, and usage rules: read `references/tokens-plans.md`.
- Mockups, remove-bg, enhance, trends, and campaigns: read `references/workflows.md`.
- UI conventions, CSS, asset paths, images, and visual system: read `references/ui-assets.md` and `../../../assets/docs/guia-look-and-feel-card-skills.md` (obligatorio para cualquier UI nueva o refactor UI).
- Gemini/API calls, prompts, generated media, and persistence: read `references/ai-services.md`.
- How to keep this skill accurate and lean: read `references/maintenance.md`.

## Golden Rules

- Do not change database field names, form field names, AJAX action names, or localized JS object names casually; they connect PHP, JS, DB rows, and rendered history cards.
- Preserve WordPress login, nonce, ownership, plan, and token checks before changing endpoint behavior.
- For token-consuming AI actions, only discount tokens after a valid generation/result is produced, then return updated `tokens`.
- For user-facing flows, inspect shortcode markup, matching JS, matching CSS, endpoint, and persistence together.
- Do not introduce a new framework unless the existing module already uses it or the user explicitly asks.
- Keep module styles scoped when possible. Global CSS changes in `assets/css/styles.css` can affect many shortcodes.
- The UI should feel compact, premium, dark, purple-accented, and useful, not like a generic landing page.

## Repo Constants

- Plugin root: `bendidoai-mockup-engine`.
- Main file: `bendidoai-mockup-engine.php`.
- Constants: `BENDIDOAI_PLUGIN_PATH`, `BENDIDOAI_PLUGIN_URL`.
- Shared frontend localized object: `benditoai_ajax` with `ajax_url`, `nonce`, and `plugin_url`.
- Main global stylesheet: `assets/css/styles.css`.
- Model wizard script currently loads from `includes/modules/modelos-ai/modelos-ai-script.js`.

## Look And Feel Snapshot

- Base: near-black backgrounds, dark purple surfaces, bright violet accents.
- Text: white titles, soft lavender body copy, muted metadata.
- Surfaces: subtle translucent purple borders, compact cards, stable spacing, no white tool surfaces.
- Motion: smooth and intentional, respect `prefers-reduced-motion`, avoid abrupt layout shifts.
- GSAP motion preference: calm, fluid, premium, and Apple-like; avoid robotic frame-by-frame motion, abrupt snapping, or aggressive transitions unless the user explicitly asks.
- Media: previews should be clear, contained, `object-fit: cover`, and never feel like broken placeholders.
- Typography: inherit the global font; avoid setting `font-family` inside modules unless there is a strong reason.
- Preferred UI density: SaaS/tool-like, scannable, practical. Avoid oversized decorative sections inside tools.

## Common Implementation Patterns

- Shortcode renders wrapper, data attributes, forms/cards, and sometimes inline CSS.
- JS reads data attributes, manages state, and posts `FormData` or JSON to `admin-ajax.php`.
- PHP sanitizes request data, validates capability/state, calls helpers/services, stores result, and returns JSON.
- History UIs often need both initial PHP-rendered cards and JS-created cards to stay in sync.
- Image assets should usually live under `assets/images/...`; uploaded/generated media belongs in WordPress uploads.

## Before Editing Checklist

- Identify the module and read only its reference file first.
- Confirm the exact shortcode/action/script/style names with `rg`.
- Check whether similar behavior already exists in another module.
- For UI, compare against the look and feel guide and existing components.
- For AI flows, trace form field -> JS state -> AJAX sanitizer -> prompt -> DB/response -> history rendering.
- For scroll/animation work, prefer GSAP/ScrollTrigger patterns already loaded by the plugin when available; use scrubbed timelines and softened progress rather than manual jumps.

## Rules For Future Work

- Preserve WordPress nonce, login, ownership, and plan checks before changing endpoint behavior.
- For user-facing generation tools, return updated `tokens` when a token-consuming action succeeds.
- For UI work, check both shortcode markup and related JS; much of this plugin uses PHP-rendered HTML plus vanilla JS.
- Do not assume paths from older notes are correct; verify with `rg --files` if a reference and source disagree.
- Keep references concise. Put "what connects to what" here, not whole functions.
- When you add or change important behavior, update this skill so the next AI understands the new connection.

