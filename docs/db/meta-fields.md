# Meta Fields

## Overview

All check-in metadata is stored in WordPress `wp_postmeta` table with keys prefixed with `_bj_`. The leading underscore makes these fields "hidden" in the WordPress admin by default.

## Meta Field Categories

### Identifiers

Unique identifiers for Untappd entities.

| Meta Key | Type | Description | Example |
|----------|------|-------------|---------|
| `_bj_checkin_id` | string | Untappd check-in ID (unique) | `"1527514863"` |
| `_bj_beer_id` | int | Untappd beer ID | `12345` |
| `_bj_brewery_id` | int | Untappd brewery ID | `6789` |
| `_bj_checkin_url` | string | Original Untappd check-in URL | `"https://untappd.com/user/jaz_on/checkin/1527514863"` |

**Usage**: Deduplication, linking back to Untappd, API integration (future).

---

### Beer Data

Information about the beer itself.

| Meta Key | Type | Description | Example |
|----------|------|-------------|---------|
| `_bj_beer_name` | string | Beer name | `"Meteor Blonde De Garde"` |
| `_bj_brewery_name` | string | Brewery name | `"Brasserie Meteor"` |
| `_bj_beer_style` | string | Beer style (redundant with taxonomy, for search) | `"Blonde Ale"` |
| `_bj_beer_abv` | float | Alcohol by volume percentage | `5.5` |
| `_bj_beer_ibu` | int | International Bitterness Units | `25` |
| `_bj_beer_description` | text | Official beer description (long text) | `"A refreshing blonde ale..."` |

**Note**: `_bj_beer_style` is redundant with the `beer_style` taxonomy but stored for easier searching and filtering.

---

### Check-in Data

Data specific to this check-in instance.

| Meta Key | Type | Description | Example | Required |
|----------|------|-------------|---------|----------|
| `_bj_rating_raw` | float | Original Untappd rating (0-5 with decimals) | `4.25` | ✓ |
| `_bj_rating_rounded` | int | Mapped star rating (0-5 stars) | `4` | ✓ |
| `_bj_serving_type` | string | Type of serving | `"Draft"`, `"Bottle"`, `"Can"`, `"Cask"` | ○ |
| `_bj_purchase_venue` | string | Where beer was purchased (if different from consumption venue) | `"Beer Store"` | ○ |
| `_bj_checkin_date` | datetime | Check-in date (ISO 8601 format) | `"2025-11-10T18:13:18Z"` | ✓ |

**Rating Fields**: Both `_bj_rating_raw` and `_bj_rating_rounded` are stored. The raw rating preserves original data, while rounded is used for display and filtering.

**Note**: There is no `_bj_rating` field. Only `_bj_rating_raw` and `_bj_rating_rounded` are used.

---

### Venue Data

Location where the beer was consumed.

| Meta Key | Type | Description | Example |
|----------|------|-------------|---------|
| `_bj_venue_name` | string | Venue name | `"Home"`, `"The Beer Bar"` |
| `_bj_venue_city` | string | City name | `"Strasbourg"` |
| `_bj_venue_country` | string | Country name | `"France"` |
| `_bj_venue_lat` | float | Latitude (optional, for future map feature) | `48.5734` |
| `_bj_venue_lng` | float | Longitude (optional, for future map feature) | `7.7521` |

**Note**: Venue coordinates are stored for potential map integration in Phase 3.

---

### Social Data

Social engagement metrics from Untappd.

| Meta Key | Type | Description | Example |
|----------|------|-------------|---------|
| `_bj_toast_count` | int | Number of "toasts" (likes) | `12` |
| `_bj_comment_count` | int | Number of comments | `3` |
| `_bj_badges_earned` | array | Badges earned (serialized array, Phase 3) | `["badge1", "badge2"]` |

**Note**: `_bj_badges_earned` is stored as serialized array for future badge display feature.

---

### Technical Metadata

Internal metadata for import tracking and debugging.

| Meta Key | Type | Description | Example |
|----------|------|-------------|---------|
| `_bj_source` | string | Import source | `"rss"` or `"crawler"` |
| `_bj_scraped_at` | datetime | Last scraping attempt timestamp | `"2025-11-10 18:15:00"` |
| `_bj_scraping_attempts` | int | Number of scraping attempts | `1`, `2`, `3` |
| `_bj_incomplete_reason` | string | Reason for draft status | `"missing_rating"`, `"missing_beer_name"` |

**Draft Reasons**:
- `missing_rating`: Rating not found during scraping
- `missing_beer_name`: Beer name not found
- `missing_brewery_name`: Brewery name not found
- `scraping_failed`: Scraping failed after 3 attempts

---

## Image Metadata

For featured images (attachments), additional meta fields are stored:

| Meta Key | Type | Description | Example |
|----------|------|-------------|---------|
| `_bj_image_hash` | string | MD5 hash of image URL (for duplicate detection) | `"a1b2c3d4e5f6..."` |
| `_bj_image_source_url` | string | Original Untappd image URL | `"https://images.untp.beer/..."` |

**Note**: These are stored on the attachment post, not the check-in post.

---

## Accessing Meta Fields

### WordPress Functions

```php
// Get single meta field
$rating = get_post_meta($post_id, '_bj_rating_raw', true);

// Get all meta fields (prefixed)
$all_meta = get_post_meta($post_id);

// Update meta field
update_post_meta($post_id, '_bj_rating_raw', 4.25);

// Delete meta field
delete_post_meta($post_id, '_bj_rating_raw');
```

### Query by Meta Field

```php
// Get check-ins with 4+ star rating
$args = [
    'post_type' => 'beer',
    'meta_query' => [
        [
            'key' => '_bj_rating_rounded',
            'value' => 4,
            'compare' => '>=',
        ],
    ],
];
$checkins = get_posts($args);
```

### Helper Functions

```php
// Get all check-in data
function bj_get_checkin_data($post_id) {
    return [
        'checkin_id' => get_post_meta($post_id, '_bj_checkin_id', true),
        'beer_name' => get_post_meta($post_id, '_bj_beer_name', true),
        'brewery_name' => get_post_meta($post_id, '_bj_brewery_name', true),
        'rating_raw' => get_post_meta($post_id, '_bj_rating_raw', true),
        'rating_rounded' => get_post_meta($post_id, '_bj_rating_rounded', true),
        // ... etc
    ];
}
```

## Data Types

### Strings
- Stored as `VARCHAR` or `TEXT` in database
- Sanitized with `sanitize_text_field()` before storage
- Escaped with `esc_html()` or `esc_attr()` on output

### Numbers
- **Integers**: Stored as strings, cast with `absint()` or `intval()`
- **Floats**: Stored as strings, cast with `floatval()`
- **Decimals**: Stored as strings (e.g., `"4.25"`)

### Dates
- Stored as `DATETIME` strings (MySQL format: `"2025-11-10 18:13:18"`)
- ISO 8601 format for `_bj_checkin_date`: `"2025-11-10T18:13:18Z"`

### Arrays
- Stored as serialized strings (e.g., `_bj_badges_earned`)
- Use `maybe_unserialize()` when retrieving

## Validation

### Required Fields for Publication
- `_bj_checkin_id`: Must be unique
- `_bj_beer_name`: Must not be empty
- `_bj_brewery_name`: Must not be empty
- `_bj_rating_raw`: Must be between 0 and 5
- `_bj_rating_rounded`: Must be between 0 and 5
- `_bj_checkin_date`: Must be valid date

### Optional Fields
All other fields are optional and don't prevent publication.

## Indexing

See [Indexes Documentation](indexes.md) for recommended database indexes on meta fields.

## Related Documentation

- [Schema Documentation](schema.md)
- [ERD Diagram](erd.md)
- [Options](options.md)
- [Indexes](indexes.md)
- [Import Process](../architecture/import-process.md)

