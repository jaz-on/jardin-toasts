# WordPress.org Submission Checklist

## Overview

Complete checklist for submitting Beer Journal to WordPress.org plugin directory.

## Required Files

### Main Plugin File

**File**: `beer-journal.php`

**Requirements**:
- [x] Standard WordPress plugin headers
- [x] GPL v2+ license declaration
- [x] Version number
- [x] Author information
- [x] Description
- [x] Text domain declaration

**Headers Example**:
```php
<?php
/**
 * Plugin Name: Beer Journal for Untappd
 * Plugin URI: https://wordpress.org/plugins/beer-journal/
 * Description: Import and display your Untappd beer check-ins
 * Version: 1.0.0
 * Author: jazon
 * Author URI: https://example.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: beer-journal
 * Domain Path: /languages
 */
```

---

### readme.txt

**File**: `readme.txt`

**Requirements**:
- [x] WordPress.org format
- [x] All required sections
- [x] Screenshots section
- [x] Changelog
- [x] FAQ section

**See**: [readme.txt](../../readme.txt) in project root

---

### LICENSE

**File**: `LICENSE`

**Requirements**:
- [x] Full GPL v2+ license text
- [x] No modifications to license text

**See**: [LICENSE](../../LICENSE) in project root

---

## Code Requirements

### GPL License

- [x] GPL v2+ license declared in all PHP files
- [x] License compatible with all dependencies
- [x] No proprietary code

---

### No Minified Code

- [x] No minified JavaScript in repository
- [x] No minified CSS in repository
- [x] Build files excluded from SVN
- [x] Source files included

**Note**: Minified files can be in `.gitignore` but must be in SVN for WordPress.org

---

### Data Sanitization

- [x] All input sanitized
- [x] `sanitize_text_field()` for text
- [x] `wp_kses_post()` for rich text
- [x] `esc_url_raw()` for URLs
- [x] `floatval()` / `absint()` for numbers

---

### Output Escaping

- [x] All output escaped
- [x] `esc_html()` for HTML
- [x] `esc_attr()` for attributes
- [x] `esc_url()` for URLs
- [x] `wp_json_encode()` for JSON

---

### Nonces

- [x] All forms include nonces
- [x] All AJAX requests verify nonces
- [x] `wp_nonce_field()` for forms
- [x] `check_ajax_referer()` for AJAX
- [x] `check_admin_referer()` for admin actions

---

### Capability Checks

- [x] All admin actions check capabilities
- [x] `current_user_can('manage_options')` for settings
- [x] `current_user_can('edit_post')` for editing
- [x] Proper capability mapping

---

### No Phone-Home

- [x] No external tracking without opt-in
- [x] No analytics without user consent
- [x] No data collection without disclosure

---

### Function Prefixes

- [x] All functions prefixed with `bj_`
- [x] All classes prefixed with `BJ_`
- [x] All constants prefixed with `BJ_`
- [x] No conflicts with other plugins

---

## Assets for WordPress.org

### Banner

**File**: `.wordpress-org/banner-1544x500.png`

**Requirements**:
- [x] Size: 1544×500 pixels
- [x] PNG format
- [x] Represents plugin functionality
- [x] Professional design

---

### Icon

**File**: `.wordpress-org/icon-256x256.png`

**Requirements**:
- [x] Size: 256×256 pixels
- [x] PNG format
- [x] Square format
- [x] Clear and recognizable

---

### Screenshots

**Files**: `.wordpress-org/screenshot-*.png`

**Requirements**:
- [x] Minimum 1 screenshot
- [x] Recommended: 1280×960 pixels
- [x] PNG or JPG format
- [x] Show plugin functionality

**Suggested Screenshots**:
1. Check-ins archive page
2. Single check-in view
3. Settings page
4. Rating system configuration
5. Historical import interface

---

## Internationalization

### Text Domain

- [x] Text domain: `beer-journal`
- [x] Consistent throughout codebase
- [x] All user-facing strings translatable

---

### .pot File

**File**: `languages/beer-journal.pot`

**Requirements**:
- [x] Generated from codebase
- [x] All translatable strings included
- [x] Proper headers

**Generation**:
```bash
wp i18n make-pot . languages/beer-journal.pot
```

---

### Language Files

**Structure**: `languages/beer-journal-{locale}.po`

**Optional**: Include translations if available

---

## Security Best Practices

### Input Validation

- [x] Validate all user input
- [x] Check data types
- [x] Check ranges
- [x] Validate URLs

---

### SQL Injection Prevention

- [x] Use `$wpdb->prepare()` for queries
- [x] Use WordPress query functions
- [x] No direct SQL queries with user input

---

### XSS Prevention

- [x] Escape all output
- [x] Use `wp_kses()` for rich content
- [x] Sanitize before display

---

### CSRF Prevention

- [x] Nonces on all forms
- [x] Nonces on all AJAX requests
- [x] Verify nonces before processing

---

## Performance

### Database Queries

- [x] Optimized queries
- [x] Use indexes
- [x] Avoid N+1 queries
- [x] Cache expensive operations

---

### Asset Loading

- [x] Conditional asset loading
- [x] Proper dependencies
- [x] Version numbers for cache busting

---

## Documentation

### Code Documentation

- [x] PHPDoc on all functions
- [x] Inline comments for complex logic
- [x] File headers with purpose

---

### User Documentation

- [x] README.txt complete
- [x] FAQ section
- [x] Installation instructions
- [x] Screenshots

---

## Testing

### Functionality

- [x] All features tested
- [x] Edge cases handled
- [x] Error handling tested

---

### Compatibility

- [x] Tested with WordPress 6.0+
- [x] Tested with PHP 8.2+
- [x] Tested with various themes
- [x] Tested with popular plugins

---

## SVN Structure

### Required Structure

```
beer-journal/
├── beer-journal.php
├── readme.txt
├── LICENSE
├── .wordpress-org/
│   ├── banner-1544x500.png
│   ├── icon-256x256.png
│   └── screenshot-*.png
├── includes/
├── admin/
├── public/
└── languages/
```

---

### Excluded from SVN

- `.git/`
- `node_modules/`
- `vendor/` (or include if needed)
- `.gitignore`
- Development files

---

## Submission Process

### 1. Prepare Repository

- [x] All files ready
- [x] Code reviewed
- [x] Documentation complete
- [x] Assets created

---

### 2. Create SVN Repository

- [ ] Request plugin directory access
- [ ] Create SVN repository
- [ ] Initial commit

---

### 3. Upload Files

- [ ] Upload to SVN
- [ ] Verify file structure
- [ ] Test installation from SVN

---

### 4. Review Process

- [ ] Wait for review
- [ ] Address feedback
- [ ] Resubmit if needed

---

## Related Documentation

- [Assets](assets.md)
- [i18n](i18n.md)
- [Compatibility](compatibility.md)

