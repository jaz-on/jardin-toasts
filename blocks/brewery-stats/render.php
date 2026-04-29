<?php
/**
 * @package JardinToasts
 */

defined( 'ABSPATH' ) || exit;

$term_id = isset( $attributes['breweryTermId'] ) ? absint( $attributes['breweryTermId'] ) : 0;
if ( $term_id <= 0 ) {
	return '<p class="jb-block-placeholder">' . esc_html__( 'Select a brewery term ID in the block sidebar.', 'jardin-toasts' ) . '</p>';
}

$term = get_term( $term_id, JB_Taxonomies::BREWERY );
if ( ! $term || is_wp_error( $term ) ) {
	return '<p class="jb-block-placeholder">' . esc_html__( 'Brewery not found.', 'jardin-toasts' ) . '</p>';
}

$q = new WP_Query(
	array(
		'post_type'      => JB_Post_Type::POST_TYPE,
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => false,
		'tax_query'      => array(
			array(
				'taxonomy' => JB_Taxonomies::BREWERY,
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		),
	)
);

$count = (int) $q->found_posts;
wp_reset_postdata();

return sprintf(
	'<div class="jb-brewery-stats wp-block-jardin-toasts-brewery-stats"><p class="jb-brewery-stats__title">%s</p><p class="jb-brewery-stats__count">%s</p></div>',
	esc_html( $term->name ),
	esc_html(
		sprintf(
			/* translators: %d: number of check-ins */
			_n( '%d check-in', '%d check-ins', $count, 'jardin-toasts' ),
			$count
		)
	)
);
