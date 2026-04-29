# Helper Functions

## Overview

Jardin Toasts provides numerous helper functions for common operations. These functions are used throughout the plugin and can be used by theme developers.

## Taxonomy Functions

### `jb_get_or_create_term()`

Get an existing taxonomy term or create it if it doesn't exist.

**Signature**:
```php
function jb_get_or_create_term($taxonomy, $term_name, $args = [])
```

**Parameters**:
- `$taxonomy` (string): Taxonomy name (e.g., 'beer_style', 'brewery', 'venue')
- `$term_name` (string): Term name to get or create
- `$args` (array): Optional arguments for term creation
  - `parent` (int): Parent term ID (for hierarchical taxonomies)
  - `description` (string): Term description
  - `slug` (string): Custom slug

**Returns**: `WP_Term|WP_Error` - Term object on success, WP_Error on failure

**Example**:
```php
// Get or create a beer style
$term = jb_get_or_create_term('beer_style', 'IPA - New England / Hazy', [
    'parent' => $parent_term_id,
]);

if (!is_wp_error($term)) {
    wp_set_post_terms($post_id, [$term->term_id], 'beer_style');
}
```

**Location**: `includes/functions-taxonomy.php`

---

## Draft Management Functions

### `jb_get_draft_count()`

Get the total number of draft check-ins.

**Signature**:
```php
function jb_get_draft_count()
```

**Returns**: `int` - Number of draft check-ins

**Example**:
```php
$draft_count = jb_get_draft_count();
if ($draft_count > 0) {
    echo "You have {$draft_count} draft check-ins awaiting review.";
}
```

**Location**: `includes/functions-drafts.php`

---

### `jb_get_draft_breakdown()`

Get a breakdown of draft check-ins by reason.

**Signature**:
```php
function jb_get_draft_breakdown()
```

**Returns**: `array` - Associative array with reason as key and count as value
```php
[
    'missing_rating' => 5,
    'scraping_failed' => 2,
    'missing_beer_name' => 1,
]
```

**Example**:
```php
$breakdown = jb_get_draft_breakdown();
foreach ($breakdown as $reason => $count) {
    echo "{$reason}: {$count}\n";
}
```

**Location**: `includes/functions-drafts.php`

---

### `jb_get_draft_reason_label()`

Get a human-readable label for a draft reason.

**Signature**:
```php
function jb_get_draft_reason_label($reason)
```

**Parameters**:
- `$reason` (string): Draft reason code (e.g., 'missing_rating')

**Returns**: `string` - Translated label

**Example**:
```php
$label = jb_get_draft_reason_label('missing_rating');
// Returns: "Missing Rating" (or translated equivalent)
```

**Location**: `includes/functions-drafts.php`

---

## Import Functions

### `jb_scrape_and_import_checkin()`

Scrape a check-in URL and import it into WordPress.

**Signature**:
```php
function jb_scrape_and_import_checkin($checkin_url, $existing_post_id = null)
```

**Parameters**:
- `$checkin_url` (string): Untappd check-in URL
- `$existing_post_id` (int|null): Optional existing post ID to update

**Returns**: `int|WP_Error` - Post ID on success, WP_Error on failure

**Example**:
```php
$url = 'https://untappd.com/user/jaz_on/checkin/1527514863';
$result = jb_scrape_and_import_checkin($url);

if (is_wp_error($result)) {
    error_log('Import failed: ' . $result->get_error_message());
} else {
    echo "Check-in imported as post ID: {$result}";
}
```

**Location**: `includes/functions-import.php`

---

## Cache Helper (Guidelines)

### `jb_get_cached_data()` (contrat recommandé)

Centralise l’accès aux transients avec une clé normalisée et un TTL par défaut.

**Signature (contrat)**:
```php
function jb_get_cached_data($key, callable $producer, int $ttlSeconds = null)
```

**Paramètres**:
- `$key` (string): Suffixe de clé sans préfixe (`'jb_'` sera ajouté en interne)
- `$producer` (callable): Fonction productrice appelée en cas de cache manquant
- `$ttlSeconds` (int|null): TTL en secondes, défaut 3 heures si `null`

**Retour**: Valeur produite ou mise en cache (type mixte selon usage)

**Exemple**:
```php
$stats = jb_get_cached_data('global_stats', function () {
    // compute stats...
    return ['total' => 200, 'unique' => 150];
}, HOUR_IN_SECONDS);
```

**Conventions de clés**:
- Préfixe `jb_` ajouté automatiquement
- Noms courts et déterministes: `jb_global_stats`, `jb_scrape_{id}`, `jb_query_archive_{hash}`

**Notes**:
- Voir la page [Caching](../development/caching.md) pour TTL recommandés et invalidation.

---

## Logging Functions

### `jb_get_log_directory()`

Get the absolute path to the log directory.

**Signature**:
```php
function jb_get_log_directory()
```

**Returns**: `string` - Absolute path to log directory
```php
'/path/to/wp-content/uploads/jardin-toasts/logs/'
```

**Example**:
```php
$log_dir = jb_get_log_directory();
$log_file = $log_dir . 'jardin-toasts-' . date('Y-m-d') . '.log';
```

**Location**: `includes/functions-logging.php`

---

## Related Documentation

- [Template Tags](../frontend/template-tags.md) - Frontend helper functions
- [Coding Standards](coding-standards.md) - Function naming conventions
- [Error Handling](../features/error-handling-detailed.md) - Draft management

