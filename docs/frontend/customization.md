# Template Customization

## Overview

Complete guide to customizing Jardin Toasts templates and functionality.

## Template Override

### Theme Override

**Location**: Theme directory

**Structure**:
```
/wp-content/themes/{theme}/
├── jardin-toasts/
│   ├── archive-beer.php
│   ├── single-beer.php
│   └── partials/
│       └── checkin-card.php
```

**Priority**: Theme templates override plugin templates

---

## Hooks and Filters

### Actions

**Before Check-ins List**:
```php
add_action('jb_before_checkins_list', function($query) {
    // Add custom content
});
```

**After Check-in Card**:
```php
add_action('jb_after_checkin_card', function($post_id) {
    // Add custom content
});
```

---

### Filters

**Template Path**:
```php
add_filter('jb_checkin_template', function($template, $post_id) {
    // Use custom template
    return $template;
}, 10, 2);
```

**Check-in Data**:
```php
add_filter('jb_checkin_data', function($data, $post_id) {
    // Modify data
    return $data;
}, 10, 2);
```

---

## CSS Customization

### Override Styles

**Add to Theme**:
```css
/* Override plugin styles */
.jb-checkin-card {
    border: 2px solid #your-color;
}
```

---

### CSS Variables

**Use Plugin Variables**:
```css
.jb-custom {
    color: var(--jb-primary-color);
    border-color: var(--jb-border-color);
}
```

---

## Related Documentation

- [Hooks and Filters](hooks-filters.md)
- [Templates](templates.md)
- [Template Tags](template-tags.md)

