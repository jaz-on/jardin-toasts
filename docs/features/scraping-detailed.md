# HTML Scraping - Detailed

## Overview

Detailed documentation of the HTML scraping system for extracting complete check-in metadata from Untappd pages.

## Why Scraping is Required

### RSS Limitations

The RSS feed only provides:
- Basic beer and brewery names
- Check-in URL
- Date
- Sometimes an image URL

**Missing Critical Data**:
- Rating (0-5) - **REQUIRED for publication**
- ABV % / IBU
- Beer style
- Full user comment
- Serving type
- Toast count
- Comment count

**Therefore**: Scraping is **mandatory** for complete data.

---

## HTML Structure Analysis

### Check-in Page Structure

**URL Format**: `https://untappd.com/user/{username}/checkin/{checkin_id}`

**Key Elements**:
```html
<div class="checkin-info">
  <!-- Main container -->
  
  <div class="beer-details">
    <h2>Beer Name</h2>
    <p>Brewery Name</p>
    <span>Beer Style</span>
  </div>
  
  <div class="details">
    <span>ABV: 5.5%</span>
    <span>IBU: 45</span>
  </div>
  
  <div class="rating-serving">
    <div class="rating">4.25</div>
    <span>Serving Type: Draft</span>
  </div>
  
  <div class="photo">
    <img src="https://images.untp.beer/..." />
  </div>
  
  <div class="checkin-comment">
    User's comment text here...
  </div>
  
  <div class="venue-name">
    Venue Name
  </div>
  
  <div class="caps">
    <span class="count">12</span> toasts
  </div>
</div>
```

---

## CSS Selectors

### Complete Selector Map

```php
$selectors = [
    // Main container
    'checkin_info' => '.checkin-info',
    
    // Beer information
    'beer_name' => '.beer-details h2',
    'brewery_name' => '.beer-details p',
    'beer_style' => '.beer-details span',
    
    // Beer details
    'abv' => '.details', // Parse from text
    'ibu' => '.details', // Parse from text
    
    // Rating and serving
    'rating' => '.rating-serving .rating',
    'serving_type' => '.rating-serving span',
    
    // Image
    'image' => '.photo img',
    'image_url' => '.photo img[src]',
    
    // Comment
    'comment' => '.checkin-comment',
    
    // Venue
    'venue' => '.venue-name',
    
    // Social
    'toast_count' => '.caps .count',
    'comment_count' => '.comment-count', // If available
];
```

---

## Data Extraction

### Rating Extraction

**Critical**: Rating is required for publication

**Implementation**:
```php
$rating_text = $crawler->filter('.rating-serving .rating')->text();
$rating = floatval(trim($rating_text));

// Validate
if ($rating < 0 || $rating > 5) {
    error_log('Jardin Toasts: Invalid rating - ' . $rating);
    return new WP_Error('invalid_rating', 'Rating must be 0-5');
}
```

---

### ABV and IBU Extraction

**Pattern Matching**:
```php
$details_text = $crawler->filter('.details')->text();
// "ABV: 5.5% | IBU: 45"

// Extract ABV
if (preg_match('/ABV:\s*([\d.]+)%/', $details_text, $matches)) {
    $abv = floatval($matches[1]);
} else {
    $abv = null;
}

// Extract IBU
if (preg_match('/IBU:\s*(\d+)/', $details_text, $matches)) {
    $ibu = intval($matches[1]);
} else {
    $ibu = null;
}
```

---

### Comment Extraction

**Full Text**:
```php
$comment = $crawler->filter('.checkin-comment')->text();
$comment = trim($comment);

// May be empty
if (empty($comment)) {
    $comment = '';
}
```

---

## Error Handling

### Selector Failures

**Scenario**: Untappd changes HTML structure

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
        return new WP_Error('rating_not_found', 'Rating not found');
    }
}
```

---

### Missing Elements

**Strategy**: Use fallback values or skip

**Implementation**:
```php
// Try primary selector
$beer_style = $crawler->filter('.beer-details span')->text();

// If empty, try alternative
if (empty($beer_style)) {
    $beer_style = $crawler->filter('[data-style]')->attr('data-style');
}

// If still empty, use empty string (optional field)
if (empty($beer_style)) {
    $beer_style = '';
}
```

---

## Rate Limiting

### Importance

- **Respect Servers**: Avoid overwhelming Untappd
- **Prevent Bans**: Too many requests can result in IP blocking
- **Ethical**: Follow web scraping best practices

---

### Implementation

**Delay Between Requests**:
```php
$delay = get_option('jb_scraping_delay', 3); // Default 3 seconds

// Wait before next request
sleep($delay);

// Or use transients for more precise timing
$last_request = get_transient('jb_last_scrape_request');
if ($last_request && (time() - $last_request) < $delay) {
    $wait = $delay - (time() - $last_request);
    sleep($wait);
}
set_transient('jb_last_scrape_request', time(), 60);
```

---

### Recommended Delays

- **RSS Sync**: 2-3 seconds between check-ins
- **Historical Import**: 3-5 seconds between check-ins
- **Retry Attempts**: 5-10 seconds between retries

---

## Handling HTML Changes

### Risk

Untappd may change HTML structure at any time, breaking selectors.

---

### Mitigation Strategies

1. **Multiple Selectors**: Try multiple selector patterns
2. **Fallback Values**: Use defaults when data missing
3. **Logging**: Log warnings when selectors fail
4. **Admin Notifications**: Alert admin of scraping issues
5. **Graceful Degradation**: Import what's available

---

### Example Fallback Chain

```php
// Try primary selector
$rating = $crawler->filter('.rating-serving .rating')->text();

// If empty, try alternative
if (empty($rating)) {
    $rating = $crawler->filter('[data-rating]')->attr('data-rating');
}

// If still empty, try text content
if (empty($rating)) {
    $rating = $crawler->filter('.rating')->text();
}

// If still empty, log and save as draft
if (empty($rating)) {
    error_log('Jardin Toasts: Could not extract rating from ' . $url);
    // Save as draft with reason
    update_post_meta($post_id, '_jb_incomplete_reason', 'missing_rating');
    return;
}
```

---

## Performance Optimization

### Caching

**Don't Cache HTML**: Always fetch fresh data

**Cache Parsed Data**: Cache extracted data for failed retries

---

### Parallel Processing

**Not Recommended**: Respect rate limits

**Sequential**: Process one check-in at a time

---

## Related Documentation

- [Scraping Architecture](../architecture/scraping.md)
- [Error Handling Detailed](error-handling-detailed.md)

