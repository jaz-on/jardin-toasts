# Contributing Guide

## Overview

Thank you for your interest in contributing to Beer Journal! This guide will help you get started.

## Getting Started

### Prerequisites

- WordPress 6.0+
- PHP 8.2+
- Composer
- npm (for Gutenberg blocks)
- Git

---

### Development Setup

1. **Clone Repository**:
```bash
git clone https://github.com/your-username/beer-journal.git
cd beer-journal
```

2. **Install Dependencies**:
```bash
composer install
npm install
```

3. **Set Up WordPress**:
- Install WordPress locally
- Activate plugin
- Configure settings

---

## Development Workflow

### Branch Strategy

- **main**: Production-ready code
- **develop**: Development branch
- **feature/**: Feature branches
- **bugfix/**: Bug fix branches
- **hotfix/**: Critical fixes

---

### Creating a Branch

```bash
# Feature branch
git checkout -b feature/new-feature

# Bug fix branch
git checkout -b bugfix/fix-name
```

---

### Commit Messages

**Format**: Conventional Commits

**Examples**:
```
feat: Add rating filter to archive
fix: Resolve scraping timeout issue
docs: Update installation guide
refactor: Improve RSS parser performance
```

---

### Pull Request Process

1. **Create Branch**: From `develop`
2. **Make Changes**: Follow coding standards
3. **Write Tests**: Add/update tests
4. **Update Documentation**: Update relevant docs
5. **Submit PR**: To `develop` branch
6. **Code Review**: Address feedback
7. **Merge**: After approval

---

## Code Standards

### WordPress Coding Standards

- **WPCS**: Follow WordPress Coding Standards
- **PHPCS**: Run PHPCS before committing
- **PHPStan**: Level 5 minimum

**See**: [Coding Standards Documentation](coding-standards.md)

---

### Code Style

- **Indentation**: Tabs (not spaces)
- **Line Endings**: Unix (LF)
- **Naming**: See architecture rules
- **Comments**: PHPDoc on all functions

---

## Testing

### Unit Tests

**Framework**: PHPUnit

**Run checks**:
```bash
composer run phpcs
composer run phpstan
composer test
```

**Write Tests**:
- Test all public functions
- Test edge cases
- Test error handling

**See**: [Testing Documentation](testing.md)

---

### Manual Testing

**Checklist**:
- [ ] Test on WordPress 6.0+
- [ ] Test on PHP 8.2+
- [ ] Test with different themes
- [ ] Test with popular plugins
- [ ] Test error scenarios

---

## Documentation

### Code Documentation

- **PHPDoc**: All functions and classes
- **Inline Comments**: Complex logic
- **File Headers**: Purpose and author

---

### User Documentation

- **README**: Keep updated
- **Changelog**: Document changes
- **User Guides**: Update if needed

---

### Documentation Workflow

When updating documentation:

1. **Small corrections**: Commit directly to `docs` branch
2. **Feature documentation**: Create `docs/feature-name` branch
3. **Major updates**: Create `docs/update-topic` branch
4. **Validation**: Run `scripts/validate-docs.sh` before merging

**Documentation Structure**:
- Architecture docs: `docs/architecture/`
- Database schema: `docs/db/`
- Features: `docs/features/`
- User flows: `docs/user-flows/`
- Development guides: `docs/development/`

**Tools**:
- `scripts/validate-docs.sh` - Validate documentation
- `scripts/analyze-docs.php` - Analyze and generate reports
- [Prompts réutilisables](prompts-reutilisables.md) - AI-assisted analysis
- [Template de plan](template-plan-developpement.md) - Development plan template

**Before merging documentation**:
- [ ] All links work correctly
- [ ] Mermaid diagrams are valid
- [ ] Naming conventions are consistent (bj_, BJ_, _bj_)
- [ ] Cross-references are correct
- [ ] Validation script passes

---

## Submission Checklist

Before submitting a PR:

- [ ] Code follows WordPress Coding Standards
- [ ] All tests pass
- [ ] Documentation updated
- [ ] No PHP errors or warnings
- [ ] No JavaScript errors
- [ ] Tested on multiple WordPress versions
- [ ] Tested with different themes
- [ ] Security: Input sanitized, output escaped
- [ ] Performance: No obvious performance issues

---

## Related Documentation

- [Coding Standards](coding-standards.md)
- [Testing](testing.md)
- [Build Process](build-process.md)

