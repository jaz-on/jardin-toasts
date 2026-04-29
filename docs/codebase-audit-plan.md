# Plan d'audit et de documentation du codebase Jardin Toasts

## Vue d'ensemble
Ce plan suit la stratégie "Docs First, Code Second" pour auditer et documenter le plugin WordPress Jardin Toasts avant d'écrire du code. L'objectif est de transformer un nouveau dépôt en système documenté et compréhensible.

**Contexte du projet :**
- Plugin WordPress pour synchroniser les check-ins Untappd
- Pas d'API officielle → scraping HTML nécessaire
- RSS feed limité (25 derniers check-ins)
- Import d'images dans Media Library
- Templates surchargeables (thème-agnostique)
- Publication prévue sur WordPress.org
- ~200 check-ins historiques à importer
- Système de notation avec labels personnalisables
- Polling adaptatif selon activité utilisateur

---

## Phase 1 : Configuration initiale

### Étape 1.1 : Cloner et configurer le dépôt
- [ ] Cloner le dépôt localement (si pas déjà fait)
- [ ] Vérifier la structure de base du projet
- [ ] Initialiser Git si nécessaire
- [ ] Vérifier les prérequis système :
  - PHP 8.3
  - WordPress 6.8
  - MySQL 5.7+ / MariaDB 10.3+
  - Extensions PHP : curl, dom, json, mbstring

### Étape 1.2 : Configurer Cursor avec les règles globales
- [ ] Créer le dossier `.cursor/` à la racine
- [ ] Créer `.cursor/rules/` pour les règles spécifiques au projet
- [ ] Créer `.cursorrules` ou intégrer dans les règles globales de Cursor

**Règles à inclure :**
- Conventions WordPress — respecter les standards WordPress Coding Standards
- Extensions de code existant — ne jamais réinventer
- Code auto-documenté — identifiants expressifs, commentaires minimaux
- DRY + KISS — réutiliser les abstractions, solution la plus simple
- Séparation des préoccupations — models, repositories, routes dans leurs packages
- Gestion explicite des erreurs — pas d'exceptions non capturées
- Documentation — docstrings sur tous les symboles publics
- Documentation dans `/docs/**/*.md`
- Architecture dans `.cursor/rules/*.mdc`
- Préfixe des fonctions : `jardin_toasts_` (ou `jb_` pour les fonctions courtes)
- Préfixe des classes : `JB_` (ex: `JB_Importer`, `JB_Scraper`)
- Text domain : `jardin-toasts`
- Support WordPress : 6.0+ minimum
- Sécurité : sanitization, escaping, nonces, capability checks
- Internationalisation : toutes les chaînes visibles traduisibles
- Performance : cache, index database, lazy loading

### Étape 1.3 : Créer la structure de documentation
- [ ] Créer `/docs/` à la racine
- [ ] Créer `/docs/db/` pour la documentation de la base de données
- [ ] Créer `/docs/api/` pour la documentation API (si applicable)
- [ ] Créer `/docs/architecture/` pour la documentation d'architecture
- [ ] Créer `/docs/user-flows/` pour les flux utilisateur
- [ ] Créer `/docs/features/` pour les fonctionnalités
- [ ] Créer `/docs/wordpress/` pour la documentation WordPress.org
- [ ] Créer `/docs/development/` pour la documentation de développement
- [ ] Créer `/docs/frontend/` pour la documentation frontend

---

## Phase 2 : Modèle de branches (mise à jour 2026-04)

L’approche « branche Git nommée `docs` » a été **abandonnée**. À la place :

- **`main`** : branche par défaut GitHub ; référence + releases.
- **`dev`** : intégration quotidienne (ex. Git Updater sur un site de dev).
- Le dossier **`docs/`** est commité sur **`dev`** et **`main`** comme le reste du dépôt.

Pour de gros chantiers uniquement documentation, utiliser une branche **`feature/docs-…`** depuis **`dev`**, puis merger dans **`dev`**.

**Références :** [DEVELOPMENT.md](../DEVELOPMENT.md), [workflow-documentation.md](development/workflow-documentation.md).

---

## Phase 3 : Les sept grandes questions à poser à Cursor

### Question 1 : Qu'est-ce que fait réellement le codebase ?
**Objectif :** Créer un README lisible et complet

- [ ] Demander à Cursor d'analyser le projet et générer un README.md
- [ ] Inclure :
  - Description du plugin (synchronisation check-ins Untappd)
  - Installation et activation
  - Configuration initiale (URL RSS Untappd)
  - Utilisation de base (sync automatique, import historique)
  - Prérequis système (PHP, WordPress, extensions)
  - Structure du projet
  - Fonctionnalités principales
  - Contribution
  - Licence (GPL v2+)
  - Liens vers documentation complète

**Fichier à créer :** `/README.md`

**Références spécifiques :**
- Contraintes du projet (pas d'API, scraping nécessaire)
- Volumétrie (~200 check-ins historiques)
- Publication WordPress.org

### Question 2 : Backend et architecture
**Objectif :** Documenter les couches, packages, services et leurs interactions

- [ ] Analyser la structure du plugin WordPress selon les specs
- [ ] Identifier et documenter les composants principaux :

#### 2.1 Système de synchronisation RSS
- [ ] Documenter `includes/class-rss-parser.php` (ou `class-rss-sync.php`)
- [ ] Polling adaptatif intelligent (6h/jour/semaine selon activité)
- [ ] Optimisation ressources (comparaison GUID avant scraping)
- [ ] Structure du flux RSS Untappd
- [ ] Limitations du RSS (pas de rating, ABV, style, etc.)
- [ ] Processus de synchronisation étape par étape
- [ ] Gestion des erreurs et retry logic (3 tentatives)

#### 2.2 Système de scraping HTML
- [ ] Documenter `includes/class-scraper.php` (ou `class-crawler.php`)
- [ ] Utilisation de Symfony DomCrawler
- [ ] Sélecteurs HTML à parser (`.checkin-info`, `.beer-details`, etc.)
- [ ] Extraction des métadonnées complètes
- [ ] Gestion des timeouts et erreurs réseau
- [ ] Rate limiting et délais entre requêtes

#### 2.3 Système d'import et traitement
- [ ] Documenter `includes/class-importer.php`
- [ ] Logique de publication (Scénario A - Rating obligatoire)
- [ ] Données obligatoires vs optionnelles
- [ ] Système de retry automatique + manuel
- [ ] Gestion des drafts et notifications admin
- [ ] Déduplication (par ID Untappd)

#### 2.4 Gestion des Custom Post Types
- [ ] Documenter `includes/class-post-type.php`
- [ ] CPT `jb_checkin` (ou `beer_checkin`)
- [ ] Configuration (public, REST API, supports, etc.)
- [ ] Structure du post (title, content, date, featured image)

#### 2.5 Système de taxonomies
- [ ] Documenter `includes/class-taxonomies.php`
- [ ] Taxonomie `jb_beer_style` (hiérarchique)
- [ ] Taxonomie `jb_brewery` (non-hiérarchique)
- [ ] Taxonomie `jb_venue` (non-hiérarchique, optionnelle)
- [ ] Auto-création immédiate avec notification admin
- [ ] Gestion des doublons et merge

#### 2.6 Gestion des métadonnées
- [ ] Documenter `includes/class-meta-fields.php`
- [ ] Identifiants uniques (`_jb_checkin_id`, `_jb_beer_id`, etc.)
- [ ] Données bière (nom, brasserie, style, ABV, IBU, description)
- [ ] Données check-in (rating, serving type, date)
- [ ] Données lieu (venue, city, country, lat/lng)
- [ ] Données sociales (toasts, comments, badges)
- [ ] Métadonnées techniques (source, scraped_at, attempts)

#### 2.7 Système de notation
- [ ] Documenter le système de rating double (`_jb_rating_raw`, `_jb_rating_rounded`)
- [ ] Règles de mapping par défaut (0-5 stars)
- [ ] Fonction de mapping personnalisable
- [ ] Labels personnalisés par niveau
- [ ] Interface admin "Rating System"
- [ ] Template tags pour affichage

#### 2.8 Gestion des images
- [ ] Documenter `includes/class-image-handler.php`
- [ ] Processus d'import (téléchargement, hash MD5, Media Library)
- [ ] Optimisations (resize 1200×1200, thumbnails, WebP)
- [ ] Gestion des erreurs (placeholder, retry, skip)
- [ ] Alt text et captions automatiques

#### 2.9 Cron jobs et scheduling
- [ ] Documenter `includes/class-action-scheduler.php` (si utilisé)
- [ ] Polling adaptatif (sixhourly/daily/weekly)
- [ ] WP-Cron events (`jb_rss_sync`, `jb_background_import_batch`)
- [ ] Checkpoints et reprise après interruption

#### 2.10 Page de réglages admin
- [ ] Documenter `includes/class-settings.php`
- [ ] Documenter `admin/class-admin.php`
- [ ] Onglets de réglages :
  - Synchronisation (RSS URL, fréquence, notifications)
  - Import historique (crawler manuel, batch size, délais)
  - Options générales (images, données, debug)
  - Rating system (mapping, labels)
  - Taxonomies (review/merge)
  - Advanced/Debug

- [ ] Créer un diagramme d'architecture global
- [ ] Documenter les interactions entre composants
- [ ] Flux de données (RSS → Parser → Scraper → Importer → CPT)

**Fichiers à créer :**
- `/docs/architecture/overview.md`
- `/docs/architecture/components.md`
- `/docs/architecture/data-flow.md`
- `/docs/architecture/rss-sync.md`
- `/docs/architecture/scraping.md`
- `/docs/architecture/import-process.md`
- `/docs/architecture/rating-system.md`
- `/docs/architecture/image-handling.md`
- `.cursor/rules/architecture.mdc`

### Question 3 : Schéma de base de données
**Objectif :** ERD et explications dans `/docs/db/`

- [ ] Analyser les Custom Post Types :
  - `jb_checkin` (check-ins Untappd)
  - Structure du post (title, content, date, status)
- [ ] Analyser les taxonomies :
  - `jb_beer_style` (hiérarchique, slug: `beer-style`)
  - `jb_brewery` (non-hiérarchique, slug: `brewery`)
  - `jb_venue` (non-hiérarchique, slug: `venue`)
- [ ] Analyser les meta fields (post meta) :
  - Identifiants : `_jb_checkin_id`, `_jb_beer_id`, `_jb_brewery_id`, `_jb_checkin_url`
  - Données bière : `_jb_beer_name`, `_jb_brewery_name`, `_jb_beer_style`, `_jb_beer_abv`, `_jb_beer_ibu`, `_jb_beer_description`
  - Données check-in : `_jb_rating`, `_jb_serving_type`, `_jb_checkin_date`
  - Données lieu : `_jb_venue_name`, `_jb_venue_city`, `_jb_venue_country`, `_jb_venue_lat`, `_jb_venue_lng`
  - Données sociales : `_jb_toast_count`, `_jb_comment_count`, `_jb_badges_earned`
  - Métadonnées techniques : `_jb_source`, `_jb_scraped_at`, `_jb_scraping_attempts`, `_jb_incomplete_reason`
  - Système de notation : `_jb_rating_raw`, `_jb_rating_rounded`
- [ ] Documenter les options WordPress utilisées :
  - `jb_last_checkin_date`
  - `jb_last_imported_guid`
  - `jb_rating_rules`
  - `jb_rating_labels`
  - `jb_new_terms_created`
  - `jb_import_checkpoint`
- [ ] Documenter les transients :
  - `jb_new_terms_notice`
  - `jb_global_stats`
  - `jb_top_breweries`
- [ ] Créer un diagramme ERD (Mermaid) avec :
  - Tables WordPress (posts, postmeta, terms, term_taxonomy, term_relationships)
  - Relations entre CPT et taxonomies
  - Meta fields et leurs types
- [ ] Documenter les relations entre entités
- [ ] Documenter les index database recommandés :
  - Index unique sur `_jb_checkin_id`
  - Index composé sur `post_type` + `post_date`

**Fichiers à créer :**
- `/docs/db/schema.md`
- `/docs/db/erd.md` (avec diagramme Mermaid)
- `/docs/db/meta-fields.md`
- `/docs/db/options.md`
- `/docs/db/indexes.md`

### Question 4 : Documentation OpenAPI (si API REST)
**Objectif :** Spécification OpenAPI-v3 si le plugin expose une API REST

- [ ] Vérifier si le plugin expose des endpoints REST
- [ ] Si oui, demander à Cursor de générer une spécification OpenAPI
- [ ] Documenter les endpoints, paramètres, réponses

**Fichiers à créer :**
- `/docs/api/openapi.yaml` (si applicable)
- `/docs/api/endpoints.md`

### Question 5 : Stack frontend
**Objectif :** Bibliothèque de composants, design tokens, règles de thème

- [ ] Analyser les templates du plugin :
  - `public/templates/archive-beer_checkin.php`
  - `public/templates/single-beer_checkin.php`
  - `public/templates/taxonomy-beer-style.php`
  - `public/templates/taxonomy-brewery.php`
  - `public/templates/taxonomy-venue.php`
- [ ] Documenter le système de templates WordPress :
  - Hiérarchie de templates (thème > plugin)
  - Templates surchargeables
  - Hooks de customisation (`jb_before_checkins_list`, `jb_after_checkin_card`, etc.)
  - Filtres (`jb_checkin_template`, `jb_checkin_classes`, `jb_checkin_data`)
- [ ] Documenter les partials réutilisables :
  - `public/partials/checkin-card.php`
  - `public/partials/rating-stars.php`
- [ ] Documenter les template tags disponibles :
  - `jb_get_checkin_data($post_id)`
  - `jb_rating_stars($rating, $echo = true)`
  - `jb_beer_style($post_id, $link = true)`
  - `jb_brewery_link($post_id)`
  - `jb_venue_info($post_id)`
  - `jb_beer_image($post_id, $size = 'medium')`
  - `jb_display_rating($post_id, $show_label, $show_raw)`
- [ ] Documenter les shortcodes (si Phase 2)
- [ ] Analyser les assets (CSS/JS) :
  - `public/assets/css/public.css`
  - `public/assets/js/public.js`
  - `admin/assets/css/admin.css`
  - `admin/assets/js/admin.js`
  - `admin/assets/js/import-progress.js`
- [ ] Documenter les classes CSS et le système de styling :
  - Design philosophy (Database + Grid Hybrid)
  - Grid cards (visual mode)
  - Table view (database mode)
  - Responsive breakpoints
  - Classes utilitaires
- [ ] Documenter les Gutenberg blocks (Phase 2) :
  - `jardin-toasts/checkins-list` (paramètres, layouts, filtres)
  - `jardin-toasts/checkin-card` (éléments affichés)
  - `jardin-toasts/stats-dashboard` (statistiques, graphiques)

**Fichiers à créer :**
- `/docs/frontend/templates.md`
- `/docs/frontend/template-hierarchy.md`
- `/docs/frontend/template-tags.md`
- `/docs/frontend/hooks-filters.md`
- `/docs/frontend/shortcodes.md` (si applicable)
- `/docs/frontend/styling.md`
- `/docs/frontend/assets.md`
- `/docs/frontend/gutenberg-blocks.md` (Phase 2)

### Question 6 : Flux utilisateur
**Objectif :** Diagrammer les parcours de l'installation aux parcours avancés

- [ ] Flux d'installation et configuration :
  - Installation du plugin
  - Activation
  - Configuration initiale (URL RSS Untappd)
  - Premiers réglages (images, données, notifications)
- [ ] Flux de synchronisation initiale :
  - Déclenchement manuel ou automatique
  - Fetch RSS feed
  - Parsing et détection nouveaux check-ins
  - Scraping des pages Untappd
  - Import dans CPT
  - Gestion des erreurs
- [ ] Flux de synchronisation automatique :
  - Polling adaptatif (déclenchement selon activité)
  - Comparaison GUID
  - Import si nouveaux check-ins
  - Notifications (email, dashboard)
- [ ] Flux d'import historique :
  - Accès page import
  - Configuration (batch size, délais, portée)
  - Démarrage import (manuel ou background)
  - Progression en temps réel
  - Pause/reprise
  - Finalisation et notification
- [ ] Flux d'affichage des check-ins :
  - Archive (grid/table view)
  - Single check-in
  - Navigation (prev/next)
  - Filtres et recherche
- [ ] Flux de navigation (archives, single, taxonomies) :
  - Archive check-ins
  - Single check-in
  - Taxonomie beer-style
  - Taxonomie brewery
  - Taxonomie venue
- [ ] Flux de gestion des erreurs :
  - Échec scraping (retry automatique)
  - Check-ins en draft (notification admin)
  - Retry manuel
  - Logs et debugging
- [ ] Flux de configuration du système de notation :
  - Accès settings "Rating System"
  - Configuration mapping rules
  - Personnalisation labels
  - Sauvegarde et application

**Fichiers à créer :**
- `/docs/user-flows/installation.md`
- `/docs/user-flows/sync.md`
- `/docs/user-flows/historical-import.md`
- `/docs/user-flows/display.md`
- `/docs/user-flows/navigation.md`
- `/docs/user-flows/error-handling.md`
- `/docs/user-flows/rating-configuration.md`

**Format :** Markdown + diagrammes Mermaid pour chaque flux

### Question 7 : Fonctionnalités principales
**Objectif :** Listes de fonctionnalités avec références aux modules critiques

- [ ] Synchronisation RSS automatique :
  - Polling adaptatif intelligent
  - Optimisation ressources (comparaison GUID)
  - Gestion des erreurs et retry
  - Module : `includes/class-rss-parser.php`
- [ ] Scraping des pages Untappd :
  - Extraction métadonnées complètes
  - Sélecteurs HTML
  - Rate limiting
  - Module : `includes/class-scraper.php`
- [ ] Import historique (crawler) :
  - Mode manuel avec progression
  - Mode background (WP-Cron)
  - Checkpoints et reprise
  - Module : `includes/class-crawler.php`
- [ ] Import des images dans Media Library :
  - Téléchargement et hash MD5
  - Resize automatique
  - Génération thumbnails
  - Module : `includes/class-image-handler.php`
- [ ] Gestion des Custom Post Types :
  - CPT `jb_checkin`
  - Configuration REST API
  - Module : `includes/class-post-type.php`
- [ ] Système de taxonomies :
  - Auto-création immédiate
  - Notifications admin
  - Module : `includes/class-taxonomies.php`
- [ ] Templates surchargeables :
  - Hiérarchie WordPress
  - Hooks et filtres
  - Module : `public/templates/`
- [ ] Polling adaptatif intelligent :
  - Détection activité utilisateur
  - Ajustement fréquence (6h/jour/semaine)
  - Module : `includes/class-rss-sync.php`
- [ ] Gestion des erreurs et retry logic :
  - 3 tentatives automatiques
  - Retry manuel admin
  - Notifications
  - Module : `includes/class-importer.php`
- [ ] Système de notation :
  - Rating raw + rounded
  - Mapping personnalisable
  - Labels personnalisés
  - Module : Settings + template tags
- [ ] Logging et debugging :
  - Logs détaillés
  - Interface admin logs
  - Module : `admin/views/logs-page.php`
- [ ] Page de réglages admin complète :
  - 5 onglets de configuration
  - Interface utilisateur
  - Module : `admin/class-admin.php` + `includes/class-settings.php`
- [ ] Déduplication :
  - Par ID Untappd
  - Module : `includes/class-deduplicator.php` (si séparé)

**Fichiers à créer :**
- `/docs/features/checklist.md`
- `/docs/features/core-modules.md`
- `/docs/features/mvp-features.md` (Phase 1)
- `/docs/features/advanced-features.md` (Phase 2)
- `/docs/features/future-features.md` (Phase 3+)

---

## Phase 4 : Documentation technique WordPress

### Étape 4.1 : Documentation WordPress standard
- [ ] Créer `readme.txt` pour WordPress.org (format standard)
- [ ] Inclure :
  - Headers requis (Contributors, Tags, Requires at least, etc.)
  - Description complète
  - Installation
  - FAQ
  - Screenshots
  - Changelog
  - Upgrade Notice
- [ ] Créer `CHANGELOG.md`
- [ ] Documenter les hooks WordPress utilisés :
  - Actions : `plugins_loaded`, `init`, `wp_enqueue_scripts`, `admin_notices`, etc.
  - Filtres : `jb_checkin_template`, `jb_checkin_classes`, `jb_checkin_data`, `jb_rating_display`
- [ ] Documenter la compatibilité :
  - Versions WordPress (6.0+ minimum, testé jusqu'à 6.7+)
  - Versions PHP (8.2+ minimum)
  - Versions MySQL/MariaDB
  - Extensions PHP requises
- [ ] Documenter les dépendances :
  - Composer : Guzzle, Symfony DomCrawler, Symfony CSS Selector
  - npm : @wordpress/scripts (pour blocks Gutenberg)
  - WordPress : SimplePie (pour RSS)

**Fichiers à créer :**
- `/readme.txt` (format WordPress.org)
- `/CHANGELOG.md`
- `/docs/wordpress/hooks.md`
- `/docs/wordpress/filters.md`
- `/docs/wordpress/compatibility.md`
- `/docs/wordpress/dependencies.md`

### Étape 4.2 : Documentation de développement
- [ ] Guide de contribution :
  - Structure du projet
  - Standards de code
  - Processus de PR
  - Tests requis
- [ ] Standards de code :
  - WordPress Coding Standards (WPCS)
  - PHPStan Level 5 minimum
  - Préfixes (fonctions, classes, options)
  - Internationalisation
- [ ] Processus de test :
  - Tests unitaires (PHPUnit)
  - Tests d'intégration
  - Tests de performance
  - CI/CD (optionnel)
- [ ] Guide de déploiement :
  - Build process (npm, composer)
  - Préparation WordPress.org
  - Assets requis (banners, icons, screenshots)
  - SVN workflow

**Fichiers à créer :**
- `/docs/development/contributing.md`
- `/docs/development/coding-standards.md`
- `/docs/development/testing.md`
- `/docs/development/deployment.md`
- `/docs/development/build-process.md`

### Étape 4.3 : Documentation WordPress.org
- [ ] Checklist de soumission WordPress.org :
  - Fichiers requis (jardin-toasts.php, readme.txt, LICENSE)
  - Code requirements (GPL, sanitization, nonces, etc.)
  - Assets (banners, icons, screenshots)
  - Security best practices
- [ ] Documentation des assets :
  - Banner 1544×500px
  - Banner 772×250px
  - Icon 256×256px
  - Screenshots (1280×960px recommandé)
- [ ] Documentation i18n :
  - Text domain : `jardin-toasts`
  - Génération .pot file
  - Structure `/languages/`
  - Chargement text domain

**Fichiers à créer :**
- `/docs/wordpress/submission-checklist.md`
- `/docs/wordpress/assets.md`
- `/docs/wordpress/i18n.md`

---

## Phase 5 : Documentation spécifique aux fonctionnalités

### Étape 5.1 : Documentation du système de synchronisation
- [ ] Processus RSS détaillé :
  - Fetch feed
  - Parse XML
  - Extraction données
  - Comparaison GUID
  - Décision scraping
- [ ] Processus scraping détaillé :
  - Requête HTTP
  - Parsing HTML (sélecteurs)
  - Extraction métadonnées
  - Gestion erreurs
- [ ] Polling adaptatif :
  - Détection activité
  - Calcul fréquence
  - Mise à jour schedule
- [ ] Gestion des erreurs :
  - Types d'erreurs
  - Retry logic
  - Notifications
  - Logs

**Fichiers à créer :**
- `/docs/features/rss-sync-detailed.md`
- `/docs/features/scraping-detailed.md`
- `/docs/features/polling-adaptive.md`
- `/docs/features/error-handling-detailed.md`

### Étape 5.2 : Documentation du système de notation
- [ ] Architecture du système :
  - Stockage double (raw + rounded)
  - Règles de mapping
  - Fonction de mapping
- [ ] Configuration :
  - Interface admin
  - Personnalisation labels
  - Affichage frontend
- [ ] Template tags :
  - `jb_display_rating()`
  - Filtres disponibles

**Fichiers à créer :**
- `/docs/features/rating-system-detailed.md`
- `/docs/features/rating-configuration.md`

### Étape 5.3 : Documentation de l'import historique
- [ ] Mode manuel :
  - Interface admin
  - Progression AJAX
  - Pause/reprise
- [ ] Mode background :
  - WP-Cron integration
  - Checkpoints
  - Notifications
- [ ] Optimisations :
  - Batch size
  - Délais entre requêtes
  - Rate limiting

**Fichiers à créer :**
- `/docs/features/historical-import-detailed.md`

### Étape 5.4 : Documentation des templates frontend
- [ ] Design philosophy :
  - Database + Grid Hybrid
  - Responsive design
  - Thème-agnostique
- [ ] Templates disponibles :
  - Archive (grid/table)
  - Single
  - Taxonomies
- [ ] Customisation :
  - Hiérarchie templates
  - Hooks et filtres
  - Template tags

**Fichiers à créer :**
- `/docs/frontend/templates-detailed.md`
- `/docs/frontend/customization.md`

---

## Phase 6 : Révision et affinage

### Étape 6.1 : Révision initiale
- [ ] Lire toutes les documentations générées
- [ ] Identifier les incohérences
- [ ] Noter les questions à poser à Cursor
- [ ] Identifier les sections manquantes
- [ ] Vérifier les références croisées
- [ ] Vérifier la cohérence avec les spécifications techniques

### Étape 6.2 : Itération avec Cursor
- [ ] Demander des clarifications sur les points flous
- [ ] Demander des diagrammes supplémentaires si nécessaire
- [ ] Demander des exemples de code dans la documentation
- [ ] Affiner les descriptions techniques
- [ ] Compléter les sections manquantes
- [ ] Ajouter des diagrammes Mermaid pour les flux complexes

### Étape 6.3 : Validation
- [ ] Vérifier que tous les fichiers sont créés
- [ ] Vérifier la cohérence entre les documents
- [ ] Vérifier les liens croisés
- [ ] Vérifier la conformité aux standards WordPress
- [ ] Vérifier la conformité avec les spécifications techniques
- [ ] Tester les diagrammes Mermaid
- [ ] Vérifier l'orthographe et la grammaire (français)

---

## Phase 7 : Maintien à long terme

### Étape 7.1 : Configurer la synchronisation automatique
- [ ] S'assurer que les règles Cursor incluent "keep docs in sync"
- [ ] Tester que les modifications de code déclenchent des suggestions de mise à jour de la doc
- [ ] Configurer des rappels pour mettre à jour la doc lors des changements majeurs

### Étape 7.2 : Processus de maintenance
- [ ] Documenter le processus de mise à jour de la documentation
- [ ] Créer un checklist pour les PRs (vérifier la doc)
- [ ] Intégrer la vérification de la doc dans le workflow
- [ ] Planifier des révisions périodiques (trimestrielles)

---

## Checklist finale

### Structure de fichiers créée
- [ ] `/docs/` avec tous les sous-dossiers
- [ ] `.cursor/rules/` avec les règles d'architecture
- [ ] `/README.md` complet
- [ ] `/readme.txt` pour WordPress.org
- [ ] `/CHANGELOG.md`
- [ ] `/LICENSE` (GPL v2+)

### Documentation générée
- [ ] README principal
- [ ] Architecture complète (tous les composants)
- [ ] Schéma de base de données avec ERD
- [ ] Documentation frontend (templates, hooks, tags)
- [ ] Flux utilisateur avec diagrammes Mermaid
- [ ] Liste des fonctionnalités (MVP, avancées, futures)
- [ ] Documentation WordPress standard (hooks, compatibilité)
- [ ] Documentation de développement (contribution, tests)
- [ ] Documentation WordPress.org (soumission, assets, i18n)

### Validation
- [ ] Tous les liens fonctionnent
- [ ] Diagrammes Mermaid valides
- [ ] Cohérence entre les documents
- [ ] Conformité aux standards WordPress
- [ ] Conformité avec les spécifications techniques (`.todo/untappd-sync-specs.md`)
- [ ] Documentation prête pour l'onboarding
- [ ] Documentation prête pour la soumission WordPress.org

---

## Notes importantes

### Contexte spécifique au projet Jardin Toasts
- Plugin WordPress pour synchroniser les check-ins Untappd
- Pas d'API officielle → scraping nécessaire
- RSS feed limité (25 derniers check-ins)
- Import d'images dans Media Library
- Templates surchargeables
- Publication prévue sur WordPress.org
- ~200 check-ins historiques à importer
- Système de notation avec labels personnalisables
- Polling adaptatif selon activité utilisateur

### Adaptations de la stratégie originale
- Ajout de la documentation WordPress.org (`readme.txt`, assets, i18n)
- Focus sur les Custom Post Types et taxonomies WordPress
- Documentation des hooks et filtres WordPress
- Documentation du système de scraping (contrainte majeure)
- Documentation du système de notation (fonctionnalité clé)
- Intégration des spécifications existantes (`.todo/untappd-sync-specs.md`)
- Documentation des phases de développement (MVP, avancées, futures)

### Ordre de développement recommandé (pour référence)
1. Structure de base (CPT, taxonomies, métadonnées, settings)
2. RSS Sync (priorité)
3. Crawler historique
4. Templates frontend
5. Polish & optimisation (caching, logs, blocks, i18n)

### Prochaines étapes après l'audit
1. Travailler sur **`dev`** (branche + commits dans le dépôt, dossier `docs/` inclus)
2. Ouvrir une PR **`dev` → `main`** pour review si besoin
3. Fusionner dans **`main`** une fois validé
4. Poursuivre le développement avec la documentation comme référence
5. Mettre à jour la documentation au fur et à mesure du développement

---

## Références

- [Article original de Matt Bernier](https://www.mbernier.com/articles/2025.06.07-documenting-a-new-git-repo)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress.org Plugin Submission](https://developer.wordpress.org/plugins/wordpress-org/)
- Spécifications techniques : `.todo/untappd-sync-specs.md`
- [Symfony DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html)
- [Guzzle HTTP Client](https://docs.guzzlephp.org/)

