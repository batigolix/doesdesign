<!-- AI generated -->
# Shindo overrides — SCSS

Source for `assets/css/shindo-overrides.css`, the stylesheet loaded
after Dopetrope's `main.css` (see `shindo.libraries.yml`) to override
upstream declarations without touching the vendored file.

## Structure

- `../overrides.scss` — entry point; `@use`'s every partial in this
  directory.
- `_variables.scss` — shared kleuren, gutter-waardes en breakpoints.
- `_h1.scss` — node title sizing (bead doesdesign-vg1).
- `_messages.scss` — Drupal status/warning/error message styling
  (bead doesdesign-w8y).
- `_featured-image.scss` — Dopetrope-style featured image anchor
  (bead doesdesign-zmf).
- `_gallery.scss` — object gallery grid alignment + gap
  (beads doesdesign-z1n / doesdesign-x6r).
- `_pager.scss` — Drupal pager pill styling
  (bead doesdesign-cq9).
- `_lists.scss` — bullet reset op Drupal-lists
  (bead doesdesign-098).
- `_banner.scss` — random hero banner
  (bead doesdesign-xgx).
- `_intro.scss` — intro-columns gap
  (bead doesdesign-050).
- `_contextual.scss` — herstel contextual trigger pencil-icon
  (bead doesdesign-w8yy).
- `_typography.scss` — kleine block-element margins.

## Build

```
cd web/themes/custom/shindo
npm run build:overrides    # sass -> assets/css/shindo-overrides.css
npm run build              # main + overrides in één keer
npm run watch              # live reload voor beide bundles
```

Compressed style is used voor de output. De bron-SCSS behoudt alle
bead-references en rationale-comments; die worden door de compressor
weggegooid.

## Adding a new override

1. Kies of maak een partial die past bij het onderdeel.
2. `@use "variables" as *;` bovenaan als je kleuren of breakpoints
   nodig hebt.
3. Voeg een `@use "overrides/<naam>";` toe aan
   `assets/sass/overrides.scss`.
4. Run `npm run build:overrides` en commit zowel de SCSS als de
   gegenereerde `assets/css/shindo-overrides.css`.
