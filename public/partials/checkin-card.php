<?php
/**
 * Check-in card for archives.
 *
 * @package JardinToasts
 */

$classes = get_option( 'jb_microformats_enabled', true ) ? 'jb-card h-entry' : 'jb-card';
?>
<article <?php post_class( $classes ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" class="jb-card__thumb"><?php the_post_thumbnail( 'medium' ); ?></a>
	<?php endif; ?>
	<div class="jb-card__body">
		<h2 class="jb-card__title p-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="jb-card__rating"><?php jb_the_rating_stars(); ?></div>
		<div class="jb-card__excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
