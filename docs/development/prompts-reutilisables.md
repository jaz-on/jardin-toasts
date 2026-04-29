# Prompts réutilisables pour analyse de documentation

Ce document contient des prompts standardisés pour analyser la documentation et générer des plans de développement détaillés.

## Prompt 1 : Analyse complète et plan MVP

```
Analyse la documentation complète du plugin Jardin Toasts et crée un plan de développement détaillé pour la Phase 1 (MVP).

Contexte :
- Documentation complète dans /docs/
- Architecture définie dans docs/architecture/
- Ordre de développement dans DEVELOPMENT.md
- Checklist des features dans docs/features/checklist.md

Tâches :
1. Analyser la documentation technique (architecture, db, features)
2. Identifier les dépendances entre composants
3. Créer un plan d'implémentation séquentiel avec :
   - Ordre d'implémentation des classes
   - Dépendances entre modules
   - Points d'intégration WordPress
   - Tests à prévoir à chaque étape
4. Estimer la complexité de chaque module
5. Identifier les risques et points d'attention

Format attendu :
- Plan par module avec sous-tâches
- Checklist de développement avec dépendances
- Ordre d'exécution recommandé
- Points de validation à chaque étape

Références clés :
- docs/architecture/components.md (composants)
- docs/architecture/data-flow.md (flux de données)
- docs/db/schema.md (structure DB)
- DEVELOPMENT.md (ordre de développement)
- docs/features/checklist.md (features MVP)
```

## Prompt 2 : Analyse d'un module spécifique

```
Analyse la documentation de Jardin Toasts et crée un plan de développement pour implémenter le module [MODULE_NAME] (Phase 1, priorité [X] selon DEVELOPMENT.md).

Analyse :
1. Lire docs/architecture/[module].md
2. Lire docs/architecture/components.md (section [CLASS_NAME])
3. Lire docs/features/[module]-detailed.md
4. Lire docs/user-flows/[flow].md
5. Identifier les dépendances (WordPress cron, SimplePie, etc.)

Créer un plan avec :
- Structure de la classe [CLASS_NAME]
- Méthodes à implémenter
- Hooks WordPress à utiliser
- Tests unitaires à prévoir
- Intégration avec [DEPENDENCIES]
- Gestion d'erreurs
- Logging

Format : Checklist détaillée avec code examples basés sur la documentation.
```

### Exemple d'utilisation : Module RSS Sync

```
Analyse la documentation de Jardin Toasts et crée un plan de développement pour implémenter le module RSS Sync (Phase 1, priorité 2 selon DEVELOPMENT.md).

Analyse :
1. Lire docs/architecture/rss-sync.md
2. Lire docs/architecture/components.md (section JB_RSS_Parser)
3. Lire docs/features/rss-sync-detailed.md
4. Lire docs/user-flows/sync.md
5. Identifier les dépendances (WordPress cron, SimplePie, etc.)

Créer un plan avec :
- Structure de la classe JB_RSS_Parser
- Méthodes à implémenter
- Hooks WordPress à utiliser
- Tests unitaires à prévoir
- Intégration avec JB_Importer
- Gestion d'erreurs
- Logging

Format : Checklist détaillée avec code examples basés sur la documentation.
```

## Prompt 3 : Validation de cohérence

```
Valide la cohérence entre la documentation et le code existant :

1. Compare docs/architecture/components.md avec les classes PHP existantes
2. Vérifie que les noms de classes/fonctions correspondent
3. Vérifie que les hooks documentés existent dans le code
4. Identifie les écarts entre doc et code
5. Propose un plan de correction

Format : Rapport avec liste des écarts et plan de correction.
```

## Prompt 4 : Génération de plan de développement pour une feature

```
Génère un plan de développement détaillé pour la feature [FEATURE_NAME] en utilisant le template standardisé.

Références :
- docs/architecture/[relevant-files].md
- docs/features/[feature]-detailed.md
- docs/user-flows/[relevant-flow].md
- docs/db/[relevant-schema].md

Format de sortie : Utiliser le template dans docs/development/template-plan-developpement.md
```

## Utilisation

Ces prompts peuvent être utilisés avec un assistant IA pour :

1. **Planification initiale** : Utiliser Prompt 1 pour créer le plan complet MVP
2. **Développement itératif** : Utiliser Prompt 2 pour chaque module à implémenter
3. **Validation continue** : Utiliser Prompt 3 régulièrement pour maintenir la cohérence
4. **Nouvelles features** : Utiliser Prompt 4 pour documenter de nouvelles fonctionnalités

## Notes

- Toujours référencer les fichiers de documentation existants
- Suivre l'ordre de développement défini dans DEVELOPMENT.md
- Respecter les conventions de nommage documentées
- Inclure les aspects sécurité, performance et tests dans chaque plan

