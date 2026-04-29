# Post-MVP — backlog consolidé

**Source de vérité** pour les idées retenues après le MVP (Phase 1).  
Ne pas traiter comme livrable v1 ; prioriser et découper en releases au fil de l’eau.

**Références connexes** : méta badges / recalcul notes décrites ailleurs — `docs/db/meta-fields.md`, `docs/user-flows/rating-configuration.md`.  
**Pistes doc / fichiers manquants** : synthétiser ce qui reste utile dans `docs/codebase-audit-plan.md` (sections blocks, shortcodes, fichiers à ajouter) sans dupliquer tout l’audit.

---

## 1. Statistiques avancées & widget

**Objectif** : aller au-delà du bandeau « At a glance » (compteurs + dernier sync).

- [ ] Définir le périmètre métier (par style, par mois, tendance check-ins, ABV moyen, etc.).
- [ ] Choisir la stack graphiques (**Chart.js** ou dépendance embarquée légère compatible admin WP).
- [ ] Écran dédié ou onglet admin « Statistics » avec graphiques interactifs (filtres date, taxonomie).
- [ ] **Widget tableau de bord** WordPress (résumé + lien vers l’écran complet).
- [ ] Performance : requêtes agrégées, transients, invalidation cohérente avec `jb_invalidate_stats_cache` / imports.
- [ ] Accessibilité : données tabulaires en secours pour les graphiques.

---

## 2. Export & import (CSV, JSON, import générique)

**Objectif** : sauvegarder et réinjecter des données sans passer uniquement par Untappd.

- [ ] **Export CSV** : colonnes check-in (meta `_jb_*`, taxonomies, dates) ; sélection plage de posts.
- [ ] **Export JSON** : schéma versionné (`schema_version`) pour évolutions futures.
- [ ] **Import générique** : mapping colonnes JSON/CSV ↔ champs plugin ; validation, mode dry-run, rapport d’erreurs.
- [ ] Corrélation avec `_jb_checkin_id` / dédup ; option mise à jour vs création.
- [ ] Capabilities dédiées (`export_jardin_toasts`, `import_jardin_toasts`) ou réutilisation `manage_options` + filtre.
- [ ] Documentation utilisateur (formats, exemples).

---

## 3. Intégrations externes

**Objectif** : autres sources que Untappd + pipeline « universel ».

- [ ] **BeerAdvocate** : faisabilité (API / HTML / légal), périmètre MVP d’intégration.
- [ ] **RateBeer** : idem.
- [ ] **Import universel** : normalisateur commun (même pipeline que CSV JSON → `JB_Importer`) ; plugins / filtres `jb_import_row`.
- [ ] Journalisation, rate limits, et positionnement légal (ToS par source).

---

## 4. UI admin — fusion / revue des taxonomies

**Objectif** : gérer les doublons et fusionner `beer_style`, `brewery`, `venue` sans SQL.

- [ ] Liste des termes avec compteur de posts ; recherche.
- [ ] Action « fusionner vers… » (term source → term cible) avec réassignation des posts.
- [ ] Prévisualisation / confirmation ; journal ou undo limité si faisable.
- [ ] Optionnel : détection de suggestions (slug similaire, Levenshtein) — phase ultérieure.

---

## 5. Filtres front par note (AJAX / facettes)

**Objectif** : filtrer les archives listes (grid/table) sans recharger toute la page si possible.

- [ ] UI filtres (étoiles min/max, ou buckets alignés sur `jb_rating_rules`).
- [ ] Endpoint AJAX ou requête `WP_Query` + fragment HTML ou JSON.
- [ ] Compatibilité SEO : URL query args (`?min_rating=3`) vs pur AJAX ; pagination.
- [ ] Cache / perfs sur grandes listes.

---

## 6. Recalcul des notes sur les posts existants

**Objectif** : après changement des règles `jb_rating_rules`, mettre à jour `_jb_rating_rounded` (et affichage) sans ré-importer depuis Untappd.

- [ ] Commande WP-CLI ou outil admin « Recalculer » avec batch + progression.
- [ ] Utiliser `_jb_rating_raw` + règles courantes ; conserver traçabilité (log ou option « dernière regénération »).
- [ ] Gérer les posts sans note brute (brouillons, incomplets).

---

## 7. Badges Untappd — stockage & affichage

**Objectif** : exploiter les badges déjà envisagés en méta sérialisée pour un affichage futur.

- [ ] Confirmer / compléter le schéma méta (`_jb_badges_earned` ou équivalent dans `docs/db/meta-fields.md`).
- [ ] Remplir le champ à l’import / scrape si Untappd expose les badges sur la page check-in.
- [ ] Rendu front (liste, icônes CDN Untappd si autorisé) + option on/off dans les réglages.
- [ ] Accessibilité et perf (lazy load).

---

## 8. Documentation Phase 2 (produit & dev)

**Objectif** : aligner la doc sur le backlog ci-dessus sans tout refaire `codebase-audit-plan.md`.

- [ ] **Shortcodes** : documenter les signatures prévues (`[jardin_toasts_*]`), attributs, exemples.
- [ ] **Gutenberg** : liste des blocs cibles, mapping design/code, guide d’extension.
- [ ] **Fichiers / pages manquants** : extraire de `docs/codebase-audit-plan.md` les entrées encore pertinentes (chemins `docs/frontend/gutenberg-blocks.md`, etc.) ; créer les stubs ou marquer « obsolète ».
- [ ] Liens depuis README / roadmap vers ce fichier pour éviter la dérive.

---

## Mise à jour de ce document

- Ajouter les sous-tâches et critères d’acceptation au fil des sprints.
- Les idées **non** retenues (carte, wishlist, PWA, REST, etc.) ne sont **plus** listées ici volontairement ; l’historique éventuel reste dans le git log / anciennes versions du CHANGELOG.
