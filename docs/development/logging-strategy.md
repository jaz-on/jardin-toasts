# Logging Strategy

## Overview

Jardin Toasts logs important events, errors, and debugging information to help diagnose issues and track import progress.

## Log Location

**Directory**: `wp-content/uploads/jardin-toasts/logs/`

**File Naming**: `jardin-toasts-{YYYY-MM-DD}.log`

**Example**: `jardin-toasts-2025-11-10.log`

## Log Levels

Following WordPress standards:

- **ERROR**: Critical errors that prevent functionality
- **WARNING**: Non-critical issues that may affect functionality
- **INFO**: General informational messages
- **DEBUG**: Detailed debugging information (only in debug mode)

## Log Format

```
[YYYY-MM-DD HH:MM:SS] LEVEL: Message
```

**Example**:
```
[2025-11-10 18:15:23] INFO: RSS sync started
[2025-11-10 18:15:24] INFO: Found 5 new check-ins
[2025-11-10 18:15:25] WARNING: Check-in saved as draft (missing_rating)
[2025-11-10 18:15:26] ERROR: Failed to download image: Network timeout
```

## Log Rotation

### Strategy: Daily Rotation

**Implementation**:
- New log file created each day
- Old logs kept for configurable retention period
- Automatic cleanup of old logs

**Retention Settings**:
- Default: 30 days
- Configurable via option: `jb_log_retention_days` (default: 30)
- Admin can set to 0 to disable auto-cleanup

### Cleanup Process

**Schedule**: Daily via WP-Cron

**Process**:
1. Check log directory
2. Find log files older than retention period
3. Delete old files
4. Log cleanup action

**Implementation**:
```php
add_action('jb_daily_log_cleanup', 'jb_cleanup_old_logs');

function jb_cleanup_old_logs() {
    $retention_days = get_option('jb_log_retention_days', 30);
    
    if ($retention_days === 0) {
        return; // Cleanup disabled
    }
    
    $log_dir = jb_get_log_directory();
    $cutoff_date = strtotime("-{$retention_days} days");
    
    $files = glob($log_dir . 'jardin-toasts-*.log');
    $deleted = 0;
    
    foreach ($files as $file) {
        $file_date = filemtime($file);
        
        if ($file_date < $cutoff_date) {
            if (unlink($file)) {
                $deleted++;
            }
        }
    }
    
    if ($deleted > 0) {
        jb_log(sprintf('Cleaned up %d old log file(s)', $deleted), 'INFO');
    }
}
```

## Log Size Management

### Maximum File Size

**Limit**: 10 MB per log file

**Behavior**: When file exceeds limit:
- Current file is closed
- New file created with suffix: `jardin-toasts-{YYYY-MM-DD}-{N}.log`
- Example: `jardin-toasts-2025-11-10-2.log`

**Implementation**:
```php
function jb_get_log_file_path() {
    $log_dir = jb_get_log_directory();
    $date = date('Y-m-d');
    $base_file = $log_dir . "jardin-toasts-{$date}.log";
    
    // Check if file exists and is too large
    if (file_exists($base_file) && filesize($base_file) > 10 * 1024 * 1024) {
        // Find next available number
        $counter = 1;
        while (file_exists("{$log_dir}jardin-toasts-{$date}-{$counter}.log")) {
            $counter++;
        }
        return "{$log_dir}jardin-toasts-{$date}-{$counter}.log";
    }
    
    return $base_file;
}
```

## Logging Functions

### Core Logging Function

```php
function jb_log($message, $level = 'INFO') {
    // Skip DEBUG in production
    if ($level === 'DEBUG' && !WP_DEBUG) {
        return;
    }
    
    $log_file = jb_get_log_file_path();
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[{$timestamp}] {$level}: {$message}\n";
    
    // Ensure directory exists
    $log_dir = dirname($log_file);
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
    }
    
    // Append to log file
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
```

### Convenience Functions

```php
function jb_log_error($message) {
    jb_log($message, 'ERROR');
}

function jb_log_warning($message) {
    jb_log($message, 'WARNING');
}

function jb_log_info($message) {
    jb_log($message, 'INFO');
}

function jb_log_debug($message) {
    jb_log($message, 'DEBUG');
}
```

## What to Log

### Always Log (INFO/ERROR/WARNING)
- RSS sync start/end
- Import batch start/end
- Failed imports with reasons
- Network errors
- Scraping failures
- Image download failures
- Draft creation reasons

### Debug Mode Only (DEBUG)
- Detailed scraping steps
- Selector matching results
- Data transformation steps
- Cache hits/misses
- Performance metrics

## Log Viewing

### Admin Interface

**Location**: Jardin Toasts → Logs

**Features**:
- View current day's log
- Download log files
- Filter by log level
- Search log entries
- Clear logs (with confirmation)

**Implementation**:
```php
add_submenu_page(
    'edit.php?post_type=beer_checkin',
    __('Logs', 'jardin-toasts'),
    __('Logs', 'jardin-toasts'),
    'manage_options',
    'jardin-toasts-logs',
    'jb_render_logs_page'
);
```

## Security Considerations

1. **File Permissions**: Log files should be readable only by server user
2. **Directory Protection**: Add `.htaccess` to prevent direct web access
3. **Sensitive Data**: Never log passwords, API keys, or personal data
4. **Path Disclosure**: Use relative paths in error messages

## Performance Considerations

1. **Async Logging**: Consider queueing log writes for high-volume scenarios
2. **Log Rotation**: Prevents disk space issues
3. **Debug Mode**: Only enable detailed logging when needed

## Related Documentation

- [Error Handling](../features/error-handling-detailed.md)
- [Development Guidelines](coding-standards.md)

