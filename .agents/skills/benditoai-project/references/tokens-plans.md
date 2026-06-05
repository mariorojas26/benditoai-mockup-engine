# Tokens And Plans

## Token Helpers

- `benditoai_get_user_tokens($user_id)`: get current user tokens and initialize missing meta.
- `benditoai_decrease_tokens($user_id, $amount = 1)`: decrease tokens unless user has unlimited tokens.
- `benditoai_user_has_tokens($user_id, $amount = 1)`: check available balance.
- `benditoai_user_has_unlimited_tokens($user_id)`: admin unlimited or other unlimited state.
- `benditoai_use_token($tokens = 1)`: common helper in `tokens-usage.php`.

## Token UI

- Shortcodes: `[benditoai_tokens]`, `[benditoai_desktop_tokens]`.
- Header account shortcode: `[benditoai_desktop_user]` renders the desktop account button and includes the current token balance inside its dropdown.
- Frontend manager: `assets/js/core/tokens.js`.
- JS functions: `window.benditoaiTokensManager.actualizar(tokens)` and `window.benditoaiActualizarTokensInstantaneo(tokens)`.
- Refresh endpoint: action `benditoai_get_tokens`.

## Header Account Token Placement

- In the desktop header, the standalone token counter is hidden with CSS and tokens live inside `includes/modules/auth/auth-dropdown.php`.
- Dropdown order is: `Mis modelos`, `Plan: {plan}`, `Tokens {count}`, `Cerrar sesion`.
- Admin/unlimited token text can still be computed by PHP, but the header presentation should stay compact and avoid a separate token pill beside the account button.
- Keep `[benditoai_tokens]` and `[benditoai_desktop_tokens]` available for other pages or admin-controlled placements; avoid duplicating them in the Astra header unless the UX is intentionally changed.

## Token Rule For AI Actions

Validate input and external response first. Only discount tokens after a valid result is produced or committed, then return updated `tokens` in JSON so the UI can refresh instantly.

## Plans

- Config: `includes/modules/plans/plans-config.php`.
- Runtime helpers: `includes/modules/plans/plans-functions.php`.
- Default plan: `includes/modules/plans/plans-init.php`.
- Update endpoint: `wp_ajax_benditoai_update_user_plan`.
- Pricing UI shortcode: `[benditoai_plan_cards]` from `includes/modules/plans/plans-shortcode.php`.
- Model creation uses plan data to enforce `max_modelos`.
- Saved outfits use plan/outfit limit data in model history UI and backend validation.

## Adding A New AI Tool

Minimum pattern:

- Shortcode checks login before rendering account-only features.
- Form/button use shared request-manager conventions when applicable: `.benditoai-ai-form`, `.benditoai-ai-button`.
- AJAX endpoint validates login and nonce.
- Endpoint returns `wp_send_json_success()` or `wp_send_json_error()`.
- On success, include updated `tokens` if tokens changed.
- Frontend calls `benditoaiActualizarTokensInstantaneo(tokens)` when response includes tokens.
