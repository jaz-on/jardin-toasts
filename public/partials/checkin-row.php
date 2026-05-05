<?php
/**
 * Table row for archive (table layout).
 *
 * @package JardinToasts
 */
?>
<tr>
	<td class="jardin-toasts-row__thumb">
		<?php if ( has_post_thumbnail() ) : ?>
			<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'thumbnail' ); ?></a>
		<?php endif; ?>
	</td>
	<td class="jardin-toasts-row__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
	<td class="jardin-toasts-row__rating"><?php jardin_toasts_the_rating_stars(); ?></td>
	<td class="jardin-toasts-row__date"><?php echo esc_html( get_the_date() ); ?></td>
</tr>
