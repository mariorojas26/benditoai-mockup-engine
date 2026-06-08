# Maintenance

## Update This Skill When

- A new module, shortcode, AJAX action, DB table, or service file is added.
- A field is renamed across frontend/backend.
- Asset directories change.
- Token, plan, or saved outfit rules change.
- Header/account menu behavior changes, especially if CSS is split between `assets/css/styles.css` and WordPress generated Custom CSS/JS files.
- A repeated debugging discovery would save future context.

## Keep It Lean

- Keep `SKILL.md` as a router.
- Put details in one-level reference files.
- Prefer file paths, contracts, field names, and flow summaries over copied code.
- Avoid long examples unless they prevent repeated mistakes.

## Suggested Future Automation

Add a small index generator only if it stays deterministic and compact. Useful output:

- list of shortcodes and source files,
- list of `wp_ajax_*` actions,
- list of enqueued frontend scripts,
- list of modules and primary files,
- last updated timestamp.

Do not auto-paste full source into references. The point is to route attention, not duplicate the repo.

## Performance Asset Checklist

- Put heavy home/plugin imagery under `assets/images/`; do not point v1 tooling at WordPress uploads without approval.
- Run `npm run optimize:images -- --dry-run` and review the list before `npm run optimize:images`.
- Keep JPG/PNG originals and commit generated `.webp` files under `assets/images-webp/`.
- Use `npm run optimize:images:watch` during asset-heavy editing so new images in `assets/images/` are converted automatically.
- For new shortcode images, prefer `benditoai_get_image_asset()` plus `<picture>` and fallback.
- Add `width`, `height`, `decoding="async"`, and `loading="lazy"` unless the image is truly visible immediately above the fold.
- Keep `Choices`/model wizard assets conditional to pages with `[benditoai_modelos_ai]` or `[benditoai_modelos_ai_historial]`.
- After changes, check home desktop/mobile, one non-home page, and model pages if their URLs are known.

## Header Change Checklist

- Note whether the change belongs to WordPress menu configuration, generated Custom CSS/JS, plugin CSS, or shortcode PHP.
- If generated CSS files in `wp-content/uploads/custom-css-js/` were edited directly, document the snippet purpose and mirror durable fallback rules in repo CSS when practical.
- Verify `body.home` and at least one `body:not(.home)` page after header position changes.
- Keep account button sizing rules documented when changing truncation, max width, padding, or dropdown row order.
