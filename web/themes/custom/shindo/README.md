<!-- AI generated -->
# Shindo

Drupal 11 theme for [doesdesign.nl](https://doesdesign.nl), based on the [html5up.net Dopetrope](https://html5up.net/dopetrope) HTML template. Configurable via the theme-settings UI (colors, fonts, layout options).

## Status

Skeleton stage — see `/Users/boris/Sites/doesdesign/NEW_THEME_PLAN.md` and the beads tracker for the full roadmap.

## Base theme

`stable9` — matches the existing `denbei` theme so template inheritance stays predictable.

## Regions (13)

Header/nav: `header`, `primary_menu`, `secondary_menu`, `help`
Main: `pre_main`, `content`, `sidebar_first`, `sidebar_second`
Footer: `footer_col1`, `footer_col2`, `footer_col3`, `footer`, `post_footer`

## Configurable settings (planned)

- **Colors** — 5 hex-input color pickers (primary, secondary, accent, background, text); Olivero-style hex→HSL injected as CSS custom properties. See beads `doesdesign-5wg`.
- **Layout** — container width (narrow/default/wide), sidebar position (left/right/none), hero toggle. See beads `doesdesign-il3`.
- **Fonts** — provided by the [fontyourface](https://www.drupal.org/project/fontyourface) contrib module (`/admin/appearance/font-your-face`), not duplicated in shindo's own settings. See beads `doesdesign-lfy`.
- **Standard Drupal** — logo, favicon, slogan (inherited from `stable9`).

## Build

Two paths — pick whichever fits your workflow.

**Preferred: via DDEV** (works without any host-side Node install):

```bash
ddev theme-build shindo      # one-off compile screen.scss + print.scss → css/
ddev theme-watch shindo      # rebuild on save (Ctrl+C to stop)
ddev theme-build             # build every theme under web/themes/custom that has a package.json
```

These commands run inside the DDEV web container against Node/npm there (Node 22 as configured in `.ddev/config.yaml`).

**Direct via host npm** (requires Node ≥ 14 locally):

```bash
cd web/themes/custom/shindo
npm install
npm run build         # or: npm run watch
```

Compiled output lives in `css/` and is committed alongside sources (matches the pattern used by `denbei`).

## TODO in skeleton

- `logo.svg`, `favicon.ico`, `screenshot.png` — placeholders needed for the appearance page. Add before enabling.
- `css/screen.css`, `css/print.css` — currently missing; will be produced by the SCSS build pipeline (see `doesdesign-d5b`).
