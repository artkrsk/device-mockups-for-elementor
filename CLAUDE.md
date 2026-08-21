# Arts Device Mockups for Elementor

Standalone wp.org plugin: device mockups (browser, laptop, tablet, phone, bare frames) as a native
Elementor **widget** with skins, rendering image/video/gallery screens. Extraction of
`arts-store-mockups` (ArtsStore monorepo, `/Users/art/Projects/ArtsStore/packages/arts-store-mockups`)
— the donor stays in the shop; this repo lives its own life. The build/tooling chassis is the
vendored reference from `repeater-tags-for-elementor-acf` (see that repo's CLAUDE.md for the
full build/meta/CI documentation — identical system, different slug). Project state that
changes over time lives in the auto-memory, not in this file.

## Stack & requirements

PHP 8.0+ · WP 6.0+ · Elementor free ≥3.5. Plain `arts/base` foundation with hand-rolled
Elementor registration (deliberate: `arts/elementor-extension` was evaluated and rejected —
its extras are theme-ecosystem features, and the donor package itself hand-rolls; keep the
donor's registration pattern). `Requires Plugins: elementor` header (hand-maintained; the meta
stamper skips this field by name); no runtime Elementor guard — everything but asset
*registration* hangs off `elementor/*` hooks, and registering handles nothing enqueues is inert.

## Commands

`package.json` carries only the human-facing scripts; everything else runs through `pnpm exec`
or the repo-local binaries. There is no `dev`, `sync`, `typecheck`, or `lint` script; `phpstan`
exists only as a composer script.

```bash
pnpm dev:plugin   # stamp meta, then watch: esbuild TS + sass → src/php/libraries/…, mirror → Local site
pnpm build        # production ZIP in dist/
pnpm test         # vitest (passWithNoTests until the first test lands)
pnpm release      # version bump + changelog + tag

pnpm exec tsc --noEmit                        # typecheck — esbuild does NOT type-check
pnpm exec biome check                         # src/ts, dev/, project.config.js (config in biome.json)
pnpm exec stylelint 'src/styles/**/*.scss'
vendor/bin/phpstan analyse --memory-limit=1G  # level max (composer install first)
vendor/bin/phpcs                              # ArtsFramework ruleset over src/php
node dev/sync-fixtures.js                     # push dev/mu-plugins fixtures to the Local site
```

`lefthook` wires the fast ones into pre-commit (biome / stylelint / phpcbf --write, then tsc)
and the slow ones into pre-push (vitest, phpstan, phpcs). Hooks are advisory — `LEFTHOOK=0`
skips them; CI is the authoritative gate.

Fresh clone: `composer install`, create `.env` with `DEV_TARGET=<Local site plugin dir>`, then
`pnpm dev:plugin`. Strauss copies the `arts/*` packages into `vendor-prefixed/` under
`ArtsDeviceMockups\` via the composer post-install/post-update hooks — except
`arts/get-template-part`, which is `exclude_from_prefix`'d because it ships a global function.
The main plugin file `require_once`s that file explicitly for a separate reason: the production
`vendor/` autoloader carries no package `files` entries, so nothing else would load the global.
ONE config file (`project.config.js`, unknown keys are hard errors); machine-specific
`DEV_TARGET` lives in the gitignored `.env`; `pnpm build` needs no `.env` (CI-safe). Production
compiles into a staging dir under `dist/` and never writes the source tree.

Dev site: `/Users/art/Local Sites/fluid-ds/app/public` (shared with other Arts plugins — no
overlap). wp-cli against it (Local ships its own PHP/MySQL/wp-cli per site — source the env
first, one invocation, and stay in the site dir wp-cli lands in):

```bash
source <(grep -E '^[[:space:]]*(export|cd|unset)' "/Users/art/Library/Application Support/Local/ssh-entry/wNlVYAQFQ.sh")
wp plugin list
```

## Research routing — do this BEFORE implementing against a third party

**Never answer third-party questions from training data** — these libraries move; trace the
real source. Route via the Task/Agent tool:

| Layer | Agent |
| --- | --- |
| Elementor PHP (widgets, skins, controls, render pipeline) | `elementor-backend` |
| Elementor editor/frontend JS (handlers, `elementorFrontend`, `$e`, Backbone) | `elementor-frontend` |
| WordPress core (hooks, caps, meta, WP_Query, caching) | `wordpress-internals` |
| Arts Framework lift sources (ArtsBase, ArtsUtilities, ArtsGetTemplatePart, donor mockups package) | `arts-framework` |
| Other third-party plugin internals | `plugin-internals` |

- **context7 MCP** for library/tooling docs (esbuild, sass, TypeScript config, Vimeo/YouTube
  player APIs) — pull current docs, don't recall from memory.
- Built-in web search for anything without an agent or indexed source (e.g. wp.org guideline
  checks).

## Frozen identifiers (public surface — changing any of these breaks saved pages)

- Widget name `arts-device-mockup` · editor category `arts-widgets` · skin ids `bare`,
  `laptop`, `tablet`, `browser`, `phone`.
- One CSS + one JS handle, both `arts-device-mockups`, declared via the widget's
  `get_style_depends()` / `get_script_depends()`.
- PHP ↔ JS contract is data attributes only, never settings: the partials emit
  `data-arts-device-mockup*` and the frontend TS reads nothing else.
- `window.artsDeviceMockups` is the manager surface AJAX consumers call.
- Constants `ARTS_DEVICE_MOCKUPS_PLUGIN_VERSION` and `ARTS_DEVICE_MOCKUPS_TEMPLATES_DIR`.

## Gotchas

- Elementor's V4 "atomic widgets" React editor is experimental — do NOT build against it.
- **Writing `_elementor_data` raw via wp-cli leaves `_elementor_element_cache` stale** — the
  frontend serves old markup until you `delete_post_meta(…, '_elementor_element_cache')`
  (editor saves invalidate it automatically; only raw meta writes hit this).
- **`elementorFrontend.init()` runs AFTER `DOMContentLoaded`** — jQuery 3 dispatches ready
  callbacks through a Deferred (`setTimeout`), and in the editor preview Elementor inits off the
  iframe's `load`. So the document-wide pass in `index.ts` always goes first: `elementorFrontend`
  `.utils` is undefined there (which is why the initial render self-injects the video SDK), and
  the handler's `editMode` opt only lands on markup Elementor re-renders.
- A skin that registers its own controls MUST re-target the shared skin object at the widget
  instance the `after_section_end` action hands it (`set_parent( $widget )`) before opening a
  section. Skipping it re-triggers the `_skin` control injection — "Cannot redeclare control
  _skin". Skins with no extra controls don't need it.

## Conventions

- wp.org discipline: self-contained, GPL-compatible, prefixed. No phone-home and no bundled
  remote assets — the one deliberate exception is the YouTube / Vimeo player SDK, fetched from
  the provider only when a video of that type is configured (Elementor's shared loader is reused
  only for mockups initialized after Elementor's `init()`; the initial render always self-injects).
- Naming family (frozen once released): namespace `Arts\DeviceMockups` · prefixes
  `arts_device_mockups_*` / `ARTS_DEVICE_MOCKUPS_*` · text domain `device-mockups-for-elementor`.
- `composer.json` is the source of truth for plugin meta AND the version — the `version` field
  is stamped into the header, readme Stable tag, PHP define, package.json and banners by
  `arts-wp` (from `@arts/wp-plugin-tooling`, where build/release/changelog mechanics live).
  Edit composer.json, never the stamped fields directly.
- `vendor/` ships autoloader-only in production; packages live prefixed in `vendor-prefixed/`.
- Tests are logic-only — no UI/markup/rendering tests.
- Runtime deps are `arts/base`, `arts/utilities`, `arts/get-template-part`. The donor's
  shop-coupled `arts/store-shared` and `arts/store-ui` deliberately did not come along; if a
  lift from the donor reaches for them, strip or inline the usage instead of adding the dep.
