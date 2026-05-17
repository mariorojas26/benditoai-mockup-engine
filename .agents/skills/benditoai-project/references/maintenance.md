# Maintenance

## Update This Skill When

- A new module, shortcode, AJAX action, DB table, or service file is added.
- A field is renamed across frontend/backend.
- Asset directories change.
- Token, plan, or saved outfit rules change.
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
