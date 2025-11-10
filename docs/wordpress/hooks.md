# WordPress Hooks (Actions)

## Overview

Beer Journal uses WordPress actions (hooks) throughout the codebase. This document lists all actions provided by the plugin.

## Plugin Lifecycle Hooks

### Activation

**Hook**: `bj_plugin_activated`

**Fired**: On plugin activation

**Parameters**: None

**Usage**:
```php
add_action('bj_plugin_activated', function() {
    // Custom activation logic
});
```

---

### Deactivation

**Hook**: `bj_plugin_deactivated`

**Fired**: On plugin deactivation

**Parameters**: None

**Usage**:
```php
add_action('bj_plugin_deactivated', function() {
    // Custom deactivation logic
});
```

---

## Import Hooks

### Before Check-in Import

**Hook**: `bj_before_checkin_import`

**Fired**: Before importing a single check-in

**Parameters**:
- `$data` (array): Check-in data to import

**Usage**:
```php
add_action('bj_before_checkin_import', function($data) {
    // Modify data before import
    $data['custom_field'] = 'value';
});
```

---

### After Check-in Imported

**Hook**: `bj_after_checkin_imported`

**Fired**: After a check-in is successfully imported

**Parameters**:
- `$post_id` (int): WordPress post ID
- `$data` (array): Imported data

**Usage**:
```php
add_action('bj_after_checkin_imported', function($post_id, $data) {
    // Send notification, update external system, etc.
    wp_mail('admin@example.com', 'New Check-in', 'Check-in imported: ' . $data['beer_name']);
});
```

---

### After Batch Import

**Hook**: `bj_after_batch_import`

**Fired**: After a batch of check-ins is imported

**Parameters**:
- `$count` (int): Number of check-ins imported
- `$imported_ids` (array): Array of post IDs

**Usage**:
```php
add_action('bj_after_batch_import', function($count, $imported_ids) {
    // Log batch completion
    error_log("Imported {$count} check-ins");
});
```

---

## RSS Sync Hooks

### Before RSS Sync

**Hook**: `bj_before_rss_sync`

**Fired**: Before RSS synchronization starts

**Parameters**: None

**Usage**:
```php
add_action('bj_before_rss_sync', function() {
    // Prepare for sync
    update_option('bj_sync_in_progress', true);
});
```

---

### After RSS Sync

**Hook**: `bj_after_rss_sync`

**Fired**: After RSS synchronization completes

**Parameters**:
- `$imported_count` (int): Number of check-ins imported

**Usage**:
```php
add_action('bj_after_rss_sync', function($imported_count) {
    // Cleanup, notifications, etc.
    update_option('bj_sync_in_progress', false);
});
```

---

## Scraping Hooks

### Before Scraping

**Hook**: `bj_before_scraping`

**Fired**: Before scraping a check-in page

**Parameters**:
- `$url` (string): Check-in URL to scrape

**Usage**:
```php
add_action('bj_before_scraping', function($url) {
    // Log scraping attempt
    error_log("Scraping: {$url}");
});
```

---

### After Scraping

**Hook**: `bj_after_scraping`

**Fired**: After scraping completes (success or failure)

**Parameters**:
- `$url` (string): Check-in URL
- `$data` (array|WP_Error): Scraped data or error

**Usage**:
```php
add_action('bj_after_scraping', function($url, $data) {
    if (is_wp_error($data)) {
        error_log("Scraping failed: {$url}");
    }
});
```

---

## Frontend Hooks

### Before Check-ins List

**Hook**: `bj_before_checkins_list`

**Fired**: Before the check-ins list is displayed

**Parameters**:
- `$query` (WP_Query): The query object

**Usage**:
```php
add_action('bj_before_checkins_list', function($query) {
    // Add custom header
    echo '<div class="custom-header">Total: ' . $query->found_posts . '</div>';
});
```

---

### After Check-in Card

**Hook**: `bj_after_checkin_card`

**Fired**: After each check-in card is displayed

**Parameters**:
- `$post_id` (int): Post ID

**Usage**:
```php
add_action('bj_after_checkin_card', function($post_id) {
    // Add custom content after each card
    echo '<div class="custom-footer">Custom Footer</div>';
});
```

---

### Before Single Check-in

**Hook**: `bj_before_single_checkin`

**Fired**: Before single check-in template loads

**Parameters**:
- `$post_id` (int): Post ID

**Usage**:
```php
add_action('bj_before_single_checkin', function($post_id) {
    // Add custom content before single view
});
```

---

### After Single Check-in

**Hook**: `bj_after_single_checkin`

**Fired**: After single check-in template loads

**Parameters**:
- `$post_id` (int): Post ID

**Usage**:
```php
add_action('bj_after_single_checkin', function($post_id) {
    // Add custom content after single view
});
```

---

## Image Hooks

### Before Image Download

**Hook**: `bj_before_image_download`

**Fired**: Before downloading an image

**Parameters**:
- `$url` (string): Image URL
- `$post_id` (int): Post ID

**Usage**:
```php
add_action('bj_before_image_download', function($url, $post_id) {
    // Log image download
    error_log("Downloading image for post {$post_id}: {$url}");
});
```

---

### After Image Downloaded

**Hook**: `bj_after_image_downloaded`

**Fired**: After image is downloaded and imported

**Parameters**:
- `$attachment_id` (int): Attachment ID
- `$post_id` (int): Post ID
- `$url` (string): Original image URL

**Usage**:
```php
add_action('bj_after_image_downloaded', function($attachment_id, $post_id, $url) {
    // Process downloaded image
    update_post_meta($attachment_id, 'custom_meta', 'value');
});
```

---

## Taxonomy Hooks

### Before Term Created

**Hook**: `bj_before_term_created`

**Fired**: Before creating a taxonomy term

**Parameters**:
- `$term_name` (string): Term name
- `$taxonomy` (string): Taxonomy name

**Usage**:
```php
add_action('bj_before_term_created', function($term_name, $taxonomy) {
    // Normalize term name
    return sanitize_title($term_name);
});
```

---

### After Term Created

**Hook**: `bj_after_term_created`

**Fired**: After a taxonomy term is created

**Parameters**:
- `$term_id` (int): Term ID
- `$term_name` (string): Term name
- `$taxonomy` (string): Taxonomy name

**Usage**:
```php
add_action('bj_after_term_created', function($term_id, $term_name, $taxonomy) {
    // Log new term creation
    error_log("Created term: {$term_name} ({$taxonomy})");
});
```

---

## Settings Hooks

### Before Settings Save

**Hook**: `bj_before_settings_save`

**Fired**: Before settings are saved

**Parameters**:
- `$settings` (array): Settings array

**Usage**:
```php
add_action('bj_before_settings_save', function($settings) {
    // Validate settings
    if (empty($settings['rss_feed_url'])) {
        return new WP_Error('missing_url', 'RSS feed URL required');
    }
});
```

---

### After Settings Save

**Hook**: `bj_after_settings_save`

**Fired**: After settings are saved

**Parameters**:
- `$settings` (array): Saved settings

**Usage**:
```php
add_action('bj_after_settings_save', function($settings) {
    // Clear cache, update schedules, etc.
    wp_clear_scheduled_hook('bj_rss_sync');
});
```

---

## Complete Action List

### Import Actions
- `bj_before_checkin_import` - Before importing check-in
- `bj_after_checkin_imported` - After check-in imported
- `bj_after_batch_import` - After batch import

### RSS Sync Actions
- `bj_before_rss_sync` - Before RSS sync
- `bj_after_rss_sync` - After RSS sync

### Scraping Actions
- `bj_before_scraping` - Before scraping
- `bj_after_scraping` - After scraping

### Frontend Actions
- `bj_before_checkins_list` - Before check-ins list
- `bj_after_checkin_card` - After check-in card
- `bj_before_single_checkin` - Before single check-in
- `bj_after_single_checkin` - After single check-in

### Image Actions
- `bj_before_image_download` - Before image download
- `bj_after_image_downloaded` - After image downloaded

### Taxonomy Actions
- `bj_before_term_created` - Before term created
- `bj_after_term_created` - After term created

### Settings Actions
- `bj_before_settings_save` - Before settings save
- `bj_after_settings_save` - After settings save

### Plugin Lifecycle Actions
- `bj_plugin_activated` - Plugin activated
- `bj_plugin_deactivated` - Plugin deactivated

## Related Documentation

- [Filters](filters.md)
- [Hooks and Filters (Frontend)](../frontend/hooks-filters.md)

