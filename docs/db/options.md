# WordPress Options

## Overview

Jardin Toasts stores configuration and state in the WordPress `wp_options` table. **Canonical option names use the prefix `jt_`.** Defaults and sanitization live in [`includes/class-settings.php`](../../includes/class-settings.php) (`JT_Settings::get_defaults()`, `sanitize_value_for_key()`).

### Migration depuis beer-journal / `jb_*`

Les sites mis à jour depuis d’anciennes versions peuvent encore avoir des lignes `bj_*` / `jb_*` jusqu’à la migration. Le flux (options, métas, cron, blocs) est décrit dans **[Identifiants et migration](../development/legacy-identifiers.md)** — ne pas copier d’anciens noms `jb_*` dans du nouveau code.

---

## Options enregistrées (réglages + défauts)

Ces clés correspondent à `JT_Settings::get_defaults()` et aux lectures directes cohérentes dans le plugin.

### Synchronisation RSS

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jt_rss_feed_url` | string | URL du flux RSS Untappd | URL RSS par défaut du mainteneur (`jt_get_default_rss_feed_url()`) |
| `jt_sync_enabled` | bool | Sync planifiée (cron / Action Scheduler) | `true` |
| `jt_rss_max_per_run` | int | Plafond d’éléments traités par passe RSS (1–100) | `10` |
| `jt_last_imported_guid` | string | Dernier GUID vu (optimisation / état) | `""` (interne au flux, pas dans le formulaire) |
| `jt_last_checkin_date` | string | Dernière date de check-in importée (polling adaptatif) | `""` (interne) |
| `jt_last_rss_sync_at` | string | Horodatage ISO de la dernière sync RSS réussie | `""` |

### Compte Untappd

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jt_untappd_username` | string | Nom d’utilisateur Untappd (affichage / crawl) | `jt_get_default_untappd_username()` |
| `jt_excluded_checkins` | array | IDs de check-ins exclus de la sync | `[]` (interne + filtrage) |

### Système de notation

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jt_rating_rules` | array | Bandes min/max → étoile entière | `jt_get_default_rating_rules()` |
| `jt_rating_labels` | array | Libellés 0–5 | `jt_get_default_rating_labels()` |
| `jt_rating_rounding_enabled` | bool | Activer l’arrondi selon les règles | `true` |

### Import historique / file d’attente

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jt_import_checkpoint` | array | File crawl, état, compteurs | `[]` (interne) |
| `jt_import_batch_size` | int | Taille de lot | `25` |
| `jt_import_delay` | int | Délai entre requêtes (secondes) | `3` |
| `jt_import_mode` | string | `manual` ou `background` | `manual` |

### Images et placeholder

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jt_import_images` | bool | Importer les images dans la médiathèque | `true` |
| `jt_use_placeholder_image` | bool | Utiliser une image de substitution si échec | `true` |
| `jt_placeholder_image_id` | int | ID de pièce jointe (médiathèque) | `0` |

### Général / affichage

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jt_scraping_delay` | int | Délai minimum entre scrapes (secondes) | `3` |
| `jt_import_social_data` | bool | Données sociales (toasts, commentaires) | `true` |
| `jt_import_venues` | bool | Lieux / taxonomie venue | `true` |
| `jt_archive_layout` | string | `grid` ou `table` | `grid` |

### SEO

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jt_schema_enabled` | bool | JSON-LD Review / Product | `true` |
| `jt_microformats_enabled` | bool | Classes microformats (`h-entry`, etc.) | `true` |

### Notifications

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jt_notify_on_sync` | bool | Email après sync RSS réussie avec imports | `false` |
| `jt_notify_on_error` | bool | Email sur erreurs (sync, etc.) | `true` |
| `jt_notification_email` | string | Destinataire (vide = `admin_email`) | `""` |

### Debug

| Option | Type | Description | Défaut |
|--------|------|-------------|--------|
| `jt_debug_mode` | bool | Logs niveau DEBUG | `false` |
| `jt_log_retention_days` | int | Rétention des fichiers `.log` | `30` |

---

## Options internes (hors formulaire principal)

| Option | Rôle |
|--------|------|
| `jt_rss_sync_queue` | File d’attente persistée pour les imports RSS (voir `includes/functions.php`, `JT_RSS_Parser`). |
| `jt_db_index_checkin_v1` | État de l’index postmeta optionnel (`JT_DB_Install`) : `ok`, `failed`, ou vide. |
| `jt_placeholder_toggle_migrated` | Drapeau one-shot pour la bascule placeholder (`JT_Settings::maybe_migrate_placeholder_toggle`). |
| Drapeaux `jt_*_migrated_v1` / `jardin_toasts_cron_hooks_migrated_v1` | Migrations stockage / cron — voir `legacy-identifiers.md` et `uninstall.php`. |

---

## Transients

| Clé effective | Usage |
|-----------------|--------|
| `jt_{key}` via `jt_get_cached_data()` | Cache générique (ex. stats, compteur brouillons incomplets). |
| `jt_last_scrape_ts` | Cadence entre requêtes HTTP de scrape (`JT_Scraper`). |

Les transients `bj_*` / `jb_*` legacy sont purgés lors des migrations.

---

## Accès en PHP (API WordPress)

```php
$url = get_option( 'jt_rss_feed_url', '' );
$rules = get_option( 'jt_rating_rules', array() );
update_option( 'jt_sync_enabled', true, false ); // autoload no si besoin
```

Pour les clés avec défaut centralisé : `JT_Settings::get( 'jt_rss_feed_url' )`.

---

## Désactivation et désinstallation

- **Désactivation** : les options restent (réactivation possible). Les tâches cron / Action Scheduler associées au plugin sont retirées (`JT_Deactivator`).
- **Désinstallation** : liste explicite des options supprimées dans [`uninstall.php`](../../uninstall.php). Les **posts** `beer_checkin`, médias et logs **ne sont pas** supprimés automatiquement — voir le [README](../../README.md).

---

## Documentation liée

- [Meta Fields](meta-fields.md)
- [Schema](schema.md)
- [Indexes](indexes.md)
- [Identifiants et migration](../development/legacy-identifiers.md)
