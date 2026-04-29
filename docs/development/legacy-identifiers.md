# Legacy identifiers (post–`jardin-toasts` rename)

Canonical product and repository name: **`jardin-toasts`**. The following identifiers are **intentionally unchanged** so existing databases, scheduled jobs, and third-party code keep working:

- **WordPress options and post meta keys:** `jb_*` options, `_jb_*` meta keys (historical “beer journal” prefix, still the storage contract).
- **PHP API:** class prefix `JB_`, global functions `jb_*`, constants `JB_*` (e.g. `JB_VERSION`, `JB_PLUGIN_DIR`).
- **Hooks:** all `jb_*` actions and filters (changing names would silently break extensions).
- **AJAX actions:** `jb_sync_now`, `jb_crawl_discover`, `jb_crawl_batch` (admin JS and nonces depend on them).
- **Legacy admin query args:** `admin.php?page=jardin-beer`, `jardin-beer-settings`, and `jb_jardin_beer_settings` redirect to the current settings screen.

One-time migrations live in `JB_Storage_Migration` (`includes/class-storage-migration.php`). The **legacy** Action Scheduler group slug `jardin-beer` is cleared on upgrade; new recurring actions use the `jardin-toasts` group.
