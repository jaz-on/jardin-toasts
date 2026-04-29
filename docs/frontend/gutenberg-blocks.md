# Gutenberg Blocks — version initiale (1.0.0)

## Overview

Jardin Toasts fournit des blocks Gutenberg pour afficher les check-ins dans l'éditeur de blocs. Ces blocks font partie de la version initiale (1.0.0).

Policy: pas de shortcodes/widgets. La personnalisation se fait via blocks et filtres (`jb_*`).

## Available Blocks

### 1. Check-ins List Block

**Block Name**: `jardin-toasts/checkins-list`

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
<!-- wp:jardin-toasts/checkins-list -->
<div class="wp-block-jardin-toasts-checkins-list">
    <!-- Block content -->
</div>
<!-- /wp:jardin-toasts/checkins-list -->
```

---

### 2. Check-in Card Block

**Block Name**: `jardin-toasts/checkin-card`

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
<!-- wp:jardin-toasts/checkin-card {"postId":123} -->
<div class="wp-block-jardin-toasts-checkin-card">
    <!-- Block content -->
</div>
<!-- /wp:jardin-toasts/checkin-card -->
```

---

### 3. Stats Dashboard Block

**Block Name**: `jardin-toasts/stats-dashboard`

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
<!-- wp:jardin-toasts/stats-dashboard -->
<div class="wp-block-jardin-toasts-stats-dashboard">
    <!-- Block content -->
</div>
<!-- /wp:jardin-toasts/stats-dashboard -->
```

---

## Block Registration

### Block.json

Each block uses `block.json` for configuration:

```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "jardin-toasts/checkins-list",
    "title": "Check-ins List",
    "category": "jardin-toasts",
    "icon": "beer",
    "description": "Display a list of beer check-ins",
    "keywords": ["beer", "checkin", "untappd"],
    "textdomain": "jardin-toasts",
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
2. Search for "Jardin Toasts" or "Check-ins"
3. Select desired block
4. Configure in sidebar
5. Insert into content

### In Templates

Blocks can be inserted programmatically:

```php
$content = '<!-- wp:jardin-toasts/checkins-list {"postsPerPage":12} /-->';
wp_insert_post([
    'post_content' => $content,
]);
```

## Block Styling

### Editor Styles

Blocks have separate editor styles:

```php
register_block_type('jardin-toasts/checkins-list', [
    'editor_style' => 'jardin-toasts-blocks-editor',
]);
```

### Frontend Styles

Blocks share frontend styles with templates:

```php
register_block_type('jardin-toasts/checkins-list', [
    'style' => 'jardin-toasts-public',
]);
```

## Block API

### Server-Side Rendering

For dynamic blocks, use server-side rendering:

```php
register_block_type('jardin-toasts/checkins-list', [
    'render_callback' => 'jb_render_checkins_list_block',
]);

function jb_render_checkins_list_block($attributes) {
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

---

## Validation des blocks (version initiale 1.0.0)

### Critères fonctionnels
- Insertion depuis l’éditeur (palette, recherche “Jardin Toasts”)
- Rendu côté front identique/équivalent au rendu éditeur
- Attributs appliqués correctement (tri, filtres, options d’affichage)
- Dégradé acceptable sans JavaScript côté front (si applicable)

### Accessibilité (a11y)
- Navigation clavier complète (focus visible, ordre logique)
- Alt text d’images renseigné (“{beer_name} - {brewery}”)
- Contrastes conformes WCAG sur composants rendus
- Roles/ARIA pertinents si nécessaire

### Internationalisation
- Toutes les chaînes via `__()`, `_x()`, `sprintf()` (`jardin-toasts`)
- Formats localisés (dates, nombres)

### Performance
- Build avec `@wordpress/scripts` (dépendances minimales)
- CSS scopé aux blocks et chargement conditionnel
- Requêtes WP_Query paginées, paramètres filtrables, caches WP si nécessaire

### Qualité et sécurité
- Lint JS/CSS OK
- Échappement en sortie côté PHP pour SSR
- Conformité WPCS côté PHP

### Tests manuels minimaux
- Éditeur: insertion/suppression, dupliquer, undo/redo, changement d’attributs
- Front: affichage public, thèmes variés, responsive
- Cas limites: zéro check-in, grand volume, champs manquants (image, style)

### Définition de terminé (DoD)
- Spécifications et attributs documentés ici
- Traductions prêtes (.pot)
- Lint/build passent sans erreur
- Vérifications a11y de base effectuées
- Revue visuelle sur mobile/tablette/desktop

