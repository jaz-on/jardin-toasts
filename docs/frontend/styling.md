# Styling

## Overview

Jardin Toasts provides default CSS that follows a "Database + Grid Hybrid" design philosophy. All styles can be overridden by themes.

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
.jb-checkin { }

/* Grid view */
.jb-checkins-grid { }
.jb-checkin-card { }

/* Table view */
.jb-checkins-table { }

/* Single check-in */
.jb-single { }
.jb-checkin-hero { }

/* Components */
.jb-rating { }
.jb-stars { }
.jb-filters { }
```

## CSS Classes

### Container Classes

- `.jb-archive` - Archive container
- `.jb-single` - Single check-in container
- `.jb-checkins-grid` - Grid view container
- `.jb-checkins-table` - Table view container

### Card Classes

- `.jb-checkin-card` - Individual check-in card
- `.jb-checkin-image` - Card image container
- `.jb-checkin-content` - Card content container
- `.jb-checkin-header` - Card header
- `.jb-checkin-footer` - Card footer

### Component Classes

- `.jb-rating` - Rating container
- `.jb-stars` - Stars display
- `.jb-rating-label` - Rating label
- `.jb-beer-name` - Beer name
- `.jb-brewery-name` - Brewery name
- `.jb-beer-style` - Beer style
- `.jb-venue` - Venue information
- `.jb-filters` - Filter container
- `.jb-view-toggle` - View toggle buttons

## Grid View Styles

### Grid Layout

```css
.jb-checkins-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
}

@media (max-width: 768px) {
    .jb-checkins-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .jb-checkins-grid {
        grid-template-columns: 1fr;
    }
}
```

### Card Styles

```css
.jb-checkin-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.jb-checkin-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.jb-checkin-image {
    width: 100%;
    aspect-ratio: 1;
    overflow: hidden;
}

.jb-checkin-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
```

## Table View Styles

### Table Layout

```css
.jb-checkins-table {
    width: 100%;
    border-collapse: collapse;
}

.jb-checkins-table th,
.jb-checkins-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.jb-checkins-table th {
    background: #f5f5f5;
    font-weight: 600;
}

.jb-checkins-table tr:hover {
    background: #f9f9f9;
}
```

## Rating Styles

### Stars Display

```css
.jb-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.jb-stars {
    font-size: 1.25rem;
    line-height: 1;
}

.jb-rating-label {
    font-size: 0.875rem;
    color: #666;
    margin: 0.5rem 0 0 0;
}
```

## Filter Styles

### Filter Container

```css
.jb-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f9f9f9;
    border-radius: 8px;
}

.jb-filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.jb-filter-group label {
    font-weight: 600;
    font-size: 0.875rem;
}

.jb-filter-group select,
.jb-filter-group input {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}
```

## View Toggle

### Toggle Buttons

```css
.jb-view-toggle {
    display: flex;
    gap: 0.5rem;
}

.jb-view-toggle button {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.jb-view-toggle button.active {
    background: #0073aa;
    color: #fff;
    border-color: #0073aa;
}

.jb-view-toggle button:hover {
    background: #f5f5f5;
}

.jb-view-toggle button.active:hover {
    background: #005a87;
}
```

## Single Check-in Styles

### Hero Image

```css
.jb-checkin-hero {
    width: 100%;
    max-height: 500px;
    overflow: hidden;
    margin-bottom: 2rem;
}

.jb-checkin-hero img {
    width: 100%;
    height: auto;
    object-fit: cover;
}
```

### Metadata Sidebar

```css
.jb-checkin-sidebar {
    background: #f9f9f9;
    padding: 1.5rem;
    border-radius: 8px;
}

.jb-checkin-sidebar dl {
    margin: 0;
}

.jb-checkin-sidebar dt {
    font-weight: 600;
    margin-top: 1rem;
}

.jb-checkin-sidebar dt:first-child {
    margin-top: 0;
}

.jb-checkin-sidebar dd {
    margin: 0.25rem 0 0 0;
    color: #666;
}
```

## Customization

### Override Styles

Add custom CSS to your theme:

```css
/* Override plugin styles */
.jb-checkin-card {
    /* Your custom styles */
    border: 2px solid #your-color;
}
```

### Use CSS Variables

The plugin uses CSS variables for easy theming:

```css
:root {
    --jb-primary-color: #0073aa;
    --jb-secondary-color: #00a0d2;
    --jb-border-color: #ddd;
    --jb-background: #fff;
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
    .jb-filters,
    .jb-view-toggle {
        display: none;
    }
    
    .jb-checkin-card {
        page-break-inside: avoid;
    }
}
```

## Related Documentation

- [Templates](templates.md)
- [Assets](assets.md)
- [Template Tags](template-tags.md)

