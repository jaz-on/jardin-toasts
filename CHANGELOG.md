# Changelog

All notable changes to Beer Journal will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-04-19

### Added
- Minimal installable plugin bootstrap (`beer-journal.php`): headers, constants, optional Composer autoload, text domain hook
- Git default branch `main` on GitHub; `dev` for day-to-day pushes and Git Updater on the dev site
- Branch protection on `main`; documentation validation workflow runs on `main` and `dev` when `docs/` or the workflow file changes

### Changed
- Changelog corrected: items previously listed under a shipped `1.0.0` were specification / documentation targets only; they are listed again under [Unreleased] until implemented in code

### Removed
- Redundant remote branch `docs` (history absorbed into `main` / `dev`)

## [Unreleased]

### Target MVP (1.0.0 scope — specified in `docs/`, not yet implemented in code)
- Custom Post Type `beer_checkin` for storing Untappd check-ins
- Taxonomies: `beer_style` (hierarchical), `brewery` (non-hierarchical), `venue` (non-hierarchical)
- RSS synchronization with adaptive polling (6h/daily/weekly based on activity)
- Historical import crawler with manual and background modes
- Rating system with customizable mapping rules and labels
- Image import to WordPress Media Library with optimization
- Complete metadata extraction from Untappd check-in pages
- Admin settings page with 5 tabs (Synchronization, Import, General, Rating, Advanced)
- Template system with overridable templates (archive, single, taxonomies)
- Template tags for displaying check-in data
- Hooks and filters for customization
- Logging system with detailed error tracking
- Retry logic for failed imports (automatic and manual)
- Deduplication by Untappd check-in ID
- Auto-creation of taxonomy terms with admin notifications
- Polling optimization (GUID comparison before scraping)
- WordPress 6.0+ compatibility, PHP 8.2+, Symfony DomCrawler, Guzzle, WPCS, i18n, security practices as documented

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

