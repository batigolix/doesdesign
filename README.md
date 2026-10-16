# doesdesign

Drupal 11 site for [doesdesign.nl](https://www.doesdesign.nl).

Local development runs on [DDEV](https://ddev.com/). Active theme is
`shindo` (a Dopetrope port) in `web/themes/custom/shindo/`.

## Local setup

```bash
ddev start
ddev composer install
ddev drush deploy
```

## Theme development

The `shindo` theme compiles SCSS with the `sass` CLI. Install deps once:

```bash
cd web/themes/custom/shindo && npm install
```

### npm scripts

| Script                  | What it does                                                               |
|-------------------------|----------------------------------------------------------------------------|
| `npm run watch`         | Rebuilds `main.css` and `shindo-overrides.css` on every `.scss` save.      |
| `npm run build:main`    | One-shot build of `main.css` (uncompressed).                               |
| `npm run build:overrides` | One-shot build of `shindo-overrides.css` (**compressed** — what CI expects). |
| `npm run build`         | Runs both `build:main` and `build:overrides`.                              |

> **Watch writes uncompressed CSS.** Always run `npm run build:overrides`
> once after a watch session before committing, so the checked-in
> `shindo-overrides.css` matches what CI produces.

### Where the SCSS lives

- `assets/sass/main.scss` — base Dopetrope styles.
- `assets/sass/overrides.scss` — doesdesign-specific overrides, imported
  as partials from `assets/sass/overrides/`:
  - `_banner.scss` — homepage hero banner (header + h2 positioning).
  - `_variables.scss`, `_typography.scss`, `_h1.scss`, `_buttons.scss`,
    `_gallery.scss`, `_featured-image.scss`, `_cta.scss`, …

## Pre-commit checks

See [AGENTS.md](./AGENTS.md) for the full Quality Gates checklist
(frontend lint, PHP static analysis, SCSS build, config export).

## Issue tracking

This project uses [`bd` (beads)](https://github.com/gastownhall/beads)
for issue tracking. Run `bd prime` for the full workflow context.
