# Hooks and Filters

## Overview

Beer Journal provides numerous hooks (actions) and filters for customization. All hooks are prefixed with `bj_` to avoid conflicts.

## Actions (Hooks)

Actions allow you to execute code at specific points in the plugin execution.

### Before Check-ins List

```php
/**
 * Fired before the check-ins list is displayed
 * 
 * @param WP_Query $query The query object
 */
do_action('bj_before_checkins_list', $query);
```

**Usage**:
```php
add_action('bj_before_checkins_list', function($query) {
    // Add custom content before list
    echo '<div class="custom-header">Custom Header</div>';
});
```

---

### After Check-in Card

```php
/**
 * Fired after each check-in card is displayed
 * 
 * @param int $post_id Post ID
 */
do_action('bj_after_checkin_card', $post_id);
```

**Usage**:
```php
add_action('bj_after_checkin_card', function($post_id) {
    // Add custom content after each card
    echo '<div class="custom-footer">Custom Footer</div>';
});
```

---

### After Check-in Imported

```php
/**
 * Fired after a check-in is imported
 * 
 * @param int   $post_id Post ID
 * @param array $data    Imported data
 */
do_action('bj_after_checkin_imported', $post_id, $data);
```

**Usage**:
```php
add_action('bj_after_checkin_imported', function($post_id, $data) {
    // Send notification, update external system, etc.
    wp_mail('admin@example.com', 'New Check-in', 'Check-in imported: ' . $data['beer_name']);
});
```

---

### After Batch Import

```php
/**
 * Fired after a batch of check-ins is imported
 * 
 * @param int   $count        Number of check-ins imported
 * @param array $imported_ids Array of post IDs
 */
do_action('bj_after_batch_import', $count, $imported_ids);
```

---

### Before RSS Sync

```php
/**
 * Fired before RSS sync starts
 */
do_action('bj_before_rss_sync');
```

---

### After RSS Sync

```php
/**
 * Fired after RSS sync completes
 * 
 * @param int $imported_count Number of check-ins imported
 */
do_action('bj_after_rss_sync', $imported_count);
```

---

## Filters

Filters allow you to modify data before it's used or displayed.

### Check-in Template

```php
/**
 * Filter template path
 * 
 * @param string $template Template path
 * @param int    $post_id  Post ID
 * @return string Modified template path
 */
apply_filters('bj_checkin_template', $template, $post_id);
```

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

```php
/**
 * Filter CSS classes for check-in
 * 
 * @param array $classes Array of CSS classes
 * @param int   $post_id Post ID
 * @return array Modified classes
 */
apply_filters('bj_checkin_classes', $classes, $post_id);
```

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

```php
/**
 * Filter check-in data before display
 * 
 * @param array $data    Check-in data
 * @param int   $post_id Post ID
 * @return array Modified data
 */
apply_filters('bj_checkin_data', $data, $post_id);
```

**Usage**:
```php
add_filter('bj_checkin_data', function($data, $post_id) {
    // Modify beer name
    $data['beer_name'] = strtoupper($data['beer_name']);
    return $data;
}, 10, 2);
```

---

### Rating Display

```php
/**
 * Filter rating display output
 * 
 * @param string $output  HTML output
 * @param int    $post_id Post ID
 * @param float  $raw     Raw rating
 * @param int    $rounded Rounded rating
 * @return string Modified output
 */
apply_filters('bj_rating_display', $output, $post_id, $raw, $rounded);
```

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

```php
/**
 * Filter rating mapping
 * 
 * @param int   $rounded    Rounded rating
 * @param float $raw_rating Raw rating
 * @return int Modified rounded rating
 */
apply_filters('bj_rating_mapped', $rounded, $raw_rating);
```

**Usage**:
```php
add_filter('bj_rating_mapped', function($rounded, $raw_rating) {
    // Custom mapping: round up instead of down
    return ceil($raw_rating);
}, 10, 2);
```

---

### RSS Item Parsed

```php
/**
 * Filter RSS item after parsing
 * 
 * @param array $item Parsed RSS item data
 * @return array Modified item data
 */
apply_filters('bj_rss_item_parsed', $item);
```

---

### Scraped Data

```php
/**
 * Filter scraped data before import
 * 
 * @param array $data Scraped data
 * @return array Modified data
 */
apply_filters('bj_scraped_data', $data);
```

**Usage**:
```php
add_filter('bj_scraped_data', function($data) {
    // Add custom field
    $data['custom_field'] = 'custom_value';
    return $data;
});
```

---

### Beer Name

```php
/**
 * Filter beer name
 * 
 * @param string $beer_name Beer name
 * @param int    $post_id   Post ID
 * @return string Modified beer name
 */
apply_filters('bj_beer_name', $beer_name, $post_id);
```

---

### Brewery Name

```php
/**
 * Filter brewery name
 * 
 * @param string $brewery_name Brewery name
 * @param int    $post_id      Post ID
 * @return string Modified brewery name
 */
apply_filters('bj_brewery_name', $brewery_name, $post_id);
```

---

### Archive Query Args

```php
/**
 * Filter archive query arguments
 * 
 * @param array $args WP_Query arguments
 * @return array Modified arguments
 */
apply_filters('bj_archive_query_args', $args);
```

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

### Image URL

```php
/**
 * Filter image URL before download
 * 
 * @param string $url Image URL
 * @return string Modified URL
 */
apply_filters('bj_image_url', $url);
```

---

## Complete Hook List

### Actions
- `bj_before_checkins_list` - Before check-ins list
- `bj_after_checkin_card` - After each check-in card
- `bj_after_checkin_imported` - After check-in imported
- `bj_after_batch_import` - After batch import
- `bj_before_rss_sync` - Before RSS sync
- `bj_after_rss_sync` - After RSS sync
- `bj_before_scraping` - Before scraping
- `bj_after_scraping` - After scraping

### Filters
- `bj_checkin_template` - Template path
- `bj_checkin_classes` - CSS classes
- `bj_checkin_data` - Check-in data
- `bj_rating_display` - Rating output
- `bj_rating_mapped` - Rating mapping
- `bj_rss_item_parsed` - RSS item data
- `bj_scraped_data` - Scraped data
- `bj_beer_name` - Beer name
- `bj_brewery_name` - Brewery name
- `bj_archive_query_args` - Archive query
- `bj_image_url` - Image URL

## Usage Examples

### Custom Archive Header

```php
add_action('bj_before_checkins_list', function($query) {
    $total = $query->found_posts;
    echo '<div class="bj-archive-header">';
    echo '<p>Total check-ins: ' . number_format($total) . '</p>';
    echo '</div>';
});
```

### Highlight High-Rated Check-ins

```php
add_filter('bj_checkin_classes', function($classes, $post_id) {
    $rating = bj_get_rating($post_id, false);
    if ($rating >= 4) {
        $classes[] = 'bj-high-rated';
    }
    return $classes;
}, 10, 2);
```

### Custom Rating Display

```php
add_filter('bj_rating_display', function($output, $post_id, $raw, $rounded) {
    // Use custom star icons
    $custom_stars = str_repeat('★', $rounded) . str_repeat('☆', 5 - $rounded);
    return '<div class="custom-rating">' . $custom_stars . '</div>';
}, 10, 4);
```

## Related Documentation

- [Templates](templates.md)
- [Template Tags](template-tags.md)
- [Styling](styling.md)

