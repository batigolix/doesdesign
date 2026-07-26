# Shindo theme — verfijnd migratieplan

## Context

De doesdesign D11-site draait op het custom-thema `denbei` (Drupal 7 Shoami-port, base `stable9`). Het is verouderd, mist responsive breakpoints als config, heeft hardgecodeerde block-ID-selectors in SCSS, en biedt geen theme-settings UI voor kleuren/fonts/layout. Doel: `shindo` bouwen (Dopetrope-based), configureerbaar via `/admin/appearance/settings/shindo`, met behoud van alle huidige content-structuur en block-plaatsing. Denbei blijft actief tot shindo productie-klaar is.

Het oorspronkelijke plan (`NEW_THEME_PLAN.md`) had een aantal onjuiste aannames — met name de blinde overname van alpaca's 13 regions, het over het hoofd zien van `denbei_preprocess_page` (met is_front-fix en tabs-render), het missen van het `hero-teaser` template, het niet-adresseren van `doesdesign_tools` blocks, en het ontbreken van een concrete block-config-migratiestap. Dit plan corrigeert die punten.

**Aangenomen defaults** (bij goedkeuring nog te overrulen): 10 regions, gtranslate behouden, massively verwijderen, alle 4 preprocess-hooks porten.

---

## 1. Region-set

**10 regions** (niet alpaca's 13): `header`, `header_right`, `primary_menu`, `help`, `content_top`, `content`, `sidebar`, `footer`, `footer_bottom`, `breadcrumb`.

Reden: denbei heeft er 7, en site heeft geen tweede sidebar of 3-koloms footer. `primary_menu` maakt het main-menu-block los van `content`; `footer_bottom` scheidt copyright van kolom-content; `breadcrumb` is nu hardgecodeerd in template.

## 2. Block-config-migratie

Kopieer + rename in `config/sync/` (NIET via UI — verliest weights/context):

| Bestand oud | Nieuw | Nieuwe region |
|---|---|---|
| `block.block.denbei_social.yml` | `shindo_social.yml` | `header_right` |
| `block.block.denbei_translate.yml` | `shindo_translate.yml` | `header_right` |
| `block.block.denbei_main_menu.yml` | `shindo_main_menu.yml` | `primary_menu` |
| `block.block.denbei_slideshow.yml` | `shindo_slideshow.yml` | `content_top` |
| `block.block.denbei_messages.yml` | `shindo_messages.yml` | `content` |
| `block.block.denbei_content.yml` | `shindo_content.yml` | `content` |
| `block.block.denbei_news.yml` | `shindo_news.yml` | `sidebar` |
| `block.block.denbei_search.yml` | `shindo_search.yml` | `sidebar` |
| `block.block.denbei_object_types.yml` | `shindo_object_types.yml` | `sidebar` |
| `block.block.denbei_flickr.yml` | `shindo_flickr.yml` | `sidebar` |
| `block.block.denbei_footer.yml` | `shindo_footer.yml` | `footer_bottom` |

Per bestand aanpassen: `id`, `theme: denbei→shindo`, `region:`, `dependencies.theme`. Voeg `shindo.settings` en shindo-blocks toe aan de default `config_split`; NIET in `config_ignore` (anders slaan admin-wijzigingen niet meer terug).

## 3. Theme-skeleton

`web/themes/custom/shindo/` — structuur zoals in oorspronkelijk plan, maar:
- `templates/` opdeling gelijk aan denbei (`content/`, `block/`, `navigation/`, `layout/`, `field/`, `form/`, `views/`, `misc/`, `user/`, `dataset/`, `content-edit/`).
- `config/schema/shindo.schema.yml` + `config/install/shindo.settings.yml` (met defaults gescraped uit huidige denbei `screen.css`).
- `shindo.breakpoints.yml` met mobile/tablet/desktop/wide (waardes overnemen uit denbei's SCSS `$break1..3`).
- Geen `google-fonts` externe library — vervangen door fontyourface (§7).

## 4. Preprocess-hooks (`shindo.theme`)

Alle 4 leveren:

| Hook | Actie |
|---|---|
| `shindo_preprocess_image` | 1-op-1 uit denbei — strip width/height op image-styles `square-thumb`, `square-thumb-medium`, `large-thumb`, `x-medium`. |
| `shindo_preprocess_menu_local_task` | Herschrijven — `.button`/`.primary` classes verplaatsen naar `menu-local-task.html.twig`, weg uit URL-attributen. |
| `shindo_preprocess_page` | **Verplicht porten** (plan miste dit). Behoud: site_name, site_slogan, logo, handmatige front-alias resolve (PathMatcher-cache-bug is niet gefixt), page_title via `title_resolver`, handmatige tabs-render via `plugin.manager.menu.local_task`. |
| `shindo_preprocess_html` | **Nieuw** — inline `--color-*-h/s/l` CSS custom props via `_shindo_hex_to_hsl()` (Olivero-patroon). |

## 5. Color-pipeline

- `_shindo_hex_to_hsl(string $hex): array` als helper in `shindo.theme` (Olivero-patroon; hex → \[h, s%, l%\]).
- `shindo_preprocess_html()` loopt over `color_primary`, `color_accent`, `color_bg`, `color_text`, injecteert `--color-<k>-h/s/l` in `<html style="…">`.
- SCSS gebruikt `hsl(var(--color-primary-h) var(--color-primary-s) var(--color-primary-l))`; derivaten via `calc()` op lightness.
- `theme-settings.php` gebruikt `#type: color` + `#config_target: 'shindo.settings:color_<x>'` — geen submit-handler nodig.

## 6. Fonts — fontyourface

- `composer require "drupal/fontyourface:^4.4"` (D11-compatible; recentste stable).
- Enable core module + `google_fonts_api` submodule.
- Admin mapt lettertypes via `/admin/appearance/font-your-face` naar CSS-selectors (`body`, `h1..h6, .site-title`).
- Fallback: `shindo.libraries.yml` levert `fonts` library met `font-family: 'Gentium Book Basic', Georgia, serif` (denbei's stack) — wordt overschreven zodra admin fontyourface-mapping activeert.
- Shindo's eigen theme-settings dupliceren geen font-picker; help-tekst wijst naar fontyourface UI.

## 7. Layout-opties

Settings op `shindo.settings`:
- `container_width` — enum `narrow` (960) / `default` (1200) / `wide` (1400)
- `sidebar_position` — enum `left` / `right` / `none`
- `show_hero` — boolean

Body-classes gezet in `shindo_preprocess_html()`: `shindo--container-<x>`, `shindo--sidebar-<x>`, `shindo--hero-<on|off>`. SCSS schakelt op klasse.

## 8. Templates — prioriteit

**Fase A (blokkerend)**:
1. `layout/page.html.twig` — 10 regions + `{{ tabs }}` variabele.
2. `layout/html.html.twig` — inline color-style attribuut.
3. `content/node--object.html.twig` — **Schema.org Product/Offer microdata volledig behouden**; alle velden: `body`, `field_material`, `field_media_image`, `field_price`, `field_type`, `field_weight`, `field_year`, `content.ask`.
4. `content/node--object--teaser.html.twig`
5. `content/node--object--hero-teaser.html.twig` (plan miste dit — nodig voor frontpage-hero).
6. `navigation/menu-local-task.html.twig` (met verplaatste `.button`-classes).
7. `layout/region.html.twig` (of per-region varianten).

**Fase B (styling-parity)**: 10 view-templates, 17 field-templates, 3 dataset, 2 misc, 2 user — porten alleen waar Dopetrope-look afwijkt van core-output.

**Fase C (defer, Gin dekt admin)**: 16 form-templates, 10 content-edit — alleen als denbei edit-UX gemist wordt.

## 9. JS

`js/scripts.js` — `denbeiFirstParagraph` behavior vervangen door pure CSS (`.field--name-body p:first-of-type`). Geen JS-library nodig in shindo, tenzij later Dopetrope-interacties (nav, slideshow) JS vragen — dan Drupal.behaviors, geen jQuery.

## 10. `doesdesign_tools` interactie

Blocks `social_block`, `copyright_block`, `footer_block` blijven werken (plugin-discovery is theme-agnostisch). Shindo levert overrides in `templates/block/`: `block--social-block.html.twig`, `block--copyright-block.html.twig`, `block--footer-block.html.twig`. Doesdesign_tools libraries niet global stripped — per library beslissen `libraries-override` in `shindo.info.yml` of niet.

## 11. Bestaande custom code

- **`web/themes/custom/massively/`** — verwijderen in dezelfde migratie (aanname; markeren voor bevestiging).
- **`doesdesign_local_tasks`** module — checken op conflict met shindo's menu-local-task styling; libraries-override waar nodig.
- **denbei's 5 screenshot-PNG's + `.playwright-cli/` map** — niet mee overnemen; toevoegen aan `.gitignore`.

## 12. Build & tooling

- DDEV `nodejs_version` upgraden van 14 → 20 LTS. `ddev restart` na wijziging.
- Build via plain `sass` (npm), zoals denbei: `npm run build` en `npm run watch`.
- Compiled CSS **wél committen** (denbei-parity, geen build-step op productie).
- `.gitignore` in theme: `node_modules/`, `.sass-cache/`, `*.css.map`, `.playwright-cli/`, `screenshot-*.png` (behalve `screenshot.png`).

## 13. Migratiestappen

1. **Scaffold** `web/themes/custom/shindo/` — info.yml (10 regions), breakpoints.yml, libraries.yml, theme-settings.php, config/schema/, config/install/.
2. **`shindo.theme`** — 4 preprocess-hooks + `_shindo_hex_to_hsl()`.
3. **SCSS-port** — kopieer `screen.scss`; **refactor hardgecodeerde ID-selectors** (`#block-denbei-*`, `#block-dd_tools-*`) naar class-selectors. Node 20 in DDEV. Compile → commit `css/screen.css`.
4. **Templates Fase A** — porten en renamen.
5. **fontyourface installeren** — `composer require`, enable, admin mapping.
6. **Block-config-migratie** — kopieer 11 YAML's uit `config/sync/` per §2; `drush theme:enable shindo`; `drush cim`; verifieer in UI.
7. **QA** (§14) → fix; **switch default**: `drush config:set system.theme default shindo -y`; `drush cex`.
8. **Cleanup** (aparte commit): `drush theme:uninstall denbei`, `rm -rf web/themes/custom/denbei/` en `web/themes/custom/massively/`.

## 14. Verificatie / test plan

Handmatige QA op DDEV per viewport (mobile 375×667, tablet 768×1024, desktop 1440×900, wide 1600+):

- [ ] `/admin/appearance` toont shindo; enabled zonder errors.
- [ ] `/admin/appearance/settings/shindo` toont color-pickers, layout-radios, hero-toggle; waarden persisteren via `#config_target`.
- [ ] Color-swap propageert live in `<html style>` en reflecteert in buttons, links, headings.
- [ ] fontyourface Google Font mapping op `body` en `h1..h6` rendert in DevTools computed style.
- [ ] `container_width` en `sidebar_position` schakelen visueel; `show_hero` toggle werkt.
- [ ] Content types: article + object + page — full view en edit form.
- [ ] Object view-modes: default, alt_teaser, picture, teaser, thumb — allen renderen zonder layout-breuk.
- [ ] Views: `objects`, `object_types`, `news`, `object`, `archive`, `content_recent`, `frontpage`, `glossary` — pages en blocks.
- [ ] Alle 11 shindo-blocks staan in de verwachte regions.
- [ ] `doesdesign_tools` social/copyright/footer blocks visueel-consistent met Dopetrope.
- [ ] gtranslate-block werkt (of vervangen — zie open vragen).
- [ ] Colorbox lightbox opent/sluit; focal_point-crop toont juiste focus in teasers.
- [ ] Contact-webform submit-flow werkt en matcht nieuwe stijl.
- [ ] Schema.org microdata blijft in `node--object` (check met Rich Results Test tool).
- [ ] `drush cex -y` toont alleen bedoelde config-diffs.
- [ ] Fallback: `drush theme:enable denbei` + `drush config:set system.theme default denbei` herstelt site (voor rollback).
- [ ] Site draait ook zonder Node op productie (geen build-step nodig).

## 15. Aannames (nog te overrulen door user)

1. **10 regions** i.p.v. alpaca's 13 (§1).
2. **gtranslate behouden** — geen swap naar core language switcher.
3. **massively-thema** in dezelfde migratie verwijderen.
4. **Alle 4 preprocess-hooks** porten (§4).
5. **Default kleuren** worden uit denbei's `screen.css` gescraped; geen visuele identiteitswissel.

## 16. Beads-decompositie

Eén epic + fase-issues (`bd create`):
- `regions-and-info`
- `block-config-migration`
- `preprocess-hooks-port`
- `theme-settings-color-pipeline`
- `templates-fase-A`
- `templates-fase-B`
- `doesdesign-overrides`
- `fontyourface-integration`
- `qa-viewports`
- `remove-denbei-and-massively`

Per issue: `bd update --claim` → werk → `bd close`. `bd remember` voor region-mapping, color-defaults, tooling-versies. Session-end: `git pull --rebase && git push`.

## Kritieke bestanden

- `/home/user/repo/web/themes/custom/denbei/denbei.theme` — bron voor 3 preprocess-hooks.
- `/home/user/repo/web/themes/custom/denbei/denbei.info.yml` — regions vergelijking.
- `/home/user/repo/web/themes/custom/denbei/templates/content/node--object.html.twig` — Schema.org microdata behouden.
- `/home/user/repo/web/themes/custom/denbei/templates/content/node--object--hero-teaser.html.twig` — plan miste dit.
- `/home/user/repo/web/themes/custom/denbei/scss/screen.scss` — refactor-target voor ID-selectors.
- `/home/user/repo/config/sync/block.block.denbei_*.yml` — 11 bestanden voor block-migratie.
- `/home/user/repo/.ddev/config.yaml` — Node 14 → 20 upgrade.
- `/home/user/repo/composer.json` — fontyourface toevoegen.
- Olivero core (referentie): `core/themes/olivero/olivero.theme` — hex→HSL helper source.