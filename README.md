# jardin-toasts

Jardin · Untappd : check-ins bière (CPT), synchro et blocs. RSS + crawl HTML optionnel vers **`beer_checkin`**, taxonomies (styles, brasseries, lieux), médias, réglages admin, JSON-LD / microformats, gabarits surchargeables par le thème.

## Requirements

- WordPress **6.0+** (extension déclarée **testée jusqu’à 7.0** ; valider sur votre bêta)
- PHP **8.2+** (valider localement sous **8.4**) ; extensions : curl ou `allow_url_fopen`, dom, json, mbstring
- MySQL **5.7+** / MariaDB **10.3+**
- Recommended: [Action Scheduler](https://actionscheduler.org/) for reliable scheduled sync; otherwise WP-Cron

## Install

1. Copy to `wp-content/plugins/jardin-toasts` and activate.
2. **Jardin Toasts** (admin) → **Synchronization**: set your Untappd RSS URL (or `JB_RSS_FEED_URL` in `wp-config.php`).
3. Optional: **Historical import** for backfill (batched crawl; respect rate limits).

Runtime **Composer `vendor/`** is committed so Git / [Git Updater](https://git-updater.com/) installs work without `composer install` on the server. For local **PHPUnit / PHPCS / PHPStan**, run `composer install` in the plugin directory.

## What it does

- RSS for recent items; richer fields via **scraping** (no official Untappd API) — fragile if Untappd changes markup; use only data you may republish. See `docs/legal/scraping-notice.md`.
- **Branches:** `main` for releases/reference; **`dev`** for integration and Git Updater on staging.

Further detail: [CHANGELOG.md](CHANGELOG.md) and `/docs/` (architecture, schema, hooks, backlog in `docs/todolist-future/TODOLIST.md`).

## Jardin stack

| Repository | Role |
|------------|------|
| [jardin-theme](https://github.com/jaz-on/jardin-theme) | FSE theme, templates, patterns |
| [jardin-projects](https://github.com/jaz-on/jardin-projects) | `project` CPT, GitHub changelog sync, project blocks |
| [jardin-events](https://github.com/jaz-on/jardin-events) | `event` CPT, archive, Query Loop helpers, event blocks |
| [jardin-updates](https://github.com/jaz-on/jardin-updates) | `now` CPT, hub / permaliens, migrations |
| [jardin-scrobbles](https://github.com/jaz-on/jardin-scrobbles) | Last.fm → `listen` CPT, `/listens/`, player blocks |
| **jardin-toasts** (this repo) | Untappd RSS + import → `beer_checkin` CPT |
| [jardin-bookmarks](https://github.com/jaz-on/jardin-bookmarks) | Feedbin → `favorite` / `blogroll` CPTs, blogroll block |


## Development

```bash
composer install   # dev tools + refresh vendor if needed
```

## Release Checklist (branch `dev`)

Before each push used by Git Updater on `dev.jasonrouet.com`, run:

```bash
composer run release:dev
```

Then verify and publish:

1. `rg "myclabs/deep-copy|phpunit|phpstan" vendor/composer/autoload_files.php` returns no match.
2. Commit updated runtime Composer files (`vendor/composer/*` + tracked runtime `vendor/` changes).
3. Push branch `dev`, then update plugin with Git Updater on staging.

Optional but recommended (one-time per clone): install the local `pre-push` hook that runs these checks automatically and blocks invalid pushes.

```bash
composer run hooks:install
```

## License

GPL-2.0-or-later — see [LICENSE](LICENSE). Sponsorship: [.github/FUNDING.yml](.github/FUNDING.yml).