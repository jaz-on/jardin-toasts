# Testing

## Overview

Testing strategy for Jardin Toasts: unit tests, integration tests, and manual testing.

## Testing Framework

### PHPUnit

**Version**: ^10.5

**Purpose**: Unit and integration tests

**Installation**:
```bash
composer require --dev phpunit/phpunit
```

---

## Test Structure

### Directory Structure

```
tests/
├── bootstrap.php
├── unit/
│   ├── test-rss-parser.php
│   ├── test-scraper.php
│   └── test-importer.php
└── integration/
    ├── test-import-flow.php
    └── test-sync-flow.php
```

---

### Bootstrap File

**File**: `tests/bootstrap.php`

**Purpose**: Set up WordPress test environment

**Content**:
```php
<?php
/**
 * Test bootstrap
 */

// Load WordPress test environment
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load WordPress test functions
require_once getenv('WP_TESTS_DIR') . '/includes/functions.php';

// Load plugin
require_once dirname(__DIR__) . '/jardin-toasts.php';
```

---

## Unit Tests

### RSS Parser Tests

**File**: `tests/unit/test-rss-parser.php`

**Tests**:
- RSS feed fetching
- XML parsing
- GUID extraction
- Data extraction from title

**Example**:
```php
class Test_RSS_Parser extends WP_UnitTestCase {
    public function test_fetch_rss_feed() {
        $parser = new JB_RSS_Parser();
        $feed = $parser->fetch_feed('https://untappd.com/rss/user/test');
        $this->assertNotFalse($feed);
    }
    
    public function test_parse_rss_item() {
        $parser = new JB_RSS_Parser();
        $item = $parser->parse_item($rss_item);
        $this->assertArrayHasKey('beer_name', $item);
    }
}
```

---

### Scraper Tests

**File**: `tests/unit/test-scraper.php`

**Tests**:
- HTML fetching
- DOM parsing
- Data extraction
- Error handling

---

### Importer Tests

**File**: `tests/unit/test-importer.php`

**Tests**:
- Data validation
- Post creation
- Taxonomy assignment
- Meta field setting
- Deduplication

---

## Integration Tests

### Import Flow Test

**File**: `tests/integration/test-import-flow.php`

**Tests**:
- Complete import process
- RSS → Scrape → Import
- Error handling
- Retry logic

---

### Sync Flow Test

**File**: `tests/integration/test-sync-flow.php`

**Tests**:
- RSS sync end-to-end
- GUID comparison
- Batch processing
- Checkpoint system

---

## Running Tests

### Run All Tests

```bash
composer test
```

---

### Run Specific Test

```bash
vendor/bin/phpunit tests/unit/test-rss-parser.php
```

---

### Run with Coverage

```bash
vendor/bin/phpunit --coverage-html coverage/
```

---

## Manual Testing

### Test Checklist

**Installation**:
- [ ] Install plugin
- [ ] Activate plugin
- [ ] No PHP errors
- [ ] Settings page accessible

**Synchronization**:
- [ ] RSS feed configured
- [ ] Sync runs successfully
- [ ] Check-ins imported
- [ ] Images imported
- [ ] Taxonomies created

**Frontend**:
- [ ] Archive displays check-ins
- [ ] Single check-in displays
- [ ] Taxonomies work
- [ ] Filters work
- [ ] Search works

**Error Handling**:
- [ ] Invalid RSS URL handled
- [ ] Scraping failures handled
- [ ] Network errors handled
- [ ] Drafts created for incomplete data

---

## Test Data

### Sample RSS Feed

**File**: `tests/data/sample-rss.xml`

**Purpose**: Test RSS parsing without live feed

---

### Sample HTML

**File**: `tests/data/sample-checkin.html`

**Purpose**: Test scraping without live pages

---

## Continuous Integration

### GitHub Actions (Future)

**Workflow**:
1. Run PHPCS
2. Run PHPStan
3. Run PHPUnit tests
4. Check WordPress compatibility

---

## Related Documentation

- [Coding Standards](coding-standards.md)
- [Build Process](build-process.md)

