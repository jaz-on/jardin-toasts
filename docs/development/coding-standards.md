# Coding Standards

## Overview

Beer Journal follows WordPress Coding Standards (WPCS) with additional project-specific guidelines.

## WordPress Coding Standards

### PHP Standards

**Standard**: WordPress-Extra and WordPress-Docs

**Tools**:
- PHP_CodeSniffer with WPCS
- PHPStan Level 5

**Installation**:
```bash
composer require --dev wp-coding-standards/wpcs
```

---

### Code Style

#### Indentation

- **Tabs**: Use tabs, not spaces
- **Size**: 1 tab = 4 spaces (display)

#### Line Endings

- **Unix**: LF (Line Feed)
- **No Windows**: No CRLF

#### Line Length

- **Soft Limit**: 80 characters
- **Hard Limit**: 120 characters

---

## Naming Conventions

### Functions

**Prefix**: `bj_` for public functions

**Format**: lowercase with underscores

**Examples**:
```php
bj_get_checkin_data($post_id)
bj_display_rating($post_id)
bj_rating_stars($rating)
```

---

### Classes

**Prefix**: `BJ_` for all classes

**Format**: PascalCase

**Examples**:
```php
class BJ_Importer {}
class BJ_RSS_Parser {}
class BJ_Scraper {}
```

---

### Constants

**Prefix**: `BJ_`

**Format**: UPPERCASE with underscores

**Examples**:
```php
BJ_VERSION
BJ_PLUGIN_DIR
BJ_PLUGIN_URL
```

---

### Variables

**Format**: lowercase with underscores

**Examples**:
```php
$checkin_data
$beer_name
$rating_raw
```

---

## Code Structure

### File Headers

**Required**: All PHP files must include header

**Format**:
```php
<?php
/**
 * File: class-importer.php
 * 
 * @package Beer_Journal
 * @subpackage Includes
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
```

---

### Function Documentation

**Required**: PHPDoc for all public functions

**Format**:
```php
/**
 * Display rating with stars and optional label
 * 
 * @param int  $post_id    Post ID
 * @param bool $show_label Show custom label
 * @param bool $show_raw   Show original rating in tooltip
 * @return string HTML output
 */
function bj_display_rating($post_id, $show_label = true, $show_raw = true) {
    // Implementation
}
```

---

## Security Standards

### Input Sanitization

**Always Sanitize**:
```php
// Text
$text = sanitize_text_field($_POST['text']);

// Rich text
$content = wp_kses_post($_POST['content']);

// URL
$url = esc_url_raw($_POST['url']);

// Email
$email = sanitize_email($_POST['email']);

// Numbers
$number = absint($_POST['number']);
$float = floatval($_POST['float']);
```

---

### Output Escaping

**Always Escape**:
```php
// HTML
echo esc_html($text);

// Attributes
echo '<div class="' . esc_attr($class) . '">';

// URLs
echo '<a href="' . esc_url($url) . '">';

// JavaScript
echo '<script>var data = ' . wp_json_encode($data) . ';</script>';
```

---

### Nonces

**Required**: All forms and AJAX

```php
// Forms
wp_nonce_field('bj_action', 'bj_nonce');

// AJAX
check_ajax_referer('bj_action', 'nonce');

// Admin
check_admin_referer('bj_action', 'bj_nonce');
```

---

### Capability Checks

**Required**: All admin actions

```php
if (!current_user_can('manage_options')) {
    wp_die(__('Insufficient permissions', 'beer-journal'));
}
```

---

## Internationalization

### All User-Facing Strings

**Required**: Must be translatable

```php
// Simple
__('Beer Check-ins', 'beer-journal')

// Echo
_e('Settings', 'beer-journal')

// Escaped
esc_html__('Rating System', 'beer-journal')

// Plural
_n('%s check-in', '%s check-ins', $count, 'beer-journal')

// Context
_x('Brewery', 'taxonomy name', 'beer-journal')
```

---

## Database Queries

### Use WordPress Functions

**Preferred**:
```php
$posts = get_posts([
    'post_type' => 'beer',
    'posts_per_page' => 10,
]);
```

**Avoid**:
```php
// Direct SQL (unless necessary)
$wpdb->get_results("SELECT * FROM ...");
```

---

### Prepared Statements

**Required**: If using direct SQL

```php
$wpdb->prepare(
    "SELECT * FROM {$wpdb->posts} WHERE post_type = %s",
    'beer'
);
```

---

## Error Handling

### Use WP_Error

**Preferred**:
```php
if ($error) {
    return new WP_Error('error_code', __('Error message', 'beer-journal'));
}
```

---

### Logging

**Use WordPress Functions**:
```php
error_log('Beer Journal: Error message');
```

---

## Performance

### Caching

**Use Transients**:
```php
$data = get_transient('bj_cache_key');
if (false === $data) {
    $data = expensive_operation();
    set_transient('bj_cache_key', $data, HOUR_IN_SECONDS);
}
```

---

### Database Queries

**Optimize**:
```php
$args = [
    'update_post_meta_cache' => true,
    'update_post_term_cache' => true,
];
```

---

## Validation

### PHPCS

**Run Before Commit**:
```bash
composer phpcs
```

---

### PHPStan

**Run Static Analysis**:
```bash
composer phpstan
```

---

## Related Documentation

- [Architecture Rules](../.cursor/rules/architecture.mdc)
- [Testing](testing.md)

