# Error Handling - Detailed

## Overview

Comprehensive error handling system with retry logic, logging, and user notifications.

## Error Categories

### Network Errors

**Types**:
- Connection timeout
- DNS failure
- HTTP errors (404, 500, etc.)
- SSL certificate errors

**Handling**:
```php
$max_attempts = 3;
$attempt = 0;

while ($attempt < $max_attempts) {
    $response = wp_remote_get($url, ['timeout' => 10]);
    
    if (!is_wp_error($response)) {
        break;
    }
    
    $attempt++;
    if ($attempt < $max_attempts) {
        sleep(pow(2, $attempt)); // Exponential backoff: 1s, 2s, 4s
    }
}

if (is_wp_error($response)) {
    error_log('Jardin Toasts: Network error after 3 attempts - ' . $response->get_error_message());
    return new WP_Error('network_error', $response->get_error_message());
}
```

---

### Scraping Errors

**Types**:
- HTML structure changed
- Selectors no longer match
- Missing required elements
- Invalid data format

**Handling**:
```php
try {
    $rating = $crawler->filter('.rating-serving .rating')->text();
} catch (Exception $e) {
    // Try alternative selector
    $rating = $crawler->filter('[data-rating]')->attr('data-rating');
    
    if (empty($rating)) {
        // Log warning
        error_log('Jardin Toasts: Could not extract rating from ' . $url);
        // Save as draft
        return new WP_Error('scraping_failed', 'Rating not found');
    }
}
```

---

### Validation Errors

**Types**:
- Missing required fields
- Invalid data format
- Out of range values

**Handling**:
```php
function jb_validate_checkin_data($data) {
    $errors = [];
    
    // Required fields
    if (empty($data['beer_name'])) {
        $errors[] = 'missing_beer_name';
    }
    
    if (empty($data['brewery_name'])) {
        $errors[] = 'missing_brewery_name';
    }
    
    if (empty($data['rating'])) {
        $errors[] = 'missing_rating';
    }
    
    // Validate rating range
    if (!empty($data['rating']) && ($data['rating'] < 0 || $data['rating'] > 5)) {
        $errors[] = 'invalid_rating';
    }
    
    if (!empty($errors)) {
        return new WP_Error('validation_failed', implode(', ', $errors));
    }
    
    return true;
}
```

---

## Retry Logic

### Automatic Retry

**Network Errors**:
- Attempt 1: Immediate
- Attempt 2: +1 second (exponential backoff)
- Attempt 3: +2 seconds
- After 3 failures: Log and notify

**Scraping Errors**:
- Attempt 1: Immediate
- Attempt 2: +6 hours (WP-Cron)
- Attempt 3: +24 hours (WP-Cron)
- After 3 failures: Save as draft

---

### Retry Scheduling

**Implementation**:
```php
function jb_schedule_retry($checkin_url, $attempt) {
    $delay = [
        1 => 0,              // Immediate
        2 => 6 * HOUR_IN_SECONDS,  // +6 hours
        3 => 24 * HOUR_IN_SECONDS, // +24 hours
    ];
    
    $schedule_time = time() + ($delay[$attempt] ?? 0);
    
    wp_schedule_single_event($schedule_time, 'jb_retry_scraping', [
        $checkin_url,
        $attempt + 1,
    ]);
}
```

---

## Logging System

### Log File Structure

**Location**: `wp-content/uploads/jardin-toasts/logs/`

**File Format**: Unified log file
- `jardin-toasts-{YYYY-MM-DD}.log`

**Note**: All plugin logs (RSS sync, scraping, imports, errors) are written to the same unified log file. See [Logging Strategy](../development/logging-strategy.md) for details.

---

### Log Format

```
[2025-11-10 18:15:23] INFO: RSS sync started
[2025-11-10 18:15:24] INFO: Fetched RSS feed (25 items)
[2025-11-10 18:15:24] WARNING: Selector .rating-serving not found
[2025-11-10 18:15:24] ERROR: Failed to scrape check-in 1527514863
[2025-11-10 18:15:25] INFO: Check-in saved as draft (missing_rating)
```

---

### Log Levels

- **INFO**: Normal operations
- **WARNING**: Non-critical issues
- **ERROR**: Failures requiring attention
- **DEBUG**: Detailed information (debug mode only)

---

## Admin Notifications

### Dashboard Notices

**Types**:
- Success: "X check-ins imported successfully"
- Warning: "X check-ins saved as drafts"
- Error: "Import failed: [reason]"

**Implementation**:
```php
add_action('admin_notices', 'jb_admin_notices');

function jb_admin_notices() {
    $draft_count = jb_get_draft_count();
    
    if ($draft_count > 0) {
        printf(
            '<div class="notice notice-warning is-dismissible">
                <p><strong>Jardin Toasts:</strong> %d check-in(s) saved as drafts. 
                <a href="%s">Review drafts</a></p>
            </div>',
            $draft_count,
            admin_url('edit.php?post_type=beer_checkin&post_status=draft')
        );
    }
}
```

---

### Email Notifications

**Triggers**:
- Sync completion (if enabled)
- Errors (if enabled)
- Persistent failures

**Implementation**:
```php
function jb_send_notification($type, $data) {
    $enabled = get_option('jb_notify_on_' . $type, false);
    
    if (!$enabled) {
        return;
    }
    
    $email = get_option('jb_notification_email', get_option('admin_email'));
    $subject = sprintf(__('Jardin Toasts: %s', 'jardin-toasts'), $type);
    $message = jb_format_notification($type, $data);
    
    wp_mail($email, $subject, $message);
}
```

#### Daily Email Digest for Drafts

**Purpose**: Notify admin daily if there are draft check-ins awaiting review.

**Schedule**: Daily at 9:00 AM (configurable)

**Conditions**:
- Only sent if there are draft check-ins
- Can be disabled in settings
- Includes summary of draft reasons

**Email Content**:
- Total number of drafts
- Breakdown by reason (missing_rating, scraping_failed, etc.)
- Direct link to draft review page
- Quick action links (retry all, delete all)

**Settings**:
- Option: `jb_email_digest_enabled` (default: true)
- Option: `jb_email_digest_time` (default: '09:00')
- Option: `jb_email_digest_email` (default: admin_email)

**Implementation**:
```php
// Schedule daily digest
add_action('init', 'jb_schedule_daily_digest');

function jb_schedule_daily_digest() {
    if (!wp_next_scheduled('jb_daily_draft_digest')) {
        $time = get_option('jb_email_digest_time', '09:00');
        $timestamp = strtotime("today {$time}");
        
        if ($timestamp < time()) {
            $timestamp = strtotime("tomorrow {$time}");
        }
        
        wp_schedule_event($timestamp, 'daily', 'jb_daily_draft_digest');
    }
}

// Send digest
add_action('jb_daily_draft_digest', 'jb_send_daily_digest');

function jb_send_daily_digest() {
    if (!get_option('jb_email_digest_enabled', true)) {
        return;
    }
    
    $draft_count = jb_get_draft_count();
    
    if ($draft_count === 0) {
        return; // No drafts, no email
    }
    
    $email = get_option('jb_email_digest_email', get_option('admin_email'));
    $subject = sprintf(__('Jardin Toasts: %d check-in(s) awaiting review', 'jardin-toasts'), $draft_count);
    
    // Get breakdown by reason
    $breakdown = jb_get_draft_breakdown();
    
    $message = sprintf(
        __("You have %d check-in(s) saved as drafts:\n\n", 'jardin-toasts'),
        $draft_count
    );
    
    foreach ($breakdown as $reason => $count) {
        $message .= sprintf("- %s: %d\n", jb_get_draft_reason_label($reason), $count);
    }
    
    $message .= "\n" . __('Review drafts:', 'jardin-toasts') . "\n";
    $message .= admin_url('edit.php?post_type=beer&post_status=draft') . "\n";
    
    wp_mail($email, $subject, $message);
}
```

---

## Draft Management

### Draft Reasons

**Stored in**: `_jb_incomplete_reason` meta field

**Reasons**:
- `missing_rating`: Rating not found
- `missing_beer_name`: Beer name not found
- `missing_brewery_name`: Brewery name not found
- `scraping_failed`: Scraping failed after 3 attempts
- `validation_failed`: Data validation failed

---

### Draft Review Interface

**Admin Page**: Filter by status: draft

**Features**:
- Filter by incomplete reason
- Bulk actions: Retry, Delete, Publish (if complete)
- View error details

#### Manual Retry with Multiple Selection

**Interface**:
- Checkbox selection for multiple draft check-ins
- Bulk action dropdown: "Retry Selected"
- Individual retry button per check-in

**Process**:
1. Admin selects one or more draft check-ins
2. Clicks "Retry Selected" from bulk actions
3. Plugin forces immediate re-scraping for selected items
4. Progress indicator shows retry status
5. Successfully scraped items are automatically published
6. Failed items remain as drafts with updated error count

**Implementation**:
```php
// AJAX handler for bulk retry
add_action('wp_ajax_jb_retry_selected', 'jb_handle_bulk_retry');

function jb_handle_bulk_retry() {
    check_ajax_referer('jb_retry_nonce', 'nonce');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Insufficient permissions', 'jardin-toasts')]);
    }
    
    $post_ids = isset($_POST['post_ids']) ? array_map('absint', $_POST['post_ids']) : [];
    
    if (empty($post_ids)) {
        wp_send_json_error(['message' => __('No check-ins selected', 'jardin-toasts')]);
    }
    
    $results = [];
    foreach ($post_ids as $post_id) {
        $checkin_url = get_post_meta($post_id, '_jb_checkin_url', true);
        if ($checkin_url) {
            $result = jb_scrape_and_import_checkin($checkin_url, $post_id);
            $results[$post_id] = $result;
        }
    }
    
    wp_send_json_success(['results' => $results]);
}
```

---

## Related Documentation

- [Error Handling Flow](../user-flows/error-handling.md)
- [Import Process](../architecture/import-process.md)

