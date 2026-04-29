# Development Guide

## Repository and branches

- **`main`**: default branch on GitHub; reference tree, releases, and PR target. Protected (no force-push).
- **`dev`**: integration branch for day-to-day work. The plugin header uses `Primary Branch: main` so default GitHub / Git Updater installs track stable work; on a development site (e.g. dev.jasonrouet.com), set the branch to `dev` in Git Updater’s settings per plugin.
- **Documentation** lives in `docs/` on the same branches (no separate long-lived `docs` branch).

**Plugin status:** `1.0.0` ships the MVP described in `docs/` (CPT, sync, scraping, import, admin, front). See [CHANGELOG.md](CHANGELOG.md) for the exact shipped feature list.

## Quick Start

This guide will help you get started developing Jardin Toasts.

## Prerequisites

- **PHP**: 8.2 or higher
- **WordPress**: 6.0 or higher
- **Composer**: For PHP dependencies
- **npm**: For JavaScript dependencies (Gutenberg blocks)
- **MySQL/MariaDB**: 5.7+ / 10.3+

## Setup

### 1. Install PHP Dependencies

Runtime Composer packages are **committed** in `vendor/` so Git-based installs work out of the box. A full **`composer install`** also pulls dev tools (PHPUnit, PHPCS, WPCS, PHPStan, WordPress stubs). The plugin uses **WordPress HTTP** for scraping; runtime PHP deps are **Symfony DomCrawler** and **Symfony CSS Selector** only.

```bash
composer install
composer run phpcs    # coding standard
composer run phpstan  # static analysis
composer run test     # PHPUnit
```

After a full `composer install`, Git may show changes under `vendor/`; before committing unrelated work, align with what you intend to ship (often `composer update` + commit `composer.lock` and `vendor/` when dependencies change).

CI runs **PHP Quality** (`.github/workflows/php-quality.yml`): PHPCS, PHPStan, PHPUnit on pushes/PRs to `main` and `dev` when PHP or Composer files change.

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
jardin-toasts/
├── includes/          # Core plugin classes
├── admin/            # Admin interface
├── public/            # Frontend templates and assets
├── blocks/            # Gutenberg blocks (Phase 2)
├── languages/         # Translation files
├── tests/             # Unit tests
├── docs/              # Documentation
└── jardin-toasts.php   # Main plugin file
```

## Development Order (Phase 1 MVP)

Follow this order when implementing features:

### 1. Structure de base
- [x] Main plugin file (`jardin-toasts.php`) — bootstrap, Composer, lifecycle hooks
- [x] Activation/deactivation hooks (`JB_Activator`, `JB_Deactivator`)
- [x] Custom Post Type registration (`JB_Post_Type`)
- [x] Taxonomies registration (`JB_Taxonomies`)
- [x] Meta fields registration (`JB_Meta_Fields`)
- [x] Settings API (`JB_Settings`)

### 2. RSS Sync (Priorité)
- [x] RSS Parser (`JB_RSS_Parser`)
- [x] Adaptive polling scheduler (`JB_Action_Scheduler`)
- [x] GUID comparison logic
- [x] Integration with importer

### 3. Scraping
- [x] HTML Scraper (`JB_Scraper`)
- [x] Data extraction logic
- [x] Error handling and retry

### 4. Import Process
- [x] Importer (`JB_Importer`)
- [x] Data validation
- [x] Post creation
- [x] Taxonomy assignment
- [x] Meta fields assignment
- [x] Rating mapping

### 5. Image Handling
- [x] Image Handler (`JB_Image_Handler`)
- [x] Download from URL
- [x] Media Library integration
- [ ] Placeholder fallback (optional / theme-level)

### 6. Historical Import
- [x] Crawler (`JB_Crawler`)
- [x] Pagination handling (best-effort profile pages)
- [x] Batch processing (AJAX + optional background hook)
- [x] Progress tracking (checkpoint option)

### 7. Admin Interface
- [x] Settings page (`JB_Admin`, 5 tabs)
- [x] Import interface (discover + batch)
- [x] Progress feedback (AJAX)
- [x] Logs viewer (Advanced tab)

### 8. Frontend Templates
- [x] Archive template
- [x] Single template
- [x] Taxonomy templates
- [x] Template tags (`public/template-tags.php`)

### 9. Polish & Optimization
- [x] Caching (`jb_get_cached_data`, stats transient invalidation — see CHANGELOG [Unreleased])
- [x] Logging
- [x] Error handling refinement (core paths)
- [x] Phase 1 polish (stats UI, email alerts, archive layout, placeholder, optional DB index — see CHANGELOG [Unreleased])

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

- **Functions**: `jb_` prefix (e.g., `jb_get_checkin_data()`)
- **Classes**: `JB_` prefix (e.g., `JB_Importer`)
- **Constants**: `JB_` prefix (e.g., `JB_VERSION`)
- **Options**: `jb_` prefix (e.g., `jb_last_checkin_date`)
- **Meta Keys**: `_jb_` prefix (e.g., `_jb_checkin_id`)

### Text Domain

Always use `'jardin-toasts'` for all translatable strings:

```php
__('Beer Check-ins', 'jardin-toasts')
_e('Import Historical Check-ins', 'jardin-toasts')
esc_html__('Rating System', 'jardin-toasts')
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
- [Documentation](../docs/)

## Getting Help

- Check the [documentation](../docs/) directory
- Review [architecture documentation](../docs/architecture/)
- See [coding standards](../docs/development/coding-standards.md)
- Check [contributing guide](../docs/development/contributing.md)

