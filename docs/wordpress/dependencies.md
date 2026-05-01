# Dependencies

## Overview

Jardin Toasts dependencies: Composer packages, npm packages, and WordPress requirements.

## Composer Dependencies

### Production Dependencies

#### Guzzle HTTP Client

**Package**: `guzzlehttp/guzzle`

**Version**: `^7.8`

**Purpose**: HTTP client for fetching RSS feeds and scraping HTML pages

**Usage**:
- RSS feed fetching
- HTML page scraping
- Image downloading

**Alternative**: WordPress HTTP API (preferred when possible)

---

#### Symfony DomCrawler

**Package**: `symfony/dom-crawler`

**Version**: `^6.4`

**Purpose**: HTML parsing and DOM manipulation

**Usage**:
- Parsing Untappd HTML pages
- Extracting data using CSS selectors
- DOM traversal

**Required**: Yes (no alternative for HTML parsing)

---

#### Symfony CSS Selector

**Package**: `symfony/css-selector`

**Version**: `^6.4`

**Purpose**: CSS selector support for DomCrawler

**Usage**:
- CSS selector parsing
- Element selection

**Required**: Yes (dependency of DomCrawler)

---

### Development Dependencies

#### PHPUnit

**Package**: `phpunit/phpunit`

**Version**: `^10.5`

**Purpose**: Unit testing

**Usage**:
- Unit tests
- Integration tests

---

#### WordPress Coding Standards

**Package**: `wp-coding-standards/wpcs`

**Version**: `^3.0`

**Purpose**: Code quality and standards

**Usage**:
- PHPCS validation
- Coding standards enforcement

---

## npm Dependencies

### Production Dependencies

#### @wordpress/scripts

**Package**: `@wordpress/scripts`

**Version**: Latest

**Purpose**: Build tools for Gutenberg blocks (version 1.0.0)

**Usage**:
- Building Gutenberg blocks
- JavaScript/SCSS compilation
- Block development

**Required**: Required for Gutenberg blocks

---

## WordPress Built-in Dependencies

### SimplePie

**Library**: WordPress SimplePie (built-in)

**Purpose**: RSS feed parsing

**Usage**:
- Parsing RSS XML
- Extracting feed items

**Note**: WordPress includes SimplePie, no external dependency needed

---

### WordPress HTTP API

**API**: WordPress HTTP API

**Purpose**: HTTP requests

**Usage**:
- Fetching RSS feeds
- Downloading images
- Scraping HTML pages

**Preference**: Use WordPress HTTP API over Guzzle when possible

---

## Dependency Management

### Composer

**File**: `composer.json`

**Installation**:
```bash
composer install
```

**Production Install**:
```bash
composer install --no-dev
```

**Update**:
```bash
composer update
```

---

### npm

**File**: `package.json`

**Installation**:
```bash
npm install
```

**Build**:
```bash
npm run build
```

**Development**:
```bash
npm run start
```

---

## Version Constraints

### Semantic Versioning

**Policy**: Follow semantic versioning (SemVer)

**Format**: `MAJOR.MINOR.PATCH`

**Breaking Changes**: Only in MAJOR versions

---

### Dependency Versions

**Policy**: Use caret (^) for compatible updates

**Example**: `^7.8` allows `7.8.0` to `7.9.9` (not `8.0.0`)

---

## Security Considerations

### Dependency Updates

**Policy**: Regular security updates

**Process**:
1. Monitor security advisories
2. Update dependencies promptly
3. Test after updates
4. Document breaking changes

---

### Vulnerability Scanning

**Tools**:
- Composer security checker
- npm audit
- WordPress security plugins

---

## License Compatibility

### GPL Compatibility

**Plugin License**: GPL-2.0-or-later

**Dependencies**:
- Guzzle: MIT (GPL compatible)
- Symfony: MIT (GPL compatible)
- WordPress: GPL-2.0-or-later (compatible)

**All dependencies are GPL-compatible**

---

## Related Documentation

- [Compatibility](compatibility.md)
- [Build Process](../development/build-process.md)

