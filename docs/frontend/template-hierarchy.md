# Template Hierarchy

## Overview

Jardin Toasts follows WordPress template hierarchy conventions. Templates can be overridden by themes in a specific order of precedence.

## Template Search Order

WordPress searches for templates in this order (first match wins):

### Archive Templates

1. `jardin-toasts/archive-beer.php` (theme)
2. `archive-beer.php` (theme)
3. `archive.php` (theme - fallback)
4. `index.php` (theme - final fallback)
5. `public/templates/archive-beer.php` (plugin default)

### Single Templates

1. `jardin-toasts/single-beer.php` (theme)
2. `single-beer.php` (theme)
3. `single.php` (theme - fallback)
4. `singular.php` (theme - fallback)
5. `index.php` (theme - final fallback)
6. `public/templates/single-beer.php` (plugin default)

### Taxonomy Templates

#### Beer Style
1. `jardin-toasts/taxonomy-beer-style.php` (theme)
2. `taxonomy-beer-style.php` (theme)
3. `taxonomy.php` (theme - fallback)
4. `archive.php` (theme - fallback)
5. `index.php` (theme - final fallback)
6. `public/templates/taxonomy-beer-style.php` (plugin default)

#### Brewery
1. `jardin-toasts/taxonomy-brewery.php` (theme)
2. `taxonomy-brewery.php` (theme)
3. `taxonomy.php` (theme - fallback)
4. `archive.php` (theme - fallback)
5. `index.php` (theme - final fallback)
6. `public/templates/taxonomy-brewery.php` (plugin default)

#### Venue
1. `jardin-toasts/taxonomy-venue.php` (theme)
2. `taxonomy-venue.php` (theme)
3. `taxonomy.php` (theme - fallback)
4. `archive.php` (theme - fallback)
5. `index.php` (theme - final fallback)
6. `public/templates/taxonomy-venue.php` (plugin default)

## Template Location Priority

```
Theme Directory (Highest Priority)
├── jardin-toasts/
│   ├── archive-beer.php
│   ├── single-beer.php
│   ├── taxonomy-beer-style.php
│   ├── taxonomy-brewery.php
│   └── taxonomy-venue.php
│
├── archive-beer.php
├── single-beer.php
├── taxonomy-beer-style.php
├── taxonomy-brewery.php
└── taxonomy-venue.php

Plugin Directory (Lowest Priority - Default)
└── public/templates/
    ├── archive-beer_checkin.php
    ├── single-beer_checkin.php
    ├── taxonomy-beer-style.php
    ├── taxonomy-brewery.php
    └── taxonomy-venue.php
```

## Implementation

### Template Loading

WordPress automatically handles template hierarchy. The plugin registers the Custom Post Type and taxonomies, and WordPress searches for templates accordingly.

### Custom Template Filter

The plugin provides a filter to customize template loading:

```php
/**
 * Filter template path
 * 
 * @param string $template Template path
 * @param int    $post_id  Post ID
 */
$template = apply_filters('jb_checkin_template', $template, $post_id);
```

### Example: Force Custom Template

```php
add_filter('jb_checkin_template', function($template, $post_id) {
    // Use custom template for specific check-ins
    if (get_post_meta($post_id, '_jb_rating_rounded', true) >= 4) {
        return locate_template('jardin-toasts/single-featured.php');
    }
    return $template;
}, 10, 2);
```

## Partial Templates

Partials follow a similar hierarchy:

### Check-in Card Partial

1. `jardin-toasts/partials/checkin-card.php` (theme)
2. `partials/checkin-card.php` (theme)
3. `public/partials/checkin-card.php` (plugin default)

### Rating Stars Partial

1. `jardin-toasts/partials/rating-stars.php` (theme)
2. `partials/rating-stars.php` (theme)
3. `public/partials/rating-stars.php` (plugin default)

## Template Functions

### Locate Template

```php
/**
 * Locate template file
 * 
 * @param string $template_name Template name
 * @return string Template path
 */
function jb_locate_template($template_name) {
    // Check theme first
    $theme_template = locate_template([
        "jardin-toasts/{$template_name}",
        $template_name,
    ]);
    
    if ($theme_template) {
        return $theme_template;
    }
    
    // Fallback to plugin
    $plugin_template = plugin_dir_path(__FILE__) . "public/templates/{$template_name}";
    if (file_exists($plugin_template)) {
        return $plugin_template;
    }
    
    return '';
}
```

### Load Template

```php
/**
 * Load template file
 * 
 * @param string $template_name Template name
 * @param array  $args          Variables to pass to template
 */
function jb_get_template($template_name, $args = []) {
    $template = jb_locate_template($template_name);
    
    if (!$template) {
        return;
    }
    
    // Extract args for template
    extract($args);
    
    // Load template
    include $template;
}
```

## Best Practices

### Theme Override Structure

Organize theme overrides in a dedicated folder:

```
/wp-content/themes/{theme}/
├── jardin-toasts/
│   ├── archive-beer.php
│   ├── single-beer.php
│   ├── taxonomy-beer-style.php
│   ├── taxonomy-brewery.php
│   ├── taxonomy-venue.php
│   └── partials/
│       ├── checkin-card.php
│       └── rating-stars.php
```

### Maintain Plugin Updates

When overriding templates:
- Keep a copy of original plugin templates
- Test after plugin updates
- Use hooks/filters when possible instead of template overrides

### Child Theme Support

Child themes inherit parent theme templates:

```
Child Theme
└── jardin-toasts/
    └── archive-beer.php (overrides parent)

Parent Theme
└── jardin-toasts/
    └── archive-beer.php (overridden by child)
```

## Related Documentation

- [Templates](templates.md)
- [Template Tags](template-tags.md)
- [Hooks and Filters](hooks-filters.md)

