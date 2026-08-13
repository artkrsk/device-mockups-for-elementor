# Arts Device Mockups for Elementor

[![Tests](https://img.shields.io/github/actions/workflow/status/artkrsk/device-mockups-for-elementor/test.yml?style=flat-square&logo=githubactions&logoColor=white&label=tests)](https://github.com/artkrsk/device-mockups-for-elementor/actions/workflows/test.yml)
[![WordPress](https://img.shields.io/badge/WordPress-6.0+-21759b?style=flat-square&logo=wordpress&logoColor=white)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-8.0+-777bb4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-GPLv3-blue?style=flat-square)](LICENSE)
<!-- Once live on wp.org, add the self-updating listing badges (version / installs / rating). -->

CSS-drawn device mockups (phones, laptops, browser frames) for Elementor — galleries, hosted and embedded video inside the frames. Part of the free plugin collection at [artemsemkin.com/plugins/device-mockups-for-elementor/](https://artemsemkin.com/plugins/device-mockups-for-elementor/).

In development — not yet submitted to WordPress.org.

## Development

```bash
pnpm install && composer install
cp .env.example .env   # set DEV_TARGET to your Local site's plugin dir
```

| Command | What |
|---|---|
| `pnpm dev:plugin` | watch-compile + mirror the plugin to `DEV_TARGET` |
| `pnpm build` | release build into `dist/` |
| `pnpm test` / `pnpm test:coverage` | Vitest |
| `pnpm release <patch\|minor\|major>` | bump, stamp, validate changelog, commit, tag |

Everything else (lint, typecheck, phpstan, phpcs, knip, fallow) runs via `pnpm exec` — see the [tooling docs](https://github.com/artkrsk/wp-plugin-tooling). Dev fixtures sync with `node dev/sync-fixtures.js` (needs `DEV_TARGET`).

## License

GPL-3.0-or-later.
