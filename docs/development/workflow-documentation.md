# Workflow de documentation

Ce document décrit le workflow de documentation pour le plugin Jardin Toasts.

> **Mise à jour 2026-04 :** il n’y a plus de branche Git nommée `docs`. La doc est dans le dossier `docs/` sur **`dev`** puis **`main`**. Voir aussi [DEVELOP_BRANCH.md](DEVELOP_BRANCH.md) et [DEVELOPMENT.md](../../DEVELOPMENT.md).

## Structure de branches

```
main (référence)
  ↑
  └── dev (intégration)
      ├── feature/* (code + doc)
      └── docs/     (même dépôt, pas une branche Git)
```

## Règles de documentation

### Petites corrections

Pour les petites corrections (typos, liens cassés, clarifications) :

1. Travailler sur la branche **`dev`** (ou une branche `feature/*` depuis `dev`)
2. Valider avec `scripts/validate-docs.sh`
3. Commiter avec préfixe `docs:`
4. Merger `dev` dans `main` quand c’est prêt (PR recommandée)

```bash
git checkout dev
git pull origin dev
# Faire les corrections
./scripts/validate-docs.sh
git commit -m "docs: Fix broken link in architecture overview"
git push origin dev
# puis PR ou merge dev → main
```

### Documentation de feature

Pour documenter une nouvelle feature :

1. Créer une branche `feature/nom` depuis **`dev`**
2. Documenter dans `docs/` et le code associé
3. Valider avec `scripts/validate-docs.sh`
4. Merger la branche dans **`dev`**, puis **`dev`** dans **`main`** après validation

```bash
git checkout dev
git checkout -b feature/rss-sync-docs
# Documenter la feature
./scripts/validate-docs.sh
git commit -m "docs: Document RSS sync feature"
git checkout dev
git merge feature/rss-sync-docs --no-ff
git push origin dev
```

### Mises à jour majeures

Pour les mises à jour majeures (refonte, nouvelle structure) :

1. Créer une branche `feature/docs-refonte` (ou similaire) depuis **`dev`**
2. Effectuer les mises à jour sous `docs/`
3. Valider avec `scripts/validate-docs.sh`
4. Merger dans **`dev`**, puis **`main`** après validation complète

## Cycle de vie

### Développement code et documentation

1. **Code et doc** sur la même branche `feature/*` depuis **`dev`** (recommandé), ou doc seule sur `feature/docs-*`
2. **Merge** → **`dev`** après validation
3. **Merge** **`dev`** → **`main`** pour publication (PR recommandée)

### Exemple de workflow complet

```bash
git checkout dev
git pull origin dev
git checkout -b feature/rss-parser
# ... code + mises à jour docs/ ...
./scripts/validate-docs.sh
git commit -am "feat(rss): parser + doc"
git checkout dev
git merge feature/rss-parser --no-ff
git push origin dev
# Quand prêt pour la référence :
git checkout main
git pull origin main
git merge dev --no-ff
git push origin main
```

## Outils de validation

### Script de validation

Valide la structure, les liens et la cohérence :

```bash
./scripts/validate-docs.sh
```

Vérifie :
- Liens markdown valides
- Syntaxe Mermaid correcte
- Cohérence des préfixes (jb_, JB_, _jb_)
- Présence des fichiers requis

### Script d'analyse

Analyse la documentation et génère un rapport :

```bash
php scripts/analyze-docs.php
```

Extrait :
- Composants documentés (classes JB_*)
- Fonctions documentées (jb_*)
- Hooks WordPress
- Dépendances

Génère un rapport JSON : `scripts/docs-analysis-report.json`

## Prompts réutilisables

Pour analyser la documentation avec un assistant IA, voir [Prompts réutilisables](prompts-reutilisables.md).

### Utilisation

1. **Planification initiale** : Utiliser Prompt 1 pour créer le plan complet MVP
2. **Développement itératif** : Utiliser Prompt 2 pour chaque module
3. **Validation continue** : Utiliser Prompt 3 pour maintenir la cohérence
4. **Nouvelles features** : Utiliser Prompt 4 pour documenter de nouvelles fonctionnalités

## Template de plan de développement

Pour créer un plan de développement standardisé, utiliser le [Template de plan](template-plan-developpement.md).

## Validation avant merge

### Checklist

- [ ] Tous les liens fonctionnent
- [ ] Diagrammes Mermaid valides
- [ ] Préfixes cohérents (jb_, JB_, _jb_)
- [ ] Références croisées correctes
- [ ] Script de validation passe
- [ ] Documentation à jour avec le code

### Commandes de validation

```bash
# Validation complète
./scripts/validate-docs.sh

# Analyse de la documentation
php scripts/analyze-docs.php

# Vérification manuelle des liens
find docs -name "*.md" -exec grep -l "\[.*\](.*)" {} \;
```

## GitHub Actions

Un workflow GitHub Actions valide automatiquement la documentation sur les branches `docs` et `main`.

Voir : `.github/workflows/docs-validation.yml`

## Bonnes pratiques

1. **Documenter en parallèle** : Ne pas attendre la fin du développement pour documenter
2. **Valider régulièrement** : Exécuter les scripts de validation souvent
3. **Maintenir la cohérence** : Suivre les conventions de nommage
4. **Mettre à jour les références** : Vérifier les liens après déplacement de fichiers
5. **Utiliser les templates** : Suivre le template pour les plans de développement

## Références

- [DEVELOPMENT.md](../../DEVELOPMENT.md) - Guide de développement général
- [Contributing Guide](contributing.md) - Guide de contribution
- [Coding Standards](coding-standards.md) - Standards de code
- [Prompts réutilisables](prompts-reutilisables.md) - Prompts pour analyse IA
- [Template de plan](template-plan-developpement.md) - Template pour plans de développement

