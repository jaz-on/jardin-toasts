<?php
/**
 * jardin-toasts/recent-reviews — editorial reviews (check-ins with non-empty content).
 *
 * @package JardinToasts
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$n = isset( $attributes['postsToShow'] ) ? max( 1, min( 12, absint( $attributes['postsToShow'] ) ) ) : 3;

$q = new WP_Query(
	array(
		'post_type'      => Jardin_Toasts_Post_Type::POST_TYPE,
		'post_status'    => 'publish',
		'posts_per_page' => $n,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
		// Editorial = post_content not empty.
		'has_password'   => false,
	)
);

if ( ! $q->have_posts() ) {
	return '<div class="jardin-toasts-block-placeholder jardin-toasts-block-placeholder--empty"><p>' . esc_html__( 'No reviews yet.', 'jardin-toasts' ) . '</p><p class="jardin-toasts-block-placeholder__hint">' . esc_html__( 'A review is a check-in with editorial commentary in the post body.', 'jardin-toasts' ) . '</p></div>';
}

ob_start();
echo '<div class="jardin-toasts-recent-reviews wp-block-jardin-toasts-recent-reviews">';

$rendered = 0;
while ( $q->have_posts() ) {
	$q->the_post();
	$post_id = (int) get_the_ID();
	$content = get_post_field( 'post_content', $post_id );
	if ( '' === trim( wp_strip_all_tags( (string) $content ) ) ) {
		continue;
	}
	$brewery = (string) get_post_meta( $post_id, '_jardin_toasts_brewery_name', true );
	$style   = (string) get_post_meta( $post_id, '_jardin_toasts_beer_style', true );
	$abv     = (string) get_post_meta( $post_id, '_jardin_toasts_beer_abv', true );
	$stars   = function_exists( 'jardin_toasts_the_rating_stars' ) ? jardin_toasts_the_rating_stars( $post_id, false ) : '';
	$excerpt = wp_trim_words( wp_strip_all_tags( (string) $content ), 50, '…' );
	$meta_parts = array_filter(
		array(
			$brewery,
			$style,
			'' !== $abv ? sprintf( '%s %%', number_format_i18n( (float) $abv, 1 ) ) : '',
			esc_html( get_the_date( '', $post_id ) ),
		),
		static fn( $v ) => '' !== (string) $v
	);
	?>
	<article class="jardin-toasts-review-card">
		<header class="jardin-toasts-review-card__head">
			<span class="jardin-toasts-review-card__kind"><?php esc_html_e( 'review', 'jardin-toasts' ); ?></span>
			<?php if ( '' !== $stars ) : ?>
				<span class="jardin-toasts-review-card__stars"><?php echo $stars; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<?php endif; ?>
		</header>
		<h3 class="jardin-toasts-review-card__title">
			<a href="<?php echo esc_url( (string) get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
		</h3>
		<?php if ( ! empty( $meta_parts ) ) : ?>
			<p class="jardin-toasts-review-card__meta"><?php echo esc_html( implode( ' · ', $meta_parts ) ); ?></p>
		<?php endif; ?>
		<p class="jardin-toasts-review-card__comment"><?php echo esc_html( $excerpt ); ?></p>
	</article>
	<?php
	++$rendered;
}
echo '</div>';
wp_reset_postdata();
$buffer = (string) ob_get_clean();

if ( 0 === $rendered ) {
	return '<div class="jardin-toasts-block-placeholder jardin-toasts-block-placeholder--empty"><p>' . esc_html__( 'No reviews yet.', 'jardin-toasts' ) . '</p><p class="jardin-toasts-block-placeholder__hint">' . esc_html__( 'A review is a check-in with editorial commentary in the post body.', 'jardin-toasts' ) . '</p></div>';
}

return $buffer;
