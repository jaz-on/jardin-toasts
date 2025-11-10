# Rating System - Detailed

## Overview

Comprehensive documentation of the rating system: storage, mapping, labels, and display.

## Storage Architecture

### Dual Storage

**Raw Rating** (`_bj_rating_raw`):
- Original Untappd rating
- Format: Float (0-5 with decimals)
- Example: `4.25`
- Purpose: Preserve original data

**Rounded Rating** (`_bj_rating_rounded`):
- Mapped star rating
- Format: Integer (0-5)
- Example: `4`
- Purpose: Display and filtering

---

### Storage Implementation

```php
// Store both values
update_post_meta($post_id, '_bj_rating_raw', 4.25);
update_post_meta($post_id, '_bj_rating_rounded', 4);
```

---

## Mapping Rules

### Default Mapping

```
0.0 - 0.9  →  0 stars
1.0 - 1.9  →  1 star
2.0 - 2.9  →  2 stars
3.0 - 3.4  →  3 stars
3.5 - 4.4  →  4 stars
4.5 - 5.0  →  5 stars
```

---

### Mapping Function

```php
function bj_map_rating($raw_rating) {
    $rules = get_option('bj_rating_rules', bj_get_default_rating_rules());
    
    foreach ($rules as $rule) {
        if ($raw_rating >= $rule['min'] && $raw_rating <= $rule['max']) {
            return $rule['round'];
        }
    }
    
    // Fallback
    return round($raw_rating);
}
```

---

### Custom Mapping

**User Configuration**:
```php
$custom_rules = [
    ['min' => 0.0, 'max' => 1.4, 'round' => 1],  // More lenient
    ['min' => 1.5, 'max' => 2.4, 'round' => 2],
    ['min' => 2.5, 'max' => 3.4, 'round' => 3],
    ['min' => 3.5, 'max' => 4.4, 'round' => 4],
    ['min' => 4.5, 'max' => 5.0, 'round' => 5],
];
update_option('bj_rating_rules', $custom_rules);
```

---

## Rating Labels

### Default Labels

```php
$default_labels = [
    0 => __('Undrinkable - Not even beer', 'beer-journal'),
    1 => __('Terrible - Only if there\'s no alternative', 'beer-journal'),
    2 => __('Mediocre - Meh, it\'s okay I guess', 'beer-journal'),
    3 => __('Decent - A solid thirst quencher', 'beer-journal'),
    4 => __('Great - Now we\'re talking! A real pleasure', 'beer-journal'),
    5 => __('Exceptional - Buy it with your eyes closed. Masterpiece!', 'beer-journal'),
];
```

---

### Custom Labels

**User Configuration**:
```php
$custom_labels = [
    0 => 'Dégueulasse, à fuir comme la peste',
    1 => 'Soit je ne pouvais pas refuser, soit j\'étais ivre',
    2 => 'Ça passe quand y\'a pas d\'alternative',
    3 => 'Ok là ça commence à être okay',
    4 => 'Ah bah voilà, ça c\'est de la bière !',
    5 => 'Tu veux te faire plaisir ? Achète les yeux fermés !',
];
update_option('bj_rating_labels', $custom_labels);
```

---

## Display Functions

### Main Display Function

```php
function bj_display_rating($post_id, $show_label = true, $show_raw = true) {
    $raw = get_post_meta($post_id, '_bj_rating_raw', true);
    $rounded = get_post_meta($post_id, '_bj_rating_rounded', true);
    $labels = get_option('bj_rating_labels', []);
    
    if (empty($rounded) && $rounded !== '0') {
        return '';
    }
    
    $output = '<div class="bj-rating">';
    
    // Stars
    $stars = str_repeat('⭐', $rounded);
    if ($show_raw && $raw != $rounded && !empty($raw)) {
        $output .= sprintf(
            '<span class="bj-stars" title="%s">%s</span>',
            esc_attr(sprintf(__('Original rating: %s', 'beer-journal'), $raw)),
            $stars
        );
    } else {
        $output .= '<span class="bj-stars">' . $stars . '</span>';
    }
    
    // Label
    if ($show_label && !empty($labels[$rounded])) {
        $output .= '<p class="bj-rating-label">' . esc_html($labels[$rounded]) . '</p>';
    }
    
    $output .= '</div>';
    
    return apply_filters('bj_rating_display', $output, $post_id, $raw, $rounded);
}
```

---

## Filtering and Sorting

### Filter by Rating

```php
$args = [
    'post_type' => 'beer',
    'meta_query' => [
        [
            'key' => '_bj_rating_rounded',
            'value' => 4,
            'compare' => '>=',
        ],
    ],
];
$checkins = get_posts($args);
```

---

### Sort by Rating

```php
$args = [
    'post_type' => 'beer',
    'meta_key' => '_bj_rating_rounded',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
];
$checkins = get_posts($args);
```

---

## Related Documentation

- [Rating System Architecture](../architecture/rating-system.md)
- [Rating Configuration Flow](../user-flows/rating-configuration.md)

