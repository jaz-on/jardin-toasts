# Database Schema

## Overview

Beer Journal uses WordPress's native database structure with Custom Post Types, Taxonomies, and Post Meta. No custom tables are created - everything leverages WordPress core functionality.

## WordPress Core Tables Used

### `wp_posts`
Stores the check-in posts (Custom Post Type: `beer`)

**Key Fields**:
- `ID`: Post ID (primary key)
- `post_title`: "{beer_name} - {brewery_name}"
- `post_content`: User comment (optional)
- `post_date`: Check-in date (important for chronological order)
- `post_status`: 'publish' or 'draft'
- `post_type`: 'beer'
- `post_name`: Auto-generated slug
- `post_author`: User ID who imported (usually admin)

### `wp_postmeta`
Stores all custom metadata for check-ins

**Key Fields**:
- `meta_id`: Meta ID (primary key)
- `post_id`: Foreign key to `wp_posts.ID`
- `meta_key`: Meta field name (prefixed with `_bj_`)
- `meta_value`: Meta field value (serialized for arrays)

See [Meta Fields Documentation](meta-fields.md) for complete list.

### `wp_terms`
Stores taxonomy terms (beer styles, breweries, venues)

**Key Fields**:
- `term_id`: Term ID (primary key)
- `name`: Term name
- `slug`: Term slug

### `wp_term_taxonomy`
Defines taxonomies and term relationships

**Key Fields**:
- `term_taxonomy_id`: Primary key
- `term_id`: Foreign key to `wp_terms.term_id`
- `taxonomy`: Taxonomy name ('beer_style', 'brewery', 'venue')
- `description`: Term description (optional)
- `parent`: Parent term ID (for hierarchical taxonomies)
- `count`: Number of posts using this term

### `wp_term_relationships`
Links posts to taxonomy terms

**Key Fields**:
- `object_id`: Post ID (foreign key to `wp_posts.ID`)
- `term_taxonomy_id`: Term taxonomy ID (foreign key to `wp_term_taxonomy.term_taxonomy_id`)
- `term_order`: Order of term (usually 0)

### `wp_options`
Stores plugin settings and configuration

**Key Fields**:
- `option_id`: Option ID (primary key)
- `option_name`: Option name (prefixed with `bj_`)
- `option_value`: Option value (serialized for arrays/objects)
- `autoload`: Whether to autoload ('yes' or 'no')

See [Options Documentation](options.md) for complete list.

### `wp_postmeta` (for attachments)
Stores image metadata

**Key Fields**:
- `_wp_attachment_image_alt`: Alt text
- `_wp_attached_file`: File path
- `_wp_attachment_metadata`: Serialized array with image sizes
- `_bj_image_hash`: MD5 hash for duplicate detection
- `_bj_image_source_url`: Original Untappd URL

## Custom Post Type: `beer`

### Registration
```php
register_post_type('beer', [
    'public' => true,
    'show_in_rest' => true,        // REST API support
    'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
    'has_archive' => true,
    'rewrite' => ['slug' => 'checkins'],
    'menu_icon' => 'dashicons-beer',
    'show_in_menu' => true,
    'capability_type' => 'post',
    'map_meta_cap' => true,
]);
```

### Post Structure
- **Title**: `{beer_name} - {brewery_name}` (auto-slug by WordPress)
- **Content**: User comment (optional)
- **Date**: Check-in date (important for chronological order)
- **Status**: 'publish' or 'draft' (based on data completeness)
- **Featured Image**: Beer photo (attachment ID)

## Taxonomies

### `beer_style` (Hierarchical)
- **Type**: Hierarchical (like categories)
- **Slug**: `beer-style`
- **REST API**: Enabled
- **Examples**: IPA > American IPA, Stout > Imperial Stout
- **Purpose**: Filter and organize by beer style

### `brewery` (Non-hierarchical)
- **Type**: Non-hierarchical (like tags)
- **Slug**: `brewery`
- **REST API**: Enabled
- **Purpose**: Filter by brewery

### `venue` (Non-hierarchical)
- **Type**: Non-hierarchical (like tags)
- **Slug**: `venue`
- **REST API**: Enabled
- **Purpose**: Filter by consumption venue (optional)

## Relationships

### Post → Taxonomies
- One post can have multiple beer styles (usually one)
- One post can have one brewery
- One post can have one venue (optional)

### Post → Meta Fields
- One post has many meta fields (one-to-many)
- Meta fields are key-value pairs

### Post → Featured Image
- One post has one featured image (attachment)
- Attachment is a post of type 'attachment'

## Data Flow

```
Untappd Check-in
    ↓
Scraped Data
    ↓
WordPress Post (beer)
    ├── Post Meta (all _bj_* fields)
    ├── Taxonomies (beer_style, brewery, venue)
    └── Featured Image (attachment)
```

## Indexes

See [Indexes Documentation](indexes.md) for recommended database indexes.

## Related Documentation

- [ERD Diagram](erd.md)
- [Meta Fields](meta-fields.md)
- [Options](options.md)
- [Indexes](indexes.md)
- [Architecture Overview](../architecture/overview.md)

