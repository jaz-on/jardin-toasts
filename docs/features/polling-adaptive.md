# Adaptive Polling - Detailed

## Overview

Detailed documentation of the adaptive polling system that adjusts sync frequency based on user activity.

## Concept

### Problem

Fixed polling frequency is inefficient:
- Active users: Need frequent updates
- Inactive users: Waste resources checking when no activity

---

### Solution

Adaptive polling adjusts frequency based on user activity:
- **Active users** (recent check-ins): Check frequently
- **Moderate users** (some activity): Check daily
- **Inactive users** (no activity): Check weekly

---

## Activity Detection

### Calculation

**Last Check-in Date**:
```php
$last_checkin_date = get_option('bj_last_checkin_date');
```

**Days Since Last Check-in**:
```php
$days_since_last = (time() - strtotime($last_checkin_date)) / DAY_IN_SECONDS;
```

---

### Activity Levels

**Active** (< 7 days):
- User checked in within last week
- Likely to check in again soon
- **Frequency**: Every 6 hours

**Moderate** (7-30 days):
- User checked in within last month
- Some activity, but not frequent
- **Frequency**: Daily

**Inactive** (30+ days):
- No check-ins in over a month
- Monitoring only
- **Frequency**: Weekly

---

## Implementation

### Schedule Determination

```php
function bj_determine_sync_schedule() {
    $last_checkin_date = get_option('bj_last_checkin_date');
    
    if (empty($last_checkin_date)) {
        // First sync: check daily
        return 'daily';
    }
    
    $days_since_last = (time() - strtotime($last_checkin_date)) / DAY_IN_SECONDS;
    
    if ($days_since_last < 7) {
        // Active user
        return 'sixhourly';
    } elseif ($days_since_last < 30) {
        // Moderate activity
        return 'daily';
    } else {
        // Inactive user
        return 'weekly';
    }
}
```

---

### Schedule Update

**After Each Sync**:
```php
function bj_update_sync_schedule() {
    $schedule = bj_determine_sync_schedule();
    
    // Clear existing schedule
    wp_clear_scheduled_hook('bj_rss_sync');
    
    // Set new schedule
    wp_schedule_event(time(), $schedule, 'bj_rss_sync');
    
    // Log schedule change
    error_log("Beer Journal: Sync schedule updated to {$schedule}");
}
```

---

## Custom Cron Schedule

### Registration

**WordPress doesn't have `sixhourly` by default**:

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

---

## Schedule Transitions

### Active → Moderate

**Trigger**: No check-ins for 7+ days

**Transition**:
- Old schedule: `sixhourly` (every 6 hours)
- New schedule: `daily` (once per day)
- Update on next sync

---

### Moderate → Inactive

**Trigger**: No check-ins for 30+ days

**Transition**:
- Old schedule: `daily`
- New schedule: `weekly`
- Update on next sync

---

### Inactive → Active

**Trigger**: New check-in detected

**Transition**:
- Old schedule: `weekly`
- New schedule: `sixhourly`
- Immediate update after import

---

## Manual Override

### User Override

**Settings Option**: Manual frequency selection

**Implementation**:
```php
$manual_frequency = get_option('bj_sync_frequency_override');

if (!empty($manual_frequency)) {
    // Use manual override
    $schedule = $manual_frequency;
} else {
    // Use adaptive schedule
    $schedule = bj_determine_sync_schedule();
}
```

---

## Performance Impact

### Resource Savings

**Active User** (6h schedule):
- 4 checks per day
- ~20KB per check (RSS only if no new check-ins)
- Minimal impact

**Inactive User** (weekly schedule):
- 1 check per week
- ~5KB per check
- Negligible impact

**Comparison to Fixed Daily**:
- Active users: 4x more frequent (better UX)
- Inactive users: 7x less frequent (saves resources)

---

## Edge Cases

### First Sync

**Scenario**: No previous check-ins

**Handling**: Default to `daily` schedule

---

### Import Historical Check-ins

**Scenario**: Importing old check-ins

**Handling**: Use actual check-in dates, not import date

```php
// Use check-in date, not current date
$checkin_date = get_post_meta($post_id, '_bj_checkin_date', true);
update_option('bj_last_checkin_date', $checkin_date);
```

---

### Multiple Check-ins in One Sync

**Scenario**: Multiple new check-ins detected

**Handling**: Use most recent check-in date

```php
$latest_date = null;
foreach ($new_checkins as $checkin) {
    $date = $checkin['date'];
    if (!$latest_date || $date > $latest_date) {
        $latest_date = $date;
    }
}
update_option('bj_last_checkin_date', $latest_date);
```

---

## Related Documentation

- [RSS Sync Architecture](../architecture/rss-sync.md)
- [RSS Sync Detailed](rss-sync-detailed.md)
- [Synchronization Flow](../user-flows/sync.md)

