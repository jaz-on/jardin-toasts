# REST API Endpoints — version initiale (1.0.0)

## Overview

Jardin Toasts n'expose pas d'endpoints REST personnalisés dans la version initiale (1.0.0). Le plugin utilise l'API REST native de WordPress pour le Custom Post Type `beer_checkin` et les taxonomies.

## Current Status

### Version initiale (1.0.0) — aucun endpoint custom

The plugin registers the Custom Post Type and taxonomies with `show_in_rest => true`, which automatically exposes them via WordPress REST API:

- **Check-ins**: `GET /wp-json/wp/v2/beer_checkin`
- **Beer Styles**: `GET /wp-json/wp/v2/beer_style`
- **Breweries**: `GET /wp-json/wp/v2/brewery`
- **Venues**: `GET /wp-json/wp/v2/venue`

Ces endpoints utilisent la structure standard de l'API REST WordPress et ne nécessitent pas d'endpoints personnalisés.

## WordPress Native REST API

### Check-ins Endpoint

**Endpoint**: `GET /wp-json/wp/v2/beer_checkin`

**Parameters**:
- `per_page` (int): Number of check-ins per page (default: 10, max: 100)
- `page` (int): Page number
- `orderby` (string): Order by field (`date`, `title`, `rating`)
- `order` (string): Order direction (`asc`, `desc`)
- `beer_style` (int): Filter by beer style term ID
- `brewery` (int): Filter by brewery term ID
- `venue` (int): Filter by venue term ID
- `meta_key` (string): Filter by meta key
- `meta_value` (string): Filter by meta value

**Response Example**:
```json
{
  "id": 123,
  "date": "2025-11-09T18:13:18",
  "title": {
    "rendered": "Meteor Blonde De Garde - Brasserie Meteor"
  },
  "content": {
    "rendered": "Great beer!",
    "protected": false
  },
  "meta": {
    "_jb_checkin_id": "1527514863",
    "_jb_rating_raw": "4.25",
    "_jb_rating_rounded": "4",
    "_jb_beer_name": "Meteor Blonde De Garde",
    "_jb_brewery_name": "Brasserie Meteor"
  },
  "beer_style": [5],
  "brewery": [12],
  "featured_media": 456
}
```

### Taxonomies Endpoints

**Beer Styles**: `GET /wp-json/wp/v2/beer_style`
**Breweries**: `GET /wp-json/wp/v2/brewery`
**Venues**: `GET /wp-json/wp/v2/venue`

All follow WordPress standard taxonomy REST API structure.

## References

- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [Custom Post Type REST API](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-rest-api-support-for-custom-content-types/)
- [Custom Taxonomies REST API](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-rest-api-support-for-custom-taxonomies/)

