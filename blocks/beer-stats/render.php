<?php
/**
 * jardin-toasts/beer-stats — aggregated stats over the checkin CPT.
 *
 * @package JardinToasts
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

global $wpdb;

$post_type   = Jardin_Toasts_Post_Type::POST_TYPE;
$tax_brewery = Jardin_Toasts_Taxonomies::BREWERY;
$tax_style   = Jardin_Toasts_Taxonomies::STYLE;

$show_source = ! isset( $attributes['showSource'] ) || (bool) $attributes['showSource'];

// 1. Total checkins (proxy for "unique beers" — Untappd dedupes by checkin id).
$total_checkins = (int) wp_count_posts( $post_type )->publish;

// 2. Distinct brewery terms used by published checkins.
$brewery_count = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(DISTINCT tt.term_id)
		   FROM {$wpdb->term_taxonomy} tt
		   INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
		   INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
		  WHERE tt.taxonomy = %s
		    AND p.post_type = %s
		    AND p.post_status = 'publish'",
		$tax_brewery,
		$post_type
	)
); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

// 3. Average rating_raw (excluding nulls).
$avg_rating = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT AVG(CAST(pm.meta_value AS DECIMAL(10,4)))
		   FROM {$wpdb->postmeta} pm
		   INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		  WHERE pm.meta_key = %s
		    AND p.post_type = %s
		    AND p.post_status = 'publish'
		    AND pm.meta_value <> ''",
		'_jardin_toasts_rating_raw',
		$post_type
	)
); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$avg_rating = null === $avg_rating ? null : round( (float) $avg_rating, 1 );

// 4. Top style (term with most published checkins).
$top_style_row = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT t.name AS name, COUNT(p.ID) AS cnt
		   FROM {$wpdb->terms} t
		   INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
		   INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
		   INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
		  WHERE tt.taxonomy = %s
		    AND p.post_type = %s
		    AND p.post_status = 'publish'
		  GROUP BY t.term_id
		  ORDER BY cnt DESC
		  LIMIT 1",
		$tax_style,
		$post_type
	)
); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$top_style = $top_style_row && '' !== $top_style_row->name ? (string) $top_style_row->name : null;

// 5. Top brewery (term with most published checkins).
$top_brewery_row = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT t.name AS name, COUNT(p.ID) AS cnt
		   FROM {$wpdb->terms} t
		   INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
		   INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
		   INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
		  WHERE tt.taxonomy = %s
		    AND p.post_type = %s
		    AND p.post_status = 'publish'
		  GROUP BY t.term_id
		  ORDER BY cnt DESC
		  LIMIT 1",
		$tax_brewery,
		$post_type
	)
); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$top_brewery = $top_brewery_row && '' !== $top_brewery_row->name ? (string) $top_brewery_row->name : null;

if ( 0 === $total_checkins ) {
	return '<div class="jardin-toasts-block-placeholder jardin-toasts-block-placeholder--empty"><p>' . esc_html__( 'No beer check-ins yet.', 'jardin-toasts' ) . '</p></div>';
}

$dash = '—';

ob_start();
?>
<div class="jardin-toasts-beer-stats wp-block-jardin-toasts-beer-stats">
	<div class="jardin-toasts-beer-stats__item">
		<span class="jardin-toasts-beer-stats__num"><?php echo esc_html( number_format_i18n( $total_checkins ) ); ?></span>
		<span class="jardin-toasts-beer-stats__lbl"><?php esc_html_e( 'check-ins', 'jardin-toasts' ); ?></span>
	</div>
	<div class="jardin-toasts-beer-stats__item">
		<span class="jardin-toasts-beer-stats__num"><?php echo esc_html( number_format_i18n( $brewery_count ) ); ?></span>
		<span class="jardin-toasts-beer-stats__lbl"><?php esc_html_e( 'breweries', 'jardin-toasts' ); ?></span>
	</div>
	<div class="jardin-toasts-beer-stats__item">
		<span class="jardin-toasts-beer-stats__num"><?php echo esc_html( $top_style ?? $dash ); ?></span>
		<span class="jardin-toasts-beer-stats__lbl"><?php esc_html_e( 'top style', 'jardin-toasts' ); ?></span>
	</div>
	<div class="jardin-toasts-beer-stats__item">
		<span class="jardin-toasts-beer-stats__num"><?php echo esc_html( null !== $avg_rating ? number_format_i18n( $avg_rating, 1 ) : $dash ); ?></span>
		<span class="jardin-toasts-beer-stats__lbl"><?php esc_html_e( 'average rating', 'jardin-toasts' ); ?></span>
	</div>
	<div class="jardin-toasts-beer-stats__item">
		<span class="jardin-toasts-beer-stats__num"><?php echo esc_html( $top_brewery ?? $dash ); ?></span>
		<span class="jardin-toasts-beer-stats__lbl"><?php esc_html_e( 'favourite brewery', 'jardin-toasts' ); ?></span>
	</div>
	<?php if ( $show_source ) : ?>
	<div class="jardin-toasts-beer-stats__item jardin-toasts-beer-stats__item--source">
		<span class="jardin-toasts-beer-stats__num jardin-toasts-beer-stats__num--source">auto · untappd</span>
		<span class="jardin-toasts-beer-stats__lbl"><?php esc_html_e( 'sync source', 'jardin-toasts' ); ?></span>
	</div>
	<?php endif; ?>
</div>
<?php
return (string) ob_get_clean();
