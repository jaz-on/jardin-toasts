# MVP Features (Phase 1 - Version 1.0)

## Overview

Minimum Viable Product features for Beer Journal version 1.0. These features are required for initial release.

## Core Functionality

### 1. Custom Post Type

**Feature**: WordPress Custom Post Type for check-ins

**Implementation**:
- Post type: `beer`
- Public, with archive
- REST API support
- Featured image support
- Custom fields support

**Status**: ✅ Core feature

---

### 2. Taxonomies

**Feature**: Three taxonomies for organization

**Implementation**:
- `beer_style` (hierarchical, like categories)
- `brewery` (non-hierarchical, like tags)
- `venue` (non-hierarchical, like tags)

**Auto-Creation**: Terms created automatically on import

**Status**: ✅ Core feature

---

### 3. RSS Synchronization

**Feature**: Automatic sync of new check-ins via RSS

**Implementation**:
- Fetches Untappd RSS feed
- Parses XML
- Detects new check-ins (GUID comparison)
- Scrapes individual pages for complete data
- Imports to WordPress

**Adaptive Polling**: Frequency adjusts based on user activity

**Status**: ✅ Core feature

---

### 4. HTML Scraping

**Feature**: Scrape Untappd check-in pages for complete metadata

**Implementation**:
- Uses Symfony DomCrawler
- Extracts rating, ABV, style, comment, etc.
- Rate limiting (2-5 seconds between requests)
- Error handling and retry logic

**Status**: ✅ Core feature (required - RSS doesn't have complete data)

---

### 5. Historical Import

**Feature**: Import entire Untappd history

**Implementation**:
- Manual crawler mode
- Batch processing (25, 50, or 100 per batch)
- Progress tracking with AJAX
- Checkpoint system for resume
- Background mode (WP-Cron) for large imports

**Status**: ✅ Core feature

---

### 6. Image Import

**Feature**: Download and import images to Media Library

**Implementation**:
- Downloads from Untappd URLs
- Duplicate detection (MD5 hash)
- Resize to max 1200×1200px
- Generate thumbnails
- Set alt text and captions

**Status**: ✅ Core feature

---

### 7. Rating System

**Feature**: Customizable rating display and mapping

**Implementation**:
- Stores raw rating (0-5 with decimals)
- Maps to rounded stars (0-5)
- Customizable mapping rules
- Customizable labels per rating level
- Template tags for display

**Status**: ✅ Core feature

---

### 8. Deduplication

**Feature**: Prevent duplicate check-ins

**Implementation**:
- Uses Untappd check-in ID
- Checks before import
- Skips if already exists

**Status**: ✅ Core feature

---

## Admin Interface

### 9. Settings Page

**Feature**: Comprehensive settings interface

**Tabs**:
1. Synchronization
2. Historical Import
3. Rating System
4. Taxonomies Review
5. Advanced/Debug

**Status**: ✅ Core feature

---

### 10. Import Progress

**Feature**: Real-time import progress tracking

**Implementation**:
- AJAX updates every 5 seconds
- Progress bar with percentage
- Statistics display
- Pause/Resume functionality

**Status**: ✅ Core feature

---

### 11. Logs Viewer

**Feature**: View import and sync logs

**Implementation**:
- Log files in `wp-content/uploads/beer-journal/logs/`
- Admin interface to view logs
- Filter by date, type, severity

**Status**: ✅ Core feature

---

## Frontend

### 12. Archive Template

**Feature**: Display list of all check-ins

**Implementation**:
- Grid view (3 columns desktop, 2 tablet, 1 mobile)
- Table view (database-style)
- Toggle between views
- Filters (style, brewery, rating, date)
- Search functionality
- Pagination

**Status**: ✅ Core feature

---

### 13. Single Template

**Feature**: Display individual check-in

**Implementation**:
- Hero image
- Metadata sidebar
- Full comment
- Previous/Next navigation
- Related check-ins

**Status**: ✅ Core feature

---

### 14. Taxonomy Templates

**Feature**: Archive pages for taxonomies

**Implementation**:
- Beer style archive
- Brewery archive
- Venue archive
- Same grid/table views as main archive

**Status**: ✅ Core feature

---

### 15. Template Override Support

**Feature**: Themes can override plugin templates

**Implementation**:
- WordPress template hierarchy
- Theme override priority
- Plugin defaults as fallback

**Status**: ✅ Core feature

---

### 16. Template Tags

**Feature**: Helper functions for developers

**Implementation**:
- `bj_get_checkin_data()`
- `bj_display_rating()`
- `bj_rating_stars()`
- `bj_beer_style()`
- `bj_brewery_link()`
- `bj_venue_info()`
- `bj_beer_image()`
- And more...

**Status**: ✅ Core feature

---

### 17. Hooks and Filters

**Feature**: Customization points for developers

**Implementation**:
- Actions: `bj_before_checkins_list`, `bj_after_checkin_card`, etc.
- Filters: `bj_checkin_template`, `bj_checkin_data`, `bj_rating_display`, etc.

**Status**: ✅ Core feature

---

## Data Management

### 18. Post Status Management

**Feature**: Smart publish/draft logic

**Implementation**:
- Publishes if all required fields present (including rating)
- Saves as draft if missing required fields
- Stores reason in `_bj_incomplete_reason`

**Status**: ✅ Core feature

---

### 19. Retry Logic

**Feature**: Automatic retry for failed imports

**Implementation**:
- 3 attempts for network errors
- 3 attempts for scraping errors (over 24 hours)
- Manual retry button in admin

**Status**: ✅ Core feature

---

### 20. Error Logging

**Feature**: Comprehensive error logging

**Implementation**:
- Log files with timestamps
- Different log levels (INFO, WARNING, ERROR, DEBUG)
- Admin interface for viewing logs

**Status**: ✅ Core feature

---

### 21. Admin Notifications

**Feature**: Notify admin of important events

**Implementation**:
- Dashboard notices
- Email notifications (optional)
- New terms created notifications

**Status**: ✅ Core feature

---

## Performance

### 22. GUID Comparison Optimization

**Feature**: Skip scraping if no new check-ins

**Implementation**:
- Compare latest GUID with last imported
- Skip entire process if no changes
- Saves bandwidth and resources

**Status**: ✅ Core feature

---

### 23. Database Indexes

**Feature**: Optimized database queries

**Implementation**:
- Index on `_bj_checkin_id` for deduplication
- Index on post type and date
- Index on rating for filtering

**Status**: ✅ Core feature

---

### 24. Caching

**Feature**: Cache expensive operations

**Implementation**:
- Transients for statistics
- Object cache for queries
- Clear cache on import

**Status**: ✅ Core feature

---

## Security

### 25. Data Sanitization

**Feature**: All input sanitized

**Implementation**:
- `sanitize_text_field()` for text
- `wp_kses_post()` for rich text
- `esc_url_raw()` for URLs
- `floatval()` / `absint()` for numbers

**Status**: ✅ Core feature

---

### 26. Output Escaping

**Feature**: All output escaped

**Implementation**:
- `esc_html()` for HTML
- `esc_attr()` for attributes
- `esc_url()` for URLs

**Status**: ✅ Core feature

---

### 27. Nonces

**Feature**: Security tokens for forms and AJAX

**Implementation**:
- All forms include nonces
- All AJAX requests verify nonces
- Admin actions verify nonces

**Status**: ✅ Core feature

---

### 28. Capability Checks

**Feature**: Permission checks for all actions

**Implementation**:
- `current_user_can('manage_options')` for admin
- `current_user_can('edit_post')` for editing
- Proper capability mapping

**Status**: ✅ Core feature

---

## Internationalization

### 29. Translation Support

**Feature**: Full i18n support

**Implementation**:
- Text domain: `beer-journal`
- All strings translatable
- .pot file generation
- Language file structure

**Status**: ✅ Core feature

---

## MVP Completion Criteria

All features above must be:
- ✅ Implemented
- ✅ Tested
- ✅ Documented
- ✅ Secure
- ✅ Performant
- ✅ Accessible

## Related Documentation

- [Advanced Features](advanced-features.md)
- [Future Features](future-features.md)
- [Core Modules](core-modules.md)

