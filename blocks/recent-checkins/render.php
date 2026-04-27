<?php
/**
 * @package BeerJournal
 */

defined( 'ABSPATH' ) || exit;

$n = isset( $attributes['postsToShow'] ) ? max( 1, min( 24, absint( $attributes['postsToShow'] ) ) ) : 6;

$q = new WP_Query(
	array(
		'post_type'      => BJ_Post_Type::POST_TYPE,
		'posts_per_page' => $n,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	)
);

if ( ! $q->have_posts() ) {
	return '<p class="bj-block-placeholder">' . esc_html__( 'No check-ins yet.', 'beer-journal' ) . '</p>';
}

ob_start();
echo '<div class="bj-recent-checkins wp-block-beer-journal-recent-checkins">';
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
