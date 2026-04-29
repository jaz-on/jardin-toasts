# Caching Strategy (Transients)

## Overview

Jardin Toasts utilise les Transients WordPress pour accélérer les opérations coûteuses (scraping, statistiques, requêtes). Les transients stockent des données temporaires avec une date d’expiration.

## Conventions

- Préfixe des clés: `jb_`
- Nommage: `jb_{domaine}_{identifiant}` (court et déterministe)
  - Exemples:
    - `jb_scrape_{checkinId}`
    - `jb_global_stats`
    - `jb_query_archive_{hash}`
- TTL recommandés:
  - Scraping: 3 heures
  - Statistiques globales: 1 heure
  - Requêtes d’archives: 30 minutes

## API WordPress

```php
// Écrire
set_transient('jb_key', $data, 3 * HOUR_IN_SECONDS);

// Lire
$data = get_transient('jb_key'); // false si expiré/absent

// Supprimer
delete_transient('jb_key');
```

## Helper suggéré (contrat)

Sans imposer une implémentation, les appels devraient suivre ce contrat logique:

```php
// Contrat logique recommandé
function jb_get_cached_data($key, callable $producer, int $ttlSeconds = null) {
    $cacheKey = 'jb_' . $key;
    $cached = get_transient($cacheKey);
    if ($cached !== false) {
        return $cached;
    }
    $data = $producer();
    $ttl = $ttlSeconds ?? (3 * HOUR_IN_SECONDS); // défaut 3h
    set_transient($cacheKey, $data, $ttl);
    return $data;
}
```

## Invalidation

- Après import/sync, invalider:
  - Statistiques globales (`jb_global_stats`)
  - Requêtes d’archives liées (clés dérivées)
  - Entrées de scraping si la page a été rafraîchie
- Invalidation ciblée préférable à un “clear all” global.

## Option A (MVP)

- Caching automatique, sans UI.
- TTLs indiqués ci‑dessus.

## Option B (v1.5, future)

- `jb_cache_enabled` (bool) et `jb_cache_hours` (int)
- UI dans Settings > Advanced avec bouton “Clear cache”

## Bonnes pratiques

- Ne pas mettre en cache des données sensibles.
- Toujours prévoir un fallback si `get_transient` retourne `false`.
- Utiliser des TTL raisonnables et documentés.
- Documenter les clés de cache critiques dans cette page.

## Clés de cache (répertoire)

- `jb_global_stats`: statistiques agrégées (1h)
- `jb_top_breweries`: top brasseries (1j)
- `jb_query_archive_{hash}`: résultats d’archives (30min)
- `jb_scrape_{checkinId}`: résultat de scraping (3h)


