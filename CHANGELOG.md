# Changelog

All notable changes to Beer Journal will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Admin “At a glance” stats (cached counts + last RSS sync time); `bj_get_cached_data()` / `bj_invalidate_stats_cache()`
- Optional **email notifications** (sync success, RSS errors) with dedicated address; wired for cron, manual sync, and AJAX
- **Archive layout** option: grid (cards) or **table** on archive and taxonomy templates; shared `archive-loop` partial; `body_class` layout hint
- **Placeholder image** (attachment ID) when sideload fails
- Best-effort **DB index** `bj_checkin_meta` on `wp_postmeta` (activation + init); state stored in `bj_db_index_checkin_v1`

### Changed
- RSS sync records `bj_last_rss_sync_at` on every successful run; import invalidates stats transient
- `vendor/` (runtime Composer packages) and `composer.lock` are versioned so Git / Git Updater installs work without `composer install` on the server
- Default RSS feed URL is the maintainer’s Untappd RSS (example); override with `BJ_RSS_FEED_URL` in `wp-config.php` or the `bj_default_rss_feed_url` filter

### Planned for future versions
- Gutenberg blocks (checkins-list, checkin-card, stats-dashboard)
- Advanced statistics with charts
- WordPress dashboard widget
- Shortcodes for legacy support
- CSV/JSON export functionality
- AJAX search and filters
- REST API endpoints
- Webhooks for real-time sync
- Interactive map view (Google Maps)
- Wishlist feature
- Private notes on check-ins
- Cellar management
- Multi-user support
- Team profile aggregation
- Export to other platforms
- Import from BeerAdvocate, RateBeer
- PWA support (offline mode)

## [1.0.0] - 2026-04-20

### Added
- Custom Post Type `beer_checkin` with taxonomies `beer_style`, `brewery`, and `venue`
- RSS synchronization with adaptive WP-Cron scheduling (`sixhourly` / `daily` / `weekly`)
- HTML scraping for check-in metadata (Symfony DomCrawler, rate limiting, retries)
- Importer with deduplication by `_bj_checkin_id`, draft posts when rating is missing, `_bj_exclude_sync` respect
- Image sideload to Media Library with hash-based deduplication
- Historical import: profile discovery + batched AJAX import with checkpoint option
- Admin UI under **Beer Journal** (5 tabs: Sync, Import, General, Rating, Advanced) with logs viewer and manual sync
- Frontend templates (archive, single, taxonomies), template tags, optional JSON-LD and microformats
- File logging under uploads, PHPUnit smoke tests for helpers, `phpunit.xml.dist`
- `uninstall.php` to remove plugin options

### Notes
- Runtime dependencies ship in `vendor/`; Untappd HTML structure may change and affect scraping.

## [0.1.0] - 2026-04-19

### Added
- Minimal installable plugin bootstrap (`beer-journal.php`): headers, constants, optional Composer autoload, text domain hook
- Git default branch `main` on GitHub; `dev` for day-to-day pushes and Git Updater on the dev site
- Branch protection on `main`; documentation validation workflow runs on `main` and `dev` when `docs/` or the workflow file changes

### Changed
- Changelog corrected: items previously listed under a shipped `1.0.0` were specification / documentation targets only; they are listed again under [Unreleased] until implemented in code

### Removed
- Redundant remote branch `docs` (history absorbed into `main` / `dev`)
