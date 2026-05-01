# Features Checklist

## Overview

**Phase 1 (MVP)** is **complete** in code for everything listed in [README.md](../../README.md). Suivi des évolutions : historique Git (`git log`) et backlog [`../todolist-future/TODOLIST.md`](../todolist-future/TODOLIST.md).

**Phase 2+ (post-MVP)** is tracked only in [`../todolist-future/TODOLIST.md`](../todolist-future/TODOLIST.md) (single backlog).

## Version 1.0.x (implemented)

### Core functionality
- [x] Custom Post Type `beer_checkin` registration
- [x] Taxonomies: `beer_style`, `brewery`, `venue`
- [x] Core meta fields (`_jb_*`) on import + REST registration for main keys
- [x] RSS synchronization with adaptive WP-Cron (`sixhourly` / `daily` / `weekly`)
- [x] HTML scraping for check-in pages (DomCrawler + fallbacks)
- [x] Historical import: profile discovery + batched AJAX import; checkpoint in `jb_import_checkpoint`
- [x] Image sideload to Media Library (dedup by hash / source URL) (1.0.0)
- [x] Placeholder attachment when sideload fails (Unreleased)
- [x] Rating mapping via `jb_rating_rules` filter + defaults
- [x] Deduplication by `_jb_checkin_id`
- [x] Auto-creation of taxonomy terms on import (`wp_set_object_terms` with create)
- [x] Transient cache `jb_get_cached_data()` + `jb_invalidate_stats_cache()` (Unreleased)
- [x] Optional index `jb_checkin_meta` on `wp_postmeta` (best-effort) (Unreleased)

### Admin interface
- [x] Top-level **Jardin Toasts** menu; settings under 5 tabs (query arg `tab`)
- [x] Synchronization + RSS “Run sync now” (AJAX)
- [x] Historical import controls (discover + import batch) (AJAX)
- [x] General / Rating / Advanced options
- [x] Logs viewer (tail of today’s file, Advanced tab)
- [x] **At a glance** stats: published/draft counts + last RSS sync time (Unreleased)
- [x] Email notifications: optional on sync success and on RSS error (Unreleased)

### Frontend
- [x] Default plugin templates: archive, single, taxonomies (`public/templates/`)
- [x] Theme overrides via `jardin-toasts/` in theme (see `public/class-public.php`)
- [x] Template tags: `jb_the_rating_stars`, getters in `public/template-tags.php`
- [x] Filter `jb_checkin_content`; filter `jb_rating_display` on star markup
- [x] JSON-LD Review block (option `jb_schema_enabled`)
- [x] Microformats classes on templates (option `jb_microformats_enabled`)
- [x] Archive + taxonomy **grid or table** layout (option `jb_archive_layout`) (Unreleased)

### Data management
- [x] Publish vs draft when rating missing (draft + `_jb_incomplete_reason`)
- [x] `_jb_exclude_sync` respected on re-import
- [x] Scrape retries (scraper-level)
- [x] File logging + optional debug lines
- [x] Email notifications (sync / errors) — optional (Unreleased)

### Security & i18n
- [x] Sanitization, escaping, capabilities, AJAX nonces on admin actions
- [x] Text domain `jardin-toasts`; stub `languages/jardin-toasts.pot` (regenerate with `wp i18n make-pot` when desired)

---

## Feature Status Legend

- [x] Completed
- [ ] Planned
- [~] In Progress
- [!] Blocked
- [-] Cancelled

---

## Related Documentation

- [Post-MVP backlog (TODOLIST)](../todolist-future/TODOLIST.md)
- [Roadmap](roadmap.md)
- [Core Modules](core-modules.md)
