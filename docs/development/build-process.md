# Build Process

## Overview

Build process for Beer Journal: dependencies, compilation, and asset optimization.

## Dependencies

### Composer

**File**: `composer.json`

**Installation**:
```bash
composer install
```

**Production**:
```bash
composer install --no-dev
```

---

### npm

**File**: `package.json`

**Installation**:
```bash
npm install
```

**Purpose**: Gutenberg blocks (Phase 2)

---

## Build Scripts

### Composer Scripts

**File**: `composer.json`

**Scripts**:
```json
{
    "scripts": {
        "phpcs": "phpcs --standard=WordPress .",
        "phpstan": "phpstan analyse",
        "test": "phpunit",
        "build": "npm run build"
    }
}
```

---

### npm Scripts

**File**: `package.json`

**Scripts**:
```json
{
    "scripts": {
        "build": "wp-scripts build",
        "start": "wp-scripts start",
        "lint:js": "wp-scripts lint-js",
        "lint:css": "wp-scripts lint-style"
    }
}
```

---

## Build Steps

### 1. Install Dependencies

```bash
composer install
npm install
```

---

### 2. Run Tests

```bash
composer test
```

---

### 3. Code Validation

```bash
composer phpcs
composer phpstan
```

---

### 4. Build Assets

**Gutenberg Blocks** (Phase 2):
```bash
npm run build
```

**Output**: `blocks/build/`

---

### 5. Generate .pot File

```bash
wp i18n make-pot . languages/beer-journal.pot
```

---

## Production Build

### Complete Build

**Script**:
```bash
#!/bin/bash
# build.sh

# Install dependencies
composer install --no-dev
npm install

# Build blocks
npm run build

# Generate .pot
wp i18n make-pot . languages/beer-journal.pot

# Run validation
composer phpcs
composer phpstan

echo "Build complete!"
```

---

## Development Build

### Watch Mode

**Blocks Development**:
```bash
npm run start
```

**Features**:
- Watch for changes
- Auto-rebuild
- Hot reload

---

## Asset Optimization

### Minification

**JavaScript**: Handled by `@wordpress/scripts`

**CSS**: Handled by `@wordpress/scripts`

**Note**: Minified files not included in repository (generated on build)

---

## Build Artifacts

### Generated Files

**Blocks**:
- `blocks/build/` - Compiled blocks

**Languages**:
- `languages/beer-journal.pot` - Translation template

**Note**: These files should be committed to repository

---

## CI/CD (Future)

### GitHub Actions

**Workflow**:
1. Install dependencies
2. Run tests
3. Run validation
4. Build assets
5. Generate .pot
6. Create release

---

## Related Documentation

- [Testing](testing.md)
- [Deployment](deployment.md)

