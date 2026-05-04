<?php
/**
 * @package JardinToasts
 */

defined( 'ABSPATH' ) || exit;

$n = isset( $attributes['postsToShow'] ) ? max( 1, min( 24, absint( $attributes['postsToShow'] ) ) ) : 6;

$q = new WP_Query(
	array(
		'post_type'      => JT_Post_Type::POST_TYPE,
		'posts_per_page' => $n,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	)
);

if ( ! $q->have_posts() ) {
	return '<div class="jt-block-placeholder jt-block-placeholder--empty"><p>' . esc_html__( 'No beer check-ins in this block yet.', 'jardin-toasts' ) . '</p><p class="jt-block-placeholder__hint">' . esc_html__( 'They appear here after Untappd sync creates beer_checkin posts (WP Admin → Jardin Toasts).', 'jardin-toasts' ) . '</p></div>';
}

ob_start();
echo '<div class="jt-recent-checkins wp-block-jardin-toasts-recent-checkins">';
while ( $q->have_posts() ) {
	$q->the_post();
	$partial = JT_PLUGIN_DIR . 'public/partials/checkin-card.php';
	if ( is_readable( $partial ) ) {
		include $partial;
	}
}
echo '</div>';
wp_reset_postdata();
return (string) ob_get_clean();
