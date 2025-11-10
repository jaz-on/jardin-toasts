# WordPress Options

## Overview

Beer Journal stores configuration and state in WordPress `wp_options` table. All option names are prefixed with `bj_` to avoid conflicts.

## Option Categories

### Synchronization Options

RSS sync configuration and state.

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `bj_rss_feed_url` | string | Untappd RSS feed URL | `""` |
| `bj_sync_enabled` | bool | Whether automatic sync is enabled | `true` |
| `bj_sync_frequency` | string | Manual frequency override (optional) | `""` |
| `bj_last_checkin_date` | datetime | Date of last imported check-in | `""` |
| `bj_last_imported_guid` | string | GUID of last imported check-in | `""` |

**Usage**: 
- `bj_last_checkin_date`: Used for adaptive polling calculation
- `bj_last_imported_guid`: Used for GUID comparison optimization

---

### Rating System Options

Rating mapping and label configuration.

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `bj_rating_rules` | array | Custom rating mapping rules | `bj_get_default_rating_rules()` |
| `bj_rating_labels` | array | Custom labels for each rating level | `bj_get_default_rating_labels()` |
| `bj_rating_rounding_enabled` | bool | Enable rating rounding | `true` |
| `bj_rating_show_raw_tooltip` | bool | Show original rating in tooltip | `true` |
| `bj_rating_display_single` | bool | Display labels on single pages | `true` |
| `bj_rating_display_archive` | bool | Display labels in archive | `false` |
| `bj_rating_display_list` | bool | Display labels in list view | `false` |

**Structure**:
```php
// Rating rules
$bj_rating_rules = [
    ['min' => 0.0, 'max' => 0.9, 'round' => 0],
    ['min' => 1.0, 'max' => 1.9, 'round' => 1],
    // ... etc
];

// Rating labels
$bj_rating_labels = [
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
| `bj_import_checkpoint` | array | Import progress checkpoint | `[]` |
| `bj_import_batch_size` | int | Number of check-ins per batch | `25` |
| `bj_import_delay` | int | Delay between requests (seconds) | `3` |
| `bj_import_mode` | string | Import mode: 'manual' or 'background' | `"manual"` |

**Checkpoint Structure**:
```php
$bj_import_checkpoint = [
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
| `bj_import_images` | bool | Import images to Media Library | `true` |
| `bj_image_max_width` | int | Maximum image width (px) | `1200` |
| `bj_image_max_height` | int | Maximum image height (px) | `1200` |
| `bj_generate_thumbnails` | bool | Generate WordPress thumbnails | `true` |
| `bj_compress_images` | bool | Compress images (requires plugin) | `false` |
| `bj_placeholder_image_id` | int | Default placeholder image ID | `0` |

---

### General Options

General plugin configuration.

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `bj_scraping_delay` | int | Delay between scraping requests (seconds) | `3` |
| `bj_import_social_data` | bool | Import social data (toasts, comments) | `true` |
| `bj_import_venues` | bool | Import venue data | `true` |
| `bj_import_badges` | bool | Import badges (Phase 3) | `false` |
| `bj_deduplication_method` | string | Deduplication method: 'checkin_id' or 'name_date' | `"checkin_id"` |

---

### Notification Options

Admin notification settings.

| Option Name | Type | Description | Default |
|-------------|------|-------------|---------|
| `bj_notify_on_sync` | bool | Email notification after sync | `false` |
| `bj_notify_on_error` | bool | Email notification on errors | `true` |
| `bj_notification_email` | string | Email address for notifications | `get_option('admin_email')` |
| `bj_new_terms_created` | array | Log of newly created taxonomy terms | `[]` |

**New Terms Structure**:
```php
$bj_new_terms_created = [
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
| `bj_debug_mode` | bool | Enable detailed logging | `false` |
| `bj_log_http_requests` | bool | Log HTTP requests | `false` |
| `bj_log_retention_days` | int | Days to keep logs | `30` |

---

### Cache/Transient Options

Temporary data stored in transients (not in `wp_options`, but related).

| Transient Name | Type | Description | Expiration |
|----------------|------|-------------|------------|
| `bj_new_terms_notice` | int | Count of new terms (for admin notice) | 1 week |
| `bj_global_stats` | array | Cached global statistics | 1 hour |
| `bj_top_breweries` | array | Cached top breweries list | 1 day |

**Usage**: Transients are automatically expired and don't need manual cleanup.

---

## Accessing Options

### WordPress Functions

```php
// Get option
$rss_url = get_option('bj_rss_feed_url', '');

// Update option
update_option('bj_rss_feed_url', 'https://untappd.com/rss/user/username');

// Delete option
delete_option('bj_rss_feed_url');

// Get option with default
$delay = get_option('bj_scraping_delay', 3);
```

### Array Options

For array options, WordPress automatically serializes/unserializes:

```php
// Get array option
$rules = get_option('bj_rating_rules', []);

// Update array option
update_option('bj_rating_rules', [
    ['min' => 0.0, 'max' => 0.9, 'round' => 0],
    // ... etc
]);
```

## Default Values

### Default Rating Rules

```php
function bj_get_default_rating_rules() {
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
function bj_get_default_rating_labels() {
    return [
        0 => __('Undrinkable - Not even beer', 'beer-journal'),
        1 => __('Terrible - Only if there\'s no alternative', 'beer-journal'),
        2 => __('Mediocre - Meh, it\'s okay I guess', 'beer-journal'),
        3 => __('Decent - A solid thirst quencher', 'beer-journal'),
        4 => __('Great - Now we\'re talking! A real pleasure', 'beer-journal'),
        5 => __('Exceptional - Buy it with your eyes closed. Masterpiece!', 'beer-journal'),
    ];
}
```

## Option Cleanup

### On Plugin Deactivation

Options are preserved on deactivation (user may reactivate).

### On Plugin Uninstall

Options should be deleted on uninstall:

```php
register_uninstall_hook(__FILE__, 'bj_uninstall');

function bj_uninstall() {
    // Delete all options
    delete_option('bj_rss_feed_url');
    delete_option('bj_sync_enabled');
    // ... etc
    
    // Or use a loop
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'bj_%'");
}
```

## Autoload Considerations

Most options should **not** be autoloaded (set `autoload = 'no'`) to reduce database load:

```php
update_option('bj_rating_rules', $rules, 'no'); // Don't autoload
```

**Exceptions** (should autoload):
- `bj_sync_enabled`: Frequently checked
- `bj_rating_rounding_enabled`: Frequently checked

## Related Documentation

- [Schema Documentation](schema.md)
- [Meta Fields](meta-fields.md)
- [Indexes](indexes.md)
- [Settings Documentation](../architecture/overview.md)

