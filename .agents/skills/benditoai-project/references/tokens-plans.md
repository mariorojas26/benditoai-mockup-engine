# Tokens And Plans

## Token Helpers

- `benditoai_get_user_tokens($user_id)`: get current user tokens and initialize missing meta.
- `benditoai_decrease_tokens($user_id, $amount = 1)`: decrease tokens unless user has unlimited tokens.
- `benditoai_user_has_tokens($user_id, $amount = 1)`: check available balance.
- `benditoai_user_has_unlimited_tokens($user_id)`: admin unlimited or other unlimited state.
- `benditoai_use_token($tokens = 1)`: common helper in `tokens-usage.php`.

## Token UI

- Shortcodes: `[benditoai_tokens]`, `[benditoai_desktop_tokens]`.
- Frontend manager: `assets/js/core/tokens.js`.
- JS functions: `window.benditoaiTokensManager.actualizar(tokens)` and `window.benditoaiActualizarTokensInstantaneo(tokens)`.
- Refresh endpoint: action `benditoai_get_tokens`.

## Token Rule For AI Actions

Validate input and external response first. Only discount tokens after a valid result is produced or committed, then return updated `tokens` in JSON so the UI can refresh instantly.

## Plans

- Config: `includes/modules/plans/plans-config.php`.
- Runtime helpers: `includes/modules/plans/plans-functions.php`.
- Default plan: `includes/modules/plans/plans-init.php`.
- Update endpoint: `wp_ajax_benditoai_update_user_plan`.
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
