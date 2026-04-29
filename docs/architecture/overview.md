# Architecture Overview

## Introduction

Jardin Toasts is a WordPress plugin that synchronizes Untappd beer check-ins to a WordPress site. Since Untappd doesn't provide an official API, the plugin uses a combination of RSS feeds and HTML scraping to import check-in data.

## System Architecture

The plugin follows a modular architecture with clear separation of concerns:

```
┌─────────────────────────────────────────────────────────────┐
│                    WordPress Core                           │
│  (Custom Post Types, Taxonomies, Media Library, Options)    │
└─────────────────────────────────────────────────────────────┘
                            ▲
                            │
┌─────────────────────────────────────────────────────────────┐
│                  Jardin Toasts Plugin                        │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │   RSS Sync   │  │   Scraper   │  │   Importer   │       │
│  │   (Parser)   │  │  (DomCrawler)│  │  (Processor) │       │
│  └──────────────┘  └──────────────┘  └──────────────┘       │
│         │                 │                  │               │
│         └─────────────────┴──────────────────┘               │
│                            │                                  │
│                  ┌─────────▼─────────┐                      │
│                  │   Image Handler    │                      │
│                  │  (Media Library)  │                      │
│                  └────────────────────┘                      │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │  Admin UI     │  │  Frontend    │  │   Blocks     │       │
│  │  (Settings)   │  │ (Templates) │  │ (Gutenberg) │       │
│  └──────────────┘  └──────────────┘  └──────────────┘       │
└─────────────────────────────────────────────────────────────┘
                            ▲
                            │
┌─────────────────────────────────────────────────────────────┐
│                    Untappd.com                              │
│  (RSS Feed + HTML Pages for Scraping)                       │
└─────────────────────────────────────────────────────────────┘
```

## Core Components

### 1. RSS Synchronization (`JB_RSS_Parser`)
- Fetches Untappd RSS feed (25 most recent check-ins)
- Parses XML to extract basic information
- Implements adaptive polling (6h/daily/weekly based on activity)
- Optimizes by comparing GUIDs before scraping

### 2. HTML Scraper (`JB_Scraper`)
- Scrapes individual Untappd check-in pages
- Extracts complete metadata (rating, ABV, style, etc.)
- Uses Symfony DomCrawler for HTML parsing
- Implements rate limiting and error handling

### 3. Data Importer (`JB_Importer`)
- Processes scraped data
- Creates WordPress posts (Custom Post Type)
- Assigns taxonomies (beer styles, breweries, venues)
- Manages post status (publish/draft based on data completeness)
- Handles deduplication

### 4. Image Handler (`JB_Image_Handler`)
- Downloads images from Untappd
- Imports to WordPress Media Library
- Generates thumbnails
- Handles errors and placeholders

### 5. Rating System
- Stores raw ratings (0-5 with decimals)
- Maps to rounded star ratings (0-5 stars)
- Customizable mapping rules
- Customizable labels per rating level

### 6. Admin Interface (`JB_Admin`)
- Settings pages (5 tabs)
- Import progress tracking
- Logs viewer
- Statistics dashboard

### 7. Frontend Templates (`JB_Public`)
- Archive templates (grid/table views)
- Single check-in templates
- Taxonomy templates
- Template tags for developers

## Data Flow

The complete data flow from Untappd to WordPress:

1. **RSS Feed Fetch** → Basic check-in data (title, link, date)
2. **GUID Comparison** → Check if already imported
3. **HTML Scraping** → Complete metadata (rating, ABV, style, etc.)
4. **Data Processing** → Validate and structure data
5. **Image Download** → Import to Media Library
6. **Post Creation** → Create Custom Post Type entry
7. **Taxonomy Assignment** → Assign beer styles, breweries, venues
8. **Status Determination** → Publish or draft based on completeness

## Key Design Decisions

### Why RSS + Scraping?
- **RSS Feed**: Fast, lightweight, detects new check-ins
- **HTML Scraping**: Required for complete metadata (rating, ABV, etc.)
- **Optimization**: Only scrapes when new check-ins detected

### Why Adaptive Polling?
- **Efficiency**: Reduces server load
- **User Experience**: Active users get faster updates
- **Resource Management**: Inactive users don't waste resources

### Why Custom Post Type?
- **WordPress Native**: Leverages WordPress features (REST API, Gutenberg, etc.)
- **Flexibility**: Easy to extend and customize
- **Performance**: Optimized WordPress queries
- **Compatibility**: Works with all WordPress themes

### Why Taxonomies?
- **Filtering**: Easy filtering by style, brewery, venue
- **Navigation**: Built-in WordPress taxonomy archives
- **Organization**: Hierarchical organization (beer styles)
- **SEO**: Better URL structure and organization

### Competitive Differentiators
- **API-free mode**: Fonctionne avec RSS public + scraping (pas d’API Untappd requise)
- **Rating mapping system**: Règles et labels personnalisables
- **Action Scheduler**: File persistante, retries
- **Modern blocks + filters**: Pas de shortcodes/widgets, personnalisation via filtres
- **Data quality gating**: Publication conditionnée par la complétude (ex: note requise)

### SEO & Markup
- **Schema.org JSON-LD**: Type Review/Product injecté dans `wp_head` (activé par défaut)
- **Microformats**: `h-entry`/`e-content` dans les templates (activé par défaut)
- **Options**: `jb_schema_enabled`, `jb_microformats_enabled` pour activer/désactiver

### Caching Strategy
- **Transients**: `jb_*` comme préfixe, TTL recommandés (scraping 3h, stats 1h, requêtes 30min)
- **Invalidation**: Au moment des imports/syncs pour conserver la fraîcheur
- **Version initiale (1.0.0)**: Option A (automatique)

## Technology Stack

- **WordPress**: 6.0+ (Custom Post Types, Taxonomies, REST API)
- **PHP**: 8.2+ (Typed properties, modern features)
- **Symfony DomCrawler**: HTML parsing
- **Guzzle HTTP**: HTTP client
- **SimplePie**: RSS parsing (WordPress built-in)

## Security Considerations

- **Sanitization**: All input sanitized
- **Escaping**: All output escaped
- **Nonces**: All forms and AJAX requests protected
- **Capabilities**: Proper permission checks
- **Rate Limiting**: Respects Untappd servers

## Performance Optimizations

- **Caching**: Transients for stats and expensive operations
- **Database Indexes**: Optimized queries
- **Lazy Loading**: Images and content
- **Batch Processing**: Historical imports in batches
- **GUID Comparison**: Skip scraping when no new check-ins

## Extension Points

The plugin provides multiple extension points:

- **Hooks**: Actions and filters throughout the codebase
- **Template Override**: Theme can override all templates
- **Template Tags**: Helper functions for developers
- **Filters**: Customize data, templates, and behavior
- **REST API**: Native WordPress REST API support

## Related Documentation

- [Components Detail](components.md)
- [Data Flow](data-flow.md)
- [RSS Sync](rss-sync.md)
- [Scraping](scraping.md)
- [Import Process](import-process.md)
- [Rating System](rating-system.md)
- [Image Handling](image-handling.md)

