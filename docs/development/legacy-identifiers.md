# Identifiants et migration legacy

État actuel (mai 2026) : le code et la base utilisent **un seul** préfixe canonique aligné sur les autres plugins de l'écosystème jardin :

| Type | Canonique |
|---|---|
| Classes PHP | `Jardin_Toasts_*` |
| Constantes globales | `JARDIN_TOASTS_*` (ex. `JARDIN_TOASTS_VERSION`, `JARDIN_TOASTS_PLUGIN_DIR`, `JARDIN_TOASTS_RSS_FEED_URL`) |
| Fonctions globales | `jardin_toasts_*()` |
| Hooks (filtres/actions) | `jardin_toasts_*` |
| Options `wp_options` | `jardin_toasts_*` |
| Post meta | `_jardin_toasts_*` |
| Transients | `jardin_toasts_*` |
| Préfixe CSS plugin | `jardin-toasts-*` (classes), `jardin-toasts-*` (IDs) |

## Migration automatique

À l'**activation / `plugins_loaded`**, `Jardin_Toasts_Storage_Migration` enchaîne les étapes (chaque étape est idempotente, marquée par un flag `_migrated_v1`) :

1. **`maybe_migrate()`** — import unique depuis l'ancien plugin **beer-journal** : options `bj_*` → `jardin_toasts_*`, post meta `_bj_*` → `_jt_*` (puis `_jardin_toasts_*` à l'étape 4).
2. **`maybe_migrate_jb_prefix_storage_to_jt()`** — copie puis suppression des options `jb_*` → `jardin_toasts_*`, métas `_jb_*` → `_jt_*` (puis `_jardin_toasts_*` à l'étape 4), purge des transients `jb_*`.
3. **`maybe_migrate_product_rename()`** — chemins / blocs `jardin-beer` → `jardin-toasts` dans `post_content`.
4. **`maybe_migrate_nomenclature()`** — post meta `_jt_*` → `_jardin_toasts_*` (étape finale d'unification).

Les anciens noms de hooks WP-Cron / Action Scheduler (`jb_*`, `bj_*`, `jt_*`) sont également nettoyés par ces étapes via `Jardin_Toasts_Keys::legacy_jt_cron_hooks()` et `legacy_jt_and_jb_rss_hooks()`.

## Slugs admin redirigés

Les anciens signets admin (`jardin-beer`, `jardin-beer-settings`, `jb_jardin_beer_settings`, `feed-journal`, etc.) sont automatiquement redirigés vers la page de réglages actuelle (voir `Jardin_Toasts_Admin`).

## Pour les développeurs externes

Si un thème ou un autre plugin hooke un filtre de jardin-toasts, utiliser **uniquement** les noms `jardin_toasts_*`. Les anciens noms `jt_*` ne sont plus exposés depuis le refactor de mai 2026.
