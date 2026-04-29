# WordPress Filters

## Overview

Jardin Toasts provides numerous filters for customizing data, templates, and behavior. All filters are prefixed with `jb_`.

Note: Pour éviter les divergences, la source de vérité des filtres frontend est `docs/frontend/hooks-filters.md`. Cette page référence et complète, sans dupliquer toute la matière.

## Template Filters

### Check-in Template

**Filter**: `jb_checkin_template`

**Parameters**:
- `$template` (string): Template path
- `$post_id` (int): Post ID

**Usage**:
```php
add_filter('jb_checkin_template', function($template, $post_id) {
    // Use custom template for high-rated check-ins
    if (jb_get_rating($post_id, false) >= 4) {
        return locate_template('jardin-toasts/single-featured.php');
    }
    return $template;
}, 10, 2);
```

---

### Check-in CSS Classes

**Filter**: `jb_checkin_classes`

**Parameters**:
- `$classes` (array): Array of CSS classes
- `$post_id` (int): Post ID

**Usage**:
```php
add_filter('jb_checkin_classes', function($classes, $post_id) {
    // Add custom class for high-rated check-ins
    if (jb_get_rating($post_id, false) >= 4) {
        $classes[] = 'jb-featured';
    }
    return $classes;
}, 10, 2);
```

---

### Check-in Data

**Filter**: `jb_checkin_data`

**Parameters**:
- `$data` (array): Check-in data
- `$post_id` (int): Post ID

**Usage**:
```php
add_filter('jb_checkin_data', function($data, $post_id) {
    // Modify beer name
    $data['beer_name'] = strtoupper($data['beer_name']);
    return $data;
}, 10, 2);
```

---

## Rating Filters

### Rating Display

**Filter**: `jb_rating_display`

**Parameters**:
- `$output` (string): HTML output
- `$post_id` (int): Post ID
- `$raw` (float): Raw rating
- `$rounded` (int): Rounded rating

**Usage**:
```php
add_filter('jb_rating_display', function($output, $post_id, $raw, $rounded) {
    // Replace stars with custom icons
    $stars = str_repeat('🍺', $rounded);
    return '<div class="custom-rating">' . $stars . '</div>';
}, 10, 4);
```

---

### Rating Mapping

**Filter**: `jb_rating_mapped`

**Parameters**:
- `$rounded` (int): Rounded rating
- `$raw_rating` (float): Raw rating

**Usage**:
```php
add_filter('jb_rating_mapped', function($rounded, $raw_rating) {
    // Custom mapping: round up instead of down
    return ceil($raw_rating);
}, 10, 2);
```

---

## Data Filters

### Default RSS feed URL

**Filter**: `jb_default_rss_feed_url`

**Parameters**:
- `$url` (string): Default Untappd RSS URL used when `jb_rss_feed_url` has never been saved, or when overriding via `JB_RSS_FEED_URL` in `wp-config.php`.

**Usage**:
```php
add_filter( 'jb_default_rss_feed_url', function ( $url ) {
	return 'https://untappd.com/rss/user/yourname';
} );
```

---

### RSS Item Parsed

**Filter**: `jb_rss_item_parsed`

**Parameters**:
- `$item` (array): Parsed RSS item data

**Usage**:
```php
add_filter('jb_rss_item_parsed', function($item) {
    // Modify parsed RSS item
    $item['custom_field'] = 'value';
    return $item;
});
```

---

### Scraped Data

**Filter**: `jb_scraped_data`

**Parameters**:
- `$data` (array): Scraped data

**Usage**:
```php
add_filter('jb_scraped_data', function($data) {
    // Add custom field to scraped data
    $data['custom_field'] = 'value';
    return $data;
});
```

---

### Beer Name

**Filter**: `jb_beer_name`

**Parameters**:
- `$beer_name` (string): Beer name
- `$post_id` (int): Post ID

**Usage**:
```php
add_filter('jb_beer_name', function($beer_name, $post_id) {
    // Modify beer name
    return strtoupper($beer_name);
}, 10, 2);
```

---

### Brewery Name

**Filter**: `jb_brewery_name`

**Parameters**:
- `$brewery_name` (string): Brewery name
- `$post_id` (int): Post ID

**Usage**:
```php
add_filter('jb_brewery_name', function($brewery_name, $post_id) {
    // Modify brewery name
    return $brewery_name;
}, 10, 2);
```

---

## Query Filters

### Archive Query Args

**Filter**: `jb_archive_query_args`

**Parameters**:
- `$args` (array): WP_Query arguments

**Usage**:
```php
add_filter('jb_archive_query_args', function($args) {
    // Only show check-ins with 3+ stars
    $args['meta_query'][] = [
        'key' => '_jb_rating_rounded',
        'value' => 3,
        'compare' => '>=',
    ];
    return $args;
});
```

---

### Single Query Args

**Filter**: `jb_single_query_args`

**Parameters**:
- `$args` (array): WP_Query arguments

**Usage**:
```php
add_filter('jb_single_query_args', function($args) {
    // Modify single check-in query
    return $args;
});
```

---

## Image Filters

### Image URL

**Filter**: `jb_image_url`

**Parameters**:
- `$url` (string): Image URL

**Usage**:
```php
add_filter('jb_image_url', function($url) {
    // Modify image URL before download
    return $url;
});
```

---

### Image Alt Text

**Filter**: `jb_image_alt_text`

**Parameters**:
- `$alt_text` (string): Alt text
- `$post_id` (int): Post ID

**Usage**:
```php
add_filter('jb_image_alt_text', function($alt_text, $post_id) {
    // Customize alt text
    return $alt_text . ' - Jardin Toasts';
}, 10, 2);
```

---

## Settings Filters

### Settings Defaults

**Filter**: `jb_settings_defaults`

**Parameters**:
- `$defaults` (array): Default settings

**Usage**:
```php
add_filter('jb_settings_defaults', function($defaults) {
    // Modify default settings
    $defaults['sync_frequency'] = 'daily';
    return $defaults;
});
```

---

### Settings Validation

**Filter**: `jb_settings_validation`

**Parameters**:
- `$settings` (array): Settings to validate
- `$old_settings` (array): Previous settings

**Usage**:
```php
add_filter('jb_settings_validation', function($settings, $old_settings) {
    // Validate settings
    if (empty($settings['rss_feed_url'])) {
        return new WP_Error('invalid_settings', 'RSS feed URL required');
    }
    return $settings;
}, 10, 2);
```

---

## Complete Filter List

### Template Filters
- `jb_checkin_template` - Template path
- `jb_checkin_classes` - CSS classes
- `jb_checkin_data` - Check-in data

### Rating Filters
- `jb_rating_display` - Rating output
- `jb_rating_mapped` - Rating mapping

### Data Filters
- `jb_rss_item_parsed` - RSS item data
- `jb_scraped_data` - Scraped data
- `jb_beer_name` - Beer name
- `jb_brewery_name` - Brewery name

### Query Filters
- `jb_archive_query_args` - Archive query
- `jb_single_query_args` - Single query

### Image Filters
- `jb_image_url` - Image URL
- `jb_image_alt_text` - Alt text

### Settings Filters
- `jb_settings_defaults` - Default settings
- `jb_settings_validation` - Settings validation

## Related Documentation

- [Hooks](hooks.md)
- [Hooks and Filters (Frontend)](../frontend/hooks-filters.md)

