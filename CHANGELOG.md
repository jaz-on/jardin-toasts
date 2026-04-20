# Changelog

All notable changes to Beer Journal will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
- Requires `composer install` (see `vendor/`); Untappd HTML structure may change and affect scraping.

## [0.1.0] - 2026-04-19

### Added
- Minimal installable plugin bootstrap (`beer-journal.php`): headers, constants, optional Composer autoload, text domain hook
- Git default branch `main` on GitHub; `dev` for day-to-day pushes and Git Updater on the dev site
- Branch protection on `main`; documentation validation workflow runs on `main` and `dev` when `docs/` or the workflow file changes

### Changed
- Changelog corrected: items previously listed under a shipped `1.0.0` were specification / documentation targets only; they are listed again under [Unreleased] until implemented in code

### Removed
- Redundant remote branch `docs` (history absorbed into `main` / `dev`)
