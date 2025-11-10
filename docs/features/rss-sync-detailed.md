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
    error_log('Beer Journal: Failed to fetch RSS - ' . $rss->get_error_message());
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
$last_guid = get_option('bj_last_imported_guid');

if ($latest_guid === $last_guid) {
    // No new check-ins
    error_log('Beer Journal: No new check-ins, skipping sync');
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
$last_checkin_date = get_option('bj_last_checkin_date');
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
add_filter('cron_schedules', 'bj_add_cron_schedules');

function bj_add_cron_schedules($schedules) {
    $schedules['sixhourly'] = [
        'interval' => 6 * HOUR_IN_SECONDS,
        'display' => __('Every 6 Hours', 'beer-journal'),
    ];
    return $schedules;
}
```

**Registration**:
```php
wp_clear_scheduled_hook('bj_rss_sync');
wp_schedule_event(time(), $schedule, 'bj_rss_sync');
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

## Related Documentation

- [RSS Sync Architecture](../architecture/rss-sync.md)
- [Polling Adaptive](polling-adaptive.md)
- [Synchronization Flow](../user-flows/sync.md)

