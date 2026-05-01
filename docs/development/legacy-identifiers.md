# Identifiants et migration (`jb` → `jt`)

Le code et la base utilisent désormais le préfixe **`jt_`** (options), **`_jt_`** (post meta), **`JT_`** (classes PHP), **`jt_`** (fonctions globales, transients).

Les **hooks** cron et AJAX « publics » utilisent en priorité le préfixe long **`jardin_toasts_*`** ; des alias **`jt_*`** restent en place pour compatibilité (extensions, vieux scripts).

À l’**activation / `plugins_loaded`**, `JT_Storage_Migration` enchaîne :

1. **`maybe_migrate()`** — import unique depuis beer-journal / `bj_*` vers `jt_*` (sauf si une ancienne version avait déjà posé `jb_storage_migrated_v1`).
2. **`maybe_migrate_jb_prefix_storage_to_jt()`** — copie puis suppression de toutes les options `jb_*` vers `jt_*`, renommage des métas `_jb_*` → `_jt_*`, purge des transients `jb_*`, nettoyage WP-Cron / Action Scheduler pour les noms de hooks `jb_*` et `jt_*` sur les groupes `beer-journal`, `jardin-beer`, `jardin-toasts`.
3. **`maybe_migrate_product_rename()`** — chemins / blocs `jardin-beer` → `jardin-toasts` dans `post_content` (sauf si l’ancien drapeau `jb_jardin_toasts_product_rename_v1` est déjà présent).

Les signets admin obsolètes (`jardin-beer`, `jardin-beer-settings`, `jb_jardin_beer_settings`, …) sont toujours redirigés vers l’écran réglages actuel.

---

## Alias `jardin_toasts_*` / `jt_*` (non exhaustif)

Référence pratique : constantes dans `Jardin_Toasts_Keys` ([`includes/functions.php`](../../includes/functions.php)).

| Domaine | Canonique (`jardin_toasts_*`) | Alias (`jt_*`) |
|--------|-------------------------------|------------------|
| Nonce AJAX admin | `jardin_toasts_admin` (`NONCE_ADMIN_AJAX`) | — |
| Action AJAX sync | `jardin_toasts_sync_now` | `jt_sync_now` (même callback) |
| Action AJAX crawl discover | `jardin_toasts_crawl_discover` | `jt_crawl_discover` |
| Action AJAX crawl batch | `jardin_toasts_crawl_batch` | `jt_crawl_batch` |
| Cron / AS | `jardin_toasts_rss_sync`, `jardin_toasts_rss_queue_tick`, `jardin_toasts_background_import_batch`, `jardin_toasts_daily_log_cleanup` | Anciens `jt_*` / `jb_*` / `bj_*` nettoyés par migration |
| Bulk action liste check-ins | `jardin_toasts_bulk_rescrape` | `jt_bulk_rescrape` (handler accepte les deux) |
| Query args après bulk rescrape | `jardin_toasts_rescraped`, `jardin_toasts_rescrape_total`, `jardin_toasts_rescrape_cap` | Lecture aussi de `jt_rescraped`, `jt_rescrape_total`, `jt_rescrape_cap` (notice admin) |
| Filtres (exemples) | `jardin_toasts_rating_labels`, `jardin_toasts_default_rss_feed_url`, `jardin_toasts_rating_rules`, … | `jt_rating_labels`, `jt_default_rss_feed_url`, `jt_rating_rules`, … (souvent appliqués en chaîne, voir le code) |
| User-Agent HTTP | `jardin_toasts_http_user_agent` | `jt_http_user_agent` — helper `jt_http_user_agent_string()` |
| Actions import | `jardin_toasts_before_checkin_import`, `jardin_toasts_after_checkin_imported` | `jt_before_checkin_import`, `jt_after_checkin_imported` |
| Activation / désactivation | `jardin_toasts_plugin_activated`, `jardin_toasts_plugin_deactivated` | `jt_plugin_activated`, `jt_plugin_deactivated` |

Pour les **filtres** du thème ou d’extensions, préférer le nom **`jardin_toasts_*`** ; les alias **`jt_*`** restent pris en charge pour ne pas casser le code existant.
