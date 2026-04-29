# WordPress Options

## Overview

Jardin Toasts stores configuration and state in WordPress `wp_options` table. All option names are prefixed with `jb_` to avoid conflicts.

## Option Categories

### Synchronization Options

RSS sync configuration and state.

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `jb_rss_feed_url` | string | Untappd RSS feed URL | Maintainer default RSS URL from `jb_get_default_rss_feed_url()` until saved |
| `jb_sync_enabled` | bool | Whether automatic sync is enabled | `true` |
| `jb_sync_frequency` | string | Manual frequency override (optional) | `""` |
| `jb_last_checkin_date` | datetime | Date of last imported check-in | `""` |
| `jb_last_imported_guid` | string | GUID of last imported check-in | `""` |

**Usage**: 
- `jb_last_checkin_date`: Used for adaptive polling calculation
- `jb_last_imported_guid`: Used for GUID comparison optimization

---

### Untappd Options

Integration-specific options (optionnelles selon méthode utilisée).

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `jb_untappd_username` | string | Untappd username | `""` |
| `jb_untappd_rss_key` | string | RSS/API key if required (optional) | `""` |
| `jb_excluded_checkins` | array | List of check-in IDs to exclude from sync | `[]` |

Notes:
- `jb_excluded_checkins` complète la méta `_jb_exclude_sync` au niveau post (protection fine).
- Pour l’import d’images, utiliser l’option existante `jb_import_images`.

---

### Rating System Options

Rating mapping and label configuration.

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `jb_rating_rules` | array | Custom rating mapping rules | `jb_get_default_rating_rules()` |
| `jb_rating_labels` | array | Custom labels for each rating level | `jb_get_default_rating_labels()` |
| `jb_rating_rounding_enabled` | bool | Enable rating rounding | `true` |
| `jb_rating_show_raw_tooltip` | bool | Show original rating in tooltip | `true` |
| `jb_rating_display_single` | bool | Display labels on single pages | `true` |
| `jb_rating_display_archive` | bool | Display labels in archive | `false` |
| `jb_rating_display_list` | bool | Display labels in list view | `false` |

**Structure**:
```php
// Rating rules
$jb_rating_rules = [
    ['min' => 0.0, 'max' => 0.9, 'round' => 0],
    ['min' => 1.0, 'max' => 1.9, 'round' => 1],
    // ... etc
];

// Rating labels
$jb_rating_labels = [
    0 => 'Undrinkable - Not even beer',
    1 => 'Terrible - Only if there\'s no alternative',
    // ... etc
];
```

---

### Import Options

Historical import configuration and state.

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `jb_import_checkpoint` | array | Import progress checkpoint | `[]` |
| `jb_import_batch_size` | int | Number of check-ins per batch | `25` |
| `jb_import_delay` | int | Delay between requests (seconds) | `3` |
| `jb_import_mode` | string | Import mode: 'manual' or 'background' | `"manual"` |

**Checkpoint Structure**:
```php
$jb_import_checkpoint = [
    'current_page' => 3,
    'total_imported' => 75,
    'last_checkin_id' => '123456',
    'started_at' => 1699632000, // Unix timestamp
];
```

---

### Image Options

Image import configuration.

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `jb_import_images` | bool | Import images to Media Library | `true` |
| `jb_image_max_width` | int | Maximum image width (px) | `1200` |
| `jb_image_max_height` | int | Maximum image height (px) | `1200` |
| `jb_generate_thumbnails` | bool | Generate WordPress thumbnails | `true` |
| `jb_compress_images` | bool | Compress images (requires plugin) | `false` |
| `jb_placeholder_image_id` | int | Default placeholder image ID | `0` |

---

### General Options

General plugin configuration.

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `jb_scraping_delay` | int | Delay between scraping requests (seconds) | `3` |
| `jb_import_social_data` | bool | Import social data (toasts, comments) | `true` |
| `jb_import_venues` | bool | Import venue data | `true` |
| `jb_import_badges` | bool | Import badges (Phase 3) | `false` |
| `jb_deduplication_method` | string | Deduplication method: 'checkin_id' or 'name_date' | `"checkin_id"` |

---

### SEO Options

Structured data and microformats configuration.

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `jb_schema_enabled` | bool | Enable Schema.org JSON-LD (Review/Product) | `true` |
| `jb_microformats_enabled` | bool | Enable microformats in templates (`h-entry`, `e-content`) | `true` |

Notes:
- These options are enabled by default. They can be disabled in Settings > Advanced.
- Always escape JSON output and avoid sensitive data.

---

### Notification Options

Admin notification settings.

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `jb_notify_on_sync` | bool | Email notification after sync | `false` |
| `jb_notify_on_error` | bool | Email notification on errors | `true` |
| `jb_notification_email` | string | Email address for notifications | `get_option('admin_email')` |
| `jb_new_terms_created` | array | Log of newly created taxonomy terms | `[]` |

**New Terms Structure**:
```php
$jb_new_terms_created = [
    [
        'taxonomy' => 'beer_style',
        'term' => 'IPA',
        'term_id' => 5,
        'created_at' => '2025-11-10 18:15:00',
        'source_checkin' => 123,
    ],
    // ... more terms
];
```

---

### Debug Options

Debugging and logging configuration.

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `jb_debug_mode` | bool | Enable detailed logging | `false` |
| `jb_log_http_requests` | bool | Log HTTP requests | `false` |
| `jb_log_retention_days` | int | Days to keep logs | `30` |

---

### Cache/Transient Options

Temporary data stored in transients (not in `wp_options`, but related).

| Transient Name | Type | Description | Expiration |
|----------------|------|-------------|------------|
| `jb_new_terms_notice` | int | Count of new terms (for admin notice) | 1 week |
| `jb_global_stats` | array | Cached global statistics | 1 hour |
| `jb_top_breweries` | array | Cached top breweries list | 1 day |

**Usage**: Transients are automatically expired and don't need manual cleanup.

---

## Future Options (v1.5)

### Cache Configuration (Option B)

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `jb_cache_enabled` | bool | Enable/disable application-level caching | `true` |
| `jb_cache_hours` | int | Cache duration in hours (applies to scraping/stats/queries) | `3` |

Notes:
- MVP uses Option A (automatic, no UI).
- Option B will provide a simple setting and a “Clear cache” button.

---

## Accessing Options

### WordPress Functions

```php
// Get option
$rss_url = get_option('jb_rss_feed_url', '');

// Update option
update_option('jb_rss_feed_url', 'https://untappd.com/rss/user/username');

// Delete option
delete_option('jb_rss_feed_url');

// Get option with default
$delay = get_option('jb_scraping_delay', 3);
```

### Array Options

For array options, WordPress automatically serializes/unserializes:

```php
// Get array option
$rules = get_option('jb_rating_rules', []);

// Update array option
update_option('jb_rating_rules', [
    ['min' => 0.0, 'max' => 0.9, 'round' => 0],
    // ... etc
]);
```

## Default Values

### Default Rating Rules

```php
function jb_get_default_rating_rules() {
    return [
        ['min' => 0.0, 'max' => 0.9, 'round' => 0],
        ['min' => 1.0, 'max' => 1.9, 'round' => 1],
        ['min' => 2.0, 'max' => 2.9, 'round' => 2],
        ['min' => 3.0, 'max' => 3.4, 'round' => 3],
        ['min' => 3.5, 'max' => 4.4, 'round' => 4],
        ['min' => 4.5, 'max' => 5.0, 'round' => 5],
    ];
}
```

### Default Rating Labels

```php
function jb_get_default_rating_labels() {
    return [
        0 => __('Undrinkable - Not even beer', 'jardin-toasts'),
        1 => __('Terrible - Only if there\'s no alternative', 'jardin-toasts'),
        2 => __('Mediocre - Meh, it\'s okay I guess', 'jardin-toasts'),
        3 => __('Decent - A solid thirst quencher', 'jardin-toasts'),
        4 => __('Great - Now we\'re talking! A real pleasure', 'jardin-toasts'),
        5 => __('Exceptional - Buy it with your eyes closed. Masterpiece!', 'jardin-toasts'),
    ];
}
```

## Option Cleanup

### On Plugin Deactivation

Options are preserved on deactivation (user may reactivate).

### On Plugin Uninstall

Options should be deleted on uninstall:

```php
register_uninstall_hook(__FILE__, 'jb_uninstall');

function jb_uninstall() {
    // Delete all options
    delete_option('jb_rss_feed_url');
    delete_option('jb_sync_enabled');
    // ... etc
    
    // Or use a loop
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'jb_%'");
}
```

## Autoload Considerations

Most options should **not** be autoloaded (set `autoload = 'no'`) to reduce database load:

```php
update_option('jb_rating_rules', $rules, 'no'); // Don't autoload
```

**Exceptions** (should autoload):
- `jb_sync_enabled`: Frequently checked
- `jb_rating_rounding_enabled`: Frequently checked

## Related Documentation

- [Schema Documentation](schema.md)
- [Meta Fields](meta-fields.md)
- [Indexes](indexes.md)
- [Settings Documentation](../architecture/overview.md)

