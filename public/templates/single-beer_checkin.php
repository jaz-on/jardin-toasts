<?php
/**
 * Single check-in template.
 *
 * @package BeerJournal
 */

get_header();

$mf_article = get_option( 'bj_microformats_enabled', true ) ? 'h-entry' : '';
$mf_content = get_option( 'bj_microformats_enabled', true ) ? 'e-content' : '';
?>
<div id="primary" class="content-area">
	<main id="main" class="site-main">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( $mf_article ); ?>>
				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title p-name">', '</h1>' ); ?>
					<div class="bj-meta">
						<?php bj_the_rating_stars(); ?>
						<time class="bj-date" datetime="<?php echo esc_attr( get_post_meta( get_the_ID(), '_bj_checkin_date', true ) ); ?>">
							<?php echo esc_html( get_the_date() ); ?>
						</time>
					</div>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="bj-featured">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
				<?php endif; ?>

				<div class="entry-content <?php echo esc_attr( $mf_content ); ?>">
					<?php
					$raw = get_post_field( 'post_content', get_the_ID() );
					$raw = apply_filters( 'bj_checkin_content', $raw );
					echo apply_filters( 'the_content', $raw ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</div>

				<?php
				$url = get_post_meta( get_the_ID(), '_bj_checkin_url', true );
				if ( is_string( $url ) && '' !== $url ) :
					?>
					<p class="bj-untappd-link">
						<a href="<?php echo esc_url( $url ); ?>" rel="noopener noreferrer" target="_blank"><?php esc_html_e( 'View on Untappd', 'beer-journal' ); ?></a>
					</p>
				<?php endif; ?>
			</article>
			<?php
		endwhile;
		?>
	</main>
</div>
<?php
get_footer();
