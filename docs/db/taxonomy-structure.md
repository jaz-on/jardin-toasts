# Taxonomy Structure - Beer Styles

## Overview

The `beer_style` taxonomy is hierarchical, allowing for parent-child relationships (e.g., "IPA" → "IPA - American", "IPA - New England / Hazy").

## Proposed Structure

### Hierarchical Organization

**Level 1 - Main Categories** (Parent terms):
- IPA
- Pale Ale
- Stout
- Sour
- Lager
- Wheat Beer
- Porter
- Belgian Ale
- Farmhouse Ale
- etc.

**Level 2 - Subcategories** (Child terms):
- IPA → IPA - American
- IPA → IPA - New England / Hazy
- IPA → IPA - Imperial / Double
- Stout → Stout - Imperial / Double
- Stout → Stout - Pastry
- Sour → Sour - Fruited
- Sour → Sour - Berliner Weisse
- etc.

### Import Strategy

**From Untappd HTML**:
- Untappd displays styles as flat strings: "IPA - New England / Hazy"
- Need to parse and create hierarchy automatically

**Parsing Logic**:
```php
function bj_parse_beer_style($style_string) {
    // Example: "IPA - New England / Hazy"
    // Split by " - " to get parent and child
    $parts = explode(' - ', $style_string, 2);
    
    if (count($parts) === 2) {
        $parent_name = trim($parts[0]);  // "IPA"
        $child_name = trim($parts[1]);    // "New England / Hazy"
        
        // Get or create parent term
        $parent_term = bj_get_or_create_term('beer_style', $parent_name, [
            'parent' => 0,
        ]);
        
        // Get or create child term
        $child_term = bj_get_or_create_term('beer_style', $child_name, [
            'parent' => $parent_term->term_id,
        ]);
        
        return $child_term;
    } else {
        // No hierarchy, create flat term
        return bj_get_or_create_term('beer_style', $style_string, [
            'parent' => 0,
        ]);
    }
}
```

### Alternative: Flat Structure

If hierarchical parsing is too complex initially:

**Option**: Store styles as flat terms
- "IPA - New England / Hazy" → single term
- Simpler to implement
- Less flexible for filtering/grouping
- Can be migrated to hierarchical later

### Recommendation

**Phase 1 (MVP)**: Start with flat structure
- Simpler implementation
- Faster to develop
- Can parse hierarchy later

**Phase 2**: Migrate to hierarchical
- Add migration script
- Parse existing terms
- Create parent-child relationships

### Migration Script (Future)

```php
function bj_migrate_styles_to_hierarchical() {
    $terms = get_terms([
        'taxonomy' => 'beer_style',
        'hide_empty' => false,
    ]);
    
    foreach ($terms as $term) {
        $parts = explode(' - ', $term->name, 2);
        
        if (count($parts) === 2) {
            $parent_name = trim($parts[0]);
            $parent_term = bj_get_or_create_term('beer_style', $parent_name, [
                'parent' => 0,
            ]);
            
            wp_update_term($term->term_id, 'beer_style', [
                'parent' => $parent_term->term_id,
            ]);
        }
    }
}
```

## Related Documentation

- [Database Schema](schema.md)
- [Taxonomies](schema.md#taxonomies)

