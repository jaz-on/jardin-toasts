# REST API Endpoints

## Overview

Beer Journal does **not** currently expose custom REST API endpoints in Phase 1 (MVP). The plugin uses WordPress's built-in REST API for the Custom Post Type `beer_checkin` and taxonomies.

## Current Status

### Phase 1 (MVP) - No Custom Endpoints

The plugin registers the Custom Post Type and taxonomies with `show_in_rest => true`, which automatically exposes them via WordPress REST API:

- **Check-ins**: `GET /wp-json/wp/v2/beer_checkin`
- **Beer Styles**: `GET /wp-json/wp/v2/beer_style`
- **Breweries**: `GET /wp-json/wp/v2/brewery`
- **Venues**: `GET /wp-json/wp/v2/venue`

These endpoints use WordPress's standard REST API structure and don't require custom endpoints.

## Planned for Phase 3

Custom REST API endpoints are planned for Phase 3 (Version 2.0) and will include:

### Potential Endpoints

- `POST /wp-json/beer-journal/v1/sync` - Trigger manual RSS sync
- `POST /wp-json/beer-journal/v1/import` - Trigger historical import
- `GET /wp-json/beer-journal/v1/stats` - Get statistics
- `GET /wp-json/beer-journal/v1/checkin/{id}/retry` - Retry failed import
- `POST /wp-json/beer-journal/v1/webhook` - Webhook endpoint for real-time sync (future)

### Authentication

All custom endpoints will require:
- WordPress authentication (nonces, cookies, or application passwords)
- Capability check: `current_user_can('manage_options')` for admin actions

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
    "_bj_checkin_id": "1527514863",
    "_bj_rating_raw": "4.25",
    "_bj_rating_rounded": "4",
    "_bj_beer_name": "Meteor Blonde De Garde",
    "_bj_brewery_name": "Brasserie Meteor"
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

## Future API Documentation

When custom endpoints are implemented in Phase 3, this document will be expanded with:
- Complete OpenAPI 3.0 specification (`openapi.yaml`)
- Authentication methods
- Request/response schemas
- Error handling
- Rate limiting
- Webhook documentation

## References

- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [Custom Post Type REST API](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-rest-api-support-for-custom-content-types/)
- [Custom Taxonomies REST API](https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-rest-api-support-for-custom-taxonomies/)

