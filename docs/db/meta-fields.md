# Meta Fields

## Overview

Les métadonnées des check-ins sont stockées dans `wp_postmeta` avec le préfixe **`_jt_`** (underscore initial = champs « cachés » dans l’éditeur classique). L’enregistrement REST et une partie des callbacks de sanitization sont dans [`includes/class-meta-fields.php`](../../includes/class-meta-fields.php) ; l’écriture à l’import dans [`includes/class-importer.php`](../../includes/class-importer.php) (`save_meta()`).

### Migration depuis `jb_*` / beer-journal

Les anciennes clés `_jb_*` sont renommées vers `_jt_*` au chargement du plugin selon **[Identifiants et migration](../development/legacy-identifiers.md)**.

---

## Identifiants

| Meta Key | Type | Description |
|----------|------|-------------|
| `_jt_checkin_id` | string | ID Untappd du check-in (dédoublonnage) |
| `_jt_checkin_url` | string | URL publique Untappd du check-in |

---

## Bière et brasserie

| Meta Key | Type | Description |
|----------|------|-------------|
| `_jt_beer_name` | string | Nom de la bière |
| `_jt_brewery_name` | string | Nom de la brasserie |
| `_jt_beer_style` | string | Style (redondant avec la taxonomie si assignée) |
| `_jt_beer_abv` | number | ABV % |
| `_jt_beer_ibu` | int | IBU |

---

## Check-in

| Meta Key | Type | Description |
|----------|------|-------------|
| `_jt_rating_raw` | number | Note brute Untappd (0–5) |
| `_jt_rating_rounded` | int | Niveau étoiles mappé (0–5) |
| `_jt_serving_type` | string | Type de service (fût, bouteille, etc.) |
| `_jt_checkin_date` | string | Date du check-in (chaîne stockée) |
| `_jt_exclude_sync` | string | `1` = ne pas mettre à jour ce post depuis la sync auto |

**Remarque** : il n’existe pas de méta `_jt_rating` unique ; seules `_jt_rating_raw` et `_jt_rating_rounded` sont utilisées.

---

## Lieu

| Meta Key | Type | Description |
|----------|------|-------------|
| `_jt_venue_name` | string | Nom du lieu (consommé avec l’option `jardin_toasts_import_venues`) |

---

## Social (si présent dans les données scrapées)

| Meta Key | Type | Description |
|----------|------|-------------|
| `_jt_toast_count` | int | Nombre de toasts |
| `_jt_comment_count` | int | Nombre de commentaires |

---

## Technique / import

| Meta Key | Type | Description |
|----------|------|-------------|
| `_jt_source` | string | `rss` ou `crawler` |
| `_jt_scraped_at` | string | Horodatage ISO du dernier scrape |
| `_jt_incomplete_reason` | string | Raison brouillon (`missing_rating`, `missing_beer_name`, etc.) |

---

## Métas sur les **pièces jointes** (images)

| Meta Key | Description |
|----------|-------------|
| `_jt_image_hash` | MD5 de l’URL source (dédoublonnage) |
| `_jt_image_source_url` | URL Untappd d’origine |

Ces clés sont sur la pièce jointe, pas sur le post check-in.

---

## Accès en PHP

```php
$rating = get_post_meta( $post_id, '_jt_rating_raw', true );
update_post_meta( $post_id, '_jt_beer_name', sanitize_text_field( $name ) );
```

### Requête par note arrondie

```php
$args = array(
	'post_type'      => 'beer_checkin',
	'posts_per_page' => 10,
	'meta_query'     => array(
		array(
			'key'     => '_jt_rating_rounded',
			'value'   => 4,
			'compare' => '>=',
			'type'    => 'NUMERIC',
		),
	),
);
```

Le CPT public est `beer_checkin` (voir `Jardin_Toasts_Post_Type::POST_TYPE`).

---

## Champs requis pour un post **publié**

L’importeur définit le statut `publish` lorsque bière, brasserie et note brute sont présents ; sinon `draft` avec `_jt_incomplete_reason`. Voir `Jardin_Toasts_Importer::import_checkin_data()`.

---

## Documentation liée

- [Options](options.md)
- [Schema](schema.md)
- [Indexes](indexes.md)
- [Identifiants et migration](../development/legacy-identifiers.md)
