# Frontend Assets

## Overview

Jardin Toasts enqueues CSS and JavaScript files for frontend functionality. All assets are properly versioned and can be dequeued if needed.

## CSS Files

### Main Stylesheet

**File**: `public/assets/css/public.css`

**Handle**: `jardin-toasts-public`

**Dependencies**: None

**Version**: Plugin version

**Enqueued**: On all frontend pages (archive, single, taxonomies)

**Purpose**: Main styling for check-ins display

---

## JavaScript Files

### Main JavaScript

**File**: `public/assets/js/public.js`

**Handle**: `jardin-toasts-public`

**Dependencies**: `jquery` (optional)

**Version**: Plugin version

**Enqueued**: On all frontend pages

**Purpose**: View toggle, filters, interactive features

**Features**:
- Grid/Table view toggle
- Filter interactions
- AJAX pagination (optional)
- Lazy loading (if implemented)

---

## Asset Enqueuing

### Automatic Enqueuing

Assets are automatically enqueued on relevant pages:

```php
add_action('wp_enqueue_scripts', 'jb_enqueue_public_assets');

function jb_enqueue_public_assets() {
    // Only on check-in pages
    if (!is_singular('beer') && !is_post_type_archive('beer') && !is_tax(['beer_style', 'brewery', 'venue'])) {
        return;
    }
    
    // Enqueue CSS
    wp_enqueue_style(
        'jardin-toasts-public',
        plugin_dir_url(__FILE__) . 'public/assets/css/public.css',
        [],
        JB_VERSION
    );
    
    // Enqueue JS
    wp_enqueue_script(
        'jardin-toasts-public',
        plugin_dir_url(__FILE__) . 'public/assets/js/public.js',
        ['jquery'],
        JB_VERSION,
        true
    );
    
    // Localize script
    wp_localize_script('jardin-toasts-public', 'bjData', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('jb_public_nonce'),
    ]);
}
```

### Conditional Enqueuing

Assets can be conditionally enqueued:

```php
// Only on archive pages
if (is_post_type_archive('beer')) {
    wp_enqueue_style('jardin-toasts-public');
}

// Only on single pages
if (is_singular('beer')) {
    wp_enqueue_style('jardin-toasts-public');
}
```

## Dequeuing Assets

### Remove Plugin Styles

```php
add_action('wp_enqueue_scripts', function() {
    wp_dequeue_style('jardin-toasts-public');
}, 100);
```

### Remove Plugin Scripts

```php
add_action('wp_enqueue_scripts', function() {
    wp_dequeue_script('jardin-toasts-public');
}, 100);
```

## Asset Optimization

### Minification

For production, assets should be minified:

```php
// Development
$css_file = 'public/assets/css/public.css';

// Production
$css_file = 'public/assets/css/public.min.css';
```

### Concatenation

Multiple CSS/JS files can be concatenated for better performance.

### CDN Support

Assets can be served from CDN:

```php
$css_url = 'https://cdn.example.com/jardin-toasts/public.css';
wp_enqueue_style('jardin-toasts-public', $css_url);
```

## Localization

### JavaScript Localization

```php
wp_localize_script('jardin-toasts-public', 'bjData', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('jb_public_nonce'),
    'strings' => [
        'loading' => __('Loading...', 'jardin-toasts'),
        'error' => __('An error occurred', 'jardin-toasts'),
    ],
]);
```

**Usage in JavaScript**:
```javascript
jQuery.ajax({
    url: bjData.ajaxUrl,
    data: {
        action: 'jb_filter_checkins',
        nonce: bjData.nonce,
    },
});
```

## Versioning

### Cache Busting

Assets are versioned to prevent caching issues:

```php
wp_enqueue_style(
    'jardin-toasts-public',
    $css_url,
    [],
    JB_VERSION // Changes on each plugin update
);
```

### File Modification Time

Alternative versioning using file modification time:

```php
$version = filemtime(plugin_dir_path(__FILE__) . 'public/assets/css/public.css');
wp_enqueue_style('jardin-toasts-public', $css_url, [], $version);
```

## Dependencies

### CSS Dependencies

Plugin CSS has no dependencies by default. If your theme requires specific CSS frameworks, you can add them:

```php
wp_enqueue_style(
    'jardin-toasts-public',
    $css_url,
    ['theme-style'], // Dependencies
    JB_VERSION
);
```

### JavaScript Dependencies

Default dependencies:
- `jquery` (optional, for compatibility)

Additional dependencies can be added:

```php
wp_enqueue_script(
    'jardin-toasts-public',
    $js_url,
    ['jquery', 'lodash'], // Dependencies
    JB_VERSION,
    true
);
```

## Inline Styles

### Dynamic CSS

For dynamic styles, use inline CSS:

```php
add_action('wp_head', function() {
    $primary_color = get_option('jb_primary_color', '#0073aa');
    ?>
    <style>
        .jb-checkin-card {
            border-color: <?php echo esc_attr($primary_color); ?>;
        }
    </style>
    <?php
});
```

## Related Documentation

- [Styling](styling.md)
- [Templates](templates.md)
- [Template Tags](template-tags.md)

