# Styling

## Overview

Beer Journal provides default CSS that follows a "Database + Grid Hybrid" design philosophy. All styles can be overridden by themes.

## Design Philosophy

### Database + Grid Hybrid

The plugin supports two viewing modes:

1. **Grid View** (Visual Mode): Card-based layout with images
2. **Table View** (Database Mode): Spreadsheet-style with all data visible

### Responsive Design

- **Desktop**: 3-column grid or full table
- **Tablet**: 2-column grid or scrollable table
- **Mobile**: 1-column list or stacked cards

## CSS Structure

### Main Stylesheet

**File**: `public/assets/css/public.css`

**Organization**:
```css
/* Base styles */
.bj-checkin { }

/* Grid view */
.bj-checkins-grid { }
.bj-checkin-card { }

/* Table view */
.bj-checkins-table { }

/* Single check-in */
.bj-single { }
.bj-checkin-hero { }

/* Components */
.bj-rating { }
.bj-stars { }
.bj-filters { }
```

## CSS Classes

### Container Classes

- `.bj-archive` - Archive container
- `.bj-single` - Single check-in container
- `.bj-checkins-grid` - Grid view container
- `.bj-checkins-table` - Table view container

### Card Classes

- `.bj-checkin-card` - Individual check-in card
- `.bj-checkin-image` - Card image container
- `.bj-checkin-content` - Card content container
- `.bj-checkin-header` - Card header
- `.bj-checkin-footer` - Card footer

### Component Classes

- `.bj-rating` - Rating container
- `.bj-stars` - Stars display
- `.bj-rating-label` - Rating label
- `.bj-beer-name` - Beer name
- `.bj-brewery-name` - Brewery name
- `.bj-beer-style` - Beer style
- `.bj-venue` - Venue information
- `.bj-filters` - Filter container
- `.bj-view-toggle` - View toggle buttons

## Grid View Styles

### Grid Layout

```css
.bj-checkins-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

@media (max-width: 768px) {
    .bj-checkins-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .bj-checkins-grid {
        grid-template-columns: 1fr;
    }
}
```

### Card Styles

```css
.bj-checkin-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.bj-checkin-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.bj-checkin-image {
    width: 100%;
    aspect-ratio: 1;
    overflow: hidden;
}

.bj-checkin-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
```

## Table View Styles

### Table Layout

```css
.bj-checkins-table {
    width: 100%;
    border-collapse: collapse;
}

.bj-checkins-table th,
.bj-checkins-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.bj-checkins-table th {
    background: #f5f5f5;
    font-weight: 600;
}

.bj-checkins-table tr:hover {
    background: #f9f9f9;
}
```

## Rating Styles

### Stars Display

```css
.bj-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.bj-stars {
    font-size: 1.25rem;
    line-height: 1;
}

.bj-rating-label {
    font-size: 0.875rem;
    color: #666;
    margin: 0.5rem 0 0 0;
}
```

## Filter Styles

### Filter Container

```css
.bj-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f9f9f9;
    border-radius: 8px;
}

.bj-filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.bj-filter-group label {
    font-weight: 600;
    font-size: 0.875rem;
}

.bj-filter-group select,
.bj-filter-group input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}
```

## View Toggle

### Toggle Buttons

```css
.bj-view-toggle {
    display: flex;
    gap: 0.5rem;
}

.bj-view-toggle button {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.bj-view-toggle button.active {
    background: #0073aa;
    color: #fff;
    border-color: #0073aa;
}

.bj-view-toggle button:hover {
    background: #f5f5f5;
}

.bj-view-toggle button.active:hover {
    background: #005a87;
}
```

## Single Check-in Styles

### Hero Image

```css
.bj-checkin-hero {
    width: 100%;
    max-height: 500px;
    overflow: hidden;
    margin-bottom: 2rem;
}

.bj-checkin-hero img {
    width: 100%;
    height: auto;
    object-fit: cover;
}
```

### Metadata Sidebar

```css
.bj-checkin-sidebar {
    background: #f9f9f9;
    padding: 1.5rem;
    border-radius: 8px;
}

.bj-checkin-sidebar dl {
    margin: 0;
}

.bj-checkin-sidebar dt {
    font-weight: 600;
    margin-top: 1rem;
}

.bj-checkin-sidebar dt:first-child {
    margin-top: 0;
}

.bj-checkin-sidebar dd {
    margin: 0.25rem 0 0 0;
    color: #666;
}
```

## Customization

### Override Styles

Add custom CSS to your theme:

```css
/* Override plugin styles */
.bj-checkin-card {
    /* Your custom styles */
    border: 2px solid #your-color;
}
```

### Use CSS Variables

The plugin uses CSS variables for easy theming:

```css
:root {
    --bj-primary-color: #0073aa;
    --bj-secondary-color: #00a0d2;
    --bj-border-color: #ddd;
    --bj-background: #fff;
}
```

### Theme Integration

The plugin styles are designed to work with any theme. If conflicts occur:

1. Increase specificity in your theme CSS
2. Use `!important` sparingly
3. Override specific classes as needed

## Responsive Breakpoints

### Standard Breakpoints

```css
/* Mobile */
@media (max-width: 480px) { }

/* Tablet */
@media (min-width: 481px) and (max-width: 768px) { }

/* Desktop */
@media (min-width: 769px) { }
```

## Print Styles

### Print-Friendly Styles

```css
@media print {
    .bj-filters,
    .bj-view-toggle {
        display: none;
    }
    
    .bj-checkin-card {
        page-break-inside: avoid;
    }
}
```

## Related Documentation

- [Templates](templates.md)
- [Assets](assets.md)
- [Template Tags](template-tags.md)

