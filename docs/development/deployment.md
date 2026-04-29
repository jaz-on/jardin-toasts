# Deployment Guide

## Overview

Process for deploying Jardin Toasts to WordPress.org and production environments.

## Pre-Deployment Checklist

### Code Quality

- [ ] All tests pass
- [ ] PHPCS validation passes
- [ ] PHPStan Level 5 passes
- [ ] No PHP errors or warnings
- [ ] No JavaScript errors

---

### Documentation

- [ ] README.txt updated
- [ ] CHANGELOG.md updated
- [ ] Code documentation complete
- [ ] User documentation complete

---

### Assets

- [ ] Banner created (1544×500)
- [ ] Icon created (256×256)
- [ ] Screenshots created (4-5)
- [ ] All assets in `.wordpress-org/`

---

### Internationalization

- [ ] .pot file generated
- [ ] All strings translatable
- [ ] Text domain consistent

---

## WordPress.org Deployment

### SVN Setup

**Initial Setup**:
```bash
svn co https://plugins.svn.wordpress.org/jardin-toasts/trunk jardin-toasts-svn
```

---

### File Structure

**Required Structure**:
```
jardin-toasts/
├── jardin-toasts.php
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

### Exclude from SVN

**Files to Exclude**:
- `.git/`
- `node_modules/`
- `vendor/` (or include if needed)
- `.gitignore`
- Development files
- Build files (unless needed)

---

### SVN Commands

**Add Files**:
```bash
svn add --force .
```

**Commit**:
```bash
svn ci -m "Version 1.0.0"
```

**Tag Release**:
```bash
svn cp trunk tags/1.0.0
svn ci -m "Tag version 1.0.0"
```

---

## Version Management

### Version Numbering

**Format**: `MAJOR.MINOR.PATCH`

**Examples**:
- `1.0.0` - Initial release
- `1.0.2` - Bug fix (patch)
- `1.1.0` - New features
- `2.0.0` - Breaking changes

---

### Update Version

**Files to Update**:
- `jardin-toasts.php` (header)
- `readme.txt` (Stable tag)
- `CHANGELOG.md`
- `package.json` (if applicable)

---

## Build Process

### Production Build

**Steps**:
1. Run tests
2. Build assets (if needed)
3. Generate .pot file
4. Validate code
5. Create release package

**See**: [Build Process Documentation](build-process.md)

---

## Release Process

### 1. Prepare Release

- [ ] Update version numbers
- [ ] Update changelog
- [ ] Run full test suite
- [ ] Create release notes

---

### 2. Create Release Package

- [ ] Build production assets
- [ ] Generate .pot file
- [ ] Create ZIP file
- [ ] Test installation from ZIP

---

### 3. Deploy to SVN

- [ ] Commit to trunk
- [ ] Create tag
- [ ] Update stable tag in readme.txt
- [ ] Submit for review (if first release)

---

### 4. Post-Release

- [ ] Announce release
- [ ] Update documentation
- [ ] Monitor for issues
- [ ] Address feedback

---

## GitHub Updater (Development Only)

### Purpose

During development phase, GitHub Updater allows automatic updates from GitHub releases without requiring WordPress.org submission. This is **removed before WordPress.org submission**.

### Configuration

**Plugin Headers** (in `jardin-toasts.php`):
```php
/**
 * Plugin Name: Jardin Toasts
 * ...
 * GitHub Plugin URI: jaz-on/jardin-toasts
 * GitHub Branch: main
 * Primary Branch: main
 */
```

**Composer Dependency** (optional):
```json
{
  "require-dev": {
    "afragen/git-updater": "^11.0"
  }
}
```

### Usage

1. Install GitHub Updater plugin (if using Composer dependency)
2. Create GitHub releases for each version
3. Plugin auto-updates from GitHub releases
4. **Before WordPress.org submission**: Remove GitHub headers from plugin file

### Removal Before Submission

**Required Steps**:
- [ ] Remove `GitHub Plugin URI` header
- [ ] Remove `GitHub Branch` header
- [ ] Remove `Primary Branch` header
- [ ] Remove `afragen/git-updater` from Composer (if added)
- [ ] Test plugin without GitHub Updater

**Note**: This is a repository configuration, not a plugin feature. It should not appear in the final WordPress.org submission.

---

## Related Documentation

- [Build Process](build-process.md)
- [Submission Checklist](../wordpress/submission-checklist.md)

