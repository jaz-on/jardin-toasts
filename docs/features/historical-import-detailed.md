# Historical Import - Detailed

## Overview

Detailed documentation of the historical import system for importing entire Untappd history.

## Import Modes

### Manual Mode

**Recommended for**: ~200 check-ins

**Process**:
1. User clicks "Start Import"
2. Browser stays open
3. Process runs synchronously
4. Progress updates via AJAX every 5 seconds
5. Can pause/resume

**Limitations**:
- Browser timeout (typically 30-60 seconds)
- Limited to ~8 pages (200 check-ins) before timeout

---

### Background Mode

**Recommended for**: Large imports (1000+ check-ins)

**Process**:
1. User clicks "Start Background Import"
2. WP-Cron takes over
3. One batch per hour
4. Email notification when complete

**Advantages**:
- No browser timeout
- Can process thousands of check-ins
- Runs in background

**Disadvantages**:
- Slower (1 batch per hour)
- Requires WP-Cron to be working
- Less real-time feedback

---

## Profile Page Scraping

### URL Structure

**Format**: `https://untappd.com/user/{username}`

**Pagination**: 
- 25 check-ins per page
- Next page: `?next={checkin_id}` or similar

---

### HTML Structure

**Check-in Links**:
```html
<div class="checkin-list">
  <a href="/user/{username}/checkin/{id}">Check-in 1</a>
  <a href="/user/{username}/checkin/{id}">Check-in 2</a>
  <!-- ... 25 check-ins per page -->
</div>
```

---

### Extraction Process

```php
// Fetch profile page
$html = wp_remote_get($profile_url);

// Parse with DomCrawler
$crawler = new Crawler($html);

// Extract check-in URLs
$checkin_urls = $crawler->filter('.checkin-list a[href*="/checkin/"]')
    ->extract(['href']);

// Convert to full URLs
foreach ($checkin_urls as $url) {
    $full_url = 'https://untappd.com' . $url;
    // Process check-in
}
```

---

## Batch Processing

### Batch Size Configuration

**Options**: 25, 50, or 100 check-ins per batch

**Recommendation**:
- Manual mode: 25 (faster feedback)
- Background mode: 50-100 (efficiency)

---

### Batch Processing Loop

```php
$batch_size = get_option('bj_import_batch_size', 25);
$delay = get_option('bj_import_delay', 3);

foreach ($checkin_urls as $index => $url) {
    // Scrape and import
    bj_import_checkin_from_url($url);
    
    // Delay between requests
    if ($index < count($checkin_urls) - 1) {
        sleep($delay);
    }
    
    // Save checkpoint every batch
    if (($index + 1) % $batch_size === 0) {
        bj_save_checkpoint($index + 1);
    }
}
```

---

## Checkpoint System

### Checkpoint Data

**Stored in**: `bj_import_checkpoint` option

**Structure**:
```php
[
    'current_page' => 3,
    'total_imported' => 75,
    'last_checkin_id' => '123456',
    'started_at' => 1699632000, // Unix timestamp
    'checkin_urls' => [...], // Remaining URLs
]
```

---

### Checkpoint Save

**After Each Batch**:
```php
function bj_save_checkpoint($imported_count, $current_page, $remaining_urls) {
    update_option('bj_import_checkpoint', [
        'current_page' => $current_page,
        'total_imported' => $imported_count,
        'last_checkin_id' => $last_checkin_id,
        'started_at' => $started_at,
        'checkin_urls' => $remaining_urls,
    ]);
}
```

---

### Resume from Checkpoint

**Implementation**:
```php
function bj_resume_import() {
    $checkpoint = get_option('bj_import_checkpoint');
    
    if (empty($checkpoint)) {
        return new WP_Error('no_checkpoint', 'No checkpoint found');
    }
    
    // Continue from checkpoint
    $remaining_urls = $checkpoint['checkin_urls'];
    $total_imported = $checkpoint['total_imported'];
    
    // Process remaining URLs
    foreach ($remaining_urls as $url) {
        // Import check-in
        // Skip if already imported (deduplication)
    }
}
```

---

## Progress Tracking

### Real-Time Updates

**AJAX Endpoint**: `bj_get_import_progress`

**Updates**: Every 5 seconds

**Data Returned**:
```php
[
    'total' => 200,
    'imported' => 75,
    'percentage' => 37.5,
    'current_page' => 3,
    'total_pages' => 8,
    'time_elapsed' => 180, // seconds
    'eta' => 300, // seconds
]
```

---

### ETA Calculation

```php
$elapsed = time() - $start_time;
$rate = $imported / $elapsed; // check-ins per second
$remaining = $total - $imported;
$eta = $remaining / $rate; // seconds remaining
```

---

## Error Handling

### Network Errors

**Handling**: Retry up to 3 times, then skip

---

### Scraping Failures

**Handling**: Save as draft, continue with next

---

### Timeout Handling

**Manual Mode**:
- Save checkpoint
- Display "Import paused" message
- Provide "Resume" button

**Background Mode**:
- No timeout (WP-Cron handles)
- Checkpoint saved after each batch

---

## Related Documentation

- [Historical Import Flow](../user-flows/historical-import.md)
- [Scraping Detailed](scraping-detailed.md)

