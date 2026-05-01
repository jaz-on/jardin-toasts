# Métadonnées canoniques (plugins / thème)

Référence partagée pour l’alignement des en-têtes, des liens sous le nom du plugin et de la ligne « Version | By … ».

## URLs

| Usage | URL |
|-------|-----|
| Site auteur (extensions hors wordpress.org) | `https://jasonrouet.com` |
| Profil wordpress.org (soumission annuaire) | `https://profiles.wordpress.org/jaz_on/` |
| Donations | `https://ko-fi.com/jasonrouet` |
| Organisation GitHub | `https://github.com/jaz-on/` |

## Règle wordpress.org vs GitHub uniquement

- **Hors annuaire** : `Author URI` = `https://jasonrouet.com`. Filtrer `plugin_row_meta` pour ajouter au minimum **GitHub** (dépôt public) et **Donate** (Ko-fi). Pas de liens Support / avis w.org tant que le slug n’existe pas sur l’annuaire.
- **Sur wordpress.org** (ex. French Typo) : `Author URI` = profil w.org ; ajouter **Support**, documentation repo, lien d’avis, comme dans `french-typo.php`.

## En-tête PHP (plugins)

Ordre cible : `Plugin Name`, `Plugin URI`, `Description`, `Version`, `Requires at least`, `Tested up to`, `Requires PHP`, `Author`, `Author URI`, `License`, `License URI`, `Text Domain`, `Domain Path` (si présent), `GitHub Plugin URI`, `Primary Branch`.

## Admin WordPress

- Liens d’action : filtre `plugin_action_links_{plugin_basename( __FILE__ )}` avec **Settings** vers la vraie page d’admin.
- Ligne meta : `plugin_row_meta` (priorité 10), retour anticipé si `plugin_basename( __FILE__ ) !== $plugin_file`.

## `Primary Branch` (Git Updater)

- Valeur dans l’en-tête : **`main`** pour que les installs par défaut depuis GitHub suivent la branche stable.
- Pour un site de dev qui suit **`dev`**, configurer la branche dans l’admin Git Updater (pas besoin de `Primary Branch: dev` dans le dépôt).

### Exceptions par dépôt

- **Jardin Toasts** ([`jardin-toasts.php`](../../jardin-toasts.php)) : l’en-tête utilise volontairement **`Primary Branch: dev`** afin que les installs Git Updater par défaut sur l’environnement de développement suivent la branche d’intégration. Pour un site qui doit suivre **`main`**, régler la branche dans l’admin Git Updater plutôt que de changer le dépôt. Les autres plugins de l’org peuvent rester sur la règle **`main`** ci-dessus.
