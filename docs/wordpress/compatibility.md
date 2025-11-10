# WordPress Compatibility

## Overview

Beer Journal compatibility requirements and tested versions.

## WordPress Versions

### Minimum Required

**WordPress**: 6.0 or higher

**Rationale**:
- Block Editor improvements
- REST API enhancements
- Performance improvements
- Security updates

---

### Tested Up To

**WordPress**: 6.7

**Testing**: Plugin tested and compatible with WordPress 6.7

---

### Future Compatibility

**Policy**: Maintain compatibility with latest WordPress versions

**Updates**: Plugin will be tested with each new WordPress major release

---

## PHP Versions

### Minimum Required

**PHP**: 8.2 or higher

**Rationale**:
- Typed properties
- Modern PHP features
- Performance improvements
- Security updates

---

### Recommended

**PHP**: 8.3 or higher

**Benefits**:
- Latest features
- Better performance
- Improved error handling

---

### PHP Extensions Required

- **curl**: For HTTP requests (or `allow_url_fopen`)
- **dom**: For HTML parsing (Symfony DomCrawler)
- **json**: For JSON processing
- **mbstring**: For string manipulation

**Check Extensions**:
```php
if (!extension_loaded('curl') && !ini_get('allow_url_fopen')) {
    // Error: No way to make HTTP requests
}
```

---

## Database Versions

### MySQL

**Minimum**: 5.7 or higher

**Recommended**: 8.0 or higher

---

### MariaDB

**Minimum**: 10.3 or higher

**Recommended**: 10.6 or higher

---

## Browser Compatibility

### Frontend

**Supported Browsers**:
- Chrome (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Edge (last 2 versions)

**Mobile**:
- iOS Safari (last 2 versions)
- Chrome Mobile (last 2 versions)

---

### Admin Interface

**Supported Browsers**:
- Same as frontend
- WordPress admin compatibility

---

## Server Requirements

### Memory

**Minimum**: 128MB PHP memory limit

**Recommended**: 256MB or higher

**For Large Imports**: 512MB or higher

---

### Execution Time

**Default**: 30 seconds (PHP default)

**For Historical Imports**: 
- Manual mode: Limited by browser timeout
- Background mode: No limit (WP-Cron)

---

### File Upload

**Minimum**: 2MB

**For Image Import**: 5MB or higher recommended

---

## WordPress Features Used

### Core Features

- Custom Post Types
- Taxonomies
- Post Meta
- Media Library
- REST API
- Cron API
- Settings API
- HTTP API
- Template Hierarchy

---

### Plugins Compatibility

**Tested With**:
- Popular caching plugins
- SEO plugins
- Security plugins

**Potential Conflicts**:
- Plugins that modify post queries
- Plugins that modify media handling
- Plugins that modify cron schedules

---

## Theme Compatibility

### Block Themes

**Compatible**: Yes

**Features**:
- Template hierarchy support
- Block editor integration (Phase 2)

---

### Classic Themes

**Compatible**: Yes

**Features**:
- Template hierarchy support
- Template override support

---

### Requirements

**Theme Requirements**: None

**Plugin is theme-agnostic**: Works with any WordPress theme

---

## Multisite Compatibility

### WordPress Multisite

**Compatible**: Yes

**Considerations**:
- Settings per site
- Network activation supported
- Site-specific check-ins

---

## Known Issues

### None Currently

No known compatibility issues at this time.

---

## Testing

### Tested Environments

- WordPress 6.0 - 6.7
- PHP 8.2 - 8.3
- MySQL 5.7, 8.0
- MariaDB 10.3, 10.6

---

### Continuous Testing

**Policy**: Test with each WordPress and PHP release

**Automated**: CI/CD testing (future)

---

## Related Documentation

- [Dependencies](dependencies.md)
- [Submission Checklist](submission-checklist.md)

