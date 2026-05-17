---
name: benditoai-project
description: Compact project map for the BenditoAI WordPress plugin. Use when a request touches this repository's architecture, WordPress hooks, shortcodes, AJAX endpoints, AI generation flows, tokens, plans, model creation wizard, mockups, campaigns, saved outfits, frontend UI, assets, or Gemini service integrations, before reading source files.
---

# BenditoAI Project

Use this skill as the first stop for work in `bendidoai-mockup-engine`. It is a routing map, not a full code dump.

## Token-Saving Workflow

1. Classify the request by domain.
2. Read only the matching reference file(s) below.
3. Open source files only after naming the smallest likely file set.
4. Prefer `rg` targeted searches over broad file reads.
5. Update the relevant reference after architectural changes, new AJAX actions, new shortcodes, new assets paths, or important UI flow changes.

## Reference Router

- Project boot, folders, hooks, and global loading: read `references/overview.md`.
- File ownership and likely edit targets: read `references/file-map.md`.
- Model creation, rasgos miniwizard, model history, editing, outfits, and campaign handoff: read `references/modelos-ai.md`.
- Tokens, plans, limits, and usage rules: read `references/tokens-plans.md`.
- Mockups, remove-bg, enhance, trends, and campaigns: read `references/workflows.md`.
- UI conventions, CSS, asset paths, images, and visual system: read `references/ui-assets.md`.
- Gemini/API calls, prompts, generated media, and persistence: read `references/ai-services.md`.
- How to keep this skill accurate and lean: read `references/maintenance.md`.

## Repo Constants

- Plugin root: `bendidoai-mockup-engine`.
- Main file: `bendidoai-mockup-engine.php`.
- Constants: `BENDIDOAI_PLUGIN_PATH`, `BENDIDOAI_PLUGIN_URL`.
- Shared frontend localized object: `benditoai_ajax` with `ajax_url`, `nonce`, and `plugin_url`.
- Main global stylesheet: `assets/css/styles.css`.
- Model wizard script currently loads from `includes/modules/modelos-ai/modelos-ai-script.js`.

## Rules For Future Work

- Preserve WordPress nonce, login, ownership, and plan checks before changing endpoint behavior.
- For user-facing generation tools, return updated `tokens` when a token-consuming action succeeds.
- For UI work, check both shortcode markup and related JS; much of this plugin uses PHP-rendered HTML plus vanilla JS.
- Do not assume paths from older notes are correct; verify with `rg --files` if a reference and source disagree.
- Keep references concise. Put "what connects to what" here, not whole functions.
