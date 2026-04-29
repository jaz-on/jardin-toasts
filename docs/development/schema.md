# Schema.org & Microformats

## Overview

Jardin Toasts embarque un balisage SEO par défaut:
- JSON-LD (Schema.org) de type `Review` avec `itemReviewed` (`Product`)
- Microformats `h-entry` et `e-content` dans les templates

Ces fonctionnalités sont activées par défaut et peuvent être désactivées via les options.

## Options

| Option | Type | Default | Description |
|-------|------|---------|-------------|
| `jb_schema_enabled` | bool | `true` | Active/désactive l’injection JSON-LD |
| `jb_microformats_enabled` | bool | `true` | Active/désactive les microformats dans les templates |

Ces options résident dans `wp_options` (voir `docs/db/options.md`) et sont exposées dans Settings > Advanced.

## JSON-LD Structure (Review/Product)

Champs principaux:
- `@type`: `Review`
- `itemReviewed`: `Product` (nom de la bière, brasserie comme `Brand`)
- `reviewRating`: `Rating` (valeur 0–5, `bestRating` 5)
- `author`: `Person` (nom du site/blogueur)
- `datePublished`: date du check-in

Exemple (indicatif):

```json
{
  "@context": "https://schema.org/",
  "@type": "Review",
  "itemReviewed": {
    "@type": "Product",
    "name": "Meteor Blonde",
    "brand": {
      "@type": "Brand",
      "name": "Brasserie Meteor"
    }
  },
  "reviewRating": {
    "@type": "Rating",
    "ratingValue": "4",
    "bestRating": "5"
  },
  "author": {
    "@type": "Person",
    "name": "Jaz"
  },
  "datePublished": "2025-11-09"
}
```

Guidelines:
- Échapper le JSON correctement (`wp_json_encode`).
- N’inclure aucune donnée sensible (emails, IP, etc.).
- Respecter les limites de responsabilité (l’auteur peut être le propriétaire du site).

## Microformats

Classes à appliquer côté templates:
- `h-entry` sur l’élément article principal (single check-in)
- `p-name` pour le titre
- `e-content` pour le contenu de l’entrée (commentaire)
- `p-author` pour l’auteur
- `dt-published` pour la date

Compatibilité:
- Cohabite avec JSON-LD (Google privilégie JSON-LD).
- Utile pour Webmention et écosystèmes IndieWeb.

## Validation & Tests

- Google Rich Results Test (JSON-LD)
- Google Search Console (enhancements)
- Validateurs Microformats

## Performance & Cache

- L’injection JSON-LD est légère (quelques centaines d’octets).
- Peut être mise en cache si besoin (peu prioritaire).

## Sécurité

- Échapper le JSON et toutes chaînes insérées.
- Ne pas exposer d’identifiants internes en clair.

## Références

- schema.org Review: https://schema.org/Review
- schema.org Product: https://schema.org/Product
- schema.org Rating: https://schema.org/Rating

