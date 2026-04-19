# Scripts de documentation

Ce dossier contient les scripts utilitaires pour la gestion et la validation de la documentation.

## Scripts disponibles

### validate-docs.sh

Script de validation de la documentation.

**Usage** :
```bash
./scripts/validate-docs.sh
```

**Fonctionnalités** :
- Vérifie les liens markdown
- Valide la syntaxe Mermaid
- Vérifie la cohérence des préfixes (bj_, BJ_, _bj_)
- Génère un rapport dans `docs-validation-report.txt`

**Sortie** :
- Rapport texte : `docs-validation-report.txt`
- Code de sortie : 0 si succès, 1 si erreurs

### analyze-docs.php

Script d'analyse de la documentation.

**Usage** :
```bash
php scripts/analyze-docs.php
```

**Fonctionnalités** :
- Parse tous les fichiers markdown dans `/docs/`
- Extrait les composants documentés (classes, fonctions, hooks)
- Identifie les dépendances entre modules
- Génère un rapport JSON

**Sortie** :
- Rapport JSON : `scripts/docs-analysis-report.json`
- Affichage console avec statistiques

## Fichiers de référence

### MERGE_DOCS.md

Anciennes commandes de merge `docs` → `main` (la branche `docs` n’existe plus). Voir [DEVELOPMENT.md](../DEVELOPMENT.md) pour le flux `dev` → `main`.

### RELEASE_NOTES_DOCS.md

Notes de release pour la version de documentation complète.

## Intégration CI/CD

Les scripts sont utilisés par le workflow GitHub Actions :
- `.github/workflows/docs-validation.yml`

Le workflow s'exécute automatiquement sur :
- Push vers `docs` ou `main`
- Pull requests vers `docs` ou `main`
- Modifications dans `docs/**`

## Dépendances

### validate-docs.sh
- Bash 4.0+
- `find`, `grep`, `test` (commandes Unix standard)

### analyze-docs.php
- PHP 8.2+
- Extensions : `json`, `mbstring`

## Exemples d'utilisation

### Validation avant commit

```bash
# Valider la documentation avant de commiter
./scripts/validate-docs.sh
if [ $? -eq 0 ]; then
    git add docs/
    git commit -m "docs: Update documentation"
else
    echo "Erreurs de validation détectées. Corriger avant de commiter."
fi
```

### Analyse pour planification

```bash
# Analyser la documentation pour créer un plan
php scripts/analyze-docs.php
# Consulter scripts/docs-analysis-report.json
```

### Validation continue

```bash
# Dans un hook Git pre-commit
#!/bin/bash
./scripts/validate-docs.sh
exit $?
```

## Troubleshooting

### Erreur : Permission denied

```bash
chmod +x scripts/validate-docs.sh
```

### Erreur : PHP not found

Vérifier que PHP est installé et dans le PATH :
```bash
which php
php --version
```

### Erreur : Script must be run from project root

S'assurer d'être dans la racine du projet :
```bash
cd /path/to/beer-journal
```

## Contribution

Pour ajouter de nouveaux scripts :

1. Créer le script dans `scripts/`
2. Ajouter la documentation dans ce README
3. Mettre à jour le workflow GitHub Actions si nécessaire
4. Tester sur différentes plateformes (Linux, macOS, Windows avec Git Bash)

