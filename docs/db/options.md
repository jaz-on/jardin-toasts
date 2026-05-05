# WordPress Options

## Overview

Jardin Toasts stores configuration and state in the WordPress `wp_options` table. **Canonical option names use the prefix `jardin_toasts_`.** Defaults and sanitization live in [`includes/class-settings.php`](../../includes/class-settings.php) (`Jardin_Toasts_Settings::get_defaults()`, `sanitize_value_for_key()`).

### Migration depuis beer-journal / `jb_*`

Les sites mis à jour depuis d’anciennes versions peuvent encore avoir des lignes `bj_*` / `jb_*` jusqu’à la migration. Le flux (options, métas, cron, blocs) est décrit dans **[Identifiants et migration](../development/legacy-identifiers.md)** — ne pas copier d’anciens noms `jb_*` dans du nouveau code.

---

## Options enregistrées (réglages + défauts)

Ces clés correspondent à `Jardin_Toasts_Settings::get_defaults()` et aux lectures directes cohérentes dans le plugin.

### Synchronisation RSS

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jardin_toasts_rss_feed_url` | string | URL du flux RSS Untappd | URL RSS par défaut du mainteneur (`jardin_toasts_get_default_rss_feed_url()`) |
| `jardin_toasts_sync_enabled` | bool | Sync planifiée (cron / Action Scheduler) | `true` |
| `jardin_toasts_rss_max_per_run` | int | Plafond d’éléments traités par passe RSS (1–100) | `10` |
| `jardin_toasts_last_imported_guid` | string | Dernier GUID vu (optimisation / état) | `""` (interne au flux, pas dans le formulaire) |
| `jardin_toasts_last_checkin_date` | string | Dernière date de check-in importée (polling adaptatif) | `""` (interne) |
| `jardin_toasts_last_rss_sync_at` | string | Horodatage ISO de la dernière sync RSS réussie | `""` |

### Compte Untappd

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jardin_toasts_untappd_username` | string | Nom d’utilisateur Untappd (affichage / crawl) | `jardin_toasts_get_default_untappd_username()` |
| `jardin_toasts_excluded_checkins` | array | IDs de check-ins exclus de la sync | `[]` (interne + filtrage) |

### Système de notation

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jardin_toasts_rating_rules` | array | Bandes min/max → étoile entière | `jardin_toasts_get_default_rating_rules()` |
| `jardin_toasts_rating_labels` | array | Libellés 0–5 | `jardin_toasts_get_default_rating_labels()` |
| `jardin_toasts_rating_rounding_enabled` | bool | Activer l’arrondi selon les règles | `true` |

### Import historique / file d’attente

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jardin_toasts_import_checkpoint` | array | File crawl, état, compteurs | `[]` (interne) |
| `jardin_toasts_import_batch_size` | int | Taille de lot | `25` |
| `jardin_toasts_import_delay` | int | Délai entre requêtes (secondes) | `3` |
| `jardin_toasts_import_mode` | string | `manual` ou `background` | `manual` |

### Images et placeholder

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jardin_toasts_import_images` | bool | Importer les images dans la médiathèque | `true` |
| `jardin_toasts_use_placeholder_image` | bool | Utiliser une image de substitution si échec | `true` |
| `jardin_toasts_placeholder_image_id` | int | ID de pièce jointe (médiathèque) | `0` |

### Général / affichage

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jardin_toasts_scraping_delay` | int | Délai minimum entre scrapes (secondes) | `3` |
| `jardin_toasts_import_social_data` | bool | Données sociales (toasts, commentaires) | `true` |
| `jardin_toasts_import_venues` | bool | Lieux / taxonomie venue | `true` |
| `jardin_toasts_archive_layout` | string | `grid` ou `table` | `grid` |

### SEO

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jardin_toasts_schema_enabled` | bool | JSON-LD Review / Product | `true` |
| `jardin_toasts_microformats_enabled` | bool | Classes microformats (`h-entry`, etc.) | `true` |

### Notifications

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jardin_toasts_notify_on_sync` | bool | Email après sync RSS réussie avec imports | `false` |
| `jardin_toasts_notify_on_error` | bool | Email sur erreurs (sync, etc.) | `true` |
| `jardin_toasts_notification_email` | string | Destinataire (vide = `admin_email`) | `""` |

### Debug

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jardin_toasts_debug_mode` | bool | Logs niveau DEBUG | `false` |
| `jardin_toasts_log_retention_days` | int | Rétention des fichiers `.log` | `30` |

---

## Options internes (hors formulaire principal)

| Option | Rôle |
|--------|------|
| `jardin_toasts_rss_sync_queue` | File d’attente persistée pour les imports RSS (voir `includes/functions.php`, `Jardin_Toasts_RSS_Parser`). |
| `jardin_toasts_db_index_checkin_v1` | État de l’index postmeta optionnel (`Jardin_Toasts_DB_Install`) : `ok`, `failed`, ou vide. |
| `jardin_toasts_placeholder_toggle_migrated` | Drapeau one-shot pour la bascule placeholder (`Jardin_Toasts_Settings::maybe_migrate_placeholder_toggle`). |
| Drapeaux `jardin_toasts_*_migrated_v1` / `jardin_toasts_cron_hooks_migrated_v1` | Migrations stockage / cron — voir `legacy-identifiers.md` et `uninstall.php`. |

---

## Transients

| Clé effective | Usage |
|-----------------|--------|
| `jardin_toasts_{key}` via `jardin_toasts_get_cached_data()` | Cache générique (ex. stats, compteur brouillons incomplets). |
| `jardin_toasts_last_scrape_ts` | Cadence entre requêtes HTTP de scrape (`Jardin_Toasts_Scraper`). |

Les transients `bj_*` / `jb_*` legacy sont purgés lors des migrations.

---

## Accès en PHP (API WordPress)

```php
$url = get_option( 'jardin_toasts_rss_feed_url', '' );
$rules = get_option( 'jardin_toasts_rating_rules', array() );
update_option( 'jardin_toasts_sync_enabled', true, false ); // autoload no si besoin
```

Pour les clés avec défaut centralisé : `Jardin_Toasts_Settings::get( 'jardin_toasts_rss_feed_url' )`.

---

## Désactivation et désinstallation

- **Désactivation** : les options restent (réactivation possible). Les tâches cron / Action Scheduler associées au plugin sont retirées (`Jardin_Toasts_Deactivator`).
- **Désinstallation** : liste explicite des options supprimées dans [`uninstall.php`](../../uninstall.php). Les **posts** `beer_checkin`, médias et logs **ne sont pas** supprimés automatiquement — voir le [README](../../README.md).

---

## Documentation liée

- [Meta Fields](meta-fields.md)
- [Schema](schema.md)
- [Indexes](indexes.md)
- [Identifiants et migration](../development/legacy-identifiers.md)
