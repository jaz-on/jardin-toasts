# Database Indexes

## Overview

This document describes recommended database indexes for optimal performance with Beer Journal. WordPress automatically creates some indexes, but additional indexes may be needed for specific queries.

## WordPress Automatic Indexes

WordPress automatically creates these indexes:

### `wp_posts` Table
- `PRIMARY KEY` on `ID`
- `post_name` (for permalinks)
- `type_status_date` (post_type, post_status, post_date)
- `post_parent` (for hierarchical posts)
- `post_author` (for author queries)

### `wp_postmeta` Table
- `PRIMARY KEY` on `meta_id`
- `post_id` (for meta queries)

### `wp_terms` Table
- `PRIMARY KEY` on `term_id`
- `slug` (for term lookups)

### `wp_term_taxonomy` Table
- `PRIMARY KEY` on `term_taxonomy_id`
- `term_id` (for term relationships)

### `wp_term_relationships` Table
- `PRIMARY KEY` on (`object_id`, `term_taxonomy_id`)
- `term_taxonomy_id` (for taxonomy queries)

## Recommended Additional Indexes

### 1. Unique Index on Check-in ID

**Purpose**: Fast duplicate detection and lookups by Untappd check-in ID.

**Table**: `wp_postmeta`

**Index**:
```sql
CREATE UNIQUE INDEX bj_checkin_id_unique 
ON wp_postmeta (meta_key, meta_value(191))
WHERE meta_key = '_bj_checkin_id';
```

**Note**: MySQL/MariaDB limit on index key length is 767 bytes (191 characters for utf8mb4). The `meta_value(191)` limits the indexed portion.

**Usage**:
```php
// Fast duplicate check
$args = [
    'post_type' => 'beer',
    'meta_query' => [
        [
            'key' => '_bj_checkin_id',
            'value' => '1527514863',
            'compare' => '=',
        ],
    ],
    'posts_per_page' => 1,
];
```

**Alternative** (if unique constraint not possible):
```sql
CREATE INDEX bj_checkin_id_idx 
ON wp_postmeta (meta_key, meta_value(191))
WHERE meta_key = '_bj_checkin_id';
```

---

### 2. Composite Index for Post Type and Date

**Purpose**: Optimize queries filtering by post type and date.

**Table**: `wp_posts`

**Index**:
```sql
CREATE INDEX bj_post_type_date_idx 
ON wp_posts (post_type, post_date);
```

**Usage**:
```php
// Archive queries
$args = [
    'post_type' => 'beer',
    'orderby' => 'date',
    'order' => 'DESC',
];
```

**Note**: WordPress may already have a similar index (`type_status_date`), but this is more specific.

---

### 3. Index for Rating Queries

**Purpose**: Fast filtering and sorting by rating.

**Table**: `wp_postmeta`

**Index**:
```sql
CREATE INDEX bj_rating_rounded_idx 
ON wp_postmeta (meta_key, meta_value)
WHERE meta_key = '_bj_rating_rounded';
```

**Usage**:
```php
// Get top-rated check-ins
$args = [
    'post_type' => 'beer',
    'meta_key' => '_bj_rating_rounded',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
];
```

**Note**: For numeric comparisons, ensure `meta_value` is cast to number in queries.

---

### 4. Index for Beer Name Searches

**Purpose**: Fast text searches by beer name.

**Table**: `wp_postmeta`

**Index**:
```sql
CREATE INDEX bj_beer_name_idx 
ON wp_postmeta (meta_key, meta_value(191))
WHERE meta_key = '_bj_beer_name';
```

**Usage**:
```php
// Search by beer name
$args = [
    'post_type' => 'beer',
    'meta_query' => [
        [
            'key' => '_bj_beer_name',
            'value' => 'IPA',
            'compare' => 'LIKE',
        ],
    ],
];
```

**Note**: Full-text search would be better but requires different index type.

---

### 5. Index for Brewery Queries

**Purpose**: Fast filtering by brewery.

**Table**: `wp_postmeta`

**Index**:
```sql
CREATE INDEX bj_brewery_name_idx 
ON wp_postmeta (meta_key, meta_value(191))
WHERE meta_key = '_bj_brewery_name';
```

**Usage**:
```php
// Get all check-ins from a brewery
$args = [
    'post_type' => 'beer',
    'meta_query' => [
        [
            'key' => '_bj_brewery_name',
            'value' => 'Brasserie Meteor',
            'compare' => '=',
        ],
    ],
];
```

---

## Index Creation

### Manual Creation

Indexes can be created manually via SQL:

```sql
-- Connect to database
USE wordpress_db;

-- Create indexes
CREATE UNIQUE INDEX bj_checkin_id_unique 
ON wp_postmeta (meta_key, meta_value(191))
WHERE meta_key = '_bj_checkin_id';

CREATE INDEX bj_rating_rounded_idx 
ON wp_postmeta (meta_key, meta_value)
WHERE meta_key = '_bj_rating_rounded';
```

**Note**: MySQL 8.0+ supports filtered indexes with `WHERE` clause. For older versions, create regular indexes.

### Programmatic Creation

Create indexes on plugin activation:

```php
register_activation_hook(__FILE__, 'bj_create_indexes');

function bj_create_indexes() {
    global $wpdb;
    
    $table = $wpdb->postmeta;
    
    // Check if index exists
    $index_exists = $wpdb->get_var("
        SELECT COUNT(*)
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
        AND table_name = '{$wpdb->postmeta}'
        AND index_name = 'bj_checkin_id_unique'
    ");
    
    if (!$index_exists) {
        $wpdb->query("
            CREATE UNIQUE INDEX bj_checkin_id_unique 
            ON {$wpdb->postmeta} (meta_key, meta_value(191))
            WHERE meta_key = '_bj_checkin_id'
        ");
    }
}
```

### WordPress Database Delta

Use WordPress `dbDelta()` for safer index creation:

```php
function bj_create_indexes() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Note: dbDelta doesn't support filtered indexes well
    // Manual SQL may be needed
}
```

## Index Maintenance

### Checking Index Usage

```sql
-- Check if indexes are being used
EXPLAIN SELECT * 
FROM wp_postmeta 
WHERE meta_key = '_bj_checkin_id' 
AND meta_value = '1527514863';
```

### Monitoring Index Performance

```sql
-- Check index statistics
SHOW INDEX FROM wp_postmeta;
```

### Rebuilding Indexes

If indexes become fragmented:

```sql
-- Rebuild index
ALTER TABLE wp_postmeta DROP INDEX bj_checkin_id_unique;
CREATE UNIQUE INDEX bj_checkin_id_unique 
ON wp_postmeta (meta_key, meta_value(191))
WHERE meta_key = '_bj_checkin_id';
```

## Performance Considerations

### Index Overhead

- **Storage**: Each index uses additional disk space
- **Write Performance**: Indexes slow down INSERT/UPDATE operations
- **Read Performance**: Indexes speed up SELECT queries

**Balance**: Create indexes only for frequently queried fields.

### Query Optimization

Always use indexed fields in WHERE clauses:

```php
// Good: Uses index
$args = [
    'meta_key' => '_bj_checkin_id',
    'meta_value' => '1527514863',
];

// Bad: Full table scan
$args = [
    'meta_query' => [
        [
            'key' => '_bj_checkin_id',
            'value' => '1527514863',
            'compare' => 'LIKE', // Won't use index efficiently
        ],
    ],
];
```

## Compatibility

### MySQL Versions

- **MySQL 5.7+**: Supports filtered indexes
- **MySQL 5.6**: May need regular indexes
- **MariaDB 10.3+**: Supports filtered indexes

### WordPress Compatibility

WordPress doesn't manage custom indexes automatically. They must be created manually or via activation hook.

## Related Documentation

- [Schema Documentation](schema.md)
- [Meta Fields](meta-fields.md)
- [Options](options.md)
- [Performance Optimization](../architecture/overview.md)

