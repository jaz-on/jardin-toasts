# Branche d’intégration `dev`

Ce document décrit l’usage de la branche **`dev`** (intégration quotidienne, ex. Git Updater sur un site de développement).

## Branche `dev` actuelle

La branche **`dev`** existe déjà sur le dépôt et sert d’intégration continue (ex. Git Updater sur un site de développement). Pour en créer une équivalente sur un autre clone :

```bash
git checkout main
git pull origin main
git checkout -b dev
git push -u origin dev
```

## Utilisation de `dev`

### Workflow de développement

```
main (référence / production)
  ↑
  └── dev (intégration)
      ├── feature/base-structure
      ├── feature/rss-parser
      ├── feature/scraper
      └── ...
```

### Créer une branche de feature

```bash
# Toujours partir de dev
git checkout dev
git pull origin dev

# Créer la branche de feature
git checkout -b feature/feature-name

# Développer...
git add .
git commit -m "feat: Implement feature"

# Pousser la branche
git push -u origin feature/feature-name
```

### Merger une feature dans dev

```bash
# Sur dev
git checkout dev
git pull origin dev

# Merger la feature
git merge feature/feature-name --no-ff -m "feat: Merge feature-name into dev"

# Pousser
git push origin dev
```

### Merger dev dans main

Après validation et tests :

```bash
# Sur main
git checkout main
git pull origin main

# Merger dev
git merge dev --no-ff -m "chore: Merge dev into main for release"

# Pousser
git push origin main

# Créer un tag si release
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin --tags
```

## Règles de développement

### Branches de feature

- **Nommage** : `feature/feature-name` (kebab-case)
- **Base** : Toujours partir de `dev`
- **Merge** : Dans `dev` après validation
- **Suppression** : Supprimer après merge dans `main`

### Branches de bugfix

- **Nommage** : `bugfix/issue-description`
- **Base** : `dev` (ou `main` pour hotfix)
- **Merge** : Dans `dev` (ou `main` pour hotfix)

### Branches de hotfix

- **Nommage** : `hotfix/issue-description`
- **Base** : `main`
- **Merge** : Dans `main` ET `dev`

## Intégration avec documentation

### Documentation parallèle

Lors du développement d'une feature :

1. **Code** : Sur `feature/feature-name`
2. **Documentation** : Sur `docs/feature-name`
3. **Merge code** : `feature/feature-name` → `dev`
4. **Merge doc** : `docs/feature-name` → `docs`
5. **Merge docs** : `docs` → `main`
6. **Merge dev** : `dev` → `main`

Voir [Workflow de documentation](workflow-documentation.md) pour plus de détails.

## Exemple complet

### Développement d'une feature

```bash
# 1. Créer branche de feature
git checkout dev
git checkout -b feature/rss-parser

# 2. Développer
# ... code ...

# 3. Commiter
git add .
git commit -m "feat(rss): Add RSS parser class"

# 4. Pousser
git push -u origin feature/rss-parser

# 5. Créer PR vers dev
# (via interface GitHub)

# 6. Après merge, supprimer la branche locale
git checkout dev
git pull origin dev
git branch -d feature/rss-parser
```

### Documentation en parallèle

```bash
# 1. Créer branche de documentation
git checkout docs
git checkout -b docs/feature-rss-parser

# 2. Documenter
# ... documentation ...

# 3. Commiter
git add .
git commit -m "docs: Document RSS parser feature"

# 4. Merger dans docs
git checkout docs
git merge docs/feature-rss-parser

# 5. Valider
./scripts/validate-docs.sh

# 6. Merger docs dans main
git checkout main
git merge docs
```

## Bonnes pratiques

1. **Toujours partir de dev** pour les nouvelles features
2. **Valider avant merge** : Tests, linting, validation docs
3. **Messages de commit clairs** : Suivre Conventional Commits
4. **PR descriptives** : Expliquer les changements
5. **Tests** : Ajouter/ mettre à jour les tests
6. **Documentation** : Mettre à jour la doc en parallèle

## Références

- [DEVELOPMENT.md](../../DEVELOPMENT.md) - Guide de développement
- [Workflow de documentation](workflow-documentation.md) - Workflow docs
- [Contributing Guide](contributing.md) - Guide de contribution

