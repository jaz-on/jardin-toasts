### intégration untappd — analyse, comparaison et plan documentaire

Ce document synthétise l’implémentation Untappd issue du projet Eleventy archivé, la compare à Jardin Toasts (WordPress) et définit un plan documentaire avant toute génération de code.

#### objectifs
- Centraliser l’analyse fonctionnelle et technique utile à Jardin Toasts.
- Définir clairement le modèle de données cible (contrat interne) et les pipelines d’ingestion.
- Planifier les mises à jour de la documentation sans modifier le code.

#### résumé de l’implémentation eleventy (source d’inspiration)
- Deux pipelines d’import complémentaires :
  - RSS incrémental (léger, quotidien) avec cache d’URLs traitées et déduplication par `checkinId`.
  - Import historique HTML → CSV → ingestion, avec parsing précis : ABV, IBU, rating (classes CSS), dates (normalisées en `YYYY-MM-DD`), URLs absolues.
- Idempotence et robustesse : cache RSS, liste d’exclusion, détection de doublons, validations et logs explicites.
- Génération de contenu à partir d’un template riche (équivalent, côté WP : valeurs par défaut et normalisation au moment de l’insertion).
- Tests unitaires sur le mapping, la coersion de types, la détection de doublons, etc.

#### comparaison avec beer journal (wordpress)
- Persistance : Jardin Toasts crée un CPT (ex. `beer_checkin`) + taxonomies + métas `_jb_*` (base de données), vs fichiers `.md` en Eleventy.
- Standards : Jardin Toasts doit respecter WPCS, i18n (`jardin-toasts`), nonces/capabilities, sanitization/escaping, WP_Query, transients, pagination.
- Orchestration : tâches WP‑Cron/CLI et pages admin natives (WordPress) vs orchestrateur JS.

#### confirmations structurelles (cohérence avec la doc)
- CPT : `beer_checkin` (confirmé par `docs/db/schema.md`).
- Taxonomies :
  - `beer_style` (hiérarchique) — confirmé.
  - `brewery` (non hiérarchique) — confirmé.
  - `venue` (non hiérarchique, optionnel) — disponible.
- Remarque : conserver ces noms (« beer_style », « brewery », « venue ») pour éviter les conflits. Ne pas préfixer les taxonomies en `jb_`.

#### contrat de données interne proposé (Jardin Toasts)
Variables (inspirées de `BeerData`), avant mapping vers WP :
- Obligatoires : `checkinId` (string), `title` (string), `brewery` (string), `checkinDate` (`YYYY-MM-DD`), `untappdUrl` (string).
- Optionnelles : `style` (string), `rating` (float 0–5), `abv` (float), `ibu` (int), `beerUrl` (string), `breweryUrl` (string), `labelUrl` (string | local), `servingType` (string), `price` (string), `location` (string), `batch` (string), `brewDate` (`YYYY-MM-DD`), `bestBefore` (`YYYY-MM-DD`), `temperature` (string), `glassware` (string), `personalNotes` (markdown), `pairingSuggestions` (markdown), `availability` (markdown).

Notes :
- Normaliser en amont : dates, nombres, URLs absolues.
- Garantir l’unicité par `checkinId` (clé primaire logique) au moment de l’insertion (méta `_jb_checkin_id`). 

#### pipelines d’ingestion (sans code, pour documentation)
- RSS (quotidien) :
  - Entrée : Flux Untappd `rss/user/{username}?key={apiKey}`.
  - Étapes : cache des URLs traitées (transient + option de persistance), parsing du titre (« is drinking … by … »), formatage date, construction `BeerData` minimal, déduplication, mapping vers CPT/métas/taxos.
  - Sortie : nouveaux posts `beer_checkin` en statut brouillon ou publié selon règle.
- HTML/CSV (historique) :
  - Entrée : export HTML utilisateur (`[username]-beerlist.html`).
  - Étapes : parsing DOM (ABV/IBU/style/rating/URLs/date/ID), génération CSV, ingestion CSV (validations), construction `BeerData` enrichi, déduplication, mapping.
  - Sortie : posts `beer_checkin` créés par lots (batch + pagination).

#### anti‑doublons et exclusions
- Anti‑doublons : recherche d’un post existant via méta `_jb_checkin_id == checkinId` avant insertion.
- Exclusions : liste de `checkinId` à ignorer (option WordPress `jb_excluded_checkins` + UI admin).
- Cache RSS : transient `jb_untappd_rss_cache` (TTL court) + option de sauvegarde durable en cas d’arrêt.

#### mapping vers WordPress (cible)
- CPT : `beer_checkin` (ou équivalent existant dans le projet).
- Méta `_jb_*` :
  - Identifiants/URLs : `_jb_checkin_id` (unique), `_jb_checkin_url`, `_jb_beer_id` (opt.), `_jb_brewery_id` (opt.).
  - Notations : `_jb_rating_raw` (conserver la note Untappd brute), `_jb_rating_rounded` (conversion interne pour affichage/filtrage).
  - Caractéristiques : `_jb_beer_abv`, `_jb_beer_ibu`, `_jb_beer_style` (redondant avec taxo pour recherche).
  - Contexte : `_jb_serving_type`, `_jb_price`, `_jb_location`, `_jb_batch`, `_jb_brew_date`, `_jb_best_before`, `_jb_temperature`, `_jb_glassware`.
  - Texte : `_jb_notes_md`, `_jb_pairing_md`, `_jb_availability_md`.
- Taxonomies : `beer_style` (hiérarchique), `brewery` (non hiérarchique), `venue` (optionnel).
- Images (étiquettes) : par défaut, téléchargement dans la médiathèque (featured image) avec métas sur la pièce jointe :
  - `_jb_image_hash` (MD5 pour déduplication) et `_jb_image_source_url` (URL source Untappd).
  - Alternative configurable : ne pas télécharger et stocker uniquement l’URL distante (fallback).

#### plan documentaire — modifications proposées (sans éditer maintenant)
Pour éviter les conflits, les mises à jour ci‑dessous sont proposées. Elles pourront être intégrées dans une passe dédiée :
1) `docs/architecture/import-process.md`
   - Ajouter la vue d’ensemble des deux pipelines (RSS quotidien vs HTML/CSV historique) et leurs points de contrôle (cache, exclusions, anti‑doublons).
2) `docs/features/rss-sync-detailed.md`
   - Détailler : format du flux, parsing du titre, normalisation des dates, transient + option de cache, stratégie d’idempotence, journalisation.
3) `docs/features/historical-import-detailed.md`
   - Détailler : structure attendue de l’export HTML Untappd, extraction DOM (ABV/IBU/rating/URLs), génération CSV, validations d’ingestion en lots, reprise sur erreur.
4) `docs/architecture/data-flow.md`
   - Schéma de bout en bout depuis Untappd → normalisation → `BeerData` → mapping CPT/méta/taxos → templates front.
5) `docs/features/core-modules.md` et `docs/architecture/components.md`
   - Lister les classes cibles WordPress : `JB_Untappd_Sync`, `JB_Untappd_RSS_Importer`, `JB_Untappd_HTML_Parser`, `JB_Untappd_CSV_Importer`, `JB_Beer_Processor`, `JB_Image_Handler`.
6) `docs/wordpress/hooks.md` et `docs/wordpress/filters.md`
   - Déclarer les hooks d’orchestration (WP‑Cron/CLI) et les filtres de mapping/validation.
7) `docs/development/testing.md`
   - Tests PHPUnit suggérés : validations de mapping, coercions de types, normalisation des dates, anti‑doublons par méta, métriques d’import.
8) `docs/features/checklist.md`
   - Ajouter une checklist d’intégration Untappd (clés, caches, exclusions, taxos, médias, i18n, sécurité, performances).

#### normes et sécurité à respecter (rappel Jardin Toasts)
- WPCS, PHPStan min niveau 5, préfixes `jb_`/`JB_`/`_jb_`, i18n (`jardin-toasts`).
- Sanitization : `sanitize_text_field`, `absint`, `floatval`, `sanitize_email`, `esc_url_raw` (stockage).
- Escaping : `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` (sortie).
- Nonces/capabilities : tous formulaires/actions AJAX, `current_user_can()` pour opérations sensibles.
- Performance : transients, pagination, batchs d’import, WP_Query optimisées.

#### décisions validées (intégrées à la doc)
1) CPT/taxos : `beer_checkin`, taxonomies `beer_style`, `brewery`, `venue` (optionnel) — cohérent avec la documentation actuelle.
2) Rating : conserver `_jb_rating_raw` et calculer `_jb_rating_rounded` pour l’usage interne Jardin Toasts.
3) Import historique : parsing HTML autonome côté plugin (préférence : Symfony DomCrawler + CSS Selector, conforme à nos dépendances PHP modernes), CSV optionnel si nécessaire.
4) Étiquettes (images) : par défaut, téléchargement dans la médiathèque + stockage de l’URL source et hash de déduplication ; option pour ne stocker qu’une URL distante si souhaité.
5) Publication : statut `draft` par défaut pour tous les imports ; la publication dépendra de la complétude des données et/ou d’une action manuelle.

— Fin du document —

