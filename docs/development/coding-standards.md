# Coding Standards

## Overview

Jardin Toasts follows WordPress Coding Standards (WPCS) with additional project-specific guidelines.

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

**Prefix**: `jb_` for public functions

**Format**: lowercase with underscores

**Examples**:
```php
jb_get_checkin_data($post_id)
jb_display_rating($post_id)
jb_rating_stars($rating)
```

---

### Classes

**Prefix**: `JB_` for all classes

**Format**: PascalCase

**Examples**:
```php
class JB_Importer {}
class JB_RSS_Parser {}
class JB_Scraper {}
```

---

### Constants

**Prefix**: `JB_`

**Format**: UPPERCASE with underscores

**Examples**:
```php
JB_VERSION
JB_PLUGIN_DIR
JB_PLUGIN_URL
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
 * @package JardinToasts
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
function jb_display_rating($post_id, $show_label = true, $show_raw = true) {
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
wp_nonce_field('jb_action', 'jb_nonce');

// AJAX
check_ajax_referer('jb_action', 'nonce');

// Admin
check_admin_referer('jb_action', 'jb_nonce');
```

---

### Capability Checks

**Required**: All admin actions

```php
if (!current_user_can('manage_options')) {
    wp_die(__('Insufficient permissions', 'jardin-toasts'));
}
```

---

## Internationalization

### All User-Facing Strings

**Required**: Must be translatable

```php
// Simple
__('Beer Check-ins', 'jardin-toasts')

// Echo
_e('Settings', 'jardin-toasts')

// Escaped
esc_html__('Rating System', 'jardin-toasts')

// Plural
_n('%s check-in', '%s check-ins', $count, 'jardin-toasts')

// Context
_x('Brewery', 'taxonomy name', 'jardin-toasts')
```

---

## Database Queries

### Use WordPress Functions

**Preferred**:
```php
$posts = get_posts([
    'post_type' => 'beer_checkin',
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
    return new WP_Error('error_code', __('Error message', 'jardin-toasts'));
}
```

---

### Logging

**Use WordPress Functions**:
```php
error_log('Jardin Toasts: Error message');
```

---

## Performance

### Caching

**Use Transients**:
```php
$data = get_transient('jb_cache_key');
if (false === $data) {
    $data = expensive_operation();
    set_transient('jb_cache_key', $data, HOUR_IN_SECONDS);
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

- [Architecture Rules](../../.cursor/rules/architecture.mdc)
- [Testing](testing.md)

