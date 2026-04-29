# WordPress.org Assets

## Overview

Assets required for WordPress.org plugin directory submission.

## Required Assets

### Banner

**File**: `.wordpress-org/banner-1544x500.png`

**Size**: 1544×500 pixels

**Format**: PNG

**Purpose**: Header banner displayed on plugin directory page

**Design Guidelines**:
- Professional and clear
- Represents plugin functionality
- Includes plugin name
- Readable at full size
- No text that becomes unreadable when scaled

**Content Suggestions**:
- Plugin name: "Jardin Toasts for Untappd"
- Tagline: "Import and display your Untappd check-ins"
- Visual: Beer-related imagery or check-in cards

---

### Icon

**File**: `.wordpress-org/icon-256x256.png`

**Size**: 256×256 pixels

**Format**: PNG

**Purpose**: Plugin icon displayed in directory and admin

**Design Guidelines**:
- Square format (will be displayed as square)
- Clear and recognizable at small sizes
- Simple design
- High contrast
- No text (icon only)

**Content Suggestions**:
- Beer mug icon
- Check-in card icon
- Beer journal icon

---

### Screenshots

**Files**: `.wordpress-org/screenshot-*.png`

**Size**: Recommended 1280×960 pixels

**Format**: PNG or JPG

**Purpose**: Visual demonstration of plugin features

**Number**: Minimum 1, recommended 4-5

**Naming**: `screenshot-1.png`, `screenshot-2.png`, etc.

**Content Suggestions**:

1. **Check-ins Archive** (screenshot-1.png)
   - Grid view of check-ins
   - Shows multiple check-in cards
   - Demonstrates visual layout

2. **Single Check-in** (screenshot-2.png)
   - Individual check-in page
   - Shows hero image, metadata, rating
   - Demonstrates detail view

3. **Settings Page** (screenshot-3.png)
   - Settings interface
   - Shows synchronization options
   - Demonstrates configuration

4. **Rating System** (screenshot-4.png)
   - Rating configuration
   - Shows mapping rules and labels
   - Demonstrates customization

5. **Historical Import** (screenshot-5.png)
   - Import interface
   - Shows progress tracking
   - Demonstrates batch import

---

## Optional Assets

### Small Banner

**File**: `.wordpress-org/banner-772x250.png`

**Size**: 772×250 pixels

**Format**: PNG

**Purpose**: Smaller banner for some contexts

**Note**: Optional, but recommended

---

### Small Icon

**File**: `.wordpress-org/icon-128x128.png`

**Size**: 128×128 pixels

**Format**: PNG

**Purpose**: Smaller icon variant

**Note**: Optional, WordPress.org will scale 256×256 if needed

---

## Asset Guidelines

### Design Principles

- **Professional**: High-quality, polished design
- **Clear**: Easy to understand at a glance
- **Consistent**: Matches plugin branding
- **Accessible**: Readable and clear

---

### Technical Requirements

- **Format**: PNG (preferred) or JPG
- **Quality**: High resolution, no compression artifacts
- **Colors**: RGB color space
- **Transparency**: Use PNG for transparency if needed

---

### Content Guidelines

- **No Trademark Violations**: Don't use Untappd logos without permission
- **Accurate Representation**: Show actual plugin functionality
- **No Misleading Content**: Accurately represent features
- **Appropriate Content**: Follow WordPress.org guidelines

---

## Asset Creation

### Tools

**Recommended**:
- Adobe Photoshop
- GIMP (free)
- Figma
- Canva

---

### Templates

**WordPress.org Templates**: Available on WordPress.org developer resources

**Custom Templates**: Create based on plugin branding

---

## File Organization

### Directory Structure

```
jardin-toasts/
├── .wordpress-org/
│   ├── banner-1544x500.png
│   ├── banner-772x250.png (optional)
│   ├── icon-256x256.png
│   ├── icon-128x128.png (optional)
│   ├── screenshot-1.png
│   ├── screenshot-2.png
│   ├── screenshot-3.png
│   ├── screenshot-4.png
│   └── screenshot-5.png
```

---

### SVN Upload

**Location**: `.wordpress-org/` directory in SVN

**Note**: This directory is included in WordPress.org repository

---

## Screenshot Best Practices

### Content

- **Show Real Data**: Use actual plugin screenshots, not mockups
- **Highlight Features**: Focus on key functionality
- **Clear Labels**: Add text labels if helpful
- **Consistent Style**: Use same theme/style across screenshots

---

### Technical

- **High Resolution**: 1280×960 or higher
- **Clean Screenshots**: No browser chrome, clean interface
- **Consistent Browser**: Use same browser for all screenshots
- **Proper Cropping**: Focus on relevant content

---

## Related Documentation

- [Submission Checklist](submission-checklist.md)
- [WordPress.org Guidelines](https://developer.wordpress.org/plugins/wordpress-org/)

