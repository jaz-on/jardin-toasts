# Rating System

## Overview

The rating system manages how Untappd ratings (0-5 with decimals) are stored, mapped, and displayed. It supports customizable mapping rules and labels for each rating level.

## Architecture

### Dual Storage System

Ratings are stored in two forms:

1. **Raw Rating** (`_jb_rating_raw`): Original Untappd rating (0-5 with decimals)
   - Example: `4.25`
   - Type: `float`
   - Purpose: Preserve original data, display in tooltips

2. **Rounded Rating** (`_jb_rating_rounded`): Mapped star rating (0-5 stars)
   - Example: `4`
   - Type: `int`
   - Purpose: Display as stars, filtering, sorting

### Storage

```php
// Store both values
update_post_meta($post_id, '_jb_rating_raw', 4.25);
update_post_meta($post_id, '_jb_rating_rounded', 4);
```

## Mapping Rules

### Default Mapping

Untappd ratings (0-5 with decimals) are mapped to star ratings (0-5 stars):

```
0.0 - 0.9  →  0 stars ⭐
1.0 - 1.9  →  1 star  ⭐
2.0 - 2.9  →  2 stars ⭐⭐
3.0 - 3.4  →  3 stars ⭐⭐⭐
3.5 - 4.4  →  4 stars ⭐⭐⭐⭐
4.5 - 5.0  →  5 stars ⭐⭐⭐⭐⭐
```

### Implementation

```php
function jb_map_rating($raw_rating) {
    $rules = get_option('jb_rating_rules', jb_get_default_rating_rules());
    
    foreach ($rules as $rule) {
        if ($raw_rating >= $rule['min'] && $raw_rating <= $rule['max']) {
            return $rule['round'];
        }
    }
    
    // Fallback
    return round($raw_rating);
}

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

### Customizable Rules

Users can customize mapping rules in admin settings:

```php
// Get custom rules
$custom_rules = get_option('jb_rating_rules');

// Example custom rules
$custom_rules = [
    ['min' => 0.0, 'max' => 1.4, 'round' => 1],  // More lenient
    ['min' => 1.5, 'max' => 2.4, 'round' => 2],
    ['min' => 2.5, 'max' => 3.4, 'round' => 3],
    ['min' => 3.5, 'max' => 4.4, 'round' => 4],
    ['min' => 4.5, 'max' => 5.0, 'round' => 5],
];
update_option('jb_rating_rules', $custom_rules);
```

## Rating Labels

### Default Labels

Each rating level has a customizable label:

```php
$default_labels = [
    0 => __('Undrinkable - Not even beer', 'jardin-toasts'),
    1 => __('Terrible - Only if there\'s no alternative', 'jardin-toasts'),
    2 => __('Mediocre - Meh, it\'s okay I guess', 'jardin-toasts'),
    3 => __('Decent - A solid thirst quencher', 'jardin-toasts'),
    4 => __('Great - Now we\'re talking! A real pleasure', 'jardin-toasts'),
    5 => __('Exceptional - Buy it with your eyes closed. Masterpiece!', 'jardin-toasts'),
];
```

### Custom Labels

Users can customize labels in admin settings:

```php
// Get custom labels
$custom_labels = get_option('jb_rating_labels', $default_labels);

// Example French customization
$custom_labels = [
    0 => 'Dégueulasse, à fuir comme la peste',
    1 => 'Soit je ne pouvais pas refuser, soit j\'étais ivre',
    2 => 'Ça passe quand y\'a pas d\'alternative',
    3 => 'Ok là ça commence à être okay',
    4 => 'Ah bah voilà, ça c\'est de la bière !',
    5 => 'Tu veux te faire plaisir ? Achète les yeux fermés !',
];
update_option('jb_rating_labels', $custom_labels);
```

## Display Functions

### Template Tag

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
    $raw = get_post_meta($post_id, '_jb_rating_raw', true);
    $rounded = get_post_meta($post_id, '_jb_rating_rounded', true);
    $labels = get_option('jb_rating_labels', []);
    
    if (empty($rounded) && $rounded !== '0') {
        return '';
    }
    
    $output = '<div class="jb-rating">';
    
    // Stars
    $stars = str_repeat('⭐', $rounded);
    if ($show_raw && $raw != $rounded && !empty($raw)) {
        $output .= sprintf(
            '<span class="jb-stars" title="%s">%s</span>',
            esc_attr(sprintf(__('Original rating: %s', 'jardin-toasts'), $raw)),
            $stars
        );
    } else {
        $output .= '<span class="jb-stars">' . $stars . '</span>';
    }
    
    // Label
    if ($show_label && !empty($labels[$rounded])) {
        $output .= '<p class="jb-rating-label">' . esc_html($labels[$rounded]) . '</p>';
    }
    
    $output .= '</div>';
    
    return apply_filters('jb_rating_display', $output, $post_id, $raw, $rounded);
}
```

### Simple Star Display

```php
/**
 * Display stars only
 * 
 * @param int  $rating Rating (0-5)
 * @param bool $echo   Echo or return
 * @return string|void
 */
function jb_rating_stars($rating, $echo = true) {
    $stars = str_repeat('⭐', absint($rating));
    $output = '<span class="jb-stars">' . $stars . '</span>';
    
    if ($echo) {
        echo $output;
    } else {
        return $output;
    }
}
```

## Admin Interface

### Settings Page: Rating System

The admin interface allows users to:

1. **Enable/Disable Rating Rounding**
   - Toggle: `jb_rating_rounding_enabled`

2. **Display Original Rating in Tooltip**
   - Toggle: `jb_rating_show_raw_tooltip`

3. **Edit Mapping Rules** (Advanced)
   - Customize min/max ranges for each star level

4. **Customize Labels**
   - Text input for each rating level (0-5)
   - Max 500 characters per label

5. **Display Options**
   - Show labels on single check-in pages
   - Show labels in archive (grid cards)
   - Show labels in list view

### Settings Structure

```php
// Rating system settings
$rating_settings = [
    'rounding_enabled' => get_option('jb_rating_rounding_enabled', true),
    'show_raw_tooltip' => get_option('jb_rating_show_raw_tooltip', true),
    'rules' => get_option('jb_rating_rules', jb_get_default_rating_rules()),
    'labels' => get_option('jb_rating_labels', jb_get_default_rating_labels()),
    'display_single' => get_option('jb_rating_display_single', true),
    'display_archive' => get_option('jb_rating_display_archive', false),
    'display_list' => get_option('jb_rating_display_list', false),
];
```

## Filter Hooks

### Customize Rating Display

```php
/**
 * Filter rating display output
 * 
 * @param string $output  HTML output
 * @param int    $post_id Post ID
 * @param float  $raw     Raw rating
 * @param int    $rounded Rounded rating
 */
add_filter('jb_rating_display', function($output, $post_id, $raw, $rounded) {
    // Customize output
    return $output;
}, 10, 4);
```

### Customize Rating Mapping

```php
/**
 * Filter rating mapping
 * 
 * @param int   $rounded    Rounded rating
 * @param float $raw_rating Raw rating
 */
add_filter('jb_rating_mapped', function($rounded, $raw_rating) {
    // Custom mapping logic
    return $rounded;
}, 10, 2);
```

## Use Cases

### Filtering by Rating

```php
// Get check-ins with 4+ stars
$args = [
    'post_type' => 'beer',
    'meta_query' => [
        [
            'key' => '_jb_rating_rounded',
            'value' => 4,
            'compare' => '>=',
        ],
    ],
];
$checkins = get_posts($args);
```

### Sorting by Rating

```php
// Sort by rating (highest first)
$args = [
    'post_type' => 'beer',
    'meta_key' => '_jb_rating_rounded',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
];
$checkins = get_posts($args);
```

### Average Rating Calculation

```php
// Calculate average rating
function jb_get_average_rating() {
    global $wpdb;
    
    $avg = $wpdb->get_var("
        SELECT AVG(CAST(meta_value AS DECIMAL(3,2)))
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_jb_rating_raw'
        AND post_id IN (
            SELECT ID FROM {$wpdb->posts}
            WHERE post_type = 'beer'
            AND post_status = 'publish'
        )
    ");
    
    return round($avg, 2);
}
```

## Related Documentation

- [Import Process](import-process.md)
- [Template Tags](../frontend/template-tags.md)
- [Rating System Detailed](../features/rating-system-detailed.md)
- [Database Meta Fields](../db/meta-fields.md)

