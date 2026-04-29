<?php
/**
 * Table row for archive (table layout).
 *
 * @package JardinToasts
 */
?>
<tr>
	<td class="jb-row__thumb">
		<?php if ( has_post_thumbnail() ) : ?>
			<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'thumbnail' ); ?></a>
		<?php endif; ?>
	</td>
	<td class="jb-row__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
	<td class="jb-row__rating"><?php jb_the_rating_stars(); ?></td>
	<td class="jb-row__date"><?php echo esc_html( get_the_date() ); ?></td>
</tr>
