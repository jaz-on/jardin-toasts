# Commandes pour merger la branche docs dans main

> **Historique :** la branche distante `docs` a été retirée (2026-04). La documentation vit dans `docs/` sur **`main`** et **`dev`**. Pour publier des changements doc, merger `dev` → `main` (voir [DEVELOPMENT.md](../../DEVELOPMENT.md)). Le contenu ci-dessous reste comme référence d’anciennes procédures.

Ce fichier contenait les commandes pour merger la branche `docs` dans `main` selon l’ancien plan d’action.

## Phase 1.1 : Validation pré-merge

Avant de merger, vérifier :

1. **Cohérence des fichiers** :
   ```bash
   ./scripts/validate-docs.sh
   ```

2. **Git status propre** :
   ```bash
   git status
   # S'assurer qu'il n'y a pas de changements non commités
   # Si nécessaire : git add . && git commit -m "docs: Final updates"
   ```

3. **Absence de conflits potentiels** :
   ```bash
   git fetch origin
   git checkout main
   git pull origin main
   git checkout docs
   git merge main --no-commit --no-ff
   # Si conflits détectés, résoudre puis :
   git merge --abort
   ```

## Phase 1.2 : Processus de merge

Une fois la validation effectuée :

```bash
# 1. S'assurer d'être sur main et à jour
git checkout main
git pull origin main

# 2. Merger docs dans main avec message descriptif
git merge docs --no-ff -m "docs: Merge complete documentation (Phase 1 MVP)"

# 3. Créer un tag pour marquer cette version de documentation
git tag -a v1.0.0-docs -m "Documentation complete and validated"

# 4. Pousser les changements et le tag
git push origin main
git push origin --tags
```

## Phase 1.3 : Post-merge

Après le merge, vérifier :

1. **Fichiers docs présents** :
   ```bash
   ls -la docs/
   # Vérifier que tous les fichiers sont présents
   ```

2. **Liens internes fonctionnent** :
   ```bash
   ./scripts/validate-docs.sh
   ```

3. **Créer une release GitHub** (via interface web ou CLI) :
   - Titre : `Documentation Complete - Phase 1 MVP`
   - Tag : `v1.0.0-docs`
   - Description : Voir `RELEASE_NOTES_DOCS.md`

## Notes

- Le flag `--no-ff` crée un commit de merge explicite
- Le tag permet de revenir facilement à cette version de documentation
- Toujours valider avant et après le merge

