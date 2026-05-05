<?php
/**
 * @package JardinToasts
 */

defined( 'ABSPATH' ) || exit;

$slug = isset( $attributes['venueSlug'] ) ? sanitize_title( (string) $attributes['venueSlug'] ) : '';
$n    = isset( $attributes['postsToShow'] ) ? max( 1, min( 48, absint( $attributes['postsToShow'] ) ) ) : 12;

if ( '' === $slug ) {
	return '<p class="jardin-toasts-block-placeholder">' . esc_html__( 'Set a venue slug in the block sidebar.', 'jardin-toasts' ) . '</p>';
}

$q = new WP_Query(
	array(
		'post_type'      => Jardin_Toasts_Post_Type::POST_TYPE,
		'posts_per_page' => $n,
		'post_status'    => 'publish',
		'tax_query'      => array(
			array(
				'taxonomy' => Jardin_Toasts_Taxonomies::VENUE,
				'field'    => 'slug',
				'terms'    => $slug,
			),
		),
		'no_found_rows'  => true,
	)
);

if ( ! $q->have_posts() ) {
	return '<p class="jardin-toasts-block-placeholder">' . esc_html__( 'No check-ins for this venue.', 'jardin-toasts' ) . '</p>';
}

ob_start();
echo '<div class="jardin-toasts-menu-display wp-block-jardin-toasts-menu-display">';
while ( $q->have_posts() ) {
	$q->the_post();
	$partial = JARDIN_TOASTS_PLUGIN_DIR . 'public/partials/checkin-card.php';
	if ( is_readable( $partial ) ) {
		include $partial;
	}
}
echo '</div>';
wp_reset_postdata();
return (string) ob_get_clean();
