<?php
/**
 * Check-in card for archives.
 *
 * @package BeerJournal
 */

$classes = get_option( 'bj_microformats_enabled', true ) ? 'bj-card h-entry' : 'bj-card';
?>
<article <?php post_class( $classes ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="bj-card__thumb"><?php the_post_thumbnail( 'medium' ); ?></a>
	<?php endif; ?>
	<div class="bj-card__body">
		<h2 class="bj-card__title p-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="bj-card__rating"><?php bj_the_rating_stars(); ?></div>
		<div class="bj-card__excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
