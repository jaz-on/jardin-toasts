# Frontend Templates

## Overview

Jardin Toasts provides theme-agnostic templates that can be overridden by your theme. All templates follow WordPress template hierarchy conventions.

## Available Templates

### Archive Template
**File**: `archive-beer.php`

**Purpose**: Displays list of all check-ins

**Features**:
- Grid view (default): 3 columns desktop, 2 columns tablet, 1 column mobile
- Table view: Database-style table with all columns
- Toggle between grid and table views
- Filters: Style, Brewery, Rating, Date
- Search bar for full-text search
- Pagination

**Location**: `public/templates/archive-beer.php`

---

### Single Check-in Template
**File**: `single-beer.php`

**Purpose**: Displays individual check-in details

**Features**:
- Hero image (large, if available)
- Sidebar with metadata "data sheet"
- Full comment in prose
- Previous/Next navigation
- Related check-ins: "Other beers from this brewery"

**Location**: `public/templates/single-beer.php`

---

## Microformats

Templates appliquent par défaut des microformats utiles à l’écosystème IndieWeb:
- `h-entry` sur l’élément principal (single)
- `p-name` pour le titre
- `e-content` pour le contenu
- `dt-published` pour la date

Ces microformats peuvent être désactivés via l’option `jb_microformats_enabled`. Voir [Schema Documentation](../development/schema.md).

---

### Taxonomy Templates

#### Beer Style Archive
**File**: `taxonomy-beer-style.php`

**Purpose**: Displays check-ins filtered by beer style

**Features**:
- Style name and description
- Grid/table view (same as archive)
- Filter by other criteria (brewery, rating, date)

**Location**: `public/templates/taxonomy-beer-style.php`

---

#### Brewery Archive
**File**: `taxonomy-brewery.php`

**Purpose**: Displays check-ins filtered by brewery

**Features**:
- Brewery name
- Grid/table view
- Filter by other criteria

**Location**: `public/templates/taxonomy-brewery.php`

---

#### Venue Archive
**File**: `taxonomy-venue.php`

**Purpose**: Displays check-ins filtered by venue

**Features**:
- Venue name
- Grid/table view
- Filter by other criteria

**Location**: `public/templates/taxonomy-venue.php`

---

## Future: Menu Taxonomy Archive

Une archive pour `beer_menu` (collections éditoriales) est prévue dans une version future (pas MVP).
Le rendu suivra les mêmes principes (grid/table, filtres), exposé via blocks et filtres (pas de shortcodes).

---

## Template Hierarchy

WordPress searches for templates in this order:

1. **Theme Override**: `/wp-content/themes/{theme}/jardin-toasts/archive-beer.php`
2. **Theme Override**: `/wp-content/themes/{theme}/archive-beer.php`
3. **Plugin Default**: `/wp-content/plugins/jardin-toasts/public/templates/archive-beer.php`

See [Template Hierarchy Documentation](template-hierarchy.md) for details.

## Design Philosophy

### Database + Grid Hybrid

The templates support two viewing modes:

#### Grid View (Visual Mode)
- Card-based layout
- Large images
- Visual rating display (stars)
- Compact information
- Best for browsing

#### Table View (Database Mode)
- Spreadsheet-style layout
- All data visible at once
- Sortable columns
- Best for data analysis

### Responsive Design

- **Desktop**: 3-column grid or full table
- **Tablet**: 2-column grid or scrollable table
- **Mobile**: 1-column list or stacked cards

## Template Structure

### Archive Template Structure

```php
<?php
/**
 * Archive template for beer check-ins
 */

get_header();
?>

<div class="jb-archive">
    <?php do_action('jb_before_checkins_list'); ?>
    
    <div class="jb-archive-header">
        <h1><?php echo esc_html(get_the_archive_title()); ?></h1>
        
        <div class="jb-view-toggle">
            <button class="jb-view-grid active">Grid</button>
            <button class="jb-view-table">Table</button>
        </div>
        
        <div class="jb-filters">
            <!-- Filters here -->
        </div>
    </div>
    
    <div class="jb-checkins-grid">
        <?php
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                get_template_part('partials/checkin-card');
            }
        }
        ?>
    </div>
    
    <?php
    the_posts_pagination();
    do_action('jb_after_checkins_list');
    ?>
</div>

<?php
get_footer();
```

### Single Template Structure

```php
<?php
/**
 * Single check-in template
 */

get_header();
?>

<div class="jb-single">
    <?php while (have_posts()) : the_post(); ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class('jb-checkin'); ?>>
            
            <header class="jb-checkin-header">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="jb-checkin-hero">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
                
                <h1><?php the_title(); ?></h1>
            </header>
            
            <div class="jb-checkin-content">
                <div class="jb-checkin-main">
                    <?php the_content(); ?>
                </div>
                
                <aside class="jb-checkin-sidebar">
                    <?php get_template_part('partials/checkin-metadata'); ?>
                </aside>
            </div>
            
            <nav class="jb-checkin-navigation">
                <?php
                the_post_navigation([
                    'prev_text' => '← Previous Check-in',
                    'next_text' => 'Next Check-in →',
                ]);
                ?>
            </nav>
            
            <div class="jb-checkin-related">
                <?php get_template_part('partials/related-checkins'); ?>
            </div>
            
        </article>
        
    <?php endwhile; ?>
</div>

<?php
get_footer();
```

## Partials (Reusable Components)

### Check-in Card
**File**: `public/partials/checkin-card.php`

**Purpose**: Reusable card component for grid view

**Usage**:
```php
get_template_part('partials/checkin-card');
```

---

### Rating Stars
**File**: `public/partials/rating-stars.php`

**Purpose**: Display rating with stars

**Usage**:
```php
jb_rating_stars(get_post_meta(get_the_ID(), '_jb_rating_rounded', true));
```

---

### Check-in Metadata
**File**: `public/partials/checkin-metadata.php`

**Purpose**: Display all metadata in sidebar

**Usage**:
```php
get_template_part('partials/checkin-metadata');
```

## Customization

### Override Templates

Copy template files to your theme:

```
/wp-content/themes/{theme}/
├── jardin-toasts/
│   ├── archive-beer.php
│   ├── single-beer.php
│   └── taxonomy-beer-style.php
```

### Use Hooks and Filters

See [Hooks and Filters Documentation](hooks-filters.md) for customization options.

### Custom CSS

Add custom CSS to your theme:

```css
/* Override plugin styles */
.jb-checkin-card {
    /* Your custom styles */
}
```

## Related Documentation

- [Template Hierarchy](template-hierarchy.md)
- [Template Tags](template-tags.md)
- [Hooks and Filters](hooks-filters.md)
- [Styling](styling.md)

