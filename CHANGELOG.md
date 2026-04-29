# Changelog

All notable changes to Jardin Toasts will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- **Branding & identifiers**: canonical product / repository **`jardin-toasts`** (`jardin-toasts.php`, text domain `jardin-toasts`, Composer `jardin-toasts/jardin-toasts`, GitHub `jaz-on/jardin-toasts`, Gutenberg namespace `jardin-toasts/*`, theme overrides under `jardin-toasts/`, Action Scheduler group `jardin-toasts`, uploads log path `jardin-toasts/logs`, log files `jardin-toasts-*.log`). **Legacy (stable for stored data and developer hooks):** option and meta key prefixes `jb_` / `_jb_`, PHP symbols `JB_*` / `jb_*`, and all `jb_*` action/filter hook names. Existing installs: `JB_Storage_Migration::maybe_migrate()` still handles beer-journal / `bj_*` once; `JB_Storage_Migration::maybe_migrate_product_rename()` rewrites saved block/theme path strings from `jardin-beer` to `jardin-toasts`, clears Action Scheduler jobs in the legacy `jardin-beer` group, then recurring work is scheduled under `jardin-toasts`. **Re-activate** the plugin if WordPress still points at the deleted `beer-journal/beer-journal.php` path.
- **Fallback image** is **on by default** (opt-out); admin copy explains Media Library vs optional `jb_placeholder_attachment_id` filter for external sources (no bundled remote beer-photo API).
- **Advanced → scraping delay / RSS cap**: values are clamped on save (minimum 1); UI clarifies difference from Synchronization and Historical import pacing; new helper `jb_get_scraping_delay_seconds()` centralizes the delay used by scrapes and queue scheduling.

### Fixed
- **Admin menu**: beer style, brewery, and venue taxonomies now use `show_in_menu` with the same top-level slug as the CPT (`edit.php?post_type=beer_checkin`), so **Styles**, **Breweries**, and **Venues** appear as submenus under **Jardin Toasts** (they were previously easy to miss when the CPT lived under a custom parent).
- **Settings persistence**: the Jardin Toasts screen uses one `options.php` group but only one tab’s fields were rendered at a time, so saving (e.g. Import or Advanced) **did not POST** the RSS URL and other fields — WordPress then stored `null` for those options. All tab panels are now output in the same form (inactive tabs use the `hidden` attribute). Internal-only options (`jb_import_checkpoint`, RSS sync cursors, etc.) are **no longer** registered with `register_setting()` so they cannot be wiped by a generic save.
- **Historical import → Discover**: profile discovery no longer fails silently when Untappd returns a tiny or empty HTML body (blocked host, HTTP errors, or markup without links). Returns clear `WP_Error` messages; AJAX success distinguishes “0 new because everything is already imported” vs “nothing discovered”.
- Composer **classmap** referenced Finder duplicate filenames (`class-* 2.php`, `class-public 2.php`) that are not deployed on Linux servers; autoload now targets the canonical files and duplicate copies were removed from the tree.
- **`jardin-toasts.php`** preloads the same four core classes before `vendor/autoload.php` so sites that still ship a stale classmap (or OPcache) do not fatal when `JB_Taxonomies` (etc.) is first used.
- **`jb_parse_username_from_rss_url()`** : regex used `#` as delimiter and unescaped `#` inside the class, which broke `preg_match` (unknown modifier `]`). Switched delimiter to `~`.
- **Admin (Synchronization / Import / Advanced)** : copy now reflects **Action Scheduler** when loaded, and **WP-Cron** only as the fallback; import “background” option label and RSS cap field wording updated accordingly.

### Added
- RSS sync **queue** (`jb_rss_sync_queue`) with per-cron cap (`jb_rss_max_per_run`, Advanced settings) and follow-up hook `jb_rss_queue_tick`; batch meta lookup `jb_get_post_ids_by_checkin_ids()`; manual “Run sync now” uses higher cap via `jb_rss_manual_sync_max_items`
- **Sync health** (Advanced): queue depth, incomplete draft count, scraper markup version; stats strip shows RSS queue and incomplete drafts
- Bulk action **Re-scrape from Untappd** on check-in list (`jb_bulk_rescrape_max_per_request`); helper `jb_rescrape_checkin_post()`
- `JB_Scraper_Config` with `jb_scraper_dom_selectors` filter and `MARKUP_VERSION` for support logs
- PHPStan (`phpstan.neon.dist`, `composer phpstan`) and GitHub Action **PHP Quality** (PHPCS, PHPStan, PHPUnit)
- Admin “At a glance” stats (cached counts + last RSS sync time); `jb_get_cached_data()` / `jb_invalidate_stats_cache()`
- Optional **email notifications** (sync success, RSS errors) with dedicated address; wired for cron, manual sync, and AJAX
- **Archive layout** option: grid (cards) or **table** on archive and taxonomy templates; shared `archive-loop` partial; `body_class` layout hint
- **Placeholder image** (attachment ID) when sideload fails
- Best-effort **DB index** `jb_checkin_meta` on `wp_postmeta` (activation + init); state stored in `jb_db_index_checkin_v1`

### Changed
- **Dependencies**: removed unused Guzzle; HTTP remains WordPress `wp_remote_get`; runtime Composer is Symfony DomCrawler + CSS Selector only
- RSS sync no longer skips work on **GUID-only** match; missing check-ins after a failed import can be detected via batched ID lookup
- Display & content: public archive URL shown under layout; beer photos / venues / social as Yes–No toggles with clearer copy; fallback image uses `jb_use_placeholder_image` + Media Library picker (no attachment ID field); `jb_parse_username_from_rss_url()`, `jb_get_checkin_archive_url()`
- Import tab: username explained (slug only); “Use username from RSS feed” button; batch size & delay as labeled selects; Sync tab links to Import profile
- Ratings tab: editable raw min/max bands, star level per band, and per-star labels (saved to `jb_rating_rules` / `jb_rating_labels` with sanitization); helper `jb_get_rating_labels()`
- Settings screen: tab intros, card-style panels, stats strip, clearer copy; `JB_Settings::get()` for defaults; enqueue uses Dashicons; “General” tab renamed to “Display & content”
- Imported check-in notes: `jb_normalize_imported_post_content()` applies `wpautop` for plain text; single check-in template gets improved `.entry-content` typography
- Default Untappd username for new installs / unset option: `jaz_on` (`jb_get_untappd_username()`)
- RSS sync records `jb_last_rss_sync_at` on every successful run; import invalidates stats transient
- `vendor/` (runtime Composer packages) and `composer.lock` are versioned so Git / Git Updater installs work without `composer install` on the server
- Default RSS feed URL is the maintainer’s Untappd RSS (example); override with `JB_RSS_FEED_URL` in `wp-config.php` or the `jb_default_rss_feed_url` filter

### Planned for future versions

The retained post-MVP backlog (stats, export/import, integrations, admin tax UI, front filters, rating recalculation, badges, Phase 2 docs) lives in a **single document** : [`docs/todolist-future/TODOLIST.md`](docs/todolist-future/TODOLIST.md). Older broad idea lists are no longer maintained here.

## [1.0.0] - 2026-04-20

### Added
- Custom Post Type `beer_checkin` with taxonomies `beer_style`, `brewery`, and `venue`
- RSS synchronization with adaptive WP-Cron scheduling (`sixhourly` / `daily` / `weekly`)
- HTML scraping for check-in metadata (Symfony DomCrawler, rate limiting, retries)
- Importer with deduplication by `_jb_checkin_id`, draft posts when rating is missing, `_jb_exclude_sync` respect
- Image sideload to Media Library with hash-based deduplication
- Historical import: profile discovery + batched AJAX import with checkpoint option
- Admin UI under **Jardin Toasts** (5 tabs: Sync, Import, General, Rating, Advanced) with logs viewer and manual sync
- Frontend templates (archive, single, taxonomies), template tags, optional JSON-LD and microformats
- File logging under uploads, PHPUnit smoke tests for helpers, `phpunit.xml.dist`
- `uninstall.php` to remove plugin options

### Notes
- Runtime dependencies ship in `vendor/`; Untappd HTML structure may change and affect scraping.

## [0.1.0] - 2026-04-19

### Added
- Minimal installable plugin bootstrap (`jardin-toasts.php`): headers, constants, optional Composer autoload, text domain hook
- Git default branch `main` on GitHub; `dev` for day-to-day pushes and Git Updater on the dev site
- Branch protection on `main`; documentation validation workflow runs on `main` and `dev` when `docs/` or the workflow file changes

### Changed
- Changelog corrected: items previously listed under a shipped `1.0.0` were specification / documentation targets only; they are listed again under [Unreleased] until implemented in code

### Removed
- Redundant remote branch `docs` (history absorbed into `main` / `dev`)
