# WordPress Filters

## Overview

Beer Journal provides numerous filters for customizing data, templates, and behavior. All filters are prefixed with `bj_`.

Note: Pour éviter les divergences, la source de vérité des filtres frontend est `docs/frontend/hooks-filters.md`. Cette page référence et complète, sans dupliquer toute la matière.

## Template Filters

### Check-in Template

**Filter**: `bj_checkin_template`

**Parameters**:
- `$template` (string): Template path
- `$post_id` (int): Post ID

**Usage**:
```php
add_filter('bj_checkin_template', function($template, $post_id) {
    // Use custom template for high-rated check-ins
    if (bj_get_rating($post_id, false) >= 4) {
        return locate_template('beer-journal/single-featured.php');
    }
    return $template;
}, 10, 2);
```

---

### Check-in CSS Classes

**Filter**: `bj_checkin_classes`

**Parameters**:
- `$classes` (array): Array of CSS classes
- `$post_id` (int): Post ID

**Usage**:
```php
add_filter('bj_checkin_classes', function($classes, $post_id) {
    // Add custom class for high-rated check-ins
    if (bj_get_rating($post_id, false) >= 4) {
        $classes[] = 'bj-featured';
    }
    return $classes;
}, 10, 2);
```

---

### Check-in Data

**Filter**: `bj_checkin_data`

**Parameters**:
- `$data` (array): Check-in data
- `$post_id` (int): Post ID

**Usage**:
```php
add_filter('bj_checkin_data', function($data, $post_id) {
    // Modify beer name
    $data['beer_name'] = strtoupper($data['beer_name']);
    return $data;
}, 10, 2);
```

---

## Rating Filters

### Rating Display

**Filter**: `bj_rating_display`

**Parameters**:
- `$output` (string): HTML output
- `$post_id` (int): Post ID
- `$raw` (float): Raw rating
- `$rounded` (int): Rounded rating

**Usage**:
```php
add_filter('bj_rating_display', function($output, $post_id, $raw, $rounded) {
    // Replace stars with custom icons
    $stars = str_repeat('🍺', $rounded);
    return '<div class="custom-rating">' . $stars . '</div>';
}, 10, 4);
```

---

### Rating Mapping

**Filter**: `bj_rating_mapped`

**Parameters**:
- `$rounded` (int): Rounded rating
- `$raw_rating` (float): Raw rating

**Usage**:
```php
add_filter('bj_rating_mapped', function($rounded, $raw_rating) {
    // Custom mapping: round up instead of down
    return ceil($raw_rating);
}, 10, 2);
```

---

## Data Filters

### Default RSS feed URL

**Filter**: `bj_default_rss_feed_url`

**Parameters**:
- `$url` (string): Default Untappd RSS URL used when `bj_rss_feed_url` has never been saved, or when overriding via `BJ_RSS_FEED_URL` in `wp-config.php`.

**Usage**:
```php
add_filter( 'bj_default_rss_feed_url', function ( $url ) {
	return 'https://untappd.com/rss/user/yourname';
} );
```

---

### RSS Item Parsed

**Filter**: `bj_rss_item_parsed`

**Parameters**:
- `$item` (array): Parsed RSS item data

**Usage**:
```php
add_filter('bj_rss_item_parsed', function($item) {
    // Modify parsed RSS item
    $item['custom_field'] = 'value';
    return $item;
});
```

---

### Scraped Data

**Filter**: `bj_scraped_data`

**Parameters**:
- `$data` (array): Scraped data

**Usage**:
```php
add_filter('bj_scraped_data', function($data) {
    // Add custom field to scraped data
    $data['custom_field'] = 'value';
    return $data;
});
```

---

### Beer Name

**Filter**: `bj_beer_name`

**Parameters**:
- `$beer_name` (string): Beer name
- `$post_id` (int): Post ID

**Usage**:
```php
add_filter('bj_beer_name', function($beer_name, $post_id) {
    // Modify beer name
    return strtoupper($beer_name);
}, 10, 2);
```

---

### Brewery Name

**Filter**: `bj_brewery_name`

**Parameters**:
- `$brewery_name` (string): Brewery name
- `$post_id` (int): Post ID

**Usage**:
```php
add_filter('bj_brewery_name', function($brewery_name, $post_id) {
    // Modify brewery name
    return $brewery_name;
}, 10, 2);
```

---

## Query Filters

### Archive Query Args

**Filter**: `bj_archive_query_args`

**Parameters**:
- `$args` (array): WP_Query arguments

**Usage**:
```php
add_filter('bj_archive_query_args', function($args) {
    // Only show check-ins with 3+ stars
    $args['meta_query'][] = [
        'key' => '_bj_rating_rounded',
        'value' => 3,
        'compare' => '>=',
    ];
    return $args;
});
```

---

### Single Query Args

**Filter**: `bj_single_query_args`

**Parameters**:
- `$args` (array): WP_Query arguments

**Usage**:
```php
add_filter('bj_single_query_args', function($args) {
    // Modify single check-in query
    return $args;
});
```

---

## Image Filters

### Image URL

**Filter**: `bj_image_url`

**Parameters**:
- `$url` (string): Image URL

**Usage**:
```php
add_filter('bj_image_url', function($url) {
    // Modify image URL before download
    return $url;
});
```

---

### Image Alt Text

**Filter**: `bj_image_alt_text`

**Parameters**:
- `$alt_text` (string): Alt text
- `$post_id` (int): Post ID

**Usage**:
```php
add_filter('bj_image_alt_text', function($alt_text, $post_id) {
    // Customize alt text
    return $alt_text . ' - Beer Journal';
}, 10, 2);
```

---

## Settings Filters

### Settings Defaults

**Filter**: `bj_settings_defaults`

**Parameters**:
- `$defaults` (array): Default settings

**Usage**:
```php
add_filter('bj_settings_defaults', function($defaults) {
    // Modify default settings
    $defaults['sync_frequency'] = 'daily';
    return $defaults;
});
```

---

### Settings Validation

**Filter**: `bj_settings_validation`

**Parameters**:
- `$settings` (array): Settings to validate
- `$old_settings` (array): Previous settings

**Usage**:
```php
add_filter('bj_settings_validation', function($settings, $old_settings) {
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
- `bj_checkin_template` - Template path
- `bj_checkin_classes` - CSS classes
- `bj_checkin_data` - Check-in data

### Rating Filters
- `bj_rating_display` - Rating output
- `bj_rating_mapped` - Rating mapping

### Data Filters
- `bj_rss_item_parsed` - RSS item data
- `bj_scraped_data` - Scraped data
- `bj_beer_name` - Beer name
- `bj_brewery_name` - Brewery name

### Query Filters
- `bj_archive_query_args` - Archive query
- `bj_single_query_args` - Single query

### Image Filters
- `bj_image_url` - Image URL
- `bj_image_alt_text` - Alt text

### Settings Filters
- `bj_settings_defaults` - Default settings
- `bj_settings_validation` - Settings validation

## Related Documentation

- [Hooks](hooks.md)
- [Hooks and Filters (Frontend)](../frontend/hooks-filters.md)

