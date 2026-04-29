# Image Placeholder Strategy

## Overview

When beer images cannot be downloaded or are missing, the plugin needs a fallback placeholder image.

## Proposed Solutions

### Option 1: SVG Placeholder (Recommended)

**Advantages**:
- Scalable (vector)
- Small file size
- No external dependencies
- Can be generated dynamically

**Implementation**:
```php
function jb_get_placeholder_image_url() {
    // Generate SVG on-the-fly
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
        <rect width="400" height="400" fill="#f0f0f0"/>
        <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" 
              font-family="Arial, sans-serif" font-size="24" fill="#999">
            Beer Image
        </text>
    </svg>';
    
    // Convert to data URI
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}
```

**Location**: Generated dynamically, no file needed

---

### Option 2: Default Image in Plugin

**Advantages**:
- Consistent placeholder
- Can be customized by user
- Works even if SVG not supported

**Implementation**:
```
/assets/images/
└── placeholder-beer.png (400x400px)
```

**Usage**:
```php
function jb_get_placeholder_image_url() {
    return plugin_dir_url(__FILE__) . 'assets/images/placeholder-beer.png';
}
```

**Customization**: Allow admin to upload custom placeholder in settings

---

### Option 3: WordPress Default Placeholder

**Advantages**:
- Uses WordPress core functionality
- No additional files needed
- Consistent with WordPress ecosystem

**Implementation**:
```php
function jb_get_placeholder_image_url() {
    // Use WordPress default placeholder
    return get_option('jb_placeholder_image', 
        includes_url('images/media/default.png')
    );
}
```

---

## Recommendation

**Use Option 1 (SVG) for MVP**:
- No file management needed
- Small footprint
- Always available
- Can be enhanced later with custom SVG

**Future Enhancement**: Allow admin to upload custom placeholder image

## When to Use Placeholder

1. **Image download fails** (network error, 404, etc.)
2. **Image is missing** from Untappd check-in
3. **Image format not supported** (rare)
4. **Image exceeds size limit** (if configured)

## Implementation in Import Process

```php
function jb_handle_beer_image($image_url, $post_id) {
    if (empty($image_url)) {
        // No image URL, use placeholder
        return jb_set_placeholder_image($post_id);
    }
    
    $result = jb_download_image($image_url);
    
    if (is_wp_error($result)) {
        // Download failed, use placeholder
        error_log('Jardin Toasts: Image download failed - ' . $result->get_error_message());
        return jb_set_placeholder_image($post_id);
    }
    
    // Image downloaded successfully
    return $result;
}

function jb_set_placeholder_image($post_id) {
    $placeholder_url = jb_get_placeholder_image_url();
    
    // Create attachment from placeholder
    $attachment_id = jb_create_attachment_from_url($placeholder_url, $post_id);
    
    if ($attachment_id) {
        set_post_thumbnail($post_id, $attachment_id);
        update_post_meta($post_id, '_jb_image_source', 'placeholder');
    }
    
    return $attachment_id;
}
```

## Related Documentation

- [Image Handling](image-handling.md)
- [Import Process](import-process.md)

