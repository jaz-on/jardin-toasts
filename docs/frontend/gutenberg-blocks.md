# Gutenberg Blocks

## Overview

Beer Journal provides Gutenberg blocks for displaying check-ins in the block editor. Blocks are available in Phase 2 (Version 1.5).

## Available Blocks

### 1. Check-ins List Block

**Block Name**: `beer-journal/checkins-list`

**Purpose**: Display a list of check-ins with customizable options

**Location**: `blocks/src/checkins-list/`

#### Attributes

- `postsPerPage` (number): Number of check-ins to display (default: 12)
- `orderBy` (string): Order by field ('date', 'rating', 'title')
- `order` (string): Order direction ('asc', 'desc')
- `beerStyle` (string): Filter by beer style (optional)
- `brewery` (string): Filter by brewery (optional)
- `minRating` (number): Minimum rating (0-5)
- `layout` (string): Layout type ('grid', 'list', 'timeline', 'masonry')
- `columns` (number): Number of columns for grid (2, 3, or 4)
- `showImage` (boolean): Show images
- `showRating` (boolean): Show ratings
- `showStyle` (boolean): Show beer style
- `showBrewery` (boolean): Show brewery
- `showDate` (boolean): Show date

#### Example Usage

```jsx
<!-- wp:beer-journal/checkins-list -->
<div class="wp-block-beer-journal-checkins-list">
    <!-- Block content -->
</div>
<!-- /wp:beer-journal/checkins-list -->
```

---

### 2. Check-in Card Block

**Block Name**: `beer-journal/checkin-card`

**Purpose**: Display a single check-in card

**Location**: `blocks/src/checkin-card/`

#### Attributes

- `postId` (number): Post ID of check-in to display
- `showImage` (boolean): Show image
- `showRating` (boolean): Show rating
- `showStyle` (boolean): Show beer style
- `showBrewery` (boolean): Show brewery
- `showVenue` (boolean): Show venue
- `showDate` (boolean): Show date
- `showComment` (boolean): Show comment
- `imageSize` (string): Image size ('thumbnail', 'medium', 'large', 'full')

#### Example Usage

```jsx
<!-- wp:beer-journal/checkin-card {"postId":123} -->
<div class="wp-block-beer-journal-checkin-card">
    <!-- Block content -->
</div>
<!-- /wp:beer-journal/checkin-card -->
```

---

### 3. Stats Dashboard Block

**Block Name**: `beer-journal/stats-dashboard`

**Purpose**: Display statistics about check-ins

**Location**: `blocks/src/stats-dashboard/`

#### Attributes

- `showTotal` (boolean): Show total check-ins
- `showAverageRating` (boolean): Show average rating
- `showTopBrewery` (boolean): Show top brewery
- `showTopStyle` (boolean): Show top beer style
- `showBestRated` (boolean): Show best rated beer
- `showCharts` (boolean): Show charts
- `chartType` (string): Chart type ('line', 'pie', 'bar')

#### Statistics Displayed

- Total check-ins
- Average rating
- Top brewery (most check-ins)
- Top beer style (most check-ins)
- Best rated beer
- Charts (optional):
  - Evolution over time (line chart)
  - Distribution by style (pie chart)
  - Top 10 breweries (bar chart)

#### Example Usage

```jsx
<!-- wp:beer-journal/stats-dashboard -->
<div class="wp-block-beer-journal-stats-dashboard">
    <!-- Block content -->
</div>
<!-- /wp:beer-journal/stats-dashboard -->
```

---

## Block Registration

### Block.json

Each block uses `block.json` for configuration:

```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "beer-journal/checkins-list",
    "title": "Check-ins List",
    "category": "beer-journal",
    "icon": "beer",
    "description": "Display a list of beer check-ins",
    "keywords": ["beer", "checkin", "untappd"],
    "textdomain": "beer-journal",
    "attributes": {
        "postsPerPage": {
            "type": "number",
            "default": 12
        },
        "orderBy": {
            "type": "string",
            "default": "date"
        }
    },
    "supports": {
        "html": false,
        "align": true
    },
    "editorScript": "file:./index.js",
    "editorStyle": "file:./style.css",
    "style": "file:./style.css"
}
```

### Block Registration

```php
register_block_type(plugin_dir_path(__FILE__) . 'blocks/build/checkins-list');
```

## Block Development

### File Structure

```
blocks/
├── src/
│   ├── checkins-list/
│   │   ├── block.json
│   │   ├── index.js
│   │   ├── edit.js
│   │   ├── save.js
│   │   └── style.scss
│   ├── checkin-card/
│   │   ├── block.json
│   │   ├── index.js
│   │   ├── edit.js
│   │   ├── save.js
│   │   └── style.scss
│   └── stats-dashboard/
│       ├── block.json
│       ├── index.js
│       ├── edit.js
│       ├── save.js
│       └── style.scss
└── build/
    ├── checkins-list/
    ├── checkin-card/
    └── stats-dashboard/
```

### Build Process

Blocks are built using `@wordpress/scripts`:

```json
{
    "scripts": {
        "build": "wp-scripts build",
        "start": "wp-scripts start"
    }
}
```

## Block Usage

### In Block Editor

1. Click "+" to add block
2. Search for "Beer Journal" or "Check-ins"
3. Select desired block
4. Configure in sidebar
5. Insert into content

### In Templates

Blocks can be inserted programmatically:

```php
$content = '<!-- wp:beer-journal/checkins-list {"postsPerPage":12} /-->';
wp_insert_post([
    'post_content' => $content,
]);
```

## Block Styling

### Editor Styles

Blocks have separate editor styles:

```php
register_block_type('beer-journal/checkins-list', [
    'editor_style' => 'beer-journal-blocks-editor',
]);
```

### Frontend Styles

Blocks share frontend styles with templates:

```php
register_block_type('beer-journal/checkins-list', [
    'style' => 'beer-journal-public',
]);
```

## Block API

### Server-Side Rendering

For dynamic blocks, use server-side rendering:

```php
register_block_type('beer-journal/checkins-list', [
    'render_callback' => 'bj_render_checkins_list_block',
]);

function bj_render_checkins_list_block($attributes) {
    $args = [
        'post_type' => 'beer',
        'posts_per_page' => $attributes['postsPerPage'] ?? 12,
        'orderby' => $attributes['orderBy'] ?? 'date',
        'order' => $attributes['order'] ?? 'DESC',
    ];
    
    $query = new WP_Query($args);
    
    ob_start();
    // Render template
    include plugin_dir_path(__FILE__) . 'blocks/templates/checkins-list.php';
    return ob_get_clean();
}
```

## Related Documentation

- [Templates](templates.md)
- [Template Tags](template-tags.md)
- [Styling](styling.md)

