# UI And Assets

## Visual Direction

- Existing system uses dark surfaces, purple accents, compact cards, and vanilla JS controls.
- Preserve established look unless user explicitly asks for a new direction.
- Many model wizard styles are inline in `modelos-ai-shortcode.php`; global/shared styles are in `assets/css/styles.css`.
- Beware duplicate CSS blocks in shortcode files. Later rules often win by cascade.

## Common UI Patterns

- Choice tiles use `.baiw-choice-tile`, `data-choice-target`, `data-choice-value`, and hidden select fields.
- Tile images usually use CSS variable `--baiw-choice-image`.
- Range inputs use `.baiw-range` and `--range-progress`.
- Choices.js is used for enhanced selects where `select.baiw-enhanced-select` is present.
- Model wizard modal/back behavior lives in `modelos-ai-script.js`.

## Asset Paths

- `assets/images/rasgosAvatar/`: rasgos thumbnails.
- `assets/images/peinados/`: hairstyle JPGs named exactly by label.
- `assets/images/estilosDeModelo/`: style references and thumbnails used in model history/edit outfit flow.
- `assets/images/carrouselSkills/`: skill carousel images, with fallbacks in `cardsSkills.php`.
- Icons such as download/edit/delete are under `assets/images/`.

## Responsive Notes

- For model wizard, check desktop and mobile when changing grid/tile/rail behavior.
- Avoid layout shifts: keep stable heights for cards/tiles and stable tracks for rails.
- Horizontal rails should make scroll affordance visible with scrollbar, fade, or partial next item.

## Debugging UI

- If selected/hover/focus colors look inconsistent, search for repeated selectors in both `assets/css/styles.css` and shortcode inline style blocks.
- If an image tile appears blank, verify generated URL, file extension, URL encoding, and CSS variable `--baiw-choice-image`.
- If a button does not show, inspect JS `style.display` logic before changing CSS.
