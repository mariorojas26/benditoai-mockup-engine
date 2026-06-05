# UI And Assets

## Visual Direction

- Existing system uses dark surfaces, purple accents, compact cards, and vanilla JS controls.
- Preserve established look unless user explicitly asks for a new direction.
- Many model wizard styles are inline in `modelos-ai-shortcode.php`; global/shared styles are in `assets/css/styles.css`.
- Beware duplicate CSS blocks in shortcode files. Later rules often win by cascade.

## Brand Feel

BenditoAI should feel like a premium AI commerce studio: dark, precise, fast, and conversion-oriented. It is not a playful generic AI demo and not a white SaaS dashboard. The UI should help users generate, inspect, edit, and reuse visuals with confidence.

Good BenditoAI UI:

- Uses near-black bases and dark purple surfaces.
- Uses violet for active, focus, and primary states.
- Makes previews and generated images the visual priority.
- Keeps controls compact and predictable.
- Shows loading, empty, error, and success states clearly.
- Avoids marketing filler inside actual tools.

Bad BenditoAI UI:

- White cards or bright neutral dashboards.
- Huge hero copy inside operational tools.
- Decorative gradients/orbs that compete with images.
- Layout shifts when selecting options or when results load.
- Hiding important controls behind unclear custom UI.

## Common UI Patterns

- Choice tiles use `.baiw-choice-tile`, `data-choice-target`, `data-choice-value`, and hidden select fields.
- Tile images usually use CSS variable `--baiw-choice-image`.
- Range inputs use `.baiw-range` and `--range-progress`.
- Choices.js is used for enhanced selects where `select.baiw-enhanced-select` is present.
- Model wizard modal/back behavior lives in `modelos-ai-script.js`.
- Primary tool forms often use dark panels, compact labels, and image preview areas.
- Generated result cards must support download/edit/delete/use-for-campaign actions when that module expects them.
- If JS dynamically creates cards, match PHP-rendered classes and data attributes.

## Header And Account Menu UI

- Visual target: dark glass Astra header with subtle border, blur, premium violet active underline, and compact navigation.
- Header menu labels/order are managed in WordPress admin menus. Keep code/CSS changes visual unless menu content changes are explicitly requested.
- Main local header CSS lives in the Custom CSS/JS generated files under `wp-content/uploads/custom-css-js/`: `3243.css`, `3244.css`, and `3245.css`. Repo-side fallback and shortcode/account styling lives in `assets/css/styles.css`.
- Home behavior: `body.home #masthead` is fixed over the hero so the first section starts directly under the glass menu without a gray gap.
- Non-home behavior: `body:not(.home) #masthead` is relative, centered, and occupies layout space so page content does not sit underneath it.
- Desktop account markup comes from `[benditoai_desktop_user]` in `includes/modules/auth/auth-dropdown.php`.
- The desktop header hides `.benditoai-desktop-token-counter`; tokens appear inside the account dropdown after the plan row.
- Account button UX: width should fit short names, cap at a comfortable max for long names, and use ellipsis only when the name truly exceeds available space. Current selectors are `.benditoai-desktop-user__button`, `.benditoai-desktop-user__name`, and `.benditoai-desktop-user`.
- Dropdown order: `Mis modelos`, `Plan: {plan}`, `Tokens {count}`, `Cerrar sesion`. Keep the token row subtle and the logout row red.

## Motion And Scroll

- Use GSAP/ScrollTrigger for complex scroll-linked home/marketing animations.
- `[maquina_texto]` renders the centered home hero with four decorative floating cards, two on each side. The cards use `assets/js/home/maquina-hero.js` for subtle GSAP/ScrollTrigger parallax and ambient floating. Override images with `card_1_image` through `card_4_image`; defaults use `assets/images/Home/cardshero1.jpg` through `cardshero4.jpg`.
- Reusable GSAP stepper: use `.bai-gsap-stepper` with child `.bai-gsap-stepper__dot`; update each dot's `--bai-gsap-dot-progress` from `0` to `1` as its card enters/leaves, and keep `.is-active`/`aria-current="step"` for semantics. Each dot represents one card and grows proportionally with scroll.
- Scroll animations should have one source of truth for progress; avoid mixing CSS sticky, manual scroll math, and ScrollTrigger pinning unless the interaction explicitly requires it.
- Default GSAP feel for BenditoAI: calm, smooth, premium, and fluid. The user prefers tranquil transitions over aggressive, robotic, or frame-by-frame movement.
- Use `scrub` for scroll-linked progress and render from the smoothed timeline progress when possible, not raw trigger progress.
- Avoid separate forced snapping unless requested and tested. If snap is needed, make it subtle and non-blocking.
- Prefer transform/opacity animation, `quickTo`/timeline smoothing, and compositor-friendly CSS hints for frequently updated motion.
- Always include or preserve `prefers-reduced-motion` fallback.
- Keep animation transforms on child elements, not on pinned containers.

## Asset Paths

- `assets/images/rasgosAvatar/`: rasgos thumbnails.
- `assets/images/peinados/`: hairstyle JPGs named exactly by label.
- `assets/images/estilosDeModelo/`: style references and thumbnails used in model history/edit outfit flow.
- `assets/images/carrouselSkills/`: skill carousel images, with fallbacks in `cardsSkills.php`.
- `[benditoai_gsap_cards]` renders the "como funciona" GSAP scroll scene. It uses `assets/css/gsap-cards.css` plus `assets/js/home/gsap-cards.js`: each step has one image card and one copy block; desktop alternates image/text sides per step, the image enters from above, centers, then exits downward as the next step enters from above on the opposite side. The last step holds before the section releases. Defaults use `assets/images/1crea.png`, `assets/images/crea2.png`, and `assets/images/crea3.png`; override desktop images with `image_1`, `image_2`, `image_3` and mobile fallbacks with `image_mobile_1`, `image_mobile_2`, `image_mobile_3`.
- On the home page, pinned GSAP shortcodes read the fixed `#masthead` height and start below the header so the first scene does not sit under the floating menu. `[benditoai_gsap_cards]` also matches the header width with `min(90vw, 1620px)`, balances the top/bottom stage breathing, and keeps the desktop home overflow visible so image cards can enter from above and exit below without being clipped. The header offset behavior applies to `[benditoai_gsap_cards]` in `assets/js/home/gsap-cards.js` and `[benditoai_cards_skills]` inline in `includes/modules/ux/cardsSkills.php`.
- Icons such as download/edit/delete are under `assets/images/`.

## Responsive Notes

- For model wizard, check desktop and mobile when changing grid/tile/rail behavior.
- Avoid layout shifts: keep stable heights for cards/tiles and stable tracks for rails.
- Horizontal rails should make scroll affordance visible with scrollbar, fade, or partial next item.
- Test key UI at roughly 320, 768, 1024, and desktop widths.
- Keep media previews visible and useful on mobile.
- If a section is 100vh, ensure it still has readable content on shorter laptop viewports.

## Debugging UI

- If selected/hover/focus colors look inconsistent, search for repeated selectors in both `assets/css/styles.css` and shortcode inline style blocks.
- If an image tile appears blank, verify generated URL, file extension, URL encoding, and CSS variable `--baiw-choice-image`.
- If a button does not show, inspect JS `style.display` logic before changing CSS.
- If an AJAX UI appears stuck, inspect browser console plus JSON response shape before changing layout.
- If tokens do not update, check whether response includes `tokens` and whether `tokens.js` helper is called.
- If a modal/card exists in PHP and JS, update both render paths.

## Mandatory Look And Feel Guide

- Source of truth: `assets/docs/guia-look-and-feel-card-skills.md`.
- For any new shortcode/component/refactor UI, read and apply this guide before coding.
- If a request asks for a different style, explicitly confirm that it is an intentional exception.

