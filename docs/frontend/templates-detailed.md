# Templates - Detailed

## Overview

Detailed documentation of frontend templates: design philosophy, structure, and customization.

## Design Philosophy

### Database + Grid Hybrid

**Concept**: Support both visual browsing and data analysis

**Grid View** (Visual Mode):
- Card-based layout
- Large images
- Visual rating display
- Best for browsing

**Table View** (Database Mode):
- Spreadsheet-style
- All data visible
- Sortable columns
- Best for analysis

---

## Archive Template

### Grid View Structure

**Layout**:
- Desktop: 3 columns
- Tablet: 2 columns
- Mobile: 1 column

**Card Components**:
1. Image (if available)
2. Rating stars
3. Beer name
4. Brewery name
5. Beer style
6. ABV % (if available)
7. Venue and date

---

### Table View Structure

**Columns**:
- Photo (thumbnail)
- Beer Name
- Brewery
- Style
- Rating
- ABV
- Date
- Venue

**Features**:
- Sortable columns
- Scrollable on mobile
- Hover effects

---

## Single Template

### Layout Structure

**Hero Section**:
- Large image (if available)
- Beer name and brewery
- Rating display

**Main Content**:
- Left: User comment (if present)
- Right: Metadata sidebar

**Navigation**:
- Previous/Next check-ins
- Related check-ins

---

### Metadata Sidebar

**Data Sheet Format**:
```
Style: IPA
ABV: 6.5%
IBU: 45
Serving: Draft
Date: November 9, 2025
Venue: Home, Strasbourg, France
Toasts: 12
Comments: 3
```

---

## Responsive Design

### Breakpoints

**Mobile**: < 480px
- 1 column grid
- Stacked cards
- Collapsible filters

**Tablet**: 481-768px
- 2 column grid
- Scrollable table
- Sidebar below content

**Desktop**: > 768px
- 3 column grid
- Full table
- Sidebar visible

---

## Related Documentation

- [Templates](templates.md)
- [Customization](customization.md)
- [Styling](styling.md)

