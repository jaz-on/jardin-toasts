# Publier la documentation vers `main`

> **Historique :** ce fichier s’appelait « merger la branche `docs` ». La branche Git `docs` n’existe plus ; le dossier **`docs/`** est versionné sur **`dev`** et **`main`**.

Voir le flux complet dans [DEVELOPMENT.md](../../DEVELOPMENT.md). Synthèse :

## Avant merge

1. **Validation** :
   ```bash
   ./scripts/validate-docs.sh
   ```
2. **Arbre Git propre** : `git status` sans changements non voulus.

## Publier `dev` → `main`

```bash
git fetch origin
git checkout main
git pull origin main
git merge origin/dev --no-ff -m "chore: merge dev into main (doc/code)"
./scripts/validate-docs.sh
git push origin main
```

## Après merge

- Vérifier `docs/` et relancer `./scripts/validate-docs.sh` si besoin.
- Tags / releases : uniquement si tu en as besoin pour marquer une version (optionnel).

## Notes

- Préférer une **pull request** `dev` → `main` si plusieurs personnes contribuent (`main` est protégée).
- L’ancienne procédure `git merge docs` est **obsolète** et ne doit plus être utilisée.

