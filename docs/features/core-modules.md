# Core Modules

## Overview

Mapping of features to core plugin modules and files.

## Module Structure

```
jardin-toasts/
├── includes/          # Core functionality
├── admin/             # Admin interface
├── public/            # Frontend
├── blocks/            # Gutenberg blocks
└── languages/         # Translations
```

## Core Modules

### RSS Synchronization

**Module**: `includes/class-rss-parser.php`

**Class**: `JB_RSS_Parser`

**Features**:
- RSS feed fetching
- XML parsing
- GUID comparison
- Adaptive polling logic

**Related Files**:
- `includes/class-action-scheduler.php` - Cron scheduling
- `includes/class-importer.php` - Data import

---

### HTML Scraping

**Module**: `includes/class-scraper.php`

**Class**: `JB_Scraper`

**Features**:
- HTML page fetching
- DOM parsing (Symfony DomCrawler)
- Data extraction
- Rate limiting

**Dependencies**:
- Symfony DomCrawler
- Symfony CSS Selector
- Guzzle HTTP Client

---

### Data Import

**Module**: `includes/class-importer.php`

**Class**: `JB_Importer`

**Features**:
- Data validation
- Post creation
- Taxonomy assignment
- Meta field management
- Deduplication
- Retry logic

**Related Files**:
- `includes/class-post-type.php` - CPT registration
- `includes/class-taxonomies.php` - Taxonomy registration
- `includes/class-meta-fields.php` - Meta field management

---

### Image Handling

**Module**: `includes/class-image-handler.php`

**Class**: `JB_Image_Handler`

**Features**:
- Image download
- Duplicate detection (MD5 hash)
- Media Library import
- Thumbnail generation
- Alt text and captions

---

### Custom Post Type

**Module**: `includes/class-post-type.php`

**Class**: `JB_Post_Type`

**Features**:
- CPT registration (`beer_checkin`)
- REST API support
- Rewrite rules
- Capabilities
- Admin menu: CPT and taxonomies nest under `edit.php?post_type=beer_checkin`; **Jardin Toasts** is the CPT `menu_name` label.

---

### Taxonomies

**Module**: `includes/class-taxonomies.php`

**Class**: `JB_Taxonomies`

**Features**:
- Taxonomy registration:
  - `beer_style` (hierarchical)
  - `brewery` (non-hierarchical)
  - `venue` (non-hierarchical)
- Auto-creation of terms
- Admin notifications

---

### Meta Fields

**Module**: `includes/class-meta-fields.php`

**Class**: `JB_Meta_Fields`

**Features**:
- Meta field registration
- REST API integration
- Sanitization callbacks
- Admin display

---

### Settings Management

**Module**: `includes/class-settings.php`

**Class**: `JB_Settings`

**Features**:
- Settings registration
- Settings validation
- Settings sanitization
- Default values

**Related Files**:
- `admin/class-admin.php` - Admin interface
- `admin/views/settings-*.php` - Settings pages

---

### Admin Interface

**Module**: `admin/class-admin.php`

**Class**: `JB_Admin`

**Features**:
- Admin menu registration
- Settings pages
- Import interface
- Progress tracking
- Logs viewer
- Statistics dashboard

**Related Files**:
- `admin/views/settings-general.php`
- `admin/views/settings-import.php`
- `admin/views/settings-rating.php`
- `admin/views/settings-taxonomies.php`
- `admin/views/settings-advanced.php`
- `admin/assets/css/admin.css`
- `admin/assets/js/admin.js`
- `admin/assets/js/import-progress.js`

---

### Frontend Templates

**Module**: `public/class-public.php`

**Class**: `JB_Public`

**Features**:
- Template registration
- Asset enqueuing
- Template tags
- Hooks and filters

**Related Files**:
- `public/templates/archive-beer.php`
- `public/templates/single-beer.php`
- `public/templates/taxonomy-*.php`
- `public/partials/checkin-card.php`
- `public/partials/rating-stars.php`
- `public/assets/css/public.css`
- `public/assets/js/public.js`

---

### Rating System

**Module**: `includes/class-settings.php` (configuration) + template tags

**Features**:
- Rating mapping rules
- Custom labels
- Display functions
- Admin configuration

**Related Files**:
- `admin/views/settings-rating.php`
- Template tags in `public/class-public.php`

---

### Action Scheduler

**Module**: `includes/class-action-scheduler.php`

**Class**: `JB_Action_Scheduler`

**Features**:
- RSS sync scheduling
- Background import batches
- Retry scheduling
- Checkpoint management

---

### Historical Import

**Module**: `includes/class-crawler.php`

**Class**: `JB_Crawler`

**Features**:
- Profile page scraping
- Pagination handling
- Batch processing
- Checkpoint system
- Progress tracking

**Related Files**:
- `admin/views/settings-import.php`
- `admin/assets/js/import-progress.js`

---

## Module Dependencies

### Dependency Graph

```
JB_Admin
  ├── JB_Settings
  ├── JB_Importer
  └── JB_Crawler

JB_RSS_Parser
  ├── JB_Scraper
  └── JB_Importer

JB_Importer
  ├── JB_Post_Type
  ├── JB_Taxonomies
  ├── JB_Meta_Fields
  ├── JB_Image_Handler
  └── JB_Settings

JB_Public
  ├── JB_Post_Type
  └── JB_Taxonomies
```

## Module Responsibilities

### Separation of Concerns

- **Parsing**: RSS and HTML parsing only
- **Import**: Data processing and WordPress integration
- **Display**: Frontend templates and output
- **Configuration**: Settings management
- **Scheduling**: Cron and background tasks

## Related Documentation

- [Architecture Overview](../architecture/overview.md)
- [Components](../architecture/components.md)
- [Checklist](checklist.md)

