# Features Checklist

## Overview

This checklist tracks **what is implemented in code** for **1.0.0** versus items that remain **spec-only** or **phase 2**. The product specification in other `docs/` files may still describe future behaviour; this file is the implementation truth.

## Version 1.0.0 (code)

### Core functionality
- [x] Custom Post Type `beer_checkin` registration
- [x] Taxonomies: `beer_style`, `brewery`, `venue`
- [x] Core meta fields (`_bj_*`) on import + REST registration for main keys
- [x] RSS synchronization with adaptive WP-Cron (`sixhourly` / `daily` / `weekly`)
- [x] HTML scraping for check-in pages (DomCrawler + fallbacks)
- [x] Historical import: profile discovery + batched AJAX import; checkpoint in `bj_import_checkpoint`
- [x] Image sideload to Media Library (dedup by hash / source URL)
- [x] Rating mapping via `bj_rating_rules` filter + defaults
- [x] Deduplication by `_bj_checkin_id`
- [x] Auto-creation of taxonomy terms on import (`wp_set_object_terms` with create)

### Admin interface
- [x] Top-level **Beer Journal** menu; settings under 5 tabs (query arg `tab`)
- [x] Synchronization + RSS “Run sync now” (AJAX)
- [x] Historical import controls (discover + import batch) (AJAX)
- [x] General / Rating / Advanced options as documented (subset of full spec)
- [x] Logs viewer (tail of today’s file, Advanced tab)
- [ ] Dedicated taxonomies merge/review UI (not in 1.0.0)
- [ ] Statistics dashboard (not in 1.0.0; phase 2)

### Frontend
- [x] Default plugin templates: archive, single, taxonomies (`public/templates/`)
- [x] Theme overrides via `beer-journal/` in theme (see `public/class-public.php`)
- [x] Template tags: `bj_the_rating_stars`, getters in `public/template-tags.php`
- [x] Filter `bj_checkin_content`; filter `bj_rating_display` on star markup
- [x] JSON-LD Review block (option `bj_schema_enabled`)
- [x] Microformats classes on templates (option `bj_microformats_enabled`)
- [ ] Grid vs table archive toggle (not in 1.0.0)
- [ ] Front-end filters by rating beyond theme/query (not in 1.0.0)

### Data management
- [x] Publish vs draft when rating missing (draft + `_bj_incomplete_reason`)
- [x] `_bj_exclude_sync` respected on re-import
- [x] Scrape retries (scraper-level)
- [x] File logging + optional debug lines
- [ ] Email notifications on sync/error (options exist in spec; not wired in 1.0.0)

### Performance & polish
- [x] GUID short-circuit before per-item work
- [ ] Full transient strategy / `bj_get_cached_data()` as in caching doc (partial)
- [ ] DB indexes applied via dbDelta (documented; not a custom migration in 1.0.0)
- [x] Batch historical import

### Security & i18n
- [x] Sanitization, escaping, capabilities, AJAX nonces on admin actions
- [x] Text domain `beer-journal`; stub `languages/beer-journal.pot` (regenerate with `wp i18n make-pot` when desired)

---

## Feature Status Legend

- [x] Completed
- [ ] Planned
- [~] In Progress
- [!] Blocked
- [-] Cancelled

---

## Related Documentation

- [Roadmap](roadmap.md)
- [Core Modules](core-modules.md)

