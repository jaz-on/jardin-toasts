# Beer Journal for Untappd

A WordPress plugin to automatically sync and display your Untappd beer check-ins on your WordPress site.

## Development status

**Current release: 1.0.0** — full MVP: CPT `beer_checkin`, taxonomies, RSS sync with adaptive cron, HTML scraping, historical import (discover + batched AJAX), admin settings (5 tabs), logging, frontend templates, JSON-LD / microformats options. See [CHANGELOG.md](CHANGELOG.md) and [DEVELOPMENT.md](DEVELOPMENT.md).

**Dependencies:** run `composer install` in the plugin directory so `vendor/` exists (the plugin uses Symfony DomCrawler and registers autoloaded classes). If you deploy with Git Updater, ensure Composer has been run on the target site or commit `vendor/` for distribution builds.

**Branches:** [`main`](https://github.com/jaz-on/beer-journal) is the default branch (reference + releases). Day-to-day integration and [Git Updater](https://git-updater.com/) on a dev site typically use the **`dev`** branch.

## Description

Beer Journal allows you to automatically sync your Untappd check-ins to your WordPress site, creating a beautiful beer journal with ratings, photos, and detailed information about each beer you've tried.

### Key Features

- **Automatic RSS Sync**: Automatically syncs your latest Untappd check-ins via RSS feed with adaptive polling
- **Historical Import**: Import your entire Untappd history with a manual crawler
- **Rating System**: Customizable rating system with labels and star mapping
- **Image Management**: Automatically imports beer photos to WordPress Media Library
- **Taxonomies**: Auto-creates beer styles, breweries, and venues as taxonomies
- **Theme-Agnostic Templates**: Overridable templates that work with any WordPress theme
- **Gutenberg Blocks**: Display check-ins with customizable blocks (Phase 2)

## Installation

1. Upload the plugin files to `/wp-content/plugins/beer-journal/`
2. Activate the plugin through the 'Plugins' screen
3. Go to **Beer Journal** in the admin menu (or **Settings** from the plugin row) and open the **Synchronization** tab to configure your Untappd RSS feed URL
4. Start syncing your check-ins!

## Requirements

- **WordPress**: 6.0 or higher
- **PHP**: 8.2 or higher
- **MySQL**: 5.7+ / MariaDB 10.3+
- **PHP Extensions**: curl (or allow_url_fopen), dom, json, mbstring

**Scheduled sync** uses WordPress’s pseudo-cron: visits to your site trigger scheduled tasks. On low-traffic sites, sync may run late unless you add a real cron job that hits `wp-cron.php` on a schedule (see WordPress documentation for “Cron Jobs”).

## Configuration

### Initial Setup

1. Get your Untappd RSS feed URL: `https://untappd.com/rss/user/YOUR_USERNAME`
2. Go to **Beer Journal** in the admin menu and open the **Synchronization** tab
3. Enter your RSS feed URL
4. Choose your sync frequency (adaptive polling recommended)
5. Click "Save Settings"

### Import Historical Check-ins

1. Go to **Beer Journal** → **Historical import** tab
2. Enter your Untappd profile URL
3. Configure batch size and delays
4. Click "Start Import"

## Documentation

Complete documentation is available in the `/docs/` directory:

- [Architecture Overview](docs/architecture/overview.md)
- [Database Schema](docs/db/schema.md)
- [Frontend Templates](docs/frontend/templates.md)
- [User Flows](docs/user-flows/installation.md)
- [WordPress Integration](docs/wordpress/hooks.md)
- [Development Guide](docs/development/contributing.md)

## Project Structure

```
beer-journal/
├── includes/          # Core plugin classes
├── admin/             # Admin interface
├── public/            # Frontend templates and assets
├── blocks/            # Gutenberg blocks (Phase 2)
├── languages/         # Translation files
└── docs/              # Complete documentation
```

## Features by Phase

### Phase 1 (MVP) - Version 1.0
- Custom Post Type + Taxonomies + Metadata
- RSS automatic synchronization
- Historical manual crawler
- Local image import
- Default frontend templates
- Complete admin settings page
- Logs and error handling

### Phase 2 (Advanced) - Version 1.5
- Gutenberg blocks (3 blocks)
- Advanced statistics with charts
- WordPress dashboard widget
- Shortcodes for legacy support
- CSV/JSON export
- AJAX search/filters

### Phase 3+ (Future)
- REST API endpoints
- Webhooks for real-time sync
- Interactive map view
- Wishlist feature
- Private notes on check-ins
- Cellar management

## Important Notes

### No Official API

This plugin does **not** use an official Untappd API (none exists). Instead, it:
- Uses the RSS feed for recent check-ins (limited to 25 most recent)
- Scrapes HTML pages for complete metadata (rating, ABV, style, etc.)
- Implements rate limiting to respect Untappd's servers

Use the plugin only with **public** Untappd data you are allowed to republish on your site. Untappd may change page HTML at any time; scraping can break until the plugin is updated. This project is not affiliated with Untappd. A longer notice for site owners is in [docs/legal/scraping-notice.md](docs/legal/scraping-notice.md).

### Data Limitations

The RSS feed only contains:
- Check-in title, link, date
- Basic beer and brewery names
- Sometimes a photo URL

**Missing from RSS** (requires scraping):
- Rating (0-5)
- ABV % / IBU
- Beer style
- Full comment
- Serving type
- Toast count

## Contributing

Contributions are welcome! Please read our [Contributing Guide](docs/development/contributing.md) for details on:
- Code standards (WordPress Coding Standards)
- Testing requirements
- Pull request process

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2025 Beer Journal Contributors

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## Support

- **WordPress.org Forum**: [Coming soon]
- **GitHub Issues**: [Repository URL]
- **Documentation**: See `/docs/` directory

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a complete list of changes.

## Credits

Developed with ❤️ for the beer community.

---

**Note**: This plugin is not affiliated with or endorsed by Untappd. It is an independent project that respects Untappd's trademark guidelines.

