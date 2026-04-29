# RSS Synchronization - Detailed

## Overview

Detailed documentation of the RSS synchronization system, including implementation details, optimizations, and edge cases.

## RSS Feed Structure

### Feed URL Format

```
https://untappd.com/rss/user/{username}
```

**Example**: `https://untappd.com/rss/user/jaz_on`

---

### Feed Content

**Items**: Up to 25 most recent check-ins

**Update Frequency**: Real-time (when user checks in)

**Format**: Standard RSS 2.0

---

### Item Structure

```xml
<item>
  <title>Jason is drinking a Meteor Blonde De Garde by Brasserie Meteor at Untappd at Home</title>
  <link>https://untappd.com/user/jaz_on/checkin/1527514863</link>
  <guid isPermaLink="true">https://untappd.com/user/jaz_on/checkin/1527514863</guid>
  <description>
    <![CDATA[<img src="https://images.untp.beer/..." />]]>
  </description>
  <pubDate>Sun, 09 Nov 2025 18:13:18 +0000</pubDate>
</item>
```

---

## Parsing Process

### Step 1: Fetch Feed

**Method**: WordPress `fetch_feed()` or `wp_remote_get()`

**Implementation**:
```php
$rss = fetch_feed($rss_url);

if (is_wp_error($rss)) {
    error_log('Jardin Toasts: Failed to fetch RSS - ' . $rss->get_error_message());
    return false;
}
```

**Error Handling**:
- Retry up to 3 times
- Exponential backoff
- Log errors

---

### Step 2: Extract Items

**Method**: SimplePie `get_items()`

**Implementation**:
```php
$items = $rss->get_items(0, 25); // Get up to 25 items
```

---

### Step 3: Parse Title

**Pattern**: "User is drinking a {beer_name} by {brewery_name} at {venue}"

**Extraction**:
```php
$title = $item->get_title();
// "Jason is drinking a Meteor Blonde De Garde by Brasserie Meteor at Untappd at Home"

// Extract beer name
preg_match('/drinking a (.+?) by/', $title, $matches);
$beer_name = $matches[1] ?? '';

// Extract brewery name
preg_match('/by (.+?) at/', $title, $matches);
$brewery_name = $matches[1] ?? '';

// Extract venue (optional)
preg_match('/at (.+)$/', $title, $matches);
$venue = $matches[1] ?? '';
```

---

### Step 4: Extract GUID

**Purpose**: Unique identifier for deduplication

**Implementation**:
```php
$guid = $item->get_id();
// "https://untappd.com/user/jaz_on/checkin/1527514863"

// Extract check-in ID
preg_match('/checkin\/(\d+)/', $guid, $matches);
$checkin_id = $matches[1] ?? '';
```

---

## GUID Comparison Optimization

### Purpose

Skip expensive scraping if no new check-ins detected.

---

### Implementation

```php
// Get latest GUID from feed
$latest_guid = $rss->get_items()[0]->get_id();

// Compare with last imported
$last_guid = get_option('jb_last_imported_guid');

if ($latest_guid === $last_guid) {
    // No new check-ins
    error_log('Jardin Toasts: No new check-ins, skipping sync');
    return;
}

// New check-ins detected, continue with import
```

---

### Benefits

- **Bandwidth**: Saves ~50KB per check-in (scraping)
- **Time**: Saves 2-5 seconds per check-in
- **Resources**: Reduces server load
- **Rate Limiting**: Fewer requests to Untappd

---

## Adaptive Polling

### Activity Detection

**Logic**:
```php
$last_checkin_date = get_option('jb_last_checkin_date');
$days_since_last = (time() - strtotime($last_checkin_date)) / DAY_IN_SECONDS;

if ($days_since_last < 7) {
    // Active user: check every 6 hours
    $schedule = 'sixhourly';
} elseif ($days_since_last < 30) {
    // Moderate: check daily
    $schedule = 'daily';
} else {
    // Inactive: check weekly
    $schedule = 'weekly';
}
```

---

### Schedule Registration

**Custom Schedule**:
```php
add_filter('cron_schedules', 'jb_add_cron_schedules');

function jb_add_cron_schedules($schedules) {
    $schedules['sixhourly'] = [
        'interval' => 6 * HOUR_IN_SECONDS,
        'display' => __('Every 6 Hours', 'jardin-toasts'),
    ];
    return $schedules;
}
```

**Registration**:
```php
wp_clear_scheduled_hook('jb_rss_sync');
wp_schedule_event(time(), $schedule, 'jb_rss_sync');
```

---

## Error Handling

### Network Errors

**Types**:
- Connection timeout
- DNS failure
- HTTP errors (404, 500, etc.)

**Handling**:
```php
$max_attempts = 3;
$attempt = 0;

while ($attempt < $max_attempts) {
    $rss = fetch_feed($rss_url);
    
    if (!is_wp_error($rss)) {
        break;
    }
    
    $attempt++;
    if ($attempt < $max_attempts) {
        sleep(pow(2, $attempt)); // Exponential backoff
    }
}
```

---

### Feed Parsing Errors

**Types**:
- Invalid XML
- Missing required elements
- Encoding issues

**Handling**:
- Log error with details
- Skip problematic items
- Continue with valid items

---

## Performance Metrics

### Typical Sync

**No New Check-ins**:
- RSS fetch: ~5KB, <1 second
- GUID comparison: <0.1 seconds
- Total: <2 seconds

**New Check-ins** (1 check-in):
- RSS fetch: ~5KB, <1 second
- Scraping: ~50KB, 2-5 seconds
- Import: <1 second
- Total: 3-7 seconds

---

## RSS Cache System

### Purpose

Prevent duplicate processing of RSS items by caching processed URLs. This ensures idempotence and avoids unnecessary scraping.

### Cache Structure

**Transient Cache** (Primary):
- **Key**: `jb_untappd_rss_cache`
- **TTL**: 6 hours (aligned with polling frequency)
- **Format**: Array of check-in URLs (strings)
- **Purpose**: Fast lookup for recent syncs

**Persistent Cache** (Backup):
- **Option**: `jb_untappd_rss_cache_persistent`
- **Format**: Array of check-in URLs (strings)
- **Purpose**: Recovery after server restart or transient expiration
- **Update**: Saved after each successful sync

### Cache Format

**Structure**:
```php
// Transient format
$cache = [
    'https://untappd.com/user/jaz_on/checkin/1527514863',
    'https://untappd.com/user/jaz_on/checkin/1527514862',
    'https://untappd.com/user/jaz_on/checkin/1527514861',
    // ... more URLs
];

// Stored as serialized array in WordPress
set_transient('jb_untappd_rss_cache', $cache, 6 * HOUR_IN_SECONDS);
update_option('jb_untappd_rss_cache_persistent', $cache);
```

### Cache Operations

#### Load Cache

```php
function jb_load_rss_cache() {
    // Try transient first (faster)
    $cache = get_transient('jb_untappd_rss_cache');
    
    if ($cache === false) {
        // Fallback to persistent option
        $cache = get_option('jb_untappd_rss_cache_persistent', []);
        
        // Restore transient if persistent exists
        if (!empty($cache)) {
            set_transient('jb_untappd_rss_cache', $cache, 6 * HOUR_IN_SECONDS);
        }
    }
    
    // Convert to Set for O(1) lookup
    return array_flip($cache); // Use keys for fast lookup
}
```

#### Save Cache

```php
function jb_save_rss_cache($processed_urls) {
    $urls_array = array_values($processed_urls); // Convert Set to array
    
    // Update transient (primary)
    set_transient('jb_untappd_rss_cache', $urls_array, 6 * HOUR_IN_SECONDS);
    
    // Update persistent option (backup)
    update_option('jb_untappd_rss_cache_persistent', $urls_array);
}
```

#### Check if URL Processed

```php
function jb_is_url_processed($url, $cache) {
    return isset($cache[$url]);
}
```

### Cache Invalidation

**Automatic**:
- Transient expires after 6 hours
- Persistent cache remains until manual clear

**Manual**:
- Admin action: "Clear RSS Cache"
- Option: `jb_clear_rss_cache` (WP-CLI command)

**Implementation**:
```php
function jb_clear_rss_cache() {
    delete_transient('jb_untappd_rss_cache');
    delete_option('jb_untappd_rss_cache_persistent');
}
```

### Cache Size Management

**Limitation**: Prevent unbounded growth

**Strategy**: Keep only last N URLs (e.g., 1000)

```php
function jb_trim_rss_cache($cache, $max_size = 1000) {
    if (count($cache) > $max_size) {
        // Keep most recent URLs (assuming chronological order)
        return array_slice($cache, -$max_size, null, true);
    }
    return $cache;
}
```

### Performance Considerations

**Lookup Time**: O(1) with array keys (hash map)

**Memory**: ~100 bytes per URL (1000 URLs ≈ 100KB)

**Storage**: Transient in object cache (if available), option in database

**Optimization**: Use `array_flip()` for Set-like behavior in PHP

---

## Related Documentation

- [RSS Sync Architecture](../architecture/rss-sync.md)
- [Polling Adaptive](polling-adaptive.md)
- [Synchronization Flow](../user-flows/sync.md)
- [Untappd Integration](untappd-integration.md)

