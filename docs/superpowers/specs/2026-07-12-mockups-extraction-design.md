# Device Mockups for Elementor — extraction design

Date: 2026-07-12
Status: approved by Artem (brainstorm session), pending spec review

## Context

Extract `arts-store-mockups` from the ArtsStore monorepo
(`/Users/art/Projects/ArtsStore/packages/arts-store-mockups`) into this standalone wp.org
plugin. The donor stays in the shop untouched; this repo lives its own life. The build/meta/CI
chassis is already scaffolded (see CLAUDE.md) — `arts/base` foundation, Strauss prefixing,
esbuild/sass pipeline, stamped meta, Plugin Check CI. Waiting stubs: `src/php/Base/*`,
`src/ts/index.ts`, `src/styles/index.sass`, entry-file boot comment.

Donor research findings that shaped this design:

- `arts/store-shared` is required in the donor's composer.json but has **zero usages** — dead
  manifest entry.
- `arts/store-ui` has exactly **one** usage: `arts_store_icon()` in `templates/Mockup/_screen.php`,
  on an overlay code path no widget control can reach (only manual shop callers set
  `overlay_icon`/`overlay_label`).
- `Services/Mockups.php` + `Plugin::get_mockups()` + the `arts-store/repeater/product_mockups/rows`
  filter are ACF shop-repeater machinery; nothing in the widget render path uses them, and the
  service fatals without ACF active.
- There are **no image/SVG asset files** — all device chrome is pure CSS (container-query `cqw`
  geometry). No licensing concerns.
- No license checks, no phone-home. Only outbound requests: YouTube `iframe_api` / Vimeo
  `player.js` injection, gated on the user picking that video type, preferring Elementor's own
  API loader when available.
- Frontend is DOM/data-attribute-driven — no PHP→JS settings bridge.

## Milestones

Both phases complete before the first public release (names freeze at release).

- **Phase 1 — parity extraction.** The donor widget, renamed, running standalone: four skins
  (bare/laptop/tablet/browser), image/video/gallery screens, identical control surface and
  frontend behavior.
- **Phase 2 — additions.** New `phone` skin and a `1:1` aspect-ratio option. Kept out of
  phase 1 so parity verification stays a clean side-by-side diff against the donor.

## Decisions

1. **Full rename, including the DOM surface** (widget slug, handles, JS global, CSS classes,
   data attributes, CSS custom properties). Rationale: names freeze once released; guarantees
   zero conflict (double JS init, CSS variable clashes) if the donor and this plugin ever share
   a site. Cost accepted: donor markup diffs no longer line up 1:1.
2. **Approach: port-and-adapt in dependency order** (templates → skins → widget → managers →
   TS/SASS), renaming and stripping shop code as each file lands. No dead code is ever
   committed; no intermediate state requires ACF.
3. **`arts/get-template-part` gets published to Packagist** (by Artem, prerequisite for
   phase 1), then joins `require` + the Strauss `packages` list next to `arts/utilities`.
   Evaluated `gamajo/template-loader` as the established alternative — rejected: class-based,
   frozen at v1.3.1, pre-WP-5.5 data passing; ours is 51 lines with clean scoped `$args`.
   The global stays `arts_get_template_part()` with its `function_exists` guard
   (shared-library pattern; Strauss copies the file, call sites unchanged).
4. **Elementor category: slug `arts-widgets`, label "Arts".** Future Arts standalone plugins
   reuse the same slug so widgets pool under one panel heading. Not persisted in page data —
   changeable later if needed.
5. **Theme override subdir: `device-mockups`** (donor used `arts`). A theme override written
   for the donor must not silently hijack this plugin's templates — the markup differs after
   the rename. Override path: `{theme}/device-mockups/Mockup/{Skin}.php`.
6. **Phone skin (phase 2): skin ID `phone`**, editor label "Phone", island-pill chrome
   (floating pill cutout top of screen, brand-neutral), **portrait only**. Landscape can be
   added later without breaking anything.
7. **`1:1` aspect ratio (phase 2):** one new option in the existing aspect-ratio SELECT
   (currently default/3:2/4:3/16:9/2:3/9:16). Needed in practice for the browser mockup.
8. **No unit tests this milestone.** Parity extraction doesn't change logic; static gates +
   manual parity pass cover it. If coverage is wanted later, the seam is
   `BaseSkin::build_args()`.
9. **The three DOM attributes outside the donor's `data-arts-mockup-*` family join the rename**
   (settled during implementation planning): `data-scroll-on-hover`, `data-gallery-active`,
   `data-gallery-index` → `data-arts-device-mockup-scroll-on-hover` / `-gallery-active` /
   `-gallery-index`. Verified parity-safe either way; renamed for full-DOM-surface consistency.
10. **Widget title: "Device Mockup"** (donor: "Arts Store Mockup") — the "Arts" category label
    already carries attribution.
11. **Browser skin `url_text` default: `example.com`** (donor: `artemsemkin.com`) — brand-neutral
    default for the public plugin.

## Scope — phase 1

### Ports over (renamed as it lands)

| Donor | This repo |
| --- | --- |
| `src/php/Plugin.php` (manager wiring, hooks, constants) | `src/php/Plugin.php` — minus the repeater filter and `get_mockups()` facade |
| `Managers/Elementor.php` | same path — category slug/label updated |
| `Managers/Assets.php` | same path — hardcoded `VERSION` const → stamped `ARTS_DEVICE_MOCKUPS_PLUGIN_VERSION` |
| `Elementor/MockupWidget.php` (~856 lines of controls) | same path — slug/text-domain/prefix renames only |
| `Elementor/Skins/{BaseSkin,Skin_Bare,Skin_Laptop,Skin_Tablet,Skin_Browser}.php` | same paths — incl. the documented `set_parent()` gotcha for Tablet/Browser |
| `templates/Mockup/{Bare,Laptop,Tablet,Browser,_screen}.php` | same paths — `_screen.php` minus the overlay-icon block |
| `Compat/WPML.php` | same path — widget slug updated in the translate config |
| `src/ts/index.ts` + `core/**` (manager, handler, instance, rotator, visibility, video players, constants, interfaces, types, `global.d.ts`) | `src/ts/` — renames concentrated in `SELECTORS`/`DATA_ATTRS` constants |
| `src/styles/{index,_mockup,_gallery,_video}.sass` | `src/styles/` — BEM block + custom props renamed; overlay CSS dropped |

### Never comes over

- `Services/Mockups.php`, `Plugin::get_mockups()`, the `arts-store/repeater/product_mockups/rows`
  filter registration (ACF shop seam).
- `arts-store-render-mockup.php` globals (`arts_store_render_mockup()` /
  `arts_store_get_mockup()`) and the composer `autoload.files` entry that loaded them —
  manual-caller API with no standalone consumer.
- The overlay-icon block in `_screen.php` and its `.arts-mockup__overlay*` CSS — only reachable
  via those dropped globals.
- Composer requires: `arts/store-shared` (zero usages), `arts/store-ui` (only the overlay call).

### Dependencies

- `arts/base` ^1.1 — already wired, Strauss-prefixed.
- `arts/utilities` — joins `require` + Strauss `packages` (Packagist availability verified
  2026-07-12). Call sites in `src/php` rewritten to the prefixed namespace by Strauss
  `update_call_sites`.
- `arts/get-template-part` — **publish to Packagist first** (not there as of 2026-07-12; 404),
  then joins `require` + Strauss `packages`.
- `@artemsemkin/elementor-types` — already in this repo's devDeps.
- Elementor free ≥3.5. No runtime guard: entry points are `elementor/*` hooks, absence is inert.

## Rename map

| Surface | Donor | This plugin |
| --- | --- | --- |
| PHP namespace | `Arts\Store\Mockups` | `Arts\DeviceMockups` |
| Constants | `ARTS_MOCKUP_TEMPLATES_DIR`, `ARTS_MOCKUP_ASSETS_URL` | `ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR`, `ARTS_DEVICE_MOCKUPS_ASSETS_URL` |
| Widget slug (persisted in `_elementor_data`) | `arts-store-mockup` | `arts-device-mockup` |
| Skin IDs (persisted as `_skin`) | `bare` / `laptop` / `tablet` / `browser` | unchanged (already generic) |
| Elementor category | `arts-store` | `arts-widgets` (label "Arts") |
| Asset handles | `arts-store-mockups` | `arts-device-mockups` |
| JS global | `window.artsStoreMockups` | `window.artsDeviceMockups` |
| Handler hooks | `frontend/element_ready/arts-store-mockup.{skin}` | `frontend/element_ready/arts-device-mockup.{skin}` |
| CSS block | `.arts-mockup` | `.arts-device-mockup` |
| Data attributes | `data-arts-mockup-*` | `data-arts-device-mockup-*` |
| Behavior data attrs | `data-scroll-on-hover` / `data-gallery-active` / `data-gallery-index` | `data-arts-device-mockup-scroll-on-hover` / `-gallery-active` / `-gallery-index` |
| Widget title | `Arts Store Mockup` | `Device Mockup` |
| Browser `url_text` default | `artemsemkin.com` | `example.com` |
| CSS custom properties | `--arts-mockup-*` | `--arts-device-mockup-*` |
| Text domain | donor's | `device-mockups-for-elementor` in every i18n call |
| Theme override subdir | `{theme}/arts/` | `{theme}/device-mockups/` |

## Architecture (phase 1)

### PHP

```
src/php/
  Plugin.php                      # concrete Plugin extends Base\Plugin; booted from entry file
  Base/…                          # existing scaffold (thin binds to prefixed arts/base)
  Managers/Elementor.php          # category + widget registration
  Managers/Assets.php             # register-only handles, stamped version
  Elementor/MockupWidget.php
  Elementor/Skins/                # BaseSkin + Skin_Bare/Laptop/Tablet/Browser
  templates/Mockup/               # Bare, Laptop, Tablet, Browser, _screen
  Compat/WPML.php
```

Hooks (donor parity minus the dropped filter):

- `wp_enqueue_scripts` + `elementor/frontend/before_enqueue_scripts` → `Assets::register()`
  (register only; enqueue happens via widget script/style deps)
- `elementor/elements/categories_registered` → category
- `elementor/widgets/register` → widget
- `wpml_elementor_widgets_to_translate` → WPML compat

Render pipeline: widget settings → skin `render()` → `BaseSkin::build_args()` (single
settings→args mapping point) → `arts_get_template_part('Mockup/{Skin}', $args,
ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR, 'device-mockups')` → pure-`$args` partial → `_screen.php`
for the media slot. Templates early-return when no media is present (graceful underflow for
dynamic-tag-bound values).

### Frontend

`index.ts`: idempotent `MockupManager.init(document)` on DOM-ready; `elementor/frontend/init`
→ handler attach per skin; refresh API on `window.artsDeviceMockups`. `MockupHandler` extends
`elementorModules.frontend.handlers.Base` when present, plain object off-Elementor. Everything
is driven by data attributes on the root `<figure>` — PHP and TS are independently verifiable.
Build: chassis esbuild (IIFE, es2018) + sass → `src/php/libraries/…`; `Assets.php` registers
handles against those outputs.

### Error handling

No new error paths. Parity behaviors: template early-returns on missing media; off-Elementor
inertness (hooks never fire); handler fallback off-Elementor; video SDK injection only on
demand with the Elementor loader preferred.

## Phase 2 — phone skin + 1:1 ratio

- `Elementor/Skins/Skin_Phone.php` extending `BaseSkin`, skin ID `phone`, label "Phone".
  No extra control section of its own in v1 (portrait only, unlike Tablet/Browser which
  register extras via `_register_controls_actions()`) — the simple 4-line `render()` pattern.
- `templates/Mockup/Phone.php` including `_screen.php` — image/video/gallery/scroll behavior
  comes free.
- CSS-only chrome in `_mockup.sass`: rounded bezel + island-pill cutout, `cqw` container-query
  geometry consistent with the other frames. Brand-neutral (no trademarked device look).
- Device-color controls: extend the existing per-skin `_skin` conditions so phone shows the
  pickers whose variables its CSS uses — body (frame), screen, border, and camera (the island
  pill is colored by the camera variable).
- JS: add `phone` to the skins constant so the handler attaches for
  `frontend/element_ready/arts-device-mockup.phone`.
- Aspect-ratio SELECT gains `1:1` (all skins, responsive, same control).

## Verification

### Static gates (all must pass locally; CI runs the same)

`pnpm lint` · `pnpm typecheck` · `pnpm phpstan` (level max) · `pnpm build` · ZIP-size tripwire
(0.5 MB — a tooling-leak guard, raise freely for legit assets) · wp.org Plugin Check action
against built `dist/`.

### Manual parity pass (Local site `fluid-ds`)

One test page exercising the matrix:

- All skins × media modes: image, self-hosted video, YouTube, Vimeo (server-rendered poster →
  JS iframe swap), gallery (hover + auto triggers, interval, loop).
- Skin extras: Browser caption-in-url-bar + custom URL text + link arrow; Tablet orientation;
  Bare corner radius; per-skin device colors.
- Behaviors: play-on-hover gating, scroll-on-hover, visibility-gated playback
  (0.25-threshold IntersectionObserver).
- Editor lifecycle: handlers re-attach on control changes in the Elementor editor preview.
- Side-by-side against donor rendering in the shop (structure matches 1:1; class names differ
  by the rename).

Gotcha: seeding `_elementor_data` raw via wp-cli leaves `_elementor_element_cache` stale —
delete that meta after raw writes.

### Success criteria

1. Full manual matrix passes on the dev site; phase 2 additions render correctly.
2. CI green (both workflows).
3. Built ZIP contains only runtime code: entry file, `src/php` (compiled libraries, templates),
   vendor autoloader, `vendor-prefixed/`.
4. Donor package untouched.

## Out of scope

- wp.org submission collateral (readme content, banners, screenshots) and the submission itself.
- Back-porting the `1:1` ratio (or anything else) to the donor.
- Feature rescoping/trimming — explicitly deferred until the plugin runs standalone.
- Landscape phone orientation, overlay-icon feature, manual render API — add later only if
  actually needed.
