<?php
/**
 * Check-in card for archives.
 *
 * @package JardinToasts
 */

$classes = get_option( 'jardin_toasts_microformats_enabled', true ) ? 'jardin-toasts-card h-entry' : 'jardin-toasts-card';
?>
<article <?php post_class( $classes ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="jardin-toasts-card__thumb"><?php the_post_thumbnail( 'medium' ); ?></a>
	<?php endif; ?>
	<div class="jardin-toasts-card__body">
		<h2 class="jardin-toasts-card__title p-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="jardin-toasts-card__rating"><?php jardin_toasts_the_rating_stars(); ?></div>
		<div class="jardin-toasts-card__excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
