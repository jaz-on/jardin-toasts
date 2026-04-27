<?php
/**
 * @package BeerJournal
 */

defined( 'ABSPATH' ) || exit;

$slug = isset( $attributes['venueSlug'] ) ? sanitize_title( (string) $attributes['venueSlug'] ) : '';
$n    = isset( $attributes['postsToShow'] ) ? max( 1, min( 48, absint( $attributes['postsToShow'] ) ) ) : 12;

if ( '' === $slug ) {
	return '<p class="bj-block-placeholder">' . esc_html__( 'Set a venue slug in the block sidebar.', 'beer-journal' ) . '</p>';
}

$q = new WP_Query(
	array(
		'post_type'      => BJ_Post_Type::POST_TYPE,
		'posts_per_page' => $n,
		'post_status'    => 'publish',
		'tax_query'      => array(
			array(
				'taxonomy' => BJ_Taxonomies::VENUE,
				'field'    => 'slug',
				'terms'    => $slug,
			),
		),
		'no_found_rows'  => true,
	)
);

if ( ! $q->have_posts() ) {
	return '<p class="bj-block-placeholder">' . esc_html__( 'No check-ins for this venue.', 'beer-journal' ) . '</p>';
}

ob_start();
echo '<div class="bj-menu-display wp-block-beer-journal-menu-display">';
while ( $q->have_posts() ) {
	$q->the_post();
	$partial = BJ_PLUGIN_DIR . 'public/partials/checkin-card.php';
	if ( is_readable( $partial ) ) {
		include $partial;
	}
}
echo '</div>';
wp_reset_postdata();
return (string) ob_get_clean();
