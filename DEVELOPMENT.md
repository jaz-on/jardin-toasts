# Development Guide

## Repository and branches

- **`main`**: default branch on GitHub; reference tree, releases, and PR target. Protected (no force-push).
- **`dev`**: integration branch for day-to-day work; typical target for [Git Updater](https://git-updater.com/) on a development site (`Primary Branch: dev` in plugin headers).
- **Documentation** lives in `docs/` on the same branches (no separate long-lived `docs` branch).

**Plugin status:** `0.1.0` is an installable bootstrap (`beer-journal.php` only). MVP behaviour is described under `docs/` and in [CHANGELOG.md](CHANGELOG.md) as *target scope*, not as shipped code.

## Quick Start

This guide will help you get started developing Beer Journal.

## Prerequisites

- **PHP**: 8.2 or higher
- **WordPress**: 6.0 or higher
- **Composer**: For PHP dependencies
- **npm**: For JavaScript dependencies (Gutenberg blocks)
- **MySQL/MariaDB**: 5.7+ / 10.3+

## Setup

### 1. Install PHP Dependencies

```bash
composer install
```

This will install:
- Guzzle HTTP client
- Symfony DomCrawler
- Symfony CSS Selector
- PHPUnit (dev)
- PHP_CodeSniffer (dev)

### 2. Install JavaScript Dependencies

```bash
npm install
```

This will install:
- @wordpress/scripts (for building Gutenberg blocks)

### 3. Development Environment

Set up a local WordPress installation and activate the plugin.

## Project Structure

```
beer-journal/
├── includes/          # Core plugin classes
├── admin/            # Admin interface
├── public/            # Frontend templates and assets
├── blocks/            # Gutenberg blocks (Phase 2)
├── languages/         # Translation files
├── tests/             # Unit tests
├── docs/              # Documentation
└── beer-journal.php   # Main plugin file
```

## Development Order (Phase 1 MVP)

Follow this order when implementing features:

### 1. Structure de base
- [x] Main plugin file (`beer-journal.php`) — bootstrap only (headers, constants, text domain); feature code still to add
- [ ] Activation/deactivation hooks
- [ ] Custom Post Type registration (`BJ_Post_Type`)
- [ ] Taxonomies registration (`BJ_Taxonomies`)
- [ ] Meta fields registration (`BJ_Meta_Fields`)
- [ ] Settings page structure (`BJ_Settings`)

### 2. RSS Sync (Priorité)
- [ ] RSS Parser (`BJ_RSS_Parser`)
- [ ] Adaptive polling scheduler (`BJ_Action_Scheduler`)
- [ ] GUID comparison logic
- [ ] Integration with importer

### 3. Scraping
- [ ] HTML Scraper (`BJ_Scraper`)
- [ ] Data extraction logic
- [ ] Error handling and retry

### 4. Import Process
- [ ] Importer (`BJ_Importer`)
- [ ] Data validation
- [ ] Post creation
- [ ] Taxonomy assignment
- [ ] Meta fields assignment
- [ ] Rating mapping

### 5. Image Handling
- [ ] Image Handler (`BJ_Image_Handler`)
- [ ] Download from URL
- [ ] Media Library integration
- [ ] Placeholder fallback

### 6. Historical Import
- [ ] Crawler (`BJ_Crawler`)
- [ ] Pagination handling
- [ ] Batch processing
- [ ] Progress tracking

### 7. Admin Interface
- [ ] Settings page (`BJ_Admin`)
- [ ] Import interface
- [ ] Progress tracking (AJAX)
- [ ] Logs viewer

### 8. Frontend Templates
- [ ] Archive template
- [ ] Single template
- [ ] Taxonomy templates
- [ ] Template tags

### 9. Polish & Optimization
- [ ] Caching
- [ ] Logging
- [ ] Error handling refinement
- [ ] Performance optimization

## Coding Standards

### WordPress Coding Standards (WPCS)

Run PHP_CodeSniffer to check code quality:

```bash
composer run phpcs
```

Auto-fix issues where possible:

```bash
composer run phpcbf
```

### Naming Conventions

- **Functions**: `bj_` prefix (e.g., `bj_get_checkin_data()`)
- **Classes**: `BJ_` prefix (e.g., `BJ_Importer`)
- **Constants**: `BJ_` prefix (e.g., `BJ_VERSION`)
- **Options**: `bj_` prefix (e.g., `bj_last_checkin_date`)
- **Meta Keys**: `_bj_` prefix (e.g., `_bj_checkin_id`)

### Text Domain

Always use `'beer-journal'` for all translatable strings:

```php
__('Beer Check-ins', 'beer-journal')
_e('Import Historical Check-ins', 'beer-journal')
esc_html__('Rating System', 'beer-journal')
```

## Testing

### Unit Tests

Run PHPUnit tests:

```bash
composer run test
```

### Manual Testing Checklist

- [ ] RSS sync works correctly
- [ ] Scraping extracts all data
- [ ] Images import to Media Library
- [ ] Taxonomies auto-create
- [ ] Rating system maps correctly
- [ ] Drafts created for incomplete data
- [ ] Retry logic works
- [ ] Admin interface functions properly
- [ ] Frontend templates display correctly

## Building Gutenberg Blocks (Phase 2)

Build blocks for production:

```bash
npm run build
```

Development mode with watch:

```bash
npm start
```

## Git Workflow

### Branches

- **`main`**: production-ready / reference; default branch; merge via PR when collaboration grows.
- **`dev`**: daily integration; push here to trigger Git Updater on the dev WordPress site (webhook + `branch=dev`).
- **`feature/*`**: short-lived branches from `dev` (or `main` for hotfixes, as needed).

Documentation changes go through the same branches: edit `docs/` on `dev`, open a PR to `main` when ready to publish.

**Tools**:
- `scripts/validate-docs.sh` - Validate documentation structure and links
- `scripts/analyze-docs.php` - Analyze documentation and generate reports
- See [Prompts réutilisables](docs/development/prompts-reutilisables.md) for AI-assisted analysis

### Commit Messages

Follow Conventional Commits format:

```
type(scope): subject

body (optional)

footer (optional)
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Test additions/changes
- `chore`: Maintenance tasks

Examples:
```
feat(rss): Add adaptive polling scheduler
fix(scraper): Handle missing rating gracefully
docs(api): Document REST endpoints
```

## Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Symfony DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html)
- [Guzzle HTTP Client](https://docs.guzzlephp.org/)
- [Documentation](../docs/)

## Getting Help

- Check the [documentation](../docs/) directory
- Review [architecture documentation](../docs/architecture/)
- See [coding standards](../docs/development/coding-standards.md)
- Check [contributing guide](../docs/development/contributing.md)

